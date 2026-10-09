@extends('layouts.app', ['title' => __('settings::messages.index_title')])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}?v={{ filemtime(public_path('css/settings.css')) }}">
@endpush

@section('content')
    <section class="dashboard module-page">
        <div class="hero compact-hero hero-with-actions">
            <div class="hero-body">
                <h2>{{ __('settings::messages.index_title') }}</h2>
                <p>{{ __('settings::messages.index_subtitle') }}</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('settings.users.create') }}" class="action-btn settings-add-user-button">
                    + {{ __('settings::messages.save') }}
                </a>
            </div>
        </div>

        @if (session('status'))
            <output class="module-alert module-alert-success">{{ session('status') }}</output>
        @endif

        <div class="module-card settings-user-filter-card">
            <h3>{{ __('settings::messages.search') }}</h3>
            <form method="GET" action="{{ route('settings.users.index') }}" class="settings-user-search">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <div class="module-field">
                    <label for="settings_user_search">{{ __('settings::messages.search') }}</label>
                    <input id="settings_user_search" name="q" type="search" value="{{ $search }}" placeholder="{{ __('settings::messages.search_placeholder') }}">
                </div>
                <div class="module-field">
                    <label for="settings_user_type_filter">{{ __('settings::messages.user_type') }}</label>
                    <select id="settings_user_type_filter" name="user_type">
                        <option value="">{{ __('settings::messages.all_user_types') }}</option>
                        <option value="internal" @selected($userType === 'internal')>{{ __('settings::messages.internal') }}</option>
                        <option value="external" @selected($userType === 'external')>{{ __('settings::messages.external') }}</option>
                    </select>
                </div>
                <button type="submit" class="action-btn">{{ __('settings::messages.search') }}</button>
                @if ($search !== '' || $userType !== '')
                    <a href="{{ route('settings.users.index', ['per_page' => $perPage, 'sort' => $sort, 'direction' => $direction]) }}" class="action-btn outline">{{ __('settings::messages.clear_search') }}</a>
                @endif
            </form>
        </div>

        <div class="module-card settings-user-list-card">
            <div class="settings-user-list-heading">
                <h3>{{ __('settings::messages.all_users') }}</h3>
                <span>{{ __('settings::messages.total_users', ['count' => $users->total()]) }}</span>
            </div>

            <div class="module-table-wrap">
                @php
                    $sortableHeaders = [
                        'employee_code' => __('settings::messages.employee_code'),
                        'full_name' => __('settings::messages.full_name'),
                        'email' => __('settings::messages.email'),
                        'user_type' => __('settings::messages.user_type'),
                        'role' => __('settings::messages.role'),
                        'status' => __('settings::messages.status'),
                        'created_at' => __('settings::messages.created_at'),
                    ];
                    $sortUrl = function (string $column) use ($sort, $direction): string {
                        $parameters = request()->query();
                        $parameters['sort'] = $column;
                        $parameters['direction'] = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
                        unset($parameters['page']);

                        return route('settings.users.index', $parameters);
                    };
                @endphp
                <table class="module-table">
                    <thead>
                        <tr>
                            @foreach ($sortableHeaders as $column => $label)
                                <th @if ($sort === $column) aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                    <a href="{{ $sortUrl($column) }}" class="settings-user-sort-link {{ $sort === $column ? 'is-active' : '' }}">
                                        <span>{{ $label }}</span>
                                        <span class="settings-user-sort-icon" aria-hidden="true">
                                            {{ $sort === $column ? ($direction === 'asc' ? '▲' : '▼') : '↕' }}
                                        </span>
                                    </a>
                                </th>
                            @endforeach
                            <th>{{ __('settings::messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td data-label="{{ __('settings::messages.employee_code') }}">{{ $user->employee_code }}</td>
                                <td data-label="{{ __('settings::messages.full_name') }}">{{ $user->full_name }}</td>
                                <td data-label="{{ __('settings::messages.email') }}">{{ $user->email }}</td>
                                <td data-label="{{ __('settings::messages.user_type') }}">
                                    <span class="settings-user-type settings-user-type-{{ $user->user_type }}">
                                        {{ __('settings::messages.'.$user->user_type) }}
                                    </span>
                                </td>
                                <td data-label="{{ __('settings::messages.role') }}">
                                    <span class="settings-user-role settings-user-role-{{ $user->role->slug }}">
                                        {{ $user->role->display_name }}
                                    </span>
                                </td>
                                <td data-label="{{ __('settings::messages.status') }}">
                                    <span class="settings-user-status {{ $user->is_active ? 'is-active' : 'is-inactive' }}">
                                        {{ $user->is_active ? __('settings::messages.active') : __('settings::messages.inactive') }}
                                    </span>
                                </td>
                                <td data-label="{{ __('settings::messages.created_at') }}">
                                    {{ $user->created_at?->timezone(config('app.local_timezone'))->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td data-label="{{ __('settings::messages.actions') }}">
                                    <div class="module-table-actions">
                                        <a href="{{ route('settings.users.edit', $user) }}" class="module-action-button">
                                            {{ __('settings::messages.edit') }}
                                        </a>
                                        @if ($user->employee_code !== 'EMP0001')
                                            <form method="POST" action="{{ route('settings.users.toggle-active', $user) }}" class="settings-user-toggle-form" data-confirm="{{ __('settings::messages.toggle_confirmation') }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="module-action-button settings-user-toggle-button {{ $user->is_active ? 'is-danger' : 'is-success' }}">
                                                    {{ $user->is_active ? __('settings::messages.disable') : __('settings::messages.enable') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="module-table-empty">{{ __('settings::messages.no_users') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.pagination', [
                'paginator' => $users,
                'summary' => __('settings::messages.showing_users', ['from' => $users->firstItem(), 'to' => $users->lastItem(), 'total' => $users->total()]),
                'action' => route('settings.users.index'),
                'formClass' => 'customer-history-per-page-form',
                'selectId' => 'settingsUserPerPage',
                'perPage' => $perPage,
                'perPageOptions' => $perPageOptions,
                'rowsPerPageLabel' => __('settings::messages.rows_per_page'),
                'previousLabel' => __('settings::messages.previous'),
                'nextLabel' => __('settings::messages.next'),
                'queryParameters' => request()->except(['page', 'per_page']),
                'submitOnChange' => true,
            ])
        </div>
    </section>

    <script>
        document.querySelectorAll('.settings-user-toggle-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (event.defaultPrevented) return;
                event.preventDefault();

                const button = form.querySelector('.settings-user-toggle-button');
                const status = form.closest('tr')?.querySelector('.settings-user-status');
                button.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message || response.statusText);

                    status?.classList.toggle('is-active', payload.is_active);
                    status?.classList.toggle('is-inactive', !payload.is_active);
                    if (status) status.textContent = payload.status_label;
                    button.classList.toggle('is-danger', payload.is_active);
                    button.classList.toggle('is-success', !payload.is_active);
                    button.textContent = payload.action_label;
                    window.showToast?.(payload.message);
                } catch (error) {
                    window.showToast?.(error.message, 'error');
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
@endsection
