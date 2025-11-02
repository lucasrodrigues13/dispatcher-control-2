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
        // Verificar se a tabela plans existe antes de tentar alterar
        if (Schema::hasTable('plans')) {
            Schema::table('plans', function (Blueprint $table) {
                // Verificar se as colunas já existem antes de adicionar
                if (!Schema::hasColumn('plans', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                }
                
                if (!Schema::hasColumn('plans', 'max_dispatchers')) {
                    $table->integer('max_dispatchers')->default(1)->after('max_carriers');
                }
                
                if (!Schema::hasColumn('plans', 'is_custom')) {
                    $table->boolean('is_custom')->default(false)->after('active');
                }

                // Remove existing unique index on slug if it exists
                if (Schema::hasColumn('plans', 'slug')) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $indexesFound = $sm->listTableIndexes('plans');
                    if (isset($indexesFound['plans_slug_unique'])) {
                        $table->dropUnique(['slug']);
                    }
                    // Add a new unique index that includes user_id
                    $table->unique(['slug', 'user_id']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('plans')) {
            Schema::table('plans', function (Blueprint $table) {
                if (Schema::hasColumn('plans', 'slug') && Schema::hasColumn('plans', 'user_id')) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $indexesFound = $sm->listTableIndexes('plans');
                    if (isset($indexesFound['plans_slug_user_id_unique'])) {
                        $table->dropUnique(['slug', 'user_id']);
                    }
                    $table->unique('slug'); // Re-add original unique index
                }

                if (Schema::hasColumn('plans', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn(['user_id', 'max_dispatchers', 'is_custom']);
                }
            });
        }
    }
};
