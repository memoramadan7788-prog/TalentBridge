@extends('frontend.layouts.app')

@section('title', __('company.talent_pool.page_title'))

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-muted">{{ __('company.talent_pool.breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.talent_pool.title') }}</h1>
        <p class="mt-2 text-sm text-hb-muted">{{ __('company.talent_pool.subtitle') }}</p>
    </div>
@endsection

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @if ($loadError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $loadError }}</div>
    @endif

    <x-card>
        <x-slot:header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('company.talent_pool.title') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">Consent-based prospects maintained by your company.</p>
                </div>
                <a href="#add-prospect" class="rounded-lg bg-tb-primary-solid px-4 py-2 text-sm font-semibold text-white hover:bg-tb-primary-dark">{{ __('company.talent_pool.add_prospect') }}</a>
            </div>
        </x-slot:header>

        @if (count($items) === 0)
            <p class="py-8 text-center text-sm text-hb-muted">{{ __('company.talent_pool.empty') }}</p>
        @else
            <div class="space-y-4">
                @foreach ($items as $item)
                    <article class="rounded-xl border border-hb-border bg-hb-tint/40 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-hb-navy">{{ data_get($item, 'name') }}</h3>
                                <p class="mt-1 text-sm text-hb-muted">{{ data_get($item, 'email') ?: 'No email provided' }}</p>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                    @if (data_get($item, 'source'))
                                        <span class="rounded-full bg-white px-2.5 py-1 text-hb-muted">Source: {{ data_get($item, 'source') }}</span>
                                    @endif
                                    @if (data_get($item, 'relationship'))
                                        <span class="rounded-full bg-white px-2.5 py-1 text-hb-muted">Relationship: {{ data_get($item, 'relationship') }}</span>
                                    @endif
                                    @foreach ((array) data_get($item, 'tags', []) as $tag)
                                        <span class="rounded-full bg-blue-100 px-2.5 py-1 text-blue-800">#{{ data_get($tag, 'name', $tag) }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <form method="POST" action="{{ route('frontend.company.talent-pool.destroy', ['prospectId' => data_get($item, 'id')]) }}" onsubmit="return confirm('Remove this prospect from the talent pool?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
                            </form>
                        </div>

                        <details class="mt-4 rounded-lg bg-white p-3">
                            <summary class="cursor-pointer text-sm font-semibold text-hb-navy">Edit prospect</summary>
                            <form method="POST" action="{{ route('frontend.company.talent-pool.update', ['prospectId' => data_get($item, 'id')]) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                                @csrf
                                @method('PATCH')
                                <input name="name" value="{{ data_get($item, 'name') }}" required class="rounded-lg border border-hb-border px-3 py-2 text-sm" placeholder="Name">
                                <input name="email" value="{{ data_get($item, 'email') }}" type="email" class="rounded-lg border border-hb-border px-3 py-2 text-sm" placeholder="Email">
                                <select name="source" class="rounded-lg border border-hb-border px-3 py-2 text-sm">
                                    <option value="">Source</option>
                                    @foreach (['platform', 'agency', 'referral', 'talent_pool', 'direct'] as $source)
                                        <option value="{{ $source }}" @selected(data_get($item, 'source') === $source)>{{ str_replace('_', ' ', ucfirst($source)) }}</option>
                                    @endforeach
                                </select>
                                <select name="relationship" class="rounded-lg border border-hb-border px-3 py-2 text-sm">
                                    <option value="">Relationship</option>
                                    @foreach (['former_applicant', 'referral', 'silver_medalist', 'sourced', 'other'] as $relationship)
                                        <option value="{{ $relationship }}" @selected(data_get($item, 'relationship') === $relationship)>{{ str_replace('_', ' ', ucfirst($relationship)) }}</option>
                                    @endforeach
                                </select>
                                <input name="tags" value="{{ collect((array) data_get($item, 'tags', []))->map(fn ($tag) => data_get($tag, 'name', $tag))->implode(', ') }}" class="rounded-lg border border-hb-border px-3 py-2 text-sm md:col-span-2" placeholder="Tags, separated by commas">
                                <button type="submit" class="w-fit rounded-lg bg-tb-primary-solid px-4 py-2 text-sm font-semibold text-white">Save changes</button>
                            </form>
                        </details>

                        <div class="mt-4 border-t border-hb-border pt-3">
                            <p class="text-sm font-semibold text-hb-navy">Manage tags</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ((array) data_get($item, 'tags', []) as $tag)
                                    <form method="POST" action="{{ route('frontend.company.talent-pool.tags.update', ['prospectId' => data_get($item, 'id'), 'tagId' => data_get($tag, 'id')]) }}" class="flex items-center gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <input name="name" value="{{ data_get($tag, 'name', $tag) }}" class="w-28 rounded-lg border border-hb-border px-2 py-1 text-xs">
                                        <button type="submit" class="text-xs font-semibold text-tb-primary-text">Save</button>
                                    </form>
                                    <form method="POST" action="{{ route('frontend.company.talent-pool.tags.destroy', ['prospectId' => data_get($item, 'id'), 'tagId' => data_get($tag, 'id')]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-700">Remove</button>
                                    </form>
                                @endforeach
                            </div>
                            <form method="POST" action="{{ route('frontend.company.talent-pool.tags.store', ['prospectId' => data_get($item, 'id')]) }}" class="mt-3 flex gap-2">
                                @csrf
                                <input name="name" required class="rounded-lg border border-hb-border px-3 py-2 text-sm" placeholder="New tag">
                                <button type="submit" class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold text-hb-navy">Add tag</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </x-card>

    <x-card id="add-prospect">
        <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('company.talent_pool.add_modal_title') }}</h2></x-slot:header>
        <form method="POST" action="{{ route('frontend.company.talent-pool.store') }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            <input name="name" value="{{ old('name') }}" required class="rounded-lg border border-hb-border px-3 py-2 text-sm" placeholder="Prospect name">
            <input name="email" value="{{ old('email') }}" type="email" class="rounded-lg border border-hb-border px-3 py-2 text-sm" placeholder="Email">
            <select name="source" class="rounded-lg border border-hb-border px-3 py-2 text-sm">
                <option value="">Source</option>
                @foreach (['platform', 'agency', 'referral', 'talent_pool', 'direct'] as $source)
                    <option value="{{ $source }}" @selected(old('source') === $source)>{{ str_replace('_', ' ', ucfirst($source)) }}</option>
                @endforeach
            </select>
            <select name="relationship" class="rounded-lg border border-hb-border px-3 py-2 text-sm">
                <option value="">Relationship</option>
                @foreach (['former_applicant', 'referral', 'silver_medalist', 'sourced', 'other'] as $relationship)
                    <option value="{{ $relationship }}" @selected(old('relationship') === $relationship)>{{ str_replace('_', ' ', ucfirst($relationship)) }}</option>
                @endforeach
            </select>
            <input name="tags" value="{{ old('tags') }}" class="rounded-lg border border-hb-border px-3 py-2 text-sm md:col-span-2" placeholder="Tags, separated by commas">
            <label class="text-sm text-hb-muted md:col-span-2"><input type="datetime-local" name="consent_at" value="{{ old('consent_at') }}" class="mr-2 rounded-lg border border-hb-border px-3 py-2">Consent recorded at (optional)</label>
            <button type="submit" class="w-fit rounded-lg bg-tb-primary-solid px-5 py-2 text-sm font-semibold text-white">{{ __('company.talent_pool.add_confirm') }}</button>
        </form>
    </x-card>
</div>
@endsection
