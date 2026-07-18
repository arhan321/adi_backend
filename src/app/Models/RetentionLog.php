<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class RetentionLog extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'member_id',
        'point_transaction_id',
        'reminder_number',
        'points_earned',
        'whatsapp_log_id',
        'retention_date',
        'scheduled_at',
        'expires_at',
        'cancelled_at',
        'last_visit_at',
        'days_inactive',
        'status',
        'notes',
        'sent_at',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function pointTransaction(): BelongsTo
    {
        return $this->belongsTo(PointTransaction::class);
    }

    public function whatsappLog(): BelongsTo
    {
        return $this->belongsTo(WhatsappLog::class);
    }

    public function scopeDue(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('status', self::STATUS_PENDING)
            ->whereNotNull('point_transaction_id')
            ->whereNull('whatsapp_log_id')
            ->whereNull('cancelled_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->where(function (Builder $query) use ($now): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            });
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function markAsSkipped(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_SKIPPED,
            'cancelled_at' => now(),
            'notes' => $reason,
        ]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'notes' => 'Pesan retention berhasil dikirim melalui Fonnte.',
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => mb_substr($reason, 0, 1000),
        ]);
    }

    protected function casts(): array
    {
        return [
            'retention_date' => 'date',
            'scheduled_at' => 'datetime',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'last_visit_at' => 'datetime',
            'days_inactive' => 'integer',
            'reminder_number' => 'integer',
            'points_earned' => 'integer',
            'sent_at' => 'datetime',
        ];
    }
}
