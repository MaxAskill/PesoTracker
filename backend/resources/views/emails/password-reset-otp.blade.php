<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PesoTracker Password Reset OTP</title>
</head>

<body style="margin:0; padding:0; background:#020617; font-family:Arial, sans-serif; color:#ffffff;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#0F172A; border-radius:24px; overflow:hidden; border:1px solid #1E293B;">
                    <tr>
                        <td style="padding:40px 40px 20px 40px;">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:60px; height:60px; background:#10B981; border-radius:18px; text-align:center; font-size:32px; font-weight:bold; color:white;">
                                        P
                                    </td>
                                    <td style="padding-left:16px;">
                                        <h1 style="margin:0; font-size:30px; color:white;">
                                            Peso<span style="color:#34D399;">Tracker</span>
                                        </h1>
                                        <p style="margin:6px 0 0 0; color:#94A3B8; font-size:14px;">
                                            Password Reset
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 40px 10px 40px;">
                            <p style="color:#34D399; font-weight:bold; margin:0 0 12px 0;">
                                Password Reset OTP
                            </p>
                            <h2 style="margin:0; font-size:36px; line-height:1.2;">
                                Reset your password
                            </h2>
                            <p style="margin-top:20px; color:#CBD5E1; line-height:1.8; font-size:15px;">
                                We received a request to reset your PesoTracker password. Use the code below to continue.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:30px 40px;">
                            <div style="background:#020617; border:1px solid #1E293B; border-radius:20px; padding:25px 20px; display:inline-block; min-width:260px;">
                                <p style="margin:0 0 10px 0; color:#94A3B8; font-size:14px;">
                                    Your Reset Code
                                </p>
                                <div style="font-size:42px; font-weight:bold; letter-spacing:10px; color:#34D399;">
                                    {{ $otp }}
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 40px 40px 40px;">
                            <p style="color:#94A3B8; line-height:1.8; font-size:14px; margin:0;">
                                This code expires in
                                <span style="color:#FBBF24; font-weight:bold;">
                                    10 minutes
                                </span>.
                            </p>
                            <p style="color:#64748B; font-size:13px; margin-top:30px;">
                                If you did not request this, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
