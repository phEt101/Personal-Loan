@extends('layouts.app', ['title' => __('dashboard::messages.title')])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
@endpush

@section('content')
<section class="dashboard dashboard-page">
    <div class="hero compact-hero dashboard-hero">
        <div class="hero-body">
            <h2>{{ __('dashboard::messages.title') }}</h2>
            <p>{{ __('dashboard::messages.subtitle') }}</p>
        </div>
        <form method="GET" class="dashboard-filters">
            <label>{{ __('dashboard::messages.period') }}
                <select name="period">
                    @foreach ([7, 30, 90] as $days)
                        <option value="{{ $days }}" @selected($period === $days)>{{ __('dashboard::messages.days', ['count' => $days]) }}</option>
                    @endforeach
                </select>
            </label>
            @if ($groups->isNotEmpty())
                <label>{{ __('dashboard::messages.group') }}
                    <select name="group_id">
                        <option value="">{{ __('dashboard::messages.all_groups') }}</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}" @selected($groupId === $group->id)>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <button class="action-btn" type="submit">{{ __('dashboard::messages.apply') }}</button>
        </form>
    </div>

    @php
        $cards = [
            ['label' => __('dashboard::messages.new_today'), 'value' => $newToday, 'tone' => 'blue'],
            ['label' => __('dashboard::messages.new_period', ['days' => $period]), 'value' => $newInPeriod, 'tone' => 'purple'],
            ['label' => __('dashboard::messages.pending'), 'value' => $pending, 'tone' => 'amber'],
            ['label' => __('dashboard::messages.transferred'), 'value' => $transferred, 'tone' => 'green'],
            ['label' => __('dashboard::messages.overdue', ['days' => $overdueDays]), 'value' => $overdue, 'tone' => 'red'],
        ];
        $statusTotal = max(1, $pending + $transferred);
        $chartMax = max(1, ...$trend['created'], ...$trend['transferred']);
        $chartLabelInterval = $period <= 7 ? 1 : (int) ceil(($period - 1) / 6);
    @endphp
    <div class="dashboard-kpis">
        @foreach ($cards as $card)
            <article class="dashboard-kpi dashboard-kpi--{{ $card['tone'] }}">
                <span class="dashboard-kpi-label">{{ $card['label'] }}</span>
                <strong>{{ number_format($card['value']) }}</strong>
                <small>{{ __('dashboard::messages.customers') }}</small>
            </article>
        @endforeach
    </div>

    <div class="dashboard-grid dashboard-grid--overview">
        <article class="dashboard-panel dashboard-trend">
            <div class="dashboard-panel-head">
                <h3>{{ __('dashboard::messages.trend') }}</h3>
                <div class="dashboard-legend"><span class="is-created"></span>{{ __('dashboard::messages.created') }} <span class="is-transferred"></span>{{ __('dashboard::messages.transferred') }}</div>
            </div>
            <figure class="dashboard-chart" aria-label="{{ __('dashboard::messages.trend') }}">
                @foreach ($trend['labels'] as $index => $label)
                    <div class="dashboard-chart-day" title="{{ $label }}: {{ $trend['created'][$index] }} / {{ $trend['transferred'][$index] }}">
                        <div class="dashboard-chart-bars">
                            <i class="created" data-height="{{ max(3, ($trend['created'][$index] / $chartMax) * 100) }}"></i>
                            <i class="transferred" data-height="{{ max(3, ($trend['transferred'][$index] / $chartMax) * 100) }}"></i>
                        </div>
                        @if ($index === 0 || $index === count($trend['labels']) - 1 || $index % $chartLabelInterval === 0)
                            <small>{{ \Carbon\Carbon::parse($label)->format('d/m') }}</small>
                        @else
                            <small aria-hidden="true"></small>
                        @endif
                    </div>
                @endforeach
            </figure>
        </article>
        <article class="dashboard-panel dashboard-status">
            <h3>{{ __('dashboard::messages.status') }}</h3>
            <div class="dashboard-donut" data-done="{{ ($transferred / $statusTotal) * 100 }}"><span><strong>{{ number_format($pending + $transferred) }}</strong>{{ __('dashboard::messages.customers') }}</span></div>
            <div class="dashboard-status-row"><span><i class="status-dot pending"></i>{{ __('dashboard::messages.pending') }}</span><strong>{{ number_format($pending) }}</strong></div>
            <div class="dashboard-status-row"><span><i class="status-dot transferred"></i>{{ __('dashboard::messages.transferred') }}</span><strong>{{ number_format($transferred) }}</strong></div>
        </article>
    </div>

    <article class="dashboard-panel">
        <div class="dashboard-panel-head"><div><h3>{{ __('dashboard::messages.work_queue') }}</h3><p>{{ __('dashboard::messages.work_queue_hint') }}</p></div></div>
        <div class="dashboard-table-wrap"><table class="dashboard-table">
            <thead><tr><th>{{ __('dashboard::messages.customer') }}</th><th>{{ __('dashboard::messages.owner') }}</th><th>{{ __('dashboard::messages.group') }}</th><th>{{ __('dashboard::messages.waiting') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($queue as $item)
                    @php $waitDays = (int) floor(\Carbon\Carbon::parse($item->sysInsertDateTime)->diffInDays(now())); @endphp
                    <tr>
                        <td><strong>{{ trim($item->Firstname.' '.$item->Lastname) }}</strong><small>{{ $item->CustomerNo }}</small></td>
                        <td>{{ trim($item->OwnerFirstname.' '.$item->OwnerLastname) ?: '—' }}</td>
                        <td>{{ $item->GroupName ?: __('dashboard::messages.no_group') }}</td>
                        <td><span class="dashboard-wait {{ $waitDays > $overdueDays ? 'is-overdue' : '' }}">{{ $waitDays ? __('dashboard::messages.day_count', ['count' => $waitDays]) : __('dashboard::messages.today') }}</span></td>
                        <td><a class="dashboard-link" href="{{ route('customer-history.index', ['search' => $item->CustomerNo, 'open_customer' => $item->CustomerNo]) }}">{{ __('dashboard::messages.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="dashboard-empty">{{ __('dashboard::messages.empty_queue') }}</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @include('partials.pagination', [
            'paginator' => $queue,
            'summary' => __('dashboard::messages.showing_work', [
                'from' => $queue->firstItem(),
                'to' => $queue->lastItem(),
                'total' => $queue->total(),
            ]),
            'action' => route('dashboard.index'),
            'formClass' => 'dashboard-per-page-form',
            'selectId' => 'dashboardWorkPerPage',
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
            'rowsPerPageLabel' => __('dashboard::messages.rows_per_page'),
            'previousLabel' => __('dashboard::messages.previous_page'),
            'nextLabel' => __('dashboard::messages.next_page'),
            'queryParameters' => request()->except(['work_page', 'per_page']),
            'ariaLabel' => __('dashboard::messages.work_queue'),
        ])
    </article>

    <article class="dashboard-panel">
        <div class="dashboard-panel-head"><h3>{{ __('dashboard::messages.group_performance') }}</h3><span>{{ __('dashboard::messages.days', ['count' => $period]) }}</span></div>
        <div class="dashboard-performance">
            @forelse ($performance as $row)
                @php $rate = $row->total ? round(($row->transferred / $row->total) * 100) : 0; @endphp
                <div class="dashboard-performance-row">
                    <div><strong>{{ $row->name ?: __('dashboard::messages.no_group') }}</strong><small>{{ __('dashboard::messages.total') }} {{ number_format($row->total) }} · {{ __('dashboard::messages.pending') }} {{ number_format($row->pending) }}</small></div>
                    <div class="dashboard-progress"><i data-width="{{ $rate }}"></i></div>
                    <strong>{{ $rate }}%</strong>
                </div>
            @empty
                <p class="dashboard-empty">{{ __('dashboard::messages.no_data') }}</p>
            @endforelse
        </div>
    </article>
</section>
<script>
    (() => {
        window.requestAnimationFrame(() => window.requestAnimationFrame(() => {
            document.querySelectorAll('.dashboard-chart-bars i[data-height]').forEach((bar) => {
                bar.style.height = `${bar.dataset.height}%`;
            });
            document.querySelectorAll('.dashboard-donut[data-done]').forEach((chart) => {
                chart.style.setProperty('--done', chart.dataset.done);
            });
            document.querySelectorAll('.dashboard-progress i[data-width]').forEach((bar) => {
                bar.style.width = `${bar.dataset.width}%`;
            });
        }));
    })();
</script>
@endsection
