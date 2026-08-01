<?php

declare(strict_types=1);

namespace App\Services\Crm;

use App\Jobs\Crm\SendWhatsappMessageJob;
use App\Models\CrmSetting;
use App\Models\Member;
use App\Models\PointTransaction;
use App\Models\RetentionLog;
use App\Models\WhatsappLog;
use App\Support\CrmAccess;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class MemberPointService
{
    public const POINTS_PER_PURCHASE = 1;

    public function __construct(
        protected WhatsappMessageBuilder $messageBuilder,
    ) {
    }

    public function addPoints(
        Member $member,
        int $points,
        ?int $userId = null,
        string $activityName = 'Pembelian Produk',
        bool $sendWhatsapp = true,
    ): PointTransaction {
        $this->authorizePointManagement();

        if ($points !== self::POINTS_PER_PURCHASE) {
            throw new InvalidArgumentException(
                'Setiap pembelian hanya menambahkan 1 poin.',
            );
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
                'Dibatalkan karena pelanggan kembali dan mendapatkan poin baru.',
            );

            $member->update([
                'total_points' => $pointsAfter,
                'last_visit_at' => $transactionAt,
                'status' => Member::STATUS_ACTIVE,
            ]);

            $transaction = PointTransaction::query()->create([
                'member_id' => $member->id,

                /*
                 * Snapshot mempertahankan identitas pada halaman History
                 * walaupun record member nantinya dihapus permanen.
                 */
                'member_code_snapshot' => $member->member_code,
                'member_name_snapshot' => $member->name,
                'member_phone_snapshot' => $member->phone,

                'user_id' => $userId,
                'type' => PointTransaction::TYPE_EARN,
                'points_change' => $points,
                'points_before' => $pointsBefore,
                'points_after' => $pointsAfter,
                'activity_name' => $activityName ?: 'Pembelian Produk',
                'description' => 'Poin ditambahkan dari pembelian pelanggan.',
                'transaction_at' => $transactionAt,
            ]);

            $this->createRetentionSchedule(
                member: $member,
                transaction: $transaction,
                intervalDays: max(1, (int) $setting->retention_days),
                lastVisitAt: $transactionAt,
            );

            if ($sendWhatsapp && $setting->auto_send_whatsapp) {
                $freshMember = $member->refresh();

                $this->queueWhatsapp(
                    $freshMember,
                    WhatsappLog::TYPE_POINT_ADDED,
                    $this->messageBuilder->pointAdded(
                        $freshMember,
                        $points,
                        $setting,
                    ),
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
        $this->authorizePointManagement();

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
                throw new InvalidArgumentException(
                    'Poin customer belum cukup untuk redeem.',
                );
            }

            $transactionAt = CarbonImmutable::now();
            $pointsBefore = (int) $member->total_points;
            $pointsAfter = max(0, $pointsBefore - $requiredPoints);

            // Redeem juga berarti pelanggan kembali. Hentikan siklus lama.
            $this->cancelPendingRetentionCycles(
                $member->id,
                'Dibatalkan karena pelanggan kembali dan melakukan redeem.',
            );

            $member->update([
                'total_points' => $pointsAfter,
                'last_visit_at' => $transactionAt,
                'last_redeemed_at' => $transactionAt,
                'status' => Member::STATUS_ACTIVE,
            ]);

            $transaction = PointTransaction::query()->create([
                'member_id' => $member->id,

                /*
                 * Snapshot mempertahankan identitas pada halaman History
                 * walaupun record member nantinya dihapus permanen.
                 */
                'member_code_snapshot' => $member->member_code,
                'member_name_snapshot' => $member->name,
                'member_phone_snapshot' => $member->phone,

                'user_id' => $userId,
                'type' => PointTransaction::TYPE_REDEEM,
                'points_change' => -$requiredPoints,
                'points_before' => $pointsBefore,
                'points_after' => $pointsAfter,
                'activity_name' => 'Redeem '.$setting->reward_name,
                'description' => 'Customer menukarkan poin dengan reward.',
                'transaction_at' => $transactionAt,
            ]);

            if ($sendWhatsapp && $setting->auto_send_whatsapp) {
                $freshMember = $member->refresh();

                $this->queueWhatsapp(
                    $freshMember,
                    WhatsappLog::TYPE_REDEEM_SUCCESS,
                    $this->messageBuilder->redeemSuccess(
                        $freshMember,
                        $setting,
                    ),
                );
            }

            return $transaction;
        });
    }

    public function queueWhatsapp(
        Member $member,
        string $type,
        string $message,
    ): WhatsappLog {
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

    private function createRetentionSchedule(
        Member $member,
        PointTransaction $transaction,
        int $intervalDays,
        CarbonImmutable $lastVisitAt,
    ): void {
        $scheduledAt = $lastVisitAt->addDays($intervalDays);

        RetentionLog::query()->create([
            'member_id' => $member->id,
            'point_transaction_id' => $transaction->id,
            'reminder_number' => 1,
            'points_earned' => self::POINTS_PER_PURCHASE,
            'retention_date' => $scheduledAt->toDateString(),
            'scheduled_at' => $scheduledAt,
            'expires_at' => null,
            'last_visit_at' => $lastVisitAt,
            'days_inactive' => $intervalDays,
            'status' => RetentionLog::STATUS_PENDING,
            'notes' => sprintf(
                'Satu pengingat retention dijadwalkan %d hari setelah pembelian.',
                $intervalDays,
            ),
        ]);
    }

    private function cancelPendingRetentionCycles(
        int $memberId,
        string $reason,
    ): void {
        RetentionLog::query()
            ->where('member_id', $memberId)
            ->where('status', RetentionLog::STATUS_PENDING)
            ->update([
                'status' => RetentionLog::STATUS_SKIPPED,
                'cancelled_at' => now(),
                'notes' => $reason,
                'updated_at' => now(),
            ]);
    }

    private function authorizePointManagement(): void
    {
        if (
            Auth::check()
            && ! CrmAccess::canManagePoints(Auth::user())
        ) {
            throw new AuthorizationException(
                'Anda tidak memiliki akses untuk mengelola poin customer.',
            );
        }
    }
}
