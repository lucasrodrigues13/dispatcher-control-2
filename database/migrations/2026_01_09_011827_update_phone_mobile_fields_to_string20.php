<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update customers table
        if (Schema::hasColumn('customers', 'phone')) {
            DB::statement("ALTER TABLE customers MODIFY COLUMN phone VARCHAR(20) NULL");
        }

        // Update carriers table
        if (Schema::hasColumn('carriers', 'phone')) {
            DB::statement("ALTER TABLE carriers MODIFY COLUMN phone VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('carriers', 'contact_phone')) {
            DB::statement("ALTER TABLE carriers MODIFY COLUMN contact_phone VARCHAR(20) NULL");
        }

        // Update dispatchers table
        if (Schema::hasColumn('dispatchers', 'phone')) {
            DB::statement("ALTER TABLE dispatchers MODIFY COLUMN phone VARCHAR(20) NULL");
        }

        // Update employees table
        if (Schema::hasColumn('employees', 'phone')) {
            DB::statement("ALTER TABLE employees MODIFY COLUMN phone VARCHAR(20) NULL");
        }

        // Update drivers table
        if (Schema::hasColumn('drivers', 'phone')) {
            DB::statement("ALTER TABLE drivers MODIFY COLUMN phone VARCHAR(20) NULL");
        }

        // Update brokers table
        if (Schema::hasColumn('brokers', 'phone')) {
            DB::statement("ALTER TABLE brokers MODIFY COLUMN phone VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('brokers', 'accounting_phone_number')) {
            DB::statement("ALTER TABLE brokers MODIFY COLUMN accounting_phone_number VARCHAR(20) NULL");
        }

        // Update loads table
        if (Schema::hasColumn('loads', 'pickup_phone')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN pickup_phone VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('loads', 'pickup_mobile')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN pickup_mobile VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('loads', 'delivery_phone')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN delivery_phone VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('loads', 'delivery_mobile')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN delivery_mobile VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('loads', 'shipper_phone')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN shipper_phone VARCHAR(20) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert customers
        if (Schema::hasColumn('customers', 'phone')) {
            DB::statement("ALTER TABLE customers MODIFY COLUMN phone VARCHAR(255) NULL");
        }

        // Revert carriers
        if (Schema::hasColumn('carriers', 'phone')) {
            DB::statement("ALTER TABLE carriers MODIFY COLUMN phone VARCHAR(255) NULL");
        }
        if (Schema::hasColumn('carriers', 'contact_phone')) {
            DB::statement("ALTER TABLE carriers MODIFY COLUMN contact_phone VARCHAR(20) NULL");
        }

        // Revert dispatchers
        if (Schema::hasColumn('dispatchers', 'phone')) {
            DB::statement("ALTER TABLE dispatchers MODIFY COLUMN phone VARCHAR(255) NULL");
        }

        // Revert employees
        if (Schema::hasColumn('employees', 'phone')) {
            DB::statement("ALTER TABLE employees MODIFY COLUMN phone VARCHAR(255) NULL");
        }

        // Revert drivers
        if (Schema::hasColumn('drivers', 'phone')) {
            DB::statement("ALTER TABLE drivers MODIFY COLUMN phone VARCHAR(255) NULL");
        }

        // Revert brokers
        if (Schema::hasColumn('brokers', 'phone')) {
            DB::statement("ALTER TABLE brokers MODIFY COLUMN phone VARCHAR(50) NULL");
        }
        if (Schema::hasColumn('brokers', 'accounting_phone_number')) {
            DB::statement("ALTER TABLE brokers MODIFY COLUMN accounting_phone_number VARCHAR(50) NULL");
        }

        // Revert loads
        if (Schema::hasColumn('loads', 'pickup_phone')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN pickup_phone VARCHAR(100) NULL");
        }
        if (Schema::hasColumn('loads', 'pickup_mobile')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN pickup_mobile VARCHAR(50) NULL");
        }
        if (Schema::hasColumn('loads', 'delivery_phone')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN delivery_phone VARCHAR(50) NULL");
        }
        if (Schema::hasColumn('loads', 'delivery_mobile')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN delivery_mobile VARCHAR(50) NULL");
        }
        if (Schema::hasColumn('loads', 'shipper_phone')) {
            DB::statement("ALTER TABLE loads MODIFY COLUMN shipper_phone VARCHAR(50) NULL");
        }
    }
};
