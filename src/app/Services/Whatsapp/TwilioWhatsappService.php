<?php

namespace App\Services\Whatsapp;

use App\Models\WhatsappLog;
use Illuminate\Support\Str;
use RuntimeException;
use Twilio\Rest\Client;

class TwilioWhatsappService
{
    public function send(WhatsappLog $log): WhatsappLog
    {
        if (! config('crm.twilio.enabled')) {
            $log->markAsFailed('Twilio WhatsApp belum aktif. Set TWILIO_WHATSAPP_ENABLED=true di .env.');

            return $log;
        }

        $accountSid = config('crm.twilio.account_sid');
        $authToken = config('crm.twilio.auth_token');
        $from = $this->formatWhatsappAddress(config('crm.twilio.whatsapp_from'));
        $to = $this->formatWhatsappAddress($log->phone);

        if (! $accountSid || ! $authToken || ! $from || ! $to) {
            $log->markAsFailed('Konfigurasi Twilio belum lengkap atau nomor WhatsApp tidak valid.');

            return $log;
        }

        try {
            $client = new Client($accountSid, $authToken);

            $payload = [
                'from' => $from,
                'body' => $log->message_body,
            ];

            if ($callbackUrl = config('crm.twilio.status_callback_url')) {
                $payload['statusCallback'] = $callbackUrl;
            }

            $message = $client->messages->create($to, $payload);

            $log->markAsSent($message->sid ?? null);
        } catch (\Throwable $throwable) {
            $log->markAsFailed($throwable->getMessage());
        }

        return $log->refresh();
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', trim($phone));

        if (Str::startsWith($phone, '+')) {
            return $phone;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (Str::startsWith($digits, '62')) {
            return '+' . $digits;
        }

        if (Str::startsWith($digits, '0')) {
            return '+62' . substr($digits, 1);
        }

        if (Str::startsWith($digits, '8')) {
            return '+62' . $digits;
        }

        if ($digits !== '') {
            return '+' . $digits;
        }

        throw new RuntimeException('Nomor WhatsApp tidak valid.');
    }

    public function formatWhatsappAddress(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $phone = trim($phone);

        if (Str::startsWith($phone, 'whatsapp:')) {
            return $phone;
        }

        return 'whatsapp:' . $this->normalizePhone($phone);
    }
}
