<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RetentionLog extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'member_id',
        'whatsapp_log_id',
        'retention_date',
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

    public function whatsappLog(): BelongsTo
    {
        return $this->belongsTo(WhatsappLog::class);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    protected function casts(): array
    {
        return [
            'retention_date' => 'date',
            'last_visit_at' => 'datetime',
            'days_inactive' => 'integer',
            'sent_at' => 'datetime',
        ];
    }
}
