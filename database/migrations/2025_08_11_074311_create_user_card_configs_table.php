<?php

// Migration: create_user_card_configs_table.php
// Execute: php artisan make:migration create_user_card_configs_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_card_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('config_type')->default('kanban_card_fields'); // Para futuras expansões
            $table->json('config_data'); // Armazena a configuração em JSON
            $table->timestamps();

            $table->unique(['user_id', 'config_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_card_configs');
    }
};
