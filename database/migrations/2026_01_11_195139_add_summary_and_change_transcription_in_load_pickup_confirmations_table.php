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
        Schema::table('load_pickup_confirmations', function (Blueprint $table) {
            // Adicionar campo summary
            $table->text('summary')->nullable()->after('special_instructions');
            
            // Adicionar campo transcription (LONGTEXT) - nova coluna
            $table->longText('transcription')->nullable()->after('call_record_url');
        });
        
        // Migrar dados existentes de call_transcription_url para transcription (se houver)
        // Nota: Mantemos call_transcription_url por enquanto para não perder dados
        
        // Remover coluna antiga call_transcription_url (após migrar dados se necessário)
        Schema::table('load_pickup_confirmations', function (Blueprint $table) {
            $table->dropColumn('call_transcription_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('load_pickup_confirmations', function (Blueprint $table) {
            // Recriar coluna antiga call_transcription_url
            $table->string('call_transcription_url')->nullable()->after('call_record_url');
        });
        
        // Remover campos novos
        Schema::table('load_pickup_confirmations', function (Blueprint $table) {
            $table->dropColumn(['summary', 'transcription']);
        });
    }
};
