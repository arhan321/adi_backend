<?php

namespace App\Console\Commands\Crm;

use App\Models\CrmSetting;
use App\Models\Member;
use App\Models\RetentionLog;
use App\Models\WhatsappLog;
use App\Services\Crm\MemberPointService;
use App\Services\Crm\WhatsappMessageBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendRetentionMessages extends Command
{
    protected $signature = 'crm:send-retention-whatsapp {--dry-run : Cek data tanpa mengirim WhatsApp}';

    protected $description = 'Kirim pesan WhatsApp retensi otomatis untuk member yang lama tidak berkunjung.';

    public function handle(MemberPointService $memberPointService, WhatsappMessageBuilder $messageBuilder): int
    {
        $setting = CrmSetting::current();

        if (! $setting->auto_send_whatsapp) {
            $this->info('Auto-send WhatsApp sedang nonaktif.');

            return self::SUCCESS;
        }

        $retentionDays = (int) $setting->retention_days;
        $today = today();
        $processed = 0;
        $dryRun = (bool) $this->option('dry-run');

        Member::query()
            ->eligibleForRetention($retentionDays)
            ->chunkById((int) config('crm.retention.chunk_size', 100), function ($members) use ($memberPointService, $messageBuilder, $setting, $today, $dryRun, &$processed): void {
                foreach ($members as $member) {
                    $daysInactive = $member->last_visit_at?->diffInDays(now()) ?? 0;

                    if ($dryRun) {
                        $this->line("[DRY RUN] {$member->name} / {$member->phone} / {$daysInactive} hari tidak berkunjung");
                        $processed++;

                        continue;
                    }

                    DB::transaction(function () use ($member, $memberPointService, $messageBuilder, $setting, $today, $daysInactive, &$processed): void {
                        $retentionLog = RetentionLog::query()->firstOrCreate(
                            [
                                'member_id' => $member->id,
                                'retention_date' => $today->toDateString(),
                            ],
                            [
                                'last_visit_at' => $member->last_visit_at,
                                'days_inactive' => $daysInactive,
                                'status' => RetentionLog::STATUS_PENDING,
                                'notes' => 'Pesan retensi otomatis dibuat oleh scheduler.',
                            ]
                        );

                        if (! $retentionLog->wasRecentlyCreated) {
                            return;
                        }

                        $message = $messageBuilder->retention($member, $setting, $daysInactive);
                        $whatsappLog = $memberPointService->queueWhatsapp($member, WhatsappLog::TYPE_RETENTION, $message);

                        $retentionLog->update([
                            'whatsapp_log_id' => $whatsappLog->id,
                            'status' => RetentionLog::STATUS_PENDING,
                        ]);

                        $member->update([
                            'last_retention_sent_at' => now(),
                            'retention_message_count' => ((int) $member->retention_message_count) + 1,
                        ]);

                        $processed++;
                    });
                }
            });

        $this->info("Selesai. Total member diproses: {$processed}");

        return self::SUCCESS;
    }
}
