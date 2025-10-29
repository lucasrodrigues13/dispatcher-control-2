<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    public function run()
    {
        // Cria 10 carriers com dados aleatórios
        Carrier::factory()->count(10)->create();

        // Ou para criar com dados específicos:
        Carrier::create([
            'company_name' => 'Transportadora Exemplo',
            'phone' => '(99) 9999-9999',
            'mc' => 'MC123456',
            // ... outros campos
        ]);
    }
}
