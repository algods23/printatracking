<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PcvController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Tasks
    Route::get('/tasks/search', [TaskController::class, 'search'])->name('tasks.search');
    Route::get('/tasks/filter', [TaskController::class, 'filter'])->name('tasks.filter');
    Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('/tasks/{task}/receive', [TaskController::class, 'markReceived'])->name('tasks.receive');
    Route::get('/tasks/{task}/job-order-print', [TaskController::class, 'printJobOrder'])->name('tasks.job-order-print');
    Route::resource('tasks', TaskController::class);

    // Receipts
    Route::resource('receipts', ReceiptController::class);
    Route::get('/receipts/{receipt}/print', [ReceiptController::class, 'printReceipt'])->name('receipts.print');
    Route::get('/receipts/{receipt}/export-pdf', [ReceiptController::class, 'exportPdf'])->name('receipts.export-pdf');

    // Expenses
    Route::resource('expenses', ExpenseController::class);
    Route::get('/expenses/report', [ExpenseController::class, 'report'])->name('expenses.report');

    // PCV
    Route::resource('pcv', PcvController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/company', [SettingController::class, 'updateCompany'])->name('settings.company');
    Route::post('/settings/receipt', [SettingController::class, 'updateReceipt'])->name('settings.receipt');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/printer', [SettingController::class, 'updatePrinter'])->name('settings.printer');
    Route::post('/settings/theme', [SettingController::class, 'updateTheme'])->name('settings.theme');

    // User Management (Admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/settings/users', [SettingController::class, 'manageUsers'])->name('settings.users');
        Route::get('/settings/users/create', [SettingController::class, 'createUser'])->name('settings.create-user');
        Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.store-user');
        Route::get('/settings/users/{user}/edit', [SettingController::class, 'editUser'])->name('settings.edit-user');
        Route::put('/settings/users/{user}', [SettingController::class, 'updateUser'])->name('settings.update-user');
        Route::delete('/settings/users/{user}', [SettingController::class, 'deleteUser'])->name('settings.delete-user');

        // Quotation
        Route::get('/quotation', [QuotationController::class, 'index'])->name('quotation.index');
        Route::get('/quotation/download', [QuotationController::class, 'download'])->name('quotation.download');
        
        Route::post('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
        Route::post('/settings/restore', [SettingController::class, 'restore'])->name('settings.restore');
    });
});

Route::fallback(function () {
    return view('welcome');
});
