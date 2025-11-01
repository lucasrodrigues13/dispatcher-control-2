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
        Schema::create('charges_setups', function (Blueprint $table) {
            $table->id();
            $table->longText('charges_setup_array');
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade');
            $table->foreignId('dispatcher_id')->nullable()->constrained('dispatchers')->onDelete('cascade');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->enum('price', ['price', 'paid amount'])->default('price');

            // Indexes
            $table->index('carrier_id', 'fk_charges_setups_carrier_id');
            $table->index('dispatcher_id', 'fk_charges_setups_dispatcher_id');

            // MySQL JSON validation check (MySQL 5.7+)
            // Note: Laravel doesn't directly support CHECK constraints, 
            // but the database will enforce json_valid() if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges_setups');
    }
};
