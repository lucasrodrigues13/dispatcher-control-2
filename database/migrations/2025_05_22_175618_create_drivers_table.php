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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('carrier_id'); // FK para o carrier

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('ssn_tax_id')->nullable(); // Pode ser SSN (individual) ou Tax ID (empresa)
            $table->string('email')->nullable();

            $table->timestamps();

            // Chave estrangeira para carriers
            $table->foreign('carrier_id')
                  ->references('id')->on('carriers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('drivers');
    }
};
