<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido confirmado</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#92400e,#d97706);padding:28px 32px;">
                            <p style="margin:0;color:rgba(255,255,255,0.85);font-size:12px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">
                                {{ config('app.name') }}
                            </p>
                            <h1 style="margin:8px 0 0;color:#ffffff;font-size:22px;font-weight:800;line-height:1.3;">
                                ¡Gracias por tu compra!
                            </h1>
                            <p style="margin:6px 0 0;color:rgba(255,255,255,0.9);font-size:14px;">
                                Pedido {{ $pedido->numero_pedido }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 20px;color:#334155;font-size:15px;line-height:1.6;">
                                Confirmamos tu pago. Ya estamos preparando tu pedido.
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                @foreach ($pedido->items as $item)
                                    <tr>
                                        <td style="padding:8px 0;border-bottom:1px solid #f1f5f9;color:#334155;font-size:14px;">
                                            {{ $item->nombre_producto }}
                                            @if ($item->variante_nombre)
                                                <span style="color:#94a3b8;">({{ $item->variante_nombre }})</span>
                                            @endif
                                            <span style="color:#94a3b8;"> x{{ $item->cantidad }}</span>
                                        </td>
                                        <td style="padding:8px 0;border-bottom:1px solid #f1f5f9;color:#334155;font-size:14px;text-align:right;">
                                            ${{ number_format((float) $item->subtotal, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;">
                                <tr>
                                    <td style="color:#64748b;font-size:14px;">Envío</td>
                                    <td style="color:#334155;font-size:14px;text-align:right;">${{ number_format((float) $pedido->costo_envio, 2, ',', '.') }}</td>
                                </tr>
                                @if ((float) $pedido->descuento > 0)
                                    <tr>
                                        <td style="color:#64748b;font-size:14px;">Descuento</td>
                                        <td style="color:#16a34a;font-size:14px;text-align:right;">-${{ number_format((float) $pedido->descuento, 2, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="color:#0f172a;font-size:16px;font-weight:700;padding-top:8px;">Total</td>
                                    <td style="color:#0f172a;font-size:16px;font-weight:700;text-align:right;padding-top:8px;">${{ number_format((float) $pedido->total, 2, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;border-top:1px solid #f1f5f9;">
                            <p style="margin:0;color:#94a3b8;font-size:12px;">
                                Desarrollado por <span style="color:#64748b;font-weight:600;">Overcloud</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
