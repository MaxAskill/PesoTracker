<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\FinancialHealthController;
use App\Http\Controllers\FinanceAssistantController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'PesoTracker API is working'
    ]);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('transactions', TransactionController::class);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/analytics/summary', [AnalyticsController::class, 'summary']);
    Route::apiResource('budgets', BudgetController::class);
    Route::apiResource('savings-goals', SavingsGoalController::class);
    Route::get('/insights', [InsightController::class, 'index']);
    Route::get('/reports/transactions/csv', [ReportController::class, 'transactionsCsv']);
    Route::post('/receipts/scan', [ReceiptController::class, 'scan']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::apiResource(
        'recurring-transactions',
        RecurringTransactionController::class
    );
    Route::get('/financial-health', [FinancialHealthController::class, 'score']);
    Route::post('/finance-assistant', [FinanceAssistantController::class, 'ask']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);