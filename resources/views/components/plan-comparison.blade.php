@props(['page'])
@php
    $catalog = app(\App\Services\ProductCatalog::class);
    $config = \App\Models\Product::COMPARISON[$page] ?? ['price' => 'monthly', 'popular' => 'none'];
    $rows = \App\Models\ComparisonRow::query()->onPage($page)->get();
    // Monthly price before the 30% yearly discount; matches billing.js (YEARLY_DISCOUNT).
    $money = fn (float $amount): string => '৳' . number_format(round($amount));
@endphp
                <div class="features-column">
                    <div class="feature-header">
                        <h3>&nbsp;</h3>
                    </div>
                    <div class="feature-list">
                        @foreach ($rows as $row)
                        <div class="feature-item-hosting">{!! nl2br(e($row->label), false) !!}</div>
                        @endforeach
                        <div class="feature-item-hosting feature-item-cta">&nbsp;</div>
                    </div>
                </div>
                @foreach ($catalog->forPage($page) as $product)
                @php
                    $yearly = (float) $catalog->price($product);
                    $badge = $product->is_popular && $config['popular'] === 'badge';
                @endphp
                <div class="plan-column{{ $badge ? ' popular' : '' }}">
                    <div class="plan-header">
                        @if ($badge)
                        <div class="cloud-popular-badge">{{ $product->badge_text ?: 'Most Popular' }}</div>
                        @endif
                        <h3 class="plan-title">{{ $product->name }}{{ $product->is_popular && $config['popular'] === 'star' ? ' ⭐' : '' }}</h3>
                    </div>
                    <div class="plan-features">
                        @foreach ($rows as $row)
                        @php($cell = ($product->comparison ?? [])[(string) $row->id] ?? ['type' => 'text', 'text' => ''])
                        <div class="plan-feature">@if (($cell['type'] ?? 'text') === 'yes')<i class="fas fa-check"></i>@elseif (($cell['type'] ?? 'text') === 'no')<i class="fas fa-times"></i>@else{{ $cell['text'] ?? '' }}@endif</div>
                        @endforeach
                        <div class="plan-feature plan-pricing-cell">
                            <div class="pricing-box">
                                @if ($config['price'] === 'monthly')
                                <div class="price-old">
                                    <span class="strikethrough">{{ $money($yearly / 8.4) }}/mo</span>
                                    <span class="save-badge">Save 30%</span>
                                </div>
                                <div class="price-current">
                                    <span class="price-amount">{{ $money($yearly / 12) }}/mo</span>
                                    <span class="price-period"> ({{ $money($yearly) }}/yr)</span>
                                </div>
                                @elseif ($config['price'] === 'yearly')
                                <div class="price-current">
                                    <span class="price-amount">{{ $money($yearly) }}/yr</span>
                                </div>
                                @else
                                <div class="price-current">
                                    <span class="price-amount">{{ $money($yearly) }}</span>
                                    <span class="price-period">/year</span>
                                </div>
                                @endif
                                <a href="{{ $catalog->url($product) }}" class="btn-order">{{ $product->button_text ?: 'Order Now' }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
