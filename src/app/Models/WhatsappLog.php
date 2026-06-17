<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WhatsappLog extends Model
{
    use HasFactory;

    public const TYPE_POINT_ADDED = 'point_added';

    public const TYPE_REDEEM_SUCCESS = 'redeem_success';

    public const TYPE_RETENTION = 'retention';

    public const TYPE_MANUAL = 'manual';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'member_id',
        'phone',
        'message_type',
        'message_body',
        'provider',
        'provider_message_id',
        'status',
        'error_message',
        'sent_at',
        'failed_at',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function markAsSent(?string $providerMessageId = null): bool
    {
        return $this->update([
            'status' => self::STATUS_SENT,
            'provider_message_id' => $providerMessageId,
            'sent_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $errorMessage): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'failed_at' => now(),
        ]);
    }

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
