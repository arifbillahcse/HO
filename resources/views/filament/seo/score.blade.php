@php
    $colors = ['success' => ['#15803d', '#dcfce7'], 'warning' => ['#b45309', '#fef3c7'], 'danger' => ['#b91c1c', '#fee2e2']];
    [$fg, $bg] = $colors[\App\Services\SeoAnalyzer::color($score)];
    $icons = ['ok' => ['✓', '#15803d', '#dcfce7'], 'warn' => ['!', '#b45309', '#fef3c7'], 'fail' => ['✕', '#b91c1c', '#fee2e2']];
@endphp
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 18px;">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:12px;">
        <div style="width:56px;height:56px;border-radius:50%;background:{{ $bg }};color:{{ $fg }};display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;">{{ $score }}</div>
        <div>
            <div style="font-weight:700;color:#111827;font-size:15px;">SEO score {{ $score }} / 100</div>
            <div style="color:#6b7280;font-size:13px;">Updates as you type. 80+ is good. The page text is read in English.</div>
        </div>
    </div>
    @foreach (collect($checks)->groupBy('group') as $group => $items)
        <div style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin:12px 0 6px;">{{ $group }}</div>
        @foreach ($items as $check)
            @php([$icon, $iconFg, $iconBg] = $icons[$check['status']])
            <div style="display:flex;gap:10px;align-items:flex-start;padding:5px 0;">
                <div style="flex:none;width:20px;height:20px;border-radius:50%;background:{{ $iconBg }};color:{{ $iconFg }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;margin-top:1px;">{{ $icon }}</div>
                <div style="font-size:13.5px;color:#111827;line-height:1.45;">
                    {{ $check['label'] }}
                    @if ($check['detail'] !== '')
                        <span style="color:#6b7280;"> · {{ $check['detail'] }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    @endforeach
</div>
