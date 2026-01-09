<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Helpers\PhoneHelper;

return new class extends Migration
{
    /**
     * Limpa e formata um campo de telefone antes de alterar o tamanho
     */
    private function cleanPhoneField(string $table, string $column): void
    {
        if (!Schema::hasColumn($table, $column)) {
            return;
        }

        // Buscar todos os registros com telefone não nulo e maior que 20 caracteres
        // ou que não estejam no formato correto
        $records = DB::table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->get(['id', $column]);

        foreach ($records as $record) {
            $phone = $record->{$column} ?? null;
            
            if (empty($phone)) {
                continue;
            }
            
            // Se já está no formato correto e tem 20 caracteres ou menos, pular
            if (preg_match('/^\+1\d{10}$/', $phone) && strlen($phone) <= 20) {
                continue;
            }
            
            // Formatar o telefone usando o helper
            $formatted = PhoneHelper::formatPhoneForDatabase($phone, '+1');
            
            // Se não conseguir formatar ou resultar em null, tentar limpar manualmente
            if ($formatted === null || strlen($formatted) > 20) {
                // Tentar extrair apenas os dígitos e formatar manualmente
                $digits = preg_replace('/[^\d]/', '', $phone);
                
                // Se tiver 10 dígitos, formatar como +1XXXXXXXXXX (total 12 caracteres)
                if (strlen($digits) == 10) {
                    $formatted = '+1' . $digits;
                } 
                // Se tiver 11 dígitos começando com 1, formatar como +1XXXXXXXXXX (total 12 caracteres)
                elseif (strlen($digits) == 11 && strpos($digits, '1') === 0) {
                    $formatted = '+' . $digits;
                }
                // Se tiver mais de 11 dígitos, pegar apenas os últimos 10 e formatar
                elseif (strlen($digits) > 11) {
                    $last10 = substr($digits, -10);
                    $formatted = '+1' . $last10;
                }
                // Se não conseguir formatar, limpar o campo
                else {
                    $formatted = null;
                }
            }
            
            // Garantir que o formato final não exceda 20 caracteres
            if ($formatted !== null && strlen($formatted) > 20) {
                $formatted = null;
            }
            
            // Atualizar o registro apenas se o valor mudou
            if ($formatted !== $phone) {
                DB::table($table)
                    ->where('id', $record->id)
                    ->update([$column => $formatted]);
            }
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpar e formatar dados antes de alterar tamanho das colunas
        
        // Update customers table
        if (Schema::hasColumn('customers', 'phone')) {
            $this->cleanPhoneField('customers', 'phone');
            DB::statement("ALTER TABLE customers MODIFY COLUMN phone VARCHAR(20) NULL");
        }

        // Update carriers table
        if (Schema::hasColumn('carriers', 'phone')) {
            $this->cleanPhoneField('carriers', 'phone');
            DB::statement("ALTER TABLE carriers MODIFY COLUMN phone VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('carriers', 'contact_phone')) {
            $this->cleanPhoneField('carriers', 'contact_phone');
            DB::statement("ALTER TABLE carriers MODIFY COLUMN contact_phone VARCHAR(20) NULL");
        }

        // Update dispatchers table
        if (Schema::hasColumn('dispatchers', 'phone')) {
            $this->cleanPhoneField('dispatchers', 'phone');
            DB::statement("ALTER TABLE dispatchers MODIFY COLUMN phone VARCHAR(20) NULL");
        }

        // Update employees table
        if (Schema::hasColumn('employees', 'phone')) {
            $this->cleanPhoneField('employees', 'phone');
            DB::statement("ALTER TABLE employees MODIFY COLUMN phone VARCHAR(20) NULL");
        }

        // Update drivers table
        if (Schema::hasColumn('drivers', 'phone')) {
            $this->cleanPhoneField('drivers', 'phone');
            DB::statement("ALTER TABLE drivers MODIFY COLUMN phone VARCHAR(20) NULL");
        }

        // Update brokers table
        if (Schema::hasColumn('brokers', 'phone')) {
            $this->cleanPhoneField('brokers', 'phone');
            DB::statement("ALTER TABLE brokers MODIFY COLUMN phone VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('brokers', 'accounting_phone_number')) {
            $this->cleanPhoneField('brokers', 'accounting_phone_number');
            DB::statement("ALTER TABLE brokers MODIFY COLUMN accounting_phone_number VARCHAR(20) NULL");
        }

        // Update loads table
        if (Schema::hasColumn('loads', 'pickup_phone')) {
            $this->cleanPhoneField('loads', 'pickup_phone');
            DB::statement("ALTER TABLE loads MODIFY COLUMN pickup_phone VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('loads', 'pickup_mobile')) {
            $this->cleanPhoneField('loads', 'pickup_mobile');
            DB::statement("ALTER TABLE loads MODIFY COLUMN pickup_mobile VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('loads', 'delivery_phone')) {
            $this->cleanPhoneField('loads', 'delivery_phone');
            DB::statement("ALTER TABLE loads MODIFY COLUMN delivery_phone VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('loads', 'delivery_mobile')) {
            $this->cleanPhoneField('loads', 'delivery_mobile');
            DB::statement("ALTER TABLE loads MODIFY COLUMN delivery_mobile VARCHAR(20) NULL");
        }
        if (Schema::hasColumn('loads', 'shipper_phone')) {
            $this->cleanPhoneField('loads', 'shipper_phone');
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
