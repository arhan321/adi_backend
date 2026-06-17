<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['earn', 'redeem', 'adjustment'])->default('earn');
            $table->integer('points_change'); // contoh: +1 untuk pembelian, -3 untuk redeem
            $table->unsignedInteger('points_before')->default(0);
            $table->unsignedInteger('points_after')->default(0);
            $table->string('activity_name')->nullable(); // contoh: Pembelian Kopi Susu, Tukar Voucher
            $table->text('description')->nullable();
            $table->timestamp('transaction_at')->useCurrent();
            $table->timestamps();

            $table->index(['member_id', 'transaction_at']);
            $table->index(['type', 'transaction_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
