<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_location' => 'required|array',
            'start_location.lat' => 'required|numeric',
            'start_location.lng' => 'required|numeric',
            'end_location' => 'required|array',
            'end_location.lat' => 'required|numeric',
            'end_location.lng' => 'required|numeric',
            'car_class' => 'required|in:economy,comfort,business',
            'car_type' => 'required|in:sedan,minivan,suv',
            'start_address' => 'required|string',
            'end_address' => 'required|string',
        ]);

        $order = Order::create([
            'client_id' => auth()->id(),
            'start_location' => $validated['start_location'],
            'end_location' => $validated['end_location'],
            'start_address' => $validated['start_address'],
            'end_address' => $validated['end_address'],
            'status' => 'pending',
            'car_class' => $validated['car_class'],
            'car_type' => $validated['car_type'],
        ]);

        // Здесь будет логика поиска ближайшего водителя
        
        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load(['client', 'driver']));
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|in:accepted,in_progress,completed,cancelled',
            'driver_id' => 'sometimes|required|exists:users,id',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date',
            'waiting_time' => 'sometimes|required|integer',
            'price' => 'sometimes|required|numeric',
        ]);

        $order->update($validated);

        // Если заказ завершен, отправляем чек
        if ($order->status === 'completed' && $order->price > 0) {
            $this->emailService->sendOrderReceipt($order->fresh()->load(['client', 'driver']));
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->fresh()
        ]);
    }

    public function index(): JsonResponse
    {
        $orders = Order::with(['client', 'driver'])
            ->when(auth()->user()->role === 'driver', function ($query) {
                return $query->where('driver_id', auth()->id());
            })
            ->when(auth()->user()->role === 'client', function ($query) {
                return $query->where('client_id', auth()->id());
            })
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function acceptOrder(Order $order): JsonResponse
    {
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order cannot be accepted'], 400);
        }

        $order->update([
            'status' => 'accepted',
            'driver_id' => Auth::id(),
            'start_time' => now()
        ]);

        return response()->json([
            'message' => 'Order accepted successfully',
            'order' => $order->fresh()
        ]);
    }

    public function startOrder(Order $order): JsonResponse
    {
        if ($order->status !== 'accepted') {
            return response()->json(['message' => 'Order cannot be started'], 400);
        }

        $order->update([
            'status' => 'in_progress'
        ]);

        return response()->json([
            'message' => 'Order started successfully',
            'order' => $order->fresh()
        ]);
    }

    public function completeOrder(Order $order): JsonResponse
    {
        if ($order->status !== 'in_progress') {
            return response()->json(['message' => 'Order cannot be completed'], 400);
        }

        $order->update([
            'status' => 'completed',
            'end_time' => now()
        ]);

        // Отправляем чек после завершения заказа
        if ($order->price > 0) {
            $this->emailService->sendOrderReceipt($order->fresh()->load(['client', 'driver']));
        }

        return response()->json([
            'message' => 'Order completed successfully',
            'order' => $order->fresh()
        ]);
    }

    public function cancelOrder(Order $order): JsonResponse
    {
        if (!in_array($order->status, ['pending', 'accepted'])) {
            return response()->json(['message' => 'Order cannot be cancelled'], 400);
        }

        $order->update([
            'status' => 'cancelled'
        ]);

        return response()->json([
            'message' => 'Order cancelled successfully',
            'order' => $order->fresh()
        ]);
    }
} 