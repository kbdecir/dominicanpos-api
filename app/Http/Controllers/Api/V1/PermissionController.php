<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::query()
            ->orderBy('module_name')
            ->orderBy('code')
            ->get();

        return response()->json([
            'message' => 'Permisos obtenidos correctamente.',
            'data' => $permissions,
        ]);
    }
}
