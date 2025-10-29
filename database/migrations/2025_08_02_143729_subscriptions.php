// database/migrations/create_subscriptions_table.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('status'); // active, inactive, blocked, cancelled, trial
            $table->datetime('started_at');
            $table->datetime('expires_at')->nullable();
            $table->datetime('trial_ends_at')->nullable();
            $table->datetime('blocked_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_gateway_id')->nullable(); // ID do gateway (Stripe, etc)
            $table->decimal('amount', 8, 2);
            $table->integer('billing_cycle_day')->default(1); // dia do mês para cobrança
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
