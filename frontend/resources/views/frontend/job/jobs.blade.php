@extends('frontend.layouts.public')

@section('title', __('public.jobs.index_page_title'))

@section('content')
@php
    $items = \App\Support\Rows::from($jobs ?? []);
    $meta = $meta ?? null;
    $filterOptions = is_array($filterOptions ?? null) ? $filterOptions : [];
    $categories = \App\Support\Rows::from($filterOptions['categories'] ?? []);
    $totalCount = $totalCount ?? (is_numeric(data_get($meta, 'total')) ? (int) data_get($meta, 'total') : null);
    $sort = $sort ?? request('sort', 'newest');
    $isCandidate = (bool) ($isCandidate ?? false);
    $isAuthenticated = (bool) ($isAuthenticated ?? false);
    $selectedCategory = request('category_id', request('discipline'));
    $selectedCareerLevels = array_map('strval', (array) request('career_level', []));
    $hasActiveFilters = request()->hasAny([
        'location', 'country', 'city', 'area', 'work_mode', 'employment_type',
        'career_level', 'category_id', 'discipline', 'date_posted', 'salary_min', 'salary_max',
    ]);
    $employmentOptions = \App\Support\Rows::from($filterOptions['job_types'] ?? []);
    $workModeOptions = \App\Support\Rows::from($filterOptions['workplace_types'] ?? []);
    $employmentLabels = collect($employmentOptions)->mapWithKeys(fn ($option) => [(string) data_get($option, 'value') => data_get($option, 'label')])->all();
    $workModeLabels = collect($workModeOptions)->mapWithKeys(fn ($option) => [(string) data_get($option, 'value') => data_get($option, 'label')])->all();
    $careerLevelOptions = \App\Support\Rows::from($filterOptions['career_levels'] ?? []);
    $countryOptions = \App\Support\Rows::from($filterOptions['countries'] ?? []);
    $cityOptions = \App\Support\Rows::from($filterOptions['cities'] ?? []);
    $areaOptions = \App\Support\Rows::from($filterOptions['areas'] ?? []);
    $sortOptions = [
        'newest' => __('public.jobs.sort_newest'),
        'salary' => __('public.jobs.sort_salary'),
        'title' => __('public.jobs.sort_title'),
        'deadline' => __('public.jobs.sort_deadline'),
    ];

@endphp

@push('head')
<style>
    .tb-discover {
        --tb-blue: var(--color-primary-solid);
        --tb-blue-dark: var(--color-primary-solid-hover);
        --tb-text: var(--hb-text);
        --tb-muted: var(--hb-muted);
        --tb-border: var(--hb-border);
        --tb-panel: var(--hb-surface);
        --tb-page: var(--hb-page);
        background: var(--tb-page);
        color: var(--tb-text);
        min-height: 70vh;
    }
    .tb-discover *, .tb-discover *::before, .tb-discover *::after { box-sizing: border-box; }
    .tb-jobs-container { width: min(1200px, calc(100% - 40px)); margin-inline: auto; }
    .tb-search-hero { padding: 28px 0 24px; background: var(--color-primary-soft); border-bottom: 1px solid var(--hb-border); }
    .tb-search-title { margin: 0 0 17px; color: var(--hb-navy); font-size: clamp(22px, 3vw, 29px); font-weight: 700; letter-spacing: -.5px; }
    .tb-search-bar { display: flex; max-width: 860px; min-height: 54px; overflow: hidden; border: 1px solid var(--hb-border); border-radius: 6px; background: var(--hb-surface); box-shadow: 0 2px 6px rgb(24 43 70 / 7%); }
    .tb-search-input-wrap { display: flex; flex: 1; align-items: center; gap: 12px; min-width: 0; padding: 0 16px; }
    .tb-search-icon { width: 20px; height: 20px; flex: 0 0 20px; color: var(--hb-muted); }
    .tb-search-input { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; color: var(--tb-text); font-size: 15px; }
    .tb-search-input::placeholder { color: var(--hb-muted); opacity: 1; }
    .tb-search-button { min-width: 132px; padding: 0 25px; border: 0; background: var(--tb-blue); color: var(--hb-surface); cursor: pointer; font-size: 14px; font-weight: 700; transition: background .18s ease; }
    .tb-search-button:hover { background: var(--tb-blue-dark); }
    .tb-search-button:focus-visible, .tb-filter-button:focus-visible, .tb-filter-section summary:focus-visible { outline: 3px solid var(--color-primary); outline-offset: -3px; }

    .tb-jobs-layout { display: grid; grid-template-columns: 276px minmax(0, 1fr); align-items: start; gap: 20px; padding-block: 22px 40px; }
    .tb-filter-panel { position: sticky; top: 96px; overflow: hidden; border: 1px solid var(--tb-border); border-radius: 7px; background: var(--tb-panel); box-shadow: 0 1px 3px rgb(23 39 61 / 4%); }
    .tb-filter-heading { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 17px 17px 15px; border-bottom: 1px solid var(--tb-border); }
    .tb-filter-heading h2 { margin: 0; font-size: 16px; font-weight: 700; }
    .tb-filter-count { display: block; margin-top: 4px; color: var(--tb-muted); font-size: 12px; }
    .tb-clear-link { flex: 0 0 auto; color: var(--tb-blue); font-size: 12px; font-weight: 600; text-decoration: none; }
    .tb-clear-link:hover { text-decoration: underline; }
    .tb-filter-content { padding-inline: 16px; }
    .tb-filter-section { border-bottom: 1px solid var(--tb-border); }
    .tb-filter-section:last-of-type { border-bottom: 0; }
    .tb-filter-section summary { position: relative; display: flex; min-height: 48px; align-items: center; justify-content: space-between; gap: 12px; cursor: pointer; list-style: none; font-size: 13px; font-weight: 650; }
    .tb-filter-section summary::-webkit-details-marker { display: none; }
    .tb-filter-section summary::after { width: 8px; height: 8px; flex: 0 0 8px; border-right: 1.5px solid var(--hb-muted); border-bottom: 1.5px solid var(--hb-muted); content: ""; transform: rotate(45deg) translateY(-2px); transition: transform .18s ease; }
    .tb-filter-section[open] summary::after { transform: rotate(225deg) translate(-2px, -1px); }
    .tb-filter-options { display: grid; grid-template-rows: 1fr; opacity: 1; padding: 0 0 14px; transition: grid-template-rows .2s ease, opacity .2s ease; }
    .tb-filter-section:not([open]) .tb-filter-options { grid-template-rows: 0fr; opacity: 0; padding-bottom: 0; }
    .tb-filter-options-inner { min-height: 0; overflow: hidden; }
    .tb-filter-option { display: flex; min-height: 31px; align-items: center; gap: 9px; color: var(--hb-text); cursor: pointer; font-size: 12px; }
    .tb-filter-option input { width: 15px; height: 15px; flex: 0 0 15px; margin: 0; accent-color: var(--tb-blue); }
    .tb-option-count { margin-inline-start: auto; color: var(--hb-muted); font-size: 11px; }
    .tb-filter-select { width: 100%; min-height: 37px; padding: 0 10px; border: 1px solid var(--hb-border); border-radius: 4px; background: var(--hb-surface); color: var(--hb-text); font: inherit; font-size: 12px; }
    .tb-filter-select:focus { border-color: var(--color-primary); outline: 2px solid var(--color-primary-soft); }
    .tb-filter-help { margin: 8px 0 0; color: var(--hb-muted); font-size: 11px; line-height: 1.45; }
    .tb-filter-submit { width: 100%; min-height: 41px; margin: 14px 0 16px; border: 0; border-radius: 4px; background: var(--tb-blue); color: var(--hb-surface); cursor: pointer; font-size: 13px; font-weight: 700; transition: background .18s ease; }
    .tb-filter-submit:hover { background: var(--tb-blue-dark); }
    .tb-feed { min-width: 0; }
    .tb-feed-heading { display: flex; min-height: 47px; align-items: flex-start; justify-content: space-between; gap: 15px; margin-bottom: 11px; }
    .tb-feed-heading h2 { margin: 0; color: var(--hb-text); font-size: 18px; font-weight: 700; }
    .tb-feed-heading p { margin: 4px 0 0; color: var(--tb-muted); font-size: 12px; }
    .tb-sort-wrap { display: flex; align-items: center; gap: 8px; color: var(--tb-muted); font-size: 12px; white-space: nowrap; }
    .tb-sort-select { min-height: 34px; padding: 0 26px 0 9px; border: 1px solid var(--tb-border); border-radius: 4px; background: var(--hb-surface); color: var(--tb-text); font: inherit; }
    .tb-job-list { display: grid; gap: 11px; }
    .tb-job-card { position: relative; display: grid; grid-template-columns: 48px minmax(0, 1fr); gap: 14px; padding: 17px 18px 15px; border: 1px solid var(--tb-border); border-radius: 6px; background: var(--hb-surface); box-shadow: 0 1px 2px rgb(23 39 61 / 3%); transition: border-color .16s ease, box-shadow .16s ease; }
    .tb-job-card:hover { border-color: var(--hb-border); box-shadow: 0 3px 11px rgb(23 39 61 / 7%); }
    .tb-job-card[hidden] { display: none !important; }
    .tb-company-mark { display: grid; width: 46px; height: 46px; place-items: center; border: 1px solid var(--hb-border); border-radius: 5px; background: var(--hb-page); color: var(--hb-muted); font-size: 17px; font-weight: 700; text-transform: uppercase; }
    .tb-job-body { min-width: 0; }
    .tb-job-topline { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
    .tb-job-title { margin: 0; font-size: 16px; font-weight: 700; line-height: 1.4; }
    .tb-job-title a { color: var(--tb-blue); text-decoration: none; }
    .tb-job-title a:hover { color: var(--tb-blue-dark); text-decoration: underline; }
    .tb-posted { flex: 0 0 auto; color: var(--hb-muted); font-size: 11px; line-height: 1.5; text-align: end; }
    .tb-company-line { margin: 5px 0 0; color: var(--hb-text); font-size: 12px; line-height: 1.5; }
    .tb-company-line .tb-company-name { color: var(--hb-text); font-weight: 600; }
    .tb-job-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .tb-job-tag { display: inline-flex; min-height: 23px; align-items: center; padding: 3px 9px; border: 1px solid var(--hb-border); border-radius: 3px; background: var(--hb-page); color: var(--hb-text); font-size: 10px; font-weight: 600; }
    .tb-job-summary { margin: 9px 0 0; color: var(--hb-muted); font-size: 11px; line-height: 1.5; }
    .tb-job-summary-separator { padding-inline: 4px; color: var(--hb-muted); }
    .tb-job-skills { margin-top: 7px; color: var(--hb-muted); font-size: 11px; line-height: 1.5; }
    .tb-job-bottom { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; margin-top: 11px; padding-top: 10px; border-top: 1px solid var(--hb-border); }
    .tb-job-salary { color: var(--hb-text); font-size: 11px; font-weight: 600; }
    .tb-job-actions { display: flex; flex-wrap: wrap; gap: 7px; }
    .tb-job-actions > a, .tb-job-actions > button { min-height: 30px !important; padding: 5px 10px !important; border-radius: 4px !important; font-size: 11px !important; }
    .tb-empty-results { padding: 38px 20px; border: 1px solid var(--tb-border); border-radius: 6px; background: var(--hb-surface); text-align: center; }
    .tb-empty-results h3 { margin: 0; color: var(--hb-text); font-size: 16px; }
    .tb-empty-results p { margin: 8px 0 0; color: var(--tb-muted); font-size: 13px; }
    .tb-filter-error { margin-bottom: 15px; }
    .tb-cta { margin: 12px 0 36px !important; border-radius: 7px !important; }
    .dark .tb-discover {
        --tb-blue: var(--color-primary);
        --tb-blue-dark: var(--color-primary);
        --tb-text: var(--hb-border);
        --tb-muted: #9bb3ae;
        --tb-border: #2a3836;
        --tb-panel: #151d1c;
        --tb-page: #0f1514;
        color-scheme: dark;
    }
    .dark .tb-search-hero { background: #141c1b; border-color: #2a3836; }
    .dark .tb-search-title, .dark .tb-feed-heading h2 { color: #eaf5f3; }
    .dark .tb-search-bar, .dark .tb-filter-select, .dark .tb-sort-select,
    .dark .tb-job-card, .dark .tb-empty-results { background: var(--tb-panel); border-color: var(--tb-border); color: var(--tb-text); }
    .dark .tb-filter-option, .dark .tb-company-line, .dark .tb-company-line .tb-company-name,
    .dark .tb-job-salary, .dark .tb-job-summary, .dark .tb-job-skills { color: #c3d4d0; }
    .dark .tb-company-mark, .dark .tb-job-tag { background: #1a2423; border-color: #2a3836; color: #c3d4d0; }
    .dark .tb-filter-section, .dark .tb-job-bottom { border-color: var(--tb-border); }
    .dark .tb-search-input { color: var(--tb-text); }
    .dark .tb-search-input::placeholder { color: #9bb3ae; }
    .dark .tb-job-title a { color: var(--color-primary); }
    .dark .tb-empty-results h3 { color: #eaf5f3; }
    @media (max-width: 900px) {
        .tb-jobs-container { width: min(100% - 32px, 760px); }
        .tb-jobs-layout { grid-template-columns: minmax(0, 1fr); gap: 14px; }
        .tb-filter-panel { position: static; }
        .tb-filter-panel > summary { display: flex; min-height: 52px; align-items: center; justify-content: space-between; padding: 0 16px; cursor: pointer; list-style: none; font-size: 14px; font-weight: 700; }
        .tb-filter-panel > summary::-webkit-details-marker { display: none; }
        .tb-filter-panel > summary::after { content: "＋"; color: var(--tb-blue); font-size: 20px; font-weight: 400; }
        .tb-filter-panel[open] > summary::after { content: "−"; }
        .tb-filter-panel .tb-filter-heading { min-height: 42px; padding: 8px 16px; }
        .tb-filter-panel .tb-filter-heading h2,
        .tb-filter-panel .tb-filter-heading .tb-filter-count { display: none; }
        .tb-filter-panel .tb-clear-link { margin-inline-start: auto; }
        .tb-filter-content { padding-inline: 16px; }
    }
    @media (min-width: 901px) {
        .tb-filter-panel > summary { display: none; }
        .tb-filter-panel:not([open]) .tb-filter-heading,
        .tb-filter-panel:not([open]) .tb-filter-content { display: block; }
    }
    @media (max-width: 560px) {
        .tb-jobs-container { width: calc(100% - 24px); }
        .tb-search-hero { padding-block: 21px; }
        .tb-search-title { margin-bottom: 13px; font-size: 22px; }
        .tb-search-bar { min-height: 49px; }
        .tb-search-input-wrap { gap: 8px; padding-inline: 11px; }
        .tb-search-input { font-size: 13px; }
        .tb-search-button { min-width: 88px; padding-inline: 13px; font-size: 12px; }
        .tb-jobs-layout { padding-top: 13px; }
        .tb-feed-heading { flex-wrap: wrap; }
        .tb-sort-wrap { width: 100%; justify-content: flex-end; }
        .tb-job-card { grid-template-columns: 38px minmax(0, 1fr); gap: 10px; padding: 14px 12px; }
        .tb-company-mark { width: 37px; height: 37px; font-size: 14px; }
        .tb-job-title { font-size: 14px; }
        .tb-posted { max-width: 92px; font-size: 10px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .tb-discover *, .tb-discover *::before, .tb-discover *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
    }
</style>
@endpush

<div class="tb-discover" data-discover-jobs>
    <section class="tb-search-hero" aria-label="{{ __('Job search') }}">
        <div class="tb-jobs-container">
            <h1 class="tb-search-title">{{ __('Find jobs') }}</h1>
            <span class="sr-only">{{ __('public.jobs.index_title') }}</span>
            <form method="GET" action="{{ route('frontend.jobs.index') }}" class="tb-search-bar" data-jobs-search>
                <label class="tb-search-input-wrap">
                    <svg class="tb-search-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="2"></circle>
                        <path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                    </svg>
                    <span class="sr-only">{{ __('Search jobs and companies') }}</span>
                    <input class="tb-search-input" type="search" name="q" value="{{ request('q') }}" placeholder="Search jobs, companies..." autocomplete="off">
                </label>
                <button class="tb-search-button" type="submit">{{ __('Search') }}</button>
            </form>
        </div>
    </section>

    <div class="tb-jobs-container">
        @if (session('success_key'))
            <div class="tb-filter-error rounded-lg border border-hb-success/30 bg-hb-success-soft px-5 py-4 text-sm text-hb-success" role="status">
                {{ __(session('success_key')) }}
            </div>
        @endif
        @if ($errors->any())
            <div class="tb-filter-error rounded-lg border border-hb-error/30 bg-hb-error-soft px-5 py-4 text-sm text-hb-error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        @if (! empty($loadError))
            <div class="tb-empty-results tb-filter-error" role="alert" data-jobs-load-error>
                <h2>{{ __('public.jobs.load_error_title') }}</h2>
                <p>{{ __('public.jobs.load_error_body') }}</p>
                @if ($loadError !== __('public.jobs.load_error_body'))
                    <p class="mt-2 text-sm text-hb-error">{{ $loadError }}</p>
                @endif
                <div class="mt-4"><x-button variant="light" :href="request()->fullUrl()">{{ __('public.jobs.retry') }}</x-button></div>
            </div>
        @else
            <div class="tb-jobs-layout">
                <details class="tb-filter-panel" open data-filter-panel>
                    <summary>
                        <span>{{ __('Filters') }} <span class="tb-filter-count" data-filter-count-mobile>0 filters selected</span></span>
                    </summary>
                    <form method="GET" action="{{ route('frontend.jobs.index') }}" data-jobs-filter-form>
                        <div class="tb-filter-heading">
                            <div>
                                <h2>{{ __('Filters') }}</h2>
                                <span class="tb-filter-count" data-filter-count>0 filters selected</span>
                            </div>
                            <a class="tb-clear-link" href="{{ route('frontend.jobs.index') }}">{{ __('public.jobs.clear_filters') }}</a>
                        </div>

                        <input type="hidden" name="q" value="{{ request('q') }}" data-filter-keyword>
                        <input type="hidden" name="location" value="{{ request('location') }}" data-filter-location>
                        <div class="tb-filter-content">
                            <details class="tb-filter-section" open>
                                <summary>{{ __('Workplace') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner">
                                    @foreach ($workModeOptions as $option)
                                        @php $value = (string) data_get($option, 'value'); @endphp
                                        <label class="tb-filter-option">
                                            <input type="checkbox" name="work_mode" value="{{ $value }}" @checked(in_array($value, array_map('strval', (array) request('work_mode', [])), true))>
                                            <span>{{ data_get($option, 'label') }}</span>
                                            <span class="tb-option-count">{{ data_get($option, 'count', 0) }}</span>
                                        </label>
                                    @endforeach
                                </div></div>
                            </details>

                            <details class="tb-filter-section">
                                <summary>{{ __('Country') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner">
                                    <label class="sr-only" for="tb-country">{{ __('Country') }}</label>
                                    <input class="tb-filter-select" id="tb-country" name="country" type="text" list="tb-country-options" value="{{ request('country') }}" placeholder="{{ __('Any country') }}" maxlength="100" autocomplete="off" data-location-part="country">
                                    <datalist id="tb-country-options">
                                        @foreach ($countryOptions as $country)
                                            <option value="{{ data_get($country, 'name') }}"></option>
                                        @endforeach
                                    </datalist>
                                </div></div>
                            </details>

                            <details class="tb-filter-section">
                                <summary>{{ __('City') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner">
                                    <label class="sr-only" for="tb-city">{{ __('City') }}</label>
                                    <input class="tb-filter-select" id="tb-city" name="city" type="text" list="tb-city-options" value="{{ request('city') }}" placeholder="{{ __('Any city') }}" maxlength="100" autocomplete="off" data-location-part="city">
                                    <datalist id="tb-city-options">
                                        @foreach ($cityOptions as $city)
                                            <option value="{{ data_get($city, 'name') }}"></option>
                                        @endforeach
                                    </datalist>
                                </div></div>
                            </details>

                            <details class="tb-filter-section">
                                <summary>{{ __('Area') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner">
                                    <label class="sr-only" for="tb-area">{{ __('Area') }}</label>
                                    <input class="tb-filter-select" id="tb-area" name="area" type="text" list="tb-area-options" value="{{ request('area') }}" placeholder="{{ __('Any area') }}" maxlength="100" autocomplete="off" data-location-part="area">
                                    <datalist id="tb-area-options">
                                        @foreach ($areaOptions as $area)
                                            <option value="{{ data_get($area, 'name') }}"></option>
                                        @endforeach
                                    </datalist>
                                    <p class="tb-filter-help">{{ __('public.jobs.location_custom_value_hint') }}</p>
                                </div></div>
                            </details>

                            <details class="tb-filter-section">
                                <summary>{{ __('Career Level') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner">
                                    @foreach ($careerLevelOptions as $option)
                                        @php $value = (string) data_get($option, 'value'); @endphp
                                        <label class="tb-filter-option">
                                            <input type="checkbox" name="career_level" value="{{ $value }}" @checked(in_array($value, $selectedCareerLevels, true))>
                                            <span>{{ data_get($option, 'label') }}</span>
                                            <span class="tb-option-count">{{ data_get($option, 'count', 0) }}</span>
                                        </label>
                                    @endforeach
                                </div></div>
                            </details>

                            <details class="tb-filter-section">
                                <summary>{{ __('Job Category') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner">
                                    @foreach ($categories as $category)
                                        <label class="tb-filter-option">
                                            <input type="radio" name="discipline" value="{{ data_get($category, 'id') }}" @checked((string) $selectedCategory === (string) data_get($category, 'id'))>
                                            <span>{{ data_get($category, 'name') }}</span>
                                            <span class="tb-option-count">{{ data_get($category, 'count', 0) }}</span>
                                        </label>
                                    @endforeach
                                    @if (count($categories) === 0)
                                        <p class="tb-filter-help">{{ __('Job categories will appear when available.') }}</p>
                                    @endif
                                </div></div>
                            </details>

                            <details class="tb-filter-section">
                                <summary>{{ __('Job Type') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner">
                                    <label class="sr-only" for="tb-employment">{{ __('Job Type') }}</label>
                                    <select class="tb-filter-select" id="tb-employment" name="employment_type">
                                        <option value="">{{ __('Any employment type') }}</option>
                                        @foreach ($employmentOptions as $option)
                                            <option value="{{ data_get($option, 'value') }}" @selected(request('employment_type') === (string) data_get($option, 'value'))>{{ data_get($option, 'label') }} ({{ data_get($option, 'count', 0) }})</option>
                                        @endforeach
                                    </select>
                                </div></div>
                            </details>

                            <details class="tb-filter-section">
                                <summary>{{ __('Date Posted') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner">
                                    @foreach (['anytime' => __('Any time'), 'today' => __('Today'), '3days' => __('Last 3 days'), '7days' => __('Last 7 days'), '30days' => __('Last 30 days')] as $value => $label)
                                        <label class="tb-filter-option">
                                            <input type="radio" name="date_posted" value="{{ $value }}" @checked(request('date_posted', 'anytime') === $value)>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div></div>
                            </details>

                            <details class="tb-filter-section">
                                <summary>{{ __('Salary') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner grid gap-2">
                                    <label class="text-xs text-hb-muted" for="tb-salary-min">{{ __('Minimum salary') }}</label>
                                    <input class="tb-filter-select" id="tb-salary-min" name="salary_min" type="number" min="0" step="1000" value="{{ request('salary_min') }}">
                                    <label class="text-xs text-hb-muted" for="tb-salary-max">{{ __('Maximum salary') }}</label>
                                    <input class="tb-filter-select" id="tb-salary-max" name="salary_max" type="number" min="0" step="1000" value="{{ request('salary_max') }}">
                                </div></div>
                            </details>

                            <details class="tb-filter-section">
                                <summary>{{ __('Sort by') }}</summary>
                                <div class="tb-filter-options"><div class="tb-filter-options-inner">
                                    <label class="sr-only" for="tb-sort">{{ __('Sort by') }}</label>
                                    <select class="tb-filter-select" id="tb-sort" name="sort">
                                        @foreach ($sortOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div></div>
                            </details>

                            <button class="tb-filter-submit" type="submit">{{ __('Show jobs') }}</button>
                        </div>
                    </form>
                </details>

                <section class="tb-feed" aria-label="{{ __('Job results') }}">
                    <div class="tb-feed-heading">
                        <div>
                            <h2>{{ __('Open roles') }}</h2>
                            @if ($totalCount !== null)
                                <p data-jobs-match-count data-server-count="{{ $totalCount }}">{{ __('public.jobs.match_count', ['count' => $totalCount]) }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="tb-job-list" data-job-list>
                        @forelse ($items as $row)
                            @php
                                $jobId = (int) data_get($row, 'id');
                                $hiringModel = data_get($row, 'hiring_model');
                                $salaryMin = data_get($row, 'salary.min', data_get($row, 'salary_min'));
                                $salaryMax = data_get($row, 'salary.max', data_get($row, 'salary_max'));
                                $currency = data_get($row, 'salary.currency', data_get($row, 'currency'));
                                $saved = (bool) data_get($row, 'saved_by_me', false);
                                $employment = str_replace('-', '_', strtolower((string) data_get($row, 'employment_type', '')));
                                $workMode = str_replace('-', '_', strtolower((string) data_get($row, 'work_mode', '')));
                                $experience = data_get($row, 'experience_years');
                                $postedAt = data_get($row, 'posted_at', data_get($row, 'published_at'));
                                $postedDate = $postedAt ? \Illuminate\Support\Carbon::parse($postedAt) : null;
                                $companyName = data_get($row, 'company.name', data_get($row, 'company_name', 'Confidential'));
                                $jobLocation = data_get($row, 'location', '');
                                $categoryName = data_get($row, 'category.name', '');
                                $skillNames = collect(data_get($row, 'skills', []))
                                    ->map(fn ($skill) => is_string($skill) ? $skill : data_get($skill, 'name'))
                                    ->filter()
                                    ->take(4)
                                    ->implode(', ');
                            @endphp
                            <article
                                class="tb-job-card"
                                data-job-card="{{ $jobId }}"
                                data-experience="{{ is_numeric($experience) ? $experience : '' }}"
                                data-posted="{{ $postedDate?->toIso8601String() ?? '' }}"
                                data-work-mode="{{ $workMode }}"
                            >
                                <div class="tb-company-mark" aria-hidden="true">{{ strtoupper(substr((string) $companyName, 0, 1)) }}</div>
                                <div class="tb-job-body">
                                    <div class="tb-job-topline">
                                        <h3 class="tb-job-title">
                                            <a href="{{ route('frontend.jobs.show', ['jobId' => $jobId]) }}">{{ data_get($row, 'title') }}</a>
                                        </h3>
                                        <time class="tb-posted" @if ($postedDate) datetime="{{ $postedDate->toIso8601String() }}" @endif>
                                            {{ $postedDate?->diffForHumans() ?? __('Recently posted') }}
                                        </time>
                                    </div>
                                    <p class="tb-company-line">
                                        <span class="tb-company-name">{{ $companyName }}</span>
                                        @if ($jobLocation)
                                            <span aria-hidden="true"> · </span>{{ $jobLocation }}
                                        @endif
                                    </p>

                                    <div class="tb-job-tags">
                                        @if ($employment)
                                            <span class="tb-job-tag">{{ $employmentLabels[$employment] ?? str_replace('_', ' ', ucfirst($employment)) }}</span>
                                        @endif
                                        @if ($workMode)
                                            <span class="tb-job-tag">{{ $workModeLabels[$workMode] ?? str_replace('_', ' ', ucfirst($workMode)) }}</span>
                                        @endif
                                        @if ($hiringModel === 'direct')
                                            <span class="tb-job-tag">{{ __('common.sources.direct_hire') }}</span>
                                        @elseif ($hiringModel === 'agency')
                                            <span class="tb-job-tag">{{ __('common.sources.agency_managed') }}</span>
                                        @endif
                                    </div>

                                    <p class="tb-job-summary">
                                        @if ($categoryName)
                                            {{ $categoryName }}
                                        @else
                                            {{ __('All disciplines') }}
                                        @endif
                                        <span class="tb-job-summary-separator">·</span>
                                        @if (is_numeric($experience))
                                            {{ $experience }}+ {{ __('Yrs of Exp') }}
                                        @else
                                            {{ __('Experience not specified') }}
                                        @endif
                                        @if ($jobLocation)
                                            <span class="tb-job-summary-separator">·</span>{{ $jobLocation }}
                                        @endif
                                    </p>
                                    @if ($skillNames !== '')
                                        <p class="tb-job-skills">{{ __('Skills') }}: {{ $skillNames }}</p>
                                    @endif

                                    <div class="tb-job-bottom">
                                        <span class="tb-job-salary">
                                            @if ($salaryMin !== null || $salaryMax !== null)
                                                {{ \App\Support\Rows::money($salaryMin ?? $salaryMax, $currency) }}
                                                @if ($salaryMin !== null && $salaryMax !== null && (string) $salaryMin !== (string) $salaryMax)
                                                    – {{ \App\Support\Rows::money($salaryMax, $currency) }}
                                                @endif
                                                <span class="text-hb-muted"> · {{ __('public.jobs.card_salary_note') }}</span>
                                            @endif
                                        </span>
                                        <div class="tb-job-actions">
                                            <x-button variant="light" size="sm" :href="route('frontend.jobs.show', ['jobId' => $jobId])">{{ __('public.jobs.card_view_role') }}</x-button>
                                            @if ($isCandidate)
                                                @if ($saved)
                                                    <form method="POST" action="{{ route('frontend.jobs.unsave', ['jobId' => $jobId]) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                                                        <x-button type="submit" size="sm" variant="ghost">{{ __('public.jobs.unsave_job') }}</x-button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('frontend.jobs.save', ['jobId' => $jobId]) }}" class="inline" data-save-job-form onsubmit="if(this.dataset.submitted==='1'){return false;} this.dataset.submitted='1';">
                                                        @csrf
                                                        <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                                                        <x-button type="submit" size="sm" variant="primary">{{ __('public.jobs.save_job') }}</x-button>
                                                    </form>
                                                @endif
                                            @elseif (! $isAuthenticated)
                                                <x-button variant="ghost" size="sm" :href="route('frontend.account.login', ['redirect' => request()->fullUrl()])" :title="__('public.jobs.save_login_hint')">{{ __('public.jobs.save_login') }}</x-button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="tb-empty-results">
                                <h3>{{ __('public.jobs.empty_title') }}</h3>
                                <p>{{ __('public.jobs.empty_body') }}</p>
                                <a class="tb-clear-link inline-block mt-4" href="{{ route('frontend.jobs.index') }}">{{ __('public.jobs.clear_filters') }}</a>
                            </div>
                        @endforelse
                    </div>

                    @include('frontend.partials.pagination', [
                        'meta' => $meta,
                        'ariaLabel' => __('public.jobs.pagination_aria'),
                        'showStatus' => true,
                        'prevLabel' => __('public.jobs.pagination_prev'),
                        'nextLabel' => __('public.jobs.pagination_next'),
                    ])
                </section>
            </div>
        @endif

        <aside class="tb-cta overflow-hidden border border-hb-border bg-gradient-to-br from-tb-primary/10 via-hb-surface to-hb-page p-6 shadow-hb-sm sm:p-8" data-jobs-cta-banner>
            <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <h2 class="text-xl font-semibold text-hb-navy">{{ __('public.jobs.alert_title') }}</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-hb-muted">{{ __('public.jobs.alert_body') }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-button :href="route('frontend.account.register')">{{ __('public.jobs.create_account') }}</x-button>
                    @if (! $isAuthenticated)
                        <x-button variant="light" :href="route('frontend.account.login')">{{ __('public.jobs.apply_sign_in') }}</x-button>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const root = document.querySelector('[data-discover-jobs]');
    if (!root) return;

    const searchForm = root.querySelector('[data-jobs-search]') || document.querySelector('[data-jobs-search]');
    const filterForm = root.querySelector('[data-jobs-filter-form]');
    if (!filterForm) return;

    const searchInput = searchForm?.querySelector('input[name="q"]');
    const keywordInput = filterForm.querySelector('[data-filter-keyword]');
    const locationInput = filterForm.querySelector('[data-filter-location]');
    const locationParts = [...filterForm.querySelectorAll('[data-location-part]')];
    const locationInputs = Object.fromEntries(locationParts.map((input) => [input.dataset.locationPart, input]));
    const filterCountLabels = root.querySelectorAll('[data-filter-count], [data-filter-count-mobile]');
    let hadLocationSelection = locationParts.some((input) => input.value !== '');
    let previousCountry = locationInputs.country?.value || '';

    function updateLocation() {
        const selected = ['area', 'city', 'country']
            .map((part) => locationParts.find((input) => input.dataset.locationPart === part)?.value || '')
            .filter(Boolean);
        if (selected.length) {
            locationInput.value = selected[0];
            hadLocationSelection = true;
        } else if (hadLocationSelection) {
            locationInput.value = '';
            hadLocationSelection = false;
        }
    }

    function selectedFilterCount() {
        const countable = ['country', 'city', 'area', 'career_level', 'category_id', 'discipline', 'date_posted'];
        const singleCount = countable.filter((name) => {
            const selected = filterForm.querySelector(`[name="${name}"]:checked, select[name="${name}"], input[type="text"][name="${name}"]`);
            return selected && selected.value !== '' && !['anytime'].includes(selected.value);
        }).length;
        return singleCount
            + filterForm.querySelectorAll('input[name="work_mode"]:checked').length
            + (filterForm.querySelector('[name="employment_type"]')?.value ? 1 : 0)
            + [...filterForm.querySelectorAll('[name="salary_min"], [name="salary_max"]')].filter((input) => input.value !== '').length;
    }

    function updateFilterCount() {
        const count = selectedFilterCount();
        const label = `${count} ${count === 1 ? 'filter' : 'filters'} selected`;
        filterCountLabels.forEach((element) => { element.textContent = label; });
    }

    function buildSearchUrl() {
        updateLocation();
        keywordInput.value = searchInput?.value.trim() || '';
        const params = new URLSearchParams();
        new FormData(filterForm).forEach((value, key) => {
            if (String(value).trim() !== '') params.append(['work_mode', 'career_level'].includes(key) ? `${key}[]` : key, value);
        });
        params.delete('page');
        const query = params.toString();
        window.location.assign(`${filterForm.action}${query ? `?${query}` : ''}`);
    }

    searchForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        buildSearchUrl();
    });
    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();
        buildSearchUrl();
    });
    searchInput?.addEventListener('input', () => { keywordInput.value = searchInput.value; });
    locationInputs.country?.addEventListener('input', () => {
        if (locationInputs.country.value !== previousCountry) {
            locationInputs.city.value = '';
            locationInputs.area.value = '';
            previousCountry = locationInputs.country.value;
        }
        updateLocation();
        updateFilterCount();
    });
    locationInputs.city?.addEventListener('input', () => {
        updateLocation();
        updateFilterCount();
    });
    locationInputs.area?.addEventListener('input', () => {
        updateLocation();
        updateFilterCount();
    });
    filterForm.addEventListener('change', () => {
        updateLocation();
        updateFilterCount();
    });

    updateLocation();
    updateFilterCount();
})();
</script>
@endpush
@endsection
