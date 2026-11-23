@extends('frontend.layouts.app')

@section('title', __('candidate.resume_upload.matches_title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.resume_upload.matches_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.resume_upload.matches_intro') }}</p>
@endsection

@section('page_actions')
    <x-button variant="light" :href="route('frontend.candidate.profile')">{{ __('candidate.resume_upload.back_to_profile') }}</x-button>
@endsection

@section('content')
    @if ($hasPendingMatches)
        <meta http-equiv="refresh" content="8">
        <x-alert variant="info" class="mb-5" role="status">{{ __('candidate.resume_upload.match_pending') }}</x-alert>
    @endif

    <x-card class="mb-5">
        <h2 class="font-semibold text-hb-navy">{{ data_get($resume, 'original_filename') }}</h2>
        <p class="mt-2 text-sm text-hb-muted">
            {{ __('candidate.resume_upload.scan_status') }}:
            {{ __('candidate.resume_upload.scan_status_'.data_get($resume, 'ai_scan.status', 'not_started')) }}
        </p>
        @if (data_get($resume, 'ai_scan.status') !== 'ok')
            <p class="mt-2 text-sm text-hb-muted">{{ data_get($resume, 'ai_scan.error') ?: __('candidate.resume_upload.scan_before_match') }}</p>
        @endif
    </x-card>

    @if (data_get($resume, 'ai_scan.status') === 'ok')
        @php
            $placeholderPattern = '/^(none|none mentioned|not mentioned|not specified|not provided|n\/?a|no weaknesses|no specific weaknesses|لا يوجد|لا توجد|غير مذكور|غير متوفر)[.!،。 ]*$/iu';
            $strengths = collect(data_get($resume, 'ai_scan.strengths', []))->filter(fn ($item) => is_string($item) && trim($item) !== '' && ! preg_match($placeholderPattern, trim($item)))->unique()->values();
            $weaknesses = collect(data_get($resume, 'ai_scan.weaknesses', []))->filter(fn ($item) => is_string($item) && trim($item) !== '' && ! preg_match($placeholderPattern, trim($item)))->unique()->values();
            $score = (float) data_get($resume, 'ai_scan.score', 0);
        @endphp
        <x-card class="mb-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-hb-muted">{{ __('AI resume review') }}</p>
                    <h2 class="mt-1 text-xl font-bold text-hb-navy">{{ __('CV analysis') }}</h2>
                </div>
                <div class="min-w-40 rounded-xl bg-sky-50 px-4 py-3 text-center">
                    <span class="block text-xs font-semibold uppercase tracking-wide text-sky-800">{{ __('Resume score') }}</span>
                    <span class="mt-1 block text-2xl font-bold text-sky-900">{{ number_format($score, 0) }}<span class="text-sm font-medium"> / 100</span></span>
                </div>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="{{ __('Resume score') }}" aria-valuenow="{{ $score }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-sky-600" style="width: {{ max(0, min(100, $score)) }}%"></div>
            </div>
            @if (filled(data_get($resume, 'ai_scan.summary')))
                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <h3 class="text-sm font-semibold text-hb-navy">{{ __('Resume summary') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-hb-muted">{{ data_get($resume, 'ai_scan.summary') }}</p>
                </div>
            @endif
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <section class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-700" aria-hidden="true">✓</span>
                        <h3 class="font-semibold text-emerald-900">{{ __('Strengths') }}</h3>
                    </div>
                    @if ($strengths->isNotEmpty())
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                            @foreach ($strengths as $strength)<li class="flex gap-2"><span class="text-emerald-700" aria-hidden="true">•</span><span>{{ $strength }}</span></li>@endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-hb-muted">{{ __('No clear strengths were identified in the available resume text.') }}</p>
                    @endif
                </section>
                <section class="rounded-xl border border-amber-200 bg-amber-50/60 p-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-800" aria-hidden="true">↗</span>
                        <h3 class="font-semibold text-amber-950">{{ __('Areas to improve') }}</h3>
                    </div>
                    @if ($weaknesses->isNotEmpty())
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                            @foreach ($weaknesses as $weakness)<li class="flex gap-2"><span class="text-amber-700" aria-hidden="true">•</span><span>{{ $weakness }}</span></li>@endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm leading-6 text-hb-muted">{{ __('No specific improvement areas were identified. Add measurable results and clear project details to your resume for more useful feedback.') }}</p>
                    @endif
                </section>
            </div>
            @php($suggestedJobs = collect(data_get($resume, 'ai_scan.suggested_jobs', []))->filter(fn ($job) => filled(data_get($job, 'title')))->values())
            @if ($suggestedJobs->isNotEmpty())
                <section class="mt-5 border-t border-hb-border pt-5">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <div>
                            <h3 class="font-semibold text-hb-navy">{{ __('Suggested job titles') }}</h3>
                            <p class="mt-1 text-sm text-hb-muted">{{ __('Roles that appear relevant to the experience and skills in this resume.') }}</p>
                        </div>
                        <span class="text-xs font-medium text-hb-muted">{{ $suggestedJobs->count() }} {{ __('suggestions') }}</span>
                    </div>
                    <ol class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($suggestedJobs as $job)
                            <li class="rounded-xl border border-hb-border bg-white p-3">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sm font-bold text-sky-800">{{ $loop->iteration }}</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold leading-5 text-hb-navy">{{ data_get($job, 'title') }}</p>
                                        @if (data_get($job, 'approval_status') === 'pending')<p class="mt-1 text-xs text-hb-muted">{{ __('Awaiting catalog approval') }}</p>@endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
            <div class="mt-5 flex justify-end border-t border-hb-border pt-4">
                <form method="POST" action="{{ route('frontend.candidate.resume.rescan', ['resumeId' => data_get($resume, 'id')]) }}">
                    @csrf
                    <x-button variant="light" type="submit">{{ __('Refresh AI review') }}</x-button>
                </form>
            </div>
        </x-card>
    @endif

    @if ($canRetryMatches)
        <x-card class="mb-5">
            <p class="text-sm text-hb-muted">{{ __('candidate.resume_upload.match_failed') }}</p>
            <form method="POST" action="{{ route('frontend.candidate.resume.job-matches', ['resumeId' => data_get($resume, 'id')]) }}" class="mt-4">
                @csrf
                <x-button type="submit">{{ __('candidate.resume_upload.compare_jobs') }}</x-button>
            </form>
        </x-card>
    @endif

    @forelse ($matches as $match)
        <x-card class="mb-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-hb-navy">{{ data_get($match, 'job.title', __('candidate.vacancies.title')) }}</h2>
                    @if (data_get($match, 'job.category.name'))
                        <p class="mt-1 text-sm text-hb-muted">{{ data_get($match, 'job.category.name') }}</p>
                    @endif
                </div>
                @if (data_get($match, 'score') !== null)
                    <x-badge variant="success">{{ __('candidate.resume_upload.match_score', ['score' => data_get($match, 'score')]) }}</x-badge>
                @else
                    <x-badge>{{ __('candidate.resume_upload.match_status_'.data_get($match, 'status', 'pending')) }}</x-badge>
                @endif
            </div>

            @if (data_get($match, 'status') === 'failed')
                <p class="mt-4 text-sm text-hb-error">{{ data_get($match, 'error') ?: __('candidate.resume_upload.match_failed') }}</p>
            @elseif (data_get($match, 'status') === 'pending')
                <p class="mt-4 text-sm text-hb-muted">{{ __('candidate.resume_upload.match_pending') }}</p>
            @else
                @if (filled(data_get($match, 'summary')))
                    <p class="mt-4 text-sm leading-6 text-hb-muted">{{ data_get($match, 'summary') }}</p>
                @endif
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-semibold text-hb-navy">{{ __('candidate.resume_upload.matched_skills') }}</h3>
                        <p class="mt-1 text-sm text-hb-muted">{{ collect(data_get($match, 'matched_skills', []))->pluck('skill')->implode(', ') ?: '—' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-hb-navy">{{ __('candidate.resume_upload.missing_skills') }}</h3>
                        <p class="mt-1 text-sm text-hb-muted">{{ collect(data_get($match, 'missing_skills', []))->implode(', ') ?: '—' }}</p>
                    </div>
                </div>
            @endif
        </x-card>
    @empty
        <x-card>
            <p class="text-sm text-hb-muted">{{ data_get($resume, 'ai_scan.status') === 'pending' ? __('candidate.resume_upload.match_pending') : __('candidate.resume_upload.no_matches') }}</p>
            @if (data_get($resume, 'ai_scan.status') !== 'pending')
                <form method="POST" action="{{ route('frontend.candidate.resume.job-matches', ['resumeId' => data_get($resume, 'id')]) }}" class="mt-4">
                    @csrf
                    <x-button type="submit">{{ __('candidate.resume_upload.compare_jobs') }}</x-button>
                </form>
            @endif
        </x-card>
    @endforelse
@endsection
