<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido al sistema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 10px 0;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Acceso al Sistema</h1>
        </div>

        <div class="content">
            <p>Estimado(a) usuario,</p>

            <p>Nos complace informarle que su acceso al sistema ha sido habilitado exitosamente. Para iniciar sesión, deberá utilizar los siguientes datos:</p>

            <ul>
                <li><strong>Matrícula (Identificación de usuario):</strong> {{ $userIdentification }}</li>
                <li><strong>Contraseña:</strong> La CURP que proporcionó previamente al administrador</li>
            </ul>

            <p>Por favor, asegúrese de ingresar su identificación de usuario como matrícula y su CURP como contraseña en el sistema. En caso de que experimente alguna dificultad al acceder o necesite asistencia, no dude en ponerse en contacto con nuestro equipo de soporte a través de los canales oficiales.</p>

            <p>Agradecemos su confianza en nuestro sistema.</p>
        </div>

        <div class="footer">
            <p>Este es un mensaje generado automáticamente, por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
