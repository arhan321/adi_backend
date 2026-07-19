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
        Schema::create('crm_settings', function (Blueprint $table) {
            $table->id();

            // Master Promo / Lite-Loyalty
            $table->unsignedSmallInteger('redeem_required_points')->default(3);
            $table->string('reward_name')->default('1 Kopi Gratis');
            $table->boolean('promo_is_active')->default(true);

            // Automated Retention
            $table->unsignedSmallInteger('retention_days')->default(14);
            $table->time('retention_send_time')->default('07:00:00');
            $table->boolean('auto_send_whatsapp')->default(true);

            // Template pesan WhatsApp
            $table->text('point_message_template')->nullable();
            $table->text('redeem_message_template')->nullable();
            $table->text('retention_message_template')->nullable();

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_settings');
    }
};
