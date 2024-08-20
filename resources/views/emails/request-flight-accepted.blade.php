<!-- resources/views/emails/request-flight-accepted.blade.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petición de Vuelo Aceptada</title>
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
            color: #5cb85c; /* Color verde para indicar aceptación */
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

        .details {
            background-color: #d4edda;
            border-left: 5px solid #5cb85c;
            padding: 10px;
            margin-top: 20px;
            color: #155724;
            border-radius: 5px;
        }

        .comments {
            background-color: #e9ecef;
            border-left: 5px solid #6c757d;
            padding: 10px;
            margin-top: 20px;
            color: #495057;
            border-radius: 5px;
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
        <h2>Petición de Vuelo Aceptada</h2>

        <!-- Información del Estudiante -->
        <p><strong>Nombre del Estudiante:</strong> {{ $student->name }} {{ $student->last_names }}</p>
        <p><strong>Matrícula del Estudiante:</strong> {{ $student->user_identification }}</p>

        <!-- Información del Vuelo -->
        <p><strong>Fecha del Vuelo:</strong> {{ $flight->flight_date }}</p>
        <p><strong>Hora del Vuelo:</strong> {{ $flight->flight_hour }}</p>
        <p><strong>Tipo de Vuelo:</strong> {{ ucfirst($flight->type_flight) }}</p>

        <!-- Mensaje de Aceptación -->
        <div class="details">
            <p>Tu solicitud de vuelo ha sido aceptada. Por favor, asegúrate de estar preparado para tu vuelo en la fecha y hora indicadas.</p>
        </div>

        <!-- Comentarios Adicionales -->
        @if($comment)
            <div class="comments">
                <p><strong>Comentario:</strong> {{ $comment }}</p>
            </div>
        @endif

        <!-- Mensaje Adicional -->
        <p>Si tienes alguna pregunta, no dudes en ponerte en contacto con la dirección.</p>

        <div class="footer">
            <p>Este es un correo automático. Por favor, no responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>

