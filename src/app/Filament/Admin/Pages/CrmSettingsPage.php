<?php

namespace App\Filament\Admin\Pages;

use App\Models\CrmSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class CrmSettingsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings CRM';

    protected static ?string $navigationGroup = 'Kopi Banget CRM';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.admin.pages.crm-settings-page';

    public int $redeem_required_points = 3;

    public string $reward_name = '1 Kopi Gratis';

    public bool $promo_is_active = true;

    public int $retention_days = 14;

    public string $retention_send_time = '07:00';

    public bool $auto_send_whatsapp = true;

    public ?string $point_message_template = null;

    public ?string $redeem_message_template = null;

    public ?string $retention_message_template = null;

    public function mount(): void
    {
        $setting = CrmSetting::current();

        $this->redeem_required_points = (int) $setting->redeem_required_points;
        $this->reward_name = $setting->reward_name;
        $this->promo_is_active = (bool) $setting->promo_is_active;
        $this->retention_days = (int) $setting->retention_days;
        $this->retention_send_time = substr((string) $setting->retention_send_time, 0, 5) ?: '07:00';
        $this->auto_send_whatsapp = (bool) $setting->auto_send_whatsapp;
        $this->point_message_template = $setting->point_message_template;
        $this->redeem_message_template = $setting->redeem_message_template;
        $this->retention_message_template = $setting->retention_message_template;
    }

    public function save(): void
    {
        $this->validate([
            'redeem_required_points' => ['required', 'integer', 'min:1', 'max:100'],
            'reward_name' => ['required', 'string', 'max:100'],
            'promo_is_active' => ['boolean'],
            'retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'retention_send_time' => ['required', 'date_format:H:i'],
            'auto_send_whatsapp' => ['boolean'],
            'point_message_template' => ['nullable', 'string', 'max:1000'],
            'redeem_message_template' => ['nullable', 'string', 'max:1000'],
            'retention_message_template' => ['nullable', 'string', 'max:1000'],
        ]);

        CrmSetting::current()->update([
            'redeem_required_points' => $this->redeem_required_points,
            'reward_name' => $this->reward_name,
            'promo_is_active' => $this->promo_is_active,
            'retention_days' => $this->retention_days,
            'retention_send_time' => $this->retention_send_time . ':00',
            'auto_send_whatsapp' => $this->auto_send_whatsapp,
            'point_message_template' => $this->point_message_template,
            'redeem_message_template' => $this->redeem_message_template,
            'retention_message_template' => $this->retention_message_template,
            'updated_by' => Auth::id(),
        ]);

        Notification::make()
            ->title('Konfigurasi berhasil disimpan')
            ->body('Master Promo dan Automated Retention sudah diperbarui.')
            ->success()
            ->send();
    }
}
