<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ApiExceptions {

    protected function handleException(Exception $e): JsonResponse
    {
        // 1. Error de Validación (ej: no mandaste la foto)
        // 1. Error de Sesión / CSRF (Común en formularios web)
        if ($e instanceof TokenMismatchException) {
            return response()->json([
                'error' => 'Sesión expirada',
                'message' => 'Refresca la página e intenta de nuevo'
            ], 419);
        }

        // 2. Error de Base de Datos (Postgres)
        if ($e instanceof QueryException) {
            Log::error('Error de base de datos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error de base de datos',
                'message' => 'Servicio temporalmente inactivo o error en la consulta'
            ], 503);
        }

        // 3. Error de Validación (Reglas de Laravel)
        if ($e instanceof ValidationException) {
            return response()->json([
                'error' => 'Datos inválidos',
                'messages' => $e->errors()
            ], 422);
        }

        // 4. Recurso No Encontrado (404)
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'error' => 'No encontrado',
                'message' => 'El recurso solicitado no existe'
            ], 404);
        }

        return $this->genericError($e);
    }

    private function genericError(Exception $e): JsonResponse {

        // Handling Unexpected Errors
        Log::error('Error inesperado' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        // General Error return
        if (app()->environment('production')) {
            return response()->json([
                'error' => 'Error de Servidor',
                'message' => 'Error inesperado'
            ], 500);
        }

        // Detailed error return
        return response()->json([
            'error' => 'Error de servidor',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }

}
