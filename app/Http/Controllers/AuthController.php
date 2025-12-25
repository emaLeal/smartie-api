<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiExceptions;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthController
 * Handles the authentication aspects of the app
 * @package App\Http\Controllers
 **/
class AuthController extends Controller
{
    use ApiExceptions;

    /**
     * Authenticates an user in the app.
     * Validates the credentials, regenerate the session to avoid fixation attacks
     * and returns the data of the authenticated user
     * @param \Illuminate\Http\Request $request Petition object with the attributes 'name' and 'password'
     * @return \Illuminate\Http\JsonResponse JSON response with the user data with code '200' and error with '401'
     * @throws \Illuminate\Validation\ValidationException if the field's validation fails
     * @throws \Exception if an unexpected error occurs
     */
    public function login(Request $request): JsonResponse {
        try {
            if (Auth::check()) {
            $this->delete_session($request);
        }
        // Validate the name and password
        $data = $request->validate([
            'name' => 'required|string',
            'password' => 'required'
        ]);

        // Tries to authenticate
        $attempt = Auth::attempt([
            'name' => $data['name'],
            'password' => $data['password']
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

    /**
     * Register a new user in the system.
     * Realizes the data's validation, create the new register in the database
     * and returns the created user's info
     *
     * @param \Illuminate\Http\Request $request Petition object with the attributes 'name', 'email', 'password' and 'password_confirmation'
     * @return \Illuminate\Http\JsonResponse JSON response with the user data with code '201' and success message
     * @throws \Illuminate\Validation\ValidationException if the field's validation fails
     * @throws \Exception if an unexpected error occurs
     **/
    public function register(Request $request): JsonResponse {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed'
            ]);

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

    /**
     * Handles the logout and deletes the user's session.
     * @param Request $request The user's session
     * @return \Illuminate\Http\JsonResponse Code 200 when the user's session ends
     **/
    public function logout(Request $request): JsonResponse {
        $this->delete_session($request);
        return response()->json([
            'message' => 'Sesión Finalizada con exito'
        ], 200);
    }


    /**
     * Returns the user's profile
     * @param Request $request The user with the authentication tokens
     * @return \Illuminate\Http\JsonResponse Code 200 with the user's profile data
     **/
    public function profile(Request $request): JsonResponse {
        $user = $request->user();
        return response()->json([
            'user' => $user
        ], 200);
    }

    /**
     * Function that handle the deletion of a session
     * @param \Illuminate\Http\Request $request The user's session
     **/
    private function delete_session(Request $request): void {
        // CAMBIO AQUÍ: Especificamos el guard 'web'
        Auth::guard('web')->logout();
        // Invalidate the cookies
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
