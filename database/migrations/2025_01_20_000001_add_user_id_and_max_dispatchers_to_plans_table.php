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
        Schema::table('plans', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->integer('max_dispatchers')->default(1)->after('max_carriers');
            $table->boolean('is_custom')->default(false)->after('active');

            // Remove existing unique index on slug if it exists
            $table->dropUnique(['slug']);
            // Add a new unique index that includes user_id
            $table->unique(['slug', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropUnique(['slug', 'user_id']);
            $table->unique('slug'); // Re-add original unique index

            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'max_dispatchers', 'is_custom']);
        });
    }
};
