<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Models\CrmSetting;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CrmSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings CRM';

    protected static string|UnitEnum|null $navigationGroup =
        'Kopi Banget CRM';

    protected static ?int $navigationSort = 4;

    protected string $view =
        'filament.admin.pages.crm-settings-page';

    public int $redeem_required_points = 3;

    public string $reward_name = '1 Kopi Gratis';

    public bool $promo_is_active = true;

    public int $retention_days = 14;

    public string $retention_send_time = '07:00';

    public bool $auto_send_whatsapp = true;

    public ?string $point_message_template = null;

    public ?string $redeem_message_template = null;

    public ?string $retention_message_template = null;

    /**
     * Hanya role super_admin dan manajemen yang boleh
     * mengakses halaman Settings CRM.
     *
     * Pemeriksaan dibuat langsung berdasarkan nama role,
     * bukan permission, supaya tidak terpengaruh cache Shield.
     */
    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            'super_admin',
            'manajemen',
        ]);
    }

    /**
     * Menu Settings CRM tidak ditampilkan pada sidebar kasir.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        /*
         * Mencegah kasir membuka halaman menggunakan URL langsung.
         */
        abort_unless(
            static::canAccess(),
            403,
            'Hanya manajemen dan super admin yang dapat membuka pengaturan retention.',
        );

        $setting = CrmSetting::current();

        $this->redeem_required_points =
            (int) $setting->redeem_required_points;

        $this->reward_name =
            (string) $setting->reward_name;

        $this->promo_is_active =
            (bool) $setting->promo_is_active;

        $this->retention_days =
            (int) $setting->retention_days;

        $this->retention_send_time = substr(
            (string) $setting->retention_send_time,
            0,
            5,
        ) ?: '07:00';

        $this->auto_send_whatsapp =
            (bool) $setting->auto_send_whatsapp;

        $this->point_message_template =
            $setting->point_message_template;

        $this->redeem_message_template =
            $setting->redeem_message_template;

        $this->retention_message_template =
            $setting->retention_message_template;
    }

    public function save(): void
    {
        /*
         * Pengecekan ulang di backend.
         *
         * Kasir yang masih membuka tab lama tetap tidak dapat
         * mengirim perubahan melalui request Livewire.
         */
        abort_unless(
            static::canAccess(),
            403,
            'Hanya manajemen dan super admin yang dapat mengubah pengaturan retention.',
        );

        $this->validate([
            'redeem_required_points' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'reward_name' => [
                'required',
                'string',
                'max:100',
            ],
            'promo_is_active' => [
                'boolean',
            ],
            'retention_days' => [
                'required',
                'integer',
                'min:1',
                'max:365',
            ],
            'retention_send_time' => [
                'required',
                'date_format:H:i',
            ],
            'auto_send_whatsapp' => [
                'boolean',
            ],
            'point_message_template' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'redeem_message_template' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'retention_message_template' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        CrmSetting::current()->update([
            'redeem_required_points' =>
                $this->redeem_required_points,

            'reward_name' =>
                $this->reward_name,

            'promo_is_active' =>
                $this->promo_is_active,

            'retention_days' =>
                $this->retention_days,

            'retention_send_time' =>
                $this->retention_send_time.':00',

            'auto_send_whatsapp' =>
                $this->auto_send_whatsapp,

            'point_message_template' =>
                $this->point_message_template,

            'redeem_message_template' =>
                $this->redeem_message_template,

            'retention_message_template' =>
                $this->retention_message_template,

            'updated_by' =>
                Auth::id(),
        ]);

        Notification::make()
            ->title('Konfigurasi berhasil disimpan')
            ->body(
                'Master Promo dan Automated Retention sudah diperbarui.'
            )
            ->success()
            ->send();
    }
}
