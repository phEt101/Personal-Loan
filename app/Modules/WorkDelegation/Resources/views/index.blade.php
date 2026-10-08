@extends('layouts.app', ['title' => __('workdelegation::messages.title')])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/work-delegation.css') }}?v={{ filemtime(public_path('css/work-delegation.css')) }}">
@endpush

@section('content')
    <section class="dashboard module-page delegation-page">
        <div class="hero compact-hero hero-with-actions">
            <div class="hero-body">
                <h2>{{ __('workdelegation::messages.title') }}</h2>
                <p>{{ __('workdelegation::messages.subtitle') }}</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('work-delegations.create') }}" class="action-btn">+ {{ __('workdelegation::messages.add') }}</a>
            </div>
        </div>

        <div class="module-card">
            <div class="module-table-wrap">
                <table class="module-table delegation-table">
                    <thead>
                        <tr>
                            <th>{{ __('workdelegation::messages.group') }}</th>
                            <th>{{ __('workdelegation::messages.delegator') }}</th>
                            <th>{{ __('workdelegation::messages.delegate') }}</th>
                            <th>{{ __('workdelegation::messages.starts_at') }}</th>
                            <th>{{ __('workdelegation::messages.ends_at') }}</th>
                            <th>{{ __('workdelegation::messages.status') }}</th>
                            <th>{{ __('workdelegation::messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($delegations as $delegation)
                            @php($delegationStatus = $delegation->status)
                            <tr>
                                <td data-label="{{ __('workdelegation::messages.group') }}"><strong>{{ $delegation->group->name }}</strong></td>
                                <td data-label="{{ __('workdelegation::messages.delegator') }}">
                                    {{ $delegation->delegator->full_name }}<br><small>{{ $delegation->delegator->employee_code }}</small>
                                </td>
                                <td data-label="{{ __('workdelegation::messages.delegate') }}">
                                    {{ $delegation->delegate->full_name }}<br><small>{{ $delegation->delegate->employee_code }}</small>
                                </td>
                                <td data-label="{{ __('workdelegation::messages.starts_at') }}">{{ $delegation->starts_at->timezone(config('app.local_timezone'))->format('d/m/Y H:i') }}</td>
                                <td data-label="{{ __('workdelegation::messages.ends_at') }}">{{ $delegation->ends_at->timezone(config('app.local_timezone'))->format('d/m/Y H:i') }}</td>
                                <td data-label="{{ __('workdelegation::messages.status') }}">
                                    <span class="delegation-status delegation-status--{{ $delegationStatus }}">
                                        {{ __('workdelegation::messages.'.($delegationStatus === 'cancelled' ? 'cancelled_status' : $delegationStatus)) }}
                                    </span>
                                </td>
                                <td data-label="{{ __('workdelegation::messages.actions') }}">
                                    @if (in_array($delegationStatus, ['pending', 'active'], true) && (auth()->user()->isAdmin() || $delegation->delegator_user_id === auth()->id()))
                                        <div class="module-table-actions">
                                            <a class="module-action-button" href="{{ route('work-delegations.edit', $delegation) }}">
                                                {{ __('workdelegation::messages.edit') }}
                                            </a>
                                            <form method="POST" action="{{ route('work-delegations.cancel', $delegation) }}" onsubmit="return confirm(@js(__('workdelegation::messages.cancel_confirmation')))" >
                                                @csrf
                                                @method('PATCH')
                                                <button class="module-action-button is-danger" type="submit">{{ __('workdelegation::messages.cancel_action') }}</button>
                                            </form>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="module-table-empty">{{ __('workdelegation::messages.no_items') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.pagination', [
                'paginator' => $delegations,
                'summary' => __('workdelegation::messages.showing', ['from' => $delegations->firstItem(), 'to' => $delegations->lastItem(), 'total' => $delegations->total()]),
                'action' => route('work-delegations.index'),
                'formClass' => 'work-delegation-per-page-form',
                'selectId' => 'workDelegationPerPage',
                'perPage' => $perPage,
                'perPageOptions' => $perPageOptions,
                'rowsPerPageLabel' => __('workdelegation::messages.rows_per_page'),
                'previousLabel' => __('workdelegation::messages.previous_page'),
                'nextLabel' => __('workdelegation::messages.next_page'),
                'ariaLabel' => __('workdelegation::messages.pagination'),
                'queryParameters' => request()->except(['page', 'per_page']),
                'submitOnChange' => true,
            ])
        </div>
    </section>
@endsection
