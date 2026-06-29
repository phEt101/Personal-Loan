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
                <div style="font-weight: 700; margin-bottom: 0.5rem;">บันทึกข้อมูลไม่สำเร็จ กรุณาตรวจสอบข้อมูลอีกครั้ง</div>
                <ul style="margin: 0; padding-left: 1.25rem;">
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
                                <td data-label="ชื่อ">{{ $customer->name }}</td>
                                <td data-label="วันที่ทำรายการ">{{ $customer->transaction_date ?? '-' }}</td>
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
                                            class="action-btn outline"
                                            onclick="viewDocument({{ Js::from($customer) }}); return false;"
                                            style="padding: 0.4rem 0.75rem; min-width: 88px;"
                                        >
                                            ดูเอกสาร
                                        </button>
                                        <button
                                            type="button"
                                            class="action-btn"
                                            onclick="editDocument({{ Js::from($customer) }}); return false;"
                                            style="padding: 0.4rem 0.75rem; min-width: 70px;"
                                        >
                                            แก้ไข
                                        </button>
                                        <form method="POST" action="{{ route('consent.destroy', $customer->id) }}" class="delete-consent-form table-actions-form" data-name="{{ $customer->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="action-btn"
                                                style="padding: 0.4rem 0.75rem; min-width: 56px; background: #dc2626;"
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
    </section>

    <div id="modalMount"></div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

    <script>
        const modalEndpoints = {
            form: @json(route('consent.modals.form')),
            view: @json(route('consent.modals.view')),
        };

        let consentModalLoadPromise = null;
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
            
            const title = customer.title || 'นาย/นาง/นางสาว';
            const name = customer.name || '-';
            const name_en = customer.name_en || '-';
            
            // Format Dates
            const dob = customer.dob ? new Date(customer.dob).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
            const appDateFormatted = customer.app_date ? new Date(customer.app_date).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
            
            const id_card = customer.id_card || '-';
            const gender = customer.gender || '-';
            const age = customer.age ? customer.age + ' ปี' : '-';
            const nationality = customer.nationality || '-';
            const marital_status = customer.marital_status || '-';
            const education = customer.education || '-';
            const occupation = customer.occupation || '-';
            const income = customer.income ? parseInt(customer.income).toLocaleString('th-TH') + ' บาท' : '-';
            const extraIncome = customer.extraIncome ? parseInt(customer.extraIncome).toLocaleString('th-TH') + ' บาท' : '-';
            const extraIncomeSource = customer.extraIncomeSource || '-';
            const businessIncome = customer.businessIncome || '-';
            const averageMonthlyIncome = customer.averageMonthlyIncome ? parseInt(customer.averageMonthlyIncome).toLocaleString('th-TH') + ' บาท' : '-';
            const hasOtherDebts = customer.hasOtherDebts || '-';
            const otherDebtInstallment = customer.otherDebtInstallment ? parseInt(customer.otherDebtInstallment).toLocaleString('th-TH') + ' บาท' : '-';
            const hasExistingLoan = customer.hasExistingLoan || '-';
            const existingLoanInstallment = customer.existingLoanInstallment ? parseInt(customer.existingLoanInstallment).toLocaleString('th-TH') + ' บาท' : '-';

            const signed_at = customer.signed_at || new Date().toISOString().split('T')[0];
            const signedDateFormatted = new Date(signed_at).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' });

            // Officer & Application Details
            const officer_name = customer.officer_name || '-';
            const officer_phone = customer.officer_phone || '-';
            const spouse_title = customer.spouse_title || '-';
            const spouse_name = customer.spouse_name || '-';
            const spouse_phone = customer.spouse_phone || '-';
            const spouse_mobile = customer.spouse_mobile || '-';
            const spouse_education = customer.spouse_education || '-';
            const spouse_occupation = customer.spouse_occupation || '-';
            const spouse_company = customer.spouse_company || '-';
            const spouse_income = customer.spouse_income ? parseInt(customer.spouse_income).toLocaleString('th-TH') + ' บาท' : '-';
            
            // Address fields
            const dwelling_type = customer.dwelling_type || '-';
            const residence_status = customer.residence_status || '-';
            const residence_rent_amount = customer.residence_rent_amount ? parseInt(customer.residence_rent_amount).toLocaleString('th-TH') + ' บาท' : '-';
            const residence_years = customer.residence_years ? customer.residence_years + ' ปี' : '-';
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
            const line_id = customer.line_id || '-';
            // Work fields
            const companyType = customer.companyType || '-';
            const companyName = customer.companyName || '-';
            const businessType = customer.businessType || '-';
            const workOccupation = customer.workOccupation || '-';
            const workPosition = customer.workPosition || '-';
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
            const previousBusinessType = customer.previousBusinessType || '-';
            const previousPosition = customer.previousPosition || '-';
            const previousIncome = customer.previousIncome ? parseInt(customer.previousIncome).toLocaleString('th-TH') + ' บาท' : '-';
            const previousWorkYears = customer.previousWorkYears || '0';
            const previousPhone = customer.previousPhone || '-';
            // Document delivery fields
            const documentDelivery = customer.documentDelivery || '-';
            const documentEmail = customer.documentEmail || '-';
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
            const refEmail = customer.refEmail || '-';
            const refLineId = customer.refLineId || '-';
            // Loan request fields
            const loanTerm = customer.loanTerm ? `${customer.loanTerm} เดือน` : '-';
            const loanAmountType = customer.loanAmountType || '-';
            const customLoanAmount = customer.customLoanAmount ? parseInt(customer.customLoanAmount).toLocaleString('th-TH') + ' บาท' : '-';
            const loanPurpose = customer.loanPurpose || '-';
            const bankName = customer.bankName || '-';
            const bankBranch = customer.bankBranch || '-';
            const accountName = customer.accountName || '-';
            const accountType = customer.accountType || '-';
            const accountNumber = customer.accountNumber || '-';
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
                            <td style="padding: 0.5rem;">${dob}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">เพศ / อายุ / สัญชาติ:</td>
                            <td style="padding: 0.5rem;">${gender} / ${age} / สัญชาติ ${nationality}</td>
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
                            <td style="padding: 0.5rem;">${occupation}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">รายได้รวมต่อเดือน:</td>
                            <td style="padding: 0.5rem; color: #166534; font-weight: 700;">${income}</td>
                        </tr>
                        ${customer.extraIncome && parseInt(customer.extraIncome) > 0 ? `
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">รายได้พิเศษ:</td>
                            <td style="padding: 0.5rem;">${extraIncome}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">แหล่งที่มาของรายได้พิเศษ:</td>
                            <td style="padding: 0.5rem;">${extraIncomeSource}</td>
                        </tr>
                        ` : ''}
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">ชื่อกิจการ:</td>
                            <td style="padding: 0.5rem;">${businessIncome}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">รายได้จากกิจการ เฉลี่ยต่อเดือน:</td>
                            <td style="padding: 0.5rem;">${averageMonthlyIncome}</td>
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

                ${(customer.income && parseInt(customer.income) < 30000) ? `
                <div style="margin-bottom: 1.5rem; background: #e0f2fe; border: 1px solid #0ea5e9; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #0369a1; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #0ea5e9; padding-bottom: 0.35rem;">การแจ้งการมีวงเงินสินเชื่อบุคคล (เฉพาะผู้มีรายได้น้อยกว่า 30,000 บาท)</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600;">ในช่วง 2 เดือนที่ผ่านมาผู้กู้เคยได้รับอนุมัติสินเชื่อ หรือ ยื่นสมัครสินเชื่อบุคคล/สินเชื่อนาโนไฟแนนซ์/สินเชื่อสวัสดิการพนักงานกับสถาบันการเงินมากกว่า 2 แห่งหรือไม่:</td>
                            <td style="padding: 0.5rem;">${hasExistingLoan || '-'}</td>
                        </tr>
                    </table>
                    <div style="margin-top: 0.75rem; background: #fef9c3; border: 1px solid #facc15; padding: 0.6rem 0.85rem; border-radius: 0.375rem; font-size: 0.85rem; color: #854d0e;">
                        <strong>**</strong> กรณีพบว่ามีสินเชื่อบุคคล/สินเชื่อนาโน/สินเชื่อสวัสดิการพนักงานกับสถาบันการเงินตั้งแต่ 3 แห่งขึ้นไป บริษัทมีสิทธิ์ปฏิเสธการให้สินเชื่อ หรือระงับการให้สินเชื่อ หรือยกเลิกสัญญา
                    </div>
                </div>
                ` : ''}

                ${(marital_status === 'สมรส' || marital_status === 'สมรสไม่จดทะเบียน') ? `
                <div style="margin-bottom: 1.5rem; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #d97706; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #f59e0b; padding-bottom: 0.35rem;">ข้อมูลคู่สมรส</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #fef3c7;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">คำนำหน้านาม - ชื่อ - นามสกุล:</td>
                            <td style="padding: 0.5rem;">${spouse_title} ${spouse_name}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fef3c7;">
                            <td style="padding: 0.5rem; font-weight: 600;">หมายเลขโทรศัพท์:</td>
                            <td style="padding: 0.5rem;">${spouse_phone}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fef3c7;">
                            <td style="padding: 0.5rem; font-weight: 600;">หมายเลขโทรศัพท์มือถือ:</td>
                            <td style="padding: 0.5rem;">${spouse_mobile}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fef3c7;">
                            <td style="padding: 0.5rem; font-weight: 600;">การศึกษา:</td>
                            <td style="padding: 0.5rem;">${spouse_education}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fef3c7;">
                            <td style="padding: 0.5rem; font-weight: 600;">อาชีพ / บริษัท:</td>
                            <td style="padding: 0.5rem;">${spouse_occupation} (${spouse_company})</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600;">รายได้ต่อเดือน:</td>
                            <td style="padding: 0.5rem; color: #d97706; font-weight: 700;">${spouse_income}</td>
                        </tr>
                    </table>
                </div>
                ` : ''}

                <div style="margin-bottom: 1.5rem; background: #fce7f3; border: 1px solid #f472b6; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #be185d; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #f472b6; padding-bottom: 0.35rem;">ข้อมูลที่อยู่</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ลักษณะที่อยู่อาศัย:</td>
                            <td style="padding: 0.5rem;">${dwelling_type}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600;">สถานที่อยู่:</td>
                            <td style="padding: 0.5rem;">${residence_status}</td>
                        </tr>
                        ${customer.residence_status === 'เช่า/ผ่อนชำระ' ? `
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600;">ยอดเช่า/ผ่อนต่อเดือน:</td>
                            <td style="padding: 0.5rem;">${residence_rent_amount}</td>
                        </tr>
                        ` : ''}
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600;">อยู่อาศัยมาเป็นเวลา:</td>
                            <td style="padding: 0.5rem;">${residence_years}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #fce7f3;">
                            <td style="padding: 0.5rem; font-weight: 600;">ที่อยู่:</td>
                            <td style="padding: 0.5rem;">
                                ${address_no !== '-' ? 'เลขที่ ' + address_no : ''}
                                ${address_floor !== '-' ? ' ชั้น ' + address_floor : ''}
                                ${address_village !== '-' ? ' หมู่ ' + address_village : ''}
                                ${address_building !== '-' ? ' ' + address_building : ''}
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
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600;">Line ID:</td>
                            <td style="padding: 0.5rem;">${line_id}</td>
                        </tr>
                    </table>
                </div>

                <!-- Work Address Section -->
                <div style="margin-bottom: 1.5rem; background: #e0f2fe; border: 1px solid #0ea5e9; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #0369a1; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #0ea5e9; padding-bottom: 0.35rem;">สถานที่ทำงานปัจจุบัน</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ประเภทสถานที่ทำงาน:</td>
                            <td style="padding: 0.5rem;">${companyType}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ชื่อกิจการ/ที่ทำงาน:</td>
                            <td style="padding: 0.5rem;">${companyName}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ประเภทธุรกิจ:</td>
                            <td style="padding: 0.5rem;">${businessType}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">อาชีพ:</td>
                            <td style="padding: 0.5rem;">${workOccupation}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ตำแหน่ง:</td>
                            <td style="padding: 0.5rem;">${workPosition}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">อายุงาน:</td>
                            <td style="padding: 0.5rem;">${workYears} ปี ${workMonths} เดือน</td>
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
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ประเภทธุรกิจ:</td>
                            <td style="padding: 0.5rem;">${previousBusinessType}</td>
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
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">อายุงาน:</td>
                            <td style="padding: 0.5rem;">${previousWorkYears} ปี</td>
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
                    <h4 style="color: #166534; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #22c55e; padding-bottom: 0.35rem;">สถานที่ส่งเอกสาร</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #dcfce7;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">สถานที่ส่งเอกสาร:</td>
                            <td style="padding: 0.5rem;">${documentDelivery}</td>
                        </tr>
                        ${documentDelivery === 'E-mail' ? `
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">E-mail:</td>
                            <td style="padding: 0.5rem;">${documentEmail}</td>
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
                        <tr style="border-bottom: 1px solid #ede9fe;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">E-mail:</td>
                            <td style="padding: 0.5rem;">${refEmail}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">Line ID:</td>
                            <td style="padding: 0.5rem;">${refLineId}</td>
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
                    <h4 style="color: #166534; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #22c55e; padding-bottom: 0.35rem;">ข้อมูลบัญชีสำหรับรับโอนเงินกู้</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #f0fdf4;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ธนาคาร:</td>
                            <td style="padding: 0.5rem;">${bankName}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f0fdf4;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">สาขา:</td>
                            <td style="padding: 0.5rem;">${bankBranch}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f0fdf4;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ชื่อบัญชี:</td>
                            <td style="padding: 0.5rem;">${accountName}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f0fdf4;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ประเภทบัญชี:</td>
                            <td style="padding: 0.5rem;">${accountType}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">เลขที่บัญชี:</td>
                            <td style="padding: 0.5rem;">${accountNumber}</td>
                        </tr>
                    </table>
                </div>

                <!-- Section 5: ข้อความยินยอม -->
                <div style="margin-bottom: 1.5rem; background: #e5f0ff; border: 1px solid #3b82f6; border-radius: 0.5rem; padding: 0.85rem 1.25rem;">
                    <h4 style="color: #1e40af; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #3b82f6; padding-bottom: 0.35rem;">ส่วนที่ 5: ข้อความยินยอม</h4>
                    <p style="text-align: left; line-height: 1.8; margin-bottom: 1.5rem; text-indent: 3rem;">
                        ข้าพเจ้าขอรับรองว่าข้อมูลรายละเอียดที่ระบุไว้ข้างต้นเป็นความจริงทุกประการ และข้าพเจ้ารับทราบการมอบอำนาจให้ทางบริษัทติดต่อสอบถาม และ/หรือ ตรวจสอบข้อมูลรายละเอียดต่างๆ ของข้าพเจ้าในบัตรประชาชน และ/หรือ บุคคลที่เกี่ยวข้องได้จากบุคคล และ/หรือ นิติบุคคลอื่นใดและไม่ว่าด้วยวิธีใด นอกจากข้าพเจ้ารับทราบให้บริษัทมีสิทธิอย่างสมบูรณ์ที่จะปฏิเสธ หรืออนุมัติการขอสินเชื่อครั้งนี้ หรืออนุมัติเป็นอย่างอื่น รวมทั้งปฏิบัติตามข้อกำหนดและเงื่อนไขตามที่บริษัทเห็นสมควรทุกประการ และ/หรือ ที่บริษัทจะเปลี่ยนแปลงภายหลัง และข้าพเจ้ารับทราบที่จะเสียค่าธรรมเนียม ค่าใช้จ่ายต่างๆ ที่บริษัทกำหนดทุกประการ การดำเนินการของบริษัทตามความประสงค์ของข้าพเจ้าในคำขอฉบับนี้ให้ถือว่าข้าพเจ้าได้รับสินเชื่อโดยชอบธรรมจากบริษัท (ผู้ได้รับผลประโยชน์ที่แท้จริงคือผู้ขอสินเชื่อ)
                    </p>
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 0.5rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
                        <h5 style="color: #856404; margin: 0 0 0.75rem 0; font-size: 0.95rem; font-weight: 700;">ข้อควรระวัง</h5>
                        <ul style="margin: 0; padding-left: 1.5rem; list-style-type: disc; color: #856404;">
                            <li style="margin-bottom: 0.5rem;">หากท่านผิดนัดชำระหนี้ บริษัทจะคิดดอกเบี้ยสูงสุดตั้งแต่วันที่เริ่มผิดนัด และอาจจะมีค่าติดตามทวงถามหนี้ (คิดเมื่อครบกำหนดชำระ และมีการทวงถามหนี้แล้ว)</li>
                            <li>กรุณาอ่านข้อกำหนด และเงื่อนไขที่สำคัญก่อนลงนาม หากมีข้อสงสัยสามารถติดต่อเจ้าหน้าที่ เบอร์โทรศัพท์ 082-257-7997</li>
                        </ul>
                    </div>
                    <p style="text-align: left; line-height: 1.8; margin-bottom: 0.75rem; text-indent: 3rem;">
                        ข้าพเจ้าทราบว่าบริษัทอาจเก็บรวบรวมข้อมูลส่วนบุคคลของข้าพเจ้าและบุคคลที่ข้าพเจ้าระบุไว้ในเอกสารนี้ เช่น ผู้กู้ร่วม ผู้ค้ำประกัน เพื่อใช้ในการบริหารความเสี่ยงของบริษัท และขอรับรองว่า ข้าพเจ้าได้แจ้งให้บุคคลดังกล่าวทราบถึงการเก็บรวบรวมข้อมูลส่วนบุคคลนี้ด้วย
                    </p>
                    <p style="text-align: left; line-height: 1.8; text-indent: 3rem;">
                        ข้าพเจ้าได้อ่านและทำความเข้าใจ รับทราบถึงเนื้อหาของประกาศความเป็นส่วนตัวของบริษัท ดังที่ปรากฏรายละเอียดหน้าเว็บไซต์ <a href="https://www.bigmoneyplus.co.th/การคุ้มครองข้อมูลส่วนบุคคล" target="_blank" style="color: #059669; text-decoration: underline;">www.bigmoneyplus.co.th/การคุ้มครองข้อมูลส่วนบุคคล</a> และรับทราบว่าบริษัทเก็บรวบรวมใช้ และ/หรือ เปิดเผยข้อมูลส่วนบุคคลภายใต้หรือเกี่ยวกับคำขอฉบับนี้เพื่อวัตถุประสงค์ตามที่ระบุไว้ในประกาศความเป็นส่วนตัวของบริษัท
                    </p>
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
            const openBtn = document.getElementById('openConsentModal');
            const closeBtn = document.getElementById('closeConsentModal');
            const cancelBtn = document.getElementById('cancelConsentModal');
            const consentForm = document.getElementById('consentForm');
            const consentModalTitle = document.getElementById('consentModalTitle');
            const consentFormMethod = document.getElementById('consentFormMethod');
            const consentSubmitBtn = document.getElementById('consentSubmitBtn');
            const appNoInput = document.getElementById('app_no');
            const appDateInput = document.getElementById('app_date');
            const signatureDataInput = document.getElementById('signatureData');
            const modalStoreUrl = modal?.dataset.storeUrl || '';
            const modalUpdateBaseUrl = modal?.dataset.updateBaseUrl || '';
            const modalNextAppNo = modal?.dataset.nextAppNo || '';
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
                            this.removeAttribute('readonly');
                        }, { once: true });
                    }
                });
            }

            disableFormAutofill(modal?.querySelector('.consent-form'));

            const postCodeApi = {
                options: @json(route('consent.postcodes.options')),
            };

            const addressProvinceInput = document.getElementById('address_province');
            const addressDistrictInput = document.getElementById('address_district');
            const addressSubdistrictInput = document.getElementById('address_subdistrict');
            const addressPostalInput = document.getElementById('address_postal');

            const addressProvinceList = document.getElementById('address_province_list');
            const addressDistrictList = document.getElementById('address_district_list');
            const addressSubdistrictList = document.getElementById('address_subdistrict_list');
            const addressPostalList = document.getElementById('address_postal_list');

            const selectedAddressFilters = {
                province: '',
                city: '',
                district: '',
                post_code: '',
            };

            let postCodeRequestSeq = 0;

            function fillDatalist(datalistEl, items) {
                if (!datalistEl) return;
                datalistEl.innerHTML = '';
                items.forEach(function(item) {
                    const option = document.createElement('option');
                    option.value = item;
                    datalistEl.appendChild(option);
                });
            }

            function buildPostCodeOptionsUrl(filters, queries) {
                const url = new URL(postCodeApi.options, window.location.origin);
                Object.entries(filters || {}).forEach(function([key, value]) {
                    if (value) url.searchParams.set(key, value);
                });
                Object.entries(queries || {}).forEach(function([key, value]) {
                    if (value) url.searchParams.set(key, value);
                });
                return url.toString();
            }

            async function fetchPostCodeOptions(filters, queries) {
                const requestSeq = ++postCodeRequestSeq;
                const url = buildPostCodeOptionsUrl(filters, queries);
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return null;
                const data = await response.json();
                if (requestSeq !== postCodeRequestSeq) return null;
                return data && typeof data === 'object' ? data : null;
            }

            async function refreshAllPostCodeLists() {
                const data = await fetchPostCodeOptions(selectedAddressFilters, {});
                if (!data) return;
                fillDatalist(addressProvinceList, Array.isArray(data.provinces) ? data.provinces : []);
                fillDatalist(addressDistrictList, Array.isArray(data.cities) ? data.cities : []);
                fillDatalist(addressSubdistrictList, Array.isArray(data.districts) ? data.districts : []);
                fillDatalist(addressPostalList, Array.isArray(data.post_codes) ? data.post_codes : []);
            }

            async function refreshFocusedPostCodeList(fieldKey, queryValue) {
                const queries = {};
                if (fieldKey === 'province') queries.q_province = queryValue;
                if (fieldKey === 'city') queries.q_city = queryValue;
                if (fieldKey === 'district') queries.q_district = queryValue;
                if (fieldKey === 'post_code') queries.q_post_code = queryValue;

                const data = await fetchPostCodeOptions(selectedAddressFilters, queries);
                if (!data) return;

                if (fieldKey === 'province') fillDatalist(addressProvinceList, Array.isArray(data.provinces) ? data.provinces : []);
                if (fieldKey === 'city') fillDatalist(addressDistrictList, Array.isArray(data.cities) ? data.cities : []);
                if (fieldKey === 'district') fillDatalist(addressSubdistrictList, Array.isArray(data.districts) ? data.districts : []);
                if (fieldKey === 'post_code') fillDatalist(addressPostalList, Array.isArray(data.post_codes) ? data.post_codes : []);
            }

            function bindPostCodeField(inputEl, fieldKey) {
                if (!inputEl) return;

                inputEl.addEventListener('input', function() {
                    const value = (this.value || '').trim();
                    if (value === '') {
                        selectedAddressFilters[fieldKey] = '';
                        refreshAllPostCodeLists();
                        return;
                    }

                    refreshFocusedPostCodeList(fieldKey, value);
                });

                inputEl.addEventListener('change', function() {
                    selectedAddressFilters[fieldKey] = (this.value || '').trim();
                    refreshAllPostCodeLists();
                });
            }

            bindPostCodeField(addressProvinceInput, 'province');
            bindPostCodeField(addressDistrictInput, 'city');
            bindPostCodeField(addressSubdistrictInput, 'district');
            bindPostCodeField(addressPostalInput, 'post_code');

            refreshAllPostCodeLists();

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

            function syncConditionalSections() {
                isSyncingConditionalSections = true;
                try {
                    [
                        'title',
                        'marital_status',
                        'spouse_title',
                        'occupation',
                        'spouse_occupation',
                        'hasOtherDebts',
                        'dwelling_type',
                        'residence_status',
                        'companyType',
                        'documentDelivery',
                        'loanAmountType'
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

            function prepareCreateModal() {
                consentForm?.reset();
                setFieldValue('consent_id', '');
                if (consentForm) {
                    consentForm.action = modalStoreUrl;
                }
                if (consentFormMethod) {
                    consentFormMethod.value = 'POST';
                }
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

                selectedAddressFilters.province = '';
                selectedAddressFilters.city = '';
                selectedAddressFilters.district = '';
                selectedAddressFilters.post_code = '';
                refreshAllPostCodeLists();
                syncConditionalSections();
            }

            function prepareEditModal(customer) {
                if (!consentForm) return;
                const hasCustomResidenceStatus = Boolean(
                    customer.residence_status &&
                    !['เช่า/ผ่อนชำระ', 'ปลอดภาระ', 'บ้านพักสวัสดิการ', 'อื่นๆ'].includes(customer.residence_status)
                );

                consentForm.reset();
                setFieldValue('consent_id', customer.id);
                consentForm.action = `${modalUpdateBaseUrl}/${customer.id}`;
                if (consentFormMethod) {
                    consentFormMethod.value = 'PUT';
                }
                if (consentModalTitle) {
                    consentModalTitle.textContent = 'แก้ไขใบยินยอมแบบละเอียด';
                }
                if (consentSubmitBtn) {
                    consentSubmitBtn.textContent = 'บันทึกการแก้ไข';
                }

                [
                    'app_date', 'app_no', 'officer_name', 'officer_phone', 'title', 'name', 'name_en', 'dob', 'id_card', 'gender',
                    'age', 'nationality', 'marital_status', 'education', 'occupation', 'spouse_title', 'spouse_name',
                    'spouse_phone', 'spouse_mobile', 'spouse_education', 'spouse_occupation', 'spouse_company',
                    'spouse_income', 'dwelling_type', 'residence_status', 'residence_rent_amount', 'residence_years',
                    'address_no', 'address_floor', 'address_village', 'address_building', 'address_soi', 'address_road',
                    'address_subdistrict', 'address_district', 'address_province', 'address_postal', 'phone_home',
                    'phone_mobile', 'email', 'line_id'
                ].forEach(function(fieldName) {
                    setFieldValue(fieldName, customer[fieldName]);
                });

                setFieldValue('income', customer.income);
                setFieldValue('extraIncome', customer.extraIncome);
                setFieldValue('extraIncomeSource', customer.extraIncomeSource);
                setFieldValue('businessIncome', customer.businessIncome);
                setFieldValue('averageMonthlyIncome', customer.averageMonthlyIncome);
                setFieldValue('hasOtherDebts', customer.hasOtherDebts);
                setFieldValue('otherDebtInstallment', customer.otherDebtInstallment);
                setFieldValue('hasExistingLoan', customer.hasExistingLoan);
                setFieldValue('useHomeAddress', customer.useHomeAddress);
                setFieldValue('companyType', customer.companyType);
                setFieldValue('companyName', customer.companyName);
                setFieldValue('businessType', customer.businessType);
                setFieldValue('workOccupation', customer.workOccupation);
                setFieldValue('workPosition', customer.workPosition);
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
                setFieldValue('previousBusinessType', customer.previousBusinessType);
                setFieldValue('previousPosition', customer.previousPosition);
                setFieldValue('previousIncome', customer.previousIncome);
                setFieldValue('previousWorkYears', customer.previousWorkYears);
                setFieldValue('previousPhone', customer.previousPhone);
                setFieldValue('documentDelivery', customer.documentDelivery);
                setFieldValue('documentEmail', customer.documentEmail);
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
                setFieldValue('refEmail', customer.refEmail);
                setFieldValue('refLineId', customer.refLineId);
                setFieldValue('loanTerm', customer.loanTerm);
                setFieldValue('loanAmountType', customer.loanAmountType);
                setFieldValue('customLoanAmount', customer.customLoanAmount);
                setFieldValue('loanPurpose', customer.loanPurpose);
                setFieldValue('bankName', customer.bankName);
                setFieldValue('bankBranch', customer.bankBranch);
                setFieldValue('accountName', customer.accountName);
                setFieldValue('accountType', customer.accountType);
                setFieldValue('accountNumber', customer.accountNumber);
                setFieldValue('signatureData', customer.signatureData);

                setSelectWithOther('title', 'title_other', customer.title, ['นาย', 'นาง', 'นางสาว']);
                setSelectWithOther('occupation', 'occupationOther', customer.occupation, ['พนักงานบริษัท', 'ข้าราชการ/ทหาร/ตำรวจ', 'เจ้าของกิจการ', 'อาชีพอิสระ', 'รับจ้าง', 'อื่นๆ']);
                setSelectWithOther('spouse_title', 'spouse_title_other', customer.spouse_title, ['นาย', 'นาง', 'นางสาว']);
                setSelectWithOther('spouse_occupation', 'spouseOccupationOther', customer.spouse_occupation, ['พนักงานบริษัท', 'ข้าราชการ/ทหาร/ตำรวจ', 'เจ้าของกิจการ', 'อาชีพอิสระ', 'รับจ้าง', 'อื่นๆ']);
                setSelectWithOther('companyType', 'companyTypeOther', customer.companyType, ['บจก.', 'บมจ.', 'หจก.', 'ร้านค้า/ทะเบียนพาณิชย์']);

                selectedAddressFilters.province = (customer.address_province || '').trim();
                selectedAddressFilters.city = (customer.address_district || '').trim();
                selectedAddressFilters.district = (customer.address_subdistrict || '').trim();
                selectedAddressFilters.post_code = (customer.address_postal || '').trim();
                refreshAllPostCodeLists();

                if ((customer.dwelling_type || '').startsWith('อาศัยอยู่กับผู้อื่น: ')) {
                    setFieldValue('dwelling_type', 'อาศัยอยู่กับผู้อื่น');
                    consentForm?.querySelector('[name="dwelling_type"]')?.dispatchEvent(new Event('change'));
                    setFieldValue('dwelling_type_other', customer.dwelling_type.replace('อาศัยอยู่กับผู้อื่น: ', ''));
                } else {
                    setFieldValue('dwelling_type', customer.dwelling_type);
                }

                if (hasCustomResidenceStatus) {
                    setFieldValue('residence_status', 'อื่นๆ');
                    consentForm?.querySelector('[name="residence_status"]')?.dispatchEvent(new Event('change'));
                    setFieldValue('residence_status_other', customer.residence_status);
                } else {
                    setFieldValue('residence_status', customer.residence_status);
                }

                syncConditionalSections();

                if (hasCustomResidenceStatus) {
                    setFieldValue('residence_status_other', customer.residence_status);
                }

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

            // Handle spouse fields based on marital status
            const maritalStatusSelect = document.getElementById('marital_status');
            const spouseSectionTitle = document.getElementById('spouse_section_title');
            const spouseFields = document.getElementById('spouse_fields');
            
            if (maritalStatusSelect) {
                maritalStatusSelect.addEventListener('change', function() {
                    if (this.value === 'สมรส' || this.value === 'สมรสไม่จดทะเบียน') {
                        spouseSectionTitle.classList.remove('hidden');
                        spouseFields.classList.remove('hidden');
                    } else {
                        spouseSectionTitle.classList.add('hidden');
                        spouseFields.classList.add('hidden');
                    }
                });
            }

            // Handle "อื่นๆ" choice for spouse's คำนำหน้านาม
            const spouseTitleSelect = document.getElementById('spouse_title');
            const spouseTitleOtherWrapper = document.getElementById('spouse_title_other_wrapper');
            const spouseTitleOtherInput = document.getElementById('spouse_title_other');

            if (spouseTitleSelect) {
                spouseTitleSelect.addEventListener('change', function() {
                    if (this.value === 'อื่นๆ') {
                        spouseTitleOtherWrapper.classList.remove('hidden');
                        spouseTitleOtherInput.setAttribute('required', 'required');
                    } else {
                        spouseTitleOtherWrapper.classList.add('hidden');
                        spouseTitleOtherInput.removeAttribute('required');
                        spouseTitleOtherInput.value = '';
                    }
                });
            }

            // Handle "อื่นๆ" choice for applicant's occupation
            const occupationSelect = document.getElementById('occupation');
            const occupationOtherWrapper = document.getElementById('occupationOtherWrapper');
            const occupationOtherInput = document.getElementById('occupationOther');

            if (occupationSelect) {
                occupationSelect.addEventListener('change', function() {
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

            // Handle "อื่นๆ" choice for spouse's occupation
            const spouseOccupationSelect = document.getElementById('spouse_occupation');
            const spouseOccupationOtherWrapper = document.getElementById('spouseOccupationOtherWrapper');
            const spouseOccupationOtherInput = document.getElementById('spouseOccupationOther');

            if (spouseOccupationSelect) {
                spouseOccupationSelect.addEventListener('change', function() {
                    if (this.value === 'อื่นๆ') {
                        spouseOccupationOtherWrapper.classList.remove('hidden');
                        spouseOccupationOtherInput.setAttribute('required', 'required');
                    } else {
                        spouseOccupationOtherWrapper.classList.add('hidden');
                        spouseOccupationOtherInput.removeAttribute('required');
                        spouseOccupationOtherInput.value = '';
                    }
                });
            }

            // Handle extra income source field
            const extraIncomeInput = document.getElementById('extraIncome');
            const extraIncomeSourceWrapper = document.getElementById('extraIncomeSourceWrapper');
            const extraIncomeSourceInput = document.getElementById('extraIncomeSource');

            if (extraIncomeInput) {
                extraIncomeInput.addEventListener('input', function() {
                    if (this.value && parseInt(this.value) > 0) {
                        extraIncomeSourceWrapper.classList.remove('hidden');
                        extraIncomeSourceInput.setAttribute('required', 'required');
                    } else {
                        extraIncomeSourceWrapper.classList.add('hidden');
                        extraIncomeSourceInput.removeAttribute('required');
                        extraIncomeSourceInput.value = '';
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

            // Handle dwelling type "อาศัยอยู่กับผู้อื่น"
            const dwellingTypeSelect = document.getElementById('dwelling_type');
            const dwellingTypeOtherWrapper = document.getElementById('dwelling_type_other_wrapper');
            const dwellingTypeOtherInput = document.getElementById('dwelling_type_other');

            if (dwellingTypeSelect) {
                dwellingTypeSelect.addEventListener('change', function() {
                    if (this.value === 'อาศัยอยู่กับผู้อื่น') {
                        dwellingTypeOtherWrapper.classList.remove('hidden');
                        dwellingTypeOtherInput.setAttribute('required', 'required');
                    } else {
                        dwellingTypeOtherWrapper.classList.add('hidden');
                        dwellingTypeOtherInput.removeAttribute('required');
                        dwellingTypeOtherInput.value = '';
                    }
                });
            }

            // Handle residence status
            const residenceStatusSelect = document.getElementById('residence_status');
            const residenceRentWrapper = document.getElementById('residence_rent_wrapper');
            const residenceRentAmountInput = document.getElementById('residence_rent_amount');
            const residenceStatusOtherWrapper = document.getElementById('residence_status_other_wrapper');
            const residenceStatusOtherInput = document.getElementById('residence_status_other');

            if (residenceStatusSelect) {
                residenceStatusSelect.addEventListener('change', function() {
                    // Reset all
                    residenceRentWrapper.classList.add('hidden');
                    residenceRentAmountInput.removeAttribute('required');
                    if (!isSyncingConditionalSections) {
                        residenceRentAmountInput.value = '';
                    }
                    residenceStatusOtherWrapper.classList.add('hidden');
                    residenceStatusOtherInput.removeAttribute('required');
                    if (!isSyncingConditionalSections) {
                        residenceStatusOtherInput.value = '';
                    }

                    if (this.value === 'เช่า/ผ่อนชำระ') {
                        residenceRentWrapper.classList.remove('hidden');
                        residenceRentAmountInput.setAttribute('required', 'required');
                    } else if (this.value === 'อื่นๆ') {
                        residenceStatusOtherWrapper.classList.remove('hidden');
                        residenceStatusOtherInput.setAttribute('required', 'required');
                    }
                });
            }

            // Handle company type "อื่นๆ"
            const companyTypeSelect = document.getElementById('companyType');
            const companyTypeOtherWrapper = document.getElementById('companyTypeOtherWrapper');
            const companyTypeOtherInput = document.getElementById('companyTypeOther');

            if (companyTypeSelect) {
                companyTypeSelect.addEventListener('change', function() {
                    if (this.value === 'อื่นๆ') {
                        companyTypeOtherWrapper.classList.remove('hidden');
                        companyTypeOtherInput.setAttribute('required', 'required');
                    } else {
                        companyTypeOtherWrapper.classList.add('hidden');
                        companyTypeOtherInput.removeAttribute('required');
                        companyTypeOtherInput.value = '';
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

            if (useHomeAddressCheckbox) {
                useHomeAddressCheckbox.addEventListener('change', function() {
                    homeAddressFields.forEach(field => {
                        const homeInput = document.getElementById(field.id);
                        const workInput = document.getElementById(field.workId);
                        
                        if (this.checked) {
                            workInput.value = homeInput.value;
                            workInput.readOnly = true;
                        } else {
                            workInput.value = '';
                            workInput.readOnly = false;
                        }
                    });
                });

                // Also listen to changes in home address fields when checkbox is checked
                homeAddressFields.forEach(field => {
                    const homeInput = document.getElementById(field.id);
                    homeInput.addEventListener('input', function() {
                        if (useHomeAddressCheckbox.checked) {
                            const workInput = document.getElementById(field.workId);
                            workInput.value = this.value;
                        }
                    });
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

            // Handle document delivery email field
            const documentDeliverySelect = document.getElementById('documentDelivery');
            const documentEmailWrapper = document.getElementById('documentEmailWrapper');
            const documentEmailInput = document.getElementById('documentEmail');

            if (documentDeliverySelect) {
                documentDeliverySelect.addEventListener('change', function() {
                    if (this.value === 'E-mail') {
                        documentEmailWrapper.classList.remove('hidden');
                        documentEmailInput.setAttribute('required', 'required');
                    } else {
                        documentEmailWrapper.classList.add('hidden');
                        documentEmailInput.removeAttribute('required');
                        documentEmailInput.value = '';
                    }
                });
            }

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

            // Handle Section 4 visibility (only when income < 30,000)
            const incomeInput = document.getElementById('income');
            const section4Container = document.getElementById('section4_container');

            function toggleSection4() {
                const incomeValue = parseInt(incomeInput?.value) || 0;
                const shouldShow = incomeValue < 30000;
                
                if (section4Container) {
                    if (shouldShow) {
                        section4Container.classList.remove('hidden');
                    } else {
                        section4Container.classList.add('hidden');
                    }
                }
            }

            if (incomeInput) {
                incomeInput.addEventListener('input', toggleSection4);
                toggleSection4();
            }

            // Restrict ID card/Juridical registration input to digits only
            const idCardInput = document.getElementById('id_card');
            if (idCardInput) {
                idCardInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
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
                prepareCreateModal();
                openModal();
                initializeFormSignature(signatureDataInput?.value || '');
            }

            window.editDocument = async function(customer) {
                let fullCustomer = customer;
                const customerId = customer?.id;
                if (customerId) {
                    try {
                        const response = await fetch(`${modalUpdateBaseUrl}/${customerId}/data`, { headers: { 'Accept': 'application/json' } });
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

            if (openBtn) openBtn.addEventListener('click', openCreateModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

            if (closeViewBtn) closeViewBtn.addEventListener('click', closeViewModal);
            if (closeViewFooterBtn) closeViewFooterBtn.addEventListener('click', closeViewModal);

            const hasServerErrors = @json($errors->any());
            const oldConsentInput = @json(\Illuminate\Support\Arr::except(old(), ['_token', '_method']));

            function populateConsentFormFromOldInput(oldData) {
                if (!oldData || typeof oldData !== 'object') return;

                Object.entries(oldData).forEach(function([key, value]) {
                    if (key === 'prevent_autofill') return;
                    setFieldValue(key, value);
                });

                selectedAddressFilters.province = (addressProvinceInput?.value || '').trim();
                selectedAddressFilters.city = (addressDistrictInput?.value || '').trim();
                selectedAddressFilters.district = (addressSubdistrictInput?.value || '').trim();
                selectedAddressFilters.post_code = (addressPostalInput?.value || '').trim();

                refreshAllPostCodeLists();
                syncConditionalSections();
            }

            function openModalWithOldInput(oldData) {
                const consentId = (oldData?.consent_id ?? '').toString().trim();
                const isEdit = consentId !== '';

                consentForm?.reset();
                if (consentForm) {
                    consentForm.action = isEdit ? `${modalUpdateBaseUrl}/${consentId}` : modalStoreUrl;
                }
                if (consentFormMethod) {
                    consentFormMethod.value = isEdit ? 'PUT' : 'POST';
                }
                if (consentModalTitle) {
                    consentModalTitle.textContent = isEdit ? 'แก้ไขใบยินยอมแบบละเอียด' : 'สร้างใบยินยอมแบบละเอียด';
                }
                if (consentSubmitBtn) {
                    consentSubmitBtn.textContent = isEdit ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูลใบสมัคร';
                }
                if (!isEdit && appNoInput) {
                    appNoInput.value = modalNextAppNo;
                }
                if (!isEdit && appDateInput) {
                    appDateInput.value = '{{ date('Y-m-d') }}';
                }
                if (signatureDataInput) {
                    signatureDataInput.value = '';
                }
                if (signaturePad) {
                    signaturePad.clear();
                }

                selectedAddressFilters.province = '';
                selectedAddressFilters.city = '';
                selectedAddressFilters.district = '';
                selectedAddressFilters.post_code = '';
                refreshAllPostCodeLists();
                syncConditionalSections();

                populateConsentFormFromOldInput(oldData);
                openModal();
                initializeFormSignature(signatureDataInput?.value || '');
            }

            const urlParams = new URLSearchParams(window.location.search);
            if (hasServerErrors) {
                openModalWithOldInput(oldConsentInput);
                window.history.replaceState({}, document.title, window.location.pathname);
            } else if (urlParams.get('openCreate') === '1') {
                openCreateModal();
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Initialize Signature Pad
            function initSignaturePad() {
                const canvas = document.getElementById('signaturePad');
                if (!canvas) return;

                // Set canvas size correctly for high DPI
                const rect = canvas.getBoundingClientRect();
                const ratio = window.devicePixelRatio || 1;
                canvas.width = Math.max(1, rect.width * ratio);
                canvas.height = Math.max(1, rect.height * ratio);
                
                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)'
                });
                
                // Rescale context for high DPI
                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                
                // Save signature data to hidden input (as JSON)
                signaturePad.addEventListener('endStroke', function() {
                    document.getElementById('signatureData').value = JSON.stringify(signaturePad.toData());
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
                consentForm.addEventListener('submit', function(event) {
                    if (!signaturePad || signaturePad.isEmpty()) {
                        event.preventDefault();
                        window.alert('กรุณาเซ็นลายเซ็นผู้ขอสินเชื่อก่อนบันทึก');
                        return;
                    }

                    if (signatureDataInput) {
                        signatureDataInput.value = JSON.stringify(signaturePad.toData());
                    }
                });
                consentForm.dataset.boundSubmit = 'true';
            }

            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
                if (event.target === viewModal) {
                    closeViewModal();
                }
            });
        });
    </script>
@endsection
