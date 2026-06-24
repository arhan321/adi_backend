#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

echo "=== BACKUP FILE ==="
BACKUP_DIR="storage/backups/fix_fonnte_autosend_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

backup_file() {
    if [ -f "$1" ]; then
        mkdir -p "$BACKUP_DIR/$(dirname "$1")"
        cp "$1" "$BACKUP_DIR/$1"
        echo "backup: $1"
    fi
}

backup_file .env
backup_file app/Services/Whatsapp/FonnteWhatsappService.php
backup_file config/services.php

echo "=== UPDATE ENV FONNTE + QUEUE ==="

set_env() {
    KEY="$1"
    VALUE="$2"

    if grep -q "^${KEY}=" .env; then
        sed -i "s|^${KEY}=.*|${KEY}=${VALUE}|g" .env
    else
        echo "${KEY}=${VALUE}" >> .env
    fi
}

set_env "FONNTE_ENABLED" "true"
set_env "FONNTE_WHATSAPP_ENABLED" "true"
set_env "FONNTE_URL" "https://api.fonnte.com/send"
set_env "FONNTE_COUNTRY_CODE" "0"
set_env "FONNTE_CONNECT_ONLY" "true"
set_env "FONNTE_TIMEOUT" "30"

# Supaya pesan WA langsung dieksekusi saat klik Simpan Poin/Redeem,
# tidak nunggu queue worker.
set_env "QUEUE_CONNECTION" "sync"

echo "=== UPDATE CONFIG SERVICES FONNTE ==="

php -r '
$path = "config/services.php";
$content = file_get_contents($path);

$newBlock = <<<PHP
    "fonnte" => [
        "enabled" => env("FONNTE_WHATSAPP_ENABLED", env("FONNTE_ENABLED", false)),
        "token" => env("FONNTE_TOKEN"),
        "url" => env("FONNTE_URL", "https://api.fonnte.com/send"),
        "country_code" => env("FONNTE_COUNTRY_CODE", "0"),
        "connect_only" => env("FONNTE_CONNECT_ONLY", true),
        "timeout" => env("FONNTE_TIMEOUT", 30),
    ],
PHP;

if (preg_match("/[\"\\x27]fonnte[\"\\x27]\\s*=>\\s*\\[[\\s\\S]*?\\n\\s*\\],/m", $content)) {
    $content = preg_replace("/[\"\\x27]fonnte[\"\\x27]\\s*=>\\s*\\[[\\s\\S]*?\\n\\s*\\],/m", $newBlock, $content, 1);
} else {
    $content = preg_replace("/\\n\\];\\s*$/", "\n".$newBlock."\n\n];\n", $content, 1);
}

file_put_contents($path, $content);
'

echo "=== REWRITE FonnteWhatsappService BIAR SUPPORT ENV LAMA + BARU ==="

mkdir -p app/Services/Whatsapp

cat > app/Services/Whatsapp/FonnteWhatsappService.php <<'PHP'
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
            config('services.fonnte.enabled', env('FONNTE_WHATSAPP_ENABLED', env('FONNTE_ENABLED', false))),
            FILTER_VALIDATE_BOOLEAN
        );

        if (! $enabled) {
            $log->markAsFailed('Fonnte WhatsApp belum aktif. Set FONNTE_WHATSAPP_ENABLED=true atau FONNTE_ENABLED=true di file .env.');

            return $log->refresh();
        }

        $token = trim((string) config('services.fonnte.token', env('FONNTE_TOKEN')));
        $url = trim((string) config('services.fonnte.url', env('FONNTE_URL', 'https://api.fonnte.com/send')));

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
                ->timeout((int) config('services.fonnte.timeout', env('FONNTE_TIMEOUT', 30)))
                ->post($url, [
                    'target' => $target,
                    'message' => (string) $log->message_body,

                    // Karena normalizePhone() sudah menghasilkan format 628xxxx,
                    // countryCode dibuat 0 supaya tidak dobel jadi 62628xxxx.
                    'countryCode' => (string) config('services.fonnte.country_code', env('FONNTE_COUNTRY_CODE', '0')),

                    'connectOnly' => filter_var(
                        config('services.fonnte.connect_only', env('FONNTE_CONNECT_ONLY', true)),
                        FILTER_VALIDATE_BOOLEAN
                    ),
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
PHP

echo "=== CEK SYNTAX ==="
php -l app/Services/Whatsapp/FonnteWhatsappService.php
php -l config/services.php

echo "=== CLEAR CACHE ==="
rm -f bootstrap/cache/*.php
composer dump-autoload
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan queue:restart || true

echo "=== CEK CONFIG TERBARU ==="
php artisan tinker --execute="dump(config('services.fonnte')); dump(config('queue.default'));"

echo "=== TEST KIRIM ULANG LOG WHATSAPP TERAKHIR YANG FAILED ==="
php artisan tinker --execute="
\$log = \App\Models\WhatsappLog::query()
    ->where('provider', 'fonnte')
    ->latest()
    ->first();

if (! \$log) {
    dump('Belum ada whatsapp log.');
    return;
}

\$log->forceFill([
    'status' => \App\Models\WhatsappLog::STATUS_PENDING,
    'provider_message_id' => null,
    'error_message' => null,
    'sent_at' => null,
    'failed_at' => null,
])->save();

app(\App\Services\Whatsapp\FonnteWhatsappService::class)->send(\$log);

dump(\$log->refresh()->only([
    'id',
    'phone',
    'message_type',
    'provider',
    'status',
    'provider_message_id',
    'error_message',
    'sent_at',
    'failed_at',
]));
"

echo ""
echo "=== SELESAI ==="
echo "Backup ada di: $BACKUP_DIR"
