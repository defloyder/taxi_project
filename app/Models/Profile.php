<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово заполнять
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'phone',
        'avatar',
    ];

    /**
     * Получить пользователя, владеющего этим профилем
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 