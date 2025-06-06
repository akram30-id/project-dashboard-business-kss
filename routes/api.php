<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ReportController;
use App\Http\Controllers\webhook\AccurateInvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('dashboard_accurate')->group(function () {
    Route::get('annual_invoice', [DashboardController::class, 'getAnnualInvoice'])->name('dashboard_accurate.annual_invoice');
    Route::get('annual_sales', [DashboardController::class, 'getAnnualSales'])->name('dashboard_accurate.annual_sales');
    Route::get('annual_accrue', [DashboardController::class, 'getAnnualAccrue'])->name('dashboard_accurate.annual_accrue');
});

Route::prefix('report_annual')->group(function () {
    Route::get('/', [ReportController::class, 'apiListAnnual'])->name('report_annual');
    Route::get('/monthly', [ReportController::class, 'apiListMonthly'])->name('report_annual.monthly');
});

Route::prefix('invoice_monthly_detail')->group(function () {
    Route::get('/', [ReportController::class, 'apiDetailMonthly'])->name('invoice_monthly_detail');
    Route::get('/annual', [ReportController::class, 'apiDetailAnnualy'])->name('invoice_monthly_detail.annual');
});


Route::prefix('webhook')->group(function () {
    Route::get('/accurate_invoice_annual', [AccurateInvoiceController::class, 'apiGetDataAnnual'])->name('webhook.accurate_invoice_annual');
});
