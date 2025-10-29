<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardReportController;
use App\Http\Controllers\Dashboard\ReportExportController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These

// Rotas para dados dos gráficos
Route::prefix('reports')->group(function () {
    Route::get('{reportType}/chart', [App\Http\Controllers\ReportController::class, 'getChartData']);
});
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('data/{tipo}', [DashboardReportController::class, 'getData']);
Route::get('export/{tipo}', [ReportExportController::class, 'export']);

// Rotas específicas para dados dos gráficos
Route::get('data/management', [App\Http\Controllers\ReportController::class, 'getManagementData']);
Route::get('data/loads-averages', [App\Http\Controllers\ReportController::class, 'getLoadsAveragesData']);
Route::get('data/revenue', [App\Http\Controllers\ReportController::class, 'getRevenueData']);
Route::get('data/commission', [App\Http\Controllers\ReportController::class, 'getCommissionData']);
Route::get('data/carrier-revenue', [App\Http\Controllers\ReportController::class, 'getCarrierRevenueData']);
Route::get('data/forecast', [App\Http\Controllers\ReportController::class, 'getForecastData']);
Route::get('data/upcoming-payments', [App\Http\Controllers\ReportController::class, 'getUpcomingPaymentsData']);
Route::get('data/past-due', [App\Http\Controllers\ReportController::class, 'getPastDueData']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});
