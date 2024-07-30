<!-- resources/views/emails/employee-entry-notification.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notificación de Registro de Entrada</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Notificación de Registro de Entrada</h1>
        </div>
        <div class="content">
            <p>Se registro su asistencia en el sistema.</p>
            <p><strong>Fecha y Hora:</strong> {{ $entryDateTime }}</p>
            <p><strong>Su nombre:</strong> {{ $employeeName }}</p>
        </div>
        <div class="footer">
            <p>Atentamente,</p>
            <p>El equipo de administración</p>
        </div>
    </div>
</body>
</html>


