<?php

use App\Http\Controllers\CommissionController;
use App\Http\Controllers\PickupConfirmationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DispatcherController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\AttachementController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\LoadImportController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\ContainerLoadController;
use App\Http\Controllers\AdditionalServiceController;
use App\Http\Controllers\Admin\SubscriptionManagementController;
use App\Http\Controllers\adminController;
use App\Http\Controllers\Auth\LoginPageController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ChargeSetupController;
use App\Http\Controllers\TimeLineChargeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return response()->json(['message' => 'Logged out']);
})->name('logout.api');





Route::get('/', [LoginPageController::class, 'showLoginForm'])->name('login.form');

Route::get('/email-exists', function (Illuminate\Http\Request $request) {
    $exists = \App\Models\User::where('email', $request->query('email'))->exists();
    return response()->json(['exists' => $exists]);
})->name('api.email.exists');


Route::middleware('auth')->group(function () {

    // Dashboard Routes - Reports
    Route::prefix('report')->name('report.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
        Route::get('/chart-data', [ReportController::class, 'getChartData'])->name('chart-data');
    });
    
    // Alias para compatibilidade com rotas antigas usando /reports
    Route::get('/reports', [ReportController::class, 'index'])->name('report');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// ⭐ NOVO: Rota para admin alternar tenant em visualização
Route::middleware(['auth'])->post('/admin/switch-tenant', [\App\Http\Controllers\AdminTenantController::class, 'switchTenant'])->name('admin.switch-tenant');

// Routas
Route::middleware(['auth', 'verified'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// Commissions - Only for Owners, Subowners and Admins
Route::middleware(['auth', 'can.access.agreements'])->group(function () {
    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::get('/commissions/add', [CommissionController::class, 'create'])->name('commissions.create');
    Route::post('/commissions', [CommissionController::class, 'store'])->name('commissions.store');
    Route::get('/commissions/{id}', [CommissionController::class, 'show'])->name('commissions.show');
    Route::get('/commissions/{id}/edit', [CommissionController::class, 'edit'])->name('commissions.edit');
    Route::put('/commissions/{id}', [CommissionController::class, 'update'])->name('commissions.update');
    Route::delete('/commissions/{id}', [CommissionController::class, 'destroy'])->name('commissions.destroy');
    Route::get('/deals/{id}/commissions', [CommissionController::class, 'commissions'])->name('deals.commissions');
});

Route::get('/attachments/list', [AttachementController::class, 'index'])->name('attachments.index');
Route::get('/attachments/add', [AttachementController::class, 'create'])->name('attachments.create');
Route::post('/attachments/store', [AttachementController::class, 'store'])->name('attachments.store');
Route::get('/attachments/{id}', [AttachementController::class, 'show'])->name('attachments.show');
Route::get('/attachments/{id}/edit', [AttachementController::class, 'edit'])->name('attachments.edit');
Route::put('/attachments/{id}', [AttachementController::class, 'update'])->name('attachments.update');
Route::delete('/attachments/{id}', [AttachementController::class, 'destroy'])->name('attachments.destroy');


Route::get('/attachments/list/{id}', [AttachementController::class, 'index2'])->name('attachments.index2');
Route::get('/attachments/add/{id}', [AttachementController::class, 'create2'])->name('attachments.create2');

// Permissões e roles
Route::middleware('auth')->group(function () {
    Route::get('/permissoes', [adminController::class, 'permissoes'])->name('permissoes');
    Route::get('/roles_users', [adminController::class, 'roles_users'])->name('roles');
    Route::get('/permissions_roles', [adminController::class, 'permissions_roles'])->name('permissions');
    Route::get('/permissions_roles_by_id/{id}', [adminController::class, 'permissions_roles_by_id'])->name('permissions_by_id');
    Route::post('/salvar_roles_users', [adminController::class, 'salvar_roles_users'])->name('salvar_roles_users');
    Route::post('/actualizar_roles_users', [adminController::class, 'actualizar_roles_users'])->name('actualizar_roles_users');
    Route::post('/salvar_permissions_roles', [adminController::class, 'salvar_permissions_roles'])->name('salvar_permissions_roles');
    Route::get('/my-profile', [adminController::class, 'myProfile'])->name('my.profile');
    Route::post('/update-profile', [adminController::class, 'updateProfile'])->name('update.profile');
});

// Rotas de autenticação estão definidas em routes/auth.php

// Rotas públicas de assinatura
Route::middleware(['auth'])->group(function () {
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::get('/subscription/build-plan', [SubscriptionController::class, 'buildPlan'])->name('subscription.build-plan');
    Route::post('/subscription/build-plan', [SubscriptionController::class, 'storeCustomPlan'])->name('subscription.store-custom-plan');
    Route::post('/subscription/calculate-price', [SubscriptionController::class, 'calculatePrice'])->name('subscription.calculate-price');
    Route::get('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
    // Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success'); // ⭐ REMOVIDO: Redireciona direto para dashboard
    Route::get('/subscription/blocked', [SubscriptionController::class, 'blocked'])->name('subscription.blocked');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/subscription/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');

    // Billing routes
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    // Route::get('/billing/usage', [BillingController::class, 'usage'])->name('billing.usage');
    Route::post('/billing/update-payment-method', [BillingController::class, 'updatePaymentMethod'])->name('billing.update-payment-method');
});


Route::post('/dispatchers', [DispatcherController::class, 'store'])->name('dispatchers.store');
Route::post('/dispatchers/dashboard', [DispatcherController::class, 'storeFromDashboard'])->name('dispatchers.store.dashboard');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::post('/carriers', [CarrierController::class, 'store'])->name('carriers.store');
Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');
Route::post('/brokers', [BrokerController::class, 'store'])->name('brokers.store');
Route::middleware(['auth', 'can.access.agreements'])->post('/deals', [DealController::class, 'store'])->name('deals.store');

// Webhook público para receber confirmações do N8N (sem autenticação)
Route::post('/webhook/n8n/pickup-confirmation', [KanbanController::class, 'receivePickupConfirmation'])
    ->name('webhook.n8n.pickup-confirmation');

// Rotas protegidas por assinatura
Route::middleware(['auth', 'check.subscription'])->group(function () {
    Route::get('/dispatchers', [DispatcherController::class, 'index'])->name('dispatchers.index');
    Route::get('/dispatchers/add', [DispatcherController::class, 'create'])->name('dispatchers.create');
    Route::get('/dispatchers/{id}', [DispatcherController::class, 'show'])->name('dispatchers.show');
    Route::get('/dispatchers/{id}/edit', [DispatcherController::class, 'edit'])->name('dispatchers.edit');
    Route::put('/dispatchers/{id}', [DispatcherController::class, 'update'])->name('dispatchers.update');
    Route::delete('/dispatchers/{id}', [DispatcherController::class, 'destroy'])->name('dispatchers.destroy');
    Route::patch('/dispatchers/{id}/toggle-status', [DispatcherController::class, 'toggleStatus'])->name('dispatchers.toggle-status');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/add', [EmployeeController::class, 'create'])->name('employees.create');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::patch('/employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
    Route::get('/employees/{dispatcher_id}/getEmployee', [EmployeeController::class, 'getEmployee'])->name('employees.getEmployee');

    Route::get('/carriers', [CarrierController::class, 'index'])->name('carriers.index');
    Route::get('/carriers/add', [CarrierController::class, 'create'])->name('carriers.create');
    Route::get('/carriers/{id}', [CarrierController::class, 'show'])->name('carriers.show');
    Route::get('/carriers/{id}/edit', [CarrierController::class, 'edit'])->name('carriers.edit');
    Route::put('/carriers/{id}', [CarrierController::class, 'update'])->name('carriers.update');
    Route::delete('/carriers/{id}', [CarrierController::class, 'destroy'])->name('carriers.destroy');
    Route::patch('/carriers/{id}/toggle-status', [CarrierController::class, 'toggleStatus'])->name('carriers.toggle-status');

    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/add', [DriverController::class, 'create'])->name('drivers.create');
    Route::get('/drivers/{id}', [DriverController::class, 'show'])->name('drivers.show');
    Route::get('/drivers/{id}/edit', [DriverController::class, 'edit'])->name('drivers.edit');
    Route::put('/drivers/{id}', [DriverController::class, 'update'])->name('drivers.update');
    Route::delete('/drivers/{id}', [DriverController::class, 'destroy'])->name('drivers.destroy');
    Route::patch('/drivers/{id}/toggle-status', [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status');

    Route::get('/brokers', [BrokerController::class, 'index'])->name('brokers.index');
    Route::get('/brokers/add', [BrokerController::class, 'create'])->name('brokers.create');
    Route::get('/brokers/{id}', [BrokerController::class, 'show'])->name('brokers.show');
    Route::get('/brokers/{id}/edit', [BrokerController::class, 'edit'])->name('brokers.edit');
    Route::put('/brokers/{id}', [BrokerController::class, 'update'])->name('brokers.update');
    Route::delete('/brokers/{id}', [BrokerController::class, 'destroy'])->name('brokers.destroy');
    Route::patch('/brokers/{id}/toggle-status', [BrokerController::class, 'toggleStatus'])->name('brokers.toggle-status');

    // Deals - Only for Owners, Subowners and Admins
    Route::middleware(['can.access.agreements'])->group(function () {
        Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
        Route::get('/deals/add', [DealController::class, 'create'])->name('deals.create');
        Route::get('/deals/{id}', [DealController::class, 'show'])->name('deals.show');
        Route::get('/deals/{id}/edit', [DealController::class, 'edit'])->name('deals.edit');
        Route::put('/deals/{id}', [DealController::class, 'update'])->name('deals.update');
        Route::delete('/deals/{id}', [DealController::class, 'destroy'])->name('deals.destroy');
    });


    //LOADS
    Route::get('/importar-loads', [LoadImportController::class, 'upload'])
        ->name('loads.form');

    Route::post('/importar-loads', [LoadImportController::class, 'importar'])
        ->name('loads.importar');

    // Importação via Excel
    Route::get('/loads/import', [LoadImportController::class, 'upload'])
        ->name('loads.import.form');

    Route::post('/loads/import', [LoadImportController::class, 'importar'])
        ->name('loads.import');

    // Cadastro/edição manual
    Route::get('/loads/create', [LoadImportController::class, 'create'])
        ->name('loads.create');
    Route::post('/loads', [LoadImportController::class, 'store'])
        ->name('loads.store');

    Route::get('/loads/show/{id}', [LoadImportController::class, 'show'])
        ->name('loads.show');

    Route::get('/loads/edit/{id}', [LoadImportController::class, 'edit'])
        ->name('loads.edit');

    Route::put('/loads/update/{id}', [LoadImportController::class, 'update'])
        ->name('loads.update');

    Route::post('/loads/{load}/update-employee', [LoadImportController::class, 'updateEmployee'])
        ->name('loads.updateEmployee');


    Route::delete('/loads/destroy/{id}', [LoadImportController::class, 'destroy'])
        ->name('loads.destroy');

    Route::delete('/loads/delete-all', [LoadImportController::class, 'destroyAll'])->name('loads.destroyAll');

    // Listagem de todos os loads (com filtros integrados)
    Route::get('/loads', [LoadImportController::class, 'index'])
        ->name('loads.index');

    Route::get('/loads/search', [LoadImportController::class, 'search'])
        ->name('loads.search');
    Route::post('/loads/apagar-varios', [LoadImportController::class, 'apagarVarios'])->name('loads.apagar_varios');


    // Kanban Mode
    Route::get('/loads/mode', [KanbanController::class, 'kanbanMode'])
        ->name('loads.mode');
    Route::put('/loads/update-ajax/{id}', [KanbanController::class, 'updateLoadAjax'])
        ->name('loads.update.ajax');
    Route::patch('/loads/{id}/kanban-status', [KanbanController::class, 'updateKanbanStatus'])
        ->name('loads.kanban.status');
    Route::get('/loads/card-fields-config', [KanbanController::class, 'getCardFieldsConfig'])
        ->name('loads.card.fields.config');
    Route::post('/loads/card-fields-config', [KanbanController::class, 'saveCardFieldsConfig'])
        ->name('loads.card.fields.save');
    // Rota para obter lista de drivers
    Route::get('/loads/get-drivers-list', [KanbanController::class, 'getDriversList'])
        ->name('loads.drivers.list');
    // Sincronizar kanban_status dos loads em 'new'
    Route::post('/loads/sync-new-kanban-status', [KanbanController::class, 'syncNewLoadsKanbanStatus'])
        ->name('loads.sync.new.kanban.status');

    // Confirm Pickup Loads - Enqueue for N8N processing
    Route::post('/loads/confirm-pickup', [KanbanController::class, 'confirmPickupLoads'])
        ->name('loads.confirm-pickup');
    
    // Pickup Confirmations
    Route::get('/pickup-confirmations', [PickupConfirmationController::class, 'index'])
        ->name('pickup-confirmations.index');
    Route::get('/pickup-confirmations/data', [PickupConfirmationController::class, 'getConfirmations'])
        ->name('pickup-confirmations.data');
    Route::get('/pickup-confirmations/jobs', [PickupConfirmationController::class, 'getEnqueuedJobs'])
        ->name('pickup-confirmations.jobs');
    Route::get('/pickup-confirmations/{id}/download-transcription', [PickupConfirmationController::class, 'downloadTranscription'])
        ->name('pickup-confirmations.download-transcription');
    Route::get('/pickup-confirmations/{id}/download-audio', [PickupConfirmationController::class, 'downloadAudio'])
        ->name('pickup-confirmations.download-audio');
    Route::post('/pickup-confirmations/retry/{uuid}', [PickupConfirmationController::class, 'retryFailedJob'])
        ->name('pickup-confirmations.retry');

    // Voice Calls Management
    Route::get('/voice-calls', [App\Http\Controllers\VoiceCallsController::class, 'index'])
        ->name('voice-calls.index');
    Route::get('/voice-calls/data', [App\Http\Controllers\VoiceCallsController::class, 'getCalls'])
        ->name('voice-calls.data');


    // Kanban Container
    Route::get('/mode/container/list', [ContainerController::class, 'index'])->name('container.index');
    Route::get('/mode/container/add', [ContainerController::class, 'create'])->name('container.create');
    Route::post('/mode/container/store', [ContainerController::class, 'store'])->name('container.store');
    Route::get('/mode/container/{id}', [ContainerController::class, 'show'])->name('container.show');
    Route::get('/mode/container/{id}/edit', [ContainerController::class, 'edit'])->name('container.edit');
    Route::put('/mode/container/{id}', [ContainerController::class, 'update'])->name('container.update');
    Route::delete('/mode/container/{id}', [ContainerController::class, 'destroy'])->name('container.destroy');

    Route::get('/mode/container_loads/list', [ContainerLoadController::class, 'index'])->name('container_loads.index');
    Route::get('/mode/container_loads/add', [ContainerLoadController::class, 'create'])->name('container_loads.create');
    Route::post('/mode/container_loads/store', [ContainerLoadController::class, 'store'])->name('container_loads.store');
    Route::get('/mode/container_loads/{id}', [ContainerLoadController::class, 'show'])->name('container_loads.show');
    Route::get('/mode/container_loads/{id}/edit', [ContainerLoadController::class, 'edit'])->name('container_loads.edit');
    Route::put('/mode/container_loads/{id}', [ContainerLoadController::class, 'update'])->name('container_loads.update');
    Route::delete('/mode/container_loads/{id}', [ContainerLoadController::class, 'destroy'])->name('container_loads.destroy');

    Route::get('/additional_services/list', [AdditionalServiceController::class, 'index'])->name('additional_services.index');
    Route::get('/additional_services/add', [AdditionalServiceController::class, 'create'])->name('additional_services.create');
    Route::post('/additional_services/store', [AdditionalServiceController::class, 'store'])->name('additional_services.store');
    Route::get('/additional_services/{id}', [AdditionalServiceController::class, 'show'])->name('additional_services.show');
    Route::get('/additional_services/{id}/edit', [AdditionalServiceController::class, 'edit'])->name('additional_services.edit');
    Route::put('/additional_services/{id}', [AdditionalServiceController::class, 'update'])->name('additional_services.update');
    Route::delete('/additional_services/{id}', [AdditionalServiceController::class, 'destroy'])->name('additional_services.destroy');

    Route::get('/charges_setups/list', [ChargeSetupController::class, 'index'])->name('charges_setups.index');
    Route::get('/charges_setups/add', [ChargeSetupController::class, 'create'])->name('charges_setups.create');
    Route::post('/charges_setups/store', [ChargeSetupController::class, 'store'])->name('charges_setups.store');
    Route::get('/charges_setups/{id}', [ChargeSetupController::class, 'show'])->name('charges_setups.show');
    Route::get('/charges_setups/{id}/edit', [ChargeSetupController::class, 'edit'])->name('charges_setups.edit');
    Route::put('/charges_setups/{id}', [ChargeSetupController::class, 'update'])->name('charges_setups.update');
    Route::delete('/charges_setups/{id}', [ChargeSetupController::class, 'destroy'])->name('charges_setups.destroy');
    Route::get('/charge-setups/by-carrier/{carrierId}', [ChargeSetupController::class, 'getSetupByCarrier'])
        ->name('charge_setups.by_carrier');
    Route::get('/charge-setups/all-carriers', [ChargeSetupController::class, 'getAllCarriersSetup'])
        ->name('charge_setups.all_carriers');

    // time_line_charges
    Route::get('/invoices/list', [TimeLineChargeController::class, 'index'])->name('time_line_charges.index');
    Route::get('/invoices/add', [TimeLineChargeController::class, 'create'])->name('time_line_charges.create');
    Route::post('/invoices/store', [TimeLineChargeController::class, 'store'])->name('time_line_charges.store');
    Route::get('/invoices/{id}', [TimeLineChargeController::class, 'show'])->name('time_line_charges.show');
    Route::get('/invoices/{id}/edit', [TimeLineChargeController::class, 'edit'])->name('time_line_charges.edit');
    Route::put('/invoices/update/{id}', [TimeLineChargeController::class, 'update'])->name('time_line_charges.update');
    Route::delete('/invoices/{id}', [TimeLineChargeController::class, 'destroy'])->name('time_line_charges.destroy');

    Route::get('/invoice/{invoice}/report-loads', [TimeLineChargeController::class, 'getLoadsFromInvoice'])
        ->name('invoice.loads.report');


    Route::get('/time_line_charges/filter-loads', [TimeLineChargeController::class, 'filterLoads'])->name('time_line_charges.filterLoads');
    Route::get('/time_line_charges/get-charges-setup/{id}', [TimeLineChargeController::class, 'getChargeSetup'])->name('time_line_charges.getChargesSetup');
    Route::delete('/time_line_charges/load_invoice/destroy/{load_id}/{time_line_charge_id}', [TimeLineChargeController::class, 'load_invoice_destroy'])->name('time_line_charges.load_invoice.destroy');
    Route::get('/time_line_charges/{id}/details', [TimeLineChargeController::class, 'getChargeDetails'])->name('time_line_charges.details');
    Route::get('/time_line_charges/{id}/excel', [TimeLineChargeController::class, 'exportExcel'])->name('time_line_charges.excel');



    Route::post('/employee_loads/store', [TimeLineChargeController::class, 'store'])->name('employee_loads.store');
    Route::get('/employee_loads/{id}/show', [TimeLineChargeController::class, 'show'])->name('employee_loads.show');
    Route::delete('/employee_loads/{id}/destroy', [TimeLineChargeController::class, 'destroy'])->name('employee_loads.destroy');



    Route::prefix('api/vin')->group(function () {
        Route::post('/decode', [App\Http\Controllers\VinDecoderController::class, 'decodeVin'])
            ->name('vin.decode');

        Route::post('/validate', [App\Http\Controllers\VinDecoderController::class, 'validateVin'])
            ->name('vin.validate');
    });



    // Rotas do Admin - Gestão de Subscrições
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

        // Gestão de Subscrições
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [SubscriptionManagementController::class, 'index'])
                ->name('index');

            Route::get('/{user}', [SubscriptionManagementController::class, 'show'])
                ->name('show');

            Route::post('/{user}/block', [SubscriptionManagementController::class, 'blockSubscription'])
                ->name('block');

            Route::post('/{user}/unblock', [SubscriptionManagementController::class, 'unblockSubscription'])
                ->name('unblock');

            Route::post('/{user}/change-plan', [SubscriptionManagementController::class, 'changePlan'])
                ->name('change-plan');

            Route::post('/{user}/extend', [SubscriptionManagementController::class, 'extendSubscription'])
                ->name('extend');

            Route::delete('/{user}/delete', [SubscriptionManagementController::class, 'deleteUser'])
                ->name('delete');

            Route::patch('/{user}/toggle-status', [SubscriptionManagementController::class, 'toggleUserStatus'])
                ->name('toggle-user-status');

            Route::get('/export/users', [SubscriptionManagementController::class, 'exportUsers'])
                ->name('export');
        });
    });
});




Route::post('/webhook/stripe', [WebhookController::class, 'handle']);


// Subscription payment routes
Route::post('/api/subscription/create-payment-intent', [SubscriptionController::class, 'createPaymentIntent']);
Route::post('/api/subscription/process-payment', [SubscriptionController::class, 'processPayment']);
Route::post('/api/subscription/process-downgrade', [SubscriptionController::class, 'processDowngrade']);

// Existing payment routes
Route::post('/api/payments/create-intent', [PaymentController::class, 'createIntent']);
Route::post('/api/payments/confirm-intent', [PaymentController::class, 'confirmIntent']);
Route::post('/api/payments/refund', [PaymentController::class, 'refund']);

Route::get('/payments/index', [PaymentController::class, 'index']);

require __DIR__ . '/auth.php';
