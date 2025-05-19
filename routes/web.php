<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Client\RegisterController;
use App\Http\Controllers\Client\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Маршруты аутентификации
Auth::routes(['verify' => true, 'login' => false, 'register' => false]);

// Маршруты для клиентов
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [ClientDashboardController::class, 'orders'])->name('orders');
    Route::post('/order', [ClientDashboardController::class, 'store'])->name('order.store');
});

// Маршруты для водителей
Route::middleware(['auth', 'role:driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/', [DriverDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [DriverDashboardController::class, 'orders'])->name('orders');
    Route::post('/location', [DriverDashboardController::class, 'updateLocation'])->name('location.update');
    Route::post('/availability', [DriverDashboardController::class, 'toggleAvailability'])->name('availability.toggle');
});

// Маршруты для заказов (общие)
Route::middleware(['auth'])->prefix('orders')->name('orders.')->group(function () {
    Route::patch('/{order}/accept', [OrderController::class, 'acceptOrder'])->name('accept');
    Route::patch('/{order}/start', [OrderController::class, 'startOrder'])->name('start');
    Route::patch('/{order}/complete', [OrderController::class, 'completeOrder'])->name('complete');
    Route::patch('/{order}/cancel', [OrderController::class, 'cancelOrder'])->name('cancel');
});

// Маршруты для администраторов
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::get('/orders', [AdminDashboardController::class, 'orders'])->name('orders');
    Route::get('/statistics', [AdminDashboardController::class, 'statistics'])->name('statistics');
    
    // Управление пользователями
    Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminDashboardController::class, 'deleteUser'])->name('users.delete');
});

// Маршруты для регистрации с кодом
Route::post('/send-code', [RegisterController::class, 'sendCode']);
Route::post('/verify-code', [RegisterController::class, 'verifyCode']);

// Маршрут для логина пользователя через LoginController
Route::post('/login', [LoginController::class, 'login']);
