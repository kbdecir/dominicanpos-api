<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\CompanyUserController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\InvitationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{companyId}', [CompanyController::class, 'show']);
        Route::put('/{companyId}', [CompanyController::class, 'update']);
        Route::patch('/{companyId}/activate', [CompanyController::class, 'activate']);
        Route::patch('/{companyId}/deactivate', [CompanyController::class, 'deactivate']);
        Route::get('/{companyId}/branches', [BranchController::class, 'indexByCompany']);

        Route::get('/{companyId}/users', [CompanyUserController::class, 'index']);
        Route::post('/{companyId}/users/invite', [CompanyUserController::class, 'invite']);
        Route::patch('/{companyId}/users/{userId}/role', [CompanyUserController::class, 'updateRole']);
        Route::patch('/{companyId}/users/{userId}/activate', [CompanyUserController::class, 'activate']);
        Route::patch('/{companyId}/users/{userId}/deactivate', [CompanyUserController::class, 'deactivate']);
        Route::get('/{companyId}/users/{userId}/permissions', [CompanyUserController::class, 'permissions']);
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
});
