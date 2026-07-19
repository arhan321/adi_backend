<?php

namespace App\Filament\Admin\Pages;

use UnitEnum;
use Throwable;
use BackedEnum;
use App\Models\Member;
use Filament\Pages\Page;
use Illuminate\Validation\Rule;
use Filament\Notifications\Notification;
use App\Services\Whatsapp\FonnteWhatsappService;

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

    public function mount(mixed $member = null): void
    {
        $memberId = $this->resolveMemberId($member);

        if (! $memberId) {
            Notification::make()
                ->title('Member belum dipilih')
                ->body('Silakan pilih member dari Dashboard CRM terlebih dahulu.')
                ->warning()
                ->send();

            $this->redirect(CrmDashboard::getUrl());

            return;
        }

        $record = Member::query()->find($memberId);

        if (! $record) {
            Notification::make()
                ->title('Member tidak ditemukan')
                ->body('Data member yang ingin diedit tidak tersedia atau sudah dihapus.')
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
        $member = $this->getMemberRecord();

        if (! $member) {
            Notification::make()
                ->title('Member tidak ditemukan')
                ->body('Silakan kembali ke Dashboard CRM lalu pilih member lagi.')
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

            $phoneAlreadyUsed = Member::withTrashed()
                ->where('phone', $normalizedPhone)
                ->where('id', '!=', $member->id)
                ->exists();

            if ($phoneAlreadyUsed) {
                Notification::make()
                    ->title('Nomor sudah digunakan')
                    ->body('Nomor WhatsApp tersebut sudah terdaftar pada member lain.')
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
                ->title('Member berhasil diperbarui')
                ->body($member->name . ' sudah diperbarui di CRM Kopi Banget.')
                ->success()
                ->send();

            return redirect()->to(
                CrmDashboard::getUrl([
                    'phone' => $normalizedPhone,
                ])
            );
        } catch (Throwable $throwable) {
            Notification::make()
                ->title('Gagal memperbarui member')
                ->body($throwable->getMessage())
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

        if (is_array($value) || is_object($value) || $value === null || $value === '') {
            return null;
        }

        $value = preg_replace('/[^0-9]/', '', (string) $value);

        if ($value === '') {
            return null;
        }

        return (int) $value;
    }
}
