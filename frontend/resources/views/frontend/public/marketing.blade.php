@extends('frontend.layouts.public')

@section('title', __('Marketing & Services'))

@section('content')
<div class="max-w-6xl mx-auto px-4 py-12">
    <!-- Header Section -->
    <div class="text-center max-w-3xl mx-auto mb-16">
        <h1 class="text-4xl font-black tracking-tight text-neutral-900 sm:text-5xl">
            Flexible Hiring & Engagement Models
        </h1>
        <p class="mt-4 text-lg text-slate-600">
            Choose the right partnership model tailored to your company's growth and hiring velocity.
        </p>
    </div>

    <!-- 1. Engagement Models Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-sky-200 transition hover:shadow-md">
            <h3 class="text-xl font-bold text-neutral-900 mb-3">Contingent</h3>
            <p class="text-slate-600 text-sm mb-6">Pay only when you successfully hire. Ideal for standard and fast-paced openings.</p>
            <span class="inline-block bg-tb-primary-soft text-tb-primary-text text-xs font-bold px-3 py-1 rounded-full">Performance Based</span>
        </div>
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-sky-200 transition hover:shadow-md">
            <h3 class="text-xl font-bold text-neutral-900 mb-3">Retained</h3>
            <p class="text-slate-600 text-sm mb-6">Dedicated executive search and priority sourcing for critical leadership roles.</p>
            <span class="inline-block bg-tb-primary-soft text-tb-primary-text text-xs font-bold px-3 py-1 rounded-full">Dedicated Search</span>
        </div>
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-sky-200 transition hover:shadow-md">
            <h3 class="text-xl font-bold text-neutral-900 mb-3">Embedded</h3>
            <p class="text-slate-600 text-sm mb-6">Our recruiters integrate directly into your team as an extension of your HR department.</p>
            <span class="inline-block bg-tb-primary-soft text-tb-primary-text text-xs font-bold px-3 py-1 rounded-full">Full Integration</span>
        </div>
    </div>

    <!-- 2. Process Steps Section -->
    <div class="mb-20">
        <h2 class="text-2xl font-bold text-neutral-900 text-center mb-10">Our Hiring Process</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 rounded-xl bg-sky-50 border border-sky-50">
                <span class="text-tb-primary font-black text-xl mb-2 block">01</span>
                <h4 class="font-bold text-neutral-900 mb-1">Discovery</h4>
                <p class="text-sm text-slate-500">Understanding your technical and cultural requirements.</p>
            </div>
            <div class="p-6 rounded-xl bg-sky-50 border border-sky-50">
                <span class="text-tb-primary font-black text-xl mb-2 block">02</span>
                <h4 class="font-bold text-neutral-900 mb-1">Sourcing</h4>
                <p class="text-sm text-slate-500">Filtering top-tier vetted talent from our network.</p>
            </div>
            <div class="p-6 rounded-xl bg-sky-50 border border-sky-50">
                <span class="text-tb-primary font-black text-xl mb-2 block">03</span>
                <h4 class="font-bold text-neutral-900 mb-1">Interview</h4>
                <p class="text-sm text-slate-500">Coordinating technical and behavioral rounds.</p>
            </div>
            <div class="p-6 rounded-xl bg-sky-50 border border-sky-50">
                <span class="text-tb-primary font-black text-xl mb-2 block">04</span>
                <h4 class="font-bold text-neutral-900 mb-1">Onboarding</h4>
                <p class="text-sm text-slate-500">Seamless offer management and candidate integration.</p>
            </div>
        </div>
    </div>

    <!-- 3. Specialties Section -->
    <div class="mb-20 text-center">
        <h2 class="text-2xl font-bold text-neutral-900 mb-6">Our Specialties</h2>
        <div class="flex flex-wrap justify-center gap-3">
            <span class="px-4 py-2 bg-sky-50 text-slate-700 rounded-full text-sm font-semibold">Software Engineering</span>
            <span class="px-4 py-2 bg-sky-50 text-slate-700 rounded-full text-sm font-semibold">DevOps & Cloud</span>
            <span class="px-4 py-2 bg-sky-50 text-slate-700 rounded-full text-sm font-semibold">UI/UX Design</span>
            <span class="px-4 py-2 bg-sky-50 text-slate-700 rounded-full text-sm font-semibold">Product Management</span>
            <span class="px-4 py-2 bg-sky-50 text-slate-700 rounded-full text-sm font-semibold">Data Science</span>
        </div>
    </div>

    <!-- 4. CTA Form Section with Validation & UI States -->
    <div class="max-w-xl mx-auto bg-sky-50 p-8 rounded-3xl border border-sky-200">
        <h3 class="text-2xl font-bold text-neutral-900 mb-2 text-center">Ready to scale your team?</h3>
        <p class="text-sm text-slate-500 mb-6 text-center">Get in touch with our recruitment experts today.</p>

        <form id="cta-marketing-form" class="space-y-4" autocomplete="off">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full rounded-lg border-sky-200 border px-3 py-2 text-sm focus:ring-tb-primary focus:border-tb-primary" placeholder="John Doe">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Work Email</label>
                <input type="email" name="email" required class="w-full rounded-lg border-sky-200 border px-3 py-2 text-sm focus:ring-tb-primary focus:border-tb-primary" placeholder="john@company.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Engagement Model</label>
                <select name="model" class="w-full rounded-lg border-sky-200 border px-3 py-2 text-sm focus:ring-tb-primary focus:border-tb-primary">
                    <option value="contingent">Contingent</option>
                    <option value="retained">Retained</option>
                    <option value="embedded">Embedded</option>
                </select>
            </div>

            <button type="submit" id="submit-btn" class="w-full rounded-full bg-tb-primary px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-tb-primary/25 transition hover:bg-tb-primary-solid focus:outline-none">
                Submit Request
            </button>
            <p id="form-message" class="text-center text-sm mt-3 hidden"></p>
        </form>
    </div>
</div>

<!-- Script for API Integration & UI States -->
<script>
    document.getElementById('cta-marketing-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('submit-btn');
        const msg = document.getElementById('form-message');
        
        // UI State: Loading
        btn.disabled = true;
        btn.textContent = 'Submitting...';
        msg.classList.add('hidden');

        const formData = new FormData(this);

        try {
            const response = await fetch('/api/marketing-contact', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (response.ok) {
                msg.textContent = 'Thank you! Your request has been submitted successfully.';
                msg.className = 'text-center text-sm mt-3 text-green-600 font-semibold';
                this.reset();
            } else {
                throw new Error('Something went wrong.');
            }
        } catch (error) {
            msg.textContent = 'Failed to submit. Please try again.';
            msg.className = 'text-center text-sm mt-3 text-blue-600 font-semibold';
        } finally {
            // UI State: Reset Button
            btn.disabled = false;
            btn.textContent = 'Submit Request';
            msg.classList.remove('hidden');
        }
    });
</script>
@endsection