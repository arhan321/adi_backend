<?php

declare(strict_types=1);

namespace App\Jobs\Crm;

use Throwable;
use RuntimeException;
use App\Models\Member;
use App\Models\WhatsappLog;
use App\Models\RetentionLog;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Whatsapp\FonnteWhatsappService;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 45;

    public function __construct(public int $whatsappLogId)
    {
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(FonnteWhatsappService $whatsappService): void
    {
        $log = WhatsappLog::query()->find($this->whatsappLogId);

        if (! $log) {
            return;
        }

        // Jika provider sudah sukses tetapi worker sempat berhenti sebelum sinkronisasi,
        // jalankan sinkronisasi secara idempotent tanpa mengirim ulang pesan.
        if ($log->status === WhatsappLog::STATUS_SENT) {
            $this->syncRetentionSuccess($log);

            return;
        }

        if ($this->retentionWasCancelled($log)) {
            if ($log->status !== WhatsappLog::STATUS_FAILED) {
                $log->markAsFailed('Pengiriman dibatalkan karena siklus retention sudah berhenti.');
            }

            return;
        }

        // FonnteWhatsappService menandai log sebagai failed jika request gagal.
        // Saat retry, status dikembalikan ke pending agar percobaan berikutnya dapat berjalan.
        if ($log->status === WhatsappLog::STATUS_FAILED) {
            $log->update([
                'status' => WhatsappLog::STATUS_PENDING,
                'error_message' => null,
                'failed_at' => null,
            ]);
        }

        $result = $whatsappService->send($log);

        if ($result->status === WhatsappLog::STATUS_SENT) {
            $this->syncRetentionSuccess($result);

            return;
        }

        $error = $result->error_message ?: 'Fonnte gagal mengirim pesan WhatsApp.';

        if ($this->attempts() >= $this->tries) {
            $this->syncRetentionFailure($result, $error);

            return;
        }

        throw new RuntimeException($error);
    }

    public function failed(Throwable $exception): void
    {
        $log = WhatsappLog::query()->find($this->whatsappLogId);

        if ($log) {
            $this->syncRetentionFailure($log, $exception->getMessage());
        }
    }

    private function retentionWasCancelled(WhatsappLog $log): bool
    {
        if ($log->message_type !== WhatsappLog::TYPE_RETENTION) {
            return false;
        }

        $retentionLog = RetentionLog::query()
            ->where('whatsapp_log_id', $log->id)
            ->first();

        return ! $retentionLog
            || $retentionLog->status !== RetentionLog::STATUS_PENDING
            || $retentionLog->cancelled_at !== null;
    }

    private function syncRetentionSuccess(WhatsappLog $whatsappLog): void
    {
        if ($whatsappLog->message_type !== WhatsappLog::TYPE_RETENTION) {
            return;
        }

        DB::transaction(function () use ($whatsappLog): void {
            $retentionLog = RetentionLog::query()
                ->where('whatsapp_log_id', $whatsappLog->id)
                ->lockForUpdate()
                ->first();

            if (! $retentionLog
                || $retentionLog->status !== RetentionLog::STATUS_PENDING
                || $retentionLog->cancelled_at !== null
            ) {
                return;
            }

            $retentionLog->markAsSent();

            $member = Member::query()
                ->lockForUpdate()
                ->find($retentionLog->member_id);

            if ($member) {
                $member->update([
                    'last_retention_sent_at' => now(),
                    'retention_message_count' => ((int) $member->retention_message_count) + 1,
                ]);
            }
        });
    }

    private function syncRetentionFailure(WhatsappLog $whatsappLog, string $error): void
    {
        if ($whatsappLog->message_type !== WhatsappLog::TYPE_RETENTION) {
            return;
        }

        RetentionLog::query()
            ->where('whatsapp_log_id', $whatsappLog->id)
            ->where('status', RetentionLog::STATUS_PENDING)
            ->update([
                'status' => RetentionLog::STATUS_FAILED,
                'notes' => mb_substr($error, 0, 1000),
                'updated_at' => now(),
            ]);
    }
}
