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
        Schema::table('plans', function (Blueprint $table) {
            // Verificar se as colunas já existem antes de adicionar
            if (!Schema::hasColumn('plans', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('plans', 'max_dispatchers')) {
                $table->integer('max_dispatchers')->default(1)->after('max_carriers');
            }
            
            if (!Schema::hasColumn('plans', 'is_custom')) {
                $table->boolean('is_custom')->default(false)->after('active');
            }
        });

        // Remover índice único antigo de slug e criar novo com user_id
        // Usar DB::statement para evitar problemas com Doctrine
        try {
            DB::statement('ALTER TABLE plans DROP INDEX IF EXISTS plans_slug_unique');
        } catch (\Exception $e) {
            // Ignorar se o índice não existir
        }

        // Criar novo índice único composto
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'slug') && Schema::hasColumn('plans', 'user_id')) {
                $table->unique(['slug', 'user_id'], 'plans_slug_user_id_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Remover índice único composto
            try {
                $table->dropUnique(['slug', 'user_id']);
            } catch (\Exception $e) {
                // Ignorar se não existir
            }

            // Re-adicionar índice único original de slug
            if (Schema::hasColumn('plans', 'slug')) {
                $table->unique('slug');
            }

            // Remover colunas
            if (Schema::hasColumn('plans', 'user_id')) {
                $table->dropForeign(['user_id']);
            }
            
            if (Schema::hasColumn('plans', 'user_id')) {
                $table->dropColumn('user_id');
            }
            
            if (Schema::hasColumn('plans', 'max_dispatchers')) {
                $table->dropColumn('max_dispatchers');
            }
            
            if (Schema::hasColumn('plans', 'is_custom')) {
                $table->dropColumn('is_custom');
            }
        });
    }
};
