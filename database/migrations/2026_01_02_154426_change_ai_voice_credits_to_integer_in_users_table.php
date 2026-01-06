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
        DB::statement('UPDATE users SET ai_voice_credits = ROUND(ai_voice_credits * 100) WHERE ai_voice_credits IS NOT NULL');
        
        Schema::table('users', function (Blueprint $table) {
            // Alterar tipo de decimal para integer (centavos)
            // MySQL/MariaDB precisa fazer isso em etapas
            $table->integer('ai_voice_credits')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Converter valores existentes de centavos para dólares antes de alterar
            DB::statement('UPDATE users SET ai_voice_credits = ai_voice_credits / 100.0 WHERE ai_voice_credits IS NOT NULL');
            
            // Alterar tipo de integer para decimal
            $table->decimal('ai_voice_credits', 10, 2)->default(0)->change();
        });
    }
};
