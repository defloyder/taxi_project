<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteAddress extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово заполнять
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'address',
        'title',
    ];

    /**
     * Получить пользователя, которому принадлежит этот адрес
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 