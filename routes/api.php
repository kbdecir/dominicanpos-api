<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/invite-debug-auth-raw', function (Request $request) {
    return response()->json([
        'ok' => true,
        'stage' => 'auth-raw',
        'user_id' => $request->user()?->user_id,
        'content_type' => $request->header('Content-Type'),
        'raw' => $request->getContent(),
    ]);
})->middleware('auth:sanctum');

Route::post('/invite-debug-auth-json', function (Request $request) {
    return response()->json([
        'ok' => true,
        'stage' => 'auth-json',
        'user_id' => $request->user()?->user_id,
        'json' => $request->json()->all(),
    ]);
})->middleware('auth:sanctum');


Route::post('/invite-debug-open', function () {
    return response()->json([
        'ok' => true,
        'stage' => 'open',
    ]);
});

Route::post('/invite-debug-auth', function (Request $request) {
    return response()->json([
        'ok' => true,
        'stage' => 'auth',
        'user_id' => $request->user()?->user_id,
    ]);
})->middleware('auth:sanctum');

Route::post('/invite-debug-auth-body', function (Request $request) {
    return response()->json([
        'ok' => true,
        'stage' => 'auth-body',
        'user_id' => $request->user()?->user_id,
        'payload' => $request->all(),
        'raw' => $request->getContent(),
        'content_type' => $request->header('Content-Type'),
    ]);
})->middleware('auth:sanctum');

/*
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\CompanyUserController;
use App\Http\Controllers\Api\V1\HealthController; */


/*

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{companyId}', [CompanyController::class, 'show']);
        Route::put('/{companyId}', [CompanyController::class, 'update']);
        Route::patch('/{companyId}/activate', [CompanyController::class, 'activate']);
        Route::patch('/{companyId}/deactivate', [CompanyController::class, 'deactivate']);
        Route::get('/{companyId}/branches', [BranchController::class, 'indexByCompany']); */

/* Route::post('/invite-debug', function (Request $request) {
    return response()->json([
        'ok' => true,
        'user_id' => $request->user()?->user_id,
        'content_type' => $request->header('Content-Type'),
        'raw' => $request->getContent(),
        'payload' => $request->all(),
    ]);
})->middleware('auth:sanctum'); */

/* Route::post('/{companyId}/users/invite', function (\Illuminate\Http\Request $request, int $companyId) {
    return response()->json([
        'ok' => true,
        'company_id' => $companyId,
        'user_id' => $request->user()?->user_id,
        'content_type' => $request->header('Content-Type'),
        'raw' => $request->getContent(),
        'payload' => $request->all(),
    ]);
}); */
/*
        Route::get('/{companyId}/users', [CompanyUserController::class, 'index']);
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
 */
