<?php

namespace App\Jobs\Crm;

use App\Models\WhatsappLog;
use App\Services\Whatsapp\TwilioWhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $whatsappLogId) {}

    public function handle(TwilioWhatsappService $twilioWhatsappService): void
    {
        $log = WhatsappLog::query()->find($this->whatsappLogId);

        if (! $log || $log->status !== WhatsappLog::STATUS_PENDING) {
            return;
        }

        $twilioWhatsappService->send($log);
    }
}
