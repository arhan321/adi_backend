#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

echo "=== BACKUP .env dan config ==="
BACKUP_DIR="storage/backups/clean_fix_fonnte_env_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp .env "$BACKUP_DIR/.env.backup"
cp config/services.php "$BACKUP_DIR/services.php.backup"

echo "=== AMBIL TOKEN LAMA ==="
TOKEN="$(grep -m1 '^FONNTE_TOKEN=' .env | cut -d= -f2- || true)"

if [ -z "$TOKEN" ]; then
    echo "ERROR: FONNTE_TOKEN tidak ketemu di .env. Isi manual nanti."
fi

echo "=== BERSIHKAN ENV YANG RUSAK ==="
python3 - <<'PY'
from pathlib import Path

path = Path(".env")
lines = path.read_text().splitlines()

remove_prefixes = (
    "FONNTE_ENABLED=",
    "FONNTE_WHATSAPP_ENABLED=",
    "FONNTE_TOKEN=",
    "FONNTE_URL=",
    "FONNTE_COUNTRY_CODE=",
    "FONNTE_CONNECT_ONLY=",
    "FONNTE_TIMEOUT=",
    "QUEUE_CONNECTION=",
)

clean = []
for line in lines:
    if line.startswith(remove_prefixes):
        continue
    clean.append(line)

path.write_text("\n".join(clean).rstrip() + "\n")
PY

cat >> .env <<ENV

FONNTE_ENABLED=true
FONNTE_WHATSAPP_ENABLED=true
FONNTE_TOKEN=${TOKEN}
FONNTE_URL=https://api.fonnte.com/send
FONNTE_COUNTRY_CODE=0
FONNTE_CONNECT_ONLY=true
FONNTE_TIMEOUT=30
QUEUE_CONNECTION=sync
ENV

echo "=== REWRITE CONFIG SERVICES FONNTE ==="
python3 - <<'PY'
from pathlib import Path
import re

path = Path("config/services.php")
content = path.read_text()

block = """'fonnte' => [
        'enabled' => env('FONNTE_ENABLED', false),
        'whatsapp_enabled' => env('FONNTE_WHATSAPP_ENABLED', env('FONNTE_ENABLED', false)),
        'token' => env('FONNTE_TOKEN'),
        'url' => env('FONNTE_URL', 'https://api.fonnte.com/send'),
        'country_code' => env('FONNTE_COUNTRY_CODE', '0'),
        'connect_only' => env('FONNTE_CONNECT_ONLY', true),
        'timeout' => env('FONNTE_TIMEOUT', 30),
    ],"""

pattern = r"""['"]fonnte['"]\s*=>\s*\[[\s\S]*?\n\s*\],"""

if re.search(pattern, content):
    content = re.sub(pattern, block, content, count=1)
else:
    content = re.sub(r"\n\];\s*$", "\n    " + block + "\n\n];\n", content)

path.write_text(content)
PY

echo "=== REWRITE SERVICE FONNTE FINAL ==="
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
PHP

echo "=== CLEAR CACHE ==="
rm -f bootstrap/cache/*.php
composer dump-autoload
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "=== CEK CONFIG HARUS BENER ==="
php artisan tinker --execute="dump(config('services.fonnte')); dump(config('queue.default'));"

echo "=== TEST KIRIM LANGSUNG KE FONNTE VIA HTTP ==="
php artisan tinker --execute="
\$token = config('services.fonnte.token');
\$url = config('services.fonnte.url');

\$response = \Illuminate\Support\Facades\Http::asForm()
    ->withHeaders(['Authorization' => \$token])
    ->post(\$url, [
        'target' => '6282112125639',
        'message' => 'Test langsung dari Laravel ke Fonnte setelah fix env.',
        'countryCode' => '0',
        'connectOnly' => true,
    ]);

dump([
    'http_status' => \$response->status(),
    'body' => \$response->body(),
    'json' => \$response->json(),
]);
"

echo "=== TEST ULANG WHATSAPP LOG TERAKHIR ==="
php artisan tinker --execute="
\$log = \App\Models\WhatsappLog::query()
    ->where('provider', 'fonnte')
    ->latest()
    ->first();

if (! \$log) {
    dump('Belum ada log fonnte.');
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

echo "=== SELESAI ==="
echo "Backup: $BACKUP_DIR"
