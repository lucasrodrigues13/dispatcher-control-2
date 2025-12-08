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
            $table->enum('kanban_status', ['new', 'assigned', 'picked_up', 'delivered', 'billed', 'paid'])
                  ->default('new')
                  ->after('status_move');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->dropColumn('kanban_status');
        });
    }
};
