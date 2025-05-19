<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Администратор
        User::create([
            'name' => 'Admin',
            'email' => 'admin@taxi.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Водитель
        User::create([
            'name' => 'Driver',
            'email' => 'driver@taxi.com',
            'password' => Hash::make('driver123'),
            'role' => 'driver',
            'email_verified_at' => now(),
            'is_available' => true,
            'car_number' => 'A123BC',
            'car_model' => 'Toyota Camry',
        ]);

        // Создаем водителей
        User::create([
            'name' => 'Driver 1',
            'email' => 'driver1@taxi.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'is_available' => true,
            'car_number' => 'B456DE',
            'car_model' => 'Hyundai Solaris'
        ]);

        // Создаем клиентов
        User::create([
            'name' => 'Client 1',
            'email' => 'client1@taxi.com',
            'password' => Hash::make('password'),
            'role' => 'client'
        ]);

        User::create([
            'name' => 'Client 2',
            'email' => 'client2@taxi.com',
            'password' => Hash::make('password'),
            'role' => 'client'
        ]);
    }
}
