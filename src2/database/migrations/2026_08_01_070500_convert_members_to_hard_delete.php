<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Tambahkan snapshot identitas ke transaksi poin.
         * Kolom ini mempertahankan informasi member pada halaman History
         * setelah record utama members dihapus permanen.
         */
        if (Schema::hasTable('point_transactions')) {
            Schema::table('point_transactions', function (Blueprint $table): void {
                if (! Schema::hasColumn('point_transactions', 'member_code_snapshot')) {
                    $table->string('member_code_snapshot', 30)->nullable();
                }

                if (! Schema::hasColumn('point_transactions', 'member_name_snapshot')) {
                    $table->string('member_name_snapshot')->nullable();
                }

                if (! Schema::hasColumn('point_transactions', 'member_phone_snapshot')) {
                    $table->string('member_phone_snapshot', 20)->nullable();
                }
            });

            /*
             * Backfill seluruh transaksi lama sebelum member soft-deleted
             * dihapus permanen.
             */
            DB::table('members')
                ->select(['id', 'member_code', 'name', 'phone'])
                ->orderBy('id')
                ->chunkById(100, function ($members): void {
                    foreach ($members as $member) {
                        DB::table('point_transactions')
                            ->where('member_id', $member->id)
                            ->update([
                                'member_code_snapshot' => $member->member_code,
                                'member_name_snapshot' => $member->name,
                                'member_phone_snapshot' => $member->phone,
                            ]);
                    }
                });

            /*
             * History transaksi tidak boleh ikut terhapus.
             * Ubah relasi dari cascadeOnDelete menjadi nullOnDelete.
             */
            Schema::table('point_transactions', function (Blueprint $table): void {
                $table->dropForeign(['member_id']);
            });

            Schema::table('point_transactions', function (Blueprint $table): void {
                $table->unsignedBigInteger('member_id')->nullable()->change();
            });

            Schema::table('point_transactions', function (Blueprint $table): void {
                $table->foreign('member_id')
                    ->references('id')
                    ->on('members')
                    ->nullOnDelete();
            });
        }

        /*
         * Log retention juga dipertahankan sebagai audit log.
         * Setelah member dihapus, member_id berubah menjadi NULL.
         */
        if (Schema::hasTable('retention_logs')) {
            Schema::table('retention_logs', function (Blueprint $table): void {
                $table->dropForeign(['member_id']);
            });

            Schema::table('retention_logs', function (Blueprint $table): void {
                $table->unsignedBigInteger('member_id')->nullable()->change();
            });

            Schema::table('retention_logs', function (Blueprint $table): void {
                $table->foreign('member_id')
                    ->references('id')
                    ->on('members')
                    ->nullOnDelete();
            });
        }

        /*
         * whatsapp_logs sejak migration awal sudah nullable + nullOnDelete,
         * sehingga log WhatsApp tetap tersimpan tanpa perubahan tambahan.
         */

        /*
         * Record yang sebelumnya sudah terkena soft delete benar-benar
         * dihapus sebelum kolom deleted_at dibuang. Foreign key yang sudah
         * diubah di atas menjaga history dan log tetap tersimpan.
         */
        if (
            Schema::hasTable('members')
            && Schema::hasColumn('members', 'deleted_at')
        ) {
            DB::table('members')
                ->whereNotNull('deleted_at')
                ->delete();

            Schema::table('members', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }

    public function down(): void
    {
        /*
         * Penghapusan permanen tidak dapat mengembalikan record members
         * yang sudah dihapus. Rollback hanya mengembalikan kolom deleted_at.
         *
         * Snapshot dan foreign key nullOnDelete sengaja dipertahankan agar
         * history lama yang member_id-nya sudah NULL tidak rusak.
         */
        if (
            Schema::hasTable('members')
            && ! Schema::hasColumn('members', 'deleted_at')
        ) {
            Schema::table('members', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }
};
