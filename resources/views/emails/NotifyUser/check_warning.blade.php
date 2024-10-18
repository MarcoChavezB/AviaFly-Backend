
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de chequeo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        p {
            color: #555;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.8em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hola, {{ $employee->name }} {{ $employee->last_names }}</h1>
        <p>Este es un recordatorio para que verifiques tu hora de fin de comida.</p>
        <p>Han pasado <strong>{{ $timeDifference }}</strong> minutos desde que registraste tu última hora de comida.</p>
        <p>Por favor, asegúrate de marcar tu salida de la hora de comida.</p>

        <div class="footer">
            <p>Correo generado automaticamente</p>
        </div>
    </div>
</body>
</html>
