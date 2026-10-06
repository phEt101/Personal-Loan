<div class="card customer-history-filter-card">
    <h3>{{ __('customerhistory::messages.index.filters') }}</h3>
    <form method="GET" action="{{ route('customer-history.index') }}" class="customer-history-filter-form">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
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
        <div class="customer-history-filter-actions">
            <button type="submit" class="action-btn table-action-btn-small">{{ __('customerhistory::messages.index.search') }}</button>
            @if ($hasActiveFilters)
                <a href="{{ route('customer-history.index', ['per_page' => $perPage]) }}" class="action-btn outline table-action-btn-small customer-history-filter-clear">
                    {{ __('customerhistory::messages.index.clear_filter') }}
                </a>
            @endif
        </div>
    </form>
</div>

<div class="card card--relative customer-history-list">
    <div class="customer-history-list-header">
        <h3>{{ $isInternalUser ? __('customerhistory::messages.index.all_customers') : __('customerhistory::messages.index.my_customers') }}</h3>
        <span class="customer-history-count">{{ number_format($customers->total()) }}</span>
    </div>

    <div class="consent-table">
        <table>
            <thead>
                <tr>
                    <th>{{ __('customerhistory::messages.index.customer_no') }}</th>
                    <th>{{ __('customerhistory::messages.index.customer_name') }}</th>
                    <th>{{ __('customerhistory::messages.index.mobile') }}</th>
                    @if ($isInternalUser)
                        <th>{{ __('customerhistory::messages.index.created_by') }}</th>
                    @endif
                    <th>{{ __('customerhistory::messages.index.created_at') }}</th>
                    <th>{{ __('customerhistory::messages.index.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
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
                        @if ($isInternalUser)
                            <td data-label="{{ __('customerhistory::messages.index.created_by') }}">
                                {{ trim(($customer->CreatorFirstname ?? '').' '.($customer->CreatorLastname ?? '')) ?: '-' }}
                                @if ($customer->CreatorEmployeeCode)
                                    <small class="customer-history-employee-code">{{ $customer->CreatorEmployeeCode }}</small>
                                @endif
                            </td>
                        @endif
                        <td data-label="{{ __('customerhistory::messages.index.created_at') }}">{{ $customer->sysInsertDateTime ? \Carbon\Carbon::parse($customer->sysInsertDateTime)->format('d/m/Y H:i') : '-' }}</td>
                        <td data-label="{{ __('customerhistory::messages.index.actions') }}">
                            <div class="customer-history-row-actions">
                            <button
                                type="button"
                                class="action-btn outline table-action-btn-small customer-view-button"
                                data-detail-url="{{ route('customer-history.show', $customer->CustomerNo) }}"
                            >
                                {{ __('customerhistory::messages.index.view') }}
                            </button>
                            @if ($customer->HmeterTransferStatus !== 'transferred')
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
                        <td colspan="{{ $isInternalUser ? 6 : 5 }}" class="empty-cell">{{ __('customerhistory::messages.index.no_customers') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($customers->total() > 0)
        <div class="pagination-container">
            <nav class="pagination-nav" aria-label="Pagination">
                <div class="customer-history-pagination-info">
                    <span class="pagination-summary">
                        {{ __('customerhistory::messages.index.showing_customers', ['from' => $customers->firstItem(), 'to' => $customers->lastItem(), 'total' => $customers->total()]) }}
                    </span>
                    <form method="GET" action="{{ route('customer-history.index') }}" class="customer-history-per-page-form">
                        @if ($search !== '')
                            <input type="hidden" name="q" value="{{ $search }}">
                        @endif
                        @if ($dateFrom !== '')
                            <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                        @endif
                        @if ($dateTo !== '')
                            <input type="hidden" name="date_to" value="{{ $dateTo }}">
                        @endif
                        @if ($creatorType !== '')
                            <input type="hidden" name="creator_type" value="{{ $creatorType }}">
                        @endif
                        <label for="customerHistoryPerPage">{{ __('customerhistory::messages.index.rows_per_page') }}</label>
                        <select id="customerHistoryPerPage" name="per_page">
                            @foreach ($perPageOptions as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="pagination-list">
                    <a class="pagination-link {{ $customers->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $customers->previousPageUrl() ?: '#' }}">
                        {{ __('customerhistory::messages.index.previous_page') }}
                    </a>
                    @foreach ($paginationPages as $index => $page)
                        @if ($index > 0 && $page - $paginationPages[$index - 1] > 1)
                            <span class="pagination-ellipsis">…</span>
                        @endif

                        @if ($page === $customers->currentPage())
                            <span class="pagination-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-link" href="{{ $customers->url($page) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                    <a class="pagination-link {{ $customers->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $customers->nextPageUrl() ?: '#' }}">
                        {{ __('customerhistory::messages.index.next_page') }}
                    </a>
                </div>
            </nav>
        </div>
    @endif
</div>
