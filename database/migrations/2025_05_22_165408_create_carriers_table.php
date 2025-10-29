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
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();

            // Campos principais
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('mc')->nullable(); // Motor Carrier number
            $table->string('dot')->nullable(); // Department of Transportation number
            $table->string('ein')->nullable(); // Tax ID

            // Relacionamentos
            $table->unsignedBigInteger('user_id')->nullable(); // opcional se um carrier puder ser gerenciado diretamente por um user
            $table->unsignedBigInteger('dispatcher_company_id'); // vínculo com dispatcher

            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('dispatcher_company_id')->references('id')->on('dispatchers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carriers');
    }
};
