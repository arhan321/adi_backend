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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code', 30)->unique()->nullable();
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->date('birth_date')->nullable();
            $table->unsignedInteger('total_points')->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamp('last_redeemed_at')->nullable();
            $table->timestamp('last_retention_sent_at')->nullable();
            $table->unsignedSmallInteger('retention_message_count')->default(0);
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'last_visit_at']);
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
