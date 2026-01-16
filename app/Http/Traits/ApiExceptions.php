<?php

declare(strict_types=1);

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
        // In case the token is missing or is expired
        if ($e instanceof TokenMismatchException) {
            return response()->json([
                'error' => 'Sesión expirada',
                'message' => 'Refresca la página e intenta de nuevo'
            ], 419);
        }

        // Database error
        if ($e instanceof QueryException) {
            error_log('Error de base de datos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error de base de datos',
                'message' => 'Servicio temporalmente inactivo o error en la consulta'
            ], 503);
        }

        // Validation error
        if ($e instanceof ValidationException) {
            return response()->json([
                'error' => 'Datos inválidos',
                'messages' => $e->errors()
            ], 422);
        }

        // Not found resource
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'error' => 'No encontrado',
                'message' => 'El recurso solicitado no existe'
            ], 404);
        }

        return $this->genericError($e);
    }

    /**
     * handles a generic error in case the exception is not addresed in the other cases
     * @param Exception $e The exception that takes place
     * */
    private function genericError(Exception $e): JsonResponse {

        // Handling Unexpected Errors
        error_log('Error inesperado' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());

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
