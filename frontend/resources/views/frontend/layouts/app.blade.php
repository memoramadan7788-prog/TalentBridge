@php
    use App\Support\Frontend\WorkspaceNavigator;

    $locale = str_replace('_', '-', app()->getLocale());
    $isRtl = in_array(app()->getLocale(), ['ar'], true);

    // Shell from authenticated ApiSession identity (account_type + roles) — not URL/query.
    // Link visibility is not authorization; route middleware remains the gate.
    $uiRole = WorkspaceNavigator::resolveShell();
    $sidebarPartial = WorkspaceNavigator::sidebarPartial();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Workspace') | TalentBridge</title>

    @include('frontend.partials.theme-boot')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('frontend.partials.js-i18n')
    @stack('head')
</head>
<body class="min-h-screen bg-hb-page text-hb-text antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-tb-primary-solid focus:px-3 focus:py-2 focus:text-white">
        {{ __('common.skip_to_content') }}
    </a>

    {{-- Mobile sidebar drawer (closed: inert + aria-hidden via JS; pointer-events none in CSS) --}}
    <div
        class="fixed inset-0 z-40 hidden bg-hb-navy/40 lg:hidden"
        data-mobile-sidebar-backdrop
        hidden
        aria-hidden="true"
    ></div>
    <aside
        id="mobile-sidebar-drawer"
        class="fixed inset-y-0 start-0 z-50 flex h-dvh w-64 -translate-x-full flex-col border-e border-hb-border bg-hb-sidebar text-hb-text shadow-hb-lg transition-transform duration-200 rtl:translate-x-full lg:hidden"
        data-mobile-sidebar
        aria-label="{{ __('navigation.workspace') }}"
        aria-hidden="true"
        inert
    >
        <div class="flex h-[72px] shrink-0 items-center justify-between border-b border-hb-border px-5">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('frontend.home') }}" class="flex items-center gap-2.5">@include('frontend.partials.brand-logo', ['onDark' => false])</a>
            </div>
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-hb-nav-muted hover:bg-hb-tint hover:text-hb-navy"
                data-mobile-sidebar-close
                aria-label="{{ __('common.close_navigation') }}"
            >
                &times;
            </button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain py-4">
            @include($sidebarPartial)
        </div>
    </aside>

    <div class="flex min-h-screen items-start">
        {{-- Workspace navigation · 256px light rail --}}
        <aside
            class="sticky top-0 hidden h-screen w-64 shrink-0 flex-col border-e border-hb-border bg-hb-sidebar text-hb-text lg:flex"
            aria-label="{{ __('navigation.workspace') }}"
            data-ui-role="{{ $uiRole }}"
        >
            <div class="flex h-[72px] shrink-0 items-center gap-2.5 border-b border-hb-border px-5">
                <a href="{{ route('frontend.home') }}" class="flex items-center gap-2.5">@include('frontend.partials.brand-logo', ['onDark' => false])</a>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain py-4">
                @include($sidebarPartial)
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            @include('frontend.partials.topbar')

            <main id="main-content" class="min-w-0 overflow-x-clip" tabindex="-1">
                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                    @hasSection('page_header')
                        <x-page-header>
                            @hasSection('breadcrumb')
                                <x-slot:breadcrumb>
                                    @include('frontend.partials.breadcrumb', [
                                        'breadcrumb' => trim($__env->yieldContent('breadcrumb')),
                                        'uiRole' => $uiRole,
                                    ])
                                </x-slot:breadcrumb>
                            @endif

                            <div class="min-w-0">
                                @yield('page_header')
                            </div>
                            @hasSection('page_actions')
                                <x-slot:actions>
                                    @yield('page_actions')
                                </x-slot:actions>
                            @endif
                        </x-page-header>
                    @endif

                    @if (session('success_key'))
                        <x-alert variant="success" class="mb-4" role="status">{{ __(session('success_key'), session('success_replace', [])) }}</x-alert>
                    @elseif (session('success'))
                        <x-alert variant="success" class="mb-4" role="status">{{ session('success') }}</x-alert>
                    @endif
                    @if ($errors->has('error'))
                        <x-alert variant="error" class="mb-4" role="alert">{{ $errors->first('error') }}</x-alert>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
