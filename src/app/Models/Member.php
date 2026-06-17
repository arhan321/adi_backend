<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Member extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'member_code',
        'name',
        'phone',
        'birth_date',
        'total_points',
        'last_visit_at',
        'last_redeemed_at',
        'last_retention_sent_at',
        'retention_message_count',
        'status',
        'notes',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function whatsappLogs(): HasMany
    {
        return $this->hasMany(WhatsappLog::class);
    }

    public function retentionLogs(): HasMany
    {
        return $this->hasMany(RetentionLog::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeSearchPhone(Builder $query, ?string $phone): Builder
    {
        return $query->when($phone, function (Builder $query) use ($phone): Builder {
            $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);

            return $query->where('phone', 'like', "%{$normalizedPhone}%");
        });
    }

    public function scopeEligibleForRetention(Builder $query, int $retentionDays): Builder
    {
        return $query
            ->active()
            ->whereNotNull('last_visit_at')
            ->where('last_visit_at', '<=', now()->subDays($retentionDays))
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('last_retention_sent_at')
                    ->orWhereDate('last_retention_sent_at', '<', today());
            });
    }

    public function canRedeem(?int $requiredPoints = null): bool
    {
        $requiredPoints ??= CrmSetting::current()->redeem_required_points;

        return $this->total_points >= $requiredPoints;
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'total_points' => 'integer',
            'last_visit_at' => 'datetime',
            'last_redeemed_at' => 'datetime',
            'last_retention_sent_at' => 'datetime',
            'retention_message_count' => 'integer',
        ];
    }
}
