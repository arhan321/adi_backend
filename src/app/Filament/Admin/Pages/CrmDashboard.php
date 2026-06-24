<?php

namespace App\Filament\Admin\Pages;

use UnitEnum;
use Throwable;
use BackedEnum;
use App\Models\Member;
use Filament\Pages\Page;
use App\Models\CrmSetting;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\Auth;
use App\Services\Crm\MemberPointService;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CrmDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Dashboard CRM';

    protected static string|UnitEnum|null $navigationGroup = 'Kopi Banget CRM';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.admin.pages.crm-dashboard';

    /**
     * Nama property tetap searchPhone agar kompatibel dengan view lama.
     * Sekarang isinya boleh nomor WhatsApp ATAU nama member.
     */
    public string $searchPhone = '';

    public ?int $selectedMemberId = null;

    public ?string $searchFeedback = null;

    /**
     * Daftar hasil pencarian ketika nama/keyword menghasilkan lebih dari satu member.
     * Dipakai view untuk menampilkan nama + nomor WhatsApp agar kasir bisa memilih member yang benar.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $memberSearchResults = [];

    public int $pointsInput = 1;

    public string $activityName = 'Pembelian Produk';

    public function mount(?string $phone = null, ?string $q = null): void
    {
        /**
         * Dashboard membaca query string agar redirect seperti:
         * /admin/crm-dashboard?phone=628xxx atau /admin/crm-dashboard?q=adi zacky
         * langsung membuka profil member jika datanya valid dan tidak ambigu.
         */
        $keywordFromUrl = $phone
            ?? $q
            ?? request()->query('phone')
            ?? request()->query('q')
            ?? request()->query('search');

        $this->searchPhone = is_string($keywordFromUrl) ? trim($keywordFromUrl) : '';

        if ($this->searchPhone !== '') {
            $this->searchMember();
        }
    }

    public function searchMember(): void
    {
        $keyword = trim($this->searchPhone);

        $this->selectedMemberId = null;
        $this->searchFeedback = null;
        $this->memberSearchResults = [];

        if ($keyword === '') {
            $this->searchFeedback = 'Masukkan nama lengkap atau nomor WhatsApp member terlebih dahulu.';

            Notification::make()
                ->title('Pencarian masih kosong')
                ->body('Silakan masukkan nama lengkap atau nomor WhatsApp member.')
                ->warning()
                ->send();

            return;
        }

        $digits = preg_replace('/[^0-9]/', '', $keyword) ?: '';
        $isPhoneSearch = $this->looksLikePhoneSearch($keyword, $digits);

        if ($isPhoneSearch) {
            $member = $this->findMemberByPhone($digits);

            if ($member) {
                $this->selectMember($member, 'Member ditemukan berdasarkan nomor WhatsApp.');

                return;
            }

            $this->memberNotFound('Nomor WhatsApp tidak ditemukan. Silakan cek lagi atau tambah member baru.');

            return;
        }

        $exactNameMatches = $this->findExactNameMatches($keyword);

        if ($exactNameMatches->count() === 1) {
            $this->selectMember($exactNameMatches->first(), 'Member ditemukan berdasarkan nama lengkap.');

            return;
        }

        if ($exactNameMatches->count() > 1) {
            $this->showMultipleSearchResults(
                members: $exactNameMatches,
                title: 'Ada lebih dari satu member dengan nama yang sama.',
                message: 'Silakan pilih member yang benar dari hasil pencarian di bawah ini berdasarkan nama dan nomor WhatsApp.'
            );

            return;
        }

        $partialNameMatches = $this->findPartialNameMatches($keyword);

        if ($partialNameMatches->count() === 1) {
            $this->selectMember($partialNameMatches->first(), 'Member ditemukan berdasarkan nama.');

            return;
        }

        if ($partialNameMatches->count() > 1) {
            $this->showMultipleSearchResults(
                members: $partialNameMatches,
                title: 'Ditemukan beberapa member yang cocok.',
                message: 'Silakan pilih member yang benar dari hasil pencarian di bawah ini berdasarkan nama dan nomor WhatsApp.'
            );

            return;
        }

        $this->memberNotFound('Member tidak ditemukan berdasarkan nama atau nomor WhatsApp tersebut.');
    }

    public function addPoints(MemberPointService $memberPointService): void
    {
        $member = $this->getSelectedMember();

        if (! $member) {
            throw ValidationException::withMessages([
                'searchPhone' => 'Cari member terlebih dahulu.',
            ]);
        }

        $this->validate([
            'pointsInput' => ['required', 'integer', 'min:1', 'max:100'],
            'activityName' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $memberPointService->addPoints(
                member: $member,
                points: $this->pointsInput,
                userId: Auth::id(),
                activityName: $this->activityName ?: 'Pembelian Produk',
            );

            $this->pointsInput = 1;
            $this->activityName = 'Pembelian Produk';
            $this->selectedMemberId = $member->id;

            Notification::make()
                ->title('Poin berhasil ditambahkan')
                ->body('Total poin member sudah diperbarui dan pesan WhatsApp akan dikirim jika auto-send aktif.')
                ->success()
                ->send();
        } catch (Throwable $throwable) {
            Notification::make()
                ->title('Gagal menambah poin')
                ->body($throwable->getMessage())
                ->danger()
                ->send();
        }
    }

    public function redeem(MemberPointService $memberPointService): void
    {
        $member = $this->getSelectedMember();

        if (! $member) {
            throw ValidationException::withMessages([
                'searchPhone' => 'Cari member terlebih dahulu.',
            ]);
        }

        try {
            $memberPointService->redeem(
                member: $member,
                userId: Auth::id(),
            );

            $this->selectedMemberId = $member->id;

            Notification::make()
                ->title('Redeem berhasil')
                ->body('Poin member sudah dikurangi sesuai syarat reward.')
                ->success()
                ->send();
        } catch (Throwable $throwable) {
            Notification::make()
                ->title('Redeem gagal')
                ->body($throwable->getMessage())
                ->danger()
                ->send();
        }
    }

    public function goToAddMember(): mixed
    {
        return redirect()->to(
            CrmAddMember::getUrl([
                'phone' => $this->searchPhone,
            ])
        );
    }

    public function selectMemberFromSearchResult(int $memberId): void
    {
        $member = Member::query()->find($memberId);

        if (! $member) {
            $this->memberNotFound('Member yang dipilih sudah tidak tersedia atau sudah dihapus.');

            return;
        }

        $this->memberSearchResults = [];
        $this->selectMember($member, 'Member dipilih dari hasil pencarian.');
    }

    public function usePhoneFromSearchResult(string $phone): void
    {
        $this->searchPhone = $phone;
        $this->searchMember();
    }

    public function getSelectedMember(): ?Member
    {
        if (! $this->selectedMemberId) {
            return null;
        }

        return Member::query()->find($this->selectedMemberId);
    }

    public function getSetting(): CrmSetting
    {
        return CrmSetting::current();
    }

    public function getStats(): array
    {
        $setting = CrmSetting::current();

        return [
            'retention_days' => $setting->retention_days,
            'redeem_rule' => $setting->redeem_required_points . ' Poin = ' . $setting->reward_name,
            'total_member' => Member::query()->count(),
            'today_activity' => PointTransaction::query()
                ->whereDate('transaction_at', today())
                ->count(),
        ];
    }

    private function looksLikePhoneSearch(string $keyword, string $digits): bool
    {
        if ($digits === '') {
            return false;
        }

        /**
         * Query dianggap nomor jika isinya mayoritas angka/simbol telepon
         * dan panjang digitnya masuk akal untuk nomor WhatsApp Indonesia.
         * Ini mencegah pencarian nama seperti "adi zacky" berubah menjadi phone search.
         */
        $onlyPhoneCharacters = preg_match('/^[0-9\s\+\-\.\(\)]+$/', $keyword) === 1;

        return $onlyPhoneCharacters && strlen($digits) >= 8;
    }

    private function findMemberByPhone(string $digits): ?Member
    {
        $phoneCandidates = $this->buildPhoneCandidates($digits);

        if ($phoneCandidates === []) {
            return null;
        }

        $normalizedPhoneColumn = $this->normalizedPhoneColumnSql();

        return Member::query()
            ->where(function ($query) use ($phoneCandidates, $normalizedPhoneColumn) {
                foreach ($phoneCandidates as $candidate) {
                    $query->orWhereRaw($normalizedPhoneColumn . ' = ?', [$candidate]);
                }
            })
            ->latest()
            ->first();
    }

    private function buildPhoneCandidates(string $digits): array
    {
        $digits = preg_replace('/[^0-9]/', '', $digits) ?: '';
        $candidates = [$digits];

        if (str_starts_with($digits, '0')) {
            $withoutZero = ltrim($digits, '0');

            if ($withoutZero !== '') {
                $candidates[] = '62' . $withoutZero;
                $candidates[] = $withoutZero;
            }
        }

        if (str_starts_with($digits, '8')) {
            $candidates[] = '62' . $digits;
            $candidates[] = '0' . $digits;
        }

        if (str_starts_with($digits, '62')) {
            $withoutCountryCode = substr($digits, 2);

            if ($withoutCountryCode !== '') {
                $candidates[] = '0' . $withoutCountryCode;
                $candidates[] = $withoutCountryCode;
            }
        }

        return collect($candidates)
            ->filter(fn ($candidate) => is_string($candidate) && strlen($candidate) >= 8)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizedPhoneColumnSql(): string
    {
        /**
         * Normalisasi ringan langsung di SQL agar nomor seperti:
         * +62 812-xxxx, 62812xxxx, 0812xxxx tetap bisa dicari.
         */
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', ''), '.', ''), '(', ''), ')', '')";
    }

    private function findExactNameMatches(string $keyword)
    {
        $normalizedName = mb_strtolower(trim($keyword));

        return Member::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->latest()
            ->limit(10)
            ->get();
    }

    private function findPartialNameMatches(string $keyword)
    {
        return Member::query()
            ->where('name', 'like', '%' . $keyword . '%')
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    private function selectMember(Member $member, string $message): void
    {
        $this->selectedMemberId = $member->id;
        $this->memberSearchResults = [];
        $this->searchPhone = $member->phone;
        $this->searchFeedback = $message . ' ' . $member->name . ' - ' . $member->phone;
    }

    private function memberNotFound(string $message): void
    {
        $this->selectedMemberId = null;
        $this->memberSearchResults = [];
        $this->searchFeedback = $message;

        Notification::make()
            ->title('Member tidak ditemukan')
            ->body($message)
            ->warning()
            ->send();
    }

    private function showMultipleSearchResults($members, string $title, string $message): void
    {
        $this->selectedMemberId = null;
        $this->memberSearchResults = $this->formatMemberSearchResults($members);

        $resultCount = count($this->memberSearchResults);
        $this->searchFeedback = $message . ' Ditemukan ' . $resultCount . ' member.';

        Notification::make()
            ->title('Pilih member yang benar')
            ->body($message)
            ->warning()
            ->send();
    }

    private function formatMemberSearchResults($members): array
    {
        return collect($members)
            ->map(function (Member $member): array {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'phone' => $member->phone,
                    'member_code' => $member->member_code ?? 'KB-MEMBER',
                    'total_points' => (int) $member->total_points,
                    'last_visit_at' => $member->last_visit_at
                        ? $member->last_visit_at->diffForHumans()
                        : 'Belum ada kunjungan',
                    'initial' => strtoupper(substr($member->name, 0, 1)),
                ];
            })
            ->values()
            ->all();
    }
}
