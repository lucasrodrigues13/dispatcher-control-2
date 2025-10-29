// database/migrations/create_payments_table.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->string('status'); // pending, paid, failed, refunded
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->string('gateway_response')->nullable();
            $table->datetime('attempted_at');
            $table->datetime('paid_at')->nullable();
            $table->datetime('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->integer('attempt_count')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
