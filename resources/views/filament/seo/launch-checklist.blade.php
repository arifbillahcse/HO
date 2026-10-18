@php
    $styles = [
        'ok' => ['#15803d', '#dcfce7', '✓'],
        'warn' => ['#b45309', '#fef3c7', '!'],
        'fail' => ['#b91c1c', '#fee2e2', '✕'],
        'info' => ['#1d4ed8', '#dbeafe', 'i'],
    ];
    $counts = collect($checks)->countBy('status');
@endphp
<div style="display:flex;flex-direction:column;gap:24px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        @foreach (['ok' => 'Ready', 'warn' => 'To improve', 'fail' => 'Must fix', 'info' => 'Notes'] as $status => $label)
            <div style="background:{{ $styles[$status][1] }};color:{{ $styles[$status][0] }};border-radius:10px;padding:8px 14px;font-size:14px;font-weight:600;">
                {{ $counts[$status] ?? 0 }} {{ $label }}
            </div>
        @endforeach
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
        @foreach ($checks as $check)
            @php([$color, $background, $icon] = $styles[$check['status']])
            <div style="display:flex;gap:14px;align-items:flex-start;padding:14px 18px;{{ $loop->last ? '' : 'border-bottom:1px solid #f3f4f6;' }}">
                <div style="flex:none;width:26px;height:26px;border-radius:50%;background:{{ $background }};color:{{ $color }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">{{ $icon }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;color:#111827;font-size:14px;">{{ $check['title'] }}</div>
                    <div style="color:#4b5563;font-size:13px;margin-top:2px;overflow-wrap:anywhere;">{{ $check['detail'] }}</div>
                </div>
                @isset($check['link'])
                    <a href="{{ $check['link'] }}" style="flex:none;font-size:13px;font-weight:600;color:#d97706;text-decoration:none;">Open →</a>
                @endisset
            </div>
        @endforeach
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 22px;">
        <div style="font-weight:700;color:#111827;font-size:15px;margin-bottom:10px;">On launch day</div>
        <ol style="margin:0;padding-left:20px;color:#374151;font-size:14px;line-height:1.6;list-style:decimal;">
            @foreach ($steps as $step)
                <li style="margin-bottom:6px;">{{ $step }}</li>
            @endforeach
        </ol>
    </div>
</div>
