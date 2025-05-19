<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $client = User::where('role', 'client')->first();
        $driver = User::where('role', 'driver')->first();

        // Создаем несколько тестовых заказов
        Order::create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'start_location' => ['lat' => 55.751244, 'lng' => 37.618423], // Москва, Кремль
            'end_location' => ['lat' => 55.755826, 'lng' => 37.617300], // Москва, Красная площадь
            'status' => 'completed',
            'price' => 500.00,
            'car_class' => 'comfort',
            'car_type' => 'sedan',
            'start_time' => now()->subHours(2),
            'end_time' => now()->subHours(1),
            'waiting_time' => 0
        ]);

        Order::create([
            'client_id' => $client->id,
            'start_location' => ['lat' => 55.751244, 'lng' => 37.618423],
            'end_location' => ['lat' => 55.755826, 'lng' => 37.617300],
            'status' => 'pending',
            'car_class' => 'business',
            'car_type' => 'suv',
            'waiting_time' => 0
        ]);
    }
}
