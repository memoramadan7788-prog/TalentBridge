@extends('frontend.layouts.auth')

@section('title', __('auth.register.verification_page_title'))

@section('content')
    <p class="text-xs font-bold uppercase tracking-[0.18em] text-tb-primary-text">{{ __('auth.register.verification_eyebrow') }}</p>
    <h1 class="mt-2 text-3xl font-black tracking-tight">{{ __('auth.register.verification_title') }}</h1>

    <div class="mt-6 rounded-2xl border border-tb-primary/30 bg-tb-primary-soft p-5 text-sm leading-6 text-tb-primary-text">
        <p>{{ __('auth.register.verification_notice', ['email' => $email]) }}</p>
        <p class="mt-3">{{ __('auth.register.verification_instructions') }}</p>
    </div>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        <a
            href="{{ route('frontend.account.register') }}"
            class="inline-flex min-h-11 flex-1 items-center justify-center rounded-full border border-hb-border px-5 py-3 text-center text-sm font-bold text-hb-text transition hover:bg-hb-tint focus:outline-none focus-visible:ring-4 focus-visible:ring-tb-primary/30"
        >{{ __('auth.register.verification_return') }}</a>
        <a
            href="{{ route('frontend.contact') }}"
            class="inline-flex min-h-11 flex-1 items-center justify-center rounded-full bg-tb-primary px-5 py-3 text-center text-sm font-bold text-white transition hover:bg-tb-primary-solid focus:outline-none focus-visible:ring-4 focus-visible:ring-tb-primary/30"
        >{{ __('auth.register.verification_contact') }}</a>
    </div>
@endsection
