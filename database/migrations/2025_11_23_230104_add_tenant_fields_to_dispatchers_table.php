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
        Schema::table('dispatchers', function (Blueprint $table) {
            // Foreign key para users.id (owner do tenant)
            // Usa 'restrict' para proteger integridade dos dados
            $table->foreignId('owner_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->onDelete('restrict'); // Impede deletar owner se houver dispatchers vinculados
            
            $table->boolean('is_owner')->default(false)->after('owner_id');
            
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatchers', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropIndex(['owner_id']);
            $table->dropColumn(['owner_id', 'is_owner']);
        });
    }
};
