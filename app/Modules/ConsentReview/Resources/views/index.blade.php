@extends('layouts.app')

@section('content')
<div class="container">
    <div class="search-filter-card">
        <form method="get" class="search-filter-form">
            <div>
                <label for="search-query" class="filter-label">{{ __('consentreview::messages.search') }}</label>
                <input id="search-query" name="q" value="{{ request('q') }}" class="filter-input" placeholder="{{ __('consentreview::messages.search_placeholder') }}">
            </div>
            <div>
                <label for="search-status" class="filter-label">{{ __('consentreview::messages.status') }}</label>
                <select id="search-status" name="status" class="filter-input">
                    <option value="">{{ __('consentreview::messages.status_all') }}</option>
                    <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>{{ __('consentreview::messages.status_approved') }}</option>
                    <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>{{ __('consentreview::messages.status_rejected') }}</option>
                </select>
            </div>
            <div>
                <label for="date-from" class="filter-label">{{ __('consentreview::messages.date_from') }}</label>
                <input id="date-from" type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input">
            </div>
            <div>
                <label for="date-to" class="filter-label">{{ __('consentreview::messages.date_to') }}</label>
                <input id="date-to" type="date" name="date_to" value="{{ request('date_to') }}" class="filter-input">
            </div>
            <div class="search-actions-group">
                <button class="btn btn-search" type="submit">{{ __('consentreview::messages.search_button') }}</button>
                <button type="button" id="reset-filters" class="btn btn-reset">{{ __('consentreview::messages.reset') }}</button>
            </div>
        </form>
    </div>



    <div class="card consent-table">
        <table class="w-full">
            <thead>
                <tr>
                    <th>{{ __('consentreview::messages.col_code') }}</th>
                    <th>{{ __('consentreview::messages.col_name') }}</th>
                    <th>{{ __('consentreview::messages.col_date') }}</th>
                    <th>{{ __('consentreview::messages.col_officer_group') }}</th>
                    <th>{{ __('consentreview::messages.col_requested_amount') }}</th>
                    <th>{{ __('consentreview::messages.col_status') }}</th>
                    <th>{{ __('consentreview::messages.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($customers as $c)
                <tr>
                    <td>{{ $c->app_no }}</td>
                    <td>{{ $c->applicant?->name }}</td>
                    <td>{{ $c->created_at?->format('d/m/Y') }}</td>
                    <td>{{ $c->officer_group ?? '-' }}</td>
                    <td>{{ $c->loanRequest?->custom_loan_amount ? number_format($c->loanRequest->custom_loan_amount, 0) . ' บาท' : '-' }}</td>
                    <td>
                        @if($c->status === 'approved')
                            <span class="badge badge-signed">{{ __('consentreview::messages.status_approved') }}</span>
                        @elseif($c->status === 'rejected')
                            <span class="badge badge-pending">{{ __('consentreview::messages.status_rejected') }}</span>
                        @else
                            <span class="badge">{{ $c->status ?: '-' }}</span>
                        @endif
                    </td>
                    <td class="table-actions">
                        <button type="button" class="action-link view-docs" data-id="{{ $c->encrypted_id }}">{{ __('consentreview::messages.action_view_docs') }}</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-gray-500">{{ __('consentreview::messages.no_results') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $customers->links() }}</div>
    </div>
</div>


<script>
// AJAX search: submit form via fetch and replace table HTML
(function () {
    const form = document.querySelector('.search-filter-form');
    if (!form) return;

    async function submitAjax(e) {
        e.preventDefault();
        const params = new URLSearchParams(new FormData(form)).toString();
        const url = form.getAttribute('action') || window.location.pathname;
        try {
            const resp = await fetch(url + (params ? ('?' + params) : ''), {
                headers: { 'Accept': 'text/html' }
            });
            if (!resp.ok) throw new Error('Network response not ok');
            const text = await resp.text();
            const doc = new DOMParser().parseFromString(text, 'text/html');
            const newTable = doc.querySelector('.consent-table');
            const oldTable = document.querySelector('.consent-table');
                if (newTable && oldTable) {
                oldTable.replaceWith(newTable);
                // reattach handlers after DOM replacement
                if (typeof attachViewDocsHandlers === 'function') attachViewDocsHandlers();
            }
            // update URL in address bar
            const newUrl = url + (params ? ('?' + params) : '');
            window.history.replaceState({}, '', newUrl);
        } catch (err) {
            console.error('AJAX search failed', err);
            // fallback to full submit
            form.submit();
        }
    }

    form.addEventListener('submit', submitAjax);
})();

const modalEndpoints = {
    view: @json(route('consentreview.modals.view')),
};

const consentReviewBaseUrl = @json(url('/consent-review'));

let viewConsentModalLoadPromise = null;

function getModalMount() {
    let mount = document.getElementById('modalMount');
    if (!mount) {
        mount = document.createElement('div');
        mount.id = 'modalMount';
        document.body.appendChild(mount);
    }
    return mount;
}

async function loadModalHtml(url) {
    const response = await fetch(url, { headers: { 'Accept': 'text/html' } });
    if (!response.ok) throw new Error('Failed to load modal');
    return response.text();
}

async function ensureViewConsentModalLoaded() {
    if (document.getElementById('viewConsentModal')) return;
    if (!viewConsentModalLoadPromise) {
        viewConsentModalLoadPromise = loadModalHtml(modalEndpoints.view).finally(() => {
            viewConsentModalLoadPromise = null;
        });
    }
    const html = await viewConsentModalLoadPromise;
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    getModalMount().append(...Array.from(wrapper.children));
    // attach close handlers
    const modal = document.getElementById('viewConsentModal');
    if (modal) {
        modal.querySelectorAll('.view-modal-close, #closeViewConsentFooter').forEach(btn => {
            btn.addEventListener('click', function () {
                modal.classList.remove('show');
                modal.style.display = 'none';
            });
        });
        // also close on backdrop click
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
            }
        });
    }
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

async function viewDocument(customer) {
    await ensureViewConsentModalLoaded();
    const viewModal = document.getElementById('viewConsentModal');
    const contentDiv = document.getElementById('viewConsentContent');

    let fullCustomer = customer;
    const customerId = customer?.id || customer;
    if (customerId) {
        try {
            const response = await fetch(`${consentReviewBaseUrl}/${customerId}/data`, { headers: { 'Accept': 'application/json' } });
            if (response.ok) {
                fullCustomer = await response.json();
            }
        } catch (error) {
        }
    }

    customer = fullCustomer;

    // Use same rendering as consent module (simplified copy)
    const title = customer.title || 'นาย/นาง/นางสาว';
    const name = customer.name || '-';
    const name_en = customer.name_en || '-';
    const birthdate = customer.birthdate ? new Date(customer.birthdate).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
    const appDateFormatted = customer.app_date ? new Date(customer.app_date).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
    const id_card = customer.id_card || customer.passport || '-';
    const nationality = customer.nationality || '-';
    const marital_status = customer.marital_status || '-';
    const education = customer.education || '-';
    const occupation = customer.occupation || '-';
    const income = customer.income ? parseInt(customer.income).toLocaleString('th-TH') + ' บาท' : '-';
    const incomeDocuments = Array.isArray(customer.incomeDocuments) ? customer.incomeDocuments : [];
    const incomeDocumentsHtml = incomeDocuments.length
        ? incomeDocuments.map(function (document) {
            const url = document?.downloadUrl ?? '#';
            const name = document?.originalName ?? 'ไฟล์แนบ';
            return `<div class="file-link"><a href="${url}" target="_blank" rel="noopener" class="file-link__anchor">${escapeHtml(name)}</a></div>`;
        }).join('')
        : '-';

    const officer_name = customer.officer_name || '-';
    const officer_phone = customer.officer_phone || '-';

    const residence_status = customer.residence_status || '-';
    const address_room = customer.address_room || '-';
    const address_no = customer.address_no || '-';
    const address_floor = customer.address_floor || '-';
    const address_village = customer.address_village || '-';
    const address_building = customer.address_building || '-';
    const address_soi = customer.address_soi || '-';
    const address_road = customer.address_road || '-';
    const address_subdistrict = customer.address_subdistrict || '-';
    const address_district = customer.address_district || '-';
    const address_province = customer.address_province || '-';
    const address_postal = customer.address_postal || '-';
    const phone_home = customer.phone_home || '-';
    const phone_mobile = customer.phone_mobile || '-';

    // App No boxes
    const appNoStr = (customer.app_no || '').padEnd(13, ' ');
    let appNoBoxesHtml = '<div class="appno-box-container">';
    for (let i = 0; i < 13; i++) {
        const char = appNoStr[i].trim() ? appNoStr[i] : '&nbsp;';
        appNoBoxesHtml += `<span class="appno-box">${char}</span>`;
    }
    appNoBoxesHtml += '</div>';

    contentDiv.innerHTML = `
        <div class="consent-header">
            <div>
                <div class="consent-header__title">บริษัท บิ๊ก มันนี่ พลัส จำกัด</div>
                <div class="consent-header__subtitle">ใบคำขอให้บริการสินเชื่อส่วนบุคคล (Personal Loan)</div>
                <div class="consent-header__meta">App No.: ${customer.app_no || '-'}</div>
            </div>
            <div class="consent-header__right">
                <div class="consent-meta">วันที่: <span class="meta-value">${appDateFormatted}</span></div>
                <div class="consent-appno-row">
                    <span class="meta-label">App No.</span>
                    ${appNoBoxesHtml}
                </div>
            </div>
        </div>
        <div class="panel">
            <h4 class="section-title section-title--accent">ส่วนที่ 2: ข้อมูลส่วนตัวผู้ขอสินเชื่อ</h4>
            <table class="consent-detail-table">
                <tr>
                    <td class="label">คำนำหน้านาม - ชื่อ - นามสกุล:</td>
                    <td class="value">${title} ${name}</td>
                </tr>
                <tr>
                    <td class="label">Name - Surname (EN):</td>
                    <td class="value value--uppercase">${name_en}</td>
                </tr>
                <tr>
                    <td class="label">เลขประจำตัวประชาชน:</td>
                    <td class="value">${id_card}</td>
                </tr>
                <tr>
                    <td class="label">วัน / เดือน / ปีเกิด:</td>
                    <td class="value">${birthdate}</td>
                </tr>
                <tr>
                    <td class="label">รายได้รวมต่อเดือน:</td>
                    <td class="value value--strong">${income}</td>
                </tr>
                <tr>
                    <td class="label">ไฟล์หลักฐานการเงิน:</td>
                    <td class="value">${incomeDocumentsHtml}</td>
                </tr>
            </table>
        </div>
    `;

    if (viewModal) {
        viewModal.style.display = 'flex';
        // use CSS show class for transition
        requestAnimationFrame(() => viewModal.classList.add('show'));
    }
}

function attachViewDocsHandlers() {
    document.querySelectorAll('.view-docs').forEach(function (btn) {
        btn.removeEventListener('click', btn._consentHandler);
        btn._consentHandler = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.getAttribute('data-id');
            viewDocument(id);
        };
        btn.addEventListener('click', btn._consentHandler);
    });
}

// Attach on load and after any potential DOM updates
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', attachViewDocsHandlers);
} else {
    attachViewDocsHandlers();
}

function attachPaginationHandlers(container) {
    const root = container || document;
    root.querySelectorAll('.pagination a, .consent-table a').forEach(function (link) {
        link.removeEventListener('click', link._paginationHandler);
        link._paginationHandler = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const href = link.getAttribute('href');
            if (!href) return;
            fetch(href, { headers: { 'Accept': 'text/html' } })
                .then(r => r.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newTable = doc.querySelector('.consent-table');
                    const oldTable = document.querySelector('.consent-table');
                    if (newTable && oldTable) {
                        oldTable.replaceWith(newTable);
                        if (typeof attachViewDocsHandlers === 'function') attachViewDocsHandlers();
                        if (typeof attachPaginationHandlers === 'function') attachPaginationHandlers(newTable);
                    }
                    window.history.replaceState({}, '', href);
                })
                .catch(err => {
                    console.error('Pagination AJAX failed', err);
                    window.location.href = href; // fallback
                });
        };
        link.addEventListener('click', link._paginationHandler);
    });
}

// initial pagination attach
attachPaginationHandlers();
</script>

<script>
// Reset filters without navigating away
document.getElementById('reset-filters')?.addEventListener('click', function () {
    const form = document.querySelector('.search-filter-form');
    if (!form) return;
    form.querySelectorAll('input[name], select[name]').forEach(el => {
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
    });
    // Use requestSubmit (triggers submit event and handlers). Fallback to dispatching event.
    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    }
});
</script>

<div id="modalMount"></div>

@endsection
