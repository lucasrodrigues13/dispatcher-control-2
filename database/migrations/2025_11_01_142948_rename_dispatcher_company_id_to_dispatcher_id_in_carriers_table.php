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
        Schema::table('carriers', function (Blueprint $table) {
            // Remover foreign key antiga
            $table->dropForeign(['dispatcher_company_id']);
            
            // Renomear a coluna
            $table->renameColumn('dispatcher_company_id', 'dispatcher_id');
        });

        Schema::table('carriers', function (Blueprint $table) {
            // Recriar foreign key com o novo nome
            $table->foreign('dispatcher_id')
                  ->references('id')
                  ->on('dispatchers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            // Remover foreign key nova
            $table->dropForeign(['dispatcher_id']);
            
            // Renomear de volta
            $table->renameColumn('dispatcher_id', 'dispatcher_company_id');
        });

        Schema::table('carriers', function (Blueprint $table) {
            // Recriar foreign key antiga
            $table->foreign('dispatcher_company_id')
                  ->references('id')
                  ->on('dispatchers')
                  ->onDelete('cascade');
        });
    }
};
