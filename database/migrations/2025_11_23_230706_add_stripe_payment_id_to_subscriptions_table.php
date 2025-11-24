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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Adicionar coluna stripe_payment_id se não existir
            if (!Schema::hasColumn('subscriptions', 'stripe_payment_id')) {
                $table->string('stripe_payment_id')->nullable()->after('payment_gateway_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'stripe_payment_id')) {
                $table->dropColumn('stripe_payment_id');
            }
        });
    }
};
