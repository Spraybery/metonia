<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/api/login', [AuthController::class, 'login']);
});

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.change');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard Analytics Engine
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/db', [DashboardController::class, 'apiSnapshot'])->name('api.db');

    // Vehicle Build & Job Cards
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::get('/vehicles/print', [VehicleController::class, 'printRegister'])->name('vehicles.print_register');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{id}', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::get('/vehicles/{id}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{id}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::put('/vehicles/{id}/stage', [VehicleController::class, 'updateStage'])->name('vehicles.update_stage');
    Route::put('/vehicles/{id}/checklist', [VehicleController::class, 'updateChecklist'])->name('vehicles.update_checklist');
    Route::post('/vehicles/{id}/parts', [VehicleController::class, 'issuePart'])->name('vehicles.issue_part');
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    Route::get('/vehicles/{id}/print', [VehicleController::class, 'printJobCard'])->name('vehicles.print');

    // Materials & Store Inventory
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/print', [MaterialController::class, 'printIndex'])->name('materials.print');
    Route::get('/materials/issuance', [MaterialController::class, 'issuance'])->name('materials.issuance');
    Route::get('/materials/issuance/print', [MaterialController::class, 'printIssuance'])->name('materials.issuance.print');
    Route::get('/materials/restock', [MaterialController::class, 'restock'])->name('materials.restock');
    Route::get('/materials/restock/print', [MaterialController::class, 'printRestock'])->name('materials.restock.print');
    Route::get('/materials/safety-stock', [MaterialController::class, 'safetyStock'])->name('materials.safety_stock');
    Route::get('/materials/safety-stock/print', [MaterialController::class, 'printSafetyStock'])->name('materials.safety_stock.print');
    Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::put('/materials/{id}', [MaterialController::class, 'update'])->name('materials.update');
    Route::post('/materials/{id}/movement', [MaterialController::class, 'stockMovement'])->name('materials.movement');
    Route::put('/materials/movement/{id}', [MaterialController::class, 'updateMovement'])->name('materials.movement.update');
    Route::delete('/materials/movement/{id}', [MaterialController::class, 'destroyMovement'])->name('materials.movement.destroy');
    Route::get('/materials/{id}/movements', [MaterialController::class, 'movements'])->name('materials.movements');
    Route::delete('/materials/{id}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    // Supervisors Roster
    Route::get('/supervisors', [SupervisorController::class, 'index'])->name('supervisors.index');
    Route::get('/supervisors/print', [SupervisorController::class, 'printRegister'])->name('supervisors.print');
    Route::post('/supervisors', [SupervisorController::class, 'store'])->name('supervisors.store');
    Route::put('/supervisors/{id}', [SupervisorController::class, 'update'])->name('supervisors.update');
    Route::delete('/supervisors/{id}', [SupervisorController::class, 'destroy'])->name('supervisors.destroy');

    // Tools & Equipment Asset Register
    Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
    Route::get('/tools/print', [ToolController::class, 'printRegister'])->name('tools.print');
    Route::post('/tools', [ToolController::class, 'store'])->name('tools.store');
    Route::put('/tools/{id}', [ToolController::class, 'update'])->name('tools.update');
    Route::delete('/tools/{id}', [ToolController::class, 'destroy'])->name('tools.destroy');

    // User Management (Admin Only)
    Route::middleware('role:Admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
