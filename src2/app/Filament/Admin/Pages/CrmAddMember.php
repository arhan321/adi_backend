<?php

namespace App\Filament\Admin\Pages;

use UnitEnum;
use Throwable;
use BackedEnum;
use App\Models\Member;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Services\Whatsapp\FonnteWhatsappService;
use Illuminate\Database\UniqueConstraintViolationException;

class CrmAddMember extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Tambah Member';

    protected static string|UnitEnum|null $navigationGroup = 'Kopi Banget CRM';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.admin.pages.crm-add-member';

    public string $name = '';

    public string $phone = '';

    public ?string $birth_date = null;

    public ?string $notes = null;

    public function mount(?string $phone = null): void
    {
        $phoneFromUrl = $phone ?? request()->query('phone');
        $this->phone = is_string($phoneFromUrl) ? $phoneFromUrl : '';
    }

    public function save(): mixed
    {
        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $whatsappService = app(FonnteWhatsappService::class);
            $normalizedPhone = $whatsappService->normalizePhone($this->phone);

            $member = Member::query()->create([
                'member_code' => $this->generateMemberCode(),
                'name' => $this->name,
                'phone' => $normalizedPhone,
                'birth_date' => $this->birth_date ?: null,
                'total_points' => 0,
                'last_visit_at' => null,
                'status' => Member::STATUS_ACTIVE,
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ]);

            Notification::make()
                ->title('Member berhasil ditambahkan')
                ->body($member->name . ' sudah masuk database CRM Kopi Banget.')
                ->success()
                ->send();

            return redirect()->to(
                CrmDashboard::getUrl([
                    'phone' => $member->phone,
                ])
            );
        } catch (UniqueConstraintViolationException) {
            Notification::make()
                ->title('Nomor sudah terdaftar')
                ->body('Gunakan menu dashboard untuk mencari member tersebut.')
                ->warning()
                ->send();
        } catch (Throwable $throwable) {
            Notification::make()
                ->title('Gagal menyimpan member')
                ->body($throwable->getMessage())
                ->danger()
                ->send();
        }

        return null;
    }

    protected function generateMemberCode(): string
    {
        do {
            $code = 'KB-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        } while (Member::query()->where('member_code', $code)->exists());

        return $code;
    }
}
