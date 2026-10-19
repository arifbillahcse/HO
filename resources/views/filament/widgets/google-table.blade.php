<x-filament-widgets::widget>
    <x-filament::section :heading="$heading" :description="$description">
        @if ($error)
            <div style="color:#dc2626;font-size:14px;">{{ $error }}</div>
        @elseif ($rows === [])
            <div style="opacity:.6;font-size:14px;">{{ $empty }}</div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr>
                            @foreach ($columns as $column)
                                <th style="text-align:{{ $loop->first ? 'left' : 'right' }};font-weight:600;opacity:.65;padding:0 0 8px {{ $loop->first ? '0' : '12px' }};white-space:nowrap;">{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr style="border-top:1px solid rgba(128,128,128,.18);">
                                @foreach ($row as $cell)
                                    <td style="padding:7px 0 7px {{ $loop->first ? '0' : '12px' }};text-align:{{ $loop->first ? 'left' : 'right' }};{{ $loop->first ? 'word-break:break-word;' : 'white-space:nowrap;font-variant-numeric:tabular-nums;' }}">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
