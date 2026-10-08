@extends('layouts.app', ['title' => __('settings::messages.index_title')])

@section('content')
    <section class="dashboard settings-user-page">
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
            <div class="profile-alert profile-alert-success" role="status">{{ session('status') }}</div>
        @endif

        <div class="settings-user-card settings-user-filter-card">
            <h3>{{ __('settings::messages.search') }}</h3>
            <form method="GET" action="{{ route('settings.users.index') }}" class="settings-user-search">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <div class="profile-field">
                    <label for="settings_user_search">{{ __('settings::messages.search') }}</label>
                    <input id="settings_user_search" name="q" type="search" value="{{ $search }}" placeholder="{{ __('settings::messages.search_placeholder') }}">
                </div>
                <button type="submit" class="action-btn">{{ __('settings::messages.search') }}</button>
                @if ($search !== '')
                    <a href="{{ route('settings.users.index', ['per_page' => $perPage, 'sort' => $sort, 'direction' => $direction]) }}" class="action-btn outline">{{ __('settings::messages.clear_search') }}</a>
                @endif
            </form>
        </div>

        <div class="settings-user-card settings-user-list-card">
            <div class="settings-user-list-heading">
                <h3>{{ __('settings::messages.all_users') }}</h3>
                <span>{{ __('settings::messages.total_users', ['count' => $users->total()]) }}</span>
            </div>

            <div class="settings-user-table-wrap">
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
                <table class="settings-user-table">
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
                                    <div class="settings-user-actions">
                                        <a href="{{ route('settings.users.edit', $user) }}" class="settings-user-action-button">
                                            {{ __('settings::messages.edit') }}
                                        </a>
                                        @if ($user->employee_code !== 'EMP0001')
                                            <form method="POST" action="{{ route('settings.users.toggle-active', $user) }}" onsubmit="return confirm(@js(__('settings::messages.toggle_confirmation')))">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="settings-user-action-button {{ $user->is_active ? 'is-danger' : 'is-success' }}">
                                                    {{ $user->is_active ? __('settings::messages.disable') : __('settings::messages.enable') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="settings-user-empty">{{ __('settings::messages.no_users') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->total() > 0)
                <div class="pagination-container">
                    <nav class="pagination-nav" aria-label="Pagination">
                        <div class="customer-history-pagination-info">
                            <span class="pagination-summary">
                                {{ __('settings::messages.showing_users', ['from' => $users->firstItem(), 'to' => $users->lastItem(), 'total' => $users->total()]) }}
                            </span>
                            <form method="GET" action="{{ route('settings.users.index') }}" class="customer-history-per-page-form">
                                @if ($search !== '')
                                    <input type="hidden" name="q" value="{{ $search }}">
                                @endif
                                <input type="hidden" name="sort" value="{{ $sort }}">
                                <input type="hidden" name="direction" value="{{ $direction }}">
                                <label for="settingsUserPerPage">{{ __('settings::messages.rows_per_page') }}</label>
                                <select id="settingsUserPerPage" name="per_page" onchange="this.form.submit()">
                                    @foreach ($perPageOptions as $option)
                                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="pagination-list">
                            <a class="pagination-link {{ $users->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $users->previousPageUrl() ?: '#' }}">
                                {{ __('settings::messages.previous') }}
                            </a>
                            @foreach ($paginationPages as $index => $page)
                                @if ($index > 0 && $page - $paginationPages[$index - 1] > 1)
                                    <span class="pagination-ellipsis">…</span>
                                @endif

                                @if ($page === $users->currentPage())
                                    <span class="pagination-current" aria-current="page">{{ $page }}</span>
                                @else
                                    <a class="pagination-link" href="{{ $users->url($page) }}">{{ $page }}</a>
                                @endif
                            @endforeach
                            <a class="pagination-link {{ $users->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $users->nextPageUrl() ?: '#' }}">
                                {{ __('settings::messages.next') }}
                            </a>
                        </div>
                    </nav>
                </div>
            @endif
        </div>
    </section>
@endsection
