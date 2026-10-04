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
        .message { margin-bottom: 16px; color: #555555; }
        .project-box { background: #faf7f0; border: 1px solid #e8e0d0; border-radius: 4px; padding: 12px 16px; margin-bottom: 12px; }
        .project-box .label { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px; }
        .project-box .value { font-size: 13px; color: #333; font-weight: 500; }
        .cta { display: inline-block; background: #8d1b3d; color: #ffffff !important; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-top: 8px; }
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
                <div class="message">{{ $context['message'] }}</div>

                <div class="project-box">
                    <div class="label">Research Call</div>
                    <div class="value">{{ $programTitle }}</div>
                </div>
                <div class="project-box">
                    <div class="label">Project</div>
                    <div class="value">{{ $projectTitle }}</div>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('home') }}" class="cta">Go to RTS Dashboard</a>
                </div>
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} Qatar University — Research Tracking System<br>
                This is an automated reminder. Please do not reply to this email.
            </div>
        </div>
    </div>
</body>
</html>
