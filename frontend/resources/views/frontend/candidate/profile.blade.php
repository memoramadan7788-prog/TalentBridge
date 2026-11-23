@extends('frontend.layouts.app')

@section('title', __('candidate.profile.title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.profile.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.profile.intro') }}</p>
@endsection

@section('page_actions')
    <x-button variant="primary" size="md" :href="route('frontend.candidate.profile.update')">{{ __('candidate.profile.edit_profile') }}</x-button>
    <x-button variant="light" size="md" :href="route('frontend.candidate.resume.upload')">{{ __('candidate.profile.upload_resume') }}</x-button>
@endsection

@section('content')
@php
    $profile = is_array($profile ?? null) ? $profile : [];
    $name = data_get($profile, 'name');
    $email = data_get($profile, 'email');
    $phone = data_get($profile, 'phone');
    $location = data_get($profile, 'location');
    $headline = data_get($profile, 'headline');
    $bio = data_get($profile, 'bio');
    $years = data_get($profile, 'years_experience');
    $hasNoExperience = (bool) data_get($profile, 'has_no_experience', false);
    $experiences = \App\Support\Rows::from(data_get($profile, 'experiences', data_get($profile, 'candidate_experiences', [])));
    $educations = \App\Support\Rows::from(data_get($profile, 'educations', data_get($profile, 'candidate_educations', [])));
    $skills = \App\Support\Rows::from(data_get($profile, 'skills', data_get($profile, 'candidate_skills', [])));
    $cvs = \App\Support\Rows::from(data_get($profile, 'cvs', data_get($profile, 'candidate_cvs', [])));
    $hasPersonal = filled($name) || filled($email) || filled($phone) || filled($location);
    $hasSummary = filled($headline) || filled($bio) || $years !== null || $hasNoExperience;
@endphp

<div data-page-tabs>
    @include('frontend.partials.page-tabs', [
        'group' => 'profile',
        'ariaLabel' => __('candidate.profile.title'),
        'tabs' => [
            'overview' => __('candidate.profile.section_personal'),
            'experience' => __('candidate.profile.section_experience'),
            'education' => __('candidate.profile.section_education'),
            'skills' => __('candidate.profile.section_skills'),
            'resume' => __('candidate.profile.section_resume'),
            'account' => __('candidate.profile.section_danger'),
        ],
    ])

    <section id="profile-panel-overview" role="tabpanel" aria-labelledby="profile-tab-overview" aria-hidden="false" tabindex="0" data-page-panel="overview" class="space-y-6">
{{-- Profile picture (preview-only until Backend avatar APIs exist) --}}
<div class="mb-6">
    @include('frontend.partials.profile-avatar', [
        'profile' => $profile,
        'avatarName' => $name ?: (auth()->user()?->name ?? ''),
    ])
</div>

{{-- Personal Information --}}
<x-card>
    <x-slot:header>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.profile.section_personal') }}</h2>
            <x-button variant="light" size="sm" :href="route('frontend.candidate.profile.update')">{{ __('candidate.profile.edit_profile') }}</x-button>
        </div>
    </x-slot:header>
    @if ($hasPersonal)
        <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
            @if (filled($name))
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.profile.name') }}</dt>
                    <dd class="mt-1 text-sm text-hb-text">{{ $name }}</dd>
                </div>
            @endif
            @if (filled($email))
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.profile.email') }}</dt>
                    <dd class="mt-1 text-sm text-hb-text">{{ $email }}</dd>
                </div>
            @endif
            @if (filled($phone))
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.profile.phone') }}</dt>
                    <dd class="mt-1 text-sm text-hb-text">{{ $phone }}</dd>
                </div>
            @endif
            @if (filled($location))
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.profile.location') }}</dt>
                    <dd class="mt-1 text-sm text-hb-text">{{ $location }}</dd>
                </div>
            @endif
        </dl>
    @else
        <p class="text-sm text-hb-muted">{{ __('candidate.profile.empty_personal') }}</p>
        <div class="mt-3">
            <x-button variant="light" size="sm" :href="route('frontend.candidate.profile.update')">{{ __('candidate.profile.edit_profile') }}</x-button>
        </div>
    @endif
</x-card>

{{-- Professional Summary --}}
<x-card class="mt-6">
    <x-slot:header>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.profile.section_summary') }}</h2>
            <x-button variant="light" size="sm" :href="route('frontend.candidate.profile.update')">{{ __('candidate.profile.edit_profile') }}</x-button>
        </div>
    </x-slot:header>
    @if ($hasSummary)
        <div class="space-y-3">
            @if (filled($headline))
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.profile.headline') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-hb-navy">{{ $headline }}</dd>
                </div>
            @endif
            @if (filled($bio))
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.profile.about_you') }}</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm leading-6 text-hb-text">{{ $bio }}</dd>
                </div>
            @endif
            @if ($years !== null && $years !== '')
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.profile.years_experience') }}</dt>
                    <dd class="mt-1 text-sm text-hb-text">{{ $years }}</dd>
                </div>
            @endif
            @if ($hasNoExperience)
                <p class="text-sm text-hb-muted">{{ __('candidate.profile.no_experience_flag') }}</p>
            @endif
        </div>
    @else
        <p class="text-sm text-hb-muted">{{ __('candidate.profile.empty_summary') }}</p>
        <div class="mt-3">
            <x-button variant="light" size="sm" :href="route('frontend.candidate.profile.update')">{{ __('candidate.profile.edit_profile') }}</x-button>
        </div>
    @endif
</x-card>

{{-- Work Experience --}}
    </section>
    <section id="profile-panel-experience" role="tabpanel" aria-labelledby="profile-tab-experience" aria-hidden="true" tabindex="0" data-page-panel="experience" class="hidden space-y-6">
<x-card class="mt-6">
    <x-slot:header>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.profile.section_experience') }}</h2>
            <x-button variant="light" size="sm" :href="route('frontend.candidate.experience.add')">{{ __('candidate.profile.add_experience') }}</x-button>
        </div>
    </x-slot:header>
    @forelse ($experiences as $row)
        <div class="border-b border-hb-border py-4 last:border-0 last:pb-0 first:pt-0">
            <p class="text-sm font-semibold text-hb-navy">{{ data_get($row, 'job_title', '—') }}</p>
            <p class="mt-0.5 text-sm text-hb-text">{{ data_get($row, 'company_name', '—') }}</p>
            <p class="mt-1 text-xs text-hb-muted">
                {{ \App\Support\Rows::date(data_get($row, 'start_date'), 'Y-m') }}
                –
                @if (data_get($row, 'is_current'))
                    {{ __('candidate.profile.present') }}
                @else
                    {{ \App\Support\Rows::date(data_get($row, 'end_date'), 'Y-m') }}
                @endif
            </p>
            @if (filled(data_get($row, 'description')))
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-hb-text">{{ data_get($row, 'description') }}</p>
            @endif
        </div>
    @empty
        <p class="text-sm text-hb-muted">{{ __('candidate.profile.empty_experience') }}</p>
        <div class="mt-3">
            <x-button variant="light" size="sm" :href="route('frontend.candidate.experience.add')">{{ __('candidate.profile.add_experience') }}</x-button>
        </div>
    @endforelse
</x-card>

{{-- Education --}}
    </section>
    <section id="profile-panel-education" role="tabpanel" aria-labelledby="profile-tab-education" aria-hidden="true" tabindex="0" data-page-panel="education" class="hidden space-y-6">
<x-card class="mt-6">
    <x-slot:header>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.profile.section_education') }}</h2>
            <x-button variant="light" size="sm" :href="route('frontend.candidate.education.add')">{{ __('candidate.profile.add_education') }}</x-button>
        </div>
    </x-slot:header>
    @forelse ($educations as $row)
        <div class="border-b border-hb-border py-4 last:border-0 last:pb-0 first:pt-0">
            <p class="text-sm font-semibold text-hb-navy">{{ data_get($row, 'degree') ?: data_get($row, 'field_of_study', '—') }}</p>
            <p class="mt-0.5 text-sm text-hb-text">{{ data_get($row, 'institution', '—') }}</p>
            <p class="mt-1 text-xs text-hb-muted">
                @if (data_get($row, 'field_of_study') && data_get($row, 'degree'))
                    {{ data_get($row, 'field_of_study') }} ·
                @endif
                @if (data_get($row, 'graduation_year'))
                    {{ data_get($row, 'graduation_year') }}
                @endif
                @if (data_get($row, 'place'))
                    · {{ data_get($row, 'place') }}
                @endif
            </p>
        </div>
    @empty
        <p class="text-sm text-hb-muted">{{ __('candidate.profile.empty_education') }}</p>
        <div class="mt-3">
            <x-button variant="light" size="sm" :href="route('frontend.candidate.education.add')">{{ __('candidate.profile.add_education') }}</x-button>
        </div>
    @endforelse
</x-card>

{{-- Skills --}}
    </section>
    <section id="profile-panel-skills" role="tabpanel" aria-labelledby="profile-tab-skills" aria-hidden="true" tabindex="0" data-page-panel="skills" class="hidden space-y-6">
<x-card class="mt-6">
    <x-slot:header>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.profile.section_skills') }}</h2>
            <x-button variant="light" size="sm" :href="route('frontend.candidate.skills.add')">{{ __('candidate.profile.manage_skills') }}</x-button>
        </div>
    </x-slot:header>
    @if (! empty($skills))
        <div class="flex flex-wrap gap-2" data-profile-skills-list>
            @foreach ($skills as $skill)
                @php
                    $label = is_array($skill)
                        ? (data_get($skill, 'name') ?? data_get($skill, 'skill.name') ?? data_get($skill, 'label'))
                        : (string) $skill;
                    $level = is_array($skill) ? data_get($skill, 'level') : null;
                @endphp
                @if (filled($label))
                    <x-badge>
                        {{ $label }}
                        @if (filled($level))
                            <span class="opacity-80">· {{ $level }}</span>
                        @endif
                    </x-badge>
                @endif
            @endforeach
        </div>
    @else
        <p class="text-sm text-hb-muted" data-profile-skills-empty>{{ __('candidate.profile.empty_skills') }}</p>
        <div class="mt-3">
            <x-button variant="light" size="sm" :href="route('frontend.candidate.skills.add')">{{ __('candidate.profile.add_skill') }}</x-button>
        </div>
    @endif
</x-card>

{{-- Resume --}}
    </section>
    <section id="profile-panel-resume" role="tabpanel" aria-labelledby="profile-tab-resume" aria-hidden="true" tabindex="0" data-page-panel="resume" class="hidden space-y-6">
<x-card class="mt-6">
    <x-slot:header>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.profile.section_resume') }}</h2>
            <x-button variant="light" size="sm" :href="route('frontend.candidate.resume.upload')">{{ __('candidate.profile.upload_resume') }}</x-button>
        </div>
    </x-slot:header>
    @forelse ($cvs as $row)
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-hb-border py-3 last:border-0">
            <div>
                <p class="text-sm font-medium text-hb-navy">
                    {{ data_get($row, 'original_filename', __('candidate.profile.current_resume')) }}
                    @if (data_get($row, 'is_default'))
                        <x-badge variant="success" class="ms-2 align-middle">{{ __('candidate.profile.active_resume') }}</x-badge>
                    @endif
                </p>
                <p class="text-xs text-hb-muted">
                    {{ __('candidate.profile.updated', ['date' => \App\Support\Rows::date(data_get($row, 'uploaded_at'))]) }}
                    @if (data_get($row, 'size_bytes'))
                        · {{ number_format(((int) data_get($row, 'size_bytes')) / 1024, 0) }} KB
                    @endif
                </p>
                @php
                    $scanStatus = data_get($row, 'ai_scan.status');
                    $scanData = data_get($row, 'ai_scan.extracted_data', []);
                @endphp
                <p class="mt-2 text-xs text-hb-muted" role="status">
                    {{ __('candidate.resume_upload.scan_status') }}:
                    <span class="font-semibold text-hb-navy">{{ filled($scanStatus) ? __('candidate.resume_upload.scan_status_'.$scanStatus) : __('candidate.resume_upload.scan_status_not_started') }}</span>
                    @if (data_get($row, 'ai_scan.score') !== null)
                        · {{ __('candidate.resume_upload.scan_score', ['score' => data_get($row, 'ai_scan.score')]) }}
                    @endif
                </p>
                @if ($scanStatus === 'ok')
                    @if (filled(data_get($row, 'ai_scan.summary')))
                        <p class="mt-2 max-w-2xl text-sm text-hb-muted">{{ data_get($row, 'ai_scan.summary') }}</p>
                    @endif
                    @if (count((array) data_get($scanData, 'skills', [])))
                        <p class="mt-2 text-xs text-hb-muted">
                            <span class="font-semibold text-hb-navy">{{ __('candidate.resume_upload.extracted_skills') }}:</span>
                            {{ implode('، ', (array) data_get($scanData, 'skills', [])) }}
                        </p>
                    @endif
                    <details class="mt-3 max-w-2xl rounded-lg border border-hb-border px-3 py-2">
                        <summary class="cursor-pointer text-xs font-semibold text-tb-primary-text">{{ __('candidate.resume_upload.view_extracted_data') }}</summary>
                        <div class="mt-3 space-y-3 text-sm">
                            @foreach (['experience', 'education', 'certifications', 'languages'] as $field)
                                @if (count((array) data_get($scanData, $field, [])))
                                    <div>
                                        <h3 class="text-xs font-semibold text-hb-navy">{{ __('candidate.resume_upload.data_'.$field) }}</h3>
                                        <ul class="mt-1 list-inside list-disc text-hb-muted">
                                            @foreach ((array) data_get($scanData, $field, []) as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                            @if (count((array) data_get($scanData, 'suggested_jobs', [])))
                                <div>
                                    <h3 class="text-xs font-semibold text-hb-navy">{{ __('candidate.resume_upload.data_suggested_jobs') }}</h3>
                                    <p class="mt-1 text-hb-muted">{{ collect(data_get($scanData, 'suggested_jobs', []))->pluck('title')->implode('، ') }}</p>
                                </div>
                            @endif
                        </div>
                    </details>
                @elseif ($scanStatus === 'failed' && filled(data_get($row, 'ai_scan.error')))
                    <p class="mt-2 text-xs text-hb-error">{{ data_get($row, 'ai_scan.error') }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <x-button variant="primary" size="sm" :href="route('frontend.candidate.resume.job-matches.show', ['resumeId' => data_get($row, 'id')])">
                    {{ __('candidate.resume_upload.view_matches') }}
                </x-button>
                <form method="POST" action="{{ route('frontend.candidate.resume.job-matches', ['resumeId' => data_get($row, 'id')]) }}">
                    @csrf
                    <x-button type="submit" size="sm" variant="light" :disabled="$scanStatus === 'pending'">
                        {{ $scanStatus === 'ok' ? __('candidate.resume_upload.compare_jobs') : __('candidate.resume_upload.start_scan') }}
                    </x-button>
                </form>
                <x-button variant="light" size="sm" :href="route('frontend.candidate.resume.download', ['resumeId' => data_get($row, 'id')])">{{ __('Download') }}</x-button>
                <form method="POST" action="{{ route('frontend.candidate.resume.delete', ['resumeId' => data_get($row, 'id')]) }}" onsubmit="return confirm('{{ __('Delete this resume?') }}')">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" size="sm" variant="destructive">{{ __('Delete') }}</x-button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-sm text-hb-muted">{{ __('candidate.profile.empty_resume') }}</p>
        <div class="mt-3">
            <x-button variant="light" size="sm" :href="route('frontend.candidate.resume.upload')">{{ __('candidate.profile.upload_resume') }}</x-button>
        </div>
    @endforelse
</x-card>

{{-- Account deletion (not professional profile content; password lives in account menu) --}}
    </section>
    <section id="profile-panel-account" role="tabpanel" aria-labelledby="profile-tab-account" aria-hidden="true" tabindex="0" data-page-panel="account" class="hidden space-y-6">
<x-card class="mt-6">
    <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.profile.section_danger') }}</h2>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.profile.danger_body') }}</p>
    <form method="POST" action="{{ route('frontend.candidate.delete-account') }}" class="mt-4" onsubmit="return confirm('{{ __('Delete your account permanently?') }}')">
        @csrf
        @method('DELETE')
        <x-button type="submit" variant="destructive">{{ __('Delete account') }}</x-button>
    </form>
</x-card>
    </section>
</div>
@endsection
