<div class="card customer-history-filter-card">
    <h3>{{ __('customerhistory::messages.index.filters') }}</h3>
    <form method="GET" action="{{ route('customer-history.index') }}" class="customer-history-filter-form">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">
        <div class="customer-history-filter-field customer-history-filter-search">
            <label for="customerHistorySearch">{{ __('customerhistory::messages.index.search') }}</label>
            <input
                id="customerHistorySearch"
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="{{ __('customerhistory::messages.index.search_placeholder') }}"
            >
        </div>
        <div class="customer-history-filter-field">
            <label for="customerHistoryDateFrom">{{ __('customerhistory::messages.index.date_from') }}</label>
            <input id="customerHistoryDateFrom" type="date" name="date_from" value="{{ $dateFrom }}">
        </div>
        <div class="customer-history-filter-field">
            <label for="customerHistoryDateTo">{{ __('customerhistory::messages.index.date_to') }}</label>
            <input id="customerHistoryDateTo" type="date" name="date_to" value="{{ $dateTo }}">
        </div>
        @if ($isInternalUser)
            <div class="customer-history-filter-field">
                <label for="customerHistoryCreatorType">{{ __('customerhistory::messages.index.creator_type') }}</label>
                <select id="customerHistoryCreatorType" name="creator_type">
                    <option value="">{{ __('customerhistory::messages.index.creator_type_all') }}</option>
                    <option value="internal" @selected($creatorType === 'internal')>{{ __('customerhistory::messages.index.creator_type_internal') }}</option>
                    <option value="external" @selected($creatorType === 'external')>{{ __('customerhistory::messages.index.creator_type_external') }}</option>
                </select>
            </div>
        @endif
        @if ($canViewCreator)
            <div class="customer-history-filter-field">
                <label for="customerHistoryCreator">{{ __('customerhistory::messages.index.created_by') }}</label>
                <select id="customerHistoryCreator" name="creator_id">
                    <option value="">{{ __('customerhistory::messages.index.creator_all') }}</option>
                    @foreach ($creatorOptions as $creator)
                        <option value="{{ $creator->id }}" @selected((int) $creatorId === (int) $creator->id)>
                            {{ $creator->employee_code }} — {{ trim($creator->first_name.' '.$creator->last_name) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="customer-history-filter-actions">
            <button type="submit" class="action-btn table-action-btn-small">{{ __('customerhistory::messages.index.search') }}</button>
            @if ($hasActiveFilters)
                <a href="{{ route('customer-history.index', ['per_page' => $perPage, 'sort' => $sort, 'direction' => $direction]) }}" class="action-btn outline table-action-btn-small customer-history-filter-clear">
                    {{ __('customerhistory::messages.index.clear_filter') }}
                </a>
            @endif
        </div>
    </form>
</div>

<div class="card card--relative customer-history-list">
    <div class="customer-history-list-header">
        <h3>
            {{ $isInternalUser
                ? __('customerhistory::messages.index.all_customers')
                : ($isExternalManager
                    ? __('customerhistory::messages.index.external_customers')
                    : __('customerhistory::messages.index.my_customers')) }}
        </h3>
        <span class="customer-history-count">{{ number_format($customers->total()) }}</span>
    </div>

    <div class="consent-table">
        @php
            $sortableHeaders = [
                'customer_no' => __('customerhistory::messages.index.customer_no'),
                'customer_name' => __('customerhistory::messages.index.customer_name'),
                'mobile' => __('customerhistory::messages.index.mobile'),
            ];

            if ($canViewCreator) {
                $sortableHeaders['created_by'] = __('customerhistory::messages.index.created_by');
            }

            $sortableHeaders['created_at'] = __('customerhistory::messages.index.created_at');
            $sortUrl = function (string $column) use ($sort, $direction): string {
                $parameters = request()->query();
                $parameters['sort'] = $column;
                $parameters['direction'] = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
                unset($parameters['page'], $parameters['partial']);

                return route('customer-history.index', $parameters);
            };
        @endphp
        <table>
            <thead>
                <tr>
                    @foreach ($sortableHeaders as $column => $label)
                        <th @if ($sort === $column) aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}" @endif>
                            <a href="{{ $sortUrl($column) }}" class="customer-history-sort-link {{ $sort === $column ? 'is-active' : '' }}">
                                <span>{{ $label }}</span>
                                <span class="customer-history-sort-icon" aria-hidden="true">
                                    {{ $sort === $column ? ($direction === 'asc' ? '▲' : '▼') : '↕' }}
                                </span>
                            </a>
                        </th>
                        @if ($column === 'created_by' && $isInternalUser)
                            <th>{{ __('customerhistory::messages.index.work_source') }}</th>
                        @endif
                    @endforeach
                    <th>{{ __('customerhistory::messages.index.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    @php($canEditCustomer = $isInternalUser || (! $isExternalManager && (int) $customer->sysInsertUserId === $currentUserId))
                    <tr>
                        <td data-label="{{ __('customerhistory::messages.index.customer_no') }}">{{ $customer->CustomerNo }}</td>
                        <td data-label="{{ __('customerhistory::messages.index.customer_name') }}">
                            {{ trim($customer->Firstname.' '.$customer->Lastname) }}
                            <small class="customer-history-transfer-status is-{{ $customer->HmeterTransferStatus === 'transferred' ? 'transferred' : 'pending' }}">
                                {{ $customer->HmeterTransferStatus === 'transferred'
                                    ? __('customerhistory::messages.index.transfer_completed')
                                    : __('customerhistory::messages.index.transfer_pending') }}
                            </small>
                        </td>
                        <td data-label="{{ __('customerhistory::messages.index.mobile') }}">{{ $customer->Mobile ?: '-' }}</td>
                        @if ($canViewCreator)
                            <td data-label="{{ __('customerhistory::messages.index.created_by') }}">
                                {{ trim(($customer->CreatorFirstname ?? '').' '.($customer->CreatorLastname ?? '')) ?: '-' }}
                                @if ($customer->CreatorEmployeeCode)
                                    <small class="customer-history-employee-code">{{ $customer->CreatorEmployeeCode }}</small>
                                @endif
                            </td>
                        @endif
                        @if ($isInternalUser)
                            <td data-label="{{ __('customerhistory::messages.index.work_source') }}">
                                @if ($customer->WorkSourceType === 'delegated')
                                    <span class="delegation-status delegation-status--active">
                                        {{ __('customerhistory::messages.index.delegated_group', ['group' => $customer->CreatorGroupName]) }}
                                    </span>
                                    <small class="customer-history-employee-code">
                                        {{ __('customerhistory::messages.index.delegated_by', ['name' => $customer->DelegatedByName]) }}
                                    </small>
                                @elseif ($customer->WorkSourceType === 'group')
                                    <span class="delegation-status">{{ __('customerhistory::messages.index.regular_group', ['group' => $customer->CreatorGroupName]) }}</span>
                                @else
                                    {{ __('customerhistory::messages.index.internal_work') }}
                                @endif
                            </td>
                        @endif
                        <td data-label="{{ __('customerhistory::messages.index.created_at') }}">{{ $customer->sysInsertDateTime ? \Carbon\Carbon::parse($customer->sysInsertDateTime, 'UTC')->timezone(config('app.local_timezone'))->format('d/m/Y H:i') : '-' }}</td>
                        <td data-label="{{ __('customerhistory::messages.index.actions') }}">
                            <div class="customer-history-row-actions">
                            <button
                                type="button"
                                class="action-btn outline table-action-btn-small customer-view-button"
                                data-detail-url="{{ route('customer-history.show', $customer->CustomerNo) }}"
                            >
                                {{ __('customerhistory::messages.index.view') }}
                            </button>
                            @if ($canEditCustomer && $customer->HmeterTransferStatus !== 'transferred')
                                <button
                                    type="button"
                                    class="action-btn outline table-action-btn-small customer-edit-button"
                                    data-detail-url="{{ route('customer-history.show', $customer->CustomerNo) }}"
                                    data-update-url="{{ route('customer-history.update', $customer->CustomerNo) }}"
                                >
                                    {{ __('customerhistory::messages.index.edit') }}
                                </button>
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($canViewCreator ? 6 : 5) + ($isInternalUser ? 1 : 0) }}" class="empty-cell">{{ __('customerhistory::messages.index.no_customers') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.pagination', [
        'paginator' => $customers,
        'summary' => __('customerhistory::messages.index.showing_customers', ['from' => $customers->firstItem(), 'to' => $customers->lastItem(), 'total' => $customers->total()]),
        'action' => route('customer-history.index'),
        'formClass' => 'customer-history-per-page-form',
        'selectId' => 'customerHistoryPerPage',
        'perPage' => $perPage,
        'perPageOptions' => $perPageOptions,
        'rowsPerPageLabel' => __('customerhistory::messages.index.rows_per_page'),
        'previousLabel' => __('customerhistory::messages.index.previous_page'),
        'nextLabel' => __('customerhistory::messages.index.next_page'),
        'queryParameters' => request()->except(['page', 'partial', 'per_page']),
    ])
</div>
