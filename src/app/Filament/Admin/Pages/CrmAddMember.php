<?php

namespace App\Filament\Admin\Pages;

use App\Models\Member;
use App\Services\Whatsapp\TwilioWhatsappService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CrmAddMember extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Tambah Member';

    protected static ?string $navigationGroup = 'Kopi Banget CRM';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.admin.pages.crm-add-member';

    public string $name = '';

    public string $phone = '';

    public ?string $birth_date = null;

    public ?string $notes = null;

    public function mount(?string $phone = null): void
    {
        $this->phone = $phone ?? '';
    }

    public function save(TwilioWhatsappService $twilioWhatsappService): mixed
    {
        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $normalizedPhone = $twilioWhatsappService->normalizePhone($this->phone);

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

            return redirect()->to(CrmDashboard::getUrl([
                'phone' => $member->phone,
            ]));
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            Notification::make()
                ->title('Nomor sudah terdaftar')
                ->body('Gunakan menu dashboard untuk mencari member tersebut.')
                ->warning()
                ->send();
        } catch (\Throwable $throwable) {
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
