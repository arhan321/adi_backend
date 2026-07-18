<?php

declare(strict_types=1);

namespace App\Console\Commands\Crm;

use App\Models\Member;
use App\Models\CrmSetting;
use App\Models\WhatsappLog;
use App\Models\RetentionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Crm\MemberPointService;
use App\Services\Crm\WhatsappMessageBuilder;

class SendRetentionMessages extends Command
{
    protected $signature = 'crm:send-retention-whatsapp
        {--dry-run : Cek jadwal yang jatuh tempo tanpa mengirim WhatsApp}';

    protected $description = 'Kirim satu pengingat WhatsApp untuk setiap periode poin yang sudah jatuh tempo.';

    public function handle(
        MemberPointService $memberPointService,
        WhatsappMessageBuilder $messageBuilder,
    ): int {
        $setting = CrmSetting::current();

        if (! $setting->auto_send_whatsapp) {
            $this->info('Auto-send WhatsApp sedang nonaktif.');

            return self::SUCCESS;
        }

        $this->expireFinishedCycles();

        $processed = 0;
        $skipped = 0;
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) config('crm.retention.chunk_size', 100));

        RetentionLog::query()
            ->due()
            ->with(['member', 'pointTransaction'])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($retentionLogs) use (
                $memberPointService,
                $messageBuilder,
                $setting,
                $dryRun,
                &$processed,
                &$skipped,
            ): void {
                foreach ($retentionLogs as $retentionLog) {
                    if ($dryRun) {
                        $member = $retentionLog->member;

                        $this->line(sprintf(
                            '[DRY RUN] %s / %s / pengingat ke-%d / hari ke-%d',
                            $member?->name ?? 'Member tidak ditemukan',
                            $member?->phone ?? '-',
                            (int) $retentionLog->reminder_number,
                            (int) $retentionLog->days_inactive,
                        ));

                        $processed++;
                        continue;
                    }

                    $result = DB::transaction(function () use (
                        $retentionLog,
                        $memberPointService,
                        $messageBuilder,
                        $setting,
                    ): string {
                        $lockedLog = RetentionLog::query()
                            ->with(['member', 'pointTransaction'])
                            ->lockForUpdate()
                            ->find($retentionLog->id);

                        if (! $lockedLog
                            || $lockedLog->status !== RetentionLog::STATUS_PENDING
                            || $lockedLog->whatsapp_log_id !== null
                            || $lockedLog->cancelled_at !== null
                        ) {
                            return 'ignored';
                        }

                        if ($lockedLog->expires_at && $lockedLog->expires_at->isPast()) {
                            $lockedLog->markAsSkipped('Siklus poin sudah berakhir sebelum pengingat dikirim.');

                            return 'skipped';
                        }

                        $member = $lockedLog->member;
                        $pointTransaction = $lockedLog->pointTransaction;

                        if (! $member || ! $pointTransaction) {
                            $lockedLog->markAsSkipped('Member atau transaksi poin tidak ditemukan.');

                            return 'skipped';
                        }

                        if ($member->status !== Member::STATUS_ACTIVE) {
                            $lockedLog->markAsSkipped('Member tidak aktif.');

                            return 'skipped';
                        }

                        if ($member->last_visit_at
                            && $pointTransaction->transaction_at
                            && $member->last_visit_at->greaterThan($pointTransaction->transaction_at)
                        ) {
                            $lockedLog->markAsSkipped('Pelanggan sudah kembali setelah transaksi poin ini.');

                            return 'skipped';
                        }

                        $message = $messageBuilder->retention(
                            $member,
                            $setting,
                            (int) $lockedLog->days_inactive,
                            (int) $lockedLog->reminder_number,
                            (int) $lockedLog->points_earned,
                        );

                        $whatsappLog = $memberPointService->queueWhatsapp(
                            $member,
                            WhatsappLog::TYPE_RETENTION,
                            $message,
                        );

                        $lockedLog->update([
                            'whatsapp_log_id' => $whatsappLog->id,
                            'notes' => sprintf(
                                'Pengingat ke-%d masuk antrean WhatsApp.',
                                (int) $lockedLog->reminder_number,
                            ),
                        ]);

                        return 'queued';
                    });

                    if ($result === 'queued') {
                        $processed++;
                    } elseif ($result === 'skipped') {
                        $skipped++;
                    }
                }
            });

        $this->info("Selesai. Antrean dibuat: {$processed}. Dilewati: {$skipped}.");

        return self::SUCCESS;
    }

    private function expireFinishedCycles(): void
    {
        RetentionLog::query()
            ->where('status', RetentionLog::STATUS_PENDING)
            ->whereNotNull('point_transaction_id')
            ->whereNull('cancelled_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => RetentionLog::STATUS_SKIPPED,
                'cancelled_at' => now(),
                'notes' => 'Siklus poin berakhir. Sistem tidak mengirim pengingat lagi.',
                'updated_at' => now(),
            ]);
    }
}
