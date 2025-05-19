<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'driver_id',
        'start_location',
        'end_location',
        'start_address',
        'end_address',
        'status',
        'price',
        'car_class',
        'car_type',
        'start_time',
        'end_time',
        'waiting_time',
    ];

    protected $casts = [
        'start_location' => 'array',
        'end_location' => 'array',
        'price' => 'decimal:2',
        'waiting_time' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
} 