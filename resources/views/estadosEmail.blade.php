<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrismaU</title>
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
                        <td
                            style="display: flex; width: 100%; background-color: #0d9b50; padding: 10px 30px; text-align: center; gap: 10px;">
                            {{-- <img src="{{ asset('img/club.png') }}" alt="Logo Club Sincelejo"
                                style="width: 40px; object-fit: contain;" /> --}}
                            <h1
                                style="color: #ffffff; font-family: Georgia, serif; margin-bottom: 20px; text-align: center;">
                                Club Sincelejo</h1>
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
                            <p>Estimado(a) socio(a),</p>
                            <p>Nos dirigimos a usted para informarle que su estado de membresía en el Club Sincelejo ha
                                sido actualizado. A continuación, encontrará los detalles:</p>
                            <table width="100%"
                                style="border-collapse: separate; border-spacing: 0; margin: 20px 0; border: 1px solid #ddd;">
                                <tr>
                                    <td
                                        style="background-color: #f8f8f8; border-bottom: 1px solid #ddd; padding: 15px; width: 30%; font-weight: bold; color: #015712;">
                                        Estado Actual:</td>
                                    <td
                                        style="background-color: #fff; border: 1px solid #ddd; padding: 15px; color: #cc0000; font-weight: bold;">
                                        {{ $estado }}</td>
                                </tr>
                                <tr>
                                    <td
                                        style="background-color: #f8f8f8; border-bottom: 1px solid #ddd; padding: 15px; width: 30%; font-weight: bold; color: #015712;">
                                        Motivo:</td>
                                    <td style="border: 1px solid #ddd; padding: 15px;">{{ $motivo }}</td>
                                </tr>
                            </table>
                            <p>Si tiene alguna inquietud respecto a esta actualización o desea obtener más información,
                                por favor no dude en contactar a la gerencia del club. Estaremos encantados de
                                asistirle.</p>
                            <p>Agradecemos su comprensión y esperamos poder resolver cualquier inconveniente a la
                                brevedad posible.</p>
                            <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px; color: #666;">
                                <p>Atentamente,</p>
                                <p style="font-weight: bold; color: #015712;">Gerencia</p>
                                <p style="font-style: italic;">Club Sincelejo</p>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td
                            style="background-color: #0d9b50; color: #ffffff; text-align: center; padding: 15px; font-size: 12px;">
                            <p>© {{ \Carbon\Carbon::now()->year }} Club Sincelejo. Todos los derechos reservados.</p>
                            <p>CALLE 38 NO 34 184, Sincelejo, Sucre, Colombia</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
