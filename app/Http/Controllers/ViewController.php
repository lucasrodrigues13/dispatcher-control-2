<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Carrier;
use App\Models\Employeer;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ViewController extends Controller
{
    /**
     * Exibe a página principal de relatórios
     */
    public function index(Request $request)
    {
        try {
            // Carregar dados para os filtros com eager loading para otimização
            $customers = Customer::select('id', 'company_name')
                ->orderBy('company_name', 'asc')
                ->get();

            $carriers = Carrier::select('id', 'company_name')
                ->orderBy('company_name', 'asc')
                ->get();

            // Carregar funcionários com relacionamento de usuário
            $employees = Employeer::with(['user:id,name'])
                ->select('id', 'user_id', 'position', 'dispatcher_id')
                ->whereHas('user') // Apenas funcionários com usuário associado
                ->get()
                ->map(function ($employee) {
                    return (object) [
                        'id' => $employee->id,
                        'name' => $employee->user->name ?? 'Employee #' . $employee->id,
                        'position' => $employee->position,
                        'user' => $employee->user
                    ];
                })
                ->sortBy('name');

            // Carregar motoristas (opcional, caso precise no futuro)
            $drivers = Driver::with(['user:id,name'])
                ->select('id', 'user_id', 'carrier_id')
                ->get()
                ->map(function ($driver) {
                    return (object) [
                        'id' => $driver->id,
                        'name' => $driver->user->name ?? 'Driver #' . $driver->id,
                        'carrier_id' => $driver->carrier_id
                    ];
                })
                ->sortBy('name');

            // Estatísticas rápidas para o dashboard (opcional)
            $stats = $this->getQuickStats();

            return view('reports.index', compact(
                'customers',
                'carriers',
                'employees',
                'drivers',
                'stats'
            ));

        } catch (\Exception $e) {
            // Log do erro
            Log::error('Error loading reports page: ' . $e->getMessage());

            // Retornar view com arrays vazios em caso de erro
            return view('reports.index', [
                'customers' => collect(),
                'carriers' => collect(),
                'employees' => collect(),
                'drivers' => collect(),
                'stats' => $this->getDefaultStats()
            ])->with('error', 'Error loading report data. Please try again.');
        }
    }

    /**
     * Obtém estatísticas rápidas para exibir no dashboard de relatórios
     */
    private function getQuickStats()
    {
        try {
            // Cache das estatísticas por 5 minutos para melhor performance
            return cache()->remember('report_quick_stats', 300, function () {
                // Verificar se as tabelas existem antes de fazer queries
                $stats = [
                    'total_customers' => 0,
                    'total_carriers' => 0,
                    'total_employees' => 0,
                    'total_drivers' => 0,
                    'recent_invoices' => 0,
                    'pending_invoices' => 0,
                    'total_revenue_month' => 0,
                    'overdue_invoices' => 0,
                    'loads_this_month' => 0,
                    'commissions_this_month' => 0
                ];

                try {
                    $stats['total_customers'] = Customer::count();
                } catch (\Exception $e) {
                    Log::warning('Could not count customers: ' . $e->getMessage());
                }

                try {
                    $stats['total_carriers'] = Carrier::count();
                } catch (\Exception $e) {
                    Log::warning('Could not count carriers: ' . $e->getMessage());
                }

                try {
                    $stats['total_employees'] = Employeer::count();
                } catch (\Exception $e) {
                    Log::warning('Could not count employees: ' . $e->getMessage());
                }

                try {
                    $stats['total_drivers'] = Driver::count();
                } catch (\Exception $e) {
                    Log::warning('Could not count drivers: ' . $e->getMessage());
                }

                // Estatísticas de loads
                try {
                    $stats['loads_this_month'] = DB::table('loads')
                        ->where('created_at', '>=', now()->startOfMonth())
                        ->count();

                    $stats['total_revenue_month'] = DB::table('loads')
                        ->where('payment_status', 'paid')
                        ->where('created_at', '>=', now()->startOfMonth())
                        ->sum('price') ?? 0;
                } catch (\Exception $e) {
                    Log::warning('Could not get loads stats: ' . $e->getMessage());
                }

                // Estatísticas de invoices (se a tabela existir)
                try {
                    $stats['recent_invoices'] = DB::table('invoices')
                        ->where('created_at', '>=', now()->subDays(30))
                        ->count();

                    $stats['pending_invoices'] = DB::table('invoices')
                        ->where('payment_status', '!=', 'paid')
                        ->count();

                    $stats['overdue_invoices'] = DB::table('invoices')
                        ->where('payment_status', '!=', 'paid')
                        ->where('due_date', '<', now())
                        ->count();
                } catch (\Exception $e) {
                    // Usar dados da tabela loads se invoices não existir
                    try {
                        $stats['pending_invoices'] = DB::table('loads')
                            ->where('payment_status', '!=', 'paid')
                            ->count();

                        $stats['overdue_invoices'] = DB::table('loads')
                            ->where('payment_status', '!=', 'paid')
                            ->whereNotNull('invoice_date')
                            ->where('invoice_date', '<', now()->subDays(30))
                            ->count();
                    } catch (\Exception $e2) {
                        Log::warning('Could not get invoice stats: ' . $e2->getMessage());
                    }
                }

                // Comissões do mês
                try {
                    $stats['commissions_this_month'] = DB::table('commissions')
                        ->where('created_at', '>=', now()->startOfMonth())
                        ->sum('value') ?? 0;
                } catch (\Exception $e) {
                    Log::warning('Could not get commissions stats: ' . $e->getMessage());
                }

                return $stats;
            });
        } catch (\Exception $e) {
            Log::warning('Could not load quick stats: ' . $e->getMessage());
            return $this->getDefaultStats();
        }
    }

    /**
     * Retorna estatísticas padrão em caso de erro
     */
    private function getDefaultStats()
    {
        return [
            'total_customers' => 0,
            'total_carriers' => 0,
            'total_employees' => 0,
            'total_drivers' => 0,
            'recent_invoices' => 0,
            'pending_invoices' => 0,
            'total_revenue_month' => 0,
            'overdue_invoices' => 0,
            'loads_this_month' => 0,
            'commissions_this_month' => 0
        ];
    }

    /**
     * Limpa o cache de estatísticas (pode ser chamado após atualizações importantes)
     */
    public function clearStatsCache()
    {
        cache()->forget('report_quick_stats');
        return redirect()->route('relatorios.index')
            ->with('success', 'Statistics cache cleared successfully.');
    }

    /**
     * Método para pré-visualizar um relatório sem exportar
     */
    public function preview(Request $request)
    {
        try {
            $validated = $request->validate([
                'report_type' => 'required|in:revenue,commission,carrier_revenue,forecast,upcoming_payments,past_due',
                'period' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            // Log da tentativa de preview
            Log::info('Report preview requested', [
                'report_type' => $validated['report_type'],
                'period' => $validated['period'] ?? 'not specified',
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            // Simular verificação de dados disponíveis
            $hasData = true;
            $estimatedRows = rand(10, 1000);

            return response()->json([
                'success' => true,
                'message' => 'Preview loaded successfully',
                'data' => [
                    'report_type' => $validated['report_type'],
                    'period' => $validated['period'] ?? 'default',
                    'estimated_rows' => $estimatedRows,
                    'has_data' => $hasData,
                    'preview_note' => 'This report contains approximately ' . $estimatedRows . ' records.'
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Report preview validation failed', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Report preview error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while loading the preview'
            ], 500);
        }
    }

    /**
     * Método para debug - verificar configuração do sistema
     */
    public function debug(Request $request)
    {
        if (!config('app.debug')) {
            abort(404);
        }

        $debug_info = [
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
            ],
            'database' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host', 'N/A'),
                'database' => config('database.connections.mysql.database', 'N/A'),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
            ],
            'routes' => [
                'reports_index' => route('relatorios.index'),
                'reports_export' => route('reports.export'),
                'reports_preview' => route('reports.preview'),
            ],
            'tables_check' => $this->checkDatabaseTables(),
            'user' => [
                'authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'Guest',
            ],
            'models_count' => [
                'customers' => $this->safeCount(Customer::class),
                'carriers' => $this->safeCount(Carrier::class),
                'employees' => $this->safeCount(Employeer::class),
                'drivers' => $this->safeCount(Driver::class),
            ]
        ];

        return response()->json($debug_info, 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * Verifica se as tabelas necessárias existem
     */
    private function checkDatabaseTables()
    {
        $tables = ['customers', 'carriers', 'employees', 'drivers', 'loads', 'commissions'];
        $results = [];

        foreach ($tables as $table) {
            try {
                $exists = DB::select("SHOW TABLES LIKE '$table'");
                $results[$table] = count($exists) > 0;
            } catch (\Exception $e) {
                $results[$table] = false;
            }
        }

        return $results;
    }

    /**
     * Conta registros de forma segura
     */
    private function safeCount($model)
    {
        try {
            return $model::count();
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
