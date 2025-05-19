<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\FavoriteAddress;

class ProfileController extends Controller
{
    /**
     * Получить данные профиля
     */
    public function getProfile(Request $request)
    {
        // Для отладки: если пользователь не аутентифицирован, пытаемся найти пользователя по email из запроса
        $user = Auth::user();
        
        if (!$user && $request->has('email')) {
            $user = User::where('email', $request->email)->first();
        }
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }
        
        // Загрузка профиля и любимых адресов
        $user->load(['profile', 'favoriteAddresses']);
        
        // Преобразуем адреса в нужный формат
        $favorites = $user->favoriteAddresses->map(function($address) {
            return [
                'id' => $address->id,
                'title' => $address->title,
                'address' => $address->address
            ];
        })->toArray();
        
        // Подготовка данных для ответа
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? ($user->profile ? $user->profile->phone : null),
            'avatar' => $user->profile ? $user->profile->avatar : null,
            'favorites' => $favorites
        ];
        
        return response()->json([
            'success' => true,
            'user' => $userData
        ]);
    }
    
    /**
     * Обновить данные профиля пользователя
     */
    public function updateProfile(Request $request)
    {
        // Для отладки: если пользователь не аутентифицирован, пытаемся найти пользователя по email из запроса
        $user = Auth::user();
        
        if (!$user && $request->has('email')) {
            $user = User::where('email', $request->email)->first();
        }
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }
        
        // Валидация входных данных
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'sometimes|string|max:20|nullable',
            'avatar' => 'sometimes|string|nullable',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Обновление основных данных пользователя
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('email') && $request->email !== $user->email) {
            $user->email = $request->email;
        }
        
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        
        $user->save();
        
        // Обновление или создание профиля
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        
        if ($request->has('avatar')) {
            $profile->avatar = $request->avatar;
        }
        
        $user->profile()->save($profile);
        
        // Возвращаем обновленные данные
        return $this->getProfile($request);
    }
    
    /**
     * Обновить роль пользователя
     */
    public function updateRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:users,id',
            'role' => 'required|in:user,admin,client,driver'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($request->userId);
            $user->role = $request->role;
            $user->save();

            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении роли пользователя'
            ], 500);
        }
    }
    
    /**
     * Добавить любимый адрес
     */
    public function addFavoriteAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:255',
            'title' => 'required|string|max:100',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Для отладки: если пользователь не аутентифицирован, пытаемся найти пользователя по email из запроса
        $user = Auth::user();
        
        if (!$user && $request->has('email')) {
            $user = User::where('email', $request->email)->first();
        }
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }
        
        // Проверяем, существует ли уже такой адрес
        $existingAddress = FavoriteAddress::where('user_id', $user->id)
            ->where('address', $request->address)
            ->first();
            
        if ($existingAddress) {
            return response()->json([
                'success' => false,
                'message' => 'Этот адрес уже добавлен в избранное'
            ], 422);
        }
        
        // Создаем новый адрес
        $address = new FavoriteAddress([
            'address' => $request->address,
            'title' => $request->title,
            'user_id' => $user->id
        ]);
        
        $address->save();
        
        // Возвращаем обновленный профиль пользователя через getProfile
        return $this->getProfile($request);
    }
    
    /**
     * Удалить любимый адрес
     */
    public function deleteFavoriteAddress(Request $request, $id)
    {
        // Для отладки: если пользователь не аутентифицирован, пытаемся найти пользователя по email из запроса
        $user = Auth::user();
        
        if (!$user && $request->has('email')) {
            $user = User::where('email', $request->email)->first();
        }
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }
        
        $address = FavoriteAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Адрес не найден'
            ], 404);
        }
        
        $address->delete();
        
        // Возвращаем обновленный профиль пользователя через getProfile
        return $this->getProfile($request);
    }
} 