<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InventoryController;

Route::prefix('api')->group(function () {
    // Authentication Routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:api');

    // Protected Routes
    Route::middleware('auth:api')->group(function () {
        // Attendance Routes
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
        Route::get('/attendance/history', [AttendanceController::class, 'history']);
        Route::get('/attendance/current', [AttendanceController::class, 'current']);
        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
        Route::get('/attendance', [AttendanceController::class, 'index']);

        // Report Routes
        Route::post('/reports', [ReportController::class, 'store']);
        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/{id}', [ReportController::class, 'show']);
        Route::put('/reports/{id}', [ReportController::class, 'update']);
        Route::delete('/reports/{id}', [ReportController::class, 'destroy']);

        // Payroll Routes
        Route::get('/payslips', [PayslipController::class, 'index']);
        Route::get('/payslips/{id}', [PayslipController::class, 'show']);
        Route::post('/payslips/generate', [PayslipController::class, 'generate'])->middleware('role:admin,owner');

        // Analytics Routes
        Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('/analytics/attendance-summary', [AnalyticsController::class, 'attendanceSummary']);
        Route::get('/analytics/productivity', [AnalyticsController::class, 'productivity']);

        // Crop Management
        Route::apiResource('crops', CropController::class)->middleware('role:supervisor,owner');

        // Animal Management
        Route::apiResource('animals', AnimalController::class)->middleware('role:supervisor,owner');

        // Equipment Management
        Route::apiResource('equipment', EquipmentController::class)->middleware('role:supervisor,owner');

        // Inventory Management
        Route::apiResource('inventory', InventoryController::class)->middleware('role:supervisor,owner');
    });
});
