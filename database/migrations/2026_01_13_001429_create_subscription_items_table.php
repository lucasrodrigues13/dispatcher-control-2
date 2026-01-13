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
        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->string('stripe_subscription_item_id')->unique();
            $table->string('stripe_price_id');
            $table->enum('item_type', ['main_plan', 'ai_voice_service']);
            $table->integer('quantity')->default(1);
            $table->integer('unit_amount'); // em centavos
            $table->timestamps();
            
            $table->index('subscription_id');
            $table->index('stripe_subscription_item_id');
            $table->index('item_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
    }
};
