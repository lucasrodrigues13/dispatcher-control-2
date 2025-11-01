<?php

namespace Database\Factories;

use App\Models\Carrier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarrierFactory extends Factory
{
    protected $model = Carrier::class;

    public function definition()
    {
        return [
            'company_name' => $this->faker->company,
            'phone' => $this->faker->phoneNumber,
            'contact_name' => $this->faker->name,
            'about' => $this->faker->paragraph,
            'website' => $this->faker->url,
            'trailer_capacity' => $this->faker->numberBetween(1, 10),
            'is_auto_hauler' => $this->faker->boolean,
            'is_towing' => $this->faker->boolean,
            'is_driveaway' => $this->faker->boolean,
            'contact_phone' => $this->faker->phoneNumber,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr,
            'zip' => $this->faker->postcode,
            'country' => 'USA',
            'mc' => 'MC' . $this->faker->unique()->randomNumber(6),
            'dot' => 'DOT' . $this->faker->unique()->randomNumber(6),
            'ein' => $this->faker->unique()->randomNumber(9),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
              'dispatcher_id' => \App\Models\Dispatcher::inRandomOrder()->first()->id ?? \App\Models\Dispatcher::factory(),
        ];
    }
}
