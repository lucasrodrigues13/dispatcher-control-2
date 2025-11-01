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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatcher_id')->constrained('dispatchers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('carrier_id')->constrained('carriers')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('value');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('dispatcher_id', 'fk_dispatcher_deals');
            $table->index('carrier_id', 'fk_carrier_deals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
