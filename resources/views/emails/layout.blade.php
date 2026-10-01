<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background-color:#ffffff;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#7366ff;padding:16px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-right:10px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 64 64">
                                            <circle cx="32" cy="32" r="20" fill="none" stroke="#ffffff" stroke-width="5" stroke-linecap="round" stroke-dasharray="91 35" transform="rotate(-40 32 32)"/>
                                            <circle cx="32" cy="32" r="27.5" fill="none" stroke="#ffffff" stroke-width="4" stroke-linecap="round" stroke-dasharray="44 129" opacity="0.6" transform="rotate(120 32 32)"/>
                                            <circle cx="32" cy="32" r="7.5" fill="#ffffff"/>
                                        </svg>
                                    </td>
                                    <td>
                                        <span style="color:#ffffff;font-size:18px;font-weight:bold;">{{ config('app.name') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;color:#2b2b2b;font-size:14px;line-height:1.6;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background-color:#f8f8fb;color:#8a8a8a;font-size:12px;">
                            Este é um e-mail automático, não é necessário responder.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
