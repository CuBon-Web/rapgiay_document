<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Mã OTP đặt lại mật khẩu</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;">
                    <tr>
                        <td style="padding:28px 32px 16px;">
                            <h2 style="margin:0;font-size:22px;color:#1f4f78;">Đặt lại mật khẩu</h2>
                            @if ($customerName)
                                <p style="margin:12px 0 0;font-size:14px;color:#64748b;">Xin chào <strong>{{ $customerName }}</strong>,</p>
                            @endif
                            <p style="margin:12px 0 0;font-size:14px;color:#64748b;line-height:1.6;">
                                Bạn đã yêu cầu đặt lại mật khẩu. Sao chép mã OTP bên dưới và nhập vào trang đặt lại mật khẩu. Mã có hiệu lực trong <strong>10 phút</strong>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:8px 32px 24px;">
                            <div style="display:inline-block;padding:16px 32px;background:#f4f7fb;border:2px dashed #1f4f78;border-radius:10px;font-size:32px;font-weight:700;letter-spacing:8px;color:#1f4f78;font-family:monospace;">
                                {{ $otp }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 28px;font-size:13px;color:#94a3b8;line-height:1.6;">
                            Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này. Mật khẩu của bạn sẽ không thay đổi.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
