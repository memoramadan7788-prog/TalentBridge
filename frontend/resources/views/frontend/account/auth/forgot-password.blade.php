@extends('frontend.layouts.public')

@section('title', __('Forgot Password'))

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-hb-surface p-8 rounded-3xl shadow-sm border border-hb-border">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-tb-primary-soft text-tb-primary rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-hb-navy">Forgot password?</h2>
            <p class="text-sm text-hb-muted mt-1">No worries, we'll send you reset instructions.</p>
        </div>

        <!-- Form -->
        <form id="forgot-password-form" class="space-y-4" autocomplete="off">
            @csrf
            <div>
                <label class="block text-sm font-medium text-hb-text mb-1">Work Email</label>
                <input type="email" name="email" required class="w-full rounded-xl border-hb-border border px-3.5 py-2.5 text-sm focus:ring-tb-primary focus:border-tb-primary" placeholder="enter your email">
            </div>

            <button type="submit" id="submit-btn" class="w-full rounded-full bg-tb-primary px-6 py-3 text-sm font-bold text-white shadow-lg shadow-tb-primary/25 transition hover:bg-tb-primary-solid focus:outline-none">
                Reset Password
            </button>
            <p id="form-msg" class="text-center text-sm mt-3 hidden"></p>
        </form>

        <!-- Back to Login -->
        <div class="text-center mt-6">
            <a href="/login" class="inline-flex items-center text-sm font-semibold text-hb-muted hover:text-tb-primary-text transition gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to log in
            </a>
        </div>
    </div>
</div>

<script>
    document.getElementById('forgot-password-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('submit-btn');
        const msg = document.getElementById('form-msg');
        
        btn.disabled = true;
        btn.textContent = 'Sending...';
        msg.classList.add('hidden');

        try {
            const response = await fetch('/api/auth/forgot-password', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: new FormData(this)
            });

            if (response.ok) {
                msg.textContent = 'Reset link sent! Please check your email.';
                msg.className = 'text-center text-sm mt-3 text-green-600 font-semibold';
            } else {
                throw new Error();
            }
        } catch {
            msg.textContent = 'Failed to send reset link. Please check the email entered.';
            msg.className = 'text-center text-sm mt-3 text-blue-600 font-semibold';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Reset Password';
            msg.classList.remove('hidden');
        }
    });
</script>
@endsection