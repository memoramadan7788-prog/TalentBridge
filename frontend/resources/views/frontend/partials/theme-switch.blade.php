@php
    $themeOptions = [
        'light' => __('common.theme_light'),
        'dark' => __('common.theme_dark'),
        'system' => __('common.theme_system'),
    ];
@endphp

<div
    class="inline-flex min-h-10 shrink-0 items-center gap-0.5 rounded-lg border border-hb-border bg-hb-surface p-px text-xs font-semibold"
    role="group"
    aria-label="{{ __('common.theme_switch') }}"
    data-theme-switch
>
    @foreach ($themeOptions as $value => $label)
        <button
            type="button"
            class="inline-flex h-10 min-w-10 items-center justify-center rounded-md text-hb-muted hover:text-hb-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tb-primary/40"
            data-theme-option="{{ $value }}"
            aria-pressed="false"
            title="{{ $label }}"
        >
            @if ($value === 'light')
                <span class="sr-only">{{ $label }}</span>
                <span aria-hidden="true">☀</span>
            @elseif ($value === 'dark')
                <span class="sr-only">{{ $label }}</span>
                <span aria-hidden="true">☾</span>
            @else
                <span class="sr-only">{{ $label }}</span>
                <span aria-hidden="true">◐</span>
            @endif
        </button>
    @endforeach
</div>
