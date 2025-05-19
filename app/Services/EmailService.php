<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Отправить чек заказа на email клиента
     */
    public function sendOrderReceipt(Order $order)
    {
        try {
            Mail::send(
                'emails.order-receipt',
                ['order' => $order],
                function ($message) use ($order) {
                    $message->from(config('mail.from.address'), config('mail.from.name'))
                        ->to($order->client->email)
                        ->subject("Чек заказа такси #{$order->id}");
                }
            );

            return true;
        } catch (\Exception $e) {
            \Log::error('Ошибка при отправке чека: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'client_email' => $order->client->email
            ]);
            return false;
        }
    }

    /**
     * Отправить код подтверждения (существующий функционал)
     */
    public function sendVerificationCode(string $email, string $code)
    {
        try {
            Mail::raw(
                "Ваш код подтверждения для регистрации в TaxShare: $code",
                function ($message) use ($email) {
                    $message->from(config('mail.from.address'), config('mail.from.name'))
                        ->to($email)
                        ->subject('Код подтверждения TaxShare');
                }
            );

            return true;
        } catch (\Exception $e) {
            \Log::error('Ошибка при отправке кода подтверждения: ' . $e->getMessage(), [
                'email' => $email
            ]);
            return false;
        }
    }
} 