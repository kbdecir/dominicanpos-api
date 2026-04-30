<?php

namespace App\Exceptions;

/*
use Throwable;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
*/

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        /*         // 409 - Conflict
        $this->renderable(function (ConflictHttpException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        });

        // 404 - Not Found
        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado',
            ], 404);
        });

        // fallback general
        $this->renderable(function (Throwable $e, $request) {
            return response()->json([
    'success' => false,
    'message' => $e->getMessage(),
    'exception' => get_class($e),
    'file' => $e->getFile(),
    'line' => $e->getLine(),
], 500);
        });
 */

        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado.',
                ], 401);
            }
        });

        $this->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado.',
                ], 403);
            }
        });

        $this->renderable(function (NotFoundHttpException|ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Recurso no encontrado.',
                ], 404);
            }
        });

        $this->renderable(function (ConflictHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Conflicto de negocio.',
                ], 409);
            }
        });

        $this->renderable(function (HttpExceptionInterface $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Error de solicitud.',
                ], $e->getStatusCode());
            }
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => app()->environment('local')
                        ? $e->getMessage()
                        : 'Error interno del servidor.',
                ], 500);
            }
        });
    }
}
