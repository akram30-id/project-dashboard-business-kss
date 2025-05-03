<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('dashboard_accurate')->group(function () {
    Route::get('annual_invoice', [DashboardController::class, 'getAnnualInvoice'])->name('dashboard_accurate.annual_invoice');
    Route::get('annual_revenue', [DashboardController::class, 'getAnnualRevenue'])->name('dashboard_accurate.annual_revenue');
});

Route::prefix('report_annual')->group(function () {
    Route::get('/', [ReportController::class, 'apiListAnnual'])->name('report_annual');
});
