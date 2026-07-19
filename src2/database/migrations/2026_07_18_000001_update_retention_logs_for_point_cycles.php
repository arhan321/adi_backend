<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Foreign key member_id sebelumnya memakai composite unique index:
         * retention_logs_member_id_retention_date_unique
         *
         * MariaDB tidak mengizinkan index tersebut dihapus sebelum tersedia
         * index lain yang diawali dengan kolom member_id.
         */
        Schema::table('retention_logs', function (Blueprint $table): void {
            $table->index(
                'member_id',
                'retention_logs_member_id_index'
            );
        });

        Schema::table('retention_logs', function (Blueprint $table): void {
            $table->dropUnique(
                'retention_logs_member_id_retention_date_unique'
            );
        });

        Schema::table('retention_logs', function (Blueprint $table): void {
            $table->foreignId('point_transaction_id')
                ->nullable()
                ->after('member_id')
                ->constrained('point_transactions')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('reminder_number')
                ->nullable()
                ->after('point_transaction_id');

            $table->unsignedSmallInteger('points_earned')
                ->default(0)
                ->after('reminder_number');

            $table->timestamp('scheduled_at')
                ->nullable()
                ->after('retention_date');

            $table->timestamp('expires_at')
                ->nullable()
                ->after('scheduled_at');

            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('expires_at');

            $table->unique(
                ['point_transaction_id', 'reminder_number'],
                'retention_point_transaction_reminder_unique'
            );

            $table->index(
                ['status', 'scheduled_at', 'whatsapp_log_id'],
                'retention_due_lookup_index'
            );
        });
    }

    public function down(): void
    {
        /*
         * Hapus data yang memakai skema retention per transaksi supaya
         * unique index lama dapat dipasang kembali tanpa konflik.
         */
        DB::table('retention_logs')
            ->whereNotNull('point_transaction_id')
            ->delete();

        Schema::table('retention_logs', function (Blueprint $table): void {
            $table->dropIndex('retention_due_lookup_index');

            $table->dropUnique(
                'retention_point_transaction_reminder_unique'
            );

            $table->dropConstrainedForeignId('point_transaction_id');

            $table->dropColumn([
                'reminder_number',
                'points_earned',
                'scheduled_at',
                'expires_at',
                'cancelled_at',
            ]);
        });

        /*
         * Pasang kembali composite unique index lama terlebih dahulu.
         * Index ini dapat menopang foreign key member_id.
         */
        Schema::table('retention_logs', function (Blueprint $table): void {
            $table->unique(
                ['member_id', 'retention_date'],
                'retention_logs_member_id_retention_date_unique'
            );
        });

        /*
         * Setelah composite index lama tersedia kembali, index pengganti
         * yang dibuat pada method up() dapat dihapus.
         */
        Schema::table('retention_logs', function (Blueprint $table): void {
            $table->dropIndex('retention_logs_member_id_index');
        });
    }
};