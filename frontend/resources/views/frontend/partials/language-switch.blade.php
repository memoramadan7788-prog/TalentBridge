@php
    use App\Support\Frontend\SafeInternalRedirect;

    $current = app()->getLocale() === 'ar' ? 'ar' : 'en';
    // Preserve the active page; LocaleController re-validates this target.
    $redirect = SafeInternalRedirect::sanitize(request()->getRequestUri());
@endphp

<div
    class="inline-flex min-h-10 shrink-0 items-center gap-0.5 rounded-lg border border-hb-border bg-hb-surface p-px text-xs font-semibold"
    role="group"
    aria-label="{{ __('common.language') }}"
    data-language-switch
>
    <a
        href="{{ route('frontend.locale.switch', ['locale' => 'en', 'redirect' => $redirect]) }}"
        class="inline-flex h-10 min-w-10 items-center justify-center rounded-md px-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tb-primary/40 {{ $current === 'en' ? 'bg-hb-info-soft text-tb-primary-text' : 'text-hb-muted hover:text-hb-text' }}"
        hreflang="en"
        lang="en"
        aria-current="{{ $current === 'en' ? 'true' : 'false' }}"
        aria-label="{{ __('common.language_english') }}"
        data-lang-option="en"
    >
        EN
    </a>
    <a
        href="{{ route('frontend.locale.switch', ['locale' => 'ar', 'redirect' => $redirect]) }}"
        class="inline-flex h-10 min-w-10 items-center justify-center rounded-md px-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tb-primary/40 {{ $current === 'ar' ? 'bg-hb-info-soft text-tb-primary-text' : 'text-hb-muted hover:text-hb-text' }}"
        hreflang="ar"
        lang="ar"
        aria-current="{{ $current === 'ar' ? 'true' : 'false' }}"
        aria-label="{{ __('common.language_arabic') }}"
        data-lang-option="ar"
    >
        AR
    </a>
</div>
