{{--
    Shared sidebar navigation renderer (light rail).
    $eyebrow, $workspaceLabel, $items[] with keys: label, route?, pending?, active?, icon?, section?, badge?
    `section` (string label) starts a new titled group when it changes between items.
--}}
@php
    $eyebrow = $eyebrow ?? __('navigation.workspace');
    $workspaceLabel = $workspaceLabel ?? __('navigation.candidate.workspace');
    $items = $items ?? [];
    $navListId = 'sidebar-nav-list-'.str_replace('.', '', uniqid('', true));
    $currentSection = null;
@endphp

<nav class="flex h-full flex-col px-3" aria-label="{{ $eyebrow }}">
    <div class="mx-1 border-b border-hb-border px-2 pb-3">
        <p class="text-xs font-semibold text-hb-muted">{{ $eyebrow }}</p>
        <button
            type="button"
            class="mt-2 flex w-full items-center justify-between gap-2 rounded-lg bg-hb-tint px-2.5 py-2 text-start text-sm font-medium text-hb-navy transition-colors hover:bg-tb-primary-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tb-primary/60"
            data-sidebar-nav-toggle
            aria-expanded="true"
            aria-controls="{{ $navListId }}"
            aria-label="{{ __('navigation.toggle_nav', ['workspace' => $workspaceLabel]) }}"
        >
            <span class="min-w-0 flex-1 truncate">{{ $workspaceLabel }}</span>
            <x-icon.chevron class="h-3.5 w-3.5 shrink-0 text-current" />
        </button>
    </div>

    <ul
        id="{{ $navListId }}"
        class="mt-3 flex-1 space-y-0.5 text-sm"
        data-sidebar-nav-list
    >
        @foreach ($items as $item)
            @php
                $label = $item['label'] ?? '';
                $routeName = $item['route'] ?? null;
                $pending = (bool) ($item['pending'] ?? false);
                $isActive = (bool) ($item['active'] ?? false);
                $badge = $item['badge'] ?? null;
                $section = $item['section'] ?? null;

                if (! $isActive && $routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
                    $isActive = request()->routeIs($routeName);
                }

                $icon = $item['icon'] ?? 'grid';
                $baseClass = 'group relative flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tb-primary/50';
                $activeClass = $isActive
                    ? 'bg-hb-sidebar-active font-semibold text-tb-primary-text'
                    : 'text-hb-nav-muted hover:bg-hb-tint hover:text-hb-navy';
                $iconClass = $isActive
                    ? 'text-tb-primary-text'
                    : 'text-hb-nav-muted group-hover:text-hb-navy';
            @endphp

            @if ($section && $section !== $currentSection)
                @php $currentSection = $section; @endphp
                <li class="{{ $loop->first ? '' : 'mt-4' }} px-3 pb-1 pt-1" role="presentation">
                    <p class="text-xs font-semibold text-hb-muted">{{ $section }}</p>
                </li>
            @endif

            <li>
                @if ($pending || ! $routeName || ! \Illuminate\Support\Facades\Route::has($routeName))
                    <span
                        class="{{ $baseClass }} cursor-not-allowed text-hb-muted opacity-70"
                        title="{{ __('navigation.pending_route') }}"
                        aria-disabled="true"
                    >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center" aria-hidden="true"><x-icon.nav :name="$icon" class="h-[18px] w-[18px]" /></span>
                        <span class="min-w-0 flex-1 truncate whitespace-nowrap">{{ $label }}</span>
                        <span class="shrink-0 rounded-full bg-hb-tint px-2 py-0.5 text-[11px] font-medium text-hb-muted">{{ __('common.soon') }}</span>
                    </span>
                @else
                    <a
                        href="{{ route($routeName, $item['params'] ?? []) }}"
                        class="{{ $baseClass }} {{ $activeClass }}"
                        @if ($isActive) aria-current="page" @endif
                    >
                        @if ($isActive)
                            <span class="absolute inset-y-2 start-0 w-1 rounded-e-full bg-tb-primary-solid" aria-hidden="true"></span>
                        @endif
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center transition-colors {{ $iconClass }}" aria-hidden="true"><x-icon.nav :name="$icon" class="h-[18px] w-[18px]" /></span>
                        <span class="min-w-0 flex-1 truncate whitespace-nowrap">{{ $label }}</span>
                        @if (filled($badge))
                            <span class="shrink-0 rounded-full bg-tb-primary-soft px-2 py-0.5 text-[11px] font-semibold text-tb-primary-text">{{ $badge }}</span>
                        @endif
                    </a>
                @endif
            </li>
        @endforeach
    </ul>

    {{-- Account footer: always reachable, mirrors the topbar menu --}}
    <div class="mt-4 border-t border-hb-border py-3">
        <form method="POST" action="{{ route('frontend.account.logout') }}">
            @csrf
            <button type="submit" class="group flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2 text-start text-sm font-medium text-hb-nav-muted transition-colors hover:bg-hb-tint hover:text-hb-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tb-primary/50">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center" aria-hidden="true">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                </span>
                <span class="min-w-0 flex-1 truncate">{{ __('common.sign_out') }}</span>
            </button>
        </form>
    </div>
</nav>
