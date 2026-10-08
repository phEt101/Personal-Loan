@extends('layouts.app', ['title' => __('messages.delegation.title')])

@section('content')
    <section class="dashboard settings-user-page delegation-page">
        <div class="hero compact-hero hero-with-actions">
            <div class="hero-body">
                <h2>{{ __('messages.delegation.title') }}</h2>
                <p>{{ __('messages.delegation.subtitle') }}</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('work-delegations.create') }}" class="action-btn">+ {{ __('messages.delegation.add') }}</a>
            </div>
        </div>

        <div class="settings-user-card">
            <div class="settings-user-table-wrap">
                <table class="settings-user-table delegation-table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.delegation.group') }}</th>
                            <th>{{ __('messages.delegation.delegator') }}</th>
                            <th>{{ __('messages.delegation.delegate') }}</th>
                            <th>{{ __('messages.delegation.starts_at') }}</th>
                            <th>{{ __('messages.delegation.ends_at') }}</th>
                            <th>{{ __('messages.delegation.status') }}</th>
                            <th>{{ __('messages.delegation.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($delegations as $delegation)
                            <tr>
                                <td data-label="{{ __('messages.delegation.group') }}"><strong>{{ $delegation->group->name }}</strong></td>
                                <td data-label="{{ __('messages.delegation.delegator') }}">
                                    {{ $delegation->delegator->full_name }}<br><small>{{ $delegation->delegator->employee_code }}</small>
                                </td>
                                <td data-label="{{ __('messages.delegation.delegate') }}">
                                    {{ $delegation->delegate->full_name }}<br><small>{{ $delegation->delegate->employee_code }}</small>
                                </td>
                                <td data-label="{{ __('messages.delegation.starts_at') }}">{{ $delegation->starts_at->timezone(config('app.local_timezone'))->format('d/m/Y H:i') }}</td>
                                <td data-label="{{ __('messages.delegation.ends_at') }}">{{ $delegation->ends_at->timezone(config('app.local_timezone'))->format('d/m/Y H:i') }}</td>
                                <td data-label="{{ __('messages.delegation.status') }}">
                                    <span class="delegation-status delegation-status--{{ $delegation->status }}">
                                        {{ __('messages.delegation.'.($delegation->status === 'cancelled' ? 'cancelled_status' : $delegation->status)) }}
                                    </span>
                                </td>
                                <td data-label="{{ __('messages.delegation.actions') }}">
                                    @if ($delegation->cancelled_at === null && (auth()->user()->isAdmin() || $delegation->delegator_user_id === auth()->id()))
                                        <div class="settings-user-actions">
                                            <a class="settings-user-action-button" href="{{ route('work-delegations.edit', $delegation) }}">
                                                {{ __('messages.delegation.edit') }}
                                            </a>
                                            <form method="POST" action="{{ route('work-delegations.cancel', $delegation) }}" onsubmit="return confirm(@js(__('messages.delegation.cancel_confirmation')))" >
                                                @csrf
                                                @method('PATCH')
                                                <button class="settings-user-action-button danger" type="submit">{{ __('messages.delegation.cancel_action') }}</button>
                                            </form>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="settings-user-empty">{{ __('messages.delegation.no_items') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($delegations->hasPages())
                <div class="pagination-nav">
                    {{ $delegations->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
