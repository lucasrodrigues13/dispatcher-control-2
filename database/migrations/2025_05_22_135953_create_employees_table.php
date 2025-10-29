<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('dispatcher_id'); // FK para dispatchers

            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('position')->nullable(); // cargo ou função
            $table->string('ssn_tax_id')->nullable(); // identificação fiscal

            $table->timestamps();

            // Relacionamento com a tabela dispatchers
            $table->foreign('dispatcher_id')
                  ->references('id')->on('dispatchers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
