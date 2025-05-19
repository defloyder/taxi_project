<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный email или пароль'
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;
            
            // Загружаем связанные данные
            $user->load(['favoriteAddresses']);

            // Получаем CSRF-токен
            $csrfToken = csrf_token();

            // Проверяем роль пользователя
            if (!$user->role) {
                throw new \Exception('Не удалось определить роль пользователя');
            }
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'csrf_token' => $csrfToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name ?? $user->name ?? 'Пользователь',
                    'full_name' => $user->full_name ?? $user->name ?? 'Пользователь',
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'favorites' => $user->favoriteAddresses
                ]
            ])->withHeaders([
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Allow-Origin' => $request->header('Origin'),
                'X-CSRF-TOKEN' => $csrfToken
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при входе: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }
            return response()->json([
                'success' => true,
                'message' => 'Выход выполнен успешно'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выходе: ' . $e->getMessage()
            ], 500);
        }
    }
} 