@extends('frontend.layouts.app')

@section('title', __('Hiring analytics'))

@php
    $jobs = \App\Support\Rows::from($jobs ?? []);
    $displayValue = static fn (mixed $value, string $fallback = '—'): string => is_scalar($value) ? (string) $value : $fallback;
    $statusCounts = collect($jobs)->groupBy(fn ($job) => strtolower($displayValue(data_get($job, 'status'), 'unknown')))->map->count();
    $activeCount = collect($jobs)->filter(fn ($job) => in_array(strtolower($displayValue(data_get($job, 'status'), '')), ['active', 'open', 'published'], true))->count();
    $statusTotal = max(1, count($jobs));
@endphp

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-tb-primary-text">{{ __('Hiring team') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Hiring analytics') }}</h1>
    </div>
@endsection

@section('content')
<div class="space-y-5">
    <form method="GET" action="{{ route('frontend.company.analytics') }}" class="flex flex-col gap-3 rounded-2xl border border-hb-border bg-hb-surface p-4 sm:flex-row sm:items-end">
        <label class="block text-sm font-medium text-hb-text">{{ __('Created from') }}
            <input type="date" name="from" value="{{ $from }}" class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2 sm:w-auto">
        </label>
        <label class="block text-sm font-medium text-hb-text">{{ __('Created to') }}
            <input type="date" name="to" value="{{ $to }}" min="{{ $from }}" class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2 sm:w-auto">
        </label>
        <button type="submit" class="rounded-lg bg-tb-primary-solid px-4 py-2.5 text-sm font-semibold text-white hover:bg-tb-primary-solid-hover">{{ __('Apply date range') }}</button>
        <a href="{{ route('frontend.company.analytics') }}" class="rounded-lg border border-hb-border px-4 py-2.5 text-center text-sm font-semibold text-hb-navy hover:bg-hb-tint">{{ __('Clear') }}</a>
        <button type="button" data-export-jobs class="sm:ms-auto rounded-lg border border-hb-border px-4 py-2.5 text-sm font-semibold text-hb-navy hover:bg-hb-tint">{{ __('Export job data') }}</button>
    </form>

    @if (! $jobsAvailable || ! $analyticsAvailable)
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900" role="alert">{{ __('Could not load verified job data: :message', ['message' => $jobsAvailable ? $analyticsError : $apiMessage]) }}</div>
    @endif

    <div data-page-tabs>
        @include('frontend.partials.page-tabs', [
            'group' => 'hiring-analytics',
            'tabs' => ['overview' => __('Overview'), 'jobs' => __('Jobs in selected range')],
        ])
        <section id="hiring-analytics-panel-overview" role="tabpanel" aria-labelledby="hiring-analytics-tab-overview" aria-hidden="false" tabindex="0" data-page-panel="overview" class="space-y-5">
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Hiring key performance indicators') }}">
        @foreach ([
            ['label' => __('Roles in range'), 'value' => $jobsAvailable ? count($jobs) : '—', 'note' => __('Jobs returned by company API')],
            ['label' => __('Active / published roles'), 'value' => $jobsAvailable ? $activeCount : '—', 'note' => __('Based on job status')],
            ['label' => __('Applications'), 'value' => $analyticsAvailable ? (int) ($analytics['application_count'] ?? 0) : '—', 'note' => __('Applications for jobs in this date range')],
            ['label' => __('Median time in stage'), 'value' => $analyticsAvailable && ($analytics['overall_median_days'] ?? null) !== null ? $analytics['overall_median_days'].' days' : '—', 'note' => __('Calculated from application stage history')],
        ] as $kpi)
            <article class="rounded-xl border border-hb-border bg-hb-surface p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ $kpi['label'] }}</p>
                <p class="mt-2 text-2xl font-bold text-hb-navy">{{ $kpi['value'] }}</p>
                <p class="mt-1 text-xs text-hb-muted">{{ $kpi['note'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-hb-border bg-hb-surface p-5 shadow-sm" aria-labelledby="job-status-chart">
            <h2 id="job-status-chart" class="text-lg font-bold text-hb-navy">{{ __('Job status distribution') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('Distribution of jobs returned for the selected date range. This is not the applicant pipeline.') }}</p>
            @if ($jobsAvailable && $statusCounts->isNotEmpty())
                <ul class="mt-5 space-y-4">
                    @foreach ($statusCounts as $status => $count)
                        @php $percent = (int) round(($count / $statusTotal) * 100); @endphp
                        <li>
                            <div class="mb-1 flex justify-between gap-3 text-sm"><span class="font-medium text-hb-text">{{ \Illuminate\Support\Str::headline($status) }}</span><span class="text-hb-muted">{{ $count }} · {{ $percent }}%</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-hb-tint" role="progressbar" aria-label="{{ \Illuminate\Support\Str::headline($status) }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}"><span class="block h-full rounded-full bg-tb-primary" style="width: {{ $percent }}%"></span></div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-5 rounded-lg bg-hb-tint p-4 text-sm text-hb-muted">{{ __('No job status data is available for this range.') }}</p>
            @endif
        </section>

        <section class="rounded-2xl border border-hb-border bg-hb-surface p-5 shadow-sm" aria-labelledby="stage-distribution">
            <h2 id="stage-distribution" class="text-lg font-bold text-hb-navy">{{ __('Applicant stage distribution') }}</h2>
            @if ($analyticsAvailable && !empty($analytics['stages']))
                @php $applicationTotal = max(1, (int) ($analytics['application_count'] ?? 0)); @endphp
                <ul class="mt-5 space-y-4">
                    @foreach ($analytics['stages'] as $stage)
                        @php $count = (int) ($stage['count'] ?? 0); $percent = (int) round(($count / $applicationTotal) * 100); @endphp
                        <li>
                            <div class="mb-1 flex justify-between gap-3 text-sm"><span class="font-medium text-hb-text">{{ $stage['label'] ?? $stage['code'] }}</span><span class="text-hb-muted">{{ $count }} · {{ $percent }}%</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-hb-tint"><span class="block h-full rounded-full bg-tb-primary" style="width: {{ $percent }}%"></span></div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-5 rounded-lg bg-hb-tint p-4 text-sm text-hb-muted">{{ $analyticsAvailable ? __('No applications are available for this range.') : ($analyticsError ?? __('Could not load hiring analytics.')) }}</p>
            @endif
        </section>

        <section class="rounded-2xl border border-hb-border bg-hb-surface p-5 shadow-sm" aria-labelledby="time-in-stage">
            <h2 id="time-in-stage" class="text-lg font-bold text-hb-navy">{{ __('Time in stage') }}</h2>
            @if ($analyticsAvailable && !empty($analytics['stage_durations']))
                <div class="mt-4 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="text-xs uppercase text-hb-muted"><tr><th class="py-2">{{ __('Stage') }}</th><th class="py-2">{{ __('Median days') }}</th><th class="py-2">{{ __('Average days') }}</th><th class="py-2">{{ __('Records') }}</th></tr></thead><tbody>
                    @foreach ($analytics['stage_durations'] as $duration)
                        <tr class="border-t border-hb-border"><td class="py-2 font-medium text-hb-text">{{ $duration['label'] ?? $duration['code'] }}</td><td class="py-2">{{ $duration['median_days'] }}</td><td class="py-2">{{ $duration['average_days'] }}</td><td class="py-2">{{ $duration['sample_count'] }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <p class="mt-5 rounded-lg bg-hb-tint p-4 text-sm text-hb-muted">{{ $analyticsAvailable ? __('No stage history is available for this range yet.') : ($analyticsError ?? __('Could not load hiring analytics.')) }}</p>
            @endif
        </section>

        <section class="rounded-2xl border border-hb-border bg-hb-surface p-5 shadow-sm" aria-labelledby="source-performance">
            <h2 id="source-performance" class="text-lg font-bold text-hb-navy">{{ __('Source performance') }}</h2>
            @if ($analyticsAvailable && !empty($analytics['sources']))
                <ul class="mt-5 divide-y divide-hb-border">
                    @foreach ($analytics['sources'] as $source)
                        <li class="flex items-center justify-between gap-3 py-3 text-sm"><span class="font-medium text-hb-text">{{ $source['label'] }}</span><span class="rounded-full bg-hb-tint px-3 py-1 font-semibold text-hb-navy">{{ $source['count'] }}</span></li>
                    @endforeach
                </ul>
            @else
                <p class="mt-5 rounded-lg bg-hb-tint p-4 text-sm text-hb-muted">{{ $analyticsAvailable ? __('No source information is available for these applications yet.') : ($analyticsError ?? __('Could not load hiring analytics.')) }}</p>
            @endif
        </section>
    </div>

        </section>
        <section id="hiring-analytics-panel-jobs" role="tabpanel" aria-labelledby="hiring-analytics-tab-jobs" aria-hidden="true" tabindex="0" data-page-panel="jobs" class="hidden">
    <section class="rounded-2xl border border-hb-border bg-hb-surface p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-hb-navy">{{ __('Jobs in selected range') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('CSV export includes only the live job data shown below.') }}</p>
            </div>
            <span class="text-sm text-hb-muted">{{ count($jobs) }} {{ __('roles') }}</span>
        </div>
        @if (count($jobs))
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[36rem] text-start text-sm" data-export-table>
                    <thead><tr class="border-b border-hb-border text-xs uppercase text-hb-muted"><th class="px-3 py-3">{{ __('Title') }}</th><th class="px-3 py-3">{{ __('Status') }}</th><th class="px-3 py-3">{{ __('Location') }}</th><th class="px-3 py-3">{{ __('Created') }}</th></tr></thead>
                    <tbody>
                        @foreach ($jobs as $job)
                            <tr class="border-b border-hb-border/70"><td class="px-3 py-3 font-medium text-hb-navy">{{ $displayValue(data_get($job, 'title')) }}</td><td class="px-3 py-3">{{ $displayValue(data_get($job, 'status')) }}</td><td class="px-3 py-3">{{ $displayValue(data_get($job, 'location')) }}</td><td class="px-3 py-3">{{ \App\Support\Rows::date($displayValue(data_get($job, 'created_at'), ''), 'Y-m-d') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="mt-4 text-sm text-hb-muted">{{ __('No jobs match this date range.') }}</p>
        @endif
    </section>
        </section>
    </div>
</div>

<script>
    document.querySelector('[data-export-jobs]')?.addEventListener('click', () => {
        const table = document.querySelector('[data-export-table]');
        if (!table) return;
        const csv = [...table.rows].map((row) => [...row.cells].map((cell) => `"${cell.innerText.replaceAll('"', '""')}"`).join(',')).join('\r\n');
        const link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        link.download = 'hiring-jobs.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });
</script>
@endsection
