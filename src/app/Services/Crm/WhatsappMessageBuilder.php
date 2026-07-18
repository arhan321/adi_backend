<?php

declare(strict_types=1);

namespace App\Services\Crm;

use App\Models\Member;
use App\Models\CrmSetting;

class WhatsappMessageBuilder
{
    public function pointAdded(Member $member, int $pointsChange, CrmSetting $setting): string
    {
        return $this->render(
            $setting->point_message_template
                ?: 'Terima kasih Kak {name}. Poin Kakak bertambah {points_change}. Total poin sekarang {total_points}.',
            $member,
            $setting,
            [
                'points_change' => $pointsChange,
            ]
        );
    }

    public function redeemSuccess(Member $member, CrmSetting $setting): string
    {
        return $this->render(
            $setting->redeem_message_template
                ?: 'Selamat Kak {name}, redeem {reward_name} berhasil. Sisa poin Kakak {total_points}.',
            $member,
            $setting
        );
    }

    public function retention(
        Member $member,
        CrmSetting $setting,
        int $daysInactive,
        int $reminderNumber = 1,
        int $pointsEarned = 0,
    ): string {
        return $this->render(
            $setting->retention_message_template
                ?: 'Halo Kak {name}, ini pengingat ke-{reminder_number}. Sudah {days_inactive} hari sejak kunjungan terakhir. Poin Kakak masih ada {total_points}. Yuk mampir lagi!',
            $member,
            $setting,
            [
                'days_inactive' => $daysInactive,
                'reminder_number' => $reminderNumber,
                'points_earned' => $pointsEarned,
            ]
        );
    }

    public function render(
        string $template,
        Member $member,
        CrmSetting $setting,
        array $extra = [],
    ): string {
        $replacements = array_merge([
            'name' => $member->name,
            'phone' => $member->phone,
            'member_code' => $member->member_code ?? '-',
            'total_points' => (int) $member->total_points,
            'reward_name' => $setting->reward_name,
            'redeem_required_points' => (int) $setting->redeem_required_points,
            'retention_days' => (int) $setting->retention_days,
        ], $extra);

        foreach ($replacements as $key => $value) {
            $template = str_replace('{'.$key.'}', (string) $value, $template);
        }

        return trim($template);
    }
}
