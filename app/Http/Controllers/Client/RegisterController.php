<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmailService;
use Illuminate\Support\Facades\Session;
use App\Models\EmailCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Создаем пользователя сразу, но с неподтвержденным email
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'client'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно создан',
                'user_id' => $user->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Ошибка при регистрации: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при регистрации'
            ], 500);
        }
    }

    public function sendCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Проверяем существование пользователя
            $user = User::where('email', $request->email)
                       ->whereNull('email_verified_at')
                       ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден или email уже подтвержден'
                ], 422);
            }

            $code = rand(100000, 999999);
            EmailCode::updateOrCreate(
                ['email' => $request->email],
                ['code' => $code, 'created_at' => now()]
            );

            $this->emailService->sendVerificationCode($request->email, $code);

            return response()->json([
                'success' => true,
                'message' => 'Код подтверждения отправлен'
            ]);

        } catch (\Exception $e) {
            \Log::error('Ошибка при отправке кода: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке кода подтверждения'
            ], 500);
        }
    }

    public function verifyCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'code' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $emailCode = EmailCode::where('email', $request->email)
                ->where('code', $request->code)
                ->first();

            if (!$emailCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный код подтверждения'
                ], 422);
            }

            // Проверяем срок действия кода (30 минут)
            if ($emailCode->created_at->addMinutes(30)->isPast()) {
                $emailCode->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Код подтверждения истек'
                ], 422);
            }

            // Подтверждаем email пользователя
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $user->email_verified_at = now();
                $user->save();

                // Удаляем использованный код
                $emailCode->delete();

                // Авторизуем пользователя
                Auth::login($user);

                return response()->json([
                    'success' => true,
                    'message' => 'Email подтвержден, регистрация завершена',
                    'token' => $user->createToken('auth_token')->plainTextToken
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Ошибка при проверке кода: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при проверке кода'
            ], 500);
        }
    }
} 