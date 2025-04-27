<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json(['message' => 'User registered successfully!']);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $token = $this->authService->login($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(): JsonResponse
    {
        // Получаем текущего аутентифицированного пользователя
        $user = Auth::user();

        // Удаляем токен пользователя
        $user->tokens->each(function ($token) {
            $token->delete(); // Удаляем все токены для пользователя
        });

        // Можно удалить только один токен (например, текущий)
        // Auth::user()->token()->delete(); // Если есть токен в текущем запросе

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }
}