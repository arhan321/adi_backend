# README — CRM Kopi Banget Laravel Filament + Twilio WhatsApp Gateway

Dokumentasi ini berisi panduan lengkap untuk membangun, mengkonfigurasi, menjalankan, dan mengetes fitur **CRM Kopi Banget** pada project Laravel Filament.

Sistem ini dibuat untuk kebutuhan CRM kasir/admin, yaitu:

- menambahkan member baru,
- mencari member berdasarkan nomor WhatsApp,
- menambahkan poin pembelian,
- melakukan redeem reward,
- melihat riwayat aktivitas,
- mengatur master promo,
- mengatur automated retention,
- mengirim WhatsApp otomatis menggunakan Twilio WhatsApp Gateway,
- menjalankan retention otomatis menggunakan Laravel Scheduler.

---

## 1. Gambaran Sistem

CRM Kopi Banget adalah modul CRM sederhana berbasis **Laravel Filament** yang terhubung dengan **Twilio WhatsApp Gateway**.

Alur utama:

```text
Admin/Kasir login ke Filament
↓
Admin/Kasir membuka menu Kopi Banget CRM
↓
Admin/Kasir menambahkan member baru memakai nomor WhatsApp
↓
Admin/Kasir mencari member dari dashboard CRM
↓
Admin/Kasir menambahkan poin pembelian
↓
Sistem menyimpan transaksi poin
↓
Sistem mencatat log WhatsApp
↓
Sistem mengirim pesan WhatsApp melalui Twilio
↓
Member menerima notifikasi WhatsApp
```

---

## 2. Teknologi yang Digunakan

| Kebutuhan | Teknologi |
|---|---|
| Backend | Laravel |
| Admin panel | Laravel Filament |
| UI | Blade + Livewire + CSS inline |
| Database | MySQL / MariaDB |
| WhatsApp Gateway | Twilio WhatsApp Sandbox |
| Queue testing | `sync` |
| Scheduler | Laravel Scheduler + Cron Host |
| Container | Docker Compose |

---

## 3. Fitur CRM

### 3.1 Dashboard CRM

Fungsi halaman Dashboard CRM:

- melihat statistik member,
- mencari member menggunakan nomor WhatsApp,
- melihat detail member,
- menambahkan poin pembelian,
- melihat progress redeem,
- melakukan redeem reward,
- melihat aktivitas terbaru,
- mengirim WhatsApp otomatis saat poin/redeem.

Path file:

```text
app/Filament/Admin/Pages/CrmDashboard.php
resources/views/filament/admin/pages/crm-dashboard.blade.php
```

---

### 3.2 Tambah Member

Fungsi halaman Tambah Member:

- input nama member,
- input nomor WhatsApp,
- input tanggal lahir opsional,
- input catatan member opsional,
- menyimpan member ke database,
- redirect ke dashboard CRM setelah berhasil.

Path file:

```text
app/Filament/Admin/Pages/CrmAddMember.php
resources/views/filament/admin/pages/crm-add-member.blade.php
```

---

### 3.3 History CRM

Fungsi halaman History:

- melihat riwayat tambah poin,
- melihat riwayat redeem reward,
- filter berdasarkan keyword,
- filter tanggal mulai dan tanggal akhir,
- export CSV,
- melihat total aktivitas,
- melihat total poin masuk,
- melihat total poin redeem.

Path file:

```text
app/Filament/Admin/Pages/CrmHistory.php
resources/views/filament/admin/pages/crm-history.blade.php
```

---

### 3.4 Settings CRM

Fungsi halaman Settings:

- mengatur syarat minimum poin redeem,
- mengatur nama reward,
- mengaktifkan/nonaktifkan promo,
- mengatur durasi retention,
- mengatur jam kirim retention,
- mengaktifkan/nonaktifkan auto-send WhatsApp,
- mengatur template pesan WhatsApp.

Path file:

```text
app/Filament/Admin/Pages/CrmSettingsPage.php
resources/views/filament/admin/pages/crm-settings-page.blade.php
```

---

## 4. Struktur File Utama

```text
app/
├── Console/
│   └── Commands/
│       └── Crm/
│           └── SendRetentionMessages.php
│
├── Filament/
│   └── Admin/
│       └── Pages/
│           ├── CrmDashboard.php
│           ├── CrmAddMember.php
│           ├── CrmHistory.php
│           └── CrmSettingsPage.php
│
├── Http/
│   └── Controllers/
│       └── Crm/
│           ├── MemberController.php
│           ├── PointController.php
│           ├── CrmSettingController.php
│           └── WhatsappWebhookController.php
│
├── Jobs/
│   └── Crm/
│       └── SendWhatsappMessageJob.php
│
├── Models/
│   ├── Member.php
│   ├── PointTransaction.php
│   ├── CrmSetting.php
│   ├── WhatsappLog.php
│   └── RetentionLog.php
│
└── Services/
    ├── Crm/
    │   ├── MemberPointService.php
    │   └── WhatsappMessageBuilder.php
    │
    └── Whatsapp/
        └── TwilioWhatsappService.php
```

View:

```text
resources/views/filament/admin/pages/
├── crm-dashboard.blade.php
├── crm-add-member.blade.php
├── crm-history.blade.php
└── crm-settings-page.blade.php
```

Route:

```text
routes/crm.php
routes/console.php
```

Config:

```text
config/crm.php
```

---

## 5. Database

### 5.1 Tabel `members`

Menyimpan data member/pelanggan.

Kolom penting:

```text
id
member_code
name
phone
birth_date
total_points
last_visit_at
status
notes
created_by
created_at
updated_at
```

Nomor WhatsApp disarankan memakai format internasional:

```text
+6281287968048
```

---

### 5.2 Tabel `point_transactions`

Menyimpan riwayat perubahan poin.

Kolom penting:

```text
id
member_id
user_id
type
activity_name
points_change
points_before
points_after
transaction_at
notes
created_at
updated_at
```

Contoh type:

```text
earn
redeem
adjustment
```

---

### 5.3 Tabel `crm_settings`

Menyimpan konfigurasi CRM.

Kolom penting:

```text
id
redeem_required_points
reward_name
promo_is_active
retention_days
retention_send_time
auto_send_whatsapp
point_message_template
redeem_message_template
retention_message_template
updated_by
created_at
updated_at
```

---

### 5.4 Tabel `whatsapp_logs`

Menyimpan log pengiriman WhatsApp.

Kolom penting:

```text
id
member_id
phone
message_type
message_body
provider
provider_message_id
status
error_message
sent_at
failed_at
created_at
updated_at
```

Contoh status:

```text
pending
sent
failed
delivered
```

Contoh message type:

```text
manual
point
redeem
retention
```

---

### 5.5 Tabel `retention_logs`

Menyimpan riwayat pengiriman pesan retention.

Kolom penting:

```text
id
member_id
whatsapp_log_id
retention_date
status
created_at
updated_at
```

---

## 6. Instalasi Project Laravel

Masuk ke container PHP:

```bash
sudo docker exec -it php_adi bash
```

Masuk ke folder Laravel:

```bash
cd /var/www/html
```

Install dependency:

```bash
composer install
```

Install Twilio SDK:

```bash
composer require twilio/sdk
```

Jalankan migration:

```bash
php artisan migrate
```

Clear cache:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 7. Link Penting Twilio

Gunakan link berikut untuk setup Twilio:

| Kebutuhan | Link |
|---|---|
| Daftar akun Twilio | https://www.twilio.com/try-twilio |
| Login Twilio Console | https://console.twilio.com |
| Dokumentasi WhatsApp Sandbox | https://www.twilio.com/docs/whatsapp/sandbox |
| WhatsApp Quickstart Twilio | https://www.twilio.com/docs/whatsapp/quickstart |
| Twilio Free Trial | https://www.twilio.com/docs/usage/tutorials/how-to-use-your-free-trial-account |
| Twilio Error 63015 | https://www.twilio.com/docs/api/errors/63015 |
| Twilio Error 20003 / Authenticate | https://www.twilio.com/docs/api/errors/20003 |
| Twilio API Keys | https://www.twilio.com/docs/iam/api-keys |

---

## 8. Cara Membuat Akun Twilio

### 8.1 Daftar Twilio

Buka:

```text
https://www.twilio.com/try-twilio
```

Lakukan pendaftaran akun.

Twilio trial dapat dipakai untuk mencoba fitur Twilio sebelum upgrade akun.

---

### 8.2 Ambil Account SID dan Auth Token

Setelah login Twilio, masuk ke:

```text
Twilio Console
→ Account Dashboard
→ Account Info
```

Ambil:

```text
Account SID
Auth Token
```

Contoh format:

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Catatan penting:

- `Account SID` biasanya diawali `AC`.
- `Auth Token` harus cocok dengan Account SID.
- Jangan ambil Auth Token dari akun/subaccount yang berbeda.
- Jangan screenshot atau push token ke GitHub.
- Jika token pernah terlihat publik, segera regenerate token.

---

### 8.3 Setup WhatsApp Sandbox

Masuk ke:

```text
Twilio Console
→ Messaging
→ Try it out
→ Send a WhatsApp message
```

Pada halaman itu akan muncul:

```text
Sandbox number
Sandbox join code
```

Contoh dari project ini:

```text
Sandbox number:
+14155238886

Join code:
join behavior-additional
```

Agar nomor penerima bisa menerima pesan WhatsApp dari Twilio Sandbox, nomor tersebut harus join dulu.

Dari WhatsApp nomor penerima, kirim pesan:

```text
join behavior-additional
```

ke:

```text
+14155238886
```

Jika berhasil, Twilio akan membalas konfirmasi bahwa nomor sudah bergabung ke sandbox.

Catatan:

- Selama masih menggunakan Sandbox, hanya nomor yang sudah join sandbox yang bisa menerima pesan.
- Jika ingin mengirim ke nomor pelanggan bebas tanpa join sandbox, harus upgrade ke WhatsApp sender production.

---

## 9. Konfigurasi `.env`

Tambahkan konfigurasi ini di file `.env` Laravel:

```env
# ==============================
# CRM KOPI BANGET
# ==============================
CRM_BUSINESS_NAME="Kopi Banget"
CRM_RETENTION_SEND_TIME="07:00"
CRM_RETENTION_CHUNK_SIZE=100

# ==============================
# TWILIO WHATSAPP SANDBOX
# ==============================
TWILIO_WHATSAPP_ENABLED=true
TWILIO_ACCOUNT_SID=ISI_ACCOUNT_SID_DARI_TWILIO
TWILIO_AUTH_TOKEN=ISI_AUTH_TOKEN_DARI_TWILIO
TWILIO_WHATSAPP_FROM=+14155238886
TWILIO_STATUS_CALLBACK_URL=

# ==============================
# QUEUE UNTUK TESTING
# ==============================
QUEUE_CONNECTION=sync
```

Setelah mengubah `.env`, selalu jalankan:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

---

## 10. Test Koneksi Twilio dari Laravel

Masuk tinker:

```bash
php artisan tinker
```

Cek apakah `.env` terbaca:

```php
[
    'sid_prefix' => substr((string) env('TWILIO_ACCOUNT_SID'), 0, 4),
    'sid_length' => strlen((string) env('TWILIO_ACCOUNT_SID')),
    'token_length' => strlen((string) env('TWILIO_AUTH_TOKEN')),
    'from' => env('TWILIO_WHATSAPP_FROM'),
    'enabled' => env('TWILIO_WHATSAPP_ENABLED'),
];
```

Hasil normal:

```text
sid_prefix   => ACxx
sid_length   => 34
token_length => 32
from         => +14155238886
enabled      => true
```

Test autentikasi Twilio:

```php
$sid = trim(env('TWILIO_ACCOUNT_SID'));
$token = trim(env('TWILIO_AUTH_TOKEN'));

$twilio = new \Twilio\Rest\Client($sid, $token);

$twilio->api->accounts($sid)->fetch()->friendlyName;
```

Jika berhasil, output contoh:

```text
"My first Twilio account"
```

Jika gagal:

```text
[HTTP 401] Unable to fetch record: Authenticate
```

Solusi:

1. Copy ulang Account SID.
2. Copy ulang Auth Token.
3. Pastikan keduanya dari akun Twilio yang sama.
4. Regenerate Auth Token jika perlu.
5. Update `.env`.
6. Clear config Laravel.

---

## 11. Test Kirim WhatsApp dari Tinker

Pastikan nomor tujuan sudah join sandbox.

Contoh nomor tujuan:

```text
+6281287968048
```

Jalankan:

```php
$phone = '+6281287968048';

$log = \App\Models\WhatsappLog::create([
    'member_id' => null,
    'phone' => $phone,
    'message_type' => 'manual',
    'message_body' => 'Halo, ini pesan test dari CRM Kopi Banget menggunakan Twilio WhatsApp Gateway.',
    'provider' => 'twilio',
    'status' => 'pending',
]);

app(\App\Services\Whatsapp\TwilioWhatsappService::class)->send($log);

$log->refresh()->toArray();
```

Jika berhasil di Laravel, hasilnya:

```text
status              => sent
provider_message_id => SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
error_message       => null
```

Cek status real dari Twilio:

```php
$sid = trim(env('TWILIO_ACCOUNT_SID'));
$token = trim(env('TWILIO_AUTH_TOKEN'));

$twilio = new \Twilio\Rest\Client($sid, $token);

$messageSid = $log->provider_message_id;

$message = $twilio->messages($messageSid)->fetch();

[
    'sid' => $message->sid,
    'status' => $message->status,
    'to' => $message->to,
    'from' => $message->from,
    'error_code' => $message->errorCode,
    'error_message' => $message->errorMessage,
];
```

Target sukses:

```text
status      => delivered
error_code  => null
```

Jika muncul:

```text
error_code => 63015
```

Artinya nomor penerima belum join Twilio Sandbox.

---

## 12. Cara Test dari Halaman Admin CRM

Bagian ini menjelaskan alur test ketika website sudah bisa dibuka dan admin sudah login.

Contoh URL admin:

```text
https://domain-anda.com/admin
```

Atau saat development:

```text
http://100.100.55.22:38/admin
```

---

### 12.1 Login Admin

1. Buka halaman admin:

```text
/admin
```

2. Login menggunakan akun admin/kasir.
3. Pastikan sidebar menampilkan menu:

```text
Kopi Banget CRM
├── Dashboard CRM
├── Tambah Member
├── History
└── Settings CRM
```

Jika menu belum muncul:

- pastikan user punya role/permission yang sesuai,
- login sebagai super admin,
- clear cache Laravel.

Command:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

---

### 12.2 Test Halaman Tambah Member

Buka:

```text
/admin/crm-add-member
```

Isi form:

```text
Nama Lengkap:
Test Member WA

Nomor WhatsApp:
+6281287968048

Tanggal Lahir:
boleh kosong

Catatan:
Test pengiriman WhatsApp Twilio dari web CRM
```

Klik:

```text
Simpan Member
```

Hasil yang diharapkan:

```text
✅ Member berhasil tersimpan
✅ Total member bertambah
✅ Redirect ke Dashboard CRM atau muncul notifikasi sukses
```

Jika muncul error nomor sudah terdaftar, lanjut saja ke Dashboard CRM dan cari nomor tersebut.

---

### 12.3 Test Halaman Dashboard CRM

Buka:

```text
/admin/crm-dashboard
```

Pada kolom pencarian, masukkan:

```text
+6281287968048
```

Klik:

```text
Cari Member
```

Hasil yang diharapkan:

```text
✅ Profil member tampil
✅ Total poin tampil
✅ Progress redeem tampil
✅ Tombol tambah poin aktif
```

Tambah poin:

```text
Tambah Poin:
1

Aktivitas:
Pembelian Kopi Susu
```

Klik:

```text
Simpan Poin
```

Hasil yang diharapkan:

```text
✅ Total poin member bertambah
✅ Data masuk ke point_transactions
✅ Data masuk ke whatsapp_logs
✅ WhatsApp masuk ke nomor member
```

---

### 12.4 Cek WhatsApp Masuk

Jika pesan WhatsApp berhasil, nomor member akan menerima pesan dari Twilio Sandbox.

Jika pesan belum masuk, cek:

1. Nomor sudah join sandbox atau belum.
2. `whatsapp_logs` statusnya apa.
3. Status real Twilio dari `provider_message_id`.

Cek log terbaru:

```bash
php artisan tinker
```

```php
\App\Models\WhatsappLog::latest()->first()->toArray();
```

Jika hasilnya:

```text
status        => sent
error_message => null
```

Laravel sudah berhasil mengirim request ke Twilio.

Jika ingin cek status delivery Twilio:

```php
$sid = trim(env('TWILIO_ACCOUNT_SID'));
$token = trim(env('TWILIO_AUTH_TOKEN'));

$twilio = new \Twilio\Rest\Client($sid, $token);

$messageSid = \App\Models\WhatsappLog::latest()->first()->provider_message_id;

$message = $twilio->messages($messageSid)->fetch();

[
    'status' => $message->status,
    'to' => $message->to,
    'from' => $message->from,
    'error_code' => $message->errorCode,
    'error_message' => $message->errorMessage,
];
```

Jika `error_code` adalah `63015`, nomor belum join sandbox.

---

### 12.5 Test Redeem Reward

Jika aturan promo:

```text
3 Poin = 1 Kopi Gratis
```

Maka tambahkan poin sampai member punya minimal 3 poin.

Contoh:

```text
Tambah poin 1
Tambah poin 1
Tambah poin 1
```

Setelah poin cukup, klik:

```text
Redeem Reward
```

Hasil yang diharapkan:

```text
✅ Poin member berkurang sesuai aturan redeem
✅ Data redeem masuk ke point_transactions
✅ Data WhatsApp redeem masuk ke whatsapp_logs
✅ Member menerima pesan WhatsApp redeem
```

---

### 12.6 Test Halaman History

Buka:

```text
/admin/crm-history
```

Cek apakah muncul aktivitas:

```text
Pembelian Kopi Susu
Tambah poin +1
Redeem reward
```

Coba filter:

```text
Nama member
Nomor WhatsApp
Aktivitas
Tanggal mulai
Tanggal akhir
```

Test export:

```text
Klik Export CSV
```

Hasil yang diharapkan:

```text
✅ Data riwayat tampil
✅ Filter berjalan
✅ Export CSV berhasil
```

---

### 12.7 Test Halaman Settings CRM

Buka:

```text
/admin/crm-settings-page
```

Coba ubah:

```text
Syarat Redeem Poin:
3

Nama Reward:
1 Kopi Gratis

Status Promo:
Aktif

Durasi Pengingat:
14 Hari

Jam Kirim Retensi:
07:00

Auto-Send WhatsApp:
Aktif
```

Klik:

```text
Simpan Perubahan
```

Hasil yang diharapkan:

```text
✅ Settings berhasil disimpan
✅ Dashboard CRM menampilkan aturan baru
✅ Redeem mengikuti syarat poin terbaru
✅ Retention mengikuti durasi dan jam terbaru
```

---

## 13. Alur Test Lengkap dari Awal sampai Akhir

Gunakan alur ini untuk demo:

```text
1. Login ke /admin
2. Buka menu Kopi Banget CRM
3. Buka Tambah Member
4. Input nama dan nomor WhatsApp yang sudah join sandbox
5. Klik Simpan Member
6. Buka Dashboard CRM
7. Cari nomor member
8. Tambah poin 1 dengan aktivitas “Pembelian Kopi Susu”
9. Cek WhatsApp masuk
10. Buka History
11. Pastikan transaksi poin muncul
12. Tambah poin sampai total memenuhi syarat redeem
13. Klik Redeem Reward
14. Cek WhatsApp redeem masuk
15. Buka Settings CRM
16. Tunjukkan syarat redeem, retensi, dan template pesan
17. Jalankan test retention dry-run
18. Jelaskan scheduler otomatis via cron host
```

---

## 14. Scheduler Laravel di Docker Compose

Laravel Scheduler tidak berjalan otomatis hanya karena schedule ditulis di `routes/console.php`.

Harus ada proses yang menjalankan:

```bash
php artisan schedule:run
```

setiap menit.

Untuk project ini digunakan cron host:

```cron
* * * * * cd /home/backend/adi_coding && docker exec php_adi php /var/www/html/artisan schedule:run >> /tmp/adi_crm_scheduler.log 2>&1
```

Artinya:

```text
Setiap menit
↓
Host masuk ke folder project
↓
Host menjalankan command di container php_adi
↓
Laravel schedule:run berjalan
↓
Laravel mengecek task mana yang waktunya sudah tiba
↓
Command retention dijalankan sesuai jadwal
```

Pastikan nama container benar:

```bash
docker ps
```

Pastikan schedule terdaftar:

```bash
docker exec php_adi php /var/www/html/artisan schedule:list
```

Test manual:

```bash
docker exec php_adi php /var/www/html/artisan schedule:run
```

Cek log scheduler:

```bash
tail -f /tmp/adi_crm_scheduler.log
```

---

## 15. Schedule Retention

Pastikan di `routes/console.php` ada:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('crm:send-retention-whatsapp')
    ->dailyAt(env('CRM_RETENTION_SEND_TIME', '07:00'));
```

Test dry-run:

```bash
docker exec -it php_adi php /var/www/html/artisan crm:send-retention-whatsapp --dry-run
```

Test asli:

```bash
docker exec -it php_adi php /var/www/html/artisan crm:send-retention-whatsapp
```

Jika `.env` memakai:

```env
QUEUE_CONNECTION=sync
```

maka WhatsApp langsung dikirim tanpa queue worker.

Jika nanti production memakai:

```env
QUEUE_CONNECTION=database
```

jalankan worker:

```bash
php artisan queue:work
```

atau pasang supervisor.

---

## 16. Troubleshooting

### 16.1 Error `HTTP 401 Authenticate`

Penyebab:

- Account SID salah.
- Auth Token salah.
- Auth Token tidak cocok dengan SID.
- Token sudah regenerate tapi `.env` masih token lama.
- Config Laravel belum clear.

Solusi:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

Test:

```php
$sid = trim(env('TWILIO_ACCOUNT_SID'));
$token = trim(env('TWILIO_AUTH_TOKEN'));

$twilio = new \Twilio\Rest\Client($sid, $token);

$twilio->api->accounts($sid)->fetch()->friendlyName;
```

Jika masih gagal, regenerate Auth Token.

---

### 16.2 Error `63015`

Penyebab:

Nomor tujuan belum join Twilio Sandbox.

Solusi:

Dari WhatsApp nomor tujuan, kirim:

```text
join behavior-additional
```

ke:

```text
+14155238886
```

Setelah Twilio membalas sukses, test ulang.

---

### 16.3 Pesan Status `sent` Tapi Belum Masuk WhatsApp

Penyebab:

- Delivery masih diproses.
- Nomor belum join sandbox.
- Ada error delivery Twilio.

Solusi:

Cek status Twilio dari Message SID:

```php
$message = $twilio->messages($messageSid)->fetch();

[
    'status' => $message->status,
    'error_code' => $message->errorCode,
    'error_message' => $message->errorMessage,
];
```

Target sukses:

```text
status => delivered
```

---

### 16.4 Error `Data truncated for column message_type`

Penyebab:

Kolom `message_type` memakai enum dan value tidak tersedia.

Contoh salah:

```php
'message_type' => 'manual_test'
```

Gunakan:

```php
'message_type' => 'manual'
```

---

### 16.5 Error Tinker `Cannot use Twilio\Rest\Client as Client because the name is already in use`

Penyebab:

`use Twilio\Rest\Client;` dipanggil berulang di session tinker.

Solusi:

Keluar tinker:

```php
exit
```

Masuk lagi:

```bash
php artisan tinker
```

Atau gunakan full namespace:

```php
$twilio = new \Twilio\Rest\Client($sid, $token);
```

---

### 16.6 GitHub Menolak Push Karena Secret

Contoh:

```text
GITHUB PUSH PROTECTION
Push cannot contain secrets
Twilio Account String Identifier
```

Penyebab:

`.env` atau credential Twilio masuk commit.

Solusi:

Tambahkan ke `.gitignore`:

```gitignore
.env
.env.*
src/.env
src/.env.*
!.env.example
!src/.env.example
```

Keluarkan dari tracking Git:

```bash
git rm --cached -f .env 2>/dev/null || true
git rm --cached -f src/.env 2>/dev/null || true
```

Buat `.env.example` tanpa credential asli:

```env
TWILIO_WHATSAPP_ENABLED=false
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_WHATSAPP_FROM=+14155238886
TWILIO_STATUS_CALLBACK_URL=
```

Jika token pernah ter-push atau terlihat di screenshot, regenerate Auth Token.

---

### 16.7 File Database Besar Ikut Git

Contoh warning:

```text
database/data/ibdata1 is larger than GitHub's recommended maximum file size
```

Solusi:

Tambahkan ke `.gitignore`:

```gitignore
database/data/
db/data/
**/database/data/
**/db/data/

*.ibd
ibdata1
ib_logfile*
aria_log*
mysql.sock
```

Keluarkan dari tracking:

```bash
git rm --cached -r database/data 2>/dev/null || true
git rm --cached -r db/data 2>/dev/null || true
```

---

## 17. Keamanan

Jangan push file berikut ke GitHub:

```text
.env
database/data/
db/data/
```

Jangan pernah membagikan:

```text
TWILIO_AUTH_TOKEN
DB_PASSWORD
APP_KEY
```

Jika token sudah terlihat publik:

1. buka Twilio Console,
2. regenerate Auth Token,
3. update `.env`,
4. clear config Laravel,
5. test ulang koneksi Twilio.

---

## 18. Checklist Sebelum Demo

### Laravel

- [ ] Website bisa dibuka.
- [ ] Login admin berhasil.
- [ ] Menu Kopi Banget CRM muncul.
- [ ] Migration sudah jalan.
- [ ] `.env` benar.
- [ ] Config sudah clear.

### Twilio

- [ ] Akun Twilio aktif.
- [ ] Account SID benar.
- [ ] Auth Token benar.
- [ ] Test `friendlyName` berhasil.
- [ ] Nomor penerima join sandbox.
- [ ] Test tinker berhasil.
- [ ] WhatsApp masuk.

### CRM Web

- [ ] Tambah member berhasil.
- [ ] Cari member berhasil.
- [ ] Tambah poin berhasil.
- [ ] WhatsApp tambah poin masuk.
- [ ] Redeem berhasil.
- [ ] WhatsApp redeem masuk.
- [ ] History tampil.
- [ ] Settings bisa disimpan.
- [ ] Export CSV berhasil.

### Scheduler

- [ ] Cron host sudah dipasang.
- [ ] `schedule:list` menampilkan command retention.
- [ ] `schedule:run` tidak error.
- [ ] Retention dry-run berhasil.
- [ ] Log scheduler bisa dicek.

---

## 19. Perintah Penting

Masuk container:

```bash
sudo docker exec -it php_adi bash
```

Masuk Laravel:

```bash
cd /var/www/html
```

Clear cache:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Tinker:

```bash
php artisan tinker
```

Test Twilio account:

```php
$sid = trim(env('TWILIO_ACCOUNT_SID'));
$token = trim(env('TWILIO_AUTH_TOKEN'));
$twilio = new \Twilio\Rest\Client($sid, $token);
$twilio->api->accounts($sid)->fetch()->friendlyName;
```

Cek WhatsApp log terakhir:

```php
\App\Models\WhatsappLog::latest()->first()->toArray();
```

Cek status delivery Twilio:

```php
$log = \App\Models\WhatsappLog::latest()->first();

$twilio = new \Twilio\Rest\Client(
    trim(env('TWILIO_ACCOUNT_SID')),
    trim(env('TWILIO_AUTH_TOKEN'))
);

$message = $twilio->messages($log->provider_message_id)->fetch();

[
    'status' => $message->status,
    'error_code' => $message->errorCode,
    'error_message' => $message->errorMessage,
];
```

Test scheduler:

```bash
docker exec php_adi php /var/www/html/artisan schedule:run
```

Cek scheduler list:

```bash
docker exec php_adi php /var/www/html/artisan schedule:list
```

Cek scheduler log:

```bash
tail -f /tmp/adi_crm_scheduler.log
```

---

## 20. Alur Singkat untuk Presentasi

Gunakan narasi ini saat demo:

```text
Sistem CRM Kopi Banget digunakan kasir untuk mencatat member berdasarkan nomor WhatsApp.
Saat pelanggan membeli produk, kasir mencari nomor pelanggan dan menambahkan poin.
Setelah poin ditambahkan, sistem menyimpan transaksi, mencatat log, dan mengirim notifikasi WhatsApp melalui Twilio.
Jika poin pelanggan sudah memenuhi syarat, kasir dapat melakukan redeem reward.
Semua aktivitas tersimpan pada halaman History.
Konfigurasi poin, reward, retensi pelanggan, dan template pesan dapat diatur melalui halaman Settings.
Untuk retensi otomatis, Laravel Scheduler dijalankan setiap menit melalui cron host dan akan mengirim pesan sesuai jadwal yang ditentukan.
```

---

## 21. Catatan Production

Untuk production sungguhan:

1. Jangan gunakan Twilio Sandbox.
2. Upgrade akun Twilio.
3. Daftarkan WhatsApp sender resmi.
4. Gunakan approved message template.
5. Jalankan queue worker permanen.
6. Jalankan scheduler permanen.
7. Simpan credential hanya di `.env`.
8. Pantau `whatsapp_logs`.
9. Gunakan HTTPS.
10. Batasi akses admin dengan role/permission.

---

## 22. Kesimpulan

Integrasi CRM Kopi Banget sudah mencakup:

```text
Laravel Filament
↓
CRM Member
↓
Loyalty Point
↓
Redeem Reward
↓
Whatsapp Log
↓
Twilio WhatsApp Gateway
↓
WhatsApp Pelanggan
↓
Laravel Scheduler untuk Retention
```

Dengan dokumentasi ini, proses setup, konfigurasi Twilio, testing via Tinker, testing via halaman admin, troubleshooting, dan scheduler sudah terdokumentasi lengkap.
```
fonte whatsapp gateway
```