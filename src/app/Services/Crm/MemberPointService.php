<?php

declare(strict_types=1);

namespace App\Services\Crm;

use App\Models\Member;
use App\Models\CrmSetting;
use App\Models\WhatsappLog;
use Carbon\CarbonImmutable;
use App\Models\RetentionLog;
use InvalidArgumentException;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use App\Jobs\Crm\SendWhatsappMessageJob;

class MemberPointService
{
    public function __construct(protected WhatsappMessageBuilder $messageBuilder)
    {
    }

    public function addPoints(
        Member $member,
        int $points,
        ?int $userId = null,
        string $activityName = 'Pembelian Produk',
        bool $sendWhatsapp = true,
    ): PointTransaction {
        if ($points < 1) {
            throw new InvalidArgumentException('Jumlah poin minimal 1.');
        }

        $setting = CrmSetting::current();

        return DB::transaction(function () use (
            $member,
            $points,
            $userId,
            $activityName,
            $sendWhatsapp,
            $setting,
        ): PointTransaction {
            $member = Member::query()
                ->lockForUpdate()
                ->findOrFail($member->id);

            $transactionAt = CarbonImmutable::now();
            $pointsBefore = (int) $member->total_points;
            $pointsAfter = $pointsBefore + $points;

            // Pelanggan datang kembali. Siklus retention lama harus berhenti.
            $this->cancelPendingRetentionCycles(
                $member->id,
                'Dibatalkan karena pelanggan kembali dan mendapatkan poin baru.'
            );

            $member->update([
                'total_points' => $pointsAfter,
                'last_visit_at' => $transactionAt,
                'status' => Member::STATUS_ACTIVE,
            ]);

            $transaction = PointTransaction::query()->create([
                'member_id' => $member->id,
                'user_id' => $userId,
                'type' => PointTransaction::TYPE_EARN,
                'points_change' => $points,
                'points_before' => $pointsBefore,
                'points_after' => $pointsAfter,
                'activity_name' => $activityName ?: 'Pembelian Produk',
                'description' => 'Poin ditambahkan dari pembelian pelanggan.',
                'transaction_at' => $transactionAt,
            ]);

            $this->createRetentionCycle(
                member: $member,
                transaction: $transaction,
                pointsEarned: $points,
                intervalDays: max(1, (int) $setting->retention_days),
                cycleStartedAt: $transactionAt,
            );

            if ($sendWhatsapp && $setting->auto_send_whatsapp) {
                $freshMember = $member->refresh();

                $this->queueWhatsapp(
                    $freshMember,
                    WhatsappLog::TYPE_POINT_ADDED,
                    $this->messageBuilder->pointAdded($freshMember, $points, $setting),
                );
            }

            return $transaction;
        });
    }

    public function redeem(
        Member $member,
        ?int $userId = null,
        bool $sendWhatsapp = true,
    ): PointTransaction {
        $setting = CrmSetting::current();

        if (! $setting->promo_is_active) {
            throw new InvalidArgumentException('Promo sedang tidak aktif.');
        }

        return DB::transaction(function () use (
            $member,
            $userId,
            $sendWhatsapp,
            $setting,
        ): PointTransaction {
            $member = Member::query()
                ->lockForUpdate()
                ->findOrFail($member->id);

            $requiredPoints = (int) $setting->redeem_required_points;

            if ($member->total_points < $requiredPoints) {
                throw new InvalidArgumentException('Poin member belum cukup untuk redeem.');
            }

            $transactionAt = CarbonImmutable::now();
            $pointsBefore = (int) $member->total_points;
            $pointsAfter = max(0, $pointsBefore - $requiredPoints);

            // Redeem juga berarti pelanggan kembali. Hentikan siklus lama.
            $this->cancelPendingRetentionCycles(
                $member->id,
                'Dibatalkan karena pelanggan kembali dan melakukan redeem.'
            );

            $member->update([
                'total_points' => $pointsAfter,
                'last_visit_at' => $transactionAt,
                'last_redeemed_at' => $transactionAt,
                'status' => Member::STATUS_ACTIVE,
            ]);

            $transaction = PointTransaction::query()->create([
                'member_id' => $member->id,
                'user_id' => $userId,
                'type' => PointTransaction::TYPE_REDEEM,
                'points_change' => -$requiredPoints,
                'points_before' => $pointsBefore,
                'points_after' => $pointsAfter,
                'activity_name' => 'Redeem '.$setting->reward_name,
                'description' => 'Member menukarkan poin dengan reward.',
                'transaction_at' => $transactionAt,
            ]);

            if ($sendWhatsapp && $setting->auto_send_whatsapp) {
                $freshMember = $member->refresh();

                $this->queueWhatsapp(
                    $freshMember,
                    WhatsappLog::TYPE_REDEEM_SUCCESS,
                    $this->messageBuilder->redeemSuccess($freshMember, $setting),
                );
            }

            return $transaction;
        });
    }

    public function queueWhatsapp(Member $member, string $type, string $message): WhatsappLog
    {
        $log = WhatsappLog::query()->create([
            'member_id' => $member->id,
            'phone' => $member->phone,
            'message_type' => $type,
            'message_body' => $message,
            'provider' => 'fonnte',
            'status' => WhatsappLog::STATUS_PENDING,
        ]);

        SendWhatsappMessageJob::dispatch($log->id)->afterCommit();

        return $log;
    }

    private function createRetentionCycle(
        Member $member,
        PointTransaction $transaction,
        int $pointsEarned,
        int $intervalDays,
        CarbonImmutable $cycleStartedAt,
    ): void {
        // Satu poin mewakili satu periode 14 hari.
        // +3 poin menghasilkan pengingat hari ke-14 dan ke-28.
        // Hari ke-42 adalah akhir siklus tanpa pengingat baru.
        $reminderCount = max(0, $pointsEarned - 1);
        $expiresAt = $cycleStartedAt->addDays($pointsEarned * $intervalDays);

        for ($reminderNumber = 1; $reminderNumber <= $reminderCount; $reminderNumber++) {
            $daysInactive = $reminderNumber * $intervalDays;
            $scheduledAt = $cycleStartedAt->addDays($daysInactive);

            RetentionLog::query()->create([
                'member_id' => $member->id,
                'point_transaction_id' => $transaction->id,
                'reminder_number' => $reminderNumber,
                'points_earned' => $pointsEarned,
                'retention_date' => $scheduledAt->toDateString(),
                'scheduled_at' => $scheduledAt,
                'expires_at' => $expiresAt,
                'last_visit_at' => $cycleStartedAt,
                'days_inactive' => $daysInactive,
                'status' => RetentionLog::STATUS_PENDING,
                'notes' => sprintf(
                    'Pengingat ke-%d dari siklus %d poin. Siklus berakhir setelah %d hari.',
                    $reminderNumber,
                    $pointsEarned,
                    $pointsEarned * $intervalDays,
                ),
            ]);
        }
    }

    private function cancelPendingRetentionCycles(int $memberId, string $reason): void
    {
        RetentionLog::query()
            ->where('member_id', $memberId)
            ->whereNotNull('point_transaction_id')
            ->where('status', RetentionLog::STATUS_PENDING)
            ->update([
                'status' => RetentionLog::STATUS_SKIPPED,
                'cancelled_at' => now(),
                'notes' => $reason,
                'updated_at' => now(),
            ]);
    }
}
