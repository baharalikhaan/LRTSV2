<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { margin: 0; padding: 0; background: #f4f4f4; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wrapper { background: #f4f4f4; padding: 24px 0; }
        .container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #8d1b3d; padding: 20px 24px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 16px; margin: 0 0 4px; font-weight: 600; }
        .header p { color: #e0c0c8; font-size: 11px; margin: 0; }
        .body { padding: 24px; color: #333333; font-size: 13px; line-height: 1.6; }
        .greeting { font-size: 14px; font-weight: 600; margin-bottom: 12px; }
        .message { color: #555555; }
        .section-label { font-size: 10px; color: #8d1b3d; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; margin: 18px 0 6px; }
        .guidance { margin: 0 0 4px; padding-left: 18px; color: #555555; }
        .guidance li { margin-bottom: 6px; }
        .action-list { margin: 0 0 4px; padding-left: 18px; color: #555555; }
        .action-list li { margin-bottom: 6px; }
        .details { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .details td { padding: 7px 12px; border: 1px solid #e8e0d0; font-size: 12.5px; vertical-align: top; }
        .details td.k { background: #faf7f0; color: #6d6875; width: 34%; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
        .details td.v { color: #333333; font-weight: 500; }
        .due-ok { color: #28724e; font-weight: 700; }
        .due-soon { color: #8a6d1f; font-weight: 700; }
        .due-over { color: #a93434; font-weight: 700; }
        .cta { display: inline-block; background: #8d1b3d; color: #ffffff !important; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-top: 8px; }
        .footer { background: #faf7f0; padding: 16px 24px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <p class="eyebrow" style="color: #e8c9d2; font-size: 9.5px; letter-spacing: 0.14em; text-transform: uppercase; margin: 0 0 5px;">
                    Research Tracking System &middot; Qatar University
                </p>
                <h1>{{ $heading ?: 'Notification' }}</h1>
                <p>Office of Research and Graduate Studies</p>
            </div>
            <div class="body">
                <div class="greeting">Dear {{ $recipientName }},</div>

                <div class="message">{!! nl2br(e($body)) !!}</div>

                @if(!empty($guidance))
                    <div class="section-label">What is required</div>
                    <ul class="guidance">
                        @foreach($guidance as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($project))
                    <div class="section-label">Project details</div>
                    <table class="details">
                        @if(!empty($project['old_project_id']))
                            <tr>
                                <td class="k">Project ID</td>
                                <td class="v">{{ $project['old_project_id'] }}</td>
                            </tr>
                        @endif
                        @if(!empty($project['title']))
                            <tr>
                                <td class="k">Project title</td>
                                <td class="v">{{ $project['title'] }}</td>
                            </tr>
                        @endif
                        @if(!empty($project['program_title']))
                            <tr>
                                <td class="k">Research call</td>
                                <td class="v">{{ $project['program_title'] }}</td>
                            </tr>
                        @endif
                        @if(!empty($project['grant_name']))
                            <tr>
                                <td class="k">Grant</td>
                                <td class="v">{{ $project['grant_name'] }}</td>
                            </tr>
                        @endif
                        @if(!empty($project['cycle_year']))
                            <tr>
                                <td class="k">Year</td>
                                <td class="v">{{ $project['cycle_year'] }}</td>
                            </tr>
                        @endif
                        @if(!empty($project['deadlines']))
                            <tr>
                                <td class="k">Deadlines</td>
                                <td class="v">
                                    @foreach($project['deadlines'] as $deadline)
                                        <div style="margin-bottom:3px;">
                                            {{ $deadline['label'] }}: <strong>{{ $deadline['date'] }}</strong>
                                            @if($deadline['days_left'] === null)
                                            @elseif($deadline['days_left'] < 0)
                                                <span class="due-over">&middot; passed</span>
                                            @elseif($deadline['days_left'] <= 14)
                                                <span class="due-soon">&middot; {{ $deadline['days_left'] }} day{{ $deadline['days_left'] === 1 ? '' : 's' }} left</span>
                                            @else
                                                <span class="due-ok">&middot; {{ $deadline['days_left'] }} days left</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    </table>
                @endif

                @if(!empty($actionPoints))
                    <div class="section-label">Action required</div>
                    <ol class="action-list">
                        @foreach($actionPoints as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ol>
                @endif

                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ $actionUrl ?: route('home') }}" class="cta">{{ $actionLabel ?: 'Go to RTS Dashboard' }}</a>
                </div>

                @if(!empty($supportEmail))
                    <div class="section-label">Support and queries</div>
                    <div style="font-size:11.5px;color:#555555;">
                        If you encounter any issues, have questions, or need clarification, please email
                        <a href="mailto:{{ $supportEmail }}" style="color:#8d1b3d;text-decoration:underline;">{{ $supportEmail }}</a>.
                        Our team will be happy to assist you.
                    </div>
                @endif

                @if(!empty($disclaimer))
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;margin-top:14px;">
                        <tr>
                            <td style="background:#f7f7f5;border:1px solid #e8e8e4;border-radius:4px;padding:10px 14px;">
                                <div style="font-size:9px;font-weight:700;color:#8a8a84;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Disclaimer</div>
                                <div style="font-size:10.5px;color:#6d6875;line-height:1.5;">{{ $disclaimer }}</div>
                            </td>
                        </tr>
                    </table>
                @endif
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} Qatar University &mdash; Research Tracking System<br>
                Sent by {{ $senderName }} via RTS
            </div>
        </div>
    </div>
</body>
</html>
