<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends Controller
{
    /**
     * Authenticate a user and start a session
     *
     * Validate the user's credentials and, if correct,
     * create an authentication's session. Uses Http-Only cookies
     * for handling the session securely
     *
     * @api {post} /api/auth/login Authenticate user
     * @apiName Login
     * @apiGroup Authentication
     *
     * @bodyParam name string required Example: johndoe
     * @bodyParam password string required password
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 200 {
     *   "success": true,
     *   "user": {
     *     "id": 1,
     *     "name": "johndoe",
     *     "email": "john@example.com"
     *   },
     *   "message": "Login exitoso"
     * }
     * @response 422: {
     *   "success": false,
     *   "error": "Validation Error",
     *   "errors": {
     *     "name": ["El campo nombre es requerido."]
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "error": "Credenciales Incorrectas"
     * }
     * @response 500 {
     *   "success": false,
     *   "error": "Server Error",
     *   "message": "An unexpected error occurred"
     * }
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function login(Request $request) {
        try {
        if (Auth::check()) {
            $this->delete_session($request);
        }
        // Validate the name and password
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'password' => 'required'
        ]);

        // Returns error 422 if the validator fails
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Tries to authenticate
        $attempt = Auth::attempt([
            'name' => $validator->getValue('name'),
            'password' => $validator->getValue('password')
        ]);

        // Returns error 401 if the credentials are incorrect
        if (!$attempt) {
            return response()->json([
                'error' => 'Credenciales Incorrectas'
            ], 401);
        }

        // Regenerate session to prevent fixation attacks
        $request->session()->regenerate();

        // Get authenticated user
        $user = Auth::user();

        // Returns json with the user data
        return response()->json([
            'user' => $user,
        ], 200);

        } catch (TokenMismatchException $e) {
            return response()->json([
                'error' => 'Session expired',
                'message' => 'Refresca la pagina e instente de nuevo'
            ], 419);
        } catch (QueryException $e) {
            // Database Error
            Log::error('Error de base de datos ' . $e->getMessage());

            return response()->json([
                'error' => 'Error de base de datos',
                'message' => 'Servicio temporalmente inactivo'
            ], 503);
        } catch (Exception $e) {
           return $this->genericError($e);
        }
    }

    public function register(Request $request) {
        try {
            // Validate the required fields
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed'
            ]);

            // Return error 400 if the validation fails
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Error de validación: ' . $validator->errors()
                ], 400);
            }

            // Extract the validated data
            $data = $validator->validated();

            // Create the user
            $user = User::create($data);

            // Return message 201 if the request is successfull
            return response()->json([
                'message' => 'Usuario Registrado',
                'data' => $user
            ], 201);
        } catch (QueryException $e) {
            // Database Error
            Log::error('Error de base de datos ' . $e->getMessage());

            return response()->json([
                'error' => 'Error de base de datos',
                'message' => 'Servicio temporalmente inactivo'
            ], 503);
        }  catch (Exception $e) {
            return $this->genericError($e);
        }
    }

    public function logout(Request $request) {
        $this->delete_session($request);
        return response()->json([
            'message' => 'Sesión Finalizada con exito'
        ]);
    }

    private function delete_session(Request $request) {
        Auth::logout();
        // Invalidate the cookies
        $request->session()->invalidate();
        $request->session()->regenerateToken();
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
