<?php

namespace App\Filament\Admin\Pages;

use App\Models\CrmSetting;
use App\Models\Member;
use App\Models\PointTransaction;
use App\Services\Crm\MemberPointService;
use App\Services\Whatsapp\TwilioWhatsappService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CrmDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Dashboard CRM';

    protected static ?string $navigationGroup = 'Kopi Banget CRM';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.admin.pages.crm-dashboard';

    public string $searchPhone = '';

    public ?int $selectedMemberId = null;

    public int $pointsInput = 1;

    public string $activityName = 'Pembelian Produk';

    public function mount(?string $phone = null): void
    {
        $this->searchPhone = $phone ?? '';

        if ($this->searchPhone !== '') {
            $this->searchMember();
        }
    }

    public function searchMember(): void
    {
        $digits = preg_replace('/[^0-9]/', '', $this->searchPhone);

        $member = Member::query()
            ->where('phone', 'like', "%{$digits}%")
            ->latest()
            ->first();

        $this->selectedMemberId = $member?->id;

        if (! $member) {
            Notification::make()
                ->title('Member tidak ditemukan')
                ->body('Nomor WhatsApp belum terdaftar. Silakan tambah member baru.')
                ->warning()
                ->send();
        }
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
                ->body('Total poin member sudah diperbarui dan pesan WhatsApp dimasukkan ke antrean jika auto-send aktif.')
                ->success()
                ->send();
        } catch (\Throwable $throwable) {
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
        } catch (\Throwable $throwable) {
            Notification::make()
                ->title('Redeem gagal')
                ->body($throwable->getMessage())
                ->danger()
                ->send();
        }
    }

    public function goToAddMember(): mixed
    {
        return redirect()->to(CrmAddMember::getUrl([
            'phone' => $this->searchPhone,
        ]));
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
            'today_activity' => PointTransaction::query()->whereDate('transaction_at', today())->count(),
        ];
    }
}
