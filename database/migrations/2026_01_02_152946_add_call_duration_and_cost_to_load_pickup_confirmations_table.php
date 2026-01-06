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
        Schema::table('load_pickup_confirmations', function (Blueprint $table) {
            if (!Schema::hasColumn('load_pickup_confirmations', 'call_duration')) {
                $table->integer('call_duration')->nullable()->comment('Duration in seconds')->after('vapi_call_status');
            }
            if (!Schema::hasColumn('load_pickup_confirmations', 'call_cost')) {
                $table->decimal('call_cost', 10, 2)->nullable()->comment('Cost in USD')->after('call_duration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('load_pickup_confirmations', function (Blueprint $table) {
            if (Schema::hasColumn('load_pickup_confirmations', 'call_cost')) {
                $table->dropColumn('call_cost');
            }
            if (Schema::hasColumn('load_pickup_confirmations', 'call_duration')) {
                $table->dropColumn('call_duration');
            }
        });
    }
};
