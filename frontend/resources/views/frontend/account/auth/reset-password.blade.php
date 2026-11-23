@extends('frontend.layouts.public')

@section('title', __('Set New Password'))

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-hb-surface p-8 rounded-3xl shadow-sm border border-hb-border">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-tb-primary-soft text-tb-primary rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-hb-navy">Set new password</h2>
            <p class="text-sm text-hb-muted mt-1">Your new password must be different to previously used passwords.</p>
        </div>

        <!-- Form -->
        <form id="reset-password-form" class="space-y-4" autocomplete="off">
            @csrf
            <input type="hidden" name="token" value="{{ request()->get('token') }}">

            <div>
                <label class="block text-sm font-medium text-hb-text mb-1">Must be at least 8 characters</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required class="w-full rounded-xl border-hb-border border px-3.5 py-2.5 text-sm focus:ring-tb-primary focus:border-tb-primary pr-10" placeholder="••••••••">
                    <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-hb-muted hover:text-hb-muted">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-hb-text mb-1">Confirm password</label>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation" required class="w-full rounded-xl border-hb-border border px-3.5 py-2.5 text-sm focus:ring-tb-primary focus:border-tb-primary pr-10" placeholder="••••••••">
                    <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-hb-muted hover:text-hb-muted">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" id="reset-btn" class="w-full rounded-full bg-tb-primary px-6 py-3 text-sm font-bold text-white shadow-lg shadow-tb-primary/25 transition hover:bg-tb-primary-solid focus:outline-none">
                Reset Password
            </button>
            <p id="reset-msg" class="text-center text-sm mt-3 hidden"></p>
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
    function togglePassword(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    document.getElementById('reset-password-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('reset-btn');
        const msg = document.getElementById('reset-msg');
        
        btn.disabled = true;
        btn.textContent = 'Updating...';
        msg.classList.add('hidden');

        try {
            const response = await fetch('/api/auth/reset-password', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: new FormData(this)
            });

            if (response.ok) {
                msg.textContent = 'Password reset successfully! Redirecting...';
                msg.className = 'text-center text-sm mt-3 text-green-600 font-semibold';
                setTimeout(() => window.location.href = '/login', 2000);
            } else {
                throw new Error();
            }
        } catch {
            msg.textContent = 'Failed to reset password. Please try again.';
            msg.className = 'text-center text-sm mt-3 text-blue-600 font-semibold';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Reset Password';
            msg.classList.remove('hidden');
        }
    });
</script>
@endsection