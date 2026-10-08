@php
    $localTimezone = config('app.local_timezone');
    $formatDateTime = fn ($value) => $value
        ? \Carbon\Carbon::parse($value, 'UTC')->timezone($localTimezone)->format('d/m/Y H:i')
        : '-';
    $fullName = fn ($first, $last) => trim(($first ?? '').' '.($last ?? '')) ?: '-';
@endphp

<nav class="report-tabs" aria-label="{{ __('report::messages.title') }}">
    <a href="{{ route('reports.index', ['report' => 'transfers']) }}" class="report-tab-link {{ $report === 'transfers' ? 'is-active' : '' }}">
        {{ __('report::messages.transfer_tab') }}
    </a>
    <a href="{{ route('reports.index', ['report' => 'customers']) }}" class="report-tab-link {{ $report === 'customers' ? 'is-active' : '' }}">
        {{ __('report::messages.customer_tab') }}
    </a>
</nav>

<section class="report-card report-filter-card">
    <h2>{{ __('report::messages.filters') }}</h2>
    <form method="GET" action="{{ route('reports.index') }}" class="report-filter-grid">
        <input type="hidden" name="report" value="{{ $report }}">
        <input type="hidden" name="submitted" value="1">
        <div class="report-field">
            <label for="date_from">{{ __('report::messages.date_from') }}</label>
            <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="report-field">
            <label for="date_to">{{ __('report::messages.date_to') }}</label>
            <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        <div class="report-field">
            <label for="group_id">{{ __('report::messages.group') }}</label>
            <select id="group_id" name="group_id">
                <option value="">{{ __('report::messages.all') }}</option>
                @foreach ($groups as $group)
                    <option value="{{ $group->id }}" @selected((string) ($filters['group_id'] ?? '') === (string) $group->id)>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="report-field">
            <label for="external_user_id">{{ __('report::messages.external') }}</label>
            <select id="external_user_id" name="external_user_id">
                <option value="">{{ __('report::messages.all') }}</option>
                @foreach ($externalUsers as $user)
                    <option
                        value="{{ $user->id }}"
                        data-group-id="{{ $user->responsibility_group_id }}"
                        @selected((string) ($filters['external_user_id'] ?? '') === (string) $user->id)
                    >{{ $user->full_name }}</option>
                @endforeach
            </select>
        </div>
        @if ($report === 'transfers')
            <div class="report-field">
                <label for="confirmer_id">{{ __('report::messages.confirmer') }}</label>
                <select id="confirmer_id" name="confirmer_id">
                    <option value="">{{ __('report::messages.all') }}</option>
                    @foreach ($internalUsers as $user)
                        <option value="{{ $user->id }}" @selected((string) ($filters['confirmer_id'] ?? '') === (string) $user->id)>{{ $user->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-field">
                <label for="transfer_source">{{ __('report::messages.source') }}</label>
                <select id="transfer_source" name="transfer_source">
                    <option value="">{{ __('report::messages.all') }}</option>
                    <option value="direct" @selected(($filters['transfer_source'] ?? '') === 'direct')>{{ __('report::messages.direct') }}</option>
                    <option value="delegated" @selected(($filters['transfer_source'] ?? '') === 'delegated')>{{ __('report::messages.delegated') }}</option>
                </select>
            </div>
        @else
            <div class="report-field">
                <label for="transfer_status">{{ __('report::messages.transfer_status') }}</label>
                <select id="transfer_status" name="transfer_status">
                    <option value="">{{ __('report::messages.all') }}</option>
                    <option value="pending" @selected(($filters['transfer_status'] ?? '') === 'pending')>{{ __('report::messages.pending') }}</option>
                    <option value="transferred" @selected(($filters['transfer_status'] ?? '') === 'transferred')>{{ __('report::messages.transferred') }}</option>
                </select>
            </div>
        @endif
        <div class="report-filter-actions">
            <button type="submit" class="action-btn">{{ __('report::messages.search') }}</button>
            <a href="{{ route('reports.index', ['report' => $report]) }}" class="action-btn outline report-reset-link">{{ __('report::messages.reset') }}</a>
            @if ($hasSearched)
                <a href="{{ route('reports.export', request()->except(['page', 'partial'])) }}" class="action-btn report-excel-button">{{ __('report::messages.download_excel') }}</a>
            @else
                <button type="button" class="action-btn report-excel-button" disabled>{{ __('report::messages.download_excel') }}</button>
            @endif
        </div>
    </form>
</section>

<section class="report-card">
    <div class="report-table-wrap">
        @if (! $hasSearched)
            <div class="report-search-prompt">{{ __('report::messages.search_prompt') }}</div>
        @elseif ($report === 'transfers')
            <table class="report-table">
                <thead><tr>
                    <th>{{ __('report::messages.customer_no') }}</th>
                    <th>{{ __('report::messages.customer') }}</th>
                    <th>{{ __('report::messages.external_owner') }}</th>
                    <th>{{ __('report::messages.group') }}</th>
                    <th>{{ __('report::messages.confirmed_by') }}</th>
                    <th>{{ __('report::messages.source') }}</th>
                    <th>{{ __('report::messages.confirmed_at') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($transferRows as $row)
                        <tr>
                            <td data-label="{{ __('report::messages.customer_no') }}">{{ $row->CustomerNo }}</td>
                            <td data-label="{{ __('report::messages.customer') }}">{{ $fullName($row->Firstname, $row->Lastname) }}</td>
                            <td data-label="{{ __('report::messages.external_owner') }}">{{ $fullName($row->OwnerFirstname, $row->OwnerLastname) }}</td>
                            <td data-label="{{ __('report::messages.group') }}">{{ $row->GroupName ?: __('report::messages.unassigned_group') }}</td>
                            <td data-label="{{ __('report::messages.confirmed_by') }}">{{ $fullName($row->ConfirmerFirstname, $row->ConfirmerLastname) }}</td>
                            <td data-label="{{ __('report::messages.source') }}">
                                <span class="report-source {{ $row->HmeterWorkDelegationId ? 'is-delegated' : '' }}">{{ $row->HmeterWorkDelegationId ? __('report::messages.delegated') : __('report::messages.direct') }}</span>
                                @if ($row->HmeterWorkDelegationId)<small>{{ __('report::messages.delegated_from') }} {{ $fullName($row->DelegatorFirstname, $row->DelegatorLastname) }}</small>@endif
                            </td>
                            <td data-label="{{ __('report::messages.confirmed_at') }}">{{ $formatDateTime($row->HmeterTransferredAt) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="report-empty">{{ __('report::messages.no_transfers') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            @include('partials.pagination', [
                'paginator' => $transferRows,
                'summary' => __('report::messages.showing', ['from' => $transferRows->firstItem(), 'to' => $transferRows->lastItem(), 'total' => $transferRows->total()]),
                'action' => route('reports.index'),
                'formClass' => 'report-per-page-form',
                'selectId' => 'reportPerPage',
                'perPage' => $perPage,
                'perPageOptions' => $perPageOptions,
                'rowsPerPageLabel' => __('report::messages.rows_per_page'),
                'previousLabel' => __('report::messages.previous_page'),
                'nextLabel' => __('report::messages.next_page'),
                'ariaLabel' => __('report::messages.pagination'),
                'containerClass' => 'report-pagination',
                'queryParameters' => request()->except(['page', 'partial', 'per_page']),
            ])
        @else
            <table class="report-table">
                <thead><tr>
                    <th>{{ __('report::messages.customer_no') }}</th>
                    <th>{{ __('report::messages.customer') }}</th>
                    <th>{{ __('report::messages.external_owner') }}</th>
                    <th>{{ __('report::messages.group') }}</th>
                    <th>{{ __('report::messages.transfer_status') }}</th>
                    <th>{{ __('report::messages.created_at') }}</th>
                    <th>{{ __('report::messages.confirmed_at') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($customerRows as $row)
                        <tr>
                            <td data-label="{{ __('report::messages.customer_no') }}"><strong>{{ $row->CustomerNo }}</strong></td>
                            <td data-label="{{ __('report::messages.customer') }}">{{ $fullName($row->Firstname, $row->Lastname) }}</td>
                            <td data-label="{{ __('report::messages.external_owner') }}">{{ $fullName($row->OwnerFirstname, $row->OwnerLastname) }}</td>
                            <td data-label="{{ __('report::messages.group') }}">{{ $row->GroupName ?: __('report::messages.unassigned_group') }}</td>
                            <td data-label="{{ __('report::messages.transfer_status') }}">
                                <span class="report-source {{ $row->HmeterTransferStatus === 'transferred' ? '' : 'is-delegated' }}">
                                    {{ $row->HmeterTransferStatus === 'transferred' ? __('report::messages.transferred') : __('report::messages.pending') }}
                                </span>
                            </td>
                            <td data-label="{{ __('report::messages.created_at') }}">{{ $formatDateTime($row->sysInsertDateTime) }}</td>
                            <td data-label="{{ __('report::messages.confirmed_at') }}">{{ $formatDateTime($row->HmeterTransferredAt) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="report-empty">{{ __('report::messages.no_customers') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            @include('partials.pagination', [
                'paginator' => $customerRows,
                'summary' => __('report::messages.showing', ['from' => $customerRows->firstItem(), 'to' => $customerRows->lastItem(), 'total' => $customerRows->total()]),
                'action' => route('reports.index'),
                'formClass' => 'report-per-page-form',
                'selectId' => 'reportPerPage',
                'perPage' => $perPage,
                'perPageOptions' => $perPageOptions,
                'rowsPerPageLabel' => __('report::messages.rows_per_page'),
                'previousLabel' => __('report::messages.previous_page'),
                'nextLabel' => __('report::messages.next_page'),
                'ariaLabel' => __('report::messages.pagination'),
                'containerClass' => 'report-pagination',
                'queryParameters' => request()->except(['page', 'partial', 'per_page']),
            ])
        @endif
    </div>
</section>
