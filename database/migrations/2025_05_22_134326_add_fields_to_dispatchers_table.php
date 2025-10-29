<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::table('dispatchers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id'); // FK opcional, se quiser permitir dispatcher sem usuário vinculado
            $table->string('type')->default('individual'); // 'individual' ou 'company'
            $table->string('company_name')->nullable();
            $table->string('ein_tax_id')->nullable(); // EIN (para empresas) ou SSN (para pessoa física)
            $table->string('address')->nullable();
            $table->string('phone')->nullable();

            // Relacionamento com a tabela users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('dispatchers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'type',
                'company_name',
                'ein_tax_id',
                'address',
                'phone'
            ]);
        });
    }
};
