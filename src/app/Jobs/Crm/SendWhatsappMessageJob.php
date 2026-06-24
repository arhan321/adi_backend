<?php

namespace App\Jobs\Crm;

use App\Models\WhatsappLog;
use Illuminate\Bus\Queueable;
use App\Services\Whatsapp\FonnteWhatsappService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $whatsappLogId)
    {
    }

    public function handle(FonnteWhatsappService $whatsappService): void
    {
        $log = WhatsappLog::query()->find($this->whatsappLogId);

        if (! $log || $log->status !== WhatsappLog::STATUS_PENDING) {
            return;
        }

        $whatsappService->send($log);
    }
}
