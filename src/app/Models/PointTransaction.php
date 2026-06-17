<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PointTransaction extends Model
{
    use HasFactory;

    public const TYPE_EARN = 'earn';

    public const TYPE_REDEEM = 'redeem';

    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'member_id',
        'user_id',
        'type',
        'points_change',
        'points_before',
        'points_after',
        'activity_name',
        'description',
        'transaction_at',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEarn(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EARN);
    }

    public function scopeRedeem(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_REDEEM);
    }

    public function getFormattedPointsChangeAttribute(): string
    {
        return $this->points_change > 0
            ? '+'.$this->points_change.' Poin'
            : $this->points_change.' Poin';
    }

    protected function casts(): array
    {
        return [
            'points_change' => 'integer',
            'points_before' => 'integer',
            'points_after' => 'integer',
            'transaction_at' => 'datetime',
        ];
    }
}
