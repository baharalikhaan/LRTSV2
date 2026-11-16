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
        .message { margin-bottom: 14px; color: #444444; }
        .section-label { font-size: 10px; color: #8d1b3d; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; margin: 18px 0 6px; }
        .guidance { margin: 0 0 4px; padding-left: 18px; color: #555555; }
        .guidance li { margin-bottom: 6px; }
        .details { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .details td { padding: 7px 12px; border: 1px solid #e8e0d0; font-size: 12.5px; vertical-align: top; }
        .details td.k { background: #faf7f0; color: #6d6875; width: 34%; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
        .details td.v { color: #333333; font-weight: 500; }
        .due-ok { color: #28724e; font-weight: 700; }
        .due-soon { color: #8a6d1f; font-weight: 700; }
        .due-over { color: #a93434; font-weight: 700; }
        .pending { margin: 0; padding-left: 18px; color: #444444; }
        .pending li { margin-bottom: 4px; font-size: 12.5px; }
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
                <p class="eyebrow" style="color: #e8c9d2; font-size: 9.5px; letter-spacing: 0.14em; text-transform: uppercase; margin: 0 0 5px;">
                    Research Tracking System &middot; Qatar University
                </p>
                <h1>{{ $heading }}</h1>
                <p>Office of Research and Graduate Studies</p>
            </div>
            <div class="body">
                <div class="greeting">Dear {{ $recipientName }},</div>

                <div class="message">{{ $context['intro'] ?? $context['message'] }}</div>

                <div class="section-label">Project details</div>
                <table class="details">
                    @if(empty($details['pending']))
                        @if(!empty($details['old_project_id']))
                            <tr>
                                <td class="k">Project ID</td>
                                <td class="v">{{ $details['old_project_id'] }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="k">Project title</td>
                            <td class="v">{{ $projectTitle }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="k">Research call</td>
                        <td class="v">{{ $programTitle }}</td>
                    </tr>
                    @if(!empty($details['deadline']))
                        <tr>
                            <td class="k">{{ $details['deadline_label'] ?? 'Deadline' }}</td>
                            <td class="v">
                                {{ $details['deadline'] }}
                                @if($details['days_left'] === null)
                                @elseif($details['days_left'] < 0)
                                    <span class="due-over">&middot; overdue by {{ abs($details['days_left']) }} day{{ abs($details['days_left']) === 1 ? '' : 's' }}</span>
                                @elseif($details['days_left'] <= 14)
                                    <span class="due-soon">&middot; {{ $details['days_left'] }} day{{ $details['days_left'] === 1 ? '' : 's' }} left</span>
                                @else
                                    <span class="due-ok">&middot; {{ $details['days_left'] }} days left</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                </table>

                @if(!empty($details['pending']))
                    <div class="section-label">Projects awaiting action ({{ count($details['pending']) }})</div>
                    <ul class="pending">
                        @foreach($details['pending'] as $item)
                            <li>
                                <strong>{{ $item['id'] }}</strong>
                                @if(!empty($item['title'])) — {{ $item['title'] }} @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="message" style="margin-top:16px;">{{ $context['action'] ?? '' }}</div>

                <div style="text-align: center; margin-top: 20px;">
                    @if(!empty($details['project_url']))
                        <a href="{{ $details['project_url'] }}" class="cta">Open the project</a>
                    @else
                        <a href="{{ route('home') }}" class="cta">Go to RTS Dashboard</a>
                    @endif
                </div>

                @if(!empty($context['instructions']))
                    <div class="section-label">Instructions</div>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;margin-bottom:4px;">
                        <tr>
                            <td style="background:#f7f7f5;border:1px solid #e8e8e4;border-radius:4px;padding:10px 14px;color:#6d6875;">
                                <ol style="margin:0;padding-left:16px;">
                                    @foreach($context['instructions'] as $point)
                                        <li style="margin:0 0 5px;font-size:11px;line-height:1.5;">
                                            @if(!empty($point['label']))
                                                <strong style="color:#555555;">{{ $point['label'] }}:</strong>
                                            @endif
                                            {!! str_replace(
                                                'ros-submissions@qu.edu.qa',
                                                '<a href="mailto:ros-submissions@qu.edu.qa" style="color:#8d1b3d;text-decoration:underline;">ros-submissions@qu.edu.qa</a>',
                                                e($point['text'])
                                            ) !!}
                                            @if(!empty($point['templates']))
                                                <div style="margin-top:3px;">
                                                    @foreach($context['templates'] as $label => $url)
                                                        <a href="{{ $url }}" target="_blank" rel="noopener"
                                                           style="color:#8d1b3d;text-decoration:underline;">{{ $label }}</a>@if(!$loop->last)<span style="color:#b9b0a0;">&nbsp;&middot;&nbsp;</span>@endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            </td>
                        </tr>
                    </table>
                @endif

                @if(!empty($context['disclaimer']))
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;margin-top:10px;">
                        <tr>
                            <td style="background:#f7f7f5;border:1px solid #e8e8e4;border-radius:4px;padding:10px 14px;">
                                <div style="font-size:9px;font-weight:700;color:#8a8a84;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Disclaimer</div>
                                <div style="font-size:10.5px;color:#6d6875;line-height:1.5;">{{ $context['disclaimer'] }}</div>
                            </td>
                        </tr>
                    </table>
                @endif
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} Qatar University — Research Tracking System<br>
                This is an automated reminder. Please do not reply to this email.
            </div>
        </div>
    </div>
</body>
</html>
