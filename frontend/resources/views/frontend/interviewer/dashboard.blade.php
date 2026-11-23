@extends('frontend.layouts.app')

@section('title', __('Interviewer dashboard'))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <header class="mb-6">
        <h1 class="text-2xl font-bold text-hb-navy">{{ __('Interviewer dashboard') }}</h1>
        <p class="mt-1 text-sm text-hb-muted">{{ __('Your assigned interviews and evaluation tasks.') }}</p>
    </header>
    <section class="rounded-2xl border border-hb-border bg-hb-surface p-8 text-center shadow-sm" role="status">
        <h2 class="text-lg font-semibold text-hb-navy">{{ __('No interview data available') }}</h2>
        <p class="mt-2 text-sm text-hb-muted">{{ __('Assigned interviews and preparation tasks are not available from the connected service yet.') }}</p>
    </section>
</div>
@endsection
