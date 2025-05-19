<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'location',
        'is_available',
        'car_number',
        'car_model',
        'car_class',
        'car_type',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'location' => 'array',
        'is_available' => 'boolean',
        'role' => 'string'
    ];

    /**
     * Получить доступные роли
     */
    public static function getAvailableRoles(): array
    {
        return ['user', 'admin', 'client', 'driver'];
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function ordersAsClient()
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function ordersAsDriver()
    {
        return $this->hasMany(Order::class, 'driver_id');
    }

    /**
     * Получить профиль пользователя
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Получить любимые адреса пользователя
     */
    public function favoriteAddresses()
    {
        return $this->hasMany(FavoriteAddress::class);
    }
}
