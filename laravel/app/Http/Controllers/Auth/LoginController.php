<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Exceptions\UnauthorizedException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Connexion utilisateur
     * 
     * Authentifie un utilisateur et retourne un token JWT.
     * 
     * @param  Request  $request
     * @return JsonResponse
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Connexion réussie",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "role": "USER"
     *     },
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     *   }
     * }
     * 
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email field is required."]
     *   }
     * }
     * 
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid credentials"
     * }
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            // Attempt login
            $result = $this->authService->login(
                $request->email,
                $request->password
            );

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => $result,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (UnauthorizedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login',
            ], 500);
        }
    }

    /**
     * Déconnexion utilisateur
     * 
     * Invalide le token JWT de l'utilisateur connecté.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Déconnexion réussie"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);

        } catch (UnauthorizedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout',
            ], 500);
        }
    }

    /**
     * Rafraîchir le token JWT
     * 
     * Génère un nouveau token JWT à partir du token existant.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Token rafraîchi avec succès",
     *   "data": {
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     *   }
     * }
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => $result,
            ]);

        } catch (UnauthorizedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during token refresh',
            ], 500);
        }
    }
}
