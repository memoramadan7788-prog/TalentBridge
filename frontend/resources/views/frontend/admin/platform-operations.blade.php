@extends('frontend.layouts.app')

@section('title', __('Platform operations'))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <header class="mb-6">
        <h1 class="text-2xl font-bold text-hb-navy">{{ __('Platform operations') }}</h1>
        <p class="mt-1 text-sm text-hb-muted">{{ __('Platform health and organization operations.') }}</p>
    </header>
    <section class="rounded-2xl border border-hb-border bg-hb-surface p-8 text-center shadow-sm" role="status">
        <h2 class="text-lg font-semibold text-hb-navy">{{ __('Operations data unavailable') }}</h2>
        <p class="mt-2 text-sm text-hb-muted">{{ __('Live platform operations data is not available from the connected service yet.') }}</p>
    </section>
</div>
@endsection
