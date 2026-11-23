@extends('frontend.layouts.app')

@section('title', __('Job moderation'))

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8">
    <header class="mb-6">
        <h1 class="text-2xl font-bold text-hb-navy">{{ __('Job moderation') }}</h1>
        <p class="mt-1 text-sm text-hb-muted">{{ __('Review jobs submitted by companies.') }}</p>
    </header>
    <section class="rounded-2xl border border-hb-border bg-hb-surface p-8 text-center shadow-sm" role="status">
        <h2 class="text-lg font-semibold text-hb-navy">{{ __('No moderation data available') }}</h2>
        <p class="mt-2 text-sm text-hb-muted">{{ __('The connected platform does not provide a job moderation queue yet.') }}</p>
    </section>
</div>
@endsection
