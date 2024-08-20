<!-- resources/views/emails/request-flight.blade.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Reserva de Vuelo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .modal-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 20px;
            text-align: center;
        }

        h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        p {
            color: #555;
            font-size: 16px;
            margin: 10px 0;
            line-height: 1.5;
        }

        strong {
            color: #000;
        }

        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="modal-container">
        <h2>Detalles de la Reserva de Vuelo</h2>

        <!-- Información del Estudiante -->
        <p><strong>Nombre del Estudiante:</strong> {{ $student->name }}{{ $student->last_names}} {{$student->user_identification}}</p>
        <p><strong>Email del Estudiante:</strong> {{ $student->email }}</p>

        <!-- Información del Vuelo -->
        <p><strong>Fecha del Vuelo:</strong> {{ $flight->flight_date }}</p>
        <p><strong>Hora del Vuelo:</strong> {{ $flight->flight_hour }}</p>
        <p><strong>Tipo de Vuelo:</strong> {{ ucfirst($flight->type_flight) }}</p>

        <p>Para más detalles, por favor revise la plataforma.</p>

        <div class="footer">
            <p>Este es un correo automático. Por favor, no responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>

