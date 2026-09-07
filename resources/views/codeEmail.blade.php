<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Sincelejo - Recuperación de Contraseña</title>
</head>

<body
    style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f8f8;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0d9b50; padding: 20px; text-align: center;">
                            <h1 style="color: #ffffff; font-family: Georgia, serif; margin: 0;">Club Sincelejo</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <table width="100%">
                                <tr>
                                    <td style="vertical-align: top; width: 70%;">
                                        <h3 style="color: #015712; font-family: Georgia, serif; margin: 0;">Cordial
                                            saludo,</h3>
                                    </td>
                                    <td style="text-align: right; color: #666; width: 30%;">
                                        <strong>Fecha:</strong> {{ $fecha }}
                                    </td>
                                </tr>
                            </table>
                            <p>Hemos recibido una solicitud para recuperar tu contraseña de la cuenta del Club
                                Sincelejo.</p>
                            <p>Por favor, utiliza el siguiente código para restablecer tu contraseña:</p>
                            <div
                                style="background-color: #f0f0f0; border: 2px solid #0d9b50; border-radius: 5px; padding: 20px; margin: 20px 0; text-align: center;">
                                <p style="font-size: 24px; font-weight: bold; color: #015712; margin: 0;">Código de
                                    recuperación:</p>
                                <p style="font-size: 24px; font-weight: bold; color: #0d9b50; margin: 10px 0;">
                                    {{ $codigo }}</p>
                            </div>
                            <p>Si no has solicitado este cambio, por favor ignora este correo y tu contraseña
                                permanecerá sin cambios.
                            </p>
                            <p>Si tienes alguna pregunta o necesitas asistencia adicional, no dudes en contactar a
                                nuestro equipo de soporte.</p>
                            <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px; color: #666;">
                                <p style="margin: 0;">Atentamente,</p>
                                <p style="margin: 5px 0; font-weight: bold; color: #015712;">Equipo de Soporte</p>
                                <p style="margin: 0; font-style: italic;">Club Sincelejo</p>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td
                            style="background-color: #0d9b50; color: #ffffff; text-align: center; padding: 15px; font-size: 12px;">
                            <p style="margin: 0;">© {{ \Carbon\Carbon::now()->year }} Club Sincelejo. Todos los derechos
                                reservados.</p>
                            <p style="margin: 5px 0 0;">CALLE 38 NO 34 184, Sincelejo, Sucre, Colombia</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
