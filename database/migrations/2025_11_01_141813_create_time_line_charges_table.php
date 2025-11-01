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
        Schema::create('time_line_charges', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->nullable();
            $table->string('costumer')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('status_payment', 45)->nullable();
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade');
            $table->foreignId('dispatcher_id')->constrained('dispatchers')->onDelete('cascade');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->date('due_date')->nullable();
            $table->string('payment_terms', 50)->nullable();
            $table->text('invoice_notes')->nullable();
            $table->enum('amount_type', ['price', 'paid_amount'])->nullable();
            $table->longText('array_type_dates')->nullable(); // JSON stored as text
            $table->longText('load_ids')->nullable(); // JSON stored as text
            $table->longText('load_details')->nullable();

            // Indexes
            $table->index('carrier_id', 'fk_time_line_charges_carrier_id');
            $table->index('dispatcher_id', 'fk_time_line_charges_dispatcher_id');
            $table->index('due_date', 'idx_due_date');
            $table->index(['due_date', 'status_payment'], 'idx_due_date_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_line_charges');
    }
};
