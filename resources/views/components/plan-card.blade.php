@props(['product', 'page'])
@php
    $catalog = app(\App\Services\ProductCatalog::class);
    $ui = \App\Models\Product::CARD_UI[$page] ?? \App\Models\Product::CARD_UI['home'];
    $price = $catalog->price($product);
    $shownPrice = number_format((float) $price, str_contains($price, '.') ? 2 : 0);
@endphp
<div class="cloud-card{{ $product->is_popular ? ' popular' : '' }}">
    @if ($product->is_popular)
        <div class="cloud-popular-badge">{{ $product->badge_text ?: 'Most Popular' }}</div>
    @endif
    <div class="cloud-icon">
        <img src="{{ $catalog->icon($product) }}" alt="{{ $product->icon_alt ?: $product->name }}">
    </div>
    <h3 class="cloud-plan-name">{{ $product->name }}</h3>
    @if (filled($product->description))
        <p class="cloud-plan-desc">{{ $product->description }}</p>
    @endif
    <div class="cloud-pricing" data-yearly-price="{{ $price }}">
        @if ($product->show_original_price)
            <div class="cloud-price-original">
                <span class="orig-currency" data-orig-currency>৳</span><span class="orig-amount"></span>
                <span class="cloud-save-badge" data-i18n="{{ $ui['save'][0] }}">{{ $ui['save'][1] }}</span>
            </div>
        @endif
        <div class="cloud-price-current">
            <span class="cloud-currency" data-currency-symbol>৳</span>
            <span class="cloud-amount">{{ $shownPrice }}</span>
            <span class="cloud-period" data-i18n="{{ $ui['period'][0] }}">{{ $ui['period'][1] }}</span>
        </div>
    </div>
    <ul class="cloud-features">
        @foreach ($product->features ?? [] as $feature)
            <li>
                <svg class="cloud-icon-check" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <rect width="16" height="16" rx="3" fill="#E3F2FD"/>
                    <path d="M5 8L7 10L11 6" stroke="#1976D2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>@if (filled($feature['value'] ?? null))<strong>{{ $feature['value'] }}</strong> @endif{{ $feature['text'] ?? '' }}@if (! empty($feature['is_new'])) <span class="feature-badge new">NEW</span>@endif</span>
            </li>
        @endforeach
    </ul>
    <a href="{{ $catalog->url($product) }}" class="cloud-order-btn">{{ $product->button_text ?: 'Order Now' }}</a>
</div>
