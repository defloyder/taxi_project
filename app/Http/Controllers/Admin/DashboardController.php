<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index()
    {
        $statistics = [
            'total_orders' => Order::count(),
            'active_orders' => Order::whereIn('status', ['pending', 'accepted', 'in_progress'])->count(),
            'total_drivers' => User::where('role', 'driver')->count(),
            'active_drivers' => User::where('role', 'driver')->where('is_available', true)->count(),
            'total_clients' => User::where('role', 'client')->count(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $statistics
        ]);
    }

    public function users()
    {
        try {
            $users = User::select('id', 'name', 'email', 'phone', 'role', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone ?? '',
                        'role' => $user->role,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении списка пользователей: ' . $e->getMessage()
            ], 500);
        }
    }

    public function orders()
    {
        $orders = Order::with(['client:id,name,email,phone', 'driver:id,name,email,phone'])
            ->select('id', 'client_id', 'driver_id', 'status', 'price', 'car_class', 'car_type', 'start_time', 'end_time', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    public function statistics()
    {
        $monthlyStats = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as total_orders, SUM(price) as total_revenue')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        $driverStats = Order::selectRaw('driver_id, COUNT(*) as total_orders, AVG(price) as avg_order_price')
            ->whereNotNull('driver_id')
            ->groupBy('driver_id')
            ->with('driver:id,name,email')
            ->get();

        return response()->json([
            'success' => true,
            'monthly_stats' => $monthlyStats,
            'driver_stats' => $driverStats
        ]);
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['client', 'driver', 'admin'])],
            'phone' => 'nullable|string|max:20',
            'car_number' => 'required_if:role,driver|nullable|string',
            'car_model' => 'required_if:role,driver|nullable|string',
            'car_class' => 'required_if:role,driver|nullable|in:economy,comfort,business',
            'car_type' => 'required_if:role,driver|nullable|in:sedan,minivan,suv'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'car_number' => $validated['car_number'] ?? null,
            'car_model' => $validated['car_model'] ?? null,
            'car_class' => $validated['car_class'] ?? null,
            'car_type' => $validated['car_type'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Пользователь успешно создан',
            'user' => $user
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['client', 'driver', 'admin'])],
            'phone' => 'nullable|string|max:20',
            'car_number' => 'required_if:role,driver|nullable|string',
            'car_model' => 'required_if:role,driver|nullable|string',
            'car_class' => 'required_if:role,driver|nullable|in:economy,comfort,business',
            'car_type' => 'required_if:role,driver|nullable|in:sedan,minivan,suv'
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Данные пользователя обновлены',
            'user' => $user->fresh()
        ]);
    }

    public function deleteUser(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Пользователь удален'
        ]);
    }
} 