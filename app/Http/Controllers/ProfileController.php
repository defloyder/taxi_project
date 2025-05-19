<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            // Загружаем избранные адреса
            $user->load('favoriteAddresses');
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'full_name' => $user->full_name ?? $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'favorites' => $user->favoriteAddresses
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении профиля: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        return $this->getProfile($request);
    }

    /**
     * Обновить роль пользователя
     */
    public function updateRole(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Недостаточно прав'
                ], 403);
            }

            $validated = $request->validate([
                'userId' => 'required|exists:users,id',
                'role' => 'required|in:user,admin'
            ]);

            $targetUser = User::findOrFail($validated['userId']);
            $role = Role::where('name', $validated['role'])->first();

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Указанная роль не существует'
                ], 400);
            }

            $targetUser->role()->associate($role);
            $targetUser->save();

            return response()->json([
                'success' => true,
                'message' => 'Роль пользователя обновлена',
                'user' => array_merge($targetUser->toArray(), [
                    'role' => $targetUser->getRoleName()
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении роли: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить роль пользователя
     */
    public function getRole(Request $request)
    {
        try {
            $validated = $request->validate([
                'userId' => 'required|exists:users,id'
            ]);

            $user = User::with('role')->findOrFail($validated['userId']);

            return response()->json([
                'success' => true,
                'role' => $user->getRoleName()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении роли: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone' => 'nullable|string|max:20',
            ]);

            // Форматируем телефон: оставляем только цифры
            if (isset($validated['phone'])) {
                $validated['phone'] = preg_replace('/[^0-9]/', '', $validated['phone']);
                // Если номер начинается с 8, заменяем на 7
                if (strlen($validated['phone']) > 0 && $validated['phone'][0] === '8') {
                    $validated['phone'] = '7' . substr($validated['phone'], 1);
                }
                // Если номер не начинается с 7, добавляем его
                if (strlen($validated['phone']) > 0 && $validated['phone'][0] !== '7') {
                    $validated['phone'] = '7' . $validated['phone'];
                }
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Профиль успешно обновлен',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении профиля: ' . $e->getMessage()
            ], 500);
        }
    }
} 