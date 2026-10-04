<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { margin: 0; padding: 0; background: #f4f4f4; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wrapper { background: #f4f4f4; padding: 24px 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #8d1b3d; padding: 20px 24px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 16px; margin: 0 0 4px; font-weight: 600; }
        .header p { color: #e0c0c8; font-size: 11px; margin: 0; }
        .body { padding: 24px; color: #333333; font-size: 13px; line-height: 1.6; }
        .greeting { font-size: 14px; font-weight: 600; margin-bottom: 12px; }
        .message { color: #555555; white-space: pre-wrap; }
        .footer { background: #faf7f0; padding: 16px 24px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Research Tracking System</h1>
                <p>Qatar University — Office of Research and Graduate Studies</p>
            </div>
            <div class="body">
                <div class="greeting">Dear {{ $recipientName }},</div>
                <div class="message">{!! nl2br(e($body)) !!}</div>
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} Qatar University — Research Tracking System<br>
                Sent by {{ $senderName }} via RTS
            </div>
        </div>
    </div>
</body>
</html>
