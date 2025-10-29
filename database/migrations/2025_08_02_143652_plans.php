// database/migrations/create_plans_table.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 8, 2);
            $table->integer('max_loads_per_month')->nullable();
            $table->integer('max_loads_per_week')->nullable();
            $table->integer('max_carriers')->default(1);
            $table->integer('max_employees')->default(0);
            $table->integer('max_drivers')->default(0);
            $table->boolean('is_trial')->default(false);
            $table->integer('trial_days')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
