<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notificación</title>
</head>
<body style="margin: 0; padding: 0; background-color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #000000;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e0e0e0;">
        <!-- Header con logo -->
        <tr>
            <td style="text-align: center; padding: 40px 0; background-color: #ffffff;">
                <img src="http://api.aviafly.mx:8080/AviaFly-Backend/public/newsletters/bases/Torreon/files/avia.png" alt="AviaFly Logo" style="max-width: 180px;">
            </td>
        </tr>

        <!-- Contenido del mensaje -->
        <tr>
            <td style="padding: 30px 40px; font-size: 16px; line-height: 1.6; color: #000000;">
                {!! nl2br(e($content)) !!}
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="text-align: center; padding: 20px 40px; font-size: 12px; color: #777777; background-color: #f9f9f9;">
                © {{ date('Y') }} AviaFly. Todos los derechos reservados.
            </td>
        </tr>
    </table>
</body>
</html>
