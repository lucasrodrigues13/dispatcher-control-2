<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Carrier;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Load;
use App\Models\TimeLineCharge;
use App\Models\User;

class DashboardTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates test data for dashboard functionality
     */
    public function run()
    {
        $this->command->info('Seeding dashboard test data...');

        // Create test users if not exist
        $this->createTestUsers();

        // Create test customers
        $customers = $this->createTestCustomers();

        // Create test carriers
        $carriers = $this->createTestCarriers();

        // Create test employees
        $employees = $this->createTestEmployees();

        // Create test drivers
        $drivers = $this->createTestDrivers($carriers);

        // Create test loads with realistic data
        $loads = $this->createTestLoads($carriers, $employees);

        // Create test timeline charges (invoices)
        $this->createTestTimeLineCharges($customers, $carriers, $loads);

        $this->command->info('Dashboard test data seeded successfully!');
    }

    private function createTestUsers()
    {
        $users = [
            [
                'name' => 'Dashboard Admin',
                'email' => 'admin@dashboard.test',
                'password' => bcrypt('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Test Dispatcher',
                'email' => 'dispatcher@dashboard.test',
                'password' => bcrypt('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }
    }

    private function createTestCustomers()
    {
        $customers = [];
        $companies = [
            'ABC Logistics Corp',
            'XYZ Transportation Inc',
            'Global Shipping Solutions',
            'Metro Freight Services',
            'National Transport Co',
            'Express Delivery Systems',
            'Prime Cargo Handlers',
            'Swift Logistics Partners'
        ];

        foreach ($companies as $index => $company) {
            $customer = Customer::firstOrCreate([
                'company_name' => $company
            ], [
                'contact_name' => 'Contact ' . ($index + 1),
                'email' => strtolower(str_replace(' ', '', $company)) . '@test.com',
                'phone' => '555-' . str_pad($index + 1000, 4, '0', STR_PAD_LEFT),
                'created_at' => Carbon::now()->subDays(rand(30, 365)),
                'updated_at' => Carbon::now(),
            ]);
            $customers[] = $customer;
        }

        return collect($customers);
    }

    private function createTestCarriers()
    {
        $carriers = [];
        $user = User::first();

        $companies = [
            ['name' => 'Alpha Transport LLC', 'mc' => 'MC-123456', 'dot' => 'DOT-789012'],
            ['name' => 'Beta Hauling Inc', 'mc' => 'MC-234567', 'dot' => 'DOT-890123'],
            ['name' => 'Gamma Logistics Co', 'mc' => 'MC-345678', 'dot' => 'DOT-901234'],
            ['name' => 'Delta Freight Services', 'mc' => 'MC-456789', 'dot' => 'DOT-012345'],
            ['name' => 'Epsilon Carriers Ltd', 'mc' => 'MC-567890', 'dot' => 'DOT-123456'],
        ];

        foreach ($companies as $index => $company) {
            $carrier = Carrier::firstOrCreate([
                'company_name' => $company['name']
            ], [
                'phone' => '555-' . str_pad($index + 2000, 4, '0', STR_PAD_LEFT),
                'contact_name' => 'Carrier Contact ' . ($index + 1),
                'mc' => $company['mc'],
                'dot' => $company['dot'],
                'user_id' => $user->id,
                'address' => $index + 1 . '00 Carrier St',
                'city' => 'City ' . ($index + 1),
                'state' => 'ST',
                'zip' => '1234' . $index,
                'trailer_capacity' => rand(10, 40),
                'is_auto_hauler' => rand(0, 1),
                'created_at' => Carbon::now()->subDays(rand(60, 400)),
                'updated_at' => Carbon::now(),
            ]);
            $carriers[] = $carrier;
        }

        return collect($carriers);
    }

    private function createTestEmployees()
    {
        $employees = [];
        $user = User::first();

        $names = [
            'John Dispatcher',
            'Sarah Manager',
            'Mike Coordinator',
            'Lisa Supervisor',
            'Tom Analyst'
        ];

        foreach ($names as $index => $name) {
            $employee = Employee::firstOrCreate([
                'user_id' => $user->id,
                'phone' => '555-' . str_pad($index + 3000, 4, '0', STR_PAD_LEFT)
            ], [
                'position' => ['Dispatcher', 'Manager', 'Coordinator', 'Supervisor', 'Analyst'][$index],
                'ssn_tax_id' => 'SSN-' . str_pad($index + 10000, 5, '0', STR_PAD_LEFT),
                'created_at' => Carbon::now()->subDays(rand(90, 500)),
                'updated_at' => Carbon::now(),
            ]);
            $employees[] = $employee;
        }

        return collect($employees);
    }

    private function createTestDrivers($carriers)
    {
        $drivers = [];
        $user = User::first();

        $driverNames = [
            'Driver Mike Johnson',
            'Driver Sarah Smith',
            'Driver Robert Brown',
            'Driver Lisa Davis',
            'Driver Tom Wilson',
            'Driver Anna Garcia',
            'Driver Chris Martinez',
            'Driver Jennifer Lopez'
        ];

        foreach ($driverNames as $index => $name) {
            $carrier = $carriers->random();

            $driver = Driver::firstOrCreate([
                'carrier_id' => $carrier->id,
                'phone' => '555-' . str_pad($index + 4000, 4, '0', STR_PAD_LEFT)
            ], [
                'user_id' => $user->id,
                'ssn_tax_id' => 'DRV-' . str_pad($index + 20000, 5, '0', STR_PAD_LEFT),
                'created_at' => Carbon::now()->subDays(rand(30, 300)),
                'updated_at' => Carbon::now(),
            ]);
            $drivers[] = $driver;
        }

        return collect($drivers);
    }

    private function createTestLoads($carriers, $employees)
    {
        $loads = [];

        // Create loads for the last 6 months with varying patterns
        for ($month = 6; $month >= 0; $month--) {
            $monthStart = Carbon::now()->subMonths($month)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($month)->endOfMonth();

            // Variable load count per month (simulate business fluctuation)
            $loadCount = rand(20, 80);

            for ($i = 0; $i < $loadCount; $i++) {
                $createdAt = Carbon::createFromTimestamp(
                    rand($monthStart->timestamp, $monthEnd->timestamp)
                );

                $carrier = $carriers->random();
                $employee = $employees->random();

                // Generate realistic dates
                $scheduledPickup = $createdAt->copy()->addDays(rand(1, 5));
                $actualPickup = rand(0, 100) < 85 ? $scheduledPickup->copy()->addHours(rand(-12, 24)) : null;
                $scheduledDelivery = $scheduledPickup->copy()->addDays(rand(1, 7));
                $actualDelivery = $actualPickup ? $actualPickup->copy()->addDays(rand(1, 8)) : null;

                // Realistic pricing
                $basePrice = rand(1000, 8000);
                $brokerFeePercent = rand(8, 15) / 100;
                $brokerFee = $basePrice * $brokerFeePercent;
                $driverPay = $basePrice - $brokerFee - rand(50, 200);

                $load = Load::create([
                    'load_id' => 'LD-' . $createdAt->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'carrier_id' => $carrier->id,
                    'employee_id' => $employee->id,
                    'creation_date' => $createdAt,
                    'dispatcher' => $employee->user->name ?? 'System',

                    // Vehicle info
                    'year_make_model' => $this->getRandomVehicle(),
                    'vin' => $this->generateVIN(),
                    'lot_number' => 'LOT-' . rand(10000, 99999),

                    // Pickup info
                    'pickup_name' => 'Pickup Location ' . ($i + 1),
                    'pickup_city' => $this->getRandomCity(),
                    'pickup_state' => $this->getRandomState(),
                    'pickup_zip' => rand(10000, 99999),
                    'scheduled_pickup_date' => $scheduledPickup,
                    'actual_pickup_date' => $actualPickup,
                    'pickup_phone' => '555-' . rand(1000, 9999),

                    // Delivery info
                    'delivery_name' => 'Delivery Location ' . ($i + 1),
                    'delivery_city' => $this->getRandomCity(),
                    'delivery_state' => $this->getRandomState(),
                    'delivery_zip' => rand(10000, 99999),
                    'scheduled_delivery_date' => $scheduledDelivery,
                    'actual_delivery_date' => $actualDelivery,
                    'delivery_phone' => '555-' . rand(1000, 9999),

                    // Financial
                    'price' => $basePrice,
                    'broker_fee' => $brokerFee,
                    'driver_pay' => $driverPay,
                    'paid_amount' => rand(0, 100) < 70 ? $driverPay : 0,
                    'payment_status' => rand(0, 100) < 70 ? 'paid' : 'pending',
                    'payment_method' => ['ACH', 'Check', 'Wire', 'Cash'][rand(0, 3)],

                    // Driver and status
                    'driver' => 'Driver ' . rand(1, 20),
                    'status_move' => $actualDelivery ? 'delivered' : ($actualPickup ? 'in_transit' : 'pending'),

                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $loads[] = $load;
            }
        }

        return collect($loads);
    }

    private function createTestTimeLineCharges($customers, $carriers, $loads)
    {
        // Create timeline charges for the loads
        $charges = [];

        foreach ($loads as $load) {
            // Skip some loads to simulate not all loads are invoiced
            if (rand(0, 100) < 20) continue;

            $customer = $customers->random();
            $carrier = $carriers->random();

            // Invoice date is usually after pickup
            $invoiceDate = $load->actual_pickup_date
                ? $load->actual_pickup_date->copy()->addDays(rand(1, 7))
                : $load->created_at->copy()->addDays(rand(5, 15));

            // Due date is typically 15-30 days after invoice
            $dueDate = $invoiceDate->copy()->addDays(rand(15, 45));

            // Payment status based on due date
            $isPastDue = $dueDate->isPast();
            $paymentStatus = 'pending';

            if ($isPastDue) {
                // 60% of past due invoices are eventually paid
                $paymentStatus = rand(0, 100) < 60 ? 'paid' : 'pending';
            } else {
                // 30% of current invoices are paid early
                $paymentStatus = rand(0, 100) < 30 ? 'paid' : 'pending';
            }

            $charge = TimeLineCharge::create([
                'invoice_id' => 'INV-' . $invoiceDate->format('Ymd') . '-' . rand(1000, 9999),
                'costumer' => $customer->id,
                'price' => $load->price + rand(-200, 500), // Add some variation
                'status_payment' => $paymentStatus,
                'carrier_id' => $carrier->id,
                'date_start' => $invoiceDate,
                'date_end' => $paymentStatus === 'paid'
                    ? $dueDate->copy()->subDays(rand(0, 10))
                    : null,
                'due_date' => $dueDate,
                'payment_terms' => ['net_15', 'net_30', 'net_45'][rand(0, 2)],
                'amount_type' => 'dispatcher_fee',
                'load_ids' => [$load->load_id],
                'invoice_notes' => 'Auto-generated test invoice for load ' . $load->load_id,
                'created_at' => $invoiceDate,
                'updated_at' => $paymentStatus === 'paid'
                    ? $dueDate->copy()->subDays(rand(0, 10))
                    : $invoiceDate,
            ]);

            $charges[] = $charge;
        }

        $this->command->info('Created ' . count($charges) . ' test timeline charges');

        return collect($charges);
    }

    private function getRandomVehicle()
    {
        $vehicles = [
            '2018 Ford F-150',
            '2019 Chevrolet Silverado',
            '2020 RAM 1500',
            '2017 Toyota Tacoma',
            '2021 GMC Sierra',
            '2019 Honda Ridgeline',
            '2018 Nissan Titan',
            '2020 Ford Ranger'
        ];

        return $vehicles[array_rand($vehicles)];
    }

    private function generateVIN()
    {
        $chars = 'ABCDEFGHJKLMNPRSTUVWXYZ1234567890';
        $vin = '';
        for ($i = 0; $i < 17; $i++) {
            $vin .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $vin;
    }

    private function getRandomCity()
    {
        $cities = [
            'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix',
            'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose',
            'Austin', 'Jacksonville', 'San Francisco', 'Columbus', 'Charlotte',
            'Fort Worth', 'Indianapolis', 'Seattle', 'Denver', 'Washington'
        ];

        return $cities[array_rand($cities)];
    }

    private function getRandomState()
    {
        $states = [
            'NY', 'CA', 'TX', 'FL', 'IL', 'PA', 'OH', 'GA', 'NC', 'MI',
            'NJ', 'VA', 'WA', 'AZ', 'MA', 'TN', 'IN', 'MO', 'MD', 'WI'
        ];

        return $states[array_rand($states)];
    }
}
