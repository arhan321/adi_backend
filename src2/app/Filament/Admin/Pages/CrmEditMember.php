<?php

namespace App\Filament\Admin\Pages;

use App\Models\Member;
use App\Services\Whatsapp\FonnteWhatsappService;
use App\Support\CrmAccess;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\Rule;
use Throwable;
use UnitEnum;

class CrmEditMember extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'Edit Member';

    protected static string|UnitEnum|null $navigationGroup = 'Kopi Banget CRM';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.crm-edit-member';

    public ?int $memberId = null;

    public string $name = '';

    public string $phone = '';

    public ?string $birth_date = null;

    public string $status = Member::STATUS_ACTIVE;

    public ?string $notes = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return CrmAccess::canManageMembers(auth()->user());
    }

    public function mount(mixed $member = null): void
    {
        abort_unless(
            static::canAccess(),
            403,
            'Anda tidak memiliki akses untuk mengubah data customer.',
        );

        $memberId = $this->resolveMemberId($member);

        if (! $memberId) {
            Notification::make()
                ->title('Customer belum dipilih')
                ->body('Silakan pilih customer dari Dashboard CRM terlebih dahulu.')
                ->warning()
                ->send();

            $this->redirect(CrmDashboard::getUrl());

            return;
        }

        $record = Member::query()->find($memberId);

        if (! $record) {
            Notification::make()
                ->title('Customer tidak ditemukan')
                ->body('Data customer yang ingin diedit tidak tersedia atau sudah dihapus.')
                ->danger()
                ->send();

            $this->redirect(CrmDashboard::getUrl());

            return;
        }

        $this->memberId = $record->id;
        $this->name = (string) $record->name;
        $this->phone = (string) $record->phone;
        $this->birth_date = $record->birth_date?->format('Y-m-d');
        $this->status = $record->status ?: Member::STATUS_ACTIVE;
        $this->notes = $record->notes;
    }

    public function save(): mixed
    {
        if (! static::canAccess()) {
            Notification::make()
                ->title('Akses ditolak')
                ->body('Anda tidak memiliki akses untuk mengubah data customer.')
                ->danger()
                ->send();

            return null;
        }

        $member = $this->getMemberRecord();

        if (! $member) {
            Notification::make()
                ->title('Customer tidak ditemukan')
                ->body('Silakan kembali ke Dashboard CRM lalu pilih customer lagi.')
                ->danger()
                ->send();

            return redirect()->to(CrmDashboard::getUrl());
        }

        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in([
                Member::STATUS_ACTIVE,
                Member::STATUS_INACTIVE,
                Member::STATUS_BLOCKED,
            ])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $whatsappService = app(FonnteWhatsappService::class);
            $normalizedPhone = $whatsappService->normalizePhone($this->phone);

            /*
             * Member sudah tidak memakai SoftDeletes, sehingga pengecekan
             * nomor cukup dilakukan pada record yang benar-benar tersedia.
             */
            $phoneAlreadyUsed = Member::query()
                ->where('phone', $normalizedPhone)
                ->where('id', '!=', $member->id)
                ->exists();

            if ($phoneAlreadyUsed) {
                Notification::make()
                    ->title('Nomor sudah digunakan')
                    ->body('Nomor WhatsApp tersebut sudah terdaftar pada customer lain.')
                    ->warning()
                    ->send();

                return null;
            }

            $member->update([
                'name' => $this->name,
                'phone' => $normalizedPhone,
                'birth_date' => $this->birth_date ?: null,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            Notification::make()
                ->title('Customer berhasil diperbarui')
                ->body($member->name.' sudah diperbarui di CRM Kopi Banget.')
                ->success()
                ->send();

            return redirect()->to(
                CrmDashboard::getUrl([
                    'phone' => $normalizedPhone,
                ])
            );
        } catch (Throwable $throwable) {
            report($throwable);

            Notification::make()
                ->title('Gagal memperbarui customer')
                ->body('Terjadi kesalahan saat memperbarui data. Silakan coba kembali.')
                ->danger()
                ->send();
        }

        return null;
    }

    public function getMemberRecord(): ?Member
    {
        if (! $this->memberId) {
            return null;
        }

        return Member::query()->find($this->memberId);
    }

    private function resolveMemberId(mixed $member = null): ?int
    {
        if ($member instanceof Member) {
            return $member->id;
        }

        $value = $member
            ?? request()->query('member')
            ?? request()->route('member');

        if (
            is_array($value)
            || is_object($value)
            || $value === null
            || $value === ''
        ) {
            return null;
        }

        $value = preg_replace('/[^0-9]/', '', (string) $value);

        if ($value === '') {
            return null;
        }

        return (int) $value;
    }
}
