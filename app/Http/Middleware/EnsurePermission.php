<?php

namespace App\Http\Middleware;

use App\Services\AuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    public function handle(Request $request, Closure $next, string $permissionCode): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        $companyId = $request->route('companyId');

        if (! $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar la empresa del contexto.',
            ], 400);
        }

        $hasPermission = $this->authorizationService->userHasPermission(
            $user,
            (int) $companyId,
            $permissionCode
        );

        if (! $hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.',
                'permission' => $permissionCode,
            ], 403);
        }

        return $next($request);
    }
}
