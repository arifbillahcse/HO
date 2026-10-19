<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <div style="font-weight:600;font-size:15px;">See Google search and visitor numbers here</div>
                <div style="opacity:.7;font-size:14px;margin-top:2px;">Connect Google Search Console and Google Analytics once, and this dashboard shows clicks, positions, top searches and visitors.</div>
            </div>
            <x-filament::button tag="a" :href="$url" icon="heroicon-o-chart-bar">Connect Google</x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
