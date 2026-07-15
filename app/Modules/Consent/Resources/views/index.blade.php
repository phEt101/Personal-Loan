@extends('layouts.app', ['title' => __('consent::messages.index.page_title')])

@section('content')
    <section class="dashboard">
        <div class="hero compact-hero hero-with-actions">
            <div class="hero-body">
                <h2>{{ __('consent::messages.index.hero_title') }}</h2>
                <p>{{ __('consent::messages.index.hero_subtitle') }}</p>
            </div>
            <div class="hero-actions">
                <button type="button" id="openConsentModal" class="action-btn">{{ __('consent::messages.index.create_button') }}</button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <div class="alert-title">{{ __('consent::messages.index.save_failed') }}</div>
                <ul class="alert-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="summary-cards compact-summary">
            <div class="summary-card">
                <div class="summary-label">{{ __('consent::messages.index.summary_total') }}</div>
                <div class="summary-value">{{ $total }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">{{ __('consent::messages.index.summary_approved') }}</div>
                <div class="summary-value">{{ $approved }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">{{ __('consent::messages.index.summary_rejected') }}</div>
                <div class="summary-value">{{ $rejected }}</div>
            </div>
        </section>

        {{-- styles moved to public/css/consent.css --}}

        <!-- Search & Filter Section -->
        <div class="search-filter-card">
            <form method="GET" action="{{ route('consent.index') }}" class="search-filter-form">
                <div>
                    <label class="filter-label" for="search_q">{{ __('consent::messages.index.search.label') }}</label>
                    <input type="text" id="search_q" name="q" value="{{ request('q') }}" class="filter-input" placeholder="{{ __('consent::messages.index.search.placeholder') }}">
                </div>
                <div>
                    <label class="filter-label" for="search_status">{{ __('consent::messages.index.table.status') }}</label>
                    <select id="search_status" name="status" class="filter-input">
                        <option value="">{{ __('consent::messages.index.search.status_select') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('consent::messages.index.status.approved') }}</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('consent::messages.index.status.rejected') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('consent::messages.index.status.pending') }}</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('consent::messages.index.status.draft') }}</option>
                    </select>
                </div>
                <div>
                    <label class="filter-label" for="search_date_from">{{ __('consent::messages.index.search.date_from') }}</label>
                    <input type="date" id="search_date_from" name="date_from" value="{{ request('date_from') }}" class="filter-input">
                </div>
                <div>
                    <label class="filter-label" for="search_date_to">{{ __('consent::messages.index.search.date_to') }}</label>
                    <input type="date" id="search_date_to" name="date_to" value="{{ request('date_to') }}" class="filter-input">
                </div>
                <div class="search-actions-group">
                    <button type="submit" class="btn btn-search">{{ __('consent::messages.index.search.submit') }}</button>
                    <a href="{{ route('consent.index') }}" class="btn btn-reset">{{ __('consent::messages.index.search.reset') }}</a>
                </div>
            </form>
        </div>

        <div class="card card--relative">
            <div id="tableLoadingOverlay" class="table-loading-overlay">
                <div class="loading-spinner"></div>
            </div>
            <div class="consent-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('consent::messages.index.table.code') }}</th>
                            <th>{{ __('consent::messages.index.table.name') }}</th>
                            <th>{{ __('consent::messages.index.table.date') }}</th>
                            <th>{{ __('consent::messages.index.table.status') }}</th>
                            <th>{{ __('consent::messages.index.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td data-label="{{ __('consent::messages.index.table.code') }}" class="no-auto-link">{{ $customer->app_no ?? '-' }}</td>
                                <td data-label="{{ __('consent::messages.index.table.name') }}">{{ $customer->applicant?->name ?? '-' }}</td>
                                <td data-label="{{ __('consent::messages.index.table.date') }}">{{ $customer->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td data-label="{{ __('consent::messages.index.table.status') }}">
                                    @if($customer->status === 'approved')
                                        <span class="badge badge-signed">{{ __('consent::messages.index.status.approved') }}</span>
                                    @elseif($customer->status === 'rejected')
                                        <span class="badge badge-pending">{{ __('consent::messages.index.status.rejected') }}</span>
                                    @else
                                        <span class="badge">{{ __('consent::messages.index.status.pending') }}</span>
                                    @endif
                                </td>
                                <td data-label="{{ __('consent::messages.index.table.actions') }}">
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="action-btn outline table-action-btn-small"
                                            onclick="viewDocument({ id: {{ Js::from($customer->encrypted_id) }} }); return false;"
                                        >
                                            {{ __('consent::messages.index.actions.view') }}
                                        </button>
                                        <button
                                            type="button"
                                            class="action-btn table-action-btn-small"
                                            onclick="editDocument({ id: {{ Js::from($customer->encrypted_id) }} }); return false;"
                                        >
                                            {{ __('consent::messages.index.actions.edit') }}
                                        </button>
                                        <form method="POST" action="{{ route('consent.destroy', $customer->encrypted_id) }}" class="delete-consent-form table-actions-form" data-name="{{ $customer->applicant?->name ?? '-' }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="action-btn table-action-btn-delete"
                                            >
                                                {{ __('consent::messages.index.actions.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-cell">{{ __('consent::messages.index.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($customers, 'links'))
            <div class="pagination-container">
                @if ($customers->hasPages())
                    <nav class="pagination-nav" role="navigation" aria-label="{{ __('consent::messages.pagination.navigation') }}">
                        <div class="pagination-summary">
                            {{ __('consent::messages.pagination.showing') }}
                            @if ($customers->firstItem())
                                <span>{{ $customers->firstItem() }}</span>
                                {{ __('consent::messages.pagination.to') }}
                                <span>{{ $customers->lastItem() }}</span>
                            @else
                                <span>{{ $customers->count() }}</span>
                            @endif
                            {{ __('consent::messages.pagination.of') }}
                            <span>{{ $customers->total() }}</span>
                            {{ __('consent::messages.pagination.results') }}
                        </div>

                        <div class="pagination-list">
                            <a
                                href="{{ $customers->previousPageUrl() ?: '#' }}"
                                class="pagination-link {{ $customers->onFirstPage() ? 'is-disabled' : '' }}"
                                rel="prev"
                                aria-label="{{ __('consent::messages.pagination.previous') }}"
                                @if($customers->onFirstPage()) aria-disabled="true" tabindex="-1" @endif
                            >
                                {{ __('consent::messages.pagination.previous') }}
                            </a>

                            @foreach ($customers->getUrlRange(max(1, $customers->currentPage() - 1), min($customers->lastPage(), $customers->currentPage() + 1)) as $page => $url)
                                @if ($page === $customers->currentPage())
                                    <span class="pagination-current" aria-current="page">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="pagination-link" aria-label="{{ __('consent::messages.pagination.go_to_page', ['page' => $page]) }}">{{ $page }}</a>
                                @endif
                            @endforeach

                            <a
                                href="{{ $customers->nextPageUrl() ?: '#' }}"
                                class="pagination-link {{ $customers->hasMorePages() ? '' : 'is-disabled' }}"
                                rel="next"
                                aria-label="{{ __('consent::messages.pagination.next') }}"
                                @unless($customers->hasMorePages()) aria-disabled="true" tabindex="-1" @endunless
                            >
                                {{ __('consent::messages.pagination.next') }}
                            </a>
                        </div>
                    </nav>
                @endif
            </div>
        @endif
    </section>

    <div id="modalMount"></div>

    <!-- PDF Consent Modal -->
    <div id="pdfConsentModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('consent::messages.modal.pdf.title') }}</h3>
                <button type="button" class="close-btn" id="closePdfModal" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body modal-body--pdf">
                <div class="pdf-content-padding">
                    <h4 class="pdf-section-title">1. Sale Sheet</h4>
                    <div class="pdf-iframe-wrapper">
                         <iframe src="{{ asset('file/Sale Sheet - BMPver. 2_Personal Loan_App - Eng.pdf') }}#toolbar=1&navpanes=0&view=FitH" class="iframe-reset" title="Sale Sheet"></iframe>
                    </div>

                    <h4 class="pdf-section-title">2. เงื่อนไขและข้อตกลงการสมัคร</h4>
                    <div class="pdf-iframe-wrapper">
                         <iframe src="{{ asset('file/ใบสมัคร BMPver. 2_Personal Loan_App - Eng 4-5.pdf') }}#toolbar=1&navpanes=0&view=FitH" class="iframe-reset" title="Terms and Conditions"></iframe>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-actions modal-form-actions">
                    <button type="button" class="action-btn outline" id="cancelPdfModal">{{ __('consent::messages.modal.pdf.cancel') }}</button>
                    <button type="button" class="action-btn" id="proceedToConsentModal">{{ __('consent::messages.modal.pdf.proceed') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/signature_pad.umd.min.js') }}"></script>

    <script>
        const modalEndpoints = {
            form: @json(route('consent.modals.form')),
            view: @json(route('consent.modals.view')),
            saveStep: @json(route('consent.save-step')),
        };

        const consentI18n = {
            form: {
                createTitle: @json(__('consent::messages.modal.form.create_title')),
                editTitle: @json(__('consent::messages.modal.form.edit_title')),
                submitCreate: @json(__('consent::messages.modal.form.buttons.submit_create')),
                submitEdit: @json(__('consent::messages.modal.form.buttons.submit_edit')),
                step1: {
                    idCardNumber: @json(__('consent::messages.modal.form.step1.fields.id_card_number')),
                    passportNumber: @json(__('consent::messages.modal.form.step1.fields.passport_number')),
                    idCardPlaceholder: @json(__('consent::messages.modal.form.step1.placeholders.id_card_number')),
                    passportPlaceholder: @json(__('consent::messages.modal.form.step1.placeholders.passport_number')),
                },
            },
        };

        const consentBaseUrl = @json(url('/consent'));
        let modalNextAppNo = '';

        let consentModalLoadPromise = null;
        let viewConsentModalLoadPromise = null;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

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
            if (!response.ok) {
                throw new Error('Failed to load modal');
            }
            return response.text();
        }

        async function ensureConsentModalLoaded() {
            if (document.getElementById('consentModal')) return;
            if (!consentModalLoadPromise) {
                consentModalLoadPromise = loadModalHtml(modalEndpoints.form).finally(() => {
                    consentModalLoadPromise = null;
                });
            }
            const html = await consentModalLoadPromise;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            getModalMount().append(...Array.from(wrapper.children));
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
        }

        // JS Function to show details modal
        async function viewDocument(customer) {
            await ensureViewConsentModalLoaded();
            const viewModal = document.getElementById('viewConsentModal');
            const contentDiv = document.getElementById('viewConsentContent');

            let fullCustomer = customer;
            const customerId = customer?.id;
            if (customerId) {
                try {
                    const baseUrl = consentBaseUrl;
                    const response = await fetch(`${baseUrl}/${customerId}/data`, { headers: { 'Accept': 'application/json' } });
                    if (response.ok) {
                        fullCustomer = await response.json();
                    }
                } catch (error) {
                }
            }

            customer = fullCustomer;
            
            const title = customer.title || 'นาย/นาง/นางสาว';
            const name = customer.name || '-';
            const name_en = customer.name_en || '-';
            
            // Format Dates
            const birthdate = customer.birthdate ? new Date(customer.birthdate).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
            const appDateFormatted = customer.app_date ? new Date(customer.app_date).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
            
            const id_card = customer.id_card || customer.passport || '-';
            const nationality = customer.nationality || '-';
            const marital_status = customer.marital_status || '-';
            const education = customer.education || '-';
            const occupation = customer.occupation || '-';
            const governmentLevel = customer.governmentLevel || '-';
            const occupationOther = customer.occupationOther || '-';
            const careerField = customer.careerField || '-';
            const careerFieldOther = customer.careerFieldOther || '-';
            const income = customer.income ? parseInt(customer.income).toLocaleString('th-TH') + ' บาท' : '-';
            const extraIncome = customer.extraIncome ? parseInt(customer.extraIncome).toLocaleString('th-TH') + ' บาท' : '-';
            const extraIncomeSource = customer.extraIncomeSource || '-';
            const incomeCountry = customer.incomeCountry || '-';
            const hasOtherDebts = customer.hasOtherDebts || '-';
            const otherDebtInstallment = customer.otherDebtInstallment ? parseInt(customer.otherDebtInstallment).toLocaleString('th-TH') + ' บาท' : '-';
            const hasExistingLoan = customer.hasExistingLoan || '-';
            const existingLoanInstitutionCount = customer.existingLoanInstitutionCount ?? '-';
            const existingLoanTotalAmount = customer.existingLoanTotalAmount ? parseInt(customer.existingLoanTotalAmount).toLocaleString('th-TH') + ' บาท' : '-';
            const incomeDocuments = Array.isArray(customer.incomeDocuments) ? customer.incomeDocuments : [];
            const incomeDocumentsHtml = incomeDocuments.length
                ? incomeDocuments
                    .map(function(document) {
                        const url = document?.downloadUrl ?? '#';
                        const name = document?.originalName ?? 'ไฟล์แนบ';
                        return `<div class="file-link"><a href="${url}" target="_blank" rel="noopener" class="file-link__anchor">${escapeHtml(name)}</a></div>`;
                    })
                    .join('')
                : '-';

            const signed_at = customer.signed_at || new Date().toISOString().split('T')[0];
            const signedDateFormatted = new Date(signed_at).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' });

            // Officer & Application Details
            const officer_name = customer.officer_name || '-';
            const officer_phone = customer.officer_phone || '-';
            
            // Address fields
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
            const email = customer.email || '-';
            const documentAddressText = customer.documentAddressText || '-';
            const documentAddressProvince = customer.documentAddressProvince || '-';
            const documentAddressPostal = customer.documentAddressPostal || '-';
            const birthPlaceAddress = customer.birthPlaceAddress || '-';
            // Work fields
            const companyName = customer.companyName || '-';
            const businessType = customer.businessType || '-';
            const workDepartment = customer.workDepartment || '-';
            const workYears = customer.workYears || '0';
            const workMonths = customer.workMonths || '0';
            const workAddressNo = customer.workAddressNo || '-';
            const workAddressFloor = customer.workAddressFloor || '-';
            const workAddressVillage = customer.workAddressVillage || '-';
            const workAddressBuilding = customer.workAddressBuilding || '-';
            const workAddressSoi = customer.workAddressSoi || '-';
            const workAddressRoad = customer.workAddressRoad || '-';
            const workAddressSubdistrict = customer.workAddressSubdistrict || '-';
            const workAddressDistrict = customer.workAddressDistrict || '-';
            const workAddressProvince = customer.workAddressProvince || '-';
            const workAddressPostal = customer.workAddressPostal || '-';
            const workPhone = customer.workPhone || '-';
            // Previous work fields
            const previousCompanyName = customer.previousCompanyName || '-';
            const previousPosition = customer.previousPosition || '-';
            const previousIncome = customer.previousIncome ? parseInt(customer.previousIncome).toLocaleString('th-TH') + ' บาท' : '-';
            const previousWorkAddress = customer.previousWorkAddress || '-';
            const previousPhone = customer.previousPhone || '-';
            // Document delivery fields
            const documentDelivery = customer.documentDelivery || '-';
            // Reference person fields
            const refName = customer.refName || '-';
            const refRelation = customer.refRelation || '-';
            const refAddressNo = customer.refAddressNo || '-';
            const refAddressFloor = customer.refAddressFloor || '-';
            const refAddressVillage = customer.refAddressVillage || '-';
            const refAddressBuilding = customer.refAddressBuilding || '-';
            const refAddressSoi = customer.refAddressSoi || '-';
            const refAddressRoad = customer.refAddressRoad || '-';
            const refAddressSubdistrict = customer.refAddressSubdistrict || '-';
            const refAddressDistrict = customer.refAddressDistrict || '-';
            const refAddressProvince = customer.refAddressProvince || '-';
            const refAddressPostal = customer.refAddressPostal || '-';
            const refPhoneHome = customer.refPhoneHome || '-';
            const refPhoneMobile = customer.refPhoneMobile || '-';
            // Loan request fields
            const loanTerm = customer.loanTerm ? `${customer.loanTerm} เดือน` : '-';
            const loanAmountType = customer.loanAmountType || '-';
            const customLoanAmount = customer.customLoanAmount ? parseInt(customer.customLoanAmount).toLocaleString('th-TH') + ' บาท' : '-';
            const loanPurpose = customer.loanPurpose || '-';
            const bankName = customer.bankName || '-';
            const accountName = customer.accountName || '-';
            const accountType = customer.accountType || '-';
            const accountNumber = customer.accountNumber || '-';
            const paymentMethod = customer.paymentMethod || '-';
            const directDebitAmount = customer.directDebitAmount ? parseInt(customer.directDebitAmount).toLocaleString('th-TH') : '.....................................';
            const directDebitAccountNumber = customer.directDebitAccountNumber || '.........................................';
            // Consent & signature fields
            const signatureData = customer.signatureData || null;
            const signed_date = customer.signed_date ? new Date(customer.signed_date).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
            
            // App No 13 digits box generator
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

                <!-- ส่วนที่ 1: สำหรับเจ้าหน้าที่บริษัท -->
                <div class="panel panel--green">
                    <h4 class="section-title section-title--green">ส่วนที่ 1: สำหรับเจ้าหน้าที่บริษัท</h4>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label">เจ้าหน้าที่สินเชื่อ:</td>
                            <td class="value">${officer_name}</td>
                            <td class="label label--right">เบอร์ติดต่อ:</td>
                            <td class="value value--padded">${officer_phone}</td>
                        </tr>
                    </table>
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
                            <td class="label">สัญชาติ:</td>
                            <td class="value">สัญชาติ ${nationality}</td>
                        </tr>
                        <tr>
                            <td class="label">สถานภาพสมรส:</td>
                            <td class="value">${marital_status}</td>
                        </tr>
                        <tr>
                            <td class="label">การศึกษา:</td>
                            <td class="value">${education}</td>
                        </tr>
                        <tr>
                            <td class="label">อาชีพ:</td>
                            <td class="value">
                                ${occupation}
                                ${customer.occupation === 'ข้าราชการ' && customer.governmentLevel ? ` (ระดับ: ${governmentLevel})` : ''}
                                ${customer.occupation === 'อื่นๆ' && customer.occupationOther ? ` (ระบุ: ${occupationOther})` : ''}
                            </td>
                        </tr>
                        ${customer.careerField ? `
                        <tr>
                            <td class="label">สาขาอาชีพ:</td>
                            <td class="value">
                                ${careerField}
                                ${customer.careerField === 'อื่นๆ' && customer.careerFieldOther ? ` (ระบุ: ${careerFieldOther})` : ''}
                            </td>
                        </tr>
                        ` : ''}
                        <tr>
                            <td class="label">รายได้รวมต่อเดือน:</td>
                            <td class="value value--strong">${income}</td>
                        </tr>
                        ${customer.extraIncome && parseInt(customer.extraIncome) > 0 ? `
                        <tr>
                            <td class="label">รายได้อื่นๆ:</td>
                            <td class="value">${extraIncome}</td>
                        </tr>
                        ` : ''}
                        <tr>
                            <td class="label">แหล่งที่มาของรายได้:</td>
                            <td class="value">${extraIncomeSource}</td>
                        </tr>
                        <tr>
                            <td class="label">ประเทศที่มาของรายได้:</td>
                            <td class="value">${incomeCountry}</td>
                        </tr>
                        <tr>
                            <td class="label">ไฟล์หลักฐานการเงิน:</td>
                            <td class="value">${incomeDocumentsHtml}</td>
                        </tr>
                        <tr>
                            <td class="label">ภาระหนี้อื่นๆ ในปัจจุบัน:</td>
                            <td class="value">${hasOtherDebts}</td>
                        </tr>
                        ${customer.hasOtherDebts === 'มี' ? `
                        <tr>
                            <td class="label">ยอดผ่อนต่อเดือน:</td>
                            <td class="value">${otherDebtInstallment}</td>
                        </tr>
                        ` : ''}
                    </table>
                </div>

                <div class="panel panel--blue">
                    <h4 class="section-title">การชี้แจงการมีสินเชื่อบุคคล</h4>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label">ปัจจุบันมีวงเงินสินเชื่อส่วนบุคคล และวงเงินที่อยู่ระหว่างขอยื่น/ขอเพิ่มตั้งแต่ 2 เดือนก่อนหน้าจนถึงปัจจุบัน จากสถาบันการเงิน/ผู้ประกอบธุรกิจสินเชื่อบุคคลที่ไม่ใช่สถาบันการเงินมากกว่า 2 แห่งหรือไม่:</td>
                            <td class="value">${hasExistingLoan || '-'}</td>
                        </tr>
                        ${hasExistingLoan === 'ใช่' ? `
                        <tr>
                            <td class="label">จำนวนแห่ง:</td>
                            <td class="value">${existingLoanInstitutionCount}</td>
                        </tr>
                        <tr>
                            <td class="label">รวมทั้งสิ้น:</td>
                            <td class="value">${existingLoanTotalAmount}</td>
                        </tr>
                        ` : ''}
                    </table>
                    <div class="panel panel--yellow panel--note">
                        ( หมายเหตุ กรณีกรอกข้อมูลไม่ถูกต้องไม่ครบถ้วน และ/หรือมีรายได้หรือกระเเสเงินสดหมุนเวียนเข้าในบัญชีเงินฝากสถาบันการเงินโดยเฉลี่ยน้อยกว่า 30,000 บาทต่อเดือน โดยมีวงเงินสินเชื่อส่วนบุคคลรวมตั้งแต่ 3 แห่งขึ้นไป บริษัทมีสิทธิปฏิเสธการให้สินเชื่อ หรือกรณีที่ทําสัญญาเงินกู้ ให้ถือว่าบริษัทมีสิทธิลดหรือยกเลิกวงเงินได้ทันที )
                    </div>
                </div>

                <div class="panel panel--pink">
                    <h4 class="section-title">ข้อมูลที่อยู่</h4>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label">สถานะของการอยู่อาศัย / Residence type:</td>
                            <td class="value">${residence_status}</td>
                        </tr>
                        <tr>
                            <td class="label">ที่อยู่ปัจจุบัน / Current address:</td>
                            <td class="value">
                                ${address_building !== '-' ? 'หมู่บ้าน/อาคาร ' + address_building : ''}
                                ${address_room !== '-' ? ' เลขที่ห้อง ' + address_room : ''}
                                ${address_floor !== '-' ? ' ชั้น ' + address_floor : ''}
                                ${address_no !== '-' ? ' บ้านเลขที่ ' + address_no : ''}
                                ${address_village !== '-' ? ' หมู่ ' + address_village : ''}
                                ${address_soi !== '-' ? ' ซอย ' + address_soi : ''}
                                ${address_road !== '-' ? ' ถนน ' + address_road : ''}
                                ${address_subdistrict !== '-' ? ' แขวง/ตำบล ' + address_subdistrict : ''}
                                ${address_district !== '-' ? ' เขต/อำเภอ ' + address_district : ''}
                                ${address_province !== '-' ? ' จังหวัด ' + address_province : ''}
                                ${address_postal !== '-' ? ' ' + address_postal : ''}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">หมายเลขโทรศัพท์บ้าน:</td>
                            <td class="value">${phone_home}</td>
                        </tr>
                        <tr>
                            <td class="label">หมายเลขโทรศัพท์มือถือ:</td>
                            <td class="value">${phone_mobile}</td>
                        </tr>
                        <tr>
                            <td class="label">E-mail:</td>
                            <td class="value">${email}</td>
                        </tr>
                    </table>
                </div>

                <!-- Work Address Section -->
                <div class="panel panel--blue">
                    <h4 class="section-title">สถานที่ทำงานปัจจุบัน</h4>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label label--w30">ชื่อกิจการ/ที่ทำงาน:</td>
                            <td class="value">${companyName}</td>
                        </tr>
                        <tr>
                            <td class="label label--w30">ประเภทธุรกิจ:</td>
                            <td class="value">${businessType}</td>
                        </tr>
                        <tr>
                            <td class="label label--w30">แผนก/ฝ่าย:</td>
                            <td class="value">${workDepartment}</td>
                        </tr>
                        <tr>
                            <td class="label label--w30">ที่อยู่ที่ทำงาน:</td>
                            <td class="value">
                                ${workAddressNo !== '-' ? 'เลขที่ ' + workAddressNo : ''}
                                ${workAddressFloor !== '-' ? ' ชั้น ' + workAddressFloor : ''}
                                ${workAddressVillage !== '-' ? ' หมู่ ' + workAddressVillage : ''}
                                ${workAddressBuilding !== '-' ? ' ' + workAddressBuilding : ''}
                                ${workAddressSoi !== '-' ? ' ซอย ' + workAddressSoi : ''}
                                ${workAddressRoad !== '-' ? ' ถนน ' + workAddressRoad : ''}
                                ${workAddressSubdistrict !== '-' ? ' แขวง/ตำบล ' + workAddressSubdistrict : ''}
                                ${workAddressDistrict !== '-' ? ' เขต/อำเภอ ' + workAddressDistrict : ''}
                                ${workAddressProvince !== '-' ? ' จังหวัด ' + workAddressProvince : ''}
                                ${workAddressPostal !== '-' ? ' ' + workAddressPostal : ''}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">หมายเลขโทรศัพท์ (ที่ทำงาน):</td>
                            <td class="value">${workPhone}</td>
                        </tr>
                        <tr>
                            <td class="label">อายุงานรวม:</td>
                            <td class="value">${workYears} ปี ${workMonths} เดือน</td>
                        </tr>
                    </table>
                </div>

                <!-- Previous Work Section (conditional) -->
                ${(parseInt(workYears) * 12 + parseInt(workMonths) < 12) ? `
                <div class="panel panel--yellow">
                    <h4 class="section-title">ที่ทำงานเดิม</h4>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label">ชื่อที่ทำงานเดิม:</td>
                            <td class="value">${previousCompanyName}</td>
                        </tr>
                        <tr>
                            <td class="label">ตำแหน่ง:</td>
                            <td class="value">${previousPosition}</td>
                        </tr>
                        <tr>
                            <td class="label">รายได้ต่อเดือน:</td>
                            <td class="value">${previousIncome}</td>
                        </tr>
                        <tr>
                            <td class="label">ที่อยู่ที่ทำงานเดิม:</td>
                            <td class="value">${previousWorkAddress}</td>
                        </tr>
                        <tr>
                            <td class="label">หมายเลขโทรศัพท์:</td>
                            <td class="value">${previousPhone}</td>
                        </tr>
                    </table>
                </div>
                ` : ''}

                <!-- Document Delivery Section -->
                <div class="panel panel--green panel--compact">
                    <h4 class="section-title">ช่องทางการรับเอกสาร</h4>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label">ช่องทางการรับเอกสาร:</td>
                            <td class="value">${documentDelivery}</td>
                        </tr>
                        <tr>
                            <td class="label">ที่อยู่ตามเอกสารสําคัญ:</td>
                            <td class="value">${documentAddressText}</td>
                        </tr>
                        <tr>
                            <td class="label">จังหวัด / รหัสไปรษณีย์:</td>
                            <td class="value">${documentAddressProvince !== '-' ? documentAddressProvince : '-'} ${documentAddressPostal !== '-' ? ' ' + documentAddressPostal : ''}</td>
                        </tr>
                        ${birthPlaceAddress !== '-' ? `
                        <tr>
                            <td class="label">ที่อยู่บ้านเกิด:</td>
                            <td class="value">${birthPlaceAddress}</td>
                        </tr>
                        ` : ''}
                    </table>
                </div>

                <!-- Reference Person Section -->
                <div class="panel panel--purple panel--compact">
                    <h4 class="section-title">ข้อมูลบุคคลอ้างอิง</h4>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label">ชื่อ - นามสกุล:</td>
                            <td class="value">${refName}</td>
                        </tr>
                        <tr>
                            <td class="label">ความสัมพันธ์กับผู้กู้:</td>
                            <td class="value">${refRelation}</td>
                        </tr>
                        <tr>
                            <td class="label">ที่อยู่:</td>
                            <td class="value">${refAddressNo !== '-' ? 'เลขที่ ' + refAddressNo : ''}${refAddressFloor !== '-' ? ' ชั้น ' + refAddressFloor : ''}${refAddressVillage !== '-' ? ' หมู่ที่ ' + refAddressVillage : ''}${refAddressBuilding !== '-' ? ' ' + refAddressBuilding : ''}${refAddressSoi !== '-' ? ' ซอย ' + refAddressSoi : ''}${refAddressRoad !== '-' ? ' ถนน ' + refAddressRoad : ''}${refAddressSubdistrict !== '-' ? ' แขวง/ตำบล ' + refAddressSubdistrict : ''}${refAddressDistrict !== '-' ? ' เขต/อำเภอ ' + refAddressDistrict : ''}${refAddressProvince !== '-' ? ' จังหวัด ' + refAddressProvince : ''}${refAddressPostal !== '-' ? ' ' + refAddressPostal : ''}</td>
                        </tr>
                        <tr>
                            <td class="label">หมายเลขโทรศัพท์บ้าน:</td>
                            <td class="value">${refPhoneHome}</td>
                        </tr>
                        <tr>
                            <td class="label">หมายเลขโทรศัพท์มือถือ:</td>
                            <td class="value">${refPhoneMobile}</td>
                        </tr>
                    </table>
                </div>

                <!-- Loan request section -->
                <div class="panel panel--yellow panel--compact">
                    <h4 class="section-title">ความประสงค์ในการสมัครใช้สินเชื่อ</h4>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label">ระยะเวลาผ่อนชำระคืน:</td>
                            <td class="value">${loanTerm}</td>
                        </tr>
                        <tr>
                            <td class="label">วงเงินสินเชื่อที่ต้องการ:</td>
                            <td class="value">${loanAmountType === 'full' ? 'เต็มจำนวนตามที่บริษัทอนุมัติ' : loanAmountType === 'custom' ? `วงเงินที่ขอกู้/จำนวนทั้งสิ้น: ${customLoanAmount}` : '-'}</td>
                        </tr>
                        <tr>
                            <td class="label">วัตถุประสงค์ในการขอสินเชื่อ:</td>
                            <td class="value">${loanPurpose}</td>
                        </tr>
                    </table>
                </div>

                <!-- Bank account section -->
                <div class="panel panel--green panel--compact">
                    <h4 class="section-title">ความประสงค์ขอรับวงเงินกู้ครั้งแรกเข้าบัญชีเงินฝาก</h4>
                    <p class="muted">ในกรณีที่บริษัทอนุมัติสินเชื่อ ข้าพเจ้ามีความประสงค์ให้บริษัทโอนเงินกู้เข้าบัญชีของข้าพเจ้า โดยโอนเข้าบัญชีเงินฝากเลขที่ (กรอกข้อมูล)</p>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label">เลขที่บัญชี:</td>
                            <td class="value">${accountNumber}</td>
                        </tr>
                        <tr>
                            <td class="label">ประเภทบัญชี:</td>
                            <td class="value">${accountType}</td>
                        </tr>
                        <tr>
                            <td class="label">ธนาคาร:</td>
                            <td class="value">${bankName}</td>
                        </tr>
                        <tr>
                            <td class="label">ชื่อบัญชี:</td>
                            <td class="value">${accountName}</td>
                        </tr>
                    </table>
                </div>

                <!-- Payment method section -->
                <div class="panel panel--blue panel--compact">
                    <h4 class="section-title">วิธีการชําระเงิน</h4>
                    <table class="consent-detail-table">
                        <tr>
                            <td class="label">วิธีการชําระเงิน:</td>
                            <td class="value">${paymentMethod}</td>
                        </tr>
                        ${paymentMethod === 'ชําระโดยการหักบัญชี' ? `
                        <tr>
                            <td colspan="2"><div class="notice notice--warning">กรณียินยอมหักบัญชี ข้าพเจ้ายินยอมให้สถาบันการเงินหักเงินจากบัญชีเงินเดือนของข้าพเจ้าที่มีอยู่กับสถาบันการเงิน เป็นจํานวนเงิน <strong>${directDebitAmount}</strong> บาท/เดือน จากบัญชีเลขที่ <strong>${directDebitAccountNumber}</strong> เท่านั้น ณ วันครบกําหนดชําระตามที่บริษัทแจ้งให้ทราบ หรือทุกวันที่เงินเดือนออกในแต่ละเดือนแล้วแต่วันใดจะถึงก่อน เพื่อชําระเงินกู้รวมทั้งดอกเบี้ยจนกว่าจะชําระหนี้ให้แก่บริษัทจนเสร็จสิ้น หากบริษัทไม่สามารถหักเงินจากบัญชีดังกล่าวในวันดังกล่าวได้ ข้าพเจ้าตกลงยอมรับให้บริษัทถือว่าเป็นการผิดนัดชําระหนี้และขอรับรองว่าการที่บริษัทหักเงินจากบัญชีของข้าพเจ้าตามใบสมัครฉบับนี้เป็นไปตามคําร้องขอของข้าพเจ้า หากมีความเสียหายหรือผิดพลาดใดๆ เกิดขึ้นแก่บริษัท ข้าพเจ้าตกลงชดใช้ค่าเสียหายให้แก่บริษัททั้งจํานวนทันที</div></td>
                        </tr>
                        ` : ''}
                    </table>
                </div>

                    <div class="panel panel--yellow panel--compact">
                        <h4 class="section-title">ความประสงค์ในการสมัครใช้สินเชื่อ</h4>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">ระยะเวลาผ่อนชำระคืน:</td>
                                <td class="value">${loanTerm}</td>
                            </tr>
                            <tr>
                                <td class="label">วงเงินสินเชื่อที่ต้องการ:</td>
                                <td class="value">${loanAmountType === 'full' ? 'เต็มจำนวนตามที่บริษัทอนุมัติ' : loanAmountType === 'custom' ? `วงเงินที่ขอกู้/จำนวนทั้งสิ้น: ${customLoanAmount}` : '-'}</td>
                            </tr>
                            <tr>
                                <td class="label">วัตถุประสงค์ในการขอสินเชื่อ:</td>
                                <td class="value">${loanPurpose}</td>
                            </tr>
                        </table>
                    </div>
                <div class="signature-row">
                    <div class="signature-box">
                        <canvas id="viewSignaturePad" class="signature-canvas"></canvas>
                        <p class="signature-label">ลงนามผู้ขอสินเชื่อ</p>
                        <p class="signature-muted">( ${title} ${name} )</p>
                        <p class="signature-muted">วันที่เซ็น: ${signed_date}</p>
                    </div>
                </div>
            `;
            
            viewModal.style.display = 'flex';
            viewModal.offsetHeight;
            viewModal.classList.add('show');
            
            // Initialize signature pad for viewing
            const viewCanvas = document.getElementById('viewSignaturePad');
            if (viewCanvas) {
                // Set canvas size correctly for high DPI
                const rect = viewCanvas.getBoundingClientRect();
                viewCanvas.width = rect.width * window.devicePixelRatio;
                viewCanvas.height = rect.height * window.devicePixelRatio;
                
                const viewSignaturePad = new SignaturePad(viewCanvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)',
                    readOnly: true
                });
                
                // Rescale context for high DPI
                const ctx = viewCanvas.getContext('2d');
                ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
                
                // Load signature data if available
                if (signatureData) {
                    try {
                        viewSignaturePad.fromData(JSON.parse(signatureData));
                    } catch (e) {
                        console.error('Error loading signature:', e);
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', async function() {
            document.querySelectorAll('.alert-success').forEach(function(alertEl) {
                setTimeout(function() {
                    alertEl.style.transition = 'opacity 0.35s ease';
                    alertEl.style.opacity = '0';
                    setTimeout(function() {
                        alertEl.remove();
                    }, 400);
                }, 4000);
            });

            await Promise.all([
                ensureConsentModalLoaded(),
                ensureViewConsentModalLoaded(),
            ]);
            const modal = document.getElementById('consentModal');
            const pdfModal = document.getElementById('pdfConsentModal');
            const openBtn = document.getElementById('openConsentModal');
            const closeBtn = document.getElementById('closeConsentModal');
            const cancelBtn = document.getElementById('cancelConsentModal');
            const closePdfBtn = document.getElementById('closePdfModal');
            const cancelPdfBtn = document.getElementById('cancelPdfModal');
            const proceedToConsentBtn = document.getElementById('proceedToConsentModal');
            const consentForm = document.getElementById('consentForm');
            const consentModalTitle = document.getElementById('consentModalTitle');
            const consentSubmitBtn = document.getElementById('consentSubmitBtn');
            const nextStepBtn = document.getElementById('nextStepBtn');
            const prevStepBtn = document.getElementById('prevStepBtn');
            const wizardSteps = document.querySelectorAll('.wizard-step');
            const stepContainers = document.querySelectorAll('.step-container');
            
            let currentStep = 1;
            let maxStepReached = 1;

            function updateWizardUI() {
                wizardSteps.forEach(step => {
                    const stepNum = parseInt(step.dataset.step);
                    step.classList.toggle('active', stepNum === currentStep);
                    step.classList.toggle('completed', stepNum < currentStep);
                });

                stepContainers.forEach(container => {
                    const stepNum = parseInt(container.dataset.step);
                    container.classList.toggle('active', stepNum === currentStep);
                });

                if (prevStepBtn) {
                    prevStepBtn.classList.toggle('hidden', currentStep === 1);
                }

                if (nextStepBtn) {
                    nextStepBtn.classList.toggle('hidden', currentStep === 8);
                }

                if (consentSubmitBtn) {
                    consentSubmitBtn.classList.toggle('hidden', currentStep !== 8);
                }

                const modalBody = modal?.querySelector('.modal-body');
                if (modalBody) modalBody.scrollTop = 0;

                // Initialize/Resize signature pad when reaching step 7
                if (currentStep === 7) {
                    setTimeout(() => {
                        initSignaturePad();
                        if (signatureDataInput && signatureDataInput.value) {
                            applySignatureData(signatureDataInput.value);
                        }
                    }, 100);
                }
            }

            async function saveStepData(step) {
                if (!consentForm) return { ok: false, message: 'Form not found' };

                const formData = new FormData(consentForm);
                formData.append('step', step);
                
                // Ensure _method is POST for save-step API regardless of edit mode
                formData.set('_method', 'POST');
                
                // Add consent_id from hidden field if exists
                const consentId = document.getElementById('consent_id')?.value;
                if (consentId) {
                    formData.set('consent_id', consentId);
                }

                try {
                  
                    const response = await fetch(modalEndpoints.saveStep, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value ?? '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();
                    
                    if (response.ok && result.ok) {
                        if (result.consent_id) {
                            const idField = document.getElementById('consent_id');
                            if (idField) idField.value = result.consent_id;
                        }
                        return { ok: true, data: result };
                    } else {
                        
                        return { 
                            ok: false, 
                            message: result.message || 'กรุณาตรวจสอบข้อมูลที่กรอก', 
                            errors: result.errors 
                        };
                    }
                } catch (error) {
                   
                    return { ok: false, message: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' };
                }
            }

            function clearValidationErrors() {
                // remove label and input error markers
                modal?.querySelectorAll('.label-error').forEach(el => el.classList.remove('label-error'));
                modal?.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
            }

            function showValidationErrors(errors) {
                clearValidationErrors();
                let errorMessages = [];

                Object.entries(errors).forEach(([field, messages]) => {
                    // try to find input/select/textarea by name or id
                    const input = consentForm?.querySelector(`[name="${field}"], [name="${field}[]"], #${field}`);
                    if (input) {
                        input.classList.add('input-error');

                        // mark related label (if exists)
                        const label = modal?.querySelector(`label[for="${field}"]`) || document.querySelector(`label[for="${field}"]`);
                        if (label) label.classList.add('label-error');

                        // collect first message for alert
                        if (messages && messages.length) {
                            errorMessages.push(messages[0]);
                        }
                    } else {
                        // fallback: collect messages even if input not found
                        if (messages && messages.length) {
                            errorMessages.push(messages[0]);
                        }
                    }
                });

                if (errorMessages.length > 0) {
                    // keep alert behaviour but avoid creating .form-error DOM nodes
                    alert('กรุณากรอกข้อมูลให้ครบถ้วน:\n- ' + errorMessages.join('\n- '));
                }
            }

            if (nextStepBtn) {
                nextStepBtn.addEventListener('click', async function() {
                    this.disabled = true;
                    this.textContent = 'กำลังบันทึก...';

                    // Special handling for Step 7 (Signature)
                    if (currentStep === 7) {
                        if (signaturePad && !signaturePad.isEmpty()) {
                            if (signatureDataInput) {
                                signatureDataInput.value = JSON.stringify(signaturePad.toData());
                            }
                        } else {
                            alert('กรุณาเซ็นลายเซ็นผู้ขอสินเชื่อก่อนไปขั้นตอนถัดไป');
                            this.disabled = false;
                            this.textContent = 'ถัดไป';
                            return;
                        }
                    }

                    const result = await saveStepData(currentStep);

                    if (result.ok) {
                        currentStep++;
                        if (currentStep > maxStepReached) maxStepReached = currentStep;
                        updateWizardUI();
                    } else {
                        if (result.errors) {
                            showValidationErrors(result.errors);
                        } else {
                            alert(result.message);
                        }
                    }

                    this.disabled = false;
                    this.textContent = 'ถัดไป';
                });
            }

            if (prevStepBtn) {
                prevStepBtn.addEventListener('click', function() {
                    if (currentStep > 1) {
                        currentStep--;
                        updateWizardUI();
                    }
                });
            }

            wizardSteps.forEach(step => {
                step.addEventListener('click', async function() {
                    const targetStep = parseInt(this.dataset.step);
                    if (targetStep === currentStep) return;

                    // Allow jump back always, allow jump forward only if reached
                    if (!(targetStep < currentStep || targetStep <= maxStepReached)) {
                        return;
                    }

                    // Prevent multiple clicks
                    if (this.dataset.saving === '1') return;
                    this.dataset.saving = '1';

                    // Special handling for Step 7 (Signature) when saving current step
                    if (currentStep === 7) {
                        if (signaturePad && signaturePad.isEmpty()) {
                            alert('กรุณาเซ็นลายเซ็นผู้ขอสินเชื่อก่อนเปลี่ยนขั้นตอน');
                            this.dataset.saving = '0';
                            return;
                        }
                        if (signaturePad && !signaturePad.isEmpty()) {
                            if (signatureDataInput) signatureDataInput.value = JSON.stringify(signaturePad.toData());
                        }
                    }

                    // Save current step before navigating
                    const result = await saveStepData(currentStep);
                    if (result.ok) {
                        currentStep = targetStep;
                        if (currentStep > maxStepReached) maxStepReached = currentStep;
                        updateWizardUI();
                    } else {
                        if (result.errors) {
                            showValidationErrors(result.errors);
                        } else {
                            alert(result.message);
                        }
                    }

                    this.dataset.saving = '0';
                });
            });

            const appNoInput = document.getElementById('app_no');
            const appDateInput = document.getElementById('app_date');
            const signatureDataInput = document.getElementById('signatureData');
            modalNextAppNo = modal?.dataset.nextAppNo || '';
            let signaturePad;
            let signatureInitSeq = 0;
            let isSyncingConditionalSections = false;

            function disableFormAutofill(form) {
                if (!form) return;

                form.setAttribute('autocomplete', 'off');
                form.querySelectorAll('input, select, textarea').forEach(function(el) {
                    el.setAttribute('autocomplete', 'off');

                    if (el.type === 'hidden' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') {
                        return;
                    }

                    if (el.hasAttribute('readonly') || el.readOnly) {
                        return;
                    }

                    if (el.required || el.hasAttribute('required')) {
                        return;
                    }

                    if (['text', 'number', 'email', 'tel', 'search'].includes(el.type)) {
                        el.setAttribute('readonly', 'readonly');
                        el.addEventListener('focus', function() {
                            if (this.dataset.syncReadonly === 'true') {
                                return;
                            }
                            this.removeAttribute('readonly');
                        }, { once: true });
                    }
                });
            }

            disableFormAutofill(modal?.querySelector('.consent-form'));

            const postCodeApi = {
                options: @json(route('consent.postcodes.options')),
            };

            const addressFieldResponseKeys = {
                province: 'provinces',
                city: 'cities',
                district: 'districts',
                post_code: 'post_codes',
            };

            const addressFieldQueryKeys = {
                province: 'q_province',
                city: 'q_city',
                district: 'q_district',
                post_code: 'q_post_code',
            };

            function buildPostCodeOptionsUrl(filters, queries) {
                const url = new URL(postCodeApi.options, window.location.origin);

                Object.entries(filters || {}).forEach(function([key, value]) {
                    if (value) {
                        url.searchParams.set(key, value);
                    }
                });

                Object.entries(queries || {}).forEach(function([key, value]) {
                    if (value) {
                        url.searchParams.set(key, value);
                    }
                });

                return url.toString();
            }

            async function fetchPostCodeOptions(filters, queries) {
                const response = await fetch(buildPostCodeOptionsUrl(filters, queries), {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) return null;
                const data = await response.json();
                return data && typeof data === 'object' ? data : null;
            }

            function createAddressLookupController(config) {
                const state = {
                    selected: {
                        province: '',
                        city: '',
                        district: '',
                        post_code: '',
                    },
                    query: {
                        province: '',
                        city: '',
                        district: '',
                        post_code: '',
                    },
                    activeField: '',
                };

                const fields = {
                    province: { input: config.provinceInput, dropdown: config.provinceDropdown },
                    city: { input: config.cityInput, dropdown: config.cityDropdown },
                    district: { input: config.districtInput, dropdown: config.districtDropdown },
                    post_code: { input: config.postalInput, dropdown: config.postalDropdown },
                };

                let refreshSeq = 0;

                function getSelectedFilters() {
                    const filters = {};
                    Object.entries(state.selected).forEach(function([key, value]) {
                        if (value) {
                            filters[key] = value;
                        }
                    });
                    return filters;
                }

                function getQueryFilters() {
                    const queries = {};
                    Object.entries(state.query).forEach(function([key, value]) {
                        if (value) {
                            queries[addressFieldQueryKeys[key]] = value;
                        }
                    });
                    return queries;
                }

                function getItems(data, fieldKey) {
                    const responseKey = addressFieldResponseKeys[fieldKey];
                    return Array.isArray(data?.[responseKey]) ? data[responseKey] : [];
                }

                function syncInputs() {
                    Object.entries(fields).forEach(function([fieldKey, field]) {
                        if (!field.input) return;
                        field.input.value = state.query[fieldKey] || state.selected[fieldKey] || '';
                    });
                }

                function hideDropdown(fieldKey) {
                    const dropdown = fields[fieldKey]?.dropdown;
                    if (!dropdown) return;
                    dropdown.innerHTML = '';
                    dropdown.classList.add('hidden');
                }

                function hideAllDropdowns() {
                    Object.keys(fields).forEach(hideDropdown);
                }

                function renderDropdown(fieldKey, items) {
                    const field = fields[fieldKey];
                    const dropdown = field?.dropdown;
                    const input = field?.input;

                    if (!dropdown || !input || state.activeField !== fieldKey || input.readOnly) {
                        hideDropdown(fieldKey);
                        return;
                    }

                    const uniqueItems = Array.from(new Set(items.filter(Boolean)));
                    dropdown.innerHTML = '';

                    if (uniqueItems.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'address-search-empty';
                        empty.textContent = 'ไม่พบข้อมูลที่ตรงกับคำค้น';
                        dropdown.appendChild(empty);
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    uniqueItems.forEach(function(item, index) {
                        const option = document.createElement('button');
                        option.type = 'button';
                        option.className = 'address-search-option';
                        if (index === 0) {
                            option.classList.add('is-active');
                        }
                        option.textContent = item;
                        option.addEventListener('mousedown', function(event) {
                            event.preventDefault();
                            applySelection(fieldKey, item);
                        });
                        dropdown.appendChild(option);
                    });

                    dropdown.classList.remove('hidden');
                }

                function normalizeSelections(data, lockedFieldKey) {
                    let changed = false;

                    Object.keys(fields).forEach(function(fieldKey) {
                        if (fieldKey === lockedFieldKey) {
                            return;
                        }

                        const selectedValue = state.selected[fieldKey];
                        if (!selectedValue) {
                            return;
                        }

                        if (!getItems(data, fieldKey).includes(selectedValue)) {
                            state.selected[fieldKey] = '';
                            if (state.query[fieldKey] === selectedValue) {
                                state.query[fieldKey] = '';
                            }
                            changed = true;
                        }
                    });

                    if (changed) {
                        syncInputs();
                    }

                    return changed;
                }

                async function refresh(lockedFieldKey = '') {
                    const currentSeq = ++refreshSeq;
                    const data = await fetchPostCodeOptions(getSelectedFilters(), getQueryFilters());
                    if (!data || currentSeq !== refreshSeq) return;

                    if (normalizeSelections(data, lockedFieldKey)) {
                        await refresh(lockedFieldKey);
                        return;
                    }

                    Object.keys(fields).forEach(function(fieldKey) {
                        renderDropdown(fieldKey, getItems(data, fieldKey));
                    });
                }

                function applySelection(fieldKey, value) {
                    state.selected[fieldKey] = value;
                    state.query[fieldKey] = value;
                    state.activeField = '';
                    syncInputs();
                    hideAllDropdowns();
                    refresh(fieldKey);
                }

                function handleInput(fieldKey) {
                    const input = fields[fieldKey]?.input;
                    if (!input) return;

                    const rawValue = (input.value || '').trim();
                    const value = fieldKey === 'post_code' ? rawValue.replace(/[^\d]/g, '') : rawValue;

                    if (input.value !== value) {
                        input.value = value;
                    }

                    state.query[fieldKey] = value;
                    if (state.selected[fieldKey] !== value) {
                        state.selected[fieldKey] = '';
                    }

                    state.activeField = fieldKey;
                    refresh(fieldKey);
                }

                function bindField(fieldKey) {
                    const field = fields[fieldKey];
                    const input = field?.input;
                    if (!input) return;

                    input.addEventListener('focus', function() {
                        state.activeField = fieldKey;
                        state.query[fieldKey] = (input.value || '').trim();
                        refresh(fieldKey);
                    });

                    input.addEventListener('input', function() {
                        handleInput(fieldKey);
                    });

                    input.addEventListener('keydown', function(event) {
                        if (event.key === 'Escape') {
                            hideDropdown(fieldKey);
                        }

                        if (event.key === 'Enter') {
                            const firstOption = field.dropdown?.querySelector('.address-search-option');
                            if (firstOption) {
                                event.preventDefault();
                                firstOption.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
                            }
                        }
                    });

                    input.addEventListener('blur', function() {
                        window.setTimeout(function() {
                            if (state.activeField === fieldKey) {
                                state.activeField = '';
                            }
                            hideDropdown(fieldKey);
                        }, 150);
                    });
                }

                Object.keys(fields).forEach(bindField);

                return {
                    async reset() {
                        Object.keys(state.selected).forEach(function(fieldKey) {
                            state.selected[fieldKey] = '';
                            state.query[fieldKey] = '';
                        });
                        state.activeField = '';
                        syncInputs();
                        hideAllDropdowns();
                        await refresh();
                    },
                    async setValues(values) {
                        Object.keys(state.selected).forEach(function(fieldKey) {
                            const value = (values?.[fieldKey] || '').trim();
                            state.selected[fieldKey] = value;
                            state.query[fieldKey] = value;
                        });
                        state.activeField = '';
                        syncInputs();
                        hideAllDropdowns();
                        await refresh();
                    },
                    getValues() {
                        return {
                            province: state.selected.province || state.query.province || '',
                            city: state.selected.city || state.query.city || '',
                            district: state.selected.district || state.query.district || '',
                            post_code: state.selected.post_code || state.query.post_code || '',
                        };
                    },
                    setReadOnly(readOnly) {
                        Object.values(fields).forEach(function(field) {
                            if (!field.input) return;
                            field.input.readOnly = readOnly;
                            field.input.dataset.syncReadonly = readOnly ? 'true' : 'false';
                            field.input.style.background = readOnly ? '#f3f4f6' : '';
                            field.input.style.cursor = readOnly ? 'not-allowed' : '';
                        });

                        if (readOnly) {
                            hideAllDropdowns();
                        }
                    },
                };
            }

            const homeAddressController = createAddressLookupController({
                provinceInput: document.getElementById('address_province'),
                provinceDropdown: document.getElementById('address_province_dropdown'),
                cityInput: document.getElementById('address_district'),
                cityDropdown: document.getElementById('address_district_dropdown'),
                districtInput: document.getElementById('address_subdistrict'),
                districtDropdown: document.getElementById('address_subdistrict_dropdown'),
                postalInput: document.getElementById('address_postal'),
                postalDropdown: document.getElementById('address_postal_dropdown'),
            });

            const workAddressController = createAddressLookupController({
                provinceInput: document.getElementById('workAddressProvince'),
                provinceDropdown: document.getElementById('workAddressProvince_dropdown'),
                cityInput: document.getElementById('workAddressDistrict'),
                cityDropdown: document.getElementById('workAddressDistrict_dropdown'),
                districtInput: document.getElementById('workAddressSubdistrict'),
                districtDropdown: document.getElementById('workAddressSubdistrict_dropdown'),
                postalInput: document.getElementById('workAddressPostal'),
                postalDropdown: document.getElementById('workAddressPostal_dropdown'),
            });

            const documentAddressController = createAddressLookupController({
                provinceInput: document.getElementById('documentAddressProvince'),
                provinceDropdown: document.getElementById('documentAddressProvince_dropdown'),
                postalInput: document.getElementById('documentAddressPostal'),
                postalDropdown: document.getElementById('documentAddressPostal_dropdown'),
            });

            const refAddressController = createAddressLookupController({
                provinceInput: document.getElementById('refAddressProvince'),
                provinceDropdown: document.getElementById('refAddressProvince_dropdown'),
                cityInput: document.getElementById('refAddressDistrict'),
                cityDropdown: document.getElementById('refAddressDistrict_dropdown'),
                districtInput: document.getElementById('refAddressSubdistrict'),
                districtDropdown: document.getElementById('refAddressSubdistrict_dropdown'),
                postalInput: document.getElementById('refAddressPostal'),
                postalDropdown: document.getElementById('refAddressPostal_dropdown'),
            });

            homeAddressController.reset();
            workAddressController.reset();
            documentAddressController.reset();
            refAddressController.reset();

            function setFieldValue(fieldName, value) {
                const field = consentForm?.querySelector(`[name="${fieldName}"]`);
                if (!field) return;

                if (field.type === 'checkbox') {
                    field.checked = Boolean(value);
                    return;
                }

                if (field.type === 'date' && typeof value === 'string') {
                    const normalized = value.match(/^\d{4}-\d{2}-\d{2}/) ? value.slice(0, 10) : value;
                    field.value = normalized;
                    return;
                }

                field.value = value ?? '';
            }

            function setSelectWithOther(selectName, otherInputName, value, allowedValues) {
                const normalizedValue = value ?? '';

                if (!normalizedValue) {
                    setFieldValue(selectName, '');
                } else if (allowedValues.includes(normalizedValue)) {
                    setFieldValue(selectName, normalizedValue);
                } else {
                    setFieldValue(selectName, 'อื่นๆ');
                    const selectField = consentForm?.querySelector(`[name="${selectName}"]`);
                    selectField?.dispatchEvent(new Event('change'));
                    setFieldValue(otherInputName, normalizedValue);
                }
            }

            const documentUploadGroups = [
                {
                    key: 'incomeDocuments',
                    documentType: 'income_document',
                    acceptedDocumentTypes: [
                        'income_document',
                        'income_salary_slip',
                        'income_salary_certificate',
                        'income_salary_50tawi',
                        'income_salary_statement_6m',
                        'income_supplementary_slip',
                        'income_supplementary_statement_6m',
                        'income_registered_corporate_cert',
                        'income_registered_shareholder_list',
                        'income_registered_trade_registration',
                        'income_registered_statement_1y',
                        'income_unregistered_lease',
                        'income_unregistered_tax',
                        'income_unregistered_statement_1y',
                        'income_unregistered_invoice',
                        'income_unregistered_business_photo',
                        'income_self_individual_tax',
                        'income_self_individual_pnd',
                        'income_self_individual_statement_1y',
                        'income_self_business_tax',
                        'income_self_business_statement_1y',
                        'income_self_business_invoice',
                        'income_self_business_photo',
                    ],
                    defaultTypeLabel: 'เอกสารแสดงรายได้',
                },
                {
                    key: 'identityDocuments',
                    documentType: 'identity_document',
                    acceptedDocumentTypes: ['identity_document', 'id_card', 'passport', 'house_registration', 'work_permit', 'name_change'],
                    defaultTypeLabel: 'เอกสารแสดงตน',
                },
            ];

            const documentUploadStates = documentUploadGroups.map(function(group) {
                return {
                    ...group,
                    input: document.getElementById(group.key),
                    selectedWrapper: document.getElementById(`${group.key}SelectedWrapper`),
                    selectedList: document.getElementById(`${group.key}SelectedList`),
                    existingWrapper: document.getElementById(`${group.key}ExistingWrapper`),
                    existingList: document.getElementById(`${group.key}ExistingList`),
                    transfer: null,
                    objectUrls: [],
                };
            });

            function revokeGroupObjectUrls(groupState) {
                groupState.objectUrls.forEach(function(url) {
                    URL.revokeObjectURL(url);
                });
                groupState.objectUrls = [];
            }

            function resetGroupSelection(groupState) {
                revokeGroupObjectUrls(groupState);
                if (groupState.input) {
                    groupState.input.value = '';
                }
                groupState.transfer = null;
                if (groupState.selectedWrapper) {
                    groupState.selectedWrapper.classList.add('hidden');
                }
                if (groupState.selectedList) {
                    groupState.selectedList.innerHTML = '';
                }
            }

            function resetAllDocumentSelections() {
                documentUploadStates.forEach(function(groupState) {
                    resetGroupSelection(groupState);
                });
            }

            function renderIncomeDocumentsExisting(customer) {
                const documents = Array.isArray(customer?.incomeDocuments) ? customer.incomeDocuments : [];
                documentUploadStates.forEach(function(groupState) {
                    if (!groupState.existingWrapper || !groupState.existingList) {
                        return;
                    }

                    const groupedDocuments = documents.filter(function(document) {
                        const documentType = document?.documentType ?? '';
                        const acceptedTypes = groupState.acceptedDocumentTypes || [groupState.documentType];
                        return acceptedTypes.includes(documentType);
                    });

                    if (!groupedDocuments.length) {
                        groupState.existingWrapper.classList.add('hidden');
                        groupState.existingList.innerHTML = '';
                        return;
                    }

                    groupState.existingWrapper.classList.remove('hidden');
                    groupState.existingList.innerHTML = groupedDocuments
                        .map(function(document) {
                            const url = document?.downloadUrl ?? '#';
                            const name = document?.originalName ?? 'ไฟล์แนบ';
                            const destroyUrl = document?.destroyUrl ?? '';
                            const deleteButton = destroyUrl
                                ? `<button type="button" class="file-remove-btn" data-destroy-url="${escapeHtml(destroyUrl)}">ลบ</button>`
                                : '';

                            const isZip = (document?.mimeType === 'application/zip') || (name && name.toLowerCase().endsWith('.zip'));
                            const docId = document?.id ?? '';
                            const viewFilesBtn = isZip
                                ? `<button type="button" class="file-view-zip-btn" data-doc-id="${escapeHtml(docId)}" data-document-url="${escapeHtml(url)}">ดูไฟล์</button>`
                                : '';

                            return `<div class="file-attachment-row">
                                <a href="${escapeHtml(url)}" target="_blank" rel="noopener">${escapeHtml(name)}</a>
                                ${viewFilesBtn}
                                ${deleteButton}
                            </div>`;
                        })
                        .join('');
                });
            }

            function renderSelectedFiles(groupState) {
                if (!groupState.input || !groupState.selectedWrapper || !groupState.selectedList) {
                    return;
                }

                revokeGroupObjectUrls(groupState);
                const files = Array.from(groupState.input.files || []);
                if (!files.length) {
                    groupState.selectedWrapper.classList.add('hidden');
                    groupState.selectedList.innerHTML = '';
                    return;
                }

                groupState.selectedWrapper.classList.remove('hidden');
                groupState.selectedList.innerHTML = files
                    .map(function(file, index) {
                        const url = URL.createObjectURL(file);
                        groupState.objectUrls.push(url);
                        return `<div class="file-attachment-row">
                            <a href="${url}" target="_blank" rel="noopener">${escapeHtml(file.name)}</a>
                            <button type="button" class="file-remove-btn" data-remove-index="${index}">ลบ</button>
                        </div>`;
                    })
                    .join('');
            }

            function mergeUniqueFiles(existingFiles, incomingFiles) {
                const nextTransfer = new DataTransfer();
                const seen = new Set();

                [...existingFiles, ...incomingFiles].forEach(function(file) {
                    const key = [file.name, file.size, file.lastModified].join('|');
                    if (seen.has(key)) {
                        return;
                    }
                    seen.add(key);
                    nextTransfer.items.add(file);
                });

                return nextTransfer;
            }

            documentUploadStates.forEach(function(groupState) {
                if (groupState.input && groupState.selectedWrapper && groupState.selectedList) {
                    groupState.input.addEventListener('change', function() {
                        const previousFiles = groupState.transfer ? Array.from(groupState.transfer.files) : [];
                        const newFiles = Array.from(groupState.input.files || []);
                        const nextTransfer = mergeUniqueFiles(previousFiles, newFiles);

                        groupState.transfer = nextTransfer;
                        groupState.input.files = nextTransfer.files;
                        renderSelectedFiles(groupState);
                    });

                    groupState.selectedList.addEventListener('click', function(event) {
                        const button = event.target.closest('[data-remove-index]');
                        if (!button) return;

                        const removeIndex = Number(button.dataset.removeIndex);
                        const files = Array.from(groupState.input.files || []);
                        if (!Number.isFinite(removeIndex) || removeIndex < 0 || removeIndex >= files.length) {
                            return;
                        }

                        const nextTransfer = new DataTransfer();
                        files.forEach(function(file, index) {
                            if (index !== removeIndex) {
                                nextTransfer.items.add(file);
                            }
                        });

                        groupState.transfer = nextTransfer;
                        groupState.input.files = nextTransfer.files;
                        renderSelectedFiles(groupState);
                    });
                }

                if (groupState.existingList && groupState.existingWrapper) {
                    groupState.existingList.addEventListener('click', async function(event) {
                        const button = event.target.closest('[data-destroy-url]');
                        if (!button) return;

                        const destroyUrl = button.dataset.destroyUrl;
                        if (!destroyUrl) return;
                        if (!confirm('ต้องการลบไฟล์นี้ใช่ไหม?')) return;

                        const token = document.querySelector('input[name="_token"]')?.value ?? '';
                        const response = await fetch(destroyUrl, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            alert('ลบไฟล์ไม่สำเร็จ');
                            return;
                        }

                        button.closest('.file-attachment-row')?.remove();
                        if (!groupState.existingList.querySelector('.file-attachment-row')) {
                            groupState.existingWrapper.classList.add('hidden');
                            groupState.existingList.innerHTML = '';
                        }
                    });
                }
            });

            function syncConditionalSections() {
                isSyncingConditionalSections = true;
                try {
                    [
                        'title',
                        'id_type',
                        'marital_status',
                        'occupation',
                        'hasOtherDebts',
                        'residence_status',
                        'businessType',
                        'documentDelivery',
                        'loanAmountType',
                        'paymentMethod'
                    ].forEach(function(fieldName) {
                        const field = consentForm?.querySelector(`[name="${fieldName}"]`);
                        field?.dispatchEvent(new Event('change'));
                    });

                    ['extraIncome', 'income', 'workYears', 'workMonths'].forEach(function(fieldName) {
                        const field = consentForm?.querySelector(`[name="${fieldName}"]`);
                        field?.dispatchEvent(new Event('input'));
                    });

                    const useHomeAddressField = consentForm?.querySelector('[name="useHomeAddress"]');
                    useHomeAddressField?.dispatchEvent(new Event('change'));
                } finally {
                    isSyncingConditionalSections = false;
                }
            }

            function applySignatureData(signatureData) {
                if (!signatureData || !signaturePad) return;

                try {
                    signaturePad.fromData(JSON.parse(signatureData));
                } catch (error) {
                    console.error('Error loading signature into form:', error);
                }
            }

            async function prepareCreateModal() {
                consentForm?.reset();
                setFieldValue('consent_id', '');
                currentStep = 1;
                maxStepReached = 1;
                updateWizardUI();
                clearValidationErrors();
                resetAllDocumentSelections();
                renderIncomeDocumentsExisting(null);
                if (consentModalTitle) {
                    consentModalTitle.textContent = consentI18n.form.createTitle;
                }
                if (consentSubmitBtn) {
                    consentSubmitBtn.textContent = consentI18n.form.submitCreate;
                }
                if (appNoInput) {
                    appNoInput.value = modalNextAppNo;
                }
                if (appDateInput) {
                    appDateInput.value = '{{ date('Y-m-d') }}';
                }
                if (signatureDataInput) {
                    signatureDataInput.value = '';
                }
                if (signaturePad) {
                    signaturePad.clear();
                }

                await homeAddressController.reset();
                await workAddressController.reset();
                await documentAddressController.reset();
                syncConditionalSections();
            }

            async function prepareEditModal(customer) {
                if (!consentForm) return;

                consentForm.reset();
                currentStep = 1;
                maxStepReached = 8; // Allow jumping to any step in edit mode
                updateWizardUI();
                clearValidationErrors();
                resetAllDocumentSelections();
                homeAddressController.reset();
                workAddressController.reset();
                documentAddressController.reset();
                refAddressController.reset();
                setFieldValue('consent_id', customer.id);
                if (consentModalTitle) {
                    consentModalTitle.textContent = consentI18n.form.editTitle;
                }
                if (consentSubmitBtn) {
                    consentSubmitBtn.textContent = consentI18n.form.submitEdit;
                }

                const hasPassport = (customer.passport ?? '').toString().trim() !== '';
                const inferredIdType = hasPassport ? 'passport' : 'id_card';
                const inferredIdNumber = hasPassport ? customer.passport : customer.id_card;

                setFieldValue('id_type', inferredIdType);

                [
                    'app_date', 'app_no', 'officer_name', 'officer_phone', 'officer_group', 'title', 'name', 'name_en', 'birthdate', 'id_card',
                    'nationality', 'marital_status', 'education', 'occupation', 'governmentLevel', 'occupationOther',
                    'careerField', 'careerFieldOther', 'residence_status', 'address_room', 'address_no', 'address_floor',
                    'address_village', 'address_building', 'address_soi', 'address_road', 'address_subdistrict',
                    'address_district', 'address_province', 'address_postal', 'phone_home', 'phone_mobile', 'email',
                    'documentAddressText', 'documentAddressProvince', 'documentAddressPostal', 'birthPlaceAddress'
                ].forEach(function(fieldName) {
                    setFieldValue(fieldName, customer[fieldName]);
                });

                // Trigger change events to show correct fields
                if (occupationSelect) {
                    occupationSelect.dispatchEvent(new Event('change'));
                }
                if (careerFieldSelect) {
                    careerFieldSelect.dispatchEvent(new Event('change'));
                }

                setFieldValue('id_card', inferredIdNumber);

                setFieldValue('income', customer.income);
                setFieldValue('extraIncome', customer.extraIncome);
                setFieldValue('incomeCountry', customer.incomeCountry);
                setFieldValue('hasOtherDebts', customer.hasOtherDebts);
                setFieldValue('otherDebtInstallment', customer.otherDebtInstallment);
                setFieldValue('hasExistingLoan', customer.hasExistingLoan);
                setFieldValue('existingLoanInstitutionCount', customer.existingLoanInstitutionCount);
                setFieldValue('existingLoanTotalAmount', customer.existingLoanTotalAmount);
                setFieldValue('useHomeAddress', customer.useHomeAddress);
                setFieldValue('companyName', customer.companyName);
                setFieldValue('businessType', customer.businessType);
                setFieldValue('workDepartment', customer.workDepartment);
                setFieldValue('workYears', customer.workYears);
                setFieldValue('workMonths', customer.workMonths);
                setFieldValue('workAddressNo', customer.workAddressNo);
                setFieldValue('workAddressFloor', customer.workAddressFloor);
                setFieldValue('workAddressVillage', customer.workAddressVillage);
                setFieldValue('workAddressBuilding', customer.workAddressBuilding);
                setFieldValue('workAddressSoi', customer.workAddressSoi);
                setFieldValue('workAddressRoad', customer.workAddressRoad);
                setFieldValue('workAddressSubdistrict', customer.workAddressSubdistrict);
                setFieldValue('workAddressDistrict', customer.workAddressDistrict);
                setFieldValue('workAddressProvince', customer.workAddressProvince);
                setFieldValue('workAddressPostal', customer.workAddressPostal);
                setFieldValue('workPhone', customer.workPhone);
                setFieldValue('previousCompanyName', customer.previousCompanyName);
                setFieldValue('previousPosition', customer.previousPosition);
                setFieldValue('previousIncome', customer.previousIncome);
                setFieldValue('previousWorkAddress', customer.previousWorkAddress);
                setFieldValue('previousPhone', customer.previousPhone);
                setFieldValue('documentDelivery', customer.documentDelivery);
                setFieldValue('refName', customer.refName);
                setFieldValue('refRelation', customer.refRelation);
                setFieldValue('refAddressNo', customer.refAddressNo);
                setFieldValue('refAddressFloor', customer.refAddressFloor);
                setFieldValue('refAddressVillage', customer.refAddressVillage);
                setFieldValue('refAddressBuilding', customer.refAddressBuilding);
                setFieldValue('refAddressSoi', customer.refAddressSoi);
                setFieldValue('refAddressRoad', customer.refAddressRoad);
                setFieldValue('refAddressSubdistrict', customer.refAddressSubdistrict);
                setFieldValue('refAddressDistrict', customer.refAddressDistrict);
                setFieldValue('refAddressProvince', customer.refAddressProvince);
                setFieldValue('refAddressPostal', customer.refAddressPostal);
                setFieldValue('refPhoneHome', customer.refPhoneHome);
                setFieldValue('refPhoneMobile', customer.refPhoneMobile);
                setFieldValue('loanTerm', customer.loanTerm);
                setFieldValue('loanAmountType', customer.loanAmountType);
                setFieldValue('customLoanAmount', customer.customLoanAmount);
                setFieldValue('loanPurpose', customer.loanPurpose);
                setFieldValue('bankName', customer.bankName);
                setFieldValue('accountName', customer.accountName);
                setFieldValue('accountType', customer.accountType);
                setFieldValue('accountNumber', customer.accountNumber);
                setFieldValue('paymentMethod', customer.paymentMethod);
                setFieldValue('directDebitAmount', customer.directDebitAmount);
                setFieldValue('directDebitAccountNumber', customer.directDebitAccountNumber);
                setFieldValue('signatureData', customer.signatureData);

                setSelectWithOther('title', 'title_other', customer.title, ['นาย', 'นาง', 'นางสาว']);
                setSelectWithOther('occupation', 'occupationOther', customer.occupation, ['ข้าราชการ', 'พนักงานราชการ', 'พนักงานรัฐวิสาหกิจ', 'พนักงานบริษัทเอกชน', 'อาชีพอิสระ', 'เจ้าของกิจการที่จดทะเบียนพาณิชย์', 'เจ้าของกิจการที่ไม่จดทะเบียนพาณิชย์', 'อื่นๆ']);
                setSelectWithOther('careerField', 'careerFieldOther', customer.careerField, ['ครู/อาจารย์', 'ตํารวจ/ทหาร', 'แพทย์/ทันตแพทย์/สัตวแพยท์', 'เภสัชกร', 'พยาบาล', 'สถาปนิก', 'วิศวกร', 'บัญชีการเงิน', 'พนักงานขาย', 'อื่นๆ']);
                setSelectWithOther('extraIncomeSource', 'extraIncomeSourceOther', customer.extraIncomeSource, ['รับจ้าง/เงินเดือน', 'ค่าคอมมมิชั่น', 'โบนัส', 'ธุรกิจส่วนตัว', 'อื่นๆ']);
                setSelectWithOther('businessType', 'businessTypeOther', customer.businessType, ['การศึกษา', 'รับเหมาก่อสร้าง', 'วัสดุก่อสร้าง / Construction materials', 'บริการ', 'ฟอร์นิเจอร์/โรงเลื่อย', 'สิ่งทอ', 'พลาสติก', 'เครื่องจักร/ผลิตภัณฑ์โลหะ', 'สาธารณูปโภค/ไฟฟ้า', 'ขนส่ง', 'สาธารณูปโภค', 'ไฟฟ้า', 'เวชภัณฑ์/โรงพยาบาล/คลินิก', 'อาหาร/เครื่องดื่ม', 'ร้านสะดวกซื้อ', 'โรงแรม/ร้านอาหาร', 'อื่นๆ']);

                const extraIncomeSourceSelect = document.getElementById('extraIncomeSource');
                if (extraIncomeSourceSelect) {
                    extraIncomeSourceSelect.dispatchEvent(new Event('change'));
                }
                const hasExistingLoanSelect = document.getElementById('hasExistingLoan');
                if (hasExistingLoanSelect) {
                    hasExistingLoanSelect.dispatchEvent(new Event('change'));
                }

                renderIncomeDocumentsExisting(customer);

                await homeAddressController.setValues({
                    province: customer.address_province,
                    city: customer.address_district,
                    district: customer.address_subdistrict,
                    post_code: customer.address_postal,
                });

                await workAddressController.setValues({
                    province: customer.useHomeAddress ? customer.address_province : customer.workAddressProvince,
                    city: customer.useHomeAddress ? customer.address_district : customer.workAddressDistrict,
                    district: customer.useHomeAddress ? customer.address_subdistrict : customer.workAddressSubdistrict,
                    post_code: customer.useHomeAddress ? customer.address_postal : customer.workAddressPostal,
                });

                await documentAddressController.setValues({
                    province: customer.documentAddressProvince,
                    post_code: customer.documentAddressPostal,
                });

                await refAddressController.setValues({
                    province: customer.refAddressProvince,
                    city: customer.refAddressDistrict,
                    district: customer.refAddressSubdistrict,
                    post_code: customer.refAddressPostal,
                });

                syncConditionalSections();

                if (!customer.useHomeAddress) {
                    setFieldValue('workAddressNo', customer.workAddressNo);
                    setFieldValue('workAddressFloor', customer.workAddressFloor);
                    setFieldValue('workAddressVillage', customer.workAddressVillage);
                    setFieldValue('workAddressBuilding', customer.workAddressBuilding);
                    setFieldValue('workAddressSoi', customer.workAddressSoi);
                    setFieldValue('workAddressRoad', customer.workAddressRoad);
                    setFieldValue('workAddressSubdistrict', customer.workAddressSubdistrict);
                    setFieldValue('workAddressDistrict', customer.workAddressDistrict);
                    setFieldValue('workAddressProvince', customer.workAddressProvince);
                    setFieldValue('workAddressPostal', customer.workAddressPostal);
                }
            }

            // View Modal Elements
            const viewModal = document.getElementById('viewConsentModal');
            const closeViewBtn = document.getElementById('closeViewConsentModal');
            const closeViewFooterBtn = document.getElementById('closeViewConsentFooter');

            // Handle "อื่นๆ" choice for คำนำหน้านาม
            const titleSelect = document.getElementById('title');
            const titleOtherWrapper = document.getElementById('title_other_wrapper');
            const titleOtherInput = document.getElementById('title_other');
            const nameGroup = document.getElementById('nameGroup');

            if (titleSelect) {
                titleSelect.addEventListener('change', function() {
                    if (this.value === 'อื่นๆ') {
                        titleOtherWrapper.classList.remove('hidden');
                        titleOtherInput.setAttribute('required', 'required');
                        nameGroup.classList.remove('col-9');
                        nameGroup.classList.add('col-6');
                    } else {
                        titleOtherWrapper.classList.add('hidden');
                        titleOtherInput.removeAttribute('required');
                        titleOtherInput.value = '';
                        nameGroup.classList.remove('col-6');
                        nameGroup.classList.add('col-9');
                    }
                });
            }

            const idTypeSelect = document.getElementById('id_type');
            const idNumberLabel = document.getElementById('idNumberLabel');
            const idCardInput = document.getElementById('id_card');

            function syncIdentityDocumentField() {
                if (!idTypeSelect || !idNumberLabel || !idCardInput) {
                    return;
                }

                if (idTypeSelect.value === 'passport') {
                    idNumberLabel.innerHTML = `${consentI18n.form.step1.passportNumber} <span class="required-asterisk">*</span>`;
                    idCardInput.placeholder = consentI18n.form.step1.passportPlaceholder;
                    idCardInput.maxLength = 20;
                    idCardInput.removeAttribute('inputmode');
                    idCardInput.value = idCardInput.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                    return;
                }

                idNumberLabel.innerHTML = `${consentI18n.form.step1.idCardNumber} <span class="required-asterisk">*</span>`;
                idCardInput.placeholder = consentI18n.form.step1.idCardPlaceholder;
                idCardInput.maxLength = 13;
                idCardInput.setAttribute('inputmode', 'numeric');
                idCardInput.value = idCardInput.value.replace(/[^0-9]/g, '').slice(0, 13);
            }

            if (idTypeSelect) {
                idTypeSelect.addEventListener('change', syncIdentityDocumentField);
            }

            // Handle "อื่นๆ" choice for applicant's occupation
            const occupationSelect = document.getElementById('occupation');
            const occupationOtherWrapper = document.getElementById('occupationOtherWrapper');
            const occupationOtherInput = document.getElementById('occupationOther');
            const governmentLevelWrapper = document.getElementById('governmentLevelWrapper');
            const governmentLevelInput = document.getElementById('governmentLevel');

            if (occupationSelect) {
                occupationSelect.addEventListener('change', function() {
                    // Handle government level
                    if (this.value === 'ข้าราชการ') {
                        governmentLevelWrapper.classList.remove('hidden');
                        governmentLevelInput.setAttribute('required', 'required');
                    } else {
                        governmentLevelWrapper.classList.add('hidden');
                        governmentLevelInput.removeAttribute('required');
                        governmentLevelInput.value = '';
                    }

                    // Handle occupation other
                    if (this.value === 'อื่นๆ') {
                        occupationOtherWrapper.classList.remove('hidden');
                        occupationOtherInput.setAttribute('required', 'required');
                    } else {
                        occupationOtherWrapper.classList.add('hidden');
                        occupationOtherInput.removeAttribute('required');
                        occupationOtherInput.value = '';
                    }
                });
            }

            // Handle career field
            const careerFieldSelect = document.getElementById('careerField');
            const careerFieldOtherWrapper = document.getElementById('careerFieldOtherWrapper');
            const careerFieldOtherInput = document.getElementById('careerFieldOther');

            if (careerFieldSelect) {
                careerFieldSelect.addEventListener('change', function() {
                    if (this.value === 'อื่นๆ') {
                        careerFieldOtherWrapper.classList.remove('hidden');
                        careerFieldOtherInput.setAttribute('required', 'required');
                    } else {
                        careerFieldOtherWrapper.classList.add('hidden');
                        careerFieldOtherInput.removeAttribute('required');
                        careerFieldOtherInput.value = '';
                    }
                });
            }

            // Handle extra income source field
            const extraIncomeSourceSelect = document.getElementById('extraIncomeSource');
            const extraIncomeSourceOtherWrapper = document.getElementById('extraIncomeSourceOtherWrapper');
            const extraIncomeSourceOtherInput = document.getElementById('extraIncomeSourceOther');

            if (extraIncomeSourceSelect && extraIncomeSourceOtherWrapper && extraIncomeSourceOtherInput) {
                extraIncomeSourceSelect.addEventListener('change', function() {
                    if (this.value === 'อื่นๆ') {
                        extraIncomeSourceOtherWrapper.classList.remove('hidden');
                        extraIncomeSourceOtherInput.setAttribute('required', 'required');
                    } else {
                        extraIncomeSourceOtherWrapper.classList.add('hidden');
                        extraIncomeSourceOtherInput.removeAttribute('required');
                        extraIncomeSourceOtherInput.value = '';
                    }
                });
            }

            // Handle other debts installment field
            const hasOtherDebtsSelect = document.getElementById('hasOtherDebts');
            const otherDebtInstallmentWrapper = document.getElementById('otherDebtInstallmentWrapper');
            const otherDebtInstallmentInput = document.getElementById('otherDebtInstallment');

            if (hasOtherDebtsSelect) {
                hasOtherDebtsSelect.addEventListener('change', function() {
                    if (this.value === 'มี') {
                        otherDebtInstallmentWrapper.classList.remove('hidden');
                        otherDebtInstallmentInput.setAttribute('required', 'required');
                    } else {
                        otherDebtInstallmentWrapper.classList.add('hidden');
                        otherDebtInstallmentInput.removeAttribute('required');
                        otherDebtInstallmentInput.value = '';
                    }
                });
            }

            // Handle existing loan fields
            const hasExistingLoanSelect = document.getElementById('hasExistingLoan');
            const existingLoanInstitutionCountWrapper = document.getElementById('existingLoanInstitutionCountWrapper');
            const existingLoanInstitutionCountInput = document.getElementById('existingLoanInstitutionCount');
            const existingLoanTotalAmountWrapper = document.getElementById('existingLoanTotalAmountWrapper');
            const existingLoanTotalAmountInput = document.getElementById('existingLoanTotalAmount');

            if (hasExistingLoanSelect && existingLoanInstitutionCountWrapper && existingLoanInstitutionCountInput && existingLoanTotalAmountWrapper && existingLoanTotalAmountInput) {
                hasExistingLoanSelect.addEventListener('change', function() {
                    if (this.value === 'ใช่') {
                        existingLoanInstitutionCountWrapper.classList.remove('hidden');
                        existingLoanTotalAmountWrapper.classList.remove('hidden');
                        existingLoanInstitutionCountInput.setAttribute('required', 'required');
                        existingLoanTotalAmountInput.setAttribute('required', 'required');
                    } else {
                        existingLoanInstitutionCountWrapper.classList.add('hidden');
                        existingLoanTotalAmountWrapper.classList.add('hidden');
                        existingLoanInstitutionCountInput.removeAttribute('required');
                        existingLoanTotalAmountInput.removeAttribute('required');
                        existingLoanInstitutionCountInput.value = '';
                        existingLoanTotalAmountInput.value = '';
                    }
                });
            }

            // Handle residence status
            const residenceStatusSelect = document.getElementById('residence_status');

            if (residenceStatusSelect) {
                residenceStatusSelect.addEventListener('change', function() {
                    // Reserved for future residence-status-specific interactions.
                });
            }

            // Handle business type "อื่นๆ"
            const businessTypeSelect = document.getElementById('businessType');
            const businessTypeOtherWrapper = document.getElementById('businessTypeOtherWrapper');
            const businessTypeOtherInput = document.getElementById('businessTypeOther');

            if (businessTypeSelect) {
                businessTypeSelect.addEventListener('change', function() {
                    if (this.value === 'อื่นๆ') {
                        businessTypeOtherWrapper.classList.remove('hidden');
                        businessTypeOtherInput.setAttribute('required', 'required');
                    } else {
                        businessTypeOtherWrapper.classList.add('hidden');
                        businessTypeOtherInput.removeAttribute('required');
                        businessTypeOtherInput.value = '';
                    }
                });
            }

            // Handle copy home address to work address
            const useHomeAddressCheckbox = document.getElementById('useHomeAddress');
            const homeAddressFields = [
                { id: 'address_no', workId: 'workAddressNo' },
                { id: 'address_floor', workId: 'workAddressFloor' },
                { id: 'address_village', workId: 'workAddressVillage' },
                { id: 'address_building', workId: 'workAddressBuilding' },
                { id: 'address_soi', workId: 'workAddressSoi' },
                { id: 'address_road', workId: 'workAddressRoad' },
                { id: 'address_subdistrict', workId: 'workAddressSubdistrict' },
                { id: 'address_district', workId: 'workAddressDistrict' },
                { id: 'address_province', workId: 'workAddressProvince' },
                { id: 'address_postal', workId: 'workAddressPostal' }
            ];

            async function syncHomeAddressToWorkAddress() {
                homeAddressFields.forEach(field => {
                    const homeInput = document.getElementById(field.id);
                    const workInput = document.getElementById(field.workId);
                    if (!homeInput || !workInput) return;
                    workInput.value = homeInput.value;
                });

                await workAddressController.setValues(homeAddressController.getValues());
            }

            if (useHomeAddressCheckbox) {
                useHomeAddressCheckbox.addEventListener('change', async function() {
                    homeAddressFields.forEach(field => {
                        const homeInput = document.getElementById(field.id);
                        const workInput = document.getElementById(field.workId);

                        if (!homeInput || !workInput) return;
                        
                        if (this.checked) {
                            workInput.readOnly = true;
                            workInput.dataset.syncReadonly = 'true';
                            workInput.style.background = '#f3f4f6';
                            workInput.style.cursor = 'not-allowed';
                        } else {
                            workInput.value = '';
                            workInput.readOnly = false;
                            workInput.dataset.syncReadonly = 'false';
                            workInput.style.background = '';
                            workInput.style.cursor = '';
                        }
                    });

                    workAddressController.setReadOnly(this.checked);

                    if (this.checked) {
                        await syncHomeAddressToWorkAddress();
                    } else {
                        await workAddressController.reset();
                    }
                });

                // Also listen to changes in home address fields when checkbox is checked
                homeAddressFields.forEach(field => {
                    const homeInput = document.getElementById(field.id);
                    const syncHandler = async function() {
                        if (useHomeAddressCheckbox.checked) {
                            await syncHomeAddressToWorkAddress();
                        }
                    };

                    homeInput?.addEventListener('input', syncHandler);
                    homeInput?.addEventListener('change', syncHandler);
                });
            }

            // Handle previous work section visibility (if work experience <1 year)
            const workYearsInput = document.getElementById('workYears');
            const workMonthsInput = document.getElementById('workMonths');
            const previousWorkSection = document.getElementById('previousWorkSection');

            function togglePreviousWorkSection() {
                const totalMonths = (parseInt(workYearsInput?.value) || 0) * 12 + (parseInt(workMonthsInput?.value) || 0);
                const shouldShow = totalMonths < 12;
                
                if (previousWorkSection) {
                    if (shouldShow) {
                        previousWorkSection.classList.remove('hidden');
                    } else {
                        previousWorkSection.classList.add('hidden');
                    }
                }
            }

            if (workYearsInput) workYearsInput.addEventListener('input', togglePreviousWorkSection);
            if (workMonthsInput) workMonthsInput.addEventListener('input', togglePreviousWorkSection);
            togglePreviousWorkSection();

            // Handle custom loan amount field
            const loanAmountTypeSelect = document.getElementById('loanAmountType');
            const customLoanAmountWrapper = document.getElementById('customLoanAmountWrapper');
            const customLoanAmountInput = document.getElementById('customLoanAmount');

            if (loanAmountTypeSelect) {
                loanAmountTypeSelect.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        customLoanAmountWrapper.classList.remove('hidden');
                        customLoanAmountInput.setAttribute('required', 'required');
                    } else {
                        customLoanAmountWrapper.classList.add('hidden');
                        customLoanAmountInput.removeAttribute('required');
                        customLoanAmountInput.value = '';
                    }
                });
            }

            // Handle payment method toggle
            const paymentMethodSelect = document.getElementById('paymentMethod');
            const directDebitWrapper = document.getElementById('directDebitWrapper');
            const directDebitAmountInput = document.getElementById('directDebitAmount');
            const directDebitAccountNumberInput = document.getElementById('directDebitAccountNumber');

            if (paymentMethodSelect && directDebitWrapper) {
                const displayAmount = document.getElementById('display_directDebitAmount');
                const displayAccountNumber = document.getElementById('display_directDebitAccountNumber');

                const syncDirectDebitText = () => {
                    if (displayAmount) {
                        const val = directDebitAmountInput?.value;
                        displayAmount.textContent = val ? Number(val).toLocaleString('th-TH') : '.....................................';
                    }
                    if (displayAccountNumber) {
                        displayAccountNumber.textContent = directDebitAccountNumberInput?.value || '.........................................';
                    }
                };

                paymentMethodSelect.addEventListener('change', function() {
                    if (this.value === 'ชําระโดยการหักบัญชี') {
                        directDebitWrapper.classList.remove('hidden');
                        directDebitAmountInput?.setAttribute('required', 'required');
                        directDebitAccountNumberInput?.setAttribute('required', 'required');
                        syncDirectDebitText();
                    } else {
                        directDebitWrapper.classList.add('hidden');
                        directDebitAmountInput?.removeAttribute('required');
                        directDebitAccountNumberInput?.removeAttribute('required');
                        if (directDebitAmountInput) directDebitAmountInput.value = '';
                        if (directDebitAccountNumberInput) directDebitAccountNumberInput.value = '';
                    }
                });

                directDebitAmountInput?.addEventListener('input', syncDirectDebitText);
                directDebitAccountNumberInput?.addEventListener('input', syncDirectDebitText);
            }

            const section4Container = document.getElementById('section4_container');
            section4Container?.classList.remove('hidden');

            // Restrict identity document input based on selected document type
            if (idCardInput) {
                idCardInput.addEventListener('input', function() {
                    if (idTypeSelect?.value === 'passport') {
                        this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 20);
                        return;
                    }

                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
                });
            }

            const phoneMobileInput = document.getElementById('phone_mobile');
            if (phoneMobileInput) {
                phoneMobileInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }

            // Allow clicking anywhere on date inputs to open the date picker
            document.querySelectorAll('input[type="date"]').forEach(function(dateInput) {
                dateInput.addEventListener('click', function() {
                    try { this.showPicker(); } catch(e) {}
                });
                dateInput.style.cursor = 'pointer';
            });

            function openModal() {
                modal.style.display = 'flex';
                // Force layout reflow
                modal.offsetHeight;
                modal.classList.add('show');
            }

            function closeModal() {
                const consentIdField = document.getElementById('consent_id');
                const hadData = consentIdField && consentIdField.value;
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
                if (hadData) {
                    updateDashboard(window.location.href);
                }
            }

            function openPdfModal() {
                pdfModal.style.display = 'flex';
                const modalBody = pdfModal.querySelector('.modal-body');
                if (modalBody) modalBody.scrollTop = 0;
                pdfModal.offsetHeight;
                pdfModal.classList.add('show');
            }

            function closePdfModal() {
                pdfModal.classList.remove('show');
                setTimeout(() => {
                    pdfModal.style.display = 'none';
                }, 300);
            }

            function closeViewModal() {
                viewModal.classList.remove('show');
                setTimeout(() => {
                    viewModal.style.display = 'none';
                }, 300);
            }

            function initializeFormSignature(signatureData) {
                const seq = ++signatureInitSeq;
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        if (seq !== signatureInitSeq) return;
                        initSignaturePad();
                        applySignatureData(signatureData);

                        const clearBtn = document.getElementById('clearSignatureBtn');
                        if (clearBtn && !clearBtn.dataset.bound) {
                            clearBtn.addEventListener('click', clearSignature);
                            clearBtn.dataset.bound = 'true';
                        }
                    });
                });
            }

            function openCreateModal() {
                closePdfModal();
                prepareCreateModal();
                openModal();
                initializeFormSignature(signatureDataInput?.value || '');
            }

            window.editDocument = async function(customer) {
                let fullCustomer = customer;
                const customerId = customer?.id;
                if (customerId) {
                    try {
                        const baseUrl = consentBaseUrl;
                        const response = await fetch(`${baseUrl}/${customerId}/data`, { headers: { 'Accept': 'application/json' } });
                        if (response.ok) {
                            fullCustomer = await response.json();
                        }
                    } catch (error) {
                    }
                }

                prepareEditModal(fullCustomer);
                openModal();
                initializeFormSignature(signatureDataInput?.value || '');
            };

            // AJAX Dashboard Updates
            async function updateDashboard(url) {
                const tableEl = document.querySelector('.consent-table');
                const summaryEl = document.querySelector('.summary-cards');
                const paginationEl = document.querySelector('.pagination-container');
                const loadingOverlay = document.getElementById('tableLoadingOverlay');
                
                if (loadingOverlay) loadingOverlay.classList.add('active');
                if (tableEl) tableEl.style.opacity = '0.5';
                if (summaryEl) summaryEl.style.opacity = '0.5';
                if (paginationEl) paginationEl.style.opacity = '0.5';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!response.ok) throw new Error('Network response was not ok');
                    const html = await response.text();
                    
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newSummary = doc.querySelector('.summary-cards');
                    if (newSummary && summaryEl) {
                        summaryEl.innerHTML = newSummary.innerHTML;
                    }
                    
                    const newTable = doc.querySelector('.consent-table');
                    if (newTable && tableEl) {
                        tableEl.innerHTML = newTable.innerHTML;
                    }

                    const newPagination = doc.querySelector('.pagination-container');
                    if (paginationEl) {
                        if (newPagination) {
                            paginationEl.innerHTML = newPagination.innerHTML;
                            paginationEl.style.display = '';
                        } else {
                            paginationEl.style.display = 'none';
                        }
                    } else if (newPagination) {
                        const tableCard = document.querySelector('.card');
                        const pagContainer = document.createElement('div');
                        pagContainer.className = 'pagination-container';
                        pagContainer.innerHTML = newPagination.innerHTML;
                        tableCard.parentNode.insertBefore(pagContainer, tableCard.nextSibling);
                    }
                    
                    window.history.pushState({}, '', url);
                    bindDeleteForms();
                } catch (error) {
                    console.error('Error loading page:', error);
                } finally {
                    if (loadingOverlay) loadingOverlay.classList.remove('active');
                    if (tableEl) tableEl.style.opacity = '1';
                    if (summaryEl) summaryEl.style.opacity = '1';
                    if (paginationEl) paginationEl.style.opacity = '1';
                }
            }

            function bindDeleteForms() {
                document.querySelectorAll('.delete-consent-form').forEach(function(form) {
                    if (form.dataset.ajaxBound) return;
                    form.dataset.ajaxBound = 'true';

                    form.addEventListener('submit', async function(event) {
                        event.preventDefault();
                        const customerName = this.dataset.name || 'รายการนี้';
                        if (!window.confirm(`ยืนยันการลบใบยินยอมของ ${customerName} ?`)) {
                            return;
                        }

                        try {
                            const url = this.action;
                            const formData = new FormData(this);
                            form.style.opacity = '0.5';

                            const response = await fetch(url, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            if (response.ok) {
                                updateDashboard(window.location.href);
                            } else {
                                const errData = await response.json();
                                alert(errData.message || 'เกิดข้อผิดพลาดในการลบข้อมูล');
                            }
                        } catch (error) {
                            console.error('Delete error:', error);
                            alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                        } finally {
                            form.style.opacity = '1';
                        }
                    });
                });
            }

            // Bind delete forms on load
            bindDeleteForms();

            // Intercept Search Form Submission
            const searchForm = document.querySelector('.search-filter-form');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const url = new URL(this.action);
                    const formData = new FormData(this);
                    for (const [key, value] of formData.entries()) {
                        if (value) {
                            url.searchParams.set(key, value);
                        } else {
                            url.searchParams.delete(key);
                        }
                    }
                    updateDashboard(url.toString());
                });
            }

            // Intercept Search Form Reset
            const resetBtn = document.querySelector('.btn-reset');
            if (resetBtn) {
                resetBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const qInput = document.getElementById('search_q');
                    if (qInput) qInput.value = '';
                    const statusSelect = document.getElementById('search_status');
                    if (statusSelect) statusSelect.value = '';
                    const fromInput = document.getElementById('search_date_from');
                    if (fromInput) fromInput.value = '';
                    const toInput = document.getElementById('search_date_to');
                    if (toInput) toInput.value = '';
                    updateDashboard(this.getAttribute('href'));
                });
            }

            // Intercept Pagination Clicks
            document.addEventListener('click', function(e) {
                const paginationLink = e.target.closest('.pagination-container a');
                if (paginationLink) {
                    e.preventDefault();
                    const url = paginationLink.getAttribute('href');
                    if (url && url !== '#') {
                        updateDashboard(url);
                    }
                }
            });

            if (openBtn) openBtn.addEventListener('click', openPdfModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

            if (closePdfBtn) closePdfBtn.addEventListener('click', closePdfModal);
            if (cancelPdfBtn) cancelPdfBtn.addEventListener('click', closePdfModal);
            if (proceedToConsentBtn) proceedToConsentBtn.addEventListener('click', openCreateModal);

            if (closeViewBtn) closeViewBtn.addEventListener('click', closeViewModal);
            if (closeViewFooterBtn) closeViewFooterBtn.addEventListener('click', closeViewModal);

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('openCreate') === '1') {
                openPdfModal();
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Initialize Signature Pad
            function initSignaturePad() {
                const canvas = document.getElementById('signaturePad');
                if (!canvas) return;

                // Set canvas size correctly for high DPI
                const rect = canvas.getBoundingClientRect();
                const ratio = window.devicePixelRatio || 1;
                
                // Only initialize if we have a valid width (visible)
                if (rect.width === 0) return;

                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                
                if (signaturePad) {
                    signaturePad.off(); // Remove old listeners
                }
                
                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)'
                });
                
                // Rescale context for high DPI
                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                
                // Save signature data to hidden input (as JSON)
                signaturePad.addEventListener('endStroke', function() {
                    if (signatureDataInput) {
                    signatureDataInput.value = JSON.stringify(signaturePad.toData());
                }
            });

            // Handle resize to keep signature pad working
            window.addEventListener('resize', () => {
                if (currentStep === 7) {
                    initSignaturePad();
                }
            });
        }
            
            function clearSignature() {
                if (signaturePad) {
                    signaturePad.clear();
                    if (signatureDataInput) {
                        signatureDataInput.value = '';
                    }
                }
            }

            if (consentForm && !consentForm.dataset.boundSubmit) {
                consentForm.addEventListener('submit', async function(event) {
                    event.preventDefault(); // Intercept all submits

                    // For the final step, we use saveStepData(8)
                    const result = await saveStepData(8);
                    
                    if (result.ok) {
                        closeModal();
                        updateDashboard(window.location.href);
                    } else {
                        // Error! The alert is already shown inside saveStepData or showValidationErrors
                        if (result.errors) {
                            showValidationErrors(result.errors);
                        } else {
                            alert(result.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                        }
                    }
                });
                consentForm.dataset.boundSubmit = 'true';
            }

            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
               
                if (event.target === viewModal) {
                    closeViewModal();
                }
            });
        });
    </script>
    
        <!-- ZIP Contents Modal -->
        <div id="zipContentsModal" class="modal" style="display:none;">
            <div class="modal-content modal-sm">
                <div class="modal-header">
                    <h3>ไฟล์ใน ZIP</h3>
                    <button type="button" class="close-btn" id="closeZipContentsModal">&times;</button>
                </div>
                <div class="modal-body" id="zipContentsBody" style="max-height:60vh; overflow:auto; padding:1rem;">
                    <div id="zipContentsList">กำลังโหลด...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="zipContentsCloseBtn" class="btn">ปิด</button>
                </div>
            </div>
        </div>

        <script>
        (function(){
            const zipContentsModal = document.getElementById('zipContentsModal');
            const zipContentsList = document.getElementById('zipContentsList');
            const closeZipContentsModalBtn = document.getElementById('closeZipContentsModal');
            const zipContentsCloseBtn = document.getElementById('zipContentsCloseBtn');

            function openZipContentsModal(){
                if (!zipContentsModal) return;
                zipContentsModal.style.display = 'flex';
                zipContentsModal.offsetHeight; // reflow
                zipContentsModal.classList.add('show');
            }

            function closeZipContentsModal(){
                if (!zipContentsModal) return;
                zipContentsModal.classList.remove('show');
                setTimeout(()=>{ zipContentsModal.style.display='none'; zipContentsList.innerHTML=''; }, 200);
            }

            if (closeZipContentsModalBtn) closeZipContentsModalBtn.addEventListener('click', closeZipContentsModal);
            if (zipContentsCloseBtn) zipContentsCloseBtn.addEventListener('click', closeZipContentsModal);

            // Delegate click for ZIP view buttons
            document.addEventListener('click', async function(e){
                const btn = e.target.closest('.file-view-zip-btn');
                if (!btn) return;

                btn.disabled = true;
                const originalText = btn.textContent;
                btn.textContent = 'กำลังโหลด...';

                try {
                    const docId = btn.dataset.docId || '';
                    const customerId = document.getElementById('consent_id')?.value || '';
                    if (!docId || !customerId) throw new Error('ไม่พบข้อมูลเอกสารหรือหมายเลขคำขอ');

                    const contentsUrl = `${consentBaseUrl}/${customerId}/income-documents/${docId}/zip-contents`;
                    const response = await fetch(contentsUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(text || 'ไม่สามารถดึงรายการไฟล์จาก ZIP ได้');
                    }

                    const data = await response.json();
                    const entries = Array.isArray(data.entries) ? data.entries : [];

                    if (!entries.length) {
                        zipContentsList.innerHTML = '<div>ไม่มีไฟล์ภายใน ZIP</div>';
                    } else {
                        const itemsHtml = entries.map(function(entry){
                            const name = entry?.name ?? String(entry);
                            const encoded = encodeURIComponent(name);
                            const fileUrl = `${consentBaseUrl}/${customerId}/income-documents/${docId}/zip-file?inner=${encoded}`;
                            return `<div class="zip-entry-row"><a href="${fileUrl}" target="_blank" rel="noopener">${escapeHtml(name)}</a></div>`;
                        }).join('');
                        zipContentsList.innerHTML = itemsHtml;
                    }

                    openZipContentsModal();
                } catch (err) {
                    alert(err.message || 'เกิดข้อผิดพลาด');
                } finally {
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            });
        })();
        </script>
@endsection
