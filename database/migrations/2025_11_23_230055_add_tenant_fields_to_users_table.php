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
            // Foreign key auto-referencial: owner_id aponta para users.id
            // Usa 'restrict' para impedir deletar owner se houver usuários vinculados
            // Isso protege contra perda acidental de dados
            $table->foreignId('owner_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->onDelete('restrict'); // Impede deletar owner se houver dependentes
            
            $table->boolean('is_owner')->default(false)->after('owner_id');
            $table->boolean('is_subadmin')->default(false)->after('is_owner');
            
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropIndex(['owner_id']);
            $table->dropColumn(['owner_id', 'is_owner', 'is_subadmin']);
        });
    }
};
