<?php

declare(strict_types=1);

namespace App\Services\Crm;

use App\Models\Member;
use App\Models\PointTransaction;
use App\Models\RetentionLog;
use App\Support\CrmAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class MemberDeletionService
{
    public function delete(int $memberId): string
    {
        /*
         * Service tidak boleh dapat dipanggil oleh guest maupun user
         * yang tidak mempunyai permission hapus member.
         */
        if (
            ! Auth::check()
            || ! CrmAccess::canDeleteMembers(Auth::user())
        ) {
            throw new AuthorizationException(
                'Hanya manajemen dan super admin yang dapat menghapus customer.',
            );
        }

        return DB::transaction(function () use ($memberId): string {
            $member = Member::query()
                ->lockForUpdate()
                ->findOrFail($memberId);

            /*
             * Pastikan seluruh transaksi lama sudah mempunyai snapshot.
             * Snapshot membuat nama, nomor, dan kode member tetap tersedia
             * pada History setelah record members dihapus permanen.
             */
            PointTransaction::query()
                ->where('member_id', $member->id)
                ->whereNull('member_code_snapshot')
                ->update([
                    'member_code_snapshot' => $member->member_code,
                    'updated_at' => now(),
                ]);

            PointTransaction::query()
                ->where('member_id', $member->id)
                ->whereNull('member_name_snapshot')
                ->update([
                    'member_name_snapshot' => $member->name,
                    'updated_at' => now(),
                ]);

            PointTransaction::query()
                ->where('member_id', $member->id)
                ->whereNull('member_phone_snapshot')
                ->update([
                    'member_phone_snapshot' => $member->phone,
                    'updated_at' => now(),
                ]);

            /*
             * Seluruh jadwal retention yang masih pending dihentikan.
             * Record retention tetap disimpan sebagai log, tetapi setelah
             * member dihapus foreign key member_id akan menjadi NULL.
             */
            RetentionLog::query()
                ->where('member_id', $member->id)
                ->where('status', RetentionLog::STATUS_PENDING)
                ->update([
                    'status' => RetentionLog::STATUS_SKIPPED,
                    'cancelled_at' => now(),
                    'notes' => 'Dibatalkan karena customer dihapus permanen.',
                    'updated_at' => now(),
                ]);

            $memberName = (string) $member->name;

            /*
             * Model Member sudah tidak memakai SoftDeletes.
             * Karena itu delete() di bawah menjalankan DELETE permanen.
             *
             * Foreign key point_transactions, retention_logs, dan
             * whatsapp_logs menggunakan nullOnDelete sehingga history
             * serta log tetap tersimpan dan hanya member_id yang menjadi NULL.
             */
            $member->delete();

            return $memberName;
        });
    }
}
