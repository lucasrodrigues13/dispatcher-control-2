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
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('invoice_id')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->integer('amount'); // em centavos
            $table->enum('status', ['pending', 'succeeded', 'failed']);
            $table->text('failure_reason')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index('subscription_id');
            $table->index('user_id');
            $table->index('payment_intent_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
