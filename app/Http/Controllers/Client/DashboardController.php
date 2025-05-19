<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('client.dashboard');
    }

    public function orders()
    {
        $orders = Order::where('client_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.orders', compact('orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_location' => 'required|json',
            'end_location' => 'required|json',
            'car_class' => 'required|in:economy,comfort,business',
            'car_type' => 'required|in:sedan,minivan,suv'
        ]);

        $order = Order::create([
            'client_id' => auth()->id(),
            'start_location' => json_decode($validated['start_location'], true),
            'end_location' => json_decode($validated['end_location'], true),
            'status' => 'pending',
            'car_class' => $validated['car_class'],
            'car_type' => $validated['car_type']
        ]);

        return redirect()->route('client.orders')
            ->with('success', 'Заказ успешно создан! Ожидайте подтверждения от водителя.');
    }
} 