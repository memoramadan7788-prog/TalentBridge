@extends('frontend.layouts.app')

@section('title', __('company.vacancies_management.title'))

@section('page_header')
    <div><p class="text-sm font-semibold text-hb-muted">{{ __('company.vacancies_management.eyebrow') }}</p><h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.vacancies_management.title') }}</h1></div>
@endsection

@section('page_actions')
    <x-button variant="primary" size="md" :href="route('frontend.company.vacancies.create')">{{ __('company.vacancies_management.new') }}</x-button>
@endsection

@section('content')
@php $draftCount = collect(\App\Support\Rows::from($vacancies ?? []))->filter(fn ($row) => strtolower((string) data_get($row, 'status')) === 'draft')->count(); @endphp
<div class="space-y-5" data-company-vacancies>
    @if (session('success_key'))<x-alert variant="success">{{ __('company.vacancies_management.saved') }}</x-alert>@endif
    @if ($loadError)<x-alert variant="error">{{ $loadError }}</x-alert>@endif
    <nav class="flex flex-wrap gap-2" aria-label="{{ __('company.vacancies_management.tabs') }}">
        @foreach (['open', 'draft', 'closed', 'all'] as $tab)
            <a href="{{ route('frontend.company.vacancies.index', array_merge(request()->except('page'), ['status' => $tab])) }}" class="hb-pill" @if ($filters['status'] === $tab) aria-current="page" @endif>{{ __('company.vacancies_management.'.$tab) }}</a>
        @endforeach
    </nav>
    <x-filter-bar :action="route('frontend.company.vacancies.index')" data-loading-form>
        <input type="hidden" name="status" value="{{ $filters['status'] }}">
        <div>
            <label for="vac-q" class="hb-label">{{ __('company.vacancies_management.search') }}</label>
            <input id="vac-q" name="q" maxlength="120" value="{{ $filters['q'] }}" class="hb-input">
        </div>
        <div>
            <label for="vac-location" class="hb-label">{{ __('company.vacancies_management.location') }}</label>
            <input id="vac-location" name="location" maxlength="120" value="{{ $filters['location'] }}" class="hb-input">
        </div>
        <div>
            <label for="vac-model" class="hb-label">{{ __('company.vacancies_management.hiring_model') }}</label>
            <select id="vac-model" name="hiring_model" class="hb-input">
                <option value="">{{ __('company.vacancies_management.any_model') }}</option>
                @foreach (['direct', 'agency'] as $model)
                    <option value="{{ $model }}" @selected($filters['hiring_model'] === $model)>{{ __('company.jobs.hiring_model.'.$model) }}</option>
                @endforeach
            </select>
        </div>
        <x-slot:actions>
            <x-button type="submit" variant="primary" size="md" data-loading-submit><span data-submit-idle>{{ __('company.vacancies_management.apply_filters') }}</span><span data-submit-loading class="hidden" aria-hidden="true">{{ __('common.please_wait') }}</span></x-button>
            <a class="text-sm font-medium text-hb-muted underline-offset-4 hover:text-tb-primary-text hover:underline" href="{{ route('frontend.company.vacancies.index', ['status' => $filters['status']]) }}">{{ __('company.vacancies_management.clear') }}</a>
        </x-slot:actions>
    </x-filter-bar>
    <p class="hb-note" role="status">{{ __('company.vacancies_management.local_filter_scope') }}</p>

    @if ($draftCount > 0)<section class="rounded-2xl border border-hb-pending/30 bg-hb-pending-soft p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-hb-pending">{{ __('company.vacancies_management.draft_label') }}</p>
            <h2 class="mt-1 text-lg font-semibold text-hb-navy">{{ trans_choice('company.vacancies_management.draft_count', $draftCount, ['count' => $draftCount]) }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('company.vacancies_management.draft_hint') }}</p>
        </section>@endif

    @if (count($items) === 0 && !$loadError)<x-empty-state :title="__('company.vacancies_management.empty_title')">{{ __('company.vacancies_management.empty_body') }}</x-empty-state>@elseif (count($items) > 0)<div class="grid gap-4">@foreach ($items as $row)
        <article class="hb-card hb-card--interactive">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="hb-card__title">{{ data_get($row, 'title', __('company.vacancies_management.untitled')) }}</h2>
                        <x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge>
                    </div>
                    <p class="hb-card__hint">{{ data_get($row, 'location', '—') }} <span aria-hidden="true">·</span> {{ __('company.vacancies_management.requisition_id') }} #{{ data_get($row, 'id', '—') }}</p>
                    <dl class="hb-facts mt-4 border-t border-hb-border pt-4">
                        <div><dt>{{ __('company.vacancies_management.owner') }}</dt><dd>{{ data_get($row, 'requisition_owner.name', data_get($row, 'owner.name', data_get($row, 'requisition_owner_id', '—'))) }}</dd></div>
                        <div><dt>{{ __('company.vacancies_management.hiring_model') }}</dt><dd>{{ data_get($row, 'hiring_model') ? __('company.jobs.hiring_model.'.data_get($row, 'hiring_model')) : '—' }}</dd></div>
                        <div><dt>{{ __('company.vacancies_management.pipeline') }}</dt><dd>{{ is_numeric(data_get($row, 'applications_count')) ? trans_choice('company.vacancies_management.pipeline_count', (int) data_get($row, 'applications_count'), ['count' => (int) data_get($row, 'applications_count')]) : __('company.vacancies_management.pipeline_unavailable') }}</dd></div>
                    </dl>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                    <x-button variant="light" size="sm" :href="route('frontend.company.vacancies.overview', ['vacancyId' => data_get($row, 'id')])">{{ __('company.vacancies_management.overview') }}</x-button>
                    <x-button variant="light" size="sm" :href="route('frontend.company.vacancies.applications', ['vacancyId' => data_get($row, 'id')])">{{ __('company.vacancies_management.applications') }}</x-button>
                    <x-button variant="light" size="sm" :href="route('frontend.company.vacancies.update', ['vacancyId' => data_get($row, 'id')])">{{ __('company.vacancies_management.edit') }}</x-button>
                </div>
            </div>
        </article>
    @endforeach</div>@endif
    @if (is_array($pagination ?? null) && $pagination['last_page'] > 1)
        <nav class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="{{ __('company.vacancies_management.pagination') }}">
            <p class="text-sm text-hb-muted">
                @if (is_int($pagination['total']))
                    {{ __('company.vacancies_management.pagination_summary', ['current' => $pagination['current_page'], 'last' => $pagination['last_page'], 'total' => $pagination['total']]) }}
                @else
                    {{ __('company.vacancies_management.pagination_pages', ['current' => $pagination['current_page'], 'last' => $pagination['last_page']]) }}
                @endif
            </p>
            <div class="flex items-center gap-3">
                <x-button variant="light" size="sm" :href="route('frontend.company.vacancies.index', array_merge($filters, ['page' => $pagination['current_page'] - 1]))" :disabled="$pagination['current_page'] <= 1">{{ __('company.vacancies_management.previous') }}</x-button>
                <span class="text-sm text-hb-muted" data-pagination-pages>{{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}</span>
                <x-button variant="light" size="sm" :href="route('frontend.company.vacancies.index', array_merge($filters, ['page' => $pagination['current_page'] + 1]))" :disabled="$pagination['current_page'] >= $pagination['last_page']">{{ __('company.vacancies_management.next') }}</x-button>
            </div>
        </nav>
    @endif
</div>
@endsection
