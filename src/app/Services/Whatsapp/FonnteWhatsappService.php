<?php

declare(strict_types=1);

namespace App\Services\Whatsapp;

use App\Models\WhatsappLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FonnteWhatsappService
{
    private const PROVIDER = 'fonnte';

    public function send(WhatsappLog $log): WhatsappLog
    {
        $this->setProvider($log);

        $enabled = filter_var(
            config('services.fonnte.whatsapp_enabled', config('services.fonnte.enabled', false)),
            FILTER_VALIDATE_BOOLEAN
        );

        if (! $enabled) {
            $log->markAsFailed('Fonnte WhatsApp belum aktif. Cek FONNTE_ENABLED=true dan FONNTE_WHATSAPP_ENABLED=true di file .env.');

            return $log->refresh();
        }

        $token = trim((string) config('services.fonnte.token'));
        $url = trim((string) config('services.fonnte.url', 'https://api.fonnte.com/send'));

        if ($token === '') {
            $log->markAsFailed('Token Fonnte belum diisi. Isi FONNTE_TOKEN di file .env.');

            return $log->refresh();
        }

        try {
            $target = $this->normalizePhone((string) $log->phone);

            $response = Http::asForm()
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->timeout((int) config('services.fonnte.timeout', 30))
                ->post($url, [
                    'target' => $target,
                    'message' => (string) $log->message_body,
                    'countryCode' => (string) config('services.fonnte.country_code', '0'),
                    'connectOnly' => filter_var(config('services.fonnte.connect_only', true), FILTER_VALIDATE_BOOLEAN),
                ]);

            $json = $response->json();

            if (! $response->successful()) {
                throw new RuntimeException('Fonnte HTTP Error '.$response->status().': '.$response->body());
            }

            if (! is_array($json)) {
                throw new RuntimeException('Response Fonnte tidak valid: '.$response->body());
            }

            if (($json['status'] ?? false) !== true) {
                $message = $json['reason']
                    ?? $json['message']
                    ?? $json['detail']
                    ?? $json['error']
                    ?? $response->body()
                    ?? 'Gagal mengirim pesan via Fonnte.';

                throw new RuntimeException((string) $message);
            }

            $providerMessageId = $json['id']
                ?? data_get($json, 'data.id')
                ?? $json['requestid']
                ?? null;

            if (is_array($providerMessageId)) {
                $providerMessageId = implode(',', array_filter(array_map('strval', $providerMessageId)));
            }

            $log->markAsSent($providerMessageId ? (string) $providerMessageId : null);
        } catch (Throwable $throwable) {
            $log->markAsFailed($throwable->getMessage());
        }

        return $log->refresh();
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', trim($phone)) ?? '';

        if ($phone === '') {
            throw new RuntimeException('Nomor WhatsApp kosong.');
        }

        if (Str::startsWith($phone, '+')) {
            $phone = substr($phone, 1);
        }

        $digits = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if ($digits === '') {
            throw new RuntimeException('Nomor WhatsApp tidak valid.');
        }

        if (Str::startsWith($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (Str::startsWith($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    private function setProvider(WhatsappLog $log): void
    {
        if ($log->provider !== self::PROVIDER) {
            $log->forceFill([
                'provider' => self::PROVIDER,
            ])->save();
        }
    }
}
