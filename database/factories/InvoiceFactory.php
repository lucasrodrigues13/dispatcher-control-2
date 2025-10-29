<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Load;
use App\Models\Dispatcher;
use App\Models\Carrier;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'invoice_number' => 'INV-' . $this->faker->unique()->randomNumber(6),
           'load_id' => Load::inRandomOrder()->first()->id ?? Load::factory(),
            'dispatcher_id' => Dispatcher::inRandomOrder()->first()->id ?? Dispatcher::factory(),
            'carrier_id' => Carrier::inRandomOrder()->first()->id ?? null,
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'amount_paid' => $this->faker->optional(0.3)->randomFloat(2, 0, 10000), // 30% chance de ter valor
            'invoice_date' => $this->faker->dateTimeBetween('-3 months'),
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'paid_date' => $this->faker->optional(0.4)->dateTimeBetween('-1 month'), // 40% chance de estar pago
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'overdue']),
            'notes' => $this->faker->optional(0.7)->sentence(), // 70% chance de ter notas
        ];
    }
}
