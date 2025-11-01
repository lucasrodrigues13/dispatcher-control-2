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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('void_check_path')->nullable();
            $table->string('w9_path')->nullable();
            $table->string('coi_path')->nullable();
            $table->string('proof_fmcsa_path')->nullable();
            $table->string('drivers_license_path')->nullable();
            $table->string('truck_picture_1_path')->nullable();
            $table->string('truck_picture_2_path')->nullable();
            $table->string('truck_picture_3_path')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
