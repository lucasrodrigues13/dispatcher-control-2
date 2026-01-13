<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->unique()->after('ai_voice_credits');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->unique()->after('payment_gateway_id');
            $table->string('stripe_status')->nullable()->after('stripe_subscription_id');
            $table->timestamp('stripe_current_period_start')->nullable()->after('stripe_status');
            $table->timestamp('stripe_current_period_end')->nullable()->after('stripe_current_period_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_customer_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['stripe_subscription_id', 'stripe_status', 'stripe_current_period_start', 'stripe_current_period_end']);
        });
    }
};
