<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsappWebhookController extends Controller
{
    public function statusCallback(Request $request): Response
    {
        $messageSid = $request->input('MessageSid') ?: $request->input('SmsSid');
        $status = strtolower((string) ($request->input('MessageStatus') ?: $request->input('SmsStatus')));

        if ($messageSid) {
            $log = WhatsappLog::query()
                ->where('provider_message_id', $messageSid)
                ->first();

            if ($log) {
                if (in_array($status, ['sent', 'delivered', 'read'], true)) {
                    $log->update([
                        'status' => WhatsappLog::STATUS_SENT,
                        'sent_at' => $log->sent_at ?? now(),
                    ]);
                }

                if (in_array($status, ['failed', 'undelivered'], true)) {
                    $log->markAsFailed($request->input('ErrorMessage') ?: 'Twilio mengirim status gagal: ' . $status);
                }
            }
        }

        return response('', 204);
    }
}
