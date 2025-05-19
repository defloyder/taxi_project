<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $activeOrders = Order::where('status', 'pending')
            ->where(function($query) {
                $query->where('car_class', Auth::user()->car_class)
                    ->where('car_type', Auth::user()->car_type);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('driver.dashboard', compact('activeOrders'));
    }

    public function orders()
    {
        $orders = Order::where('driver_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('driver.orders', compact('orders'));
    }

    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|array',
            'location.lat' => 'required|numeric',
            'location.lng' => 'required|numeric'
        ]);

        Auth::user()->update([
            'location' => $validated['location']
        ]);

        return response()->json(['message' => 'Местоположение обновлено']);
    }

    public function toggleAvailability()
    {
        $user = Auth::user();
        $user->is_available = !$user->is_available;
        $user->save();

        return response()->json([
            'message' => $user->is_available ? 'Вы теперь доступны для заказов' : 'Вы теперь недоступны для заказов',
            'is_available' => $user->is_available
        ]);
    }
} 