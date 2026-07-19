<?php

declare(strict_types=1);

use App\Models\Member;
use App\Models\RetentionLog;
use App\Models\PointTransaction;
use App\Services\Crm\MemberDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deleting a member keeps transaction history and cancels pending retention', function () {
    $member = Member::query()->create([
        'member_code' => 'KB-DELETE-TEST',
        'name' => 'Member Delete Test',
        'phone' => '6281234567890',
        'total_points' => 1,
        'last_visit_at' => now(),
        'status' => Member::STATUS_ACTIVE,
    ]);

    $transaction = PointTransaction::query()->create([
        'member_id' => $member->id,
        'type' => PointTransaction::TYPE_EARN,
        'points_change' => 1,
        'points_before' => 0,
        'points_after' => 1,
        'activity_name' => 'Pembelian Produk',
        'transaction_at' => now(),
    ]);

    $retentionLog = RetentionLog::query()->create([
        'member_id' => $member->id,
        'point_transaction_id' => $transaction->id,
        'reminder_number' => 1,
        'points_earned' => 1,
        'retention_date' => today()->addDays(14),
        'scheduled_at' => now()->addDays(14),
        'expires_at' => now()->addDays(28),
        'last_visit_at' => now(),
        'days_inactive' => 14,
        'status' => RetentionLog::STATUS_PENDING,
    ]);

    $deletedMemberName = app(MemberDeletionService::class)->delete($member->id);

    expect($deletedMemberName)->toBe('Member Delete Test');

    $this->assertSoftDeleted('members', [
        'id' => $member->id,
    ]);

    $retentionLog->refresh();
    $transaction->refresh();

    expect($retentionLog->status)
        ->toBe(RetentionLog::STATUS_SKIPPED)
        ->and($retentionLog->cancelled_at)
        ->not->toBeNull()
        ->and($transaction->member?->name)
        ->toBe('Member Delete Test');
});
