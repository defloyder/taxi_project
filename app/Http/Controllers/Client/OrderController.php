<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function history()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }
            
            $orders = Order::where('client_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении истории заказов: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении истории заказов'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }
            
            $validatedData = $request->validate([
                'start_location.lat' => 'required|numeric',
                'start_location.lng' => 'required|numeric',
                'end_location.lat' => 'required|numeric',
                'end_location.lng' => 'required|numeric',
                'start_address' => 'required|string',
                'end_address' => 'required|string',
                'car_class' => 'required|string|in:economy,comfort,business',
                'car_type' => 'required|string|in:sedan,minivan,suv'
            ]);
            
            $order = new Order();
            $order->client_id = $user->id;
            $order->start_location = json_encode($validatedData['start_location']);
            $order->end_location = json_encode($validatedData['end_location']);
            $order->start_address = $validatedData['start_address'];
            $order->end_address = $validatedData['end_address'];
            $order->car_class = $validatedData['car_class'];
            $order->car_type = $validatedData['car_type'];
            $order->status = 'pending';
            $order->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно создан',
                'order' => $order
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании заказа: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании заказа'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }
            
            $order = Order::where('client_id', $user->id)
                ->where('id', $id)
                ->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении заказа: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении заказа'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }
            
            $order = Order::where('client_id', $user->id)
                ->where('id', $id)
                ->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден'
                ], 404);
            }
            
            // Разрешаем обновлять только определенные поля
            $validatedData = $request->validate([
                'status' => 'sometimes|string|in:pending,in_progress,completed,cancelled'
            ]);
            
            $order->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно обновлен',
                'order' => $order
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Ошибка при обновлении заказа: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении заказа'
            ], 500);
        }
    }
} 