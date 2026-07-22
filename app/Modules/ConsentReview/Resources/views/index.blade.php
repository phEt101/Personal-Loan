@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/review.css') }}">
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
                <label for="search-officer-group" class="filter-label">{{ __('consentreview::messages.officer_group') }}</label>
                <select id="search-officer-group" name="officer_group_id" class="filter-input">
                    <option value="">{{ __('consentreview::messages.officer_group_all') }}</option>
                    @foreach($officerGroups as $group)
                        <option value="{{ $group->id }}" {{ request('officer_group_id') == $group->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'th' ? $group->name_th : $group->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="search-product-type" class="filter-label">{{ __('consentreview::messages.product_type') }}</label>
                <select id="search-product-type" name="loan_product_id" class="filter-input">
                    <option value="">{{ __('consentreview::messages.product_type_all') }}</option>
                    @foreach($loanProducts as $product)
                        <option value="{{ $product->id }}" {{ request('loan_product_id') == $product->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'th' ? $product->name_th : $product->name_en }}
                        </option>
                    @endforeach
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
                    <td>
                        {{ app()->getLocale() === 'th' ? ($c->officerGroup?->name_th ?? '-') : ($c->officerGroup?->name_en ?? '-') }}
                    </td>
                    <td>
                        @php
                            $loanReq = $c->loanRequest;
                            $displayAmount = match($loanReq?->loan_amount_type) {
                                'full' => $loanReq->calculated_eligible_amount,
                                'custom' => $loanReq->custom_loan_amount,
                                default => null
                            };
                        @endphp
                        {{ $displayAmount ? number_format($displayAmount, 0) . ' บาท' : '-' }}
                    </td>
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

    <div id="modalMount"></div>
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

    // Ensure JSZip is available for client-side ZIP extraction
    let jszipLoadPromise = null;
    function ensureJSZipLoaded() {
        if (window.JSZip) return Promise.resolve(window.JSZip);
        if (jszipLoadPromise) return jszipLoadPromise;
        jszipLoadPromise = new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = '{{ asset('js/jszip.min.js') }}';
            s.onload = () => resolve(window.JSZip);
            s.onerror = reject;
            document.head.appendChild(s);
        });
        return jszipLoadPromise;
    }

    // Helper: build HTML for documents, extracting ZIPs if needed
    async function buildDocumentsHtml(docs) {
        if (!Array.isArray(docs) || docs.length === 0) return '-';
        await ensureJSZipLoaded().catch(() => {});
        const JSZip = window.JSZip;
        const parts = [];
        for (const doc of docs) {
            const url = doc?.downloadUrl ?? '#';
            const name = doc?.originalName ?? 'ไฟล์แนบ';
            const lower = (url || '').toLowerCase();
            const nameLower = (name || '').toLowerCase();
            if ((lower.endsWith('.zip') || nameLower.endsWith('.zip')) && JSZip) {
                try {
                    const resp = await fetch(url);
                    if (!resp.ok) {
                        parts.push(`<div class="file-link"><a href="${escapeHtml(url)}" target="_blank" rel="noopener">${escapeHtml(name)} (ดาวน์โหลด)</a></div>`);
                        continue;
                    }
                    const blob = await resp.blob();
                    const zip = await JSZip.loadAsync(blob);
                    await Promise.all(Object.keys(zip.files).map(async (filename) => {
                        const fileObj = zip.files[filename];
                        if (fileObj.dir) return;
                        const arrayBuf = await fileObj.async('arraybuffer');
                        // guess mime type from extension
                        const ext = (filename.split('.').pop() || '').toLowerCase();
                        const mimeMap = {
                            'png': 'image/png', 'jpg': 'image/jpeg', 'jpeg': 'image/jpeg', 'gif': 'image/gif',
                            'pdf': 'application/pdf', 'txt': 'text/plain', 'csv': 'text/csv', 'xls': 'application/vnd.ms-excel',
                            'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        };
                        const mime = mimeMap[ext] || 'application/octet-stream';
                        const fileBlob = new Blob([arrayBuf], { type: mime });
                        const fileUrl = URL.createObjectURL(fileBlob);
                        parts.push(`<div class="file-link"><a href="${fileUrl}" target="_blank" rel="noopener">${escapeHtml(filename)}</a></div>`);
                    }));
                } catch (e) {
                    parts.push(`<div class="file-link"><a href="${escapeHtml(url)}" target="_blank" rel="noopener">${escapeHtml(name)} (ไม่สามารถแตกไฟล์ได้)</a></div>`);
                }
            } else {
                parts.push(`<div class="file-link"><a href="${escapeHtml(url)}" target="_blank" rel="noopener" class="file-link__anchor">${escapeHtml(name)}</a></div>`);
            }
        }
        return parts.join('') || '-';
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
        const extraIncome = customer.extraIncome ? parseInt(customer.extraIncome).toLocaleString('th-TH') + ' บาท' : '-';
        const otherDebtInstallment = customer.otherDebtInstallment ? parseInt(customer.otherDebtInstallment).toLocaleString('th-TH') + ' บาท' : '-';

        const officer_name = customer.officer_name || '-';
        const officer_phone = customer.officer_phone || '-';
        const officer_group = customer.officer_group_name || '-';
        const product_type = customer.loan_product_name || '-';
        const has_other_debts = customer.hasOtherDebts || '-';
        const customLoanAmount = customer.customLoanAmount ? parseInt(customer.customLoanAmount).toLocaleString('th-TH') + ' บาท' : '-';
        const calculatedEligibleAmount = customer.calculatedEligibleAmount ? parseInt(customer.calculatedEligibleAmount).toLocaleString('th-TH') + ' บาท' : '-';

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

        // Render attachments (incomeDocuments) by expanding ZIPs when possible
        let attachmentsHtml = '';
        if (Array.isArray(customer.incomeDocuments) && customer.incomeDocuments.length) {
            const identityKeywords = /(id|identity|passport|บัตร|หลักฐาน|身份证|身份证明)/i;
            const identityCandidates = [];
            const incomeOnly = [];
            customer.incomeDocuments.forEach(function(doc) {
                const name = (doc?.originalName || '').toString();
                if (identityKeywords.test(name)) identityCandidates.push(doc);
                else incomeOnly.push(doc);
            });

            const incomeDocumentsHtml = await buildDocumentsHtml(incomeOnly);
            const identityDocumentsHtml = await buildDocumentsHtml(identityCandidates);

            attachmentsHtml = `
                <div class="panel">
                    <h4 class="section-title">เอกสารแนบ</h4>
                    ${incomeOnly.length ? `<div class="attachments-group"><strong>เอกสารแสดงรายได้</strong>${incomeDocumentsHtml}</div>` : ''}
                    ${identityCandidates.length ? `<div class="attachments-group"><strong>เอกสารแสดงตน</strong>${identityDocumentsHtml}</div>` : ''}
                </div>
            `;
        }

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
                <h4 class="section-title section-title--accent">รายละเอียดสัญญา</h4>
                <table class="consent-detail-table">
                    <tr>
                        <td class="label">กลุ่มเจ้าหน้าที่:</td>
                        <td class="value">${officer_group}</td>
                    </tr>
                    <tr>
                        <td class="label">ประเภทผลิตภัณฑ์:</td>
                        <td class="value">${product_type}</td>
                    </tr>
                    <tr>
                        <td class="label">วงเงินกู้ที่ระบุ:</td>
                        <td class="value">${customLoanAmount}</td>
                    </tr>
                    <tr>
                        <td class="label">วงเงินสูงสุดที่คำนวณได้:</td>
                        <td class="value value--strong" style="color: #059669;">${calculatedEligibleAmount}</td>
                    </tr>
                    <tr>
                        <td class="label">เลขที่ใบคำขอ:</td>
                        <td class="value">${customer.app_no || '-'}</td>
                    </tr>
                </table>
            </div>

            </div>

            <div class="panel">
                <h4 class="section-title section-title--accent">ข้อมูลส่วนตัวผู้ขอสินเชื่อ</h4>
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
                        <td class="label">สัญชาติ:</td>
                        <td class="value">${nationality}</td>
                    </tr>
                    <tr>
                        <td class="label">การศึกษา:</td>
                        <td class="value">${education}</td>
                    </tr>
                    <tr>
                        <td class="label">อาชีพ:</td>
                        <td class="value">${occupation}</td>
                    </tr>
                    <tr>
                        <td class="label">รายได้รวมต่อเดือน:</td>
                        <td class="value value--strong">${income}</td>
                    </tr>
                    <tr>
                        <td class="label">รายได้อื่นๆ:</td>
                        <td class="value">${extraIncome}</td>
                    </tr>
                    <tr>
                        <td class="label">ภาระหนี้อื่นๆ ในปัจจุบัน:</td>
                        <td class="value">${has_other_debts}</td>
                    </tr>
                    <tr>
                        <td class="label">ยอดค่างวดหนี้อื่นๆ:</td>
                        <td class="value">${otherDebtInstallment}</td>
                    </tr>
                </table>
            </div>

            <div class="calc-panel">
                <h4 class="section-title">เงื่อนไขการคำนวณ</h4>
                <div class="calc-grid">
                    <div class="calc-field">
                        <label>ดอกเบี้ยเงินกู้</label>
                        <div class="calc-input-wrapper">
                            <input type="number" step="0.01" id="calc_interest_rate" value="${customer.loanApproval?.interest_rate ? parseFloat(customer.loanApproval.interest_rate).toFixed(2) : (customer.interest_rate_cap ? parseFloat(customer.interest_rate_cap).toFixed(2) : '20.00')}">
                            <span class="unit">% ต่อปี</span>
                        </div>
                    </div>
                    <div class="calc-field">
                        <label>ค่าธรรมเนียม</label>
                        <div class="calc-input-wrapper">
                            <input type="number" step="0.01" id="calc_fee_rate" value="${customer.loanApproval?.fee_rate ? parseFloat(customer.loanApproval.fee_rate).toFixed(2) : (customer.fee_rate ? parseFloat(customer.fee_rate).toFixed(2) : '1.00')}">
                            <span class="unit">% ต่อปี</span>
                        </div>
                    </div>
                    <div class="calc-field">
                        <label>วงเงินสินเชื่อ</label>
                        <div class="calc-input-wrapper">
                            <input type="number" step="0.01" id="calc_loan_amount" value="${customer.loanApproval?.loan_amount ? parseFloat(customer.loanApproval.loan_amount).toFixed(2) : (customer.loan_amount ? parseFloat(customer.loan_amount).toFixed(2) : '0.00')}">
                            <span class="unit">บาท</span>
                        </div>
                    </div>
                    <div class="calc-field">
                        <label>เบี้ยปรับล่าช้า</label>
                        <div class="calc-input-wrapper">
                            <input type="number" step="0.01" id="calc_late_penalty_rate" value="${customer.loanApproval?.late_penalty_rate ? parseFloat(customer.loanApproval.late_penalty_rate).toFixed(2) : (customer.late_penalty_rate ? parseFloat(customer.late_penalty_rate).toFixed(2) : '3.00')}">
                            <span class="unit">% ต่อปี</span>
                        </div>
                    </div>
                    <div class="calc-field">
                        <label>จำนวนงวดการผ่อน</label>
                        <div class="calc-input-wrapper">
                            <input type="number" id="calc_installments" value="${customer.loanApproval?.installments || customer.loanTerm || customer.installments || '12'}">
                            <span class="unit">งวด</span>
                        </div>
                    </div>
                </div>

                <div class="calc-actions">
                    <button type="button" class="btn-calc" id="btn_run_calc">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="16" y1="14" x2="16" y2="18"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/></svg>
                        คำนวณ
                    </button>
                </div>

                <div class="calc-results">
                    <div class="result-field">
                        <label>ค่างวดที่ลูกค้าสามารถจ่ายได้</label>
                        <div class="calc-input-wrapper">
                            <input type="text" id="res_monthly_payment" value="${customer.loanApproval?.monthly_payment ? parseFloat(customer.loanApproval.monthly_payment).toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) : ''}" readonly>
                            <span class="unit">บาท</span>
                        </div>
                    </div>
                    <div class="result-field">
                        <label>ค่างวดต่อเดือน (ก่อนปัดเศษ)</label>
                        <div class="calc-input-wrapper">
                            <input type="text" id="res_monthly_payment_raw" value="${customer.loanApproval?.monthly_payment_raw ? parseFloat(customer.loanApproval.monthly_payment_raw).toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) : ''}" readonly>
                            <span class="unit">บาท</span>
                        </div>
                    </div>
                    <div class="result-field">
                        <label>ค่างวดต่อเดือน (หลังปัดเศษ)</label>
                        <div class="calc-input-wrapper">
                            <input type="text" id="res_monthly_payment_rounded" value="${customer.loanApproval?.monthly_payment ? parseFloat(customer.loanApproval.monthly_payment).toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) : ''}" readonly>
                            <span class="unit">บาท</span>
                        </div>
                    </div>
                    <div class="result-field">
                        <label>ดอกเบี้ยและค่าธรรมเนียมทั้งสัญญา</label>
                        <div class="calc-input-wrapper">
                            <input type="text" id="res_total_interest" value="${customer.loanApproval?.total_interest ? parseFloat(customer.loanApproval.total_interest).toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) : ''}" readonly>
                            <span class="unit">บาท</span>
                        </div>
                    </div>
                    <div class="result-field">
                        <label>มูลค่าสัญญาเงินกู้</label>
                        <div class="calc-input-wrapper">
                            <input type="text" id="res_total_contract" value="${customer.loanApproval?.total_contract_amount ? parseFloat(customer.loanApproval.total_contract_amount).toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) : ''}" readonly>
                            <span class="unit">บาท</span>
                        </div>
                    </div>
                </div>

                <div class="schedule-section">
                    <div class="schedule-title" style="cursor: pointer;" onclick="document.querySelector('.schedule-table-wrapper').classList.toggle('show')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        ตารางภาระหนี้
                    </div>
                    <div class="schedule-table-wrapper">
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>งวดที่</th>
                                    <th>วันครบกำหนดชำระ</th>
                                    <th>เงินค่างวด</th>
                                    <th>เงินต้น</th>
                                    <th>ดอกเบี้ย</th>
                                    <th>ค่าธรรมเนียม</th>
                                    <th>เงินต้นคงเหลือ</th>
                                </tr>
                            </thead>
                            <tbody id="schedule_body">
                                <!-- Generated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                ${attachmentsHtml}

                <div class="panel-actions" style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                </div>
            </div>
        `;

        if (viewModal) {
            viewModal.style.display = 'flex';
            requestAnimationFrame(() => viewModal.classList.add('show'));
        }

        // Attach calculation logic
        const btnCalc = document.getElementById('btn_run_calc');
        if (btnCalc) {
            btnCalc.onclick = function() {
                runLoanCalculation(customer);
            };
        }

        // Attach save logic
        const btnSave = document.getElementById('btn_save_approval');
        if (btnSave) {
            btnSave.onclick = function() {
                saveLoanApproval(customer.id);
            };
        }

        // If existing approval, run calculation to show table (or we could render it manually)
        if (customer.loanApproval) {
            runLoanCalculation(customer);
        }
    }

    function runLoanCalculation(customer) {
        const loanAmount = parseFloat(document.getElementById('calc_loan_amount').value) || 0;
        const interestRateYear = parseFloat(document.getElementById('calc_interest_rate').value) || 0;
        const feeRateYear = parseFloat(document.getElementById('calc_fee_rate').value) || 0;
        const installments = parseInt(document.getElementById('calc_installments').value) || 0;

        if (loanAmount <= 0 || (interestRateYear + feeRateYear) <= 0 || installments <= 0) {
            alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            return;
        }

        const totalRateYear = interestRateYear + feeRateYear;
        const rateMonth = (totalRateYear / 100) / 12;
        
        // PMT Formula: P * r * (1 + r)^n / ((1 + r)^n - 1)
        const pmt = loanAmount * rateMonth * Math.pow(1 + rateMonth, installments) / (Math.pow(1 + rateMonth, installments) - 1);
        const pmtRounded = Math.ceil(pmt);

        document.getElementById('res_monthly_payment').value = pmtRounded.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('res_monthly_payment_raw').value = pmt.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('res_monthly_payment_rounded').value = pmtRounded.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Generate Schedule
        const tbody = document.getElementById('schedule_body');
        tbody.innerHTML = '';
        
        let remainingPrincipal = loanAmount;
        let totalInterest = 0;
        let totalFee = 0;
        let totalPaid = 0;
        
        const contractDate = new Date();
        let lastDate = new Date(contractDate);
        
        for (let i = 1; i <= installments; i++) {
            const dueDate = new Date(contractDate);
            dueDate.setMonth(contractDate.getMonth() + i);
            
            // คำนวณจำนวนวันในงวดนี้
            const diffTime = Math.abs(dueDate - lastDate);
            const daysInPeriod = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            let interest = remainingPrincipal * (interestRateYear / 100) * daysInPeriod / 365;
            let fee = remainingPrincipal * (feeRateYear / 100) * daysInPeriod / 365;
            
            // Round to 2 decimals
            interest = Math.round(interest * 100) / 100;
            fee = Math.round(fee * 100) / 100;
            
            let payment = pmtRounded;
            if (i === installments) {
                // งวดสุดท้ายปรับยอดให้เงินต้นเหลือ 0
                payment = remainingPrincipal + interest + fee;
            }
            
            let principal = payment - interest - fee;
            principal = Math.round(principal * 100) / 100;

            remainingPrincipal -= principal;
            remainingPrincipal = Math.round(remainingPrincipal * 100) / 100;
            if (remainingPrincipal < 0) remainingPrincipal = 0;

            totalInterest += interest;
            totalFee += fee;
            totalPaid += payment;

            lastDate = new Date(dueDate);

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${i}</td>
                <td>${dueDate.toLocaleDateString('th-TH')}</td>
                <td>${payment.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>${principal.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>${interest.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>${fee.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>${remainingPrincipal.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            `;
            tbody.appendChild(row);
        }

        const totalRow = document.createElement('tr');
        totalRow.className = 'total-row';
        totalRow.innerHTML = `
            <td colspan="3">ยอดทั้งหมด</td>
            <td>${loanAmount.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td>${totalInterest.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td>${totalFee.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td>0.00</td>
        `;
        tbody.appendChild(totalRow);

        document.getElementById('res_total_interest').value = (totalInterest + totalFee).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('res_total_contract').value = totalPaid.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        // Show table
        document.querySelector('.schedule-table-wrapper').classList.add('show');
    }

    async function saveLoanApproval(applicationId) {
        const data = {
            application_id: applicationId,
            interest_rate: document.getElementById('calc_interest_rate').value,
            fee_rate: document.getElementById('calc_fee_rate').value,
            loan_amount: document.getElementById('calc_loan_amount').value,
            late_penalty_rate: document.getElementById('calc_late_penalty_rate').value,
            installments: document.getElementById('calc_installments').value,
            monthly_payment: document.getElementById('res_monthly_payment_rounded').value.replace(/,/g, ''),
            monthly_payment_raw: document.getElementById('res_monthly_payment_raw').value.replace(/,/g, ''),
            total_interest: document.getElementById('res_total_interest').value.replace(/,/g, ''),
            total_contract_amount: document.getElementById('res_total_contract').value.replace(/,/g, ''),
            schedule: []
        };

        // Extract schedule
        document.querySelectorAll('#schedule_body tr:not(.total-row)').forEach(row => {
            const cols = row.querySelectorAll('td');
            data.schedule.push({
                installment_no: cols[0].innerText,
                due_date: cols[1].innerText, // Should probably convert back to Y-m-d
                payment_amount: cols[2].innerText.replace(/,/g, ''),
                principal_amount: cols[3].innerText.replace(/,/g, ''),
                interest_amount: cols[4].innerText.replace(/,/g, ''),
                fee_amount: cols[5].innerText.replace(/,/g, ''),
                remaining_principal: cols[6].innerText.replace(/,/g, '')
            });
        });

        if (!data.monthly_payment) {
            alert('กรุณากดคำนวณก่อนบันทึก');
            return;
        }

        try {
            const response = await fetch(`${consentReviewBaseUrl}/${applicationId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                alert('บันทึกการอนุมัติสำเร็จ');
                document.getElementById('viewConsentModal').classList.remove('show');
                // Refresh list
                location.reload();
            } else {
                const err = await response.json();
                alert('เกิดข้อผิดพลาด: ' + (err.message || 'Unknown error'));
            }
        } catch (error) {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
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

@endsection
