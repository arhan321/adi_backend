<?php

declare(strict_types=1);

namespace App\Services\Crm;

use App\Models\Member;
use App\Models\RetentionLog;
use App\Support\CrmAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class MemberDeletionService
{
    public function delete(int $memberId): string
    {
        if (
            Auth::check()
            && ! CrmAccess::canDeleteMembers(Auth::user())
        ) {
            throw new AuthorizationException(
                'Hanya manajemen dan super admin yang dapat menghapus customer.',
            );
        }

        return DB::transaction(function () use ($memberId): string {
            $member = Member::query()
                ->lockForUpdate()
                ->findOrFail($memberId);

            RetentionLog::query()
                ->where('member_id', $member->id)
                ->where('status', RetentionLog::STATUS_PENDING)
                ->update([
                    'status' => RetentionLog::STATUS_SKIPPED,
                    'cancelled_at' => now(),
                    'notes' => 'Dibatalkan karena customer dihapus.',
                    'updated_at' => now(),
                ]);

            $memberName = (string) $member->name;

            $member->delete();

            return $memberName;
        });
    }
}
