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
        Schema::table('loads', function (Blueprint $table) {
            $table->enum('pickup_status', ['PENDING', 'READY', 'NOT_READY'])->default('PENDING')->after('kanban_status');
            $table->dateTime('pickup_last_confirmed_at')->nullable()->after('pickup_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->dropColumn(['pickup_status', 'pickup_last_confirmed_at']);
        });
    }
};
