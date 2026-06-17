# CRM Kopi Banget — Laravel Filament + Twilio WhatsApp Gateway

Dokumentasi ini menjelaskan proses pembuatan, konfigurasi, pengujian, dan troubleshooting sistem **CRM Kopi Banget** berbasis **Laravel Filament** dengan integrasi **Twilio WhatsApp Gateway**.

Project ini dibuat untuk mendukung kebutuhan CRM sederhana pada UMKM Kopi Banget, yaitu pencatatan member, pengelolaan poin loyalitas, redeem reward, riwayat aktivitas, pengaturan master promo, dan pengiriman pesan WhatsApp otomatis.

---

## 1. Gambaran Umum Sistem

Sistem CRM Kopi Banget adalah aplikasi berbasis website yang digunakan oleh kasir/admin untuk mengelola data pelanggan dan program loyalitas.

Alur utama sistem:

```text
Kasir/Admin login ke Laravel Filament
↓
Kasir mencari atau menambahkan member berdasarkan nomor WhatsApp
↓
Kasir menambahkan poin setelah pelanggan membeli produk
↓
Sistem menyimpan riwayat poin
↓
Sistem mengirim notifikasi WhatsApp melalui Twilio
↓
Jika poin cukup, pelanggan dapat melakukan redeem reward
↓
Sistem mencatat history redeem dan mengirim notifikasi WhatsApp
```

Fokus utama sistem:

1. Member management.
2. Loyalty point sederhana.
3. Redeem reward.
4. History aktivitas.
5. Master promo.
6. Automated retention.
7. WhatsApp Gateway menggunakan Twilio.

---

## 2. Teknologi yang Digunakan

| Bagian | Teknologi |
|---|---|
| Backend | Laravel |
| Admin Panel | Laravel Filament |
| Frontend Admin | Blade + Filament Page + CSS custom |
| Database | MySQL / MariaDB |
| WhatsApp Gateway | Twilio WhatsApp Sandbox |
| Queue Testing | `sync` |
| Scheduler | Laravel Scheduler / Artisan Command |
| Package WhatsApp | `twilio/sdk` |

---

## 3. Fitur Sistem

### 3.1 Login Admin/Kasir

Login mengikuti sistem autentikasi dari template Laravel Filament yang digunakan.

Akses dilakukan melalui panel admin Filament.

Contoh URL:

```text
http://IP-SERVER:PORT/admin
```

---

### 3.2 Dashboard CRM

Dashboard CRM digunakan untuk:

- Mencari member berdasarkan nomor WhatsApp.
- Menampilkan profil member.
- Melihat total poin.
- Menambahkan poin.
- Melakukan redeem reward.
- Mengirim notifikasi WhatsApp setelah update poin/redeem.

File terkait:

```text
app/Filament/Admin/Pages/CrmDashboard.php
resources/views/filament/admin/pages/crm-dashboard.blade.php
```

---

### 3.3 Tambah Member

Fitur tambah member digunakan untuk mendaftarkan pelanggan baru.

Data utama:

- Nama member.
- Nomor WhatsApp.
- Tanggal lahir.
- Catatan tambahan.

File terkait:

```text
app/Filament/Admin/Pages/CrmAddMember.php
resources/views/filament/admin/pages/crm-add-member.blade.php
```

---

### 3.4 History Aktivitas

History digunakan untuk melihat aktivitas poin dan redeem.

Data yang dicatat:

- Tanggal transaksi.
- Nama member.
- Nomor WhatsApp.
- Jenis aktivitas.
- Perubahan poin.
- User/kasir yang melakukan input.

File terkait:

```text
app/Filament/Admin/Pages/CrmHistory.php
resources/views/filament/admin/pages/crm-history.blade.php
```

---

### 3.5 Settings CRM

Settings CRM digunakan untuk mengatur:

- Jumlah poin minimal untuk redeem.
- Nama reward.
- Status promo aktif/nonaktif.
- Durasi retensi.
- Jam pengiriman retensi.
- Auto-send WhatsApp.
- Template pesan WhatsApp.

File terkait:

```text
app/Filament/Admin/Pages/CrmSettingsPage.php
resources/views/filament/admin/pages/crm-settings-page.blade.php
```

---

### 3.6 WhatsApp Gateway

Integrasi WhatsApp dilakukan menggunakan Twilio.

Fungsi utama:

- Kirim pesan setelah member mendapatkan poin.
- Kirim pesan setelah redeem berhasil.
- Kirim pesan retensi ke pelanggan pasif.
- Menyimpan log pengiriman WhatsApp.

File terkait:

```text
app/Services/Whatsapp/TwilioWhatsappService.php
app/Services/Crm/WhatsappMessageBuilder.php
app/Jobs/Crm/SendWhatsappMessageJob.php
app/Http/Controllers/Crm/WhatsappWebhookController.php
```

---

## 4. Struktur File CRM

Struktur file utama yang ditambahkan:

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

CSS:

```text
public/css/crm-kopi-banget.css
```

Config:

```text
config/crm.php
```

Route tambahan:

```text
routes/crm.php
routes/console.php
```

---

## 5. Database

### 5.1 Tabel `members`

Tabel ini menyimpan data pelanggan/member.

Kolom utama:

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

Catatan:

- `phone` digunakan sebagai identitas unik pelanggan.
- Primary key tetap menggunakan `id`.
- Nomor WhatsApp sebaiknya menggunakan format internasional, contoh: `+6281287968048`.

---

### 5.2 Tabel `point_transactions`

Tabel ini menyimpan riwayat perubahan poin.

Kolom utama:

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

Contoh `type`:

```text
earn
redeem
adjustment
```

---

### 5.3 Tabel `crm_settings`

Tabel ini menyimpan konfigurasi CRM.

Kolom utama:

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

Contoh aturan:

```text
3 poin = 1 Kopi Gratis
Retention = 14 hari
Auto-send WhatsApp = aktif
```

---

### 5.4 Tabel `whatsapp_logs`

Tabel ini menyimpan log pengiriman WhatsApp.

Kolom utama:

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

Contoh `message_type`:

```text
manual
point
redeem
retention
```

Catatan penting:

Jika kolom `message_type` menggunakan `enum`, gunakan value yang memang sudah tersedia. Jangan menggunakan value seperti `manual_test` jika tidak ada di enum, karena akan muncul error:

```text
Data truncated for column 'message_type'
```

---

### 5.5 Tabel `retention_logs`

Tabel ini digunakan untuk mencatat pengiriman pesan retensi supaya pelanggan tidak dikirimi pesan berulang secara berlebihan.

Kolom utama:

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

## 6. Instalasi Project

### 6.1 Masuk ke folder project

Jika project Laravel berada di container Docker:

```bash
sudo docker exec -it php_adi bash
```

Lalu masuk ke root Laravel:

```bash
cd /var/www/html
```

Jika menjalankan langsung di server:

```bash
cd ~/adi_coding/src
```

Sesuaikan dengan lokasi project masing-masing.

---

### 6.2 Install dependency Laravel

```bash
composer install
```

---

### 6.3 Install Twilio SDK

```bash
composer require twilio/sdk
```

---

### 6.4 Install dependency frontend

Jika project memakai Vite:

```bash
npm install
npm run build
```

Untuk development:

```bash
npm run dev
```

---

### 6.5 Jalankan migration

```bash
php artisan migrate
```

Jika ingin reset database saat development:

```bash
php artisan migrate:fresh --seed
```

Gunakan `migrate:fresh` hanya saat data boleh dihapus.

---

### 6.6 Clear cache Laravel

Setelah mengubah `.env`, config, route, atau view:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

---

## 7. Konfigurasi `.env`

Tambahkan konfigurasi berikut pada file `.env`.

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

Penjelasan:

| Key | Fungsi |
|---|---|
| `CRM_BUSINESS_NAME` | Nama bisnis yang muncul pada pesan |
| `CRM_RETENTION_SEND_TIME` | Jam default pengiriman pesan retensi |
| `CRM_RETENTION_CHUNK_SIZE` | Jumlah data yang diproses per batch |
| `TWILIO_WHATSAPP_ENABLED` | Mengaktifkan/nonaktifkan pengiriman WhatsApp |
| `TWILIO_ACCOUNT_SID` | Account SID dari Twilio |
| `TWILIO_AUTH_TOKEN` | Auth Token dari Twilio |
| `TWILIO_WHATSAPP_FROM` | Nomor WhatsApp Sandbox Twilio |
| `QUEUE_CONNECTION=sync` | Agar job WhatsApp langsung berjalan saat testing |

---

## 8. Cara Membuat Akun Twilio

### 8.1 Daftar Twilio

Buka:

```text
https://www.twilio.com/try-twilio
```

Ikuti proses pendaftaran hingga akun berhasil dibuat.

---

### 8.2 Ambil Account SID dan Auth Token

Masuk ke:

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

Masukkan ke `.env`:

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Catatan keamanan:

- Jangan commit `.env` ke GitHub.
- Jangan screenshot `.env` yang berisi token.
- Jika token pernah terlihat publik, segera lakukan regenerate Auth Token di Twilio.

---

### 8.3 Masuk ke WhatsApp Sandbox

Buka:

```text
Messaging
→ Try it out
→ Send a WhatsApp message
```

Di halaman Twilio Sandbox akan muncul:

```text
Sandbox number
Sandbox join code
```

Contoh:

```text
Nomor Twilio Sandbox: +1 415 523 8886
Kode join: join behavior-additional
```

Agar nomor penerima bisa menerima pesan, nomor tersebut harus mengirim pesan join ke Twilio Sandbox.

Contoh:

```text
Kirim ke:
+14155238886

Isi pesan:
join behavior-additional
```

Jika berhasil, Twilio akan membalas bahwa nomor tersebut sudah bergabung ke sandbox.

---

## 9. Test Koneksi Twilio dari Laravel

### 9.1 Clear config dulu

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

---

### 9.2 Masuk tinker

```bash
php artisan tinker
```

---

### 9.3 Cek apakah `.env` terbaca

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

---

### 9.4 Test autentikasi Twilio

```php
$sid = trim(env('TWILIO_ACCOUNT_SID'));
$token = trim(env('TWILIO_AUTH_TOKEN'));

$twilio = new \Twilio\Rest\Client($sid, $token);

$twilio->api->accounts($sid)->fetch()->friendlyName;
```

Jika berhasil, output akan menampilkan nama akun Twilio, contoh:

```text
"My first Twilio account"
```

Jika gagal dengan error:

```text
[HTTP 401] Unable to fetch record: Authenticate
```

Berarti `TWILIO_ACCOUNT_SID` dan `TWILIO_AUTH_TOKEN` tidak cocok atau token tidak valid.

Solusi:

1. Copy ulang Account SID dari Twilio Console.
2. Copy ulang Auth Token dari Twilio Console.
3. Jika pernah bocor, regenerate Auth Token.
4. Update `.env`.
5. Jalankan ulang:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

---

## 10. Test Kirim WhatsApp Menggunakan Tinker

Pastikan nomor tujuan sudah join sandbox.

Contoh nomor tujuan:

```text
+6281287968048
```

Masuk tinker:

```bash
php artisan tinker
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

Jika berhasil, hasilnya akan seperti:

```text
status              => sent
provider_message_id => SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
error_message       => null
sent_at             => 2026-06-17 ...
```

---

## 11. Cek Status Delivery Pesan Twilio

Kadang Laravel mencatat status `sent`, tetapi pesan belum masuk karena delivery ke WhatsApp masih diproses atau gagal di sisi sandbox.

Gunakan `provider_message_id` dari `whatsapp_logs`.

Contoh:

```php
$sid = trim(env('TWILIO_ACCOUNT_SID'));
$token = trim(env('TWILIO_AUTH_TOKEN'));

$twilio = new \Twilio\Rest\Client($sid, $token);

$messageSid = 'SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

$message = $twilio->messages($messageSid)->fetch();

[
    'sid' => $message->sid,
    'status' => $message->status,
    'to' => $message->to,
    'from' => $message->from,
    'error_code' => $message->errorCode,
    'error_message' => $message->errorMessage,
    'date_created' => $message->dateCreated?->format('Y-m-d H:i:s'),
    'date_sent' => $message->dateSent?->format('Y-m-d H:i:s'),
];
```

Status yang mungkin muncul:

| Status | Arti |
|---|---|
| `queued` | Pesan masuk antrean Twilio |
| `sent` | Pesan sudah dikirim oleh Twilio |
| `delivered` | Pesan sudah sampai ke WhatsApp penerima |
| `failed` | Pesan gagal dikirim |
| `undelivered` | Pesan tidak berhasil sampai |

---

## 12. Error yang Pernah Terjadi dan Solusinya

### 12.1 Error: `HTTP 401 Authenticate`

Contoh error:

```text
[HTTP 401] Unable to fetch record: Authenticate
```

Penyebab:

- Account SID salah.
- Auth Token salah.
- Auth Token tidak cocok dengan Account SID.
- Token sudah regenerate tetapi `.env` masih memakai token lama.
- `.env` belum terbaca karena cache Laravel.

Solusi:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

Lalu cek ulang:

```php
$sid = trim(env('TWILIO_ACCOUNT_SID'));
$token = trim(env('TWILIO_AUTH_TOKEN'));

$twilio = new \Twilio\Rest\Client($sid, $token);

$twilio->api->accounts($sid)->fetch()->friendlyName;
```

Jika masih gagal, regenerate Auth Token di Twilio.

---

### 12.2 Error: `63015`

Contoh hasil fetch pesan:

```text
status     => failed
error_code => 63015
```

Penyebab:

Nomor penerima belum join Twilio WhatsApp Sandbox.

Solusi:

Dari WhatsApp nomor penerima, kirim:

```text
join behavior-additional
```

ke:

```text
+14155238886
```

Setelah Twilio membalas sukses, test kirim ulang dari Laravel.

---

### 12.3 Error: `Data truncated for column 'message_type'`

Penyebab:

Kolom `message_type` menggunakan enum, tetapi value yang dikirim tidak tersedia.

Contoh salah:

```php
'message_type' => 'manual_test'
```

Solusi:

Gunakan value yang tersedia:

```php
'message_type' => 'manual'
```

Atau ubah migration kolom `message_type` menjadi `string` jika ingin lebih fleksibel.

---

### 12.4 Error: `Cannot use Twilio\Rest\Client as Client because the name is already in use`

Penyebab:

Di session tinker yang sama, class sudah pernah di-import menggunakan `use`.

Solusi 1:

Keluar dari tinker:

```php
exit
```

Masuk lagi:

```bash
php artisan tinker
```

Solusi 2:

Gunakan full namespace:

```php
$twilio = new \Twilio\Rest\Client($sid, $token);
```

---

### 12.5 Error Filament: `$navigationGroup must be UnitEnum|string|null`

Penyebab:

Filament versi baru mengubah tipe property page.

Salah:

```php
protected static ?string $navigationGroup = 'Kopi Banget CRM';
```

Benar:

```php
use UnitEnum;

protected static string|UnitEnum|null $navigationGroup = 'Kopi Banget CRM';
```

---

### 12.6 Error Filament: `$navigationIcon must have type string|BackedEnum|null`

Salah:

```php
protected static ?string $navigationIcon = 'heroicon-o-user-plus';
```

Benar:

```php
use BackedEnum;

protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';
```

---

### 12.7 Error Filament: `Cannot redeclare non static Page::$view as static`

Salah:

```php
protected static string $view = 'filament.admin.pages.crm-dashboard';
```

Benar:

```php
protected string $view = 'filament.admin.pages.crm-dashboard';
```

---

### 12.8 GitHub menolak push karena secret

Contoh error:

```text
GITHUB PUSH PROTECTION
Push cannot contain secrets
Twilio Account String Identifier
```

Penyebab:

File `.env` atau credential Twilio ikut masuk commit.

Solusi:

1. Pastikan `.env` masuk `.gitignore`.
2. Hapus `.env` dari tracking Git:

```bash
git rm --cached -f .env 2>/dev/null || true
git rm --cached -f src/.env 2>/dev/null || true
```

3. Buat `.env.example` tanpa credential asli.
4. Commit ulang.

Contoh `.gitignore`:

```gitignore
.env
.env.*
src/.env
src/.env.*
!.env.example
!src/.env.example

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

---

### 12.9 GitHub warning file besar `ibdata1`

Contoh warning:

```text
File database/data/ibdata1 is larger than GitHub's recommended maximum file size
```

Penyebab:

File database MySQL/MariaDB ikut masuk Git.

Solusi:

```bash
git rm --cached -r database/data 2>/dev/null || true
git rm --cached -r db/data 2>/dev/null || true
```

Tambahkan ke `.gitignore`:

```gitignore
database/data/
db/data/
**/database/data/
**/db/data/
```

---

## 13. Test dari Web CRM

Setelah test tinker berhasil, lanjut test dari web.

### 13.1 Tambah Member

1. Login ke Filament.
2. Masuk menu:

```text
Kopi Banget CRM → Tambah Member
```

3. Isi nama member.
4. Isi nomor WhatsApp yang sudah join sandbox.
5. Simpan.

Format nomor:

```text
+6281287968048
```

---

### 13.2 Tambah Poin

1. Masuk menu:

```text
Kopi Banget CRM → Dashboard CRM
```

2. Cari nomor WhatsApp member.
3. Input jumlah poin.
4. Klik simpan/tambah poin.
5. Cek WhatsApp penerima.
6. Cek tabel `whatsapp_logs`.

---

### 13.3 Redeem Reward

1. Pastikan total poin member sudah memenuhi syarat.
2. Klik tombol redeem.
3. Sistem mengurangi poin.
4. Sistem menyimpan history.
5. Sistem mengirim WhatsApp jika auto-send aktif.

---

### 13.4 Cek History

Masuk menu:

```text
Kopi Banget CRM → History
```

Cek apakah aktivitas berikut tercatat:

- Tambah member.
- Tambah poin.
- Redeem.
- Perubahan poin.

---

## 14. Automated Retention

Automated retention digunakan untuk mengirim pesan WhatsApp kepada pelanggan yang tidak berkunjung dalam jangka waktu tertentu.

Contoh:

```text
Jika retention_days = 14
Maka sistem akan mencari member dengan last_visit_at lebih dari 14 hari
Lalu mengirim pesan “We miss you” melalui WhatsApp
```

---

### 14.1 Test Retention Dry Run

```bash
php artisan crm:send-retention-whatsapp --dry-run
```

Dry run hanya mengecek data tanpa benar-benar mengirim pesan.

---

### 14.2 Jalankan Retention Asli

```bash
php artisan crm:send-retention-whatsapp
```

---

### 14.3 Scheduler Laravel

Tambahkan ke `routes/console.php` jika belum ada:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('crm:send-retention-whatsapp')
    ->dailyAt(env('CRM_RETENTION_SEND_TIME', '07:00'));
```

Cron server:

```bash
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

Jika menggunakan Docker, cron bisa dijalankan di host atau dibuat container scheduler terpisah.

---

## 15. Queue

Untuk testing, gunakan:

```env
QUEUE_CONNECTION=sync
```

Dengan ini, proses kirim WhatsApp langsung berjalan tanpa perlu worker.

Untuk production, gunakan:

```env
QUEUE_CONNECTION=database
```

Lalu jalankan:

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

Jika menggunakan supervisor:

```bash
php artisan queue:work --tries=3 --timeout=90
```

---

## 16. Keamanan

### 16.1 Jangan push `.env`

File `.env` berisi credential asli seperti:

```text
APP_KEY
DB_PASSWORD
TWILIO_ACCOUNT_SID
TWILIO_AUTH_TOKEN
```

Pastikan `.env` tidak masuk Git.

Cek:

```bash
git status
```

Jika `.env` muncul, segera keluarkan dari tracking:

```bash
git rm --cached -f .env
git rm --cached -f src/.env
```

---

### 16.2 Gunakan `.env.example`

`.env.example` boleh dipush, tetapi isinya harus placeholder:

```env
TWILIO_WHATSAPP_ENABLED=false
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_WHATSAPP_FROM=+14155238886
TWILIO_STATUS_CALLBACK_URL=
```

---

### 16.3 Regenerate Auth Token Jika Bocor

Jika Auth Token pernah:

- tampil di screenshot,
- masuk Git commit,
- terbaca oleh GitHub Push Protection,
- dikirim ke orang lain,

maka segera lakukan regenerate token di Twilio Console.

Setelah regenerate:

1. Update `.env`.
2. Jalankan:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

3. Test ulang:

```php
$sid = trim(env('TWILIO_ACCOUNT_SID'));
$token = trim(env('TWILIO_AUTH_TOKEN'));

$twilio = new \Twilio\Rest\Client($sid, $token);

$twilio->api->accounts($sid)->fetch()->friendlyName;
```

---

## 17. Catatan Tentang Twilio Sandbox dan Production

### 17.1 Sandbox

Twilio Sandbox cocok untuk:

- Development.
- Testing.
- Demo tugas akhir.
- Pengujian integrasi awal.

Keterbatasan Sandbox:

- Hanya bisa kirim ke nomor yang sudah join sandbox.
- Nomor penerima harus mengirim kode join.
- Tidak cocok untuk production langsung.
- Beberapa jenis pesan business-initiated membutuhkan template.

---

### 17.2 Production

Untuk production, perlu:

1. Upgrade akun Twilio.
2. Daftarkan WhatsApp Sender.
3. Verifikasi bisnis jika diperlukan.
4. Gunakan template message yang disetujui.
5. Ikuti aturan WhatsApp Business Platform.

Jika sudah production, nomor pelanggan tidak perlu join sandbox satu per satu.

---

## 18. Checklist Testing

Gunakan checklist ini untuk memastikan sistem siap demo.

### 18.1 Laravel dan Database

- [ ] Project bisa dibuka.
- [ ] Login Filament berhasil.
- [ ] Migration berhasil.
- [ ] Model CRM terbaca.
- [ ] Tabel `members` tersedia.
- [ ] Tabel `point_transactions` tersedia.
- [ ] Tabel `crm_settings` tersedia.
- [ ] Tabel `whatsapp_logs` tersedia.
- [ ] Tabel `retention_logs` tersedia.

---

### 18.2 Twilio

- [ ] Account SID benar.
- [ ] Auth Token benar.
- [ ] `TWILIO_WHATSAPP_FROM=+14155238886`.
- [ ] `TWILIO_WHATSAPP_ENABLED=true`.
- [ ] `php artisan config:clear` sudah dijalankan.
- [ ] Test `friendlyName` berhasil.
- [ ] Nomor tujuan sudah join sandbox.
- [ ] Test tinker menghasilkan `provider_message_id`.
- [ ] Status Twilio menjadi `delivered`.

---

### 18.3 CRM Web

- [ ] Tambah member berhasil.
- [ ] Cari member berhasil.
- [ ] Tambah poin berhasil.
- [ ] Pesan WhatsApp poin terkirim.
- [ ] Redeem berhasil.
- [ ] Pesan WhatsApp redeem terkirim.
- [ ] History bertambah.
- [ ] Settings CRM bisa disimpan.
- [ ] Retention command bisa dry-run.
- [ ] Retention command bisa mengirim pesan.

---

## 19. Perintah Penting

### Masuk container

```bash
sudo docker exec -it php_adi bash
```

### Masuk Laravel

```bash
cd /var/www/html
```

### Clear cache

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

### Tinker

```bash
php artisan tinker
```

### Migration

```bash
php artisan migrate
```

### Queue worker

```bash
php artisan queue:work
```

### Test retention

```bash
php artisan crm:send-retention-whatsapp --dry-run
php artisan crm:send-retention-whatsapp
```

### Cek Git status

```bash
git status
```

### Commit aman

```bash
git add .
git commit -m "Add CRM Kopi Banget documentation and features"
git push
```

---

## 20. Template Pesan WhatsApp

### 20.1 Pesan Tambah Poin

```text
Halo Kak {name}, terima kasih sudah berbelanja di Kopi Banget.
Poin Kakak bertambah {points} poin.
Total poin Kakak sekarang {total_points}.
```

---

### 20.2 Pesan Redeem

```text
Halo Kak {name}, redeem reward berhasil.
Reward: {reward_name}
Sisa poin Kakak sekarang {total_points}.
Terima kasih sudah menjadi pelanggan Kopi Banget.
```

---

### 20.3 Pesan Retensi

```text
Halo Kak {name}, sudah lama belum mampir ke Kopi Banget.
Poin Kakak masih ada {total_points}.
Yuk mampir lagi dan nikmati promo dari Kopi Banget.
```

---

## 21. Format Nomor WhatsApp

Gunakan format internasional:

```text
+628xxxxxxxxxx
```

Contoh:

```text
+6281287968048
```

Jangan gunakan format:

```text
081287968048
+62 812-8796-8048
81287968048
```

Sistem dapat dibuat melakukan normalisasi, tetapi format yang paling aman untuk database adalah:

```text
+6281287968048
```

---

## 22. Alur Demo untuk Sidang / Presentasi

Gunakan alur berikut untuk demonstrasi:

1. Login sebagai kasir/admin.
2. Buka menu CRM.
3. Tambahkan member baru dengan nomor WhatsApp.
4. Tampilkan bahwa member tersimpan.
5. Masuk dashboard CRM.
6. Cari nomor WhatsApp member.
7. Tambahkan 1 poin.
8. Tampilkan history poin bertambah.
9. Tampilkan pesan WhatsApp masuk.
10. Tambahkan poin sampai memenuhi syarat redeem.
11. Klik redeem.
12. Tampilkan poin berkurang.
13. Tampilkan pesan redeem masuk.
14. Buka menu history.
15. Tampilkan semua aktivitas tercatat.
16. Buka settings.
17. Tunjukkan bahwa syarat redeem dan retensi dapat diatur.
18. Jalankan command retention dry-run.
19. Jelaskan bahwa scheduler bisa menjalankan proses otomatis setiap hari.

---

## 23. Kesimpulan Implementasi

Sistem CRM Kopi Banget sudah berhasil mengintegrasikan:

```text
Laravel Filament
↓
Database CRM
↓
Point Transaction
↓
Whatsapp Log
↓
Twilio WhatsApp Gateway
↓
WhatsApp pelanggan
```

Sistem ini tidak hanya melakukan CRUD data member, tetapi juga memiliki proses bisnis CRM yang lebih kuat:

- Identifikasi pelanggan menggunakan nomor WhatsApp.
- Pencatatan poin loyalitas.
- Redeem reward.
- Riwayat aktivitas.
- Pengaturan master promo.
- Integrasi WhatsApp Gateway.
- Fitur retensi pelanggan berbasis jadwal.

Dengan sistem ini, Kopi Banget dapat mulai membangun database pelanggan dan menjaga hubungan pelanggan secara lebih terstruktur.

---

## 24. Referensi Dokumentasi Resmi

- Laravel Documentation: https://laravel.com/docs
- Laravel Scheduling: https://laravel.com/docs/scheduling
- Twilio WhatsApp Sandbox: https://www.twilio.com/docs/whatsapp/sandbox
- Twilio WhatsApp Quickstart: https://www.twilio.com/docs/whatsapp/quickstart
- Twilio Error 63015: https://www.twilio.com/docs/api/errors/63015
- Twilio Error 20003 / Authenticate: https://www.twilio.com/docs/api/errors/20003
- Twilio API Keys: https://www.twilio.com/docs/iam/api-keys

---

## 25. Catatan Akhir

Untuk tahap development dan demo tugas akhir, penggunaan **Twilio WhatsApp Sandbox** sudah cukup.

Namun, untuk penggunaan nyata/production:

1. Jangan gunakan sandbox.
2. Upgrade akun Twilio.
3. Daftarkan WhatsApp sender resmi.
4. Gunakan template message yang disetujui.
5. Lindungi credential dengan `.env`.
6. Jalankan queue worker dan scheduler secara permanen.
7. Pantau `whatsapp_logs` dan status delivery Twilio.

Dokumentasi ini dapat diperbarui sesuai perkembangan fitur CRM Kopi Banget berikutnya.
