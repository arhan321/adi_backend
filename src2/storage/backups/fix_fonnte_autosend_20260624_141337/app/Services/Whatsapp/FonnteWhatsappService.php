<?php

namespace App\Services\Whatsapp;

use RuntimeException;
use App\Models\WhatsappLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class FonnteWhatsappService
{
    public function send(WhatsappLog $log): WhatsappLog
    {
        if (! (bool) config('crm.fonnte.enabled', false)) {
            $log->markAsFailed('Fonnte WhatsApp belum aktif. Set FONNTE_WHATSAPP_ENABLED=true di file .env.');

            return $log->refresh();
        }

        $token = (string) config('crm.fonnte.token', '');
        $target = $this->formatFonnteTarget($log->phone);
        $message = trim((string) $log->message_body);

        if ($token === '' || $target === null || $message === '') {
            $log->markAsFailed('Konfigurasi Fonnte belum lengkap, nomor WhatsApp tidak valid, atau isi pesan kosong.');

            return $log->refresh();
        }

        $baseUrl = rtrim((string) config('crm.fonnte.base_url', 'https://api.fonnte.com'), '/');
        $endpoint = '/' . ltrim((string) config('crm.fonnte.send_endpoint', '/send'), '/');
        $countryCode = (string) config('crm.fonnte.country_code', '62');

        $payload = [
            'target' => $target,
            'message' => $message,
            'countryCode' => $countryCode,
        ];

        if ($device = config('crm.fonnte.device')) {
            $payload['device'] = (string) $device;
        }

        if ($delay = config('crm.fonnte.delay')) {
            $payload['delay'] = (string) $delay;
        }

        try {
            $response = Http::asForm()
                ->timeout((int) config('crm.fonnte.timeout', 30))
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->post($baseUrl . $endpoint, $payload);

            if ($response->failed()) {
                $log->markAsFailed('Fonnte HTTP error ' . $response->status() . ': ' . Str::limit($response->body(), 500));

                return $log->refresh();
            }

            $result = $response->json();
            $isSuccess = (bool) data_get($result, 'status', false);

            if (! $isSuccess) {
                $errorMessage = data_get($result, 'reason')
                    ?: data_get($result, 'message')
                    ?: data_get($result, 'detail')
                    ?: $response->body();

                $log->markAsFailed('Fonnte gagal mengirim pesan: ' . Str::limit($this->stringifyError($errorMessage), 500));

                return $log->refresh();
            }

            $providerMessageId = $this->extractMessageId($result);
            $log->markAsSent($providerMessageId);
        } catch (\Throwable $throwable) {
            $log->markAsFailed($throwable->getMessage());
        }

        return $log->refresh();
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', trim($phone));

        if ($phone === '') {
            throw new RuntimeException('Nomor WhatsApp tidak valid.');
        }

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

    public function formatFonnteTarget(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $this->normalizePhone($phone));

        if ($digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        return $digits;
    }

    private function extractMessageId(mixed $result): ?string
    {
        if (! is_array($result)) {
            return null;
        }

        $candidates = [
            data_get($result, 'id'),
            data_get($result, 'data.id'),
            data_get($result, 'data.0.id'),
            data_get($result, 'detail.id'),
            data_get($result, 'detail.0.id'),
            data_get($result, 'messages.0.id'),
        ];

        foreach ($candidates as $candidate) {
            if (is_scalar($candidate) && trim((string) $candidate) !== '') {
                return (string) $candidate;
            }
        }

        return null;
    }

    private function stringifyError(mixed $error): string
    {
        if (is_scalar($error) || $error === null) {
            return (string) $error;
        }

        return json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'Unknown error';
    }
}
