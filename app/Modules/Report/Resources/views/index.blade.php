@extends('layouts.app', ['title' => __('report::messages.title')])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/report.css') }}?v={{ filemtime(public_path('css/report.css')) }}">
@endpush

@section('content')
    <section class="report-page">
        <header class="report-hero">
            <div>
                <h1>{{ __('report::messages.title') }}</h1>
                <p>{{ __('report::messages.subtitle') }}</p>
            </div>
        </header>

        <div id="reportContentRegion" class="report-ajax-region">
            <output class="report-loading" aria-live="polite">
                <span class="report-spinner" aria-hidden="true"></span>
            </output>
            <div id="reportContentPartial">
                @include('report::_report_content')
            </div>
        </div>
    </section>

    <script>
        (() => {
            const region = document.getElementById('reportContentRegion');
            const partial = document.getElementById('reportContentPartial');

            const syncExternalUsers = () => {
                const group = partial?.querySelector('#group_id');
                const external = partial?.querySelector('#external_user_id');
                if (!group || !external) return;

                const selectedGroup = group.value;
                Array.from(external.options).slice(1).forEach((option) => {
                    const matches = selectedGroup === '' || option.dataset.groupId === selectedGroup;
                    option.hidden = !matches;
                    option.disabled = !matches;
                });

                if (external.selectedOptions[0]?.disabled) external.value = '';
            };

            const loadReport = async (url, updateHistory = false) => {
                if (!region || !partial) return false;

                const requestUrl = new URL(url, window.location.origin);
                requestUrl.searchParams.set('partial', '1');
                region.classList.add('is-loading');
                region.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(requestUrl, {
                        headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) throw new Error('Unable to load report');
                    partial.innerHTML = await response.text();
                    syncExternalUsers();

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
                const form = event.target.closest('.report-filter-grid, .report-per-page-form');
                if (!form) return;
                event.preventDefault();
                const url = new URL(form.action, window.location.origin);
                new FormData(form).forEach((value, key) => url.searchParams.set(key, value));
                loadReport(url, true);
            });

            region?.addEventListener('change', (event) => {
                if (event.target.matches('#group_id')) syncExternalUsers();
                if (event.target.matches('.report-per-page-form select')) event.target.form?.requestSubmit();
            });

            region?.addEventListener('click', (event) => {
                const link = event.target.closest('.report-tab-link, .report-reset-link, .report-pagination .pagination-link');
                if (!link || link.classList.contains('is-disabled')) return;
                event.preventDefault();
                loadReport(link.href, true);
            });

            window.addEventListener('popstate', () => loadReport(window.location.href));
            syncExternalUsers();
        })();
    </script>
@endsection
