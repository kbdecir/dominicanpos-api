<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\CompanyUserController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\CashShiftController;
use App\Http\Controllers\Api\V1\CashRegisterController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\InventoryBalanceController;
use App\Http\Controllers\Api\V1\StockMovementController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\CreditNoteController;

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    Route::post(
        'companies/{companyId}/branches/{branchId}/credit-notes',
        [CreditNoteController::class, 'store']
    )->middleware('permission:credit_notes.create');

    Route::prefix('companies/{companyId}/customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])
            ->middleware('permission:customers.view');

        Route::post('/', [CustomerController::class, 'store'])
            ->middleware('permission:customers.create');

        Route::get('/tax-id/lookup', [CustomerController::class, 'findByTaxId'])
            ->middleware('permission:customers.view');

        Route::get('/{customerId}', [CustomerController::class, 'show'])
            ->middleware('permission:customers.view');

        Route::put('/{customerId}', [CustomerController::class, 'update'])
            ->middleware('permission:customers.update');
    });

    Route::get(
        'companies/{companyId}/branches/{branchId}/inventory/movements',
        [StockMovementController::class, 'index']
    )->middleware('permission:inventory.movements.view');

    Route::get(
        'companies/{companyId}/branches/{branchId}/inventory/balances',
        [InventoryBalanceController::class, 'index']
    )->middleware('permission:inventory.view');

    Route::post(
        'companies/{companyId}/branches/{branchId}/sales',
        [SaleController::class, 'store']
    )->middleware('permission:sales.create');

    Route::get(
        'companies/{companyId}/branches/{branchId}/cash-registers/active',
        [CashRegisterController::class, 'active']
    )->middleware('permission:cash_registers.view');

    Route::prefix('companies/{companyId}/branches/{branchId}/cash-shifts')->group(function () {
        Route::post('/open', [CashShiftController::class, 'open'])
            ->middleware('permission:cash_shifts.open');

        Route::get('/open/current', [CashShiftController::class, 'current'])
            ->middleware('permission:cash_shifts.view');

        Route::post('/{cashShiftId}/close', [CashShiftController::class, 'close'])
            ->middleware('permission:cash_shifts.close');

        Route::post('/{cashShiftId}/movements', [CashShiftController::class, 'storeMovement'])
            ->middleware('permission:cash_movements.create');
    });

    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{roleId}/permissions', [RoleController::class, 'permissions']);
    Route::put('/roles/{roleId}/permissions', [RoleController::class, 'syncPermissions']);

    Route::get('/permissions', [PermissionController::class, 'index']);

    Route::patch('/invitations/{invitationId}/cancel', [InvitationController::class, 'cancel']);

    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{companyId}', [CompanyController::class, 'show']);
        Route::put('/{companyId}', [CompanyController::class, 'update']);
        Route::patch('/{companyId}/activate', [CompanyController::class, 'activate']);
        Route::patch('/{companyId}/deactivate', [CompanyController::class, 'deactivate']);
        Route::get('/{companyId}/branches', [BranchController::class, 'indexByCompany']);

        Route::get('/{companyId}/users', [CompanyUserController::class, 'index'])
            ->middleware('permission:users.view');

        Route::post('/{companyId}/users/invite', [CompanyUserController::class, 'invite'])
            ->middleware('permission:users.create');

        Route::patch('/{companyId}/users/{userId}/role', [CompanyUserController::class, 'updateRole'])
            ->middleware('permission:users.assign_roles');

        Route::patch('/{companyId}/users/{userId}/activate', [CompanyUserController::class, 'activate'])
            ->middleware('permission:users.update');

        Route::patch('/{companyId}/users/{userId}/deactivate', [CompanyUserController::class, 'deactivate'])
            ->middleware('permission:users.deactivate');

        Route::get('/{companyId}/users/{userId}/permissions', [CompanyUserController::class, 'permissions'])
            ->middleware('permission:permissions.view');

        Route::post('/{companyId}/roles', [RoleController::class, 'store']);
        Route::put('/{companyId}/roles/{roleId}', [RoleController::class, 'update']);
    });

    Route::prefix('branches')->group(function () {
        Route::post('/', [BranchController::class, 'store']);
        Route::get('/{branchId}', [BranchController::class, 'show']);
        Route::put('/{branchId}', [BranchController::class, 'update']);
        Route::patch('/{branchId}/activate', [BranchController::class, 'activate']);
        Route::patch('/{branchId}/deactivate', [BranchController::class, 'deactivate']);
    });
});

Route::prefix('v1')->group(function () {
    Route::get('/health', [HealthController::class, 'index']);

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::get('/invitations/{token}', [InvitationController::class, 'show']);
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept']);
});
