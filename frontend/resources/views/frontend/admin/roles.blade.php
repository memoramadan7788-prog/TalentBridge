@extends('frontend.layouts.app')

@section('title', __('Roles & permissions'))

@section('page_header')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Roles & permissions') }}</h1>
            <p class="mt-1 text-sm text-hb-muted">{{ __('Role catalog and access assignment from the Backend API.') }}</p>
        </div>
        <x-button variant="light" :href="route('frontend.admin.users.index')">{{ __('Back to users') }}</x-button>
    </div>
@endsection

@section('content')
@php
    $items = \App\Support\Rows::from($roles ?? []);
    $users = collect($users ?? [])->filter(fn ($user) => is_array($user))->values();
    $assignments = $users->flatMap(function (array $user) {
        return collect(data_get($user, 'roles', []))->filter(fn ($role) => is_array($role))->map(function (array $role) use ($user) {
            return array_merge($role, [
                'user_id' => data_get($user, 'id'),
                'user_name' => data_get($user, 'name'),
                'user_email' => data_get($user, 'email'),
            ]);
        });
    })->values();
@endphp

@if (session('success'))
    <x-alert variant="success" class="mb-4" role="status">{{ session('success') }}</x-alert>
@endif

@if ($errors->any())
    <x-alert variant="error" class="mb-4" role="alert">
        <ul class="list-disc ps-5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

@if (! empty($loadError))
    <x-alert variant="error" class="mb-6" role="alert">
        {{ __('Could not load roles and permissions.') }}
        <span class="opacity-80">{{ trim($loadError) }}</span>
    </x-alert>
@endif

<p class="hb-note mb-6">{{ __('Role changes are sent to the Backend API. Backend authorization and role-scope validation remain authoritative.') }}</p>

<div class="mb-8 grid items-start gap-6 lg:grid-cols-5">
    <section class="hb-card lg:col-span-2" aria-labelledby="assign-role-heading">
        <h2 id="assign-role-heading" class="hb-card__title">{{ __('Assign role') }}</h2>
        <p class="hb-card__hint">{{ __('Company roles require a company ID. Platform roles must leave it empty.') }}</p>
        @if (! empty($usersLoadError))
            <p class="mt-4 text-sm text-hb-error" role="alert">{{ $usersLoadError }}</p>
        @elseif ($users->isEmpty() || empty($items))
            <p class="mt-4 text-sm text-hb-muted">{{ __('Users or roles are unavailable.') }}</p>
        @else
            <form method="POST" action="{{ route('frontend.admin.roles.assign') }}" class="mt-5 grid gap-4">
                @csrf
                <div>
                    <label for="role-user" class="hb-label">{{ __('User') }}</label>
                    <select id="role-user" name="user_id" required class="hb-input">
                        <option value="">{{ __('Choose a user') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ data_get($user, 'id') }}" @selected((string) old('user_id') === (string) data_get($user, 'id'))>
                                {{ data_get($user, 'name', '—') }} — {{ data_get($user, 'email', '—') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    <div>
                        <label for="role-id" class="hb-label">{{ __('Role') }}</label>
                        <select id="role-id" name="role_id" required class="hb-input">
                            <option value="">{{ __('Choose a role') }}</option>
                            @foreach ($items as $role)
                                <option value="{{ data_get($role, 'id') }}" @selected((string) old('role_id') === (string) data_get($role, 'id'))>
                                    {{ data_get($role, 'name', '—') }} ({{ data_get($role, 'scope', '—') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="role-company" class="hb-label">{{ __('Company ID (company roles only)') }}</label>
                        <input id="role-company" name="company_id" type="number" min="1" value="{{ old('company_id') }}" class="hb-input" placeholder="{{ __('Optional for platform roles') }}">
                    </div>
                </div>
                <div>
                    <x-button type="submit" variant="primary">{{ __('Assign role') }}</x-button>
                </div>
            </form>
        @endif
    </section>

    <section class="hb-card lg:col-span-3" aria-labelledby="assigned-roles-heading">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 id="assigned-roles-heading" class="hb-card__title">{{ __('Current assignments') }}</h2>
            @if ($assignments->isNotEmpty())
                <span class="text-xs font-medium text-hb-muted">{{ $assignments->count() }}</span>
            @endif
        </div>
        <p class="hb-card__hint">{{ __('Remove a role only when it is no longer needed.') }}</p>
        @if ($assignments->isEmpty())
            <p class="mt-5 text-sm text-hb-muted">{{ __('No role assignments were returned by the API.') }}</p>
        @else
            <div class="mt-5 max-h-[28rem] space-y-2 overflow-y-auto pe-1">
                @foreach ($assignments as $assignment)
                    @php
                        $assignmentScope = (string) ($assignment['scope'] ?? '');
                        $assignmentVariant = $assignmentScope === 'platform' ? 'info' : ($assignmentScope === 'company' ? 'pending' : 'neutral');
                    @endphp
                    <x-list-row :name="$assignment['user_name'] ?: $assignment['user_email']" :meta="$assignment['user_email'] ?? null">
                        <x-slot:chips>
                            <x-badge variant="info">{{ $assignment['name'] ?? '—' }}</x-badge>
                            @if ($assignmentScope !== '')
                                <x-badge :variant="$assignmentVariant">{{ ucfirst($assignmentScope) }}</x-badge>
                            @endif
                            @if (! empty($assignment['company_id']))
                                <x-badge>company #{{ $assignment['company_id'] }}</x-badge>
                            @endif
                        </x-slot:chips>
                        <x-slot:actions>
                            <form method="POST" action="{{ route('frontend.admin.roles.remove', ['userId' => $assignment['user_id'], 'roleId' => $assignment['id']]) }}" onsubmit="return confirm('{{ __('Remove this role?') }}')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="ghost" size="sm" class="text-hb-error hover:bg-hb-error-soft">{{ __('Remove') }}</x-button>
                            </form>
                        </x-slot:actions>
                    </x-list-row>
                @endforeach
            </div>
        @endif
    </section>
</div>

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('Role') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Scope') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Description') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Permissions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        @php
            $scope = (string) data_get($row, 'scope', '');
            $permissions = collect(data_get($row, 'permissions', []))->filter()->values();
            $scopeVariant = $scope === 'platform' ? 'info' : ($scope === 'company' ? 'pending' : 'neutral');
        @endphp
        <tr>
            <td class="px-4 py-3">
                <p class="font-semibold text-hb-navy">{{ data_get($row, 'name', '—') }}</p>
                @if (data_get($row, 'id'))
                    <p class="text-xs text-hb-muted">#{{ data_get($row, 'id') }}</p>
                @endif
            </td>
            <td class="px-4 py-3">
                @if ($scope !== '')
                    <x-badge :variant="$scopeVariant" :dot="true">{{ ucfirst($scope) }}</x-badge>
                @else
                    <span class="text-hb-muted">—</span>
                @endif
            </td>
            <td class="px-4 py-3 max-w-[280px] text-sm text-hb-muted">{{ data_get($row, 'description') ?: '—' }}</td>
            <td class="px-4 py-3">
                @if ($permissions->isEmpty())
                    <span class="text-sm text-hb-muted">{{ __('No permissions listed') }}</span>
                @else
                    <div class="flex max-w-xl flex-wrap gap-1.5">
                        @foreach ($permissions as $key)
                            <span class="inline-flex rounded-md bg-hb-tint px-2 py-0.5 text-[11px] font-medium text-hb-navy">{{ $key }}</span>
                        @endforeach
                    </div>
                    <p class="mt-1.5 text-xs text-hb-muted">{{ __(':count permission(s)', ['count' => $permissions->count()]) }}</p>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-4 py-10 text-center text-hb-muted">
                @if (! empty($loadError))
                    {{ __('Data unavailable') }}
                @else
                    {{ __('No roles were returned by the API.') }}
                @endif
            </td>
        </tr>
    @endforelse
</x-table>
@endsection
