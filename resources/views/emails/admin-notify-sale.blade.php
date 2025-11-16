<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notificación de Venta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .content {
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            font-size: 0.8em;
            color: #777;
        }
        .sale-details {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
        }
        .sale-details p {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Notificación de Nueva Venta</h1>
        </div>
        <div class="content">
            <p>Se ha registrado una nueva venta en el sistema.</p>
            <div class="sale-details">
                <p><strong>Fecha y Hora:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
                <p><strong>Cliente:</strong> {{ $customerName }}</p>
                <p><strong>Empleado Responsable:</strong> {{ $employeeName }}</p>
                <p><strong>Monto Total:</strong> ${{ $totalAmount }}</p>
            </div>
        </div>
            <p>Correo automatizado, no responder</p>
        </div>
    </div>
</body>
</html>
