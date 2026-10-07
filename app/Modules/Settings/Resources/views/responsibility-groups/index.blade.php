@extends('layouts.app', ['title' => __('settings::messages.responsibility_groups')])

@section('content')
    <section class="dashboard settings-user-page">
        <div class="hero compact-hero hero-with-actions">
            <div class="hero-body">
                <h2>{{ __('settings::messages.responsibility_groups') }}</h2>
                <p>{{ __('settings::messages.responsibility_groups_subtitle') }}</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('settings.responsibility-groups.create') }}" class="action-btn settings-add-user-button">
                    + {{ __('settings::messages.add_responsibility_group') }}
                </a>
            </div>
        </div>

        <div class="settings-user-card">
            <div class="settings-user-table-wrap">
                <table class="settings-user-table">
                    <thead>
                        <tr>
                            <th>{{ __('settings::messages.group_name') }}</th>
                            <th>{{ __('settings::messages.internal_members') }}</th>
                            <th>{{ __('settings::messages.external_members') }}</th>
                            <th>{{ __('settings::messages.status') }}</th>
                            <th>{{ __('settings::messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groups as $group)
                            <tr>
                                <td>{{ $group->name }}</td>
                                <td>{{ $group->internalUsers->pluck('full_name')->join(', ') ?: '—' }}</td>
                                <td>{{ $group->externalUsers->pluck('full_name')->join(', ') ?: '—' }}</td>
                                <td>{{ $group->is_active ? __('settings::messages.active') : __('settings::messages.inactive') }}</td>
                                <td>
                                    <a class="settings-user-action-button" href="{{ route('settings.responsibility-groups.edit', $group) }}">
                                        {{ __('settings::messages.edit') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="settings-user-empty">{{ __('settings::messages.no_responsibility_groups') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
