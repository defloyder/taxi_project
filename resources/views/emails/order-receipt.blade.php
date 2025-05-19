<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Чек заказа такси</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .receipt {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .details {
            margin-bottom: 20px;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details th, .details td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>Чек заказа такси</h1>
            <p>Номер заказа: {{ $order->id }}</p>
            <p>Дата: {{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>

        <div class="details">
            <table>
                <tr>
                    <th>Клиент:</th>
                    <td>{{ $order->client->name }}</td>
                </tr>
                <tr>
                    <th>Водитель:</th>
                    <td>{{ $order->driver->name }}</td>
                </tr>
                <tr>
                    <th>Откуда:</th>
                    <td>{{ $order->start_address }}</td>
                </tr>
                <tr>
                    <th>Куда:</th>
                    <td>{{ $order->end_address }}</td>
                </tr>
                <tr>
                    <th>Класс автомобиля:</th>
                    <td>{{ $order->car_class }}</td>
                </tr>
                <tr>
                    <th>Тип автомобиля:</th>
                    <td>{{ $order->car_type }}</td>
                </tr>
                <tr>
                    <th>Время начала:</th>
                    <td>{{ $order->start_time ? $order->start_time->format('H:i') : '-' }}</td>
                </tr>
                <tr>
                    <th>Время окончания:</th>
                    <td>{{ $order->end_time ? $order->end_time->format('H:i') : '-' }}</td>
                </tr>
                @if($order->waiting_time)
                <tr>
                    <th>Время ожидания:</th>
                    <td>{{ $order->waiting_time }} мин.</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="total">
            Итого к оплате: {{ number_format($order->price, 2) }} ₽
        </div>
    </div>
</body>
</html> 