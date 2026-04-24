<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        // 409 - Conflict
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
    }
}
