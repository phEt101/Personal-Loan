@extends('layouts.app', ['title' => 'รายงานใบยินยอม'])

@section('content')
    <section class="dashboard">
        <div class="hero compact-hero hero-with-actions">
            <div>
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

        <section class="summary-cards compact-summary">
            <div class="summary-card">
                <div class="summary-label">ลูกค้าทั้งหมด</div>
                <div class="summary-value">{{ $total }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">ผ่านใบยินยอม</div>
                <div class="summary-value">{{ $signed }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">ยังไม่ผ่าน</div>
                <div class="summary-value">{{ $failed }}</div>
            </div>
        </section>

        <div class="card">
            <div class="consent-table">
                <table>
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>ชื่อ</th>
                            <th>วันที่เซ็น</th>
                            <th>สถานะ</th>
                            <th>การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->code ?? $customer->id }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->signed_at ?? '-' }}</td>
                                <td>
                                    @if($customer->signed)
                                        <span class="badge badge-signed">Signed</span>
                                    @else
                                        <span class="badge badge-pending">ยังไม่เซ็น</span>
                                    @endif
                                </td>
                                <td><a href="#" class="action-link" onclick="viewDocument({{ json_encode($customer) }})">ดูเอกสาร</a></td>
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

    <!-- Modal สำหรับสร้างใบยินยอม -->
    <div id="consentModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3>สร้างใบยินยอมแบบละเอียด</h3>
                <button type="button" class="close-btn" id="closeConsentModal" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body scrollable">
                <form method="POST" action="{{ route('consent.store') }}" class="consent-form">
                    @csrf
                    
                    <div class="form-grid">
                        <!-- Application Header -->
                        <div class="form-section-title" style="margin-top: 0;">
                            <span style="display:inline-flex; align-items:center;">📅</span> ข้อมูลใบคำขอ
                        </div>

                        <div class="form-group col-6">
                            <label for="app_date">วันที่เขียนคำขอ</label>
                            <input type="date" id="app_date" name="app_date" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="form-group col-6">
                            <label for="app_no">App No. (เลขที่ใบคำขอ 13 หลัก)</label>
                            <input type="text" id="app_no" name="app_no" placeholder="เช่น 1002003004005" maxlength="13">
                        </div>

                        <!-- Section 1: สำหรับเจ้าหน้าที่ -->
                        <div class="form-section-title">
                            <span style="display:inline-flex; align-items:center;">💼</span> ส่วนที่ 1: สำหรับเจ้าหน้าที่บริษัท
                        </div>

                        <div class="form-group col-6">
                            <label for="officer_name">เจ้าหน้าที่สินเชื่อ</label>
                            <input type="text" id="officer_name" name="officer_name" placeholder="ระบุชื่อเจ้าหน้าที่สินเชื่อ">
                        </div>

                        <div class="form-group col-6">
                            <label for="officer_phone">เบอร์ติดต่อ</label>
                            <input type="text" id="officer_phone" name="officer_phone" placeholder="ระบุเบอร์ติดต่อเจ้าหน้าที่">
                        </div>

                        <!-- Section 1: ข้อมูลส่วนตัว -->
                        <div class="form-section-title">
                            <span style="display:inline-flex; align-items:center;">👤</span> ส่วนที่ 2: ข้อมูลส่วนตัวผู้ขอสินเชื่อ
                        </div>

                        <div class="form-group col-3" id="titleGroup">
                            <label for="title">คำนำหน้านาม</label>
                            <select id="title" name="title">
                                <option value="นาย">นาย / Mr.</option>
                                <option value="นาง">นาง / Mrs.</option>
                                <option value="นางสาว">นางสาว / Ms.</option>
                                <option value="อื่นๆ">อื่นๆ / Other</option>
                            </select>
                        </div>

                        <div class="form-group col-3" id="title_other_wrapper" style="display: none;">
                            <label for="title_other">ระบุคำนำหน้านามอื่นๆ</label>
                            <input type="text" id="title_other" name="title_other" placeholder="เช่น ดร. / นพ.">
                        </div>

                        <div class="form-group col-9" id="nameGroup">
                            <label for="name">ชื่อ - สกุล (ภาษาไทย) <span style="color: red;">*</span></label>
                            <input type="text" id="name" name="name" placeholder="ชื่อ และ นามสกุลภาษาไทย" required>
                        </div>

                        <div class="form-group col-12">
                            <label for="name_en">ชื่อ - สกุล (ภาษาอังกฤษ ตัวพิมพ์ใหญ่)</label>
                            <input type="text" id="name_en" name="name_en" placeholder="NAME - SURNAME IN ENGLISH (UPPERCASE)">
                        </div>

                        <div class="form-group col-6">
                            <label for="id_card">เลขประจำตัวประชาชน / เลขทะเบียนนิติบุคคล</label>
                            <input type="text" id="id_card" name="id_card" placeholder="เลขบัตรประชาชน 13 หลัก" maxlength="13">
                        </div>

                        <div class="form-group col-6">
                            <label for="dob">วัน / เดือน / ปีเกิด</label>
                            <input type="date" id="dob" name="dob">
                        </div>

                        <div class="form-group col-3">
                            <label for="gender">เพศ</label>
                            <select id="gender" name="gender">
                                <option value="">เลือกเพศ</option>
                                <option value="ชาย">ชาย</option>
                                <option value="หญิง">หญิง</option>
                            </select>
                        </div>

                        <div class="form-group col-3">
                            <label for="age">อายุ (ปี)</label>
                            <input type="number" id="age" name="age" min="0" placeholder="อายุ">
                        </div>

                        <div class="form-group col-3">
                            <label for="nationality">สัญชาติ</label>
                            <input type="text" id="nationality" name="nationality" value="ไทย" placeholder="สัญชาติ">
                        </div>

                        <div class="form-group col-3">
                            <label for="marital_status">สถานภาพ</label>
                            <select id="marital_status" name="marital_status">
                                <option value="โสด">โสด</option>
                                <option value="สมรส">สมรส</option>
                                <option value="สมรสไม่จดทะเบียน">สมรสไม่จดทะเบียน</option>
                                <option value="หย่า">หย่า</option>
                                <option value="หม้าย">หม้าย</option>
                            </select>
                        </div>

                        <!-- Section 2: ข้อมูลติดต่อและสถานที่ทำงาน -->
                        <div class="form-section-title">
                            <span style="display:inline-flex; align-items:center;">📍</span> ส่วนที่ 3: ที่อยู่และข้อมูลอาชีพ
                        </div>

                        <div class="form-group col-12">
                            <label for="address">ที่อยู่อาศัยในปัจจุบัน</label>
                            <input type="text" id="address" name="address" placeholder="เลขที่, หมู่, ซอย, ถนน, ตำบล, อำเภอ, จังหวัด, รหัสไปรษณีย์">
                        </div>

                        <div class="form-group col-4">
                            <label for="mobile">หมายเลขโทรศัพท์มือถือ</label>
                            <input type="text" id="mobile" name="mobile" placeholder="0XXXXXXXXX">
                        </div>

                        <div class="form-group col-4">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="example@email.com">
                        </div>

                        <div class="form-group col-4">
                            <label for="line_id">Line ID</label>
                            <input type="text" id="line_id" name="line_id" placeholder="Line ID">
                        </div>

                        <div class="form-group col-6">
                            <label for="company_name">ชื่อสถานที่ทำงาน/กิจการ</label>
                            <input type="text" id="company_name" name="company_name" placeholder="ชื่อที่ทำงาน หรือ ชื่อร้านค้า">
                        </div>

                        <div class="form-group col-6">
                            <label for="occupation">อาชีพ</label>
                            <select id="occupation" name="occupation">
                                <option value="พนักงานบริษัท">พนักงานบริษัท/ห้างหุ้นส่วน</option>
                                <option value="ข้าราชการ/ทหาร/ตำรวจ">ข้าราชการ / ทหาร / ตำรวจ</option>
                                <option value="พนักงานรัฐวิสาหกิจ">พนักงานรัฐวิสาหกิจ</option>
                                <option value="เจ้าของกิจการ">เจ้าของกิจการ / ทะเบียนพาณิชย์</option>
                                <option value="อาชีพอิสระ">อาชีพอิสระ</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>

                        <div class="form-group col-4">
                            <label for="position">ตำแหน่ง</label>
                            <input type="text" id="position" name="position" placeholder="ตำแหน่งงาน">
                        </div>

                        <div class="form-group col-4">
                            <label for="working_years">อายุงาน (ปี)</label>
                            <input type="number" id="working_years" name="working_years" min="0" placeholder="จำนวนปี">
                        </div>

                        <div class="form-group col-4">
                            <label for="income">รายได้ประจำต่อเดือน (บาท)</label>
                            <input type="number" id="income" name="income" min="0" placeholder="เช่น 25000">
                        </div>

                        <!-- Section 3: ความประสงค์ใช้สินเชื่อและบัญชี -->
                        <div class="form-section-title">
                            <span style="display:inline-flex; align-items:center;">💵</span> ส่วนที่ 4: ความประสงค์สินเชื่อและบัญชีรับเงินกู้
                        </div>

                        <div class="form-group col-6">
                            <label for="loan_amount">วงเงินสินเชื่อที่ต้องการ (บาท)</label>
                            <input type="number" id="loan_amount" name="loan_amount" min="0" placeholder="ระบุจำนวนเงินที่ขอกู้">
                        </div>

                        <div class="form-group col-6">
                            <label for="loan_term">ระยะเวลาผ่อนชำระคืน</label>
                            <select id="loan_term" name="loan_term">
                                <option value="12">12 เดือน</option>
                                <option value="24">24 เดือน</option>
                                <option value="36">36 เดือน</option>
                                <option value="48">48 เดือน</option>
                                <option value="60">60 เดือน</option>
                            </select>
                        </div>

                        <div class="form-group col-4">
                            <label for="bank_name">ธนาคารรับเงินกู้</label>
                            <select id="bank_name" name="bank_name">
                                <option value="กสิกรไทย">ธนาคารกสิกรไทย (KBank)</option>
                                <option value="ไทยพาณิชย์">ธนาคารไทยพาณิชย์ (SCB)</option>
                                <option value="กรุงเทพ">ธนาคารกรุงเทพ (BBL)</option>
                                <option value="กรุงไทย">ธนาคารกรุงไทย (KTB)</option>
                                <option value="กรุงศรีอยุธยา">ธนาคารกรุงศรีอยุธยา (BAY)</option>
                                <option value="ทหารไทยธนชาต">ธนาคารทหารไทยธนชาต (ttb)</option>
                                <option value="ออมสิน">ธนาคารออมสิน (GSB)</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>

                        <div class="form-group col-4">
                            <label for="bank_account">เลขที่บัญชี</label>
                            <input type="text" id="bank_account" name="bank_account" placeholder="เลขที่บัญชี">
                        </div>

                        <div class="form-group col-4">
                            <label for="bank_account_name">ชื่อบัญชี</label>
                            <input type="text" id="bank_account_name" name="bank_account_name" placeholder="ชื่อบัญชีรับโอนเงินกู้">
                        </div>

                        <!-- Section 4: ข้อตกลงความยินยอม -->
                        <div class="form-section-title">
                            <span style="display:inline-flex; align-items:center;">📝</span> ส่วนที่ 5: ข้อความยินยอม (Consent)
                        </div>

                        <div class="form-group col-12 checkbox-group" style="align-items: flex-start;">
                            <input type="checkbox" id="consent_checkbox" name="consent_checkbox" required checked>
                            <label for="consent_checkbox" style="font-size: 0.85rem; line-height: 1.5; color: #4b5563;">
                                ข้าพเจ้ายินยอมให้ บริษัท บิ๊ก มันนี่ พลัส จำกัด เก็บรวบรวม ใช้ หรือเปิดเผยข้อมูลส่วนบุคคล เพื่อการตรวจสอบวิเคราะห์ข้อมูลเครดิต และประเมินความสามารถในการชำระหนี้ รวมถึงการปฏิบัติตามกฎหมายและกฎระเบียบที่เกี่ยวข้อง และรับรองว่าข้อมูลรายละเอียดที่ระบุข้างต้นทั้งหมดเป็นความจริงทุกประการ
                            </label>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">
                        <button type="button" class="action-btn outline" id="cancelConsentModal">ยกเลิก</button>
                        <button type="submit" class="action-btn">บันทึกข้อมูลใบสมัคร</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับดูเอกสารใบยินยอม -->
    <div id="viewConsentModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header" style="background: #ecfdf5; border-bottom: 2px solid #bbf7d0;">
                <h3 style="color: #065f46; display: flex; align-items: center; gap: 0.5rem;">
                    📄 เอกสารคำขอและใบยินยอมสินเชื่อบุคคล
                </h3>
                <button type="button" class="close-btn" id="closeViewConsentModal" aria-label="Close modal" style="color: #065f46;">&times;</button>
            </div>
            <div class="modal-body scrollable" style="background: #fafafa;">
                <div id="viewConsentContent" style="padding: 1.5rem; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <!-- Content populated by JS -->
                </div>
            </div>
            <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background: #ffffff; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="action-btn outline" id="closeViewConsentFooter">ปิดหน้าต่าง</button>
                <button type="button" class="action-btn" onclick="window.print()" style="background: #059669;">พิมพ์เอกสาร</button>
            </div>
        </div>
    </div>

    <script>
        // JS Function to show details modal
        function viewDocument(customer) {
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
            const address = customer.address || '-';
            const mobile = customer.mobile || '-';
            const email = customer.email || '-';
            const line_id = customer.line_id || '-';
            const company_name = customer.company_name || '-';
            const occupation = customer.occupation || '-';
            const position = customer.position || '-';
            const working_years = customer.working_years ? customer.working_years + ' ปี' : '-';
            const income = customer.income ? parseInt(customer.income).toLocaleString('th-TH') + ' บาท' : '-';
            const loan_amount = customer.loan_amount ? parseInt(customer.loan_amount).toLocaleString('th-TH') + ' บาท' : '-';
            const loan_term = customer.loan_term ? customer.loan_term + ' เดือน' : '-';
            const bank_name = customer.bank_name || '-';
            const bank_account = customer.bank_account || '-';
            const bank_account_name = customer.bank_account_name || '-';
            const signed_at = customer.signed_at || new Date().toISOString().split('T')[0];
            const signedDateFormatted = new Date(signed_at).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' });

            // Officer & Application Details
            const officer_name = customer.officer_name || '-';
            const officer_phone = customer.officer_phone || '-';
            
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
                        <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 0.25rem;">รหัสลูกค้า: ${customer.code || 'CUST-NEW'}</div>
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
                    </table>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: #10b981; border-left: 4px solid #10b981; padding-left: 0.5rem; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700;">ส่วนที่ 3: ที่อยู่และข้อมูลอาชีพ</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">ที่อยู่ปัจจุบัน:</td>
                            <td style="padding: 0.5rem;">${address}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">ข้อมูลติดต่อ:</td>
                            <td style="padding: 0.5rem;">เบอร์โทร: ${mobile} | E-mail: ${email} | Line: ${line_id}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">สถานที่ทำงาน / อาชีพ:</td>
                            <td style="padding: 0.5rem;">${company_name} (${occupation})</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">ตำแหน่ง / อายุงาน:</td>
                            <td style="padding: 0.5rem;">ตำแหน่ง: ${position} | อายุงาน: ${working_years}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">รายได้ประจำต่อเดือน:</td>
                            <td style="padding: 0.5rem; color: #166534; font-weight: 700;">${income}</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: #10b981; border-left: 4px solid #10b981; padding-left: 0.5rem; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700;">ส่วนที่ 4: รายละเอียดสินเชื่อและบัญชีรับเงิน</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600; width: 30%;">วงเงินสินเชื่อที่ขอกู้:</td>
                            <td style="padding: 0.5rem; font-weight: 700; color: #166534;">${loan_amount}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">ระยะเวลาผ่อนชำระ:</td>
                            <td style="padding: 0.5rem;">${loan_term}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.5rem; font-weight: 600;">โอนเข้าบัญชีธนาคาร:</td>
                            <td style="padding: 0.5rem;">ธนาคาร ${bank_name} | เลขที่บัญชี: ${bank_account} | ชื่อบัญชี: ${bank_account_name}</td>
                        </tr>
                    </table>
                </div>

                <div style="margin-top: 2rem; border-top: 1px dashed #d1d5db; padding-top: 1.5rem; text-align: justify; font-size: 0.82rem; color: #4b5563; line-height: 1.6;">
                    <p><strong>ข้อความยินยอม:</strong> ข้าพเจ้ารับรองว่าข้อมูลรายละเอียดที่ระบุข้างต้นเป็นความจริงทุกประการ และยินยอมให้บริษัท บิ๊ก มันนี่ พลัส จำกัด เก็บรวบรวม ใช้ หรือเปิดเผยข้อมูลส่วนบุคคล เพื่อวัตถุประสงค์ในการวิเคราะห์ประเมินความสามารถการขอสินเชื่อตามหลักเกณฑ์ที่บริษัทกำหนดไว้</p>
                    
                    <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
                        <div style="text-align: center; width: 250px;">
                            <p style="margin-bottom: 2rem;">ลงนาม.............................................................. ผู้ขอสินเชื่อ</p>
                            <p>( ${title} ${name} )</p>
                            <p>วันที่ทำรายการ: ${signedDateFormatted}</p>
                        </div>
                    </div>
                </div>
            `;
            
            viewModal.style.display = 'flex';
            viewModal.offsetHeight;
            viewModal.classList.add('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('consentModal');
            const openBtn = document.getElementById('openConsentModal');
            const closeBtn = document.getElementById('closeConsentModal');
            const cancelBtn = document.getElementById('cancelConsentModal');

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
                        titleOtherWrapper.style.display = 'grid';
                        titleOtherInput.setAttribute('required', 'required');
                        nameGroup.classList.remove('col-9');
                        nameGroup.classList.add('col-6');
                    } else {
                        titleOtherWrapper.style.display = 'none';
                        titleOtherInput.removeAttribute('required');
                        titleOtherInput.value = '';
                        nameGroup.classList.remove('col-6');
                        nameGroup.classList.add('col-9');
                    }
                });
            }

            // Restrict ID card/Juridical registration input to digits only
            const idCardInput = document.getElementById('id_card');
            if (idCardInput) {
                idCardInput.addEventListener('input', function() {
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

            if (openBtn) openBtn.addEventListener('click', openModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

            if (closeViewBtn) closeViewBtn.addEventListener('click', closeViewModal);
            if (closeViewFooterBtn) closeViewFooterBtn.addEventListener('click', closeViewModal);

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
