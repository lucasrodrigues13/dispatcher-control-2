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
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('transaction_type', ['credit', 'debit', 'refund']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->enum('source_type', ['recharge', 'call_consumption', 'admin_adjustment', 'refund']);
            $table->unsignedBigInteger('source_id')->nullable(); // ID relacionado
            $table->string('stripe_payment_intent_id')->nullable(); // Para recargas
            $table->unsignedBigInteger('call_id')->nullable(); // Para consumo de ligações
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('transaction_type');
            $table->index('source_type');
            $table->index('created_at');
            $table->index('stripe_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
