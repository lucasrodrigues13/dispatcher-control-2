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
        Schema::create('containers_loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained('containers')->onDelete('cascade');
            $table->foreignId('load_id')->constrained('loads')->onDelete('cascade');
            $table->integer('position');
            $table->timestamp('moved_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Indexes
            $table->index('container_id', 'fk_containers_loads_container_id');
            $table->index('load_id', 'fk_containers_loads_load_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('containers_loads');
    }
};
