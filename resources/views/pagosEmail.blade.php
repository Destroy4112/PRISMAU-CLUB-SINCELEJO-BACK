<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Sincelejo - Notificación de Pago</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f8f8;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
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
                                        <h3 style="color: #015712; font-family: Georgia, serif; margin: 0;">Estimado(a) Socio(a),</h3>
                                    </td>
                                    <td style="text-align: right; color: #666; width: 30%;">
                                        <strong>Fecha:</strong> {{ $fecha }}
                                    </td>
                                </tr>
                            </table>
                            <p>Le informamos sobre el estado de su pago de membresía del Club Sincelejo:</p>
                            <div style="background-color: #f0f0f0; border: 2px solid #0d9b50; border-radius: 5px; padding: 20px; margin: 20px 0;">
                                <table width="100%" style="border-collapse: separate; border-spacing: 0;">
                                    <tr>
                                        <td style="padding: 10px 0; border-bottom: 1px solid #ddd; font-weight: bold; color: #015712;">Estado del Pago:</td>
                                        <td style="padding: 10px 0; border-bottom: 1px solid #ddd; text-align: right; font-weight: bold;">
                                            {{ $estado }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 0; border-bottom: 1px solid #ddd; font-weight: bold; color: #015712;">Monto:</td>
                                        <td style="padding: 10px 0; border-bottom: 1px solid #ddd; text-align: right;">{{ $monto }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 0; border-bottom: 1px solid #ddd; font-weight: bold; color: #015712;">Período:</td>
                                        <td style="padding: 10px 0; border-bottom: 1px solid #ddd; text-align: right;">{{ $periodo }}</td>
                                    </tr>
                                </table>
                            </div>                       
                            <p>Agradecemos su puntualidad en el pago de su membresía. Su apoyo nos permite seguir ofreciendo servicios de calidad a todos nuestros socios.</p>
                                <p>Recuerde que puede realizar su pago a través de los siguientes métodos:</p>
                                <ul style="padding-left: 20px; color: #015712;">
                                    <li>Transferencia bancaria</li>
                                    <li>Pago en línea a través de nuestra página web</li>
                                    <li>Pago presencial en nuestras oficinas</li>
                                </ul>                   
                            <p>Si tiene alguna pregunta o necesita información adicional, no dude en contactar a nuestro departamento de membresías.</p>
                            <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px; color: #666;">
                                <p style="margin: 0;">Atentamente,</p>
                                <p style="margin: 5px 0; font-weight: bold; color: #015712;">Departamento de Membresías</p>
                                <p style="margin: 0; font-style: italic;">Club Sincelejo</p>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #0d9b50; color: #ffffff; text-align: center; padding: 15px; font-size: 12px;">
                            <p style="margin: 0;">© {{ \Carbon\Carbon::now()->year }} Club Sincelejo. Todos los derechos reservados.</p>
                            <p style="margin: 5px 0 0;">CALLE 38 NO 34 184, Sincelejo, Sucre, Colombia</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>