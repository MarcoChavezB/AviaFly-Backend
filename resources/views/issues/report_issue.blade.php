<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Reporte en Sistema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nuevo Reporte en el Sistema</h1>
        <p><strong>Estimado equipo,</strong></p>
        <p>Se ha reportado un nuevo problema en el sistema por parte de un estudiante. A continuación, se detallan los datos del reporte:</p>

        <p><strong>Nombre del Estudiante:</strong> {{ $student->name }}</p>
        <p><strong>Identificación del Estudiante:</strong> {{ $student->user_identification }}</p>
        <p><strong>URL del Error:</strong> <a href="{{ $urlError }}">{{ $urlError }}</a></p>
        <p><strong>Descripción del Problema:</strong></p>
        <p>{{ $description }}</p>

        <p>Por favor, revisen este reporte a la brevedad posible.</p>
        <p>Gracias,</p>
        <p>El equipo de soporte.</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Todos los derechos reservados.</p>
    </div>
</body>
</html>
