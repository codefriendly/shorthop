@php
    $data = $getSparklineData();
    $polyline = $data->polyline();
@endphp

<div {{ $getExtraAttributeBag()->class(['flex items-center gap-3']) }}>
    <span class="text-sm font-medium text-gray-950 dark:text-white">
        {{ number_format($data->totalVisits) }}
    </span>

    @if ($data->hasSparkline())
        <svg
            viewBox="0 0 96 28"
            width="96"
            height="28"
            fill="none"
            preserveAspectRatio="none"
            role="img"
            aria-label="Visits over last 7 days"
            style="color: var(--primary-500);"
        >
            <polyline
                points="{{ $polyline }}"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
    @endif
</div>
