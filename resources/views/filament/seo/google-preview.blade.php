@php
    $titleTooLong = mb_strlen($title) > 60;
    $shownTitle = $titleTooLong ? mb_substr($title, 0, 58) . '…' : $title;
    $shownDescription = mb_strlen($description) > 160 ? mb_substr($description, 0, 157) . '…' : $description;
    $address = preg_replace('#^https?://#', '', rtrim($url, '/'));
    $breadcrumb = str_replace('/', ' › ', $address);
@endphp
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 18px;font-family:Arial,Helvetica,sans-serif;max-width:640px;">
    <div style="font-size:12px;color:#5f6368;margin-bottom:6px;letter-spacing:.02em;">Google preview</div>
    <div style="display:flex;align-items:center;gap:10px;">
        @if ($favicon)
            <img src="{{ $favicon }}" alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover;background:#f1f3f4;">
        @else
            <div style="width:28px;height:28px;border-radius:50%;background:#f1f3f4;display:flex;align-items:center;justify-content:center;font-size:13px;color:#5f6368;">{{ mb_substr($brand, 0, 1) }}</div>
        @endif
        <div style="line-height:1.3;">
            <div style="font-size:14px;color:#202124;">{{ $brand }}</div>
            <div style="font-size:12px;color:#4d5156;">{{ $breadcrumb }}</div>
        </div>
    </div>
    <div style="font-size:20px;line-height:1.3;color:#1a0dab;margin:6px 0 4px;">{{ $shownTitle }}</div>
    @if ($description !== '')
        <div style="font-size:14px;line-height:1.58;color:#4d5156;">{{ $shownDescription }}</div>
    @else
        <div style="font-size:14px;line-height:1.58;color:#9aa0a6;font-style:italic;">No meta description: Google will pick a sentence from the page.</div>
    @endif
    @unless ($indexed)
        <div style="margin-top:10px;font-size:13px;color:#b91c1c;">This page is set to noindex, so it will not appear in Google.</div>
    @endunless
</div>
