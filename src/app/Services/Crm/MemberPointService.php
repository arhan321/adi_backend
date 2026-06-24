<?php

namespace App\Services\Crm;

use App\Jobs\Crm\SendWhatsappMessageJob;
use App\Models\CrmSetting;
use App\Models\Member;
use App\Models\PointTransaction;
use App\Models\WhatsappLog;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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

        return DB::transaction(function () use ($member, $points, $userId, $activityName, $sendWhatsapp, $setting): PointTransaction {
            $member = Member::query()->lockForUpdate()->findOrFail($member->id);

            $pointsBefore = (int) $member->total_points;
            $pointsAfter = $pointsBefore + $points;

            $member->update([
                'total_points' => $pointsAfter,
                'last_visit_at' => now(),
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
                'transaction_at' => now(),
            ]);

            if ($sendWhatsapp && $setting->auto_send_whatsapp) {
                $this->queueWhatsapp(
                    $member->refresh(),
                    WhatsappLog::TYPE_POINT_ADDED,
                    $this->messageBuilder->pointAdded($member, $points, $setting),
                );
            }

            return $transaction;
        });
    }

    public function redeem(Member $member, ?int $userId = null, bool $sendWhatsapp = true): PointTransaction
    {
        $setting = CrmSetting::current();

        if (! $setting->promo_is_active) {
            throw new InvalidArgumentException('Promo sedang tidak aktif.');
        }

        return DB::transaction(function () use ($member, $userId, $sendWhatsapp, $setting): PointTransaction {
            $member = Member::query()->lockForUpdate()->findOrFail($member->id);
            $requiredPoints = (int) $setting->redeem_required_points;

            if ($member->total_points < $requiredPoints) {
                throw new InvalidArgumentException('Poin member belum cukup untuk redeem.');
            }

            $pointsBefore = (int) $member->total_points;
            $pointsAfter = max(0, $pointsBefore - $requiredPoints);

            $member->update([
                'total_points' => $pointsAfter,
                'last_visit_at' => now(),
                'last_redeemed_at' => now(),
                'status' => Member::STATUS_ACTIVE,
            ]);

            $transaction = PointTransaction::query()->create([
                'member_id' => $member->id,
                'user_id' => $userId,
                'type' => PointTransaction::TYPE_REDEEM,
                'points_change' => -$requiredPoints,
                'points_before' => $pointsBefore,
                'points_after' => $pointsAfter,
                'activity_name' => 'Redeem ' . $setting->reward_name,
                'description' => 'Member menukarkan poin dengan reward.',
                'transaction_at' => now(),
            ]);

            if ($sendWhatsapp && $setting->auto_send_whatsapp) {
                $this->queueWhatsapp(
                    $member->refresh(),
                    WhatsappLog::TYPE_REDEEM_SUCCESS,
                    $this->messageBuilder->redeemSuccess($member, $setting),
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

        SendWhatsappMessageJob::dispatch($log->id);

        return $log;
    }
}
