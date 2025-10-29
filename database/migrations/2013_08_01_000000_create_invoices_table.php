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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('load_id')->constrained('loads'); // Relação com a carga
            $table->foreignId('dispatcher_id')->constrained('dispatchers');
            $table->foreignId('carrier_id')->nullable()->constrained('carriers'); // Transportadora
            $table->decimal('amount', 10, 2); // Valor total
            $table->decimal('amount_paid', 10, 2)->nullable(); // Valor pago
            $table->date('invoice_date'); // Data de emissão
            $table->date('due_date'); // Data de vencimento
            $table->date('paid_date')->nullable(); // Data de pagamento
            $table->enum('payment_status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
