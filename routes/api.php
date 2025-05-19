<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\RegisterController;
use App\Http\Controllers\Client\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Временно доступно без auth:sanctum для отладки
// Профиль пользователя
Route::get('/profile', [ProfileController::class, 'getProfile']);
Route::post('/profile', [ProfileController::class, 'updateProfile']);

// Обновление роли пользователя
Route::post('/profile/role', [ProfileController::class, 'updateRole']);

// Любимые адреса
Route::post('/profile/addresses', [ProfileController::class, 'addFavoriteAddress']);
Route::delete('/profile/addresses/{id}', [ProfileController::class, 'deleteFavoriteAddress']);

// Маршруты аутентификации и регистрации
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/email/verify/send', [RegisterController::class, 'sendCode']);
Route::post('/email/verify', [RegisterController::class, 'verifyCode']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Профиль пользователя
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    
    // Роли
    Route::get('/profile/role', [ProfileController::class, 'getRole']);
    Route::post('/profile/role', [ProfileController::class, 'updateRole']);
    
    // Любимые адреса
    Route::post('/profile/addresses', [ProfileController::class, 'addFavoriteAddress']);
    Route::delete('/profile/addresses/{id}', [ProfileController::class, 'deleteFavoriteAddress']);

    // Маршруты для заказов такси
    Route::get('/orders/history', [OrderController::class, 'history']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}', [OrderController::class, 'update']);
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
});

// Маршруты для админ панели
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::get('/users', [App\Http\Controllers\Admin\DashboardController::class, 'users']);
    Route::get('/orders', [App\Http\Controllers\Admin\DashboardController::class, 'orders']);
    Route::get('/statistics', [App\Http\Controllers\Admin\DashboardController::class, 'statistics']);
    
    // Управление пользователями
    Route::post('/users', [App\Http\Controllers\Admin\DashboardController::class, 'storeUser']);
    Route::put('/users/{user}', [App\Http\Controllers\Admin\DashboardController::class, 'updateUser']);
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\DashboardController::class, 'deleteUser']);
});
