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
        Schema::create('load_pickup_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->constrained('loads')->onDelete('cascade');
            
            $table->string('contact_name')->nullable();
            $table->boolean('car_ready_for_pickup')->default(false);
            $table->dateTime('not_ready_when')->nullable();
            $table->string('hours_of_operation')->nullable();
            $table->string('car_condition')->nullable();
            $table->boolean('is_address_correct')->default(true);
            
            // Endereço alternativo (só preenchido se is_address_correct = false)
            $table->string('pickup_address')->nullable();
            $table->string('pickup_city')->nullable();
            $table->string('pickup_state')->nullable();
            $table->string('pickup_zip')->nullable();
            
            $table->text('special_instructions')->nullable();
            $table->string('call_record_url')->nullable();
            $table->string('call_transcription_url')->nullable();
            $table->string('vapi_call_id')->unique();
            $table->string('vapi_call_status'); // success | fail
            $table->json('raw_payload'); // JSON completo recebido pelo webhook
            
            $table->timestamps();
            
            // Índice recomendado
            $table->index(['load_id', 'created_at'], 'idx_load_pickup_confirmations_load_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('load_pickup_confirmations');
    }
};
