// database/migrations/create_usage_tracking_table.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usage_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->integer('week');
            $table->integer('loads_count')->default(0);
            $table->integer('carriers_count')->default(0);
            $table->integer('employees_count')->default(0);
            $table->integer('drivers_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'year', 'month', 'week']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_tracking');
    }
};
