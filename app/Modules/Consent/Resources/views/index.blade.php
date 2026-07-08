@extends('layouts.app', ['title' => 'รายงานใบยินยอม'])

@section('content')
    <section class="dashboard">
        <div class="hero compact-hero hero-with-actions">
            <div class="hero-body">
                <h2>รายงานใบยินยอม</h2>
                <p>สรุปสถานะการเซ็นใบยินยอมของลูกค้า</p>
            </div>
            <div class="hero-actions">
                <button type="button" id="openConsentModal" class="action-btn">+ สร้างใบยินยอม</button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <div class="alert-title">บันทึกข้อมูลไม่สำเร็จ กรุณาตรวจสอบข้อมูลอีกครั้ง</div>
                <ul class="alert-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="summary-cards compact-summary">
            <div class="summary-card">
                <div class="summary-label">ลูกค้าทั้งหมด</div>
                <div class="summary-value">{{ $total }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">ผ่านเกณฑ์</div>
                <div class="summary-value">{{ $approved }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">ไม่ผ่านเกณฑ์</div>
                <div class="summary-value">{{ $rejected }}</div>
            </div>
        </section>

        <div class="card">
            <div class="consent-table">
                <table>
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>ชื่อ</th>
                            <th>วันที่ทำรายการ</th>
                            <th>สถานะ</th>
                            <th>การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td data-label="รหัส">{{ $customer->app_no ?? '-' }}</td>
                                <td data-label="ชื่อ">{{ $customer->applicant?->name ?? '-' }}</td>
                                <td data-label="วันที่ทำรายการ">{{ $customer->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td data-label="สถานะ">
                                    @if($customer->status === 'approved')
                                        <span class="badge badge-signed">ผ่าน</span>
                                    @elseif($customer->status === 'rejected')
                                        <span class="badge badge-pending">ไม่ผ่าน</span>
                                    @else
                                        <span class="badge">รอดำเนินการ</span>
                                    @endif
                                </td>
                                <td data-label="การกระทำ">
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="action-btn outline table-action-btn-small"
                                            onclick="viewDocument({ id: {{ Js::from($customer->encrypted_id) }} }); return false;"
                                        >
                                            ดูเอกสาร
                                        </button>
                                        <button
                                            type="button"
                                            class="action-btn table-action-btn-small"
                                            onclick="editDocument({ id: {{ Js::from($customer->encrypted_id) }} }); return false;"
                                        >
                                            แก้ไข
                                        </button>
                                        <form method="POST" action="{{ route('consent.destroy', $customer->encrypted_id) }}" class="delete-consent-form table-actions-form" data-name="{{ $customer->applicant?->name ?? '-' }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="action-btn table-action-btn-delete"
                                            >
                                                ลบ
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-cell">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($customers, 'links'))
            <div class="pagination-container">
                {{ $customers->onEachSide(1)->links() }}
            </div>
        @endif
    </section>

    <div id="modalMount"></div>

    <!-- PDF Consent Modal -->
    <div id="pdfConsentModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">ข้อมูลผลิตภัณฑ์และเงื่อนไขการสมัคร</h3>
                <button type="button" class="close-btn" id="closePdfModal" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body" style="padding: 0; overflow-y: auto; height: 75vh; background: #f3f4f6;">
                <div style="padding: 1.5rem;">
                    <h4 style="margin: 0 0 1rem 0; color: #10b981; font-size: 1.1rem;">1. Sale Sheet</h4>
                    <div style="height: 70vh; margin-bottom: 2rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden; background: #ffffff;">
                         <iframe src="{{ asset('file/Sale Sheet - BMPver. 2_Personal Loan_App - Eng.pdf') }}#toolbar=1&navpanes=0&view=FitH" width="100%" height="100%" style="border: none;"></iframe>
                     </div>
 
                     <h4 style="margin: 0 0 1rem 0; color: #10b981; font-size: 1.1rem;">2. เงื่อนไขและข้อตกลงการสมัคร</h4>
                     <div style="height: 70vh; border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden; background: #ffffff;">
                         <iframe src="{{ asset('file/ใบสมัคร BMPver. 2_Personal Loan_App - Eng 4-5.pdf') }}#toolbar=1&navpanes=0&view=FitH" width="100%" height="100%" style="border: none;"></iframe>
                     </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-actions modal-form-actions">
                    <button type="button" class="action-btn outline" id="cancelPdfModal">ยกเลิก</button>
                    <button type="button" class="action-btn" id="proceedToConsentModal">ยอมรับและดำเนินการต่อ</button>
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
                        return `<div style="margin-bottom: 0.35rem;"><a href="${url}" target="_blank" rel="noopener" style="color: #10b981; text-decoration: none;">📄 ${escapeHtml(name)}</a></div>`;
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
            let appNoBoxesHtml = '<div style="display: inline-flex; gap: 3px; align-items: center;">';
            for (let i = 0; i < 13; i++) {
                const char = appNoStr[i].trim() ? appNoStr[i] : '&nbsp;';
                appNoBoxesHtml += `<span style="border: 1.5px solid #1f2937; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-family: \'Courier New\', Courier, monospace; font-weight: bold; background: #ffffff; border-radius: 2px;">${char}</span>`;
            }
            appNoBoxesHtml += '</div>';

            contentDiv.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px double #10b981; padding-bottom: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 1.4rem; font-weight: 700; color: #065f46;">บริษัท บิ๊ก มันนี่ พลัส จำกัด</div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: #4b5563; margin-top: 0.25rem;">ใบคำขอให้บริการสินเชื่อส่วนบุคคล (Personal Loan)</div>
                        <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 0.25rem;">App No.: ${customer.app_no || '-'}</div>
                    </div>
                    <div style="text-align: right; display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                        <div style="font-size: 0.9rem; font-weight: 600;">วันที่: <span style="border-bottom: 1px dashed #9ca3af; padding: 0 0.5rem;">${appDateFormatted}</span></div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 0.9rem; font-weight: 600;">App No.</span>
                            ${appNoBoxesHtml}
                        </div>
                    </div>
                </div>

                <!-- ส่วนที่ 1: สำหรับเจ้าหน้าที่บริษัท -->
                <div style="margin-bottom: 1.5rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #166534; margin: 0 0 0.5rem 0; font-size: 0.95rem; font-weight: 700; border-bottom: 1px solid #bbf7d0; padding-bottom: 0.35rem;">ส่วนที่ 1: สำหรับเจ้าหน้าที่บริษัท</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr>
                            <td style="padding: 0.25rem 0; font-weight: 600; width: 20%;">เจ้าหน้าที่สินเชื่อ:</td>
                            <td style="padding: 0.25rem 0; color: #1f2937;">${officer_name}</td>
                            <td style="padding: 0.25rem 0; font-weight: 600; width: 15%; text-align: right;">เบอร์ติดต่อ:</td>
                            <td style="padding: 0.25rem 0; color: #1f2937; width: 30%; padding-left: 0.5rem;">${officer_phone}</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: #10b981; border-left: 4px solid #10b981; padding-left: 0.5rem; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700;">ส่วนที่ 2: ข้อมูลส่วนตัวผู้ขอสินเชื่อ</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">คำนำหน้านาม - ชื่อ - นามสกุล:</td>
                            <td style="padding: 0.5rem;">${title} ${name}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">Name - Surname (EN):</td>
                            <td style="padding: 0.5rem; text-transform: uppercase;">${name_en}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">เลขประจำตัวประชาชน:</td>
                            <td style="padding: 0.5rem;">${id_card}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">วัน / เดือน / ปีเกิด:</td>
                            <td style="padding: 0.5rem;">${birthdate}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">สัญชาติ:</td>
                            <td style="padding: 0.5rem;">สัญชาติ ${nationality}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">สถานภาพสมรส:</td>
                            <td style="padding: 0.5rem;">${marital_status}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">การศึกษา:</td>
                            <td style="padding: 0.5rem;">${education}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">อาชีพ:</td>
                            <td style="padding: 0.5rem;">
                                ${occupation}
                                ${customer.occupation === 'ข้าราชการ' && customer.governmentLevel ? ` (ระดับ: ${governmentLevel})` : ''}
                                ${customer.occupation === 'อื่นๆ' && customer.occupationOther ? ` (ระบุ: ${occupationOther})` : ''}
                            </td>
                        </tr>
                        ${customer.careerField ? `
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">สาขาอาชีพ:</td>
                            <td style="padding: 0.5rem;">
                                ${careerField}
                                ${customer.careerField === 'อื่นๆ' && customer.careerFieldOther ? ` (ระบุ: ${careerFieldOther})` : ''}
                            </td>
                        </tr>
                        ` : ''}
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">รายได้รวมต่อเดือน:</td>
                            <td style="padding: 0.5rem; color: #166534; font-weight: 700;">${income}</td>
                        </tr>
                        ${customer.extraIncome && parseInt(customer.extraIncome) > 0 ? `
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">รายได้อื่นๆ:</td>
                            <td style="padding: 0.5rem;">${extraIncome}</td>
                        </tr>
                        ` : ''}
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">แหล่งที่มาของรายได้:</td>
                            <td style="padding: 0.5rem;">${extraIncomeSource}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">ประเทศที่มาของรายได้:</td>
                            <td style="padding: 0.5rem;">${incomeCountry}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">ไฟล์หลักฐานการเงิน:</td>
                            <td style="padding: 0.5rem;">${incomeDocumentsHtml}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">ภาระหนี้อื่นๆ ในปัจจุบัน:</td>
                            <td style="padding: 0.5rem;">${hasOtherDebts}</td>
                        </tr>
                        ${customer.hasOtherDebts === 'มี' ? `
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">ยอดผ่อนต่อเดือน:</td>
                            <td style="padding: 0.5rem;">${otherDebtInstallment}</td>
                        </tr>
                        ` : ''}
                    </table>
                </div>

                <div style="margin-bottom: 1.5rem; background: #e0f2fe; border: 1px solid #0ea5e9; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #0369a1; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #0ea5e9; padding-bottom: 0.35rem;">การชี้แจงการมีสินเชื่อบุคคล</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600;">ปัจจุบันมีวงเงินสินเชื่อส่วนบุคคล และวงเงินที่อยู่ระหว่างขอยื่น/ขอเพิ่มตั้งแต่ 2 เดือนก่อนหน้าจนถึงปัจจุบัน จากสถาบันการเงิน/ผู้ประกอบธุรกิจสินเชื่อบุคคลที่ไม่ใช่สถาบันการเงินมากกว่า 2 แห่งหรือไม่:</td>
                            <td style="padding: 0.5rem;">${hasExistingLoan || '-'}</td>
                        </tr>
                        ${hasExistingLoan === 'ใช่' ? `
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600;">จำนวนแห่ง:</td>
                            <td style="padding: 0.5rem;">${existingLoanInstitutionCount}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600;">รวมทั้งสิ้น:</td>
                            <td style="padding: 0.5rem;">${existingLoanTotalAmount}</td>
                        </tr>
                        ` : ''}
                    </table>
                    <div style="margin-top: 0.75rem; background: #fef9c3; border: 1px solid #facc15; padding: 0.6rem 0.85rem; border-radius: 0.375rem; font-size: 0.85rem; color: #854d0e;">
                        ( หมายเหตุ กรณีกรอกข้อมูลไม่ถูกต้องไม่ครบถ้วน และ/หรือมีรายได้หรือกระเเสเงินสดหมุนเวียนเข้าในบัญชีเงินฝากสถาบันการเงินโดยเฉลี่ยน้อยกว่า 30,000 บาทต่อเดือน โดยมีวงเงินสินเชื่อส่วนบุคคลรวมตั้งแต่ 3 แห่งขึ้นไป บริษัทมีสิทธิปฏิเสธการให้สินเชื่อ หรือกรณีที่ทําสัญญาเงินกู้ ให้ถือว่าบริษัทมีสิทธิลดหรือยกเลิกวงเงินได้ทันที )
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem; background: #fce7f3; border: 1px solid #f472b6; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #be185d; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #f472b6; padding-bottom: 0.35rem;">ข้อมูลที่อยู่</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600;">สถานะของการอยู่อาศัย / Residence type:</td>
                            <td style="padding: 0.5rem;">${residence_status}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600;">ที่อยู่ปัจจุบัน / Current address:</td>
                            <td style="padding: 0.5rem;">
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
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600;">หมายเลขโทรศัพท์บ้าน:</td>
                            <td style="padding: 0.5rem;">${phone_home}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600;">หมายเลขโทรศัพท์มือถือ:</td>
                            <td style="padding: 0.5rem;">${phone_mobile}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600;">E-mail:</td>
                            <td style="padding: 0.5rem;">${email}</td>
                        </tr>
                    </table>
                </div>

                <!-- Work Address Section -->
                <div style="margin-bottom: 1.5rem; background: #e0f2fe; border: 1px solid #0ea5e9; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #0369a1; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #0ea5e9; padding-bottom: 0.35rem;">สถานที่ทำงานปัจจุบัน</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ชื่อกิจการ/ที่ทำงาน:</td>
                            <td style="padding: 0.5rem;">${companyName}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ประเภทธุรกิจ:</td>
                            <td style="padding: 0.5rem;">${businessType}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">แผนก/ฝ่าย:</td>
                            <td style="padding: 0.5rem;">${workDepartment}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ที่อยู่ที่ทำงาน:</td>
                            <td style="padding: 0.5rem;">
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
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">หมายเลขโทรศัพท์ (ที่ทำงาน):</td>
                            <td style="padding: 0.5rem;">${workPhone}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">อายุงานรวม:</td>
                            <td style="padding: 0.5rem;">${workYears} ปี ${workMonths} เดือน</td>
                        </tr>
                    </table>
                </div>

                <!-- Previous Work Section (conditional) -->
                ${(parseInt(workYears) * 12 + parseInt(workMonths) < 12) ? `
                <div style="margin-bottom: 1.5rem; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #d97706; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #f59e0b; padding-bottom: 0.35rem;">ที่ทำงานเดิม</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #fef3c7;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ชื่อที่ทำงานเดิม:</td>
                            <td style="padding: 0.5rem;">${previousCompanyName}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fef3c7;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ตำแหน่ง:</td>
                            <td style="padding: 0.5rem;">${previousPosition}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fef3c7;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">รายได้ต่อเดือน:</td>
                            <td style="padding: 0.5rem;">${previousIncome}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fef3c7;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ที่อยู่ที่ทำงานเดิม:</td>
                            <td style="padding: 0.5rem;">${previousWorkAddress}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">หมายเลขโทรศัพท์:</td>
                            <td style="padding: 0.5rem;">${previousPhone}</td>
                        </tr>
                    </table>
                </div>
                ` : ''}

                <!-- Document Delivery Section -->
                <div style="margin-bottom: 1.5rem; background: #dcfce7; border: 1px solid #22c55e; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #166534; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #22c55e; padding-bottom: 0.35rem;">ช่องทางการรับเอกสาร</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #dcfce7;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ช่องทางการรับเอกสาร:</td>
                            <td style="padding: 0.5rem;">${documentDelivery}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #dcfce7;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ที่อยู่ตามเอกสารสําคัญ:</td>
                            <td style="padding: 0.5rem;">${documentAddressText}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">จังหวัด / รหัสไปรษณีย์:</td>
                            <td style="padding: 0.5rem;">
                                ${documentAddressProvince !== '-' ? documentAddressProvince : '-'}
                                ${documentAddressPostal !== '-' ? ' ' + documentAddressPostal : ''}
                            </td>
                        </tr>
                        ${birthPlaceAddress !== '-' ? `
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ที่อยู่บ้านเกิด:</td>
                            <td style="padding: 0.5rem;">${birthPlaceAddress}</td>
                        </tr>
                        ` : ''}
                    </table>
                </div>

                <!-- Reference Person Section -->
                <div style="margin-bottom: 1.5rem; background: #ede9fe; border: 1px solid #a78bfa; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #6d28d9; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #a78bfa; padding-bottom: 0.35rem;">ข้อมูลบุคคลอ้างอิง</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #ede9fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ชื่อ - นามสกุล:</td>
                            <td style="padding: 0.5rem;">${refName}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #ede9fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ความสัมพันธ์กับผู้กู้:</td>
                            <td style="padding: 0.5rem;">${refRelation}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #ede9fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ที่อยู่:</td>
                            <td style="padding: 0.5rem;">
                                ${refAddressNo !== '-' ? 'เลขที่ ' + refAddressNo : ''}
                                ${refAddressFloor !== '-' ? ' ชั้น ' + refAddressFloor : ''}
                                ${refAddressVillage !== '-' ? ' หมู่ที่ ' + refAddressVillage : ''}
                                ${refAddressBuilding !== '-' ? ' ' + refAddressBuilding : ''}
                                ${refAddressSoi !== '-' ? ' ซอย ' + refAddressSoi : ''}
                                ${refAddressRoad !== '-' ? ' ถนน ' + refAddressRoad : ''}
                                ${refAddressSubdistrict !== '-' ? ' แขวง/ตำบล ' + refAddressSubdistrict : ''}
                                ${refAddressDistrict !== '-' ? ' เขต/อำเภอ ' + refAddressDistrict : ''}
                                ${refAddressProvince !== '-' ? ' จังหวัด ' + refAddressProvince : ''}
                                ${refAddressPostal !== '-' ? ' ' + refAddressPostal : ''}
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #ede9fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">หมายเลขโทรศัพท์บ้าน:</td>
                            <td style="padding: 0.5rem;">${refPhoneHome}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #ede9fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">หมายเลขโทรศัพท์มือถือ:</td>
                            <td style="padding: 0.5rem;">${refPhoneMobile}</td>
                        </tr>
                    </table>
                </div>

                <!-- Loan request section -->
                <div style="margin-bottom: 1.5rem; background: #fffbeb; border: 1px solid #f59e0b; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #d97706; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #f59e0b; padding-bottom: 0.35rem;">ความประสงค์ในการสมัครใช้สินเชื่อ</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #fffbeb;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ระยะเวลาผ่อนชำระคืน:</td>
                            <td style="padding: 0.5rem;">${loanTerm}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fffbeb;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">วงเงินสินเชื่อที่ต้องการ:</td>
                            <td style="padding: 0.5rem;">
                                ${loanAmountType === 'full' ? 'เต็มจำนวนตามที่บริษัทอนุมัติ' : loanAmountType === 'custom' ? `วงเงินที่ขอกู้/จำนวนทั้งสิ้น: ${customLoanAmount}` : '-'}
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fffbeb;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">วัตถุประสงค์ในการขอสินเชื่อ:</td>
                            <td style="padding: 0.5rem;">${loanPurpose}</td>
                        </tr>
                    </table>
                </div>

                <!-- Bank account section -->
                <div style="margin-bottom: 1.5rem; background: #f0fdf4; border: 1px solid #22c55e; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #166534; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #22c55e; padding-bottom: 0.35rem;">ความประสงค์ขอรับวงเงินกู้ครั้งแรกเข้าบัญชีเงินฝาก</h4>
                    <p style="font-size: 0.85rem; color: #4b5563; margin-bottom: 1rem;">ในกรณีที่บริษัทอนุมัติสินเชื่อ ข้าพเจ้ามีความประสงค์ให้บริษัทโอนเงินกู้เข้าบัญชีของข้าพเจ้า โดยโอนเข้าบัญชีเงินฝากเลขที่ (กรอกข้อมูล)</p>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #f0fdf4;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">เลขที่บัญชี:</td>
                            <td style="padding: 0.5rem;">${accountNumber}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f0fdf4;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ประเภทบัญชี:</td>
                            <td style="padding: 0.5rem;">${accountType}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f0fdf4;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ธนาคาร:</td>
                            <td style="padding: 0.5rem;">${bankName}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ชื่อบัญชี:</td>
                            <td style="padding: 0.5rem;">${accountName}</td>
                        </tr>
                    </table>
                </div>

                <!-- Payment method section -->
                <div style="margin-bottom: 1.5rem; background: #e0f2fe; border: 1px solid #0ea5e9; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #0369a1; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #0ea5e9; padding-bottom: 0.35rem;">วิธีการชําระเงิน</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">วิธีการชําระเงิน:</td>
                            <td style="padding: 0.5rem;">${paymentMethod}</td>
                        </tr>
                        ${paymentMethod === 'ชําระโดยการหักบัญชี' ? `
                        <tr>
                            <td colspan="2" style="padding: 0.5rem; font-size: 0.85rem; color: #4b5563; line-height: 1.6; background: #fffbeb; border-radius: 0.375rem; margin-top: 0.5rem; display: block;">
                                กรณียินยอมหักบัญชี ข้าพเจ้ายินยอมให้สถาบันการเงินหักเงินจากบัญชีเงินเดือนของข้าพเจ้าที่มีอยู่กับสถาบันการเงิน เป็นจํานวนเงิน <strong>${directDebitAmount}</strong> บาท/เดือน จากบัญชีเลขที่ <strong>${directDebitAccountNumber}</strong> เท่านั้น ณ วันครบกําหนดชําระตามที่บริษัทแจ้งให้ทราบ หรือทุกวันที่เงินเดือนออกในแต่ละเดือนแล้วแต่วันใดจะถึงก่อน เพื่อชําระเงินกู้รวมทั้งดอกเบี้ยจนกว่าจะชําระหนี้ให้แก่บริษัทจนเสร็จสิ้น หากบริษัทไม่สามารถหักเงินจากบัญชีดังกล่าวในวันดังกล่าวได้ ข้าพเจ้าตกลงยอมรับให้บริษัทถือว่าเป็นการผิดนัดชําระหนี้และขอรับรองว่าการที่บริษัทหักเงินจากบัญชีของข้าพเจ้าตามใบสมัครฉบับนี้เป็นไปตามคําร้องขอของข้าพเจ้า หากมีความเสียหายหรือผิดพลาดใดๆ เกิดขึ้นแก่บริษัท ข้าพเจ้าตกลงชดใช้ค่าเสียหายให้แก่บริษัททั้งจํานวนทันที
                            </td>
                        </tr>
                        ` : ''}
                    </table>
                </div>

                <!-- Section 5: ข้อความยินยอม -->
                <div style="margin-bottom: 1.5rem; background: #e5f0ff; border: 1px solid #3b82f6; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #1e40af; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #3b82f6; padding-bottom: 0.35rem;">ส่วนที่ 5: ข้อความยินยอม</h4>
                    <p style="text-align: left; line-height: 1.8; margin-bottom: 1.5rem; text-indent: 3rem;">
                        ข้าพเจ้าขอรับรองว่าข้อความข้างต้นเป็นความจริงทุกประการ รวมทั้งได้รับทราบเงื่อนไขและหลักเกณฑ์ที่กำหนดในการใช้บริการสินเชื่อ โดยลงนามในใบสมัครสินเชื่อส่วนบุคคล (Personal Loan) นี้ และเมื่อบริษัทอนุมัติสินเชื่อดังกล่าวให้ข้าพเจ้าแล้ว ข้าพเจ้าตกลงปฏิบัติตามภาระผูกพันที่เกิดขึ้นตามสัญญาสินเชื่อส่วนบุคคล (Personal Loan) ที่ปรากฏอยู่ในใบสมัครนี้ รวมทั้งข้อกำหนด/เงื่อนไขในการใช้สินเชื่อภายใต้ชื่อสินเชื่อส่วนบุคคล (Personal Loan) ของบริษัท และให้ถือว่าใบสมัครสินเชื่อส่วนบุคคล (Personal Loan) นี้ เป็นส่วนหนึ่งของสัญญาสินเชื่อส่วนบุคคล (Personal Loan) ด้วย ข้าพเจ้าได้อ่านและเข้าใจข้อกำหนดและเงื่อนไขต่างๆ ที่เกี่ยวข้องถี่ถ้วนแล้ว พร้อมทั้งได้รับสำเนาสัญญาสินเชื่อส่วนบุคคล (Personal Loan) ไว้เรียบร้อยแล้ว จึงลงลายมือชื่อไว้เป็นหลักฐาน
                    </p>
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 0.5rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
                        <h5 style="color: #856404; margin: 0 0 0.75rem 0; font-size: 0.95rem; font-weight: 700;">ข้อควรระวัง</h5>
                        <ul style="margin: 0; padding-left: 1.5rem; list-style-type: disc; color: #856404;">
                            <li style="margin-bottom: 0.5rem;">บริษัทจะคิดดอกเบี้ยตั้งแต่วันที่ผู้ขอกู้ได้รับเงินกู้ กรณีผิดนัดชำระหรือชำระต่ำกว่ายอดชำระขั้นต่ำจะมีดอกเบี้ยและค่าใช้จ่ายในการติดตามทวงถามหนี้เพิ่ม</li>
                            <li style="margin-bottom: 0.5rem;">โปรดทำความเข้าใจผลิตภัณฑ์และเงื่อนไขก่อนลงนาม หากมีข้อสงสัยหรือต้องการสอบถามข้อมูลเพิ่มเติม สามารถติดต่อได้ที่โทรศัพท์ 082-257-7997</li>
                            <li>บริษัทอาจมอบหมายให้ผู้ที่รับมอบหมายจำเป็นที่ต้องดำเนินการทางกฎหมาย หากท่านผิดนัดชำระ หรือไม่ชำระค่างวดอย่างสม่ำเสมอ</li>
                        </ul>
                    </div>
                </div>

                <!-- Signature area -->
                <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                    <div style="text-align: center; width: 300px;">
                        <canvas id="viewSignaturePad" style="width: 100%; height: 120px; border: 1px solid #e5e7eb; border-radius: 0.25rem; margin-bottom: 0.5rem; background: #ffffff;"></canvas>
                        <p style="margin: 0; font-weight: 600;">ลงนามผู้ขอสินเชื่อ</p>
                        <p style="margin: 0.25rem 0 0.5rem 0; color: #4b5563;">( ${title} ${name} )</p>
                        <p style="margin: 0; color: #6b7280;">วันที่เซ็น: ${signed_date}</p>
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
                    console.log(`[Wizard] Saving step ${step}...`);
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
                        console.log(`[Wizard] Step ${step} saved. Consent ID: ${result.consent_id}`);
                        if (result.consent_id) {
                            const idField = document.getElementById('consent_id');
                            if (idField) idField.value = result.consent_id;
                        }
                        return { ok: true, data: result };
                    } else {
                        console.error(`[Wizard] Save failed for step ${step}:`, result.message || result.errors);
                        return { 
                            ok: false, 
                            message: result.message || 'กรุณาตรวจสอบข้อมูลที่กรอก', 
                            errors: result.errors 
                        };
                    }
                } catch (error) {
                    console.error(`[Wizard] Error in step ${step}:`, error);
                    return { ok: false, message: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' };
                }
            }

            function clearValidationErrors() {
                modal?.querySelectorAll('.form-error').forEach(el => el.remove());
                modal?.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
            }

            function showValidationErrors(errors) {
                clearValidationErrors();
                let errorMessages = [];

                Object.entries(errors).forEach(([field, messages]) => {
                    const input = consentForm?.querySelector(`[name="${field}"], [name="${field}[]"]`);
                    if (input) {
                        input.classList.add('input-error');
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'form-error';
                        errorDiv.textContent = messages[0];
                        input.closest('.form-group')?.appendChild(errorDiv);
                        
                        // Collect messages for alert
                        errorMessages.push(messages[0]);
                    }
                });

                if (errorMessages.length > 0) {
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
                step.addEventListener('click', function() {
                    const targetStep = parseInt(this.dataset.step);
                    // Allow jump back always, allow jump forward only if reached
                    if (targetStep < currentStep || targetStep <= maxStepReached) {
                        currentStep = targetStep;
                        updateWizardUI();
                    }
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

            function renderIncomeDocumentsExisting(customer) {
                const wrapper = document.getElementById('incomeDocumentsExistingWrapper');
                const list = document.getElementById('incomeDocumentsExistingList');
                if (!wrapper || !list) return;

                const documents = Array.isArray(customer?.incomeDocuments) ? customer.incomeDocuments : [];
                if (!documents.length) {
                    wrapper.classList.add('hidden');
                    list.innerHTML = '';
                    return;
                }

                wrapper.classList.remove('hidden');
                list.innerHTML = documents
                    .map(function(document) {
                        const url = document?.downloadUrl ?? '#';
                        const name = document?.originalName ?? 'ไฟล์แนบ';
                        const typeLabel = document?.documentTypeLabel || 'ไฟล์แนบ';
                        const destroyUrl = document?.destroyUrl ?? '';
                        const deleteButton = destroyUrl
                            ? `<button type="button" class="file-remove-btn" data-destroy-url="${escapeHtml(destroyUrl)}">ลบ</button>`
                            : '';
                        return `<div class="file-attachment-row">
                            <a href="${escapeHtml(url)}" target="_blank" rel="noopener">📄 ${escapeHtml(typeLabel)}: ${escapeHtml(name)}</a>
                            ${deleteButton}
                        </div>`;
                    })
                    .join('');
            }

            const incomeDocumentsInput = document.getElementById('incomeDocuments');
            const incomeDocumentsSelectedWrapper = document.getElementById('incomeDocumentsSelectedWrapper');
            const incomeDocumentsSelectedList = document.getElementById('incomeDocumentsSelectedList');
            const incomeDocumentsExistingList = document.getElementById('incomeDocumentsExistingList');
            const identityDocumentsInput = document.getElementById('identityDocuments');
            const identityDocumentsSelectedWrapper = document.getElementById('identityDocumentsSelectedWrapper');
            const identityDocumentsSelectedList = document.getElementById('identityDocumentsSelectedList');
            let incomeDocumentsTransfer = null;
            let identityDocumentsTransfer = null;
            let objectUrls = [];

            function clearObjectUrls() {
                objectUrls.forEach(url => URL.revokeObjectURL(url));
                objectUrls = [];
            }

            function resetIncomeDocumentsSelection() {
                clearObjectUrls();
                if (incomeDocumentsInput) {
                    incomeDocumentsInput.value = '';
                }
                incomeDocumentsTransfer = null;
                if (incomeDocumentsSelectedWrapper) {
                    incomeDocumentsSelectedWrapper.classList.add('hidden');
                }
                if (incomeDocumentsSelectedList) {
                    incomeDocumentsSelectedList.innerHTML = '';
                }
            }

            function resetIdentityDocumentsSelection() {
                clearObjectUrls();
                if (identityDocumentsInput) {
                    identityDocumentsInput.value = '';
                }
                identityDocumentsTransfer = null;
                if (identityDocumentsSelectedWrapper) {
                    identityDocumentsSelectedWrapper.classList.add('hidden');
                }
                if (identityDocumentsSelectedList) {
                    identityDocumentsSelectedList.innerHTML = '';
                }
            }

            function renderIncomeDocumentsSelection() {
                if (!incomeDocumentsInput || !incomeDocumentsSelectedWrapper || !incomeDocumentsSelectedList) {
                    return;
                }

                clearObjectUrls();
                const files = Array.from(incomeDocumentsInput.files || []);
                if (!files.length) {
                    incomeDocumentsSelectedWrapper.classList.add('hidden');
                    incomeDocumentsSelectedList.innerHTML = '';
                    return;
                }

                incomeDocumentsSelectedWrapper.classList.remove('hidden');
                incomeDocumentsSelectedList.innerHTML = files
                    .map(function(file, index) {
                        const url = URL.createObjectURL(file);
                        objectUrls.push(url);
                        return `<div class="file-attachment-row">
                            <a href="${url}" target="_blank" rel="noopener">📄 ${escapeHtml(file.name)}</a>
                            <button type="button" class="file-remove-btn" data-remove-index="${index}">ลบ</button>
                        </div>`;
                    })
                    .join('');
            }

            function renderIdentityDocumentsSelection() {
                if (!identityDocumentsInput || !identityDocumentsSelectedWrapper || !identityDocumentsSelectedList) {
                    return;
                }

                clearObjectUrls();
                const files = Array.from(identityDocumentsInput.files || []);
                if (!files.length) {
                    identityDocumentsSelectedWrapper.classList.add('hidden');
                    identityDocumentsSelectedList.innerHTML = '';
                    return;
                }

                identityDocumentsSelectedWrapper.classList.remove('hidden');
                identityDocumentsSelectedList.innerHTML = files
                    .map(function(file, index) {
                        const url = URL.createObjectURL(file);
                        objectUrls.push(url);
                        return `<div class="file-attachment-row">
                            <a href="${url}" target="_blank" rel="noopener">📄 ${escapeHtml(file.name)}</a>
                            <button type="button" class="file-remove-btn" data-remove-identity-index="${index}">ลบ</button>
                        </div>`;
                    })
                    .join('');
            }

            if (incomeDocumentsInput && incomeDocumentsSelectedWrapper && incomeDocumentsSelectedList) {
                incomeDocumentsInput.addEventListener('change', function() {
                    const previousFiles = incomeDocumentsTransfer ? Array.from(incomeDocumentsTransfer.files) : [];
                    const newFiles = Array.from(incomeDocumentsInput.files || []);
                    const nextTransfer = new DataTransfer();
                    const seen = new Set();

                    [...previousFiles, ...newFiles].forEach(function(file) {
                        const key = [file.name, file.size, file.lastModified].join('|');
                        if (seen.has(key)) {
                            return;
                        }
                        seen.add(key);
                        nextTransfer.items.add(file);
                    });

                    incomeDocumentsTransfer = nextTransfer;
                    incomeDocumentsInput.files = nextTransfer.files;
                    renderIncomeDocumentsSelection();
                });
            }

            if (identityDocumentsInput && identityDocumentsSelectedWrapper && identityDocumentsSelectedList) {
                identityDocumentsInput.addEventListener('change', function() {
                    const previousFiles = identityDocumentsTransfer ? Array.from(identityDocumentsTransfer.files) : [];
                    const newFiles = Array.from(identityDocumentsInput.files || []);
                    const nextTransfer = new DataTransfer();
                    const seen = new Set();

                    [...previousFiles, ...newFiles].forEach(function(file) {
                        const key = [file.name, file.size, file.lastModified].join('|');
                        if (seen.has(key)) {
                            return;
                        }
                        seen.add(key);
                        nextTransfer.items.add(file);
                    });

                    identityDocumentsTransfer = nextTransfer;
                    identityDocumentsInput.files = nextTransfer.files;
                    renderIdentityDocumentsSelection();
                });
            }

            if (incomeDocumentsSelectedList && incomeDocumentsInput) {
                incomeDocumentsSelectedList.addEventListener('click', function(event) {
                    const button = event.target.closest('[data-remove-index]');
                    if (!button) return;
                    const removeIndex = Number(button.dataset.removeIndex);
                    const files = Array.from(incomeDocumentsInput.files || []);
                    if (!Number.isFinite(removeIndex) || removeIndex < 0 || removeIndex >= files.length) {
                        return;
                    }

                    const nextTransfer = new DataTransfer();
                    files.forEach(function(file, index) {
                        if (index !== removeIndex) {
                            nextTransfer.items.add(file);
                        }
                    });
                    incomeDocumentsTransfer = nextTransfer;
                    incomeDocumentsInput.files = nextTransfer.files;
                    renderIncomeDocumentsSelection();
                });
            }

            if (identityDocumentsSelectedList && identityDocumentsInput) {
                identityDocumentsSelectedList.addEventListener('click', function(event) {
                    const button = event.target.closest('[data-remove-identity-index]');
                    if (!button) return;
                    const removeIndex = Number(button.dataset.removeIdentityIndex);
                    const files = Array.from(identityDocumentsInput.files || []);
                    if (!Number.isFinite(removeIndex) || removeIndex < 0 || removeIndex >= files.length) {
                        return;
                    }

                    const nextTransfer = new DataTransfer();
                    files.forEach(function(file, index) {
                        if (index !== removeIndex) {
                            nextTransfer.items.add(file);
                        }
                    });
                    identityDocumentsTransfer = nextTransfer;
                    identityDocumentsInput.files = nextTransfer.files;
                    renderIdentityDocumentsSelection();
                });
            }

            if (incomeDocumentsExistingList) {
                incomeDocumentsExistingList.addEventListener('click', async function(event) {
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

                    const row = button.closest('.file-attachment-row');
                    row?.remove();
                    if (!incomeDocumentsExistingList.querySelector('.file-attachment-row')) {
                        document.getElementById('incomeDocumentsExistingWrapper')?.classList.add('hidden');
                        incomeDocumentsExistingList.innerHTML = '';
                    }
                });
            }

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
                resetIncomeDocumentsSelection();
                resetIdentityDocumentsSelection();
                resetIdentityDocumentsSelection();
                renderIncomeDocumentsExisting(null);
                if (consentModalTitle) {
                    consentModalTitle.textContent = 'สร้างใบยินยอมแบบละเอียด';
                }
                if (consentSubmitBtn) {
                    consentSubmitBtn.textContent = 'บันทึกข้อมูลใบสมัคร';
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
                resetIncomeDocumentsSelection();
                homeAddressController.reset();
                workAddressController.reset();
                documentAddressController.reset();
                refAddressController.reset();
                setFieldValue('consent_id', customer.id);
                if (consentModalTitle) {
                    consentModalTitle.textContent = 'แก้ไขใบยินยอมแบบละเอียด';
                }
                if (consentSubmitBtn) {
                    consentSubmitBtn.textContent = 'บันทึกการแก้ไข';
                }

                const hasPassport = (customer.passport ?? '').toString().trim() !== '';
                const inferredIdType = hasPassport ? 'passport' : 'id_card';
                const inferredIdNumber = hasPassport ? customer.passport : customer.id_card;

                setFieldValue('id_type', inferredIdType);

                [
                    'app_date', 'app_no', 'officer_name', 'officer_phone', 'title', 'name', 'name_en', 'birthdate', 'id_card',
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
                    idNumberLabel.innerHTML = 'เลขหนังสือเดินทาง <span class="required-asterisk">*</span>';
                    idCardInput.placeholder = 'ระบุเลขหนังสือเดินทาง';
                    idCardInput.maxLength = 20;
                    idCardInput.removeAttribute('inputmode');
                    idCardInput.value = idCardInput.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                    return;
                }

                idNumberLabel.innerHTML = 'เลขบัตรประจำตัวประชาชน <span class="required-asterisk">*</span>';
                idCardInput.placeholder = 'เลข 13 หลัก';
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
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
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

            document.querySelectorAll('.delete-consent-form').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    const customerName = this.dataset.name || 'รายการนี้';
                    if (!window.confirm(`ยืนยันการลบใบยินยอมของ ${customerName} ?`)) {
                        event.preventDefault();
                    }
                });
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
                        // Success! Redirect to index or show success message
                        window.location.href = "{{ route('consent.index') }}";
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
                if (event.target === modal) {
                    closeModal();
                }
                if (event.target === pdfModal) {
                    closePdfModal();
                }
                if (event.target === viewModal) {
                    closeViewModal();
                }
            });
        });
    </script>
@endsection
