@extends('frontend.layouts.app')

@section('title', __('Discover jobs'))

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-tb-primary-text">{{ __('Find your next opportunity') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Discover jobs') }}</h1>
    </div>
@endsection

@section('content')
@php
    $items = \App\Support\Rows::from($vacancies ?? []);
    $meta = $pagination ?? \App\Support\Rows::meta($vacancies ?? null);
    $skillRows = \App\Support\Rows::from($coreSkills ?? []);
    $skillNames = collect($skillRows)->map(fn ($skill) => is_string($skill) ? $skill : data_get($skill, 'name'))->filter(fn ($name) => is_string($name) && $name !== '')->values()->all();
    $queryParameters = request()->query->all();
    $preferences = data_get($profile ?? [], 'preferences', []);
    $filterKeys = ['q', 'location', 'country', 'city', 'area', 'country_id', 'city_id', 'area_id', 'work_mode', 'employment_type', 'career_level', 'category_id', 'salary_min', 'salary_max', 'date_posted', 'sort'];
    $hasFilterQuery = count(array_intersect($filterKeys, array_keys($queryParameters))) > 0;
    $searchQuery = (string) ($queryParameters['q'] ?? '');
    $selectedLocation = (string) request()->query('location', '');
    $filterFacets = is_array($filterFacets ?? null) ? $filterFacets : [];
    $workplaceOptions = $filterFacets['workplace_types'] ?? [
        ['value' => 'onsite', 'label' => __('On site')],
        ['value' => 'hybrid', 'label' => __('Hybrid')],
        ['value' => 'remote', 'label' => __('Remote')],
    ];
    $careerOptions = $filterFacets['career_levels'] ?? [];
    $categoryOptions = $filterFacets['categories'] ?? [];
    $jobTypeOptions = $filterFacets['job_types'] ?? [];
    $countryOptions = $filterFacets['countries'] ?? [];
    $cityOptions = $filterFacets['cities'] ?? [];
    $areaOptions = $filterFacets['areas'] ?? [];
    $selectedWorkModes = array_map('strval', (array) request()->query('work_mode', []));
    if (! $hasFilterQuery && filled(data_get($preferences, 'work_mode', data_get($profile ?? [], 'work_mode')))) {
        $selectedWorkModes = [(string) data_get($preferences, 'work_mode', data_get($profile ?? [], 'work_mode'))];
    }
    $selectedEmploymentTypes = array_map('strval', (array) request()->query('employment_type', []));
    $selectedCareerLevels = array_map('strval', (array) request()->query('career_level', []));
    $selectedCategory = (string) request()->query('category_id', '');
    $selectedDatePosted = (string) request()->query('date_posted', 'anytime');
    $workModeLabels = ['onsite' => __('On site'), 'hybrid' => __('Hybrid'), 'remote' => __('Remote')];
    $careerLabels = ['entry' => __('Entry Level'), 'junior' => __('Junior'), 'mid' => __('Mid-Level'), 'senior' => __('Senior'), 'lead' => __('Lead / Manager')];
    $employmentLabels = [
        'full_time' => __('common.employment.full_time'),
        'part_time' => __('common.employment.part_time'),
        'contract' => __('common.employment.contract'),
        'internship' => __('common.employment.internship'),
        'freelance' => __('Freelance'),
    ];
    $totalMatching = data_get($filterFacets, 'total_matching_jobs', data_get($meta, 'total'));
@endphp

<div class="grid items-start gap-5 xl:grid-cols-[17rem_minmax(0,1fr)]">
    <aside class="space-y-4 xl:sticky xl:top-5" aria-label="{{ __('Filters') }}">
        <details class="bg-hb-surface xl:contents" open data-candidate-filter-panel>
        <summary class="mb-3 cursor-pointer list-none rounded-2xl border border-hb-border bg-hb-surface px-4 py-4 font-bold text-hb-navy shadow-sm dark:text-white xl:hidden">{{ __('Filters') }}</summary>
        <form id="candidate-job-filters" method="GET" action="{{ route('frontend.candidate.vacancies.index') }}" class="rounded-2xl border border-hb-border bg-hb-surface shadow-sm" data-vacancy-search-form>
            <div class="flex items-center justify-between gap-3 border-b border-hb-border px-4 py-4">
                <div>
                    <h2 class="font-bold text-hb-navy dark:text-white">{{ __('Filters') }}</h2>
                    @if (is_numeric($totalMatching))
                        <p class="mt-1 text-xs text-hb-muted">{{ __('public.jobs.match_count', ['count' => (int) $totalMatching]) }}</p>
                    @endif
                </div>
                <a href="{{ route('frontend.candidate.vacancies.index') }}" class="text-xs font-semibold text-tb-primary-text hover:underline">{{ __('public.jobs.clear_filters') }}</a>
            </div>
            <div class="divide-y divide-hb-border px-4">
                <details class="py-3" open>
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('Location') }}</summary>
                    <input type="search" name="location" value="{{ $selectedLocation }}" maxlength="100" data-preference-location class="mt-3 w-full rounded-lg border border-hb-border px-3 py-2 text-sm focus:border-tb-primary focus:outline-none focus:ring-2 focus:ring-tb-primary/20" placeholder="{{ __('Any location') }}">
                    <p class="mt-2 text-xs text-hb-muted">{{ __('Search by country, city, or area.') }}</p>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('Approximate location is suggested from your public IP via IPWho; VPNs or mobile networks may affect accuracy.') }}</p>
                </details>
                <details class="py-3" open>
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('Workplace') }}</summary>
                    <div class="mt-3 space-y-2">
                        @foreach ($workplaceOptions as $option)
                            @php $value = (string) data_get($option, 'value'); @endphp
                            <label class="flex items-center gap-2 text-sm text-hb-text">
                                <input type="checkbox" name="work_mode[]" value="{{ $value }}" @checked(in_array($value, $selectedWorkModes, true)) class="rounded border-hb-border text-tb-primary-text focus:ring-tb-primary">
                                <span>{{ $workModeLabels[$value] ?? data_get($option, 'label', ucfirst($value)) }}</span>
                                <span class="ms-auto text-xs text-hb-muted">{{ data_get($option, 'count', '') }}</span>
                            </label>
                        @endforeach
                    </div>
                </details>
                <details class="py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('Country') }}</summary>
                    <select name="country_id" class="mt-3 w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm" data-location-select="country" data-submit-filter>
                        <option value="">{{ __('Any country') }}</option>
                        @foreach ($countryOptions as $option)
                            <option value="{{ data_get($option, 'id') }}" @selected((string) request('country_id') === (string) data_get($option, 'id'))>{{ data_get($option, 'name') }} ({{ data_get($option, 'count', 0) }})</option>
                        @endforeach
                    </select>
                </details>
                <details class="py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('City') }}</summary>
                    <select name="city_id" class="mt-3 w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm" data-location-select="city" data-submit-filter>
                        <option value="">{{ __('Any city') }}</option>
                        @foreach ($cityOptions as $option)
                            <option value="{{ data_get($option, 'id') }}" @selected((string) request('city_id') === (string) data_get($option, 'id'))>{{ data_get($option, 'name') }} ({{ data_get($option, 'count', 0) }})</option>
                        @endforeach
                    </select>
                </details>
                <details class="py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('Area') }}</summary>
                    <select name="area_id" class="mt-3 w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm" data-location-select="area" data-submit-filter>
                        <option value="">{{ __('Any area') }}</option>
                        @foreach ($areaOptions as $option)
                            <option value="{{ data_get($option, 'id') }}" @selected((string) request('area_id') === (string) data_get($option, 'id'))>{{ data_get($option, 'name') }} ({{ data_get($option, 'count', 0) }})</option>
                        @endforeach
                    </select>
                </details>
                <details class="py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('Career Level') }}</summary>
                    <div class="mt-3 space-y-2">
                        @foreach ($careerOptions as $option)
                            @php $value = (string) data_get($option, 'value'); @endphp
                            <label class="flex items-center gap-2 text-sm text-hb-text">
                                <input type="checkbox" name="career_level[]" value="{{ $value }}" @checked(in_array($value, $selectedCareerLevels, true)) class="rounded border-hb-border text-tb-primary-text focus:ring-tb-primary">
                                <span>{{ $careerLabels[$value] ?? data_get($option, 'label', ucfirst($value)) }}</span>
                                <span class="ms-auto text-xs text-hb-muted">{{ data_get($option, 'count', '') }}</span>
                            </label>
                        @endforeach
                    </div>
                </details>
                <details class="py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('Job Category') }}</summary>
                    <select name="category_id" class="mt-3 w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm" data-submit-filter>
                        <option value="">{{ __('Any category') }}</option>
                        @foreach ($categoryOptions as $option)
                            <option value="{{ data_get($option, 'id') }}" @selected($selectedCategory === (string) data_get($option, 'id'))>{{ data_get($option, 'name') }} ({{ data_get($option, 'count', 0) }})</option>
                        @endforeach
                    </select>
                </details>
                <details class="py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('Job Type') }}</summary>
                    <div class="mt-3 space-y-2">
                        @foreach ($jobTypeOptions as $option)
                            @php $value = (string) data_get($option, 'value'); @endphp
                            <label class="flex items-center gap-2 text-sm text-hb-text">
                                <input type="checkbox" name="employment_type[]" value="{{ $value }}" @checked(in_array($value, $selectedEmploymentTypes, true)) class="rounded border-hb-border text-tb-primary-text focus:ring-tb-primary">
                                <span>{{ $employmentLabels[$value] ?? data_get($option, 'label', ucfirst(str_replace('_', ' ', $value))) }}</span>
                                <span class="ms-auto text-xs text-hb-muted">{{ data_get($option, 'count', '') }}</span>
                            </label>
                        @endforeach
                    </div>
                </details>
                <details class="py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('Date Posted') }}</summary>
                    <div class="mt-3 space-y-2">
                        @foreach (['anytime' => __('Any time'), 'today' => __('Today'), '3days' => __('Last 3 days'), '7days' => __('Last 7 days'), '30days' => __('Last 30 days')] as $value => $label)
                            <label class="flex items-center gap-2 text-sm text-hb-text">
                                <input type="radio" name="date_posted" value="{{ $value }}" @checked($selectedDatePosted === $value) class="border-hb-border text-tb-primary-text focus:ring-tb-primary" data-submit-filter>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </details>
                <details class="py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-hb-navy dark:text-white">{{ __('Salary') }}</summary>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <label class="text-xs text-hb-muted">{{ __('Minimum salary') }}<input type="number" min="0" step="1000" name="salary_min" value="{{ request('salary_min') }}" class="mt-1 w-full rounded-lg border border-hb-border bg-hb-surface px-2 py-2 text-sm"></label>
                        <label class="text-xs text-hb-muted">{{ __('Maximum salary') }}<input type="number" min="0" step="1000" name="salary_max" value="{{ request('salary_max') }}" class="mt-1 w-full rounded-lg border border-hb-border bg-hb-surface px-2 py-2 text-sm"></label>
                    </div>
                </details>
            </div>
            <div class="p-4 pt-0">
                @if ($errors->hasAny(['q', 'location', 'country', 'city', 'area', 'country_id', 'city_id', 'area_id', 'work_mode', 'employment_type', 'career_level', 'category_id', 'salary_min', 'salary_max', 'date_posted', 'sort', 'page']))
                    <p class="mb-3 text-sm text-hb-error" role="alert">{{ __('candidate.vacancies_search.validation_error') }}</p>
                @endif
                <p class="text-center text-xs text-hb-muted">{{ __('Filters apply automatically.') }}</p>
            </div>
        </form>
        </details>
        <section class="rounded-2xl border border-hb-border bg-hb-surface p-4 shadow-sm" aria-labelledby="skills-heading">
            <div class="flex items-start justify-between gap-3">
                <div><h2 id="skills-heading" class="font-bold text-hb-navy dark:text-white">{{ __('Core skills') }}</h2><p class="mt-1 text-xs text-hb-muted">{{ __('Skills from your candidate profile.') }}</p></div>
                <a href="{{ route('frontend.candidate.skills.add') }}" class="text-sm font-semibold text-tb-primary-text hover:underline">{{ __('Edit') }}</a>
            </div>
            @if (count($skillNames))
                <ul class="mt-3 flex flex-wrap gap-2">@foreach ($skillNames as $skill)<li class="rounded-full bg-hb-tint px-3 py-1.5 text-xs font-semibold text-hb-text">{{ $skill }}</li>@endforeach</ul>
            @else
                <p class="mt-3 rounded-lg bg-hb-tint p-3 text-sm text-hb-muted">{{ __('Add skills to your profile to see more relevant match explanations.') }}</p>
            @endif
        </section>
    </aside>

    <section class="min-w-0 space-y-5" aria-label="{{ __('Job discovery') }}">
        <div class="rounded-2xl border border-hb-border bg-hb-surface p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <label class="min-w-0 flex-1 text-sm font-medium text-hb-text">
                {{ __('candidate.vacancies_search.search_label') }}
                <span class="mt-1.5 flex gap-2">
                    <input type="search" name="q" form="candidate-job-filters" value="{{ $searchQuery }}" maxlength="100" data-vacancy-search class="min-w-0 flex-1 rounded-lg border border-hb-border px-3 py-2.5 text-sm focus:border-tb-primary focus:outline-none focus:ring-2 focus:ring-tb-primary/20" placeholder="{{ __('candidate.vacancies_search.search_placeholder') }}">
                    <button type="button" data-clear-vacancy-search class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold text-hb-navy hover:bg-hb-tint">{{ __('candidate.vacancies_search.clear_search') }}</button>
                </span>
            </label>
            <label class="shrink-0 text-sm font-medium text-hb-text">{{ __('Sort by') }}
                <select name="sort" form="candidate-job-filters" class="mt-1.5 w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2.5 text-sm" data-submit-filter>
                    @foreach (['newest' => __('public.jobs.sort_newest'), 'salary' => __('public.jobs.sort_salary'), 'title' => __('public.jobs.sort_title'), 'deadline' => __('public.jobs.sort_deadline')] as $value => $label)
                        <option value="{{ $value }}" @selected(($selectedSort ?? 'newest') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            </div>
            <div class="mt-4 flex flex-wrap gap-2" aria-label="{{ __('Popular searches') }}">
                @foreach (['Frontend Developer', 'React.js', 'Node.js', 'UI/UX Designer'] as $searchTerm)
                    <button type="button" data-discovery-search="{{ $searchTerm }}" class="rounded-full border border-hb-border bg-hb-tint px-3 py-1.5 text-xs font-semibold text-hb-navy transition hover:border-tb-primary hover:bg-tb-primary-soft hover:text-tb-primary-text">{{ $searchTerm }}</button>
                @endforeach
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-hb-navy dark:text-white">{{ __('Open roles') }}</h2>
                @if (is_numeric($totalMatching))<p class="mt-1 text-sm text-hb-muted">{{ __('public.jobs.match_count', ['count' => (int) $totalMatching]) }}</p>@endif
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-save-search class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold text-hb-navy transition hover:bg-hb-tint">{{ __('Save this search') }}</button>
                <button type="button" data-toggle-hidden class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold text-hb-navy transition hover:bg-hb-tint" aria-pressed="false">{{ __('Show hidden roles') }}</button>
            </div>
        </div>
        <p class="text-sm text-hb-muted" data-discovery-status role="status" aria-live="polite"></p>
        @if (($skillsSource ?? null) === 'profile')
            <p class="text-xs text-hb-muted" role="status">{{ __('Skills are read from your profile because the dedicated skills endpoint is unavailable.') }}</p>
        @endif
        @if (count($contextErrors ?? []))
            <p class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-xs leading-5 text-blue-900 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200" role="status">{{ __('Some profile, skill, or application context could not be loaded. Discovery remains available, but match and applied indicators may be incomplete.') }}</p>
        @endif
        <input type="hidden" name="page" form="candidate-job-filters" value="{{ request()->query('page', 1) }}" data-vacancy-page>

        @if ($loadError ?? false)
            <div class="rounded-2xl border border-hb-error/30 bg-hb-surface p-6" role="alert" data-vacancies-load-error>
                <p class="font-semibold text-hb-error">{{ $loadError }}</p>
                <a href="{{ request()->fullUrl() }}" class="mt-3 inline-flex font-semibold text-tb-primary-text hover:underline">{{ __('candidate.vacancies_search.retry') }}</a>
            </div>
        @else
        <div class="grid gap-4 lg:grid-cols-2" data-job-list>
            @forelse ($items as $row)
                @php
                    $jobId = (string) data_get($row, 'id', '');
                    $companyValue = data_get($row, 'company.name') ?? data_get($row, 'company_name') ?? data_get($row, 'company');
                    $company = is_scalar($companyValue) ? $companyValue : '—';
                    $jobSkillRows = \App\Support\Rows::from(data_get($row, 'skills', []));
                    $jobSkillNames = collect($jobSkillRows)->map(fn ($skill) => is_string($skill) ? $skill : data_get($skill, 'name'))->filter(fn ($name) => is_string($name) && $name !== '')->values()->all();
                    $overlap = collect($jobSkillNames)->filter(fn ($jobSkill) => collect($skillNames)->contains(fn ($candidateSkill) => mb_strtolower($candidateSkill) === mb_strtolower($jobSkill)))->values()->all();
                    $applied = in_array($jobId, $appliedJobIds ?? [], true);
                @endphp
                <article
                    class="flex h-full flex-col rounded-2xl border border-hb-border bg-hb-surface p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    data-job-card
                    data-job-id="{{ $jobId }}"
                    data-location="{{ data_get($row, 'location', '') }}"
                    data-mode="{{ data_get($row, 'work_mode', '') }}"
                    data-search-text="{{ data_get($row, 'title', '') }} {{ $company }} {{ data_get($row, 'location', '') }}"
                    @if (in_array($jobId, $appliedJobIds ?? [], true)) data-applied="true" @endif
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-bold text-hb-navy dark:text-white">{{ data_get($row, 'title', '—') }}</h3>
                            <p class="mt-1 text-sm text-hb-muted">{{ $company }} <span aria-hidden="true">·</span> {{ data_get($row, 'location', '—') }}</p>
                        </div>
                        @if ($applied)
                            <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ __('Already applied') }}</span>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-hb-tint px-2.5 py-1 font-medium text-hb-text">{{ data_get($row, 'work_mode', __('Work mode not listed')) }}</span>
                        <span class="rounded-full bg-hb-tint px-2.5 py-1 font-medium text-hb-text">{{ __('Deadline') }}: {{ \App\Support\Rows::date(data_get($row, 'deadline'), 'Y-m-d') }}</span>
                    </div>
                    <div class="mt-4 rounded-xl bg-tb-primary-soft/70 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-tb-primary-text">{{ __('Why it fits') }}</p>
                        @if (count($overlap))
                            <p class="mt-1 text-sm text-hb-text">{{ __('Your profile matches :count listed skill(s): :skills', ['count' => count($overlap), 'skills' => implode(', ', $overlap)]) }}</p>
                        @else
                            <p class="mt-1 text-sm text-hb-text">{{ __('Compare the role requirements with your profile skills to see where you align.') }}</p>
                        @endif
                    </div>
                    <div class="mt-auto flex flex-wrap justify-end gap-2 pt-4">
                        <button type="button" data-hide-job class="rounded-lg px-3 py-2 text-sm font-semibold text-hb-muted hover:bg-hb-tint">{{ __('Hide role') }}</button>
                        <x-button variant="light" size="sm" :href="route('frontend.candidate.vacancies.show', ['vacancyId' => data_get($row, 'id')])">{{ __('View') }}</x-button>
                        @if ($applied)
                            <x-button variant="primary" size="sm" :href="route('frontend.candidate.applications.index')">{{ __('View applications') }}</x-button>
                        @else
                            <x-button variant="primary" size="sm" :href="route('frontend.candidate.vacancies.apply', ['vacancyId' => data_get($row, 'id')])">{{ __('Apply') }}</x-button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-hb-border bg-hb-surface px-6 py-12 text-center text-hb-muted lg:col-span-2" data-empty-results>{{ __('candidate.vacancies_search.empty') }}</div>
            @endforelse
        </div>
        <p class="hidden rounded-xl border border-hb-border bg-hb-surface p-6 text-center text-sm text-hb-muted" data-empty-filter>{{ __('candidate.vacancies_search.hidden_empty') }}</p>
        @include('frontend.partials.pagination', ['meta' => $meta, 'ariaLabel' => __('candidate.vacancies_search.pagination')])
        @endif
    </section>

</div>

<script>
    (() => {
        const form = document.querySelector('[data-vacancy-search-form]');
        const filterPanel = document.querySelector('[data-candidate-filter-panel]');
        const searchInput = document.querySelector('[data-vacancy-search]');
        const clearSearchButton = document.querySelector('[data-clear-vacancy-search]');
        const pageInput = document.querySelector('[data-vacancy-page]');
        const list = document.querySelector('[data-job-list]');
        const cards = list ? [...list.querySelectorAll('[data-job-card]')] : [];
        const locationInput = document.querySelector('[data-preference-location]');
        const modeInputs = [...document.querySelectorAll('[name="work_mode[]"]')];
        const saveButton = document.querySelector('[data-save-search]');
        const hiddenToggle = document.querySelector('[data-toggle-hidden]');
        const quickSearchButtons = [...document.querySelectorAll('[data-discovery-search]')];
        const status = document.querySelector('[data-discovery-status]');
        const empty = document.querySelector('[data-empty-filter]');
        const filterControls = [
            ...form.querySelectorAll('select, input[type="checkbox"], input[type="radio"]'),
            ...form.querySelectorAll('input[type="number"]'),
            ...document.querySelectorAll('select[form="candidate-job-filters"]'),
        ];
        const storageKey = 'candidate-discovery-v1';
        let hiddenIds = [];
        let showHidden = false;
        let filterTimer = null;
        const queryParameters = new URLSearchParams(window.location.search);
        const hasFilterQuery = ['q', 'location', 'country_id', 'city_id', 'area_id', 'work_mode[]', 'employment_type[]', 'career_level[]', 'category_id', 'salary_min', 'salary_max', 'date_posted', 'sort'].some((key) => queryParameters.has(key));

        if (filterPanel && window.matchMedia('(max-width: 1279px)').matches) filterPanel.open = false;
        window.addEventListener('resize', () => {
            if (filterPanel && window.matchMedia('(min-width: 1280px)').matches) filterPanel.open = true;
        });

        function readPreferences() {
            try {
                const stored = JSON.parse(localStorage.getItem(storageKey) || '{}');
                hiddenIds = Array.isArray(stored.hiddenIds) ? stored.hiddenIds.map(String) : [];
                if (!hasFilterQuery && stored.search && typeof stored.search === 'object') {
                    searchInput.value = String(stored.search.q ?? searchInput.value ?? '');
                    locationInput.value = String(stored.search.location ?? locationInput.value ?? '');
                    const storedModes = Array.isArray(stored.search.workModes) ? stored.search.workModes : [stored.search.mode].filter(Boolean);
                    modeInputs.forEach((input) => { input.checked = storedModes.includes(input.value); });
                }
            } catch {
                status.textContent = @json(__('Your browser could not load saved discovery preferences.'));
            }
            clearSearchButton.disabled = searchInput.value.trim() === '';
        }

        function persist(extra = {}) {
            try {
                const current = JSON.parse(localStorage.getItem(storageKey) || '{}');
                localStorage.setItem(storageKey, JSON.stringify({
                    ...current,
                    hiddenIds,
                    search: {
                        q: searchInput.value.trim(),
                        location: locationInput.value.trim(),
                        workModes: modeInputs.filter((input) => input.checked).map((input) => input.value),
                    },
                    ...extra,
                }));
                return true;
            } catch {
                status.textContent = @json(__('Changes could not be saved in this browser.'));
                return false;
            }
        }

        function updateCards() {
            let visible = 0;
            cards.forEach((card) => {
                const hidden = hiddenIds.includes(card.dataset.jobId);
                const isVisible = showHidden || !hidden;
                card.hidden = !isVisible;
                visible += Number(isVisible);
                const hideButton = card.querySelector('[data-hide-job]');
                hideButton.textContent = hidden ? @json(__('Unhide role')) : @json(__('Hide role'));
                hideButton.setAttribute('aria-pressed', String(hidden));
            });
            empty?.classList.toggle('hidden', visible !== 0 || cards.length === 0);
            hiddenToggle.textContent = showHidden ? @json(__('Hide hidden roles')) : @json(__('Show hidden roles'));
            hiddenToggle.setAttribute('aria-pressed', String(showHidden));
        }

        function scheduleFilterSubmit(delay = 250) {
            window.clearTimeout(filterTimer);
            filterTimer = window.setTimeout(() => {
                pageInput.value = '1';
                form.requestSubmit();
            }, delay);
        }

        async function suggestLocationFromIp() {
            if (locationInput.value.trim()) return;
            const structuredLocationSelected = [...document.querySelectorAll('[data-location-select]')]
                .some((select) => select.value !== '');
            if (structuredLocationSelected) return;

            const cacheKey = 'candidate-ip-location-suggestion-v1';
            try {
                const cachedLocation = sessionStorage.getItem(cacheKey);
                if (cachedLocation) {
                    if (cachedLocation !== 'unavailable') {
                        const cached = JSON.parse(cachedLocation);
                        locationInput.value = [cached.city, cached.country].filter(Boolean).join(', ');
                        if (locationInput.value) scheduleFilterSubmit(0);
                    }
                    return;
                }
            } catch {
                // Continue with a fresh lookup if this browser blocks session storage.
            }

            const controller = new AbortController();
            const timeout = window.setTimeout(() => controller.abort(), 3000);

            try {
                const response = await fetch('https://ipwho.is/?fields=success,city,country', {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                if (!response.ok) {
                    try { sessionStorage.setItem(cacheKey, 'unavailable'); } catch { /* Storage is optional. */ }
                    return;
                }

                const data = await response.json();
                const city = data.city || data.region;
                if (!data.success || !city || locationInput.value.trim()) {
                    try { sessionStorage.setItem(cacheKey, 'unavailable'); } catch { /* Storage is optional. */ }
                    return;
                }

                try { sessionStorage.setItem(cacheKey, JSON.stringify({ city, country: data.country || '' })); } catch { /* Storage is optional. */ }
                locationInput.value = [city, data.country].filter(Boolean).join(', ');
                scheduleFilterSubmit(0);
            } catch {
                // IP lookup is optional; keep the page usable if the service is unavailable.
                try { sessionStorage.setItem(cacheKey, 'unavailable'); } catch { /* Storage is optional. */ }
            } finally {
                window.clearTimeout(timeout);
            }
        }

        readPreferences();
        updateCards();
        if (!locationInput.value.trim()) suggestLocationFromIp();
        searchInput.addEventListener('input', () => {
            clearSearchButton.disabled = searchInput.value.trim() === '';
            scheduleFilterSubmit(300);
        });
        clearSearchButton.addEventListener('click', () => {
            searchInput.value = '';
            clearSearchButton.disabled = true;
            scheduleFilterSubmit(0);
            searchInput.focus();
        });
        locationInput.addEventListener('input', () => scheduleFilterSubmit(300));
        filterControls.forEach((control) => control.addEventListener('change', () => {
            if (control.type === 'number') {
                window.setTimeout(() => {
                    const salaryInputs = [...form.querySelectorAll('input[type="number"]')];
                    if (salaryInputs.includes(document.activeElement)) return;
                    scheduleFilterSubmit(0);
                }, 0);
                return;
            }

            scheduleFilterSubmit(0);
        }));
        document.querySelector('[name="country_id"]')?.addEventListener('change', () => {
            document.querySelector('[name="city_id"]').value = '';
            document.querySelector('[name="area_id"]').value = '';
        });
        document.querySelector('[name="city_id"]')?.addEventListener('change', () => {
            document.querySelector('[name="area_id"]').value = '';
        });
        quickSearchButtons.forEach((button) => {
            button.addEventListener('click', () => {
                searchInput.value = button.dataset.discoverySearch;
                clearSearchButton.disabled = searchInput.value.trim() === '';
                scheduleFilterSubmit(0);
            });
        });
        form.addEventListener('submit', () => {
            pageInput.value = '1';
            status.textContent = @json(__('candidate.vacancies_search.loading'));
        });
        if (!hasFilterQuery && !document.querySelector('[data-vacancies-load-error]')
            && (searchInput.value.trim() || locationInput.value.trim() || modeInputs.some((input) => input.checked))) {
            scheduleFilterSubmit(0);
        }
        saveButton.addEventListener('click', () => {
            status.textContent = persist({ savedAt: new Date().toISOString() })
                ? @json(__('Search saved on this device.'))
                : @json(__('Changes could not be saved in this browser.'));
        });
        hiddenToggle.addEventListener('click', () => {
            showHidden = !showHidden;
            updateCards();
        });
        cards.forEach((card) => {
            card.querySelector('[data-hide-job]').addEventListener('click', () => {
                const id = card.dataset.jobId;
                hiddenIds = hiddenIds.includes(id) ? hiddenIds.filter((hiddenId) => hiddenId !== id) : [...hiddenIds, id];
                const saved = persist();
                updateCards();
                status.textContent = saved
                    ? @json(__('Hidden roles updated on this device.'))
                    : @json(__('Changes could not be saved in this browser.'));
            });
        });
    })();
</script>
@endsection
