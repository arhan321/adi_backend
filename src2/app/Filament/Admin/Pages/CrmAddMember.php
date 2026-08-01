<?php

namespace App\Filament\Admin\Pages;

use UnitEnum;
use Throwable;
use BackedEnum;
use App\Models\Member;
use Filament\Pages\Page;
use App\Support\CrmAccess;
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

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return CrmAccess::canUseCashierWorkspace($user)
            && CrmAccess::canManageMembers($user);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(?string $phone = null): void
    {
        abort_unless(
            static::canAccess(),
            403,
            'Anda tidak memiliki akses untuk menambahkan customer.',
        );

        $phoneFromUrl = $phone ?? request()->query('phone');
        $this->phone = is_string($phoneFromUrl) ? $phoneFromUrl : '';
    }

    public function save(): mixed
    {
        if (! static::canAccess()) {
            Notification::make()
                ->title('Akses ditolak')
                ->body('Anda tidak memiliki akses untuk menambahkan customer.')
                ->danger()
                ->send();

            return null;
        }

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
                ->title('Customer berhasil ditambahkan')
                ->body($member->name.' sudah masuk database CRM Kopi Banget.')
                ->success()
                ->send();

            return redirect()->to(
                CrmDashboard::getUrl([
                    'member' => $member->id,
                ])
            );
        } catch (UniqueConstraintViolationException) {
            Notification::make()
                ->title('Nomor sudah terdaftar')
                ->body('Gunakan menu dashboard untuk mencari customer tersebut.')
                ->warning()
                ->send();
        } catch (Throwable $throwable) {
            report($throwable);

            Notification::make()
                ->title('Gagal menyimpan customer')
                ->body('Terjadi kesalahan saat menyimpan data. Silakan coba kembali.')
                ->danger()
                ->send();
        }

        return null;
    }

    protected function generateMemberCode(): string
    {
        do {
            $code = 'KB-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (
            Member::query()
                ->where('member_code', $code)
                ->exists()
        );

        return $code;
    }
}