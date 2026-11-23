@php
    $tabs = is_array($tabs ?? null) ? $tabs : [];
    $group = (string) ($group ?? 'page');
    $active = (string) ($active ?? array_key_first($tabs));
    $ariaLabel = (string) ($ariaLabel ?? __('common.page_sections'));
@endphp

<div class="mb-6 flex gap-2 overflow-x-auto overscroll-x-contain border-b border-hb-border pb-2" role="tablist" aria-label="{{ $ariaLabel }}">
    @foreach ($tabs as $key => $label)
        <button
            type="button"
            id="{{ $group }}-tab-{{ $key }}"
            role="tab"
            aria-controls="{{ $group }}-panel-{{ $key }}"
            aria-selected="{{ $active === $key ? 'true' : 'false' }}"
            tabindex="{{ $active === $key ? '0' : '-1' }}"
            data-page-tab="{{ $key }}"
            class="min-h-11 shrink-0 rounded-t-lg border-b-2 px-4 py-3 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tb-primary/40 {{ $active === $key ? 'border-tb-primary bg-hb-info-soft text-tb-primary-text' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
        >{{ $label }}</button>
    @endforeach
</div>
