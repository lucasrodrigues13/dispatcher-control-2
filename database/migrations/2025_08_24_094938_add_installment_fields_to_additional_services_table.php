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
        Schema::table('additional_services', function (Blueprint $table) {
            $table->boolean('is_installment')->default(false)->after('total');
            $table->enum('installment_type', ['weeks', 'months'])->nullable()->after('is_installment');
            $table->integer('installment_count')->nullable()->after('installment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_services', function (Blueprint $table) {
            $table->dropColumn(['is_installment', 'installment_type', 'installment_count']);
        });
    }
};
