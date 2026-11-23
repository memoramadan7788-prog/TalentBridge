@extends('frontend.layouts.auth')

@section('title', __('auth.login.page_title'))

@php
    $safeRedirect = $safeRedirect ?? null;
    $continuation = is_string($safeRedirect) && $safeRedirect !== ''
        && ! preg_match('#^/account/(login|register|admin-login)#', parse_url($safeRedirect, PHP_URL_PATH) ?: $safeRedirect);
@endphp

@section('auth-hide-pitch')

@section('content')
  <h1 class="text-3xl font-black tracking-tight text-[var(--hb-text)]">{{ __('auth.login.title') }}</h1>

  <form method="POST"
        action="{{ route('frontend.account.login.submit') }}"
        class="mt-6 space-y-5"
        data-login-form
        aria-label="{{ __('auth.login.form_aria') }}">
    @csrf
    @if ($continuation)
      <input type="hidden" name="redirect" value="{{ $safeRedirect }}">
    @endif

    <x-auth-field
      name="email"
      :label="__('auth.login.email_address')"
      type="email"
      icon="mail"
      :placeholder="__('auth.login.email_placeholder')"
      autocomplete="email"
      required
      autofocus
    />

    <x-auth-field
      name="password"
      :label="__('auth.login.password')"
      type="password"
      icon="lock"
      :placeholder="__('auth.login.password_placeholder')"
      autocomplete="current-password"
      required
    />

    <button
      type="submit"
      data-login-submit
      class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-tb-primary px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-tb-primary/25 transition hover:-translate-y-0.5 hover:bg-tb-primary-solid focus:outline-none focus-visible:ring-4 focus-visible:ring-tb-primary/30 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
    >
      <span data-login-idle>{{ __('auth.login.submit') }}</span>
      <span data-login-loading class="hidden items-center gap-2" aria-hidden="true">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"/></svg>
        {{ __('auth.login.submitting') }}
      </span>
    </button>
  </form>

  <div class="mt-6 space-y-3 text-center text-sm text-hb-muted dark:text-[var(--hb-muted)]">
    <p>
      {{ __('auth.login.new_here') }}
      <a href="{{ route('frontend.account.register') }}" class="font-bold text-tb-primary-text hover:underline">{{ __('auth.login.create_account') }}</a>
    </p>
  </div>
@endsection
