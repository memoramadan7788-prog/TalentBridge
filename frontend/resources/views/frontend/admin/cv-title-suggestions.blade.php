@extends('frontend.layouts.app')

@section('title', __('CV job title approvals'))

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8">
    <header class="mb-6">
        <h1 class="text-2xl font-bold text-hb-navy">{{ __('CV job title approvals') }}</h1>
        <p class="mt-1 text-sm text-hb-muted">{{ __('Review AI suggested titles before they are added to the job title catalog.') }}</p>
    </header>
    @if ($loadError)<x-alert variant="info" class="mb-4">{{ $loadError }}</x-alert>@endif
    @forelse ($suggestions as $suggestion)
        <x-card class="mb-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-hb-navy">{{ data_get($suggestion, 'title') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ data_get($suggestion, 'candidate') }} · {{ data_get($suggestion, 'filename') }}</p>
                    @if (data_get($suggestion, 'confidence') !== null)<p class="mt-1 text-xs text-hb-muted">{{ __('AI confidence') }}: {{ data_get($suggestion, 'confidence') }}%</p>@endif
                </div>
                <div class="flex gap-2">
                    @foreach (['approve' => __('Approve and add'), 'reject' => __('Reject')] as $decision => $label)
                        <form method="POST" action="{{ route('frontend.admin.cv-title-suggestions.review', data_get($suggestion, 'id')) }}">
                            @csrf<input type="hidden" name="decision" value="{{ $decision }}">
                            <x-button type="submit" :variant="$decision === 'approve' ? 'primary' : 'light'">{{ $label }}</x-button>
                        </form>
                    @endforeach
                </div>
            </div>
        </x-card>
    @empty
        <x-card><p class="text-sm text-hb-muted">{{ __('No job title suggestions are waiting for approval.') }}</p></x-card>
    @endforelse
</div>
@endsection
