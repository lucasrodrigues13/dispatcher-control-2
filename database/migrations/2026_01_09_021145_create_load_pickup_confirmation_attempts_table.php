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
        Schema::create('load_pickup_confirmation_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->constrained('loads')->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('job_uuid')->nullable(); // UUID do job enfileirado
            $table->foreignId('confirmation_id')->nullable()->constrained('load_pickup_confirmations')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('error_message')->nullable(); // Mensagem de erro se falhar
            $table->timestamps();
            
            // Índices
            $table->index(['load_id', 'status'], 'idx_attempts_load_status');
            $table->index('status', 'idx_attempts_status');
            $table->index('job_uuid', 'idx_attempts_job_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('load_pickup_confirmation_attempts');
    }
};
