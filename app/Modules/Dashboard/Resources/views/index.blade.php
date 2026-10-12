@extends('layouts.app', ['title' => __('dashboard::messages.title')])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
@endpush

@section('content')
    <div id="dashboardContentRegion" class="dashboard-ajax-region">
        <output class="dashboard-loading" aria-live="polite">
            <span class="dashboard-spinner" aria-hidden="true"></span>
        </output>
        <div id="dashboardContentPartial">
            @include('dashboard::_dashboard_content')
        </div>
    </div>

    <script>
        (() => {
            const region = document.getElementById('dashboardContentRegion');
            const partial = document.getElementById('dashboardContentPartial');

            const initialiseEffects = () => {
                window.requestAnimationFrame(() => window.requestAnimationFrame(() => {
                    partial?.querySelectorAll('.dashboard-chart-bars i[data-height]').forEach((bar) => {
                        bar.style.height = `${bar.dataset.height}%`;
                    });
                    partial?.querySelectorAll('.dashboard-donut[data-done]').forEach((chart) => {
                        chart.style.setProperty('--done', chart.dataset.done);
                    });
                    partial?.querySelectorAll('.dashboard-progress i[data-width]').forEach((bar) => {
                        bar.style.width = `${bar.dataset.width}%`;
                    });
                }));
            };

            const loadDashboard = async (url, updateHistory = false) => {
                if (!region || !partial) return false;

                const requestUrl = new URL(url, window.location.origin);
                requestUrl.searchParams.set('partial', '1');
                region.classList.add('is-loading');
                region.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(requestUrl, {
                        headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) throw new Error('Unable to load dashboard');
                    partial.innerHTML = await response.text();
                    initialiseEffects();

                    if (updateHistory) {
                        requestUrl.searchParams.delete('partial');
                        window.history.pushState({}, '', requestUrl);
                    }

                    return true;
                } catch (error) {
                    window.alert(error.message);
                    return false;
                } finally {
                    region.classList.remove('is-loading');
                    region.removeAttribute('aria-busy');
                }
            };

            region?.addEventListener('submit', (event) => {
                const form = event.target.closest('.dashboard-filters, .dashboard-per-page-form');
                if (!form) return;
                event.preventDefault();
                const url = new URL(form.action || window.location.href, window.location.origin);
                new FormData(form).forEach((value, key) => url.searchParams.set(key, value));
                loadDashboard(url, true);
            });

            region?.addEventListener('change', (event) => {
                if (!event.target.matches('.dashboard-per-page-form select')) return;

                const form = event.target.form;
                if (!form) return;
                const url = new URL(form.action, window.location.origin);
                new FormData(form).forEach((value, key) => url.searchParams.set(key, value));
                loadDashboard(url, true);
            });

            region?.addEventListener('click', (event) => {
                const link = event.target.closest('.pagination-link');
                if (!link || link.classList.contains('is-disabled')) return;
                event.preventDefault();
                loadDashboard(link.href, true);
            });

            window.addEventListener('popstate', () => loadDashboard(window.location.href));
            initialiseEffects();
        })();
    </script>
@endsection
