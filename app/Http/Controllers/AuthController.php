<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiExceptions;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiExceptions;

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

        } catch (Exception $e) {
            return $this->handleException($e);
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
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function logout(Request $request) {
        $this->delete_session($request);
        return response()->json([
            'message' => 'Sesión Finalizada con exito'
        ], 200);
    }

    public function profile(Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => $user
        ], 200);
    }

    private function delete_session(Request $request) {
        // CAMBIO AQUÍ: Especificamos el guard 'web'
        //
        Auth::guard('web')->logout();

        // Invalidate the cookies
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
