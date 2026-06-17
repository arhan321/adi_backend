<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CrmSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'redeem_required_points',
        'reward_name',
        'promo_is_active',
        'retention_days',
        'retention_send_time',
        'auto_send_whatsapp',
        'point_message_template',
        'redeem_message_template',
        'retention_message_template',
        'updated_by',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['id' => 1],
            [
                'redeem_required_points' => 3,
                'reward_name' => '1 Kopi Gratis',
                'promo_is_active' => true,
                'retention_days' => 14,
                'retention_send_time' => '07:00:00',
                'auto_send_whatsapp' => true,
                'point_message_template' => 'Terima kasih Kak {name}. Poin Kakak bertambah {points_change}. Total poin sekarang {total_points}.',
                'redeem_message_template' => 'Selamat Kak {name}, redeem {reward_name} berhasil. Sisa poin Kakak {total_points}.',
                'retention_message_template' => 'Halo Kak {name}, sudah lama tidak mampir ke Kopi Banget. Poin Kakak masih ada {total_points}. Yuk mampir lagi!',
            ]
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected function casts(): array
    {
        return [
            'redeem_required_points' => 'integer',
            'promo_is_active' => 'boolean',
            'retention_days' => 'integer',
            'auto_send_whatsapp' => 'boolean',
        ];
    }
}
