@props([
    'title',
    'count' => 0,
    'icon' => 'bi-info-circle',
    'href' => null,
    // % change vs previous period (positive/negative/null)
    'trend' => null,
    'trendLabel' => 'from last month',
])

@php
    $trendClass = is_null($trend) ? 'text-muted' : ($trend > 0 ? 'text-success' : ($trend < 0 ? 'text-danger' : 'text-secondary'));
    $trendIcon  = is_null($trend) ? 'bi-dash' : ($trend > 0 ? 'bi-arrow-up' : ($trend < 0 ? 'bi-arrow-down' : 'bi-arrow-right'));
@endphp

@php($cardOpen = $href ? '<a href="'.$href.'" class="text-decoration-none text-reset">' : '')
@php($cardClose = $href ? '</a>' : '')

{!! $cardOpen !!}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-1">{{ $title }}</h5>
                <p class="fs-4 fw-bold mb-0">{{ $count }}</p>
                <small class="{{ $trendClass }}">
                    <i class="bi {{ $trendIcon }}"></i>
                    @if (is_null($trend))
                        No change data
                    @else
                        {{ $trend > 0 ? '+' : '' }}{{ $trend }}% {{ $trendLabel }}
                    @endif
                </small>
            </div>
            <div class="fs-2 text-muted"><i class="bi {{ $icon }}"></i></div>
        </div>
    </div>
</div>
{!! $cardClose !!}
