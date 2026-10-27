<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashReconciliationController;
use App\Http\Controllers\ConsolidationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('accounts.index'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/ledger/{account}', [LedgerController::class, 'show'])->name('ledger.show');
    Route::get('/consolidation', [ConsolidationController::class, 'index'])->name('consolidation.index');
    Route::get('/reconciliation', [CashReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::post('/reconciliation', [CashReconciliationController::class, 'store'])->name('reconciliation.store');
});

Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('invoices.create');
Route::post('/invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
Route::get('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
Route::get('/invoices/{invoice}/status', [\App\Http\Controllers\InvoiceController::class, 'status'])->name('invoices.status');
Route::post('/invoices/{invoice}/simulate', [\App\Http\Controllers\InvoiceController::class, 'simulate'])->name('invoices.simulate');
