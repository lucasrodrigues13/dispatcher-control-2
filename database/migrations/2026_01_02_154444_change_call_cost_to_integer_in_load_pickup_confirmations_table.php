<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Converter valores existentes de dólares para centavos
        DB::statement('UPDATE load_pickup_confirmations SET call_cost = ROUND(call_cost * 100) WHERE call_cost IS NOT NULL');
        
        Schema::table('load_pickup_confirmations', function (Blueprint $table) {
            // Alterar tipo de decimal para integer (centavos)
            $table->integer('call_cost')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('load_pickup_confirmations', function (Blueprint $table) {
            // Converter valores existentes de centavos para dólares antes de alterar
            DB::statement('UPDATE load_pickup_confirmations SET call_cost = call_cost / 100.0 WHERE call_cost IS NOT NULL');
            
            // Alterar tipo de integer para decimal
            $table->decimal('call_cost', 10, 2)->nullable()->change();
        });
    }
};
