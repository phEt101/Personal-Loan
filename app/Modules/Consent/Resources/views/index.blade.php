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
                                <td>{{ $customer->app_no ?? '-' }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->transaction_date ?? '-' }}</td>
                                <td>
                                    @if($customer->status === 'approved')
                                        <span class="badge badge-signed">ผ่าน</span>
                                    @elseif($customer->status === 'rejected')
                                        <span class="badge badge-pending">ไม่ผ่าน</span>
                                    @else
                                        <span class="badge">รอดำเนินการ</span>
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
                <form method="POST" action="{{ route('consent.store') }}" class="consent-form" autocomplete="off">
                    @csrf
                    <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                        <input type="text" name="prevent_autofill" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-grid">
                        <!-- Application Header -->
                        <div class="form-section-title form-section-title--no-top-margin">
                            <span class="form-section-title-icon">📅</span> ข้อมูลใบคำขอ
                        </div>

                        <div class="form-group col-6">
                            <label for="app_date">วันที่เขียนคำขอ</label>
                            <input type="date" id="app_date" name="app_date" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="form-group col-6">
                            <label for="app_no">App No. (เลขที่ใบคำขอ 13 หลัก)</label>
                            <input type="text" id="app_no" name="app_no" value="{{ $nextAppNo }}" maxlength="13" readonly style="background: #f3f4f6; cursor: not-allowed;">
                        </div>

                        <!-- Section 1: สำหรับเจ้าหน้าที่ -->
                        <div class="form-section-title">
                            <span class="form-section-title-icon">💼</span> สำหรับเจ้าหน้าที่บริษัท
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
                            <span class="form-section-title-icon">👤</span> ข้อมูลส่วนตัวผู้ขอสินเชื่อ
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

                        <div class="form-group col-3 hidden" id="title_other_wrapper">
                            <label for="title_other">ระบุคำนำหน้านามอื่นๆ</label>
                            <input type="text" id="title_other" name="title_other" placeholder="เช่น ดร. / นพ.">
                        </div>

                        <div class="form-group col-9" id="nameGroup">
                            <label for="name">ชื่อ - สกุล (ภาษาไทย) <span class="required-asterisk">*</span></label>
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
                            <label for="age">อายุ (ปี) <span class="required-asterisk">*</span></label>
                            <input type="number" id="age" name="age" min="0" placeholder="อายุ" required>
                        </div>

                        <div class="form-group col-3">
                            <label for="nationality">สัญชาติ</label>
                            <input type="text" id="nationality" name="nationality" placeholder="สัญชาติ">
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

                        <div class="form-group col-6">
                            <label for="education">การศึกษา</label>
                            <select id="education" name="education">
                                <option value="">เลือกระดับการศึกษา</option>
                                <option value="มัธยมต้น">มัธยมต้น</option>
                                <option value="มัธยมปลาย">มัธยมปลาย</option>
                                <option value="อุดมศึกษา">อุดมศึกษา</option>
                                <option value="ปริญญาตรี">ปริญญาตรี</option>
                                <option value="ปริญญาโท">ปริญญาโท</option>
                                <option value="ปริญญาเอก">ปริญญาเอก</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label for="occupation">อาชีพ</label>
                            <select id="occupation" name="occupation">
                                <option value="">เลือกอาชีพ</option>
                                <option value="พนักงานบริษัท">พนักงานบริษัท</option>
                                <option value="ข้าราชการ/ทหาร/ตำรวจ">ข้าราชการ/ทหาร/ตำรวจ</option>
                                <option value="เจ้าของกิจการ">เจ้าของกิจการ</option>
                                <option value="อาชีพอิสระ">อาชีพอิสระ</option>
                                <option value="รับจ้าง">รับจ้าง</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>

                        <div class="form-group col-6 hidden" id="occupationOtherWrapper">
                            <label for="occupationOther">ระบุอาชีพ</label>
                            <input type="text" id="occupationOther" name="occupationOther" placeholder="ระบุอาชีพของคุณ">
                        </div>

                        <div class="form-group col-6">
                            <label for="income">รายได้รวมต่อเดือน (บาท) <span class="required-asterisk">*</span></label>
                            <input type="number" id="income" name="income" min="0" placeholder="รายได้ต่อเดือน" required>
                        </div>

                        <div class="form-group col-6">
                            <label for="extraIncome">รายได้พิเศษ (บาท)</label>
                            <input type="number" id="extraIncome" name="extraIncome" min="0" placeholder="รายได้พิเศษ">
                        </div>

                        <div class="form-group col-6 hidden" id="extraIncomeSourceWrapper">
                            <label for="extraIncomeSource">แหละที่มาของรายได้พิเศษ</label>
                            <input type="text" id="extraIncomeSource" name="extraIncomeSource" placeholder="แหละที่มาของรายได้พิเศษ">
                        </div>

                        <div class="form-group col-6">
                            <label for="businessIncome">ชื่อกิจการ</label>
                            <input type="text" id="businessIncome" name="businessIncome" placeholder="ระบุชื่อกิจการ">
                        </div>

                        <div class="form-group col-6">
                            <label for="averageMonthlyIncome">รายได้จากกิจการ เฉลี่ยต่อเดือน (บาท)</label>
                            <input type="number" id="averageMonthlyIncome" name="averageMonthlyIncome" min="0" placeholder="รายได้จากกิจการเฉลี่ยต่อเดือน">
                        </div>

                        <div class="form-group col-6">
                            <label for="hasOtherDebts">ภาระหนี้อื่นๆ ในปัจจุบัน <span class="required-asterisk">*</span></label>
                            <select id="hasOtherDebts" name="hasOtherDebts" required>
                                <option value="">เลือก</option>
                                <option value="ไม่มี">ไม่มี</option>
                                <option value="มี">มี</option>
                            </select>
                        </div>

                        <div class="form-group col-6 hidden" id="otherDebtInstallmentWrapper">
                            <label for="otherDebtInstallment">ยอดผ่อนต่อเดือน (บาท)</label>
                            <input type="number" id="otherDebtInstallment" name="otherDebtInstallment" min="0" placeholder="ยอดผ่อนต่อเดือน">
                        </div>

                        <!-- Spouse Information (shown when married or common-law) -->
                        <div class="form-section-title hidden" id="spouse_section_title">
                            <span class="form-section-title-icon">👨‍👩‍👧</span> ข้อมูลคู่สมรส
                        </div>

                        <div id="spouse_fields" class="hidden">
                            <div class="form-group col-3">
                                <label for="spouse_title">คำนำหน้านาม (คู่สมรส)</label>
                                <select id="spouse_title" name="spouse_title">
                                    <option value="นาย">นาย / Mr.</option>
                                    <option value="นาง">นาง / Mrs.</option>
                                    <option value="นางสาว">นางสาว / Ms.</option>
                                    <option value="อื่นๆ">อื่นๆ / Other</option>
                                </select>
                            </div>

                            <div class="form-group col-3 hidden" id="spouse_title_other_wrapper">
                                <label for="spouse_title_other">ระบุคำนำหน้านามอื่นๆ (คู่สมรส)</label>
                                <input type="text" id="spouse_title_other" name="spouse_title_other" placeholder="เช่น ดร. / นพ.">
                            </div>

                            <div class="form-group col-6">
                                <label for="spouse_name">ชื่อ - สกุล (คู่สมรส)</label>
                                <input type="text" id="spouse_name" name="spouse_name" placeholder="ชื่อ - นามสกุลคู่สมรส">
                            </div>

                            <div class="form-group col-4">
                                <label for="spouse_phone">หมายเลขโทรศัพท์ (คู่สมรส)</label>
                                <input type="text" id="spouse_phone" name="spouse_phone" placeholder="หมายเลขโทรศัพท์">
                            </div>

                            <div class="form-group col-4">
                                <label for="spouse_mobile">หมายเลขโทรศัพท์มือถือ (คู่สมรส)</label>
                                <input type="text" id="spouse_mobile" name="spouse_mobile" placeholder="0XXXXXXXXX">
                            </div>

                            <div class="form-group col-6">
                                <label for="spouse_education">การศึกษา (คู่สมรส)</label>
                                <select id="spouse_education" name="spouse_education">
                                    <option value="">เลือกระดับการศึกษา</option>
                                    <option value="มัธยมต้น">มัธยมต้น</option>
                                    <option value="มัธยมปลาย">มัธยมปลาย</option>
                                    <option value="อุดมศึกษา">อุดมศึกษา</option>
                                    <option value="ปริญญาตรี">ปริญญาตรี</option>
                                    <option value="ปริญญาโท">ปริญญาโท</option>
                                    <option value="ปริญญาเอก">ปริญญาเอก</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>

                            <div class="form-group col-6">
                                <label for="spouse_occupation">อาชีพ (คู่สมรส)</label>
                                <select id="spouse_occupation" name="spouse_occupation">
                                    <option value="">เลือกอาชีพ</option>
                                    <option value="พนักงานบริษัท">พนักงานบริษัท</option>
                                    <option value="ข้าราชการ/ทหาร/ตำรวจ">ข้าราชการ/ทหาร/ตำรวจ</option>
                                    <option value="เจ้าของกิจการ">เจ้าของกิจการ</option>
                                    <option value="อาชีพอิสระ">อาชีพอิสระ</option>
                                    <option value="รับจ้าง">รับจ้าง</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>

                            <div class="form-group col-6 hidden" id="spouseOccupationOtherWrapper">
                                <label for="spouseOccupationOther">ระบุอาชีพ (คู่สมรส)</label>
                                <input type="text" id="spouseOccupationOther" name="spouseOccupationOther" placeholder="ระบุอาชีพของคู่สมรส">
                            </div>

                            <div class="form-group col-6">
                                <label for="spouse_company">บริษัท/สถานที่ทำงาน (คู่สมรส)</label>
                                <input type="text" id="spouse_company" name="spouse_company" placeholder="ชื่อบริษัทหรือสถานที่ทำงาน">
                            </div>

                            <div class="form-group col-6">
                                <label for="spouse_income">รายได้ต่อเดือน (คู่สมรส) (บาท)</label>
                                <input type="number" id="spouse_income" name="spouse_income" min="0" placeholder="รายได้ต่อเดือน">
                            </div>
                        </div>


                        <!-- Section 4: การแจ้งการมีวงเงินสินเชื่อบุคคล -->
                        <div id="section4_container" class="section4-container">
                            <div class="form-group col-12">
                                <label>การแจ้งการมีวงเงินสินเชื่อบุคคล (เฉพาะผู้มีรายได้น้อยกว่า 30,000 บาท)</label>
                                <span class="question-text">
                                    ในช่วง 2 เดือนที่ผ่านมาผู้กู้เคยได้รับอนุมัติสินเชื่อ หรือ ยื่นสมัครสินเชื่อบุคคล/สินเชื่อนาโนไฟแนนซ์/สินเชื่อสวัสดิการพนักงานกับสถาบันการเงินมากกว่า 2 แห่งหรือไม่
                                </span>
                            </div>

                            <div class="form-group col-6">
                                <select id="hasExistingLoan" name="hasExistingLoan">
                                    <option value="">เลือก</option>
                                    <option value="ใช่">ใช่</option>
                                    <option value="ไม่ใช่">ไม่ใช่</option>
                                </select>
                            </div>

                            <div class="form-group col-12 note-box">
                                <span>** กรณีพบว่ามีสินเชื่อบุคคล/สินเชื่อนาโน/สินเชื่อสวัสดิการพนักงานกับสถาบันการเงินตั้งแต่ 3 แห่งขึ้นไป บริษัทมีทีธิปฏิเสธการให้สินเชื่อ หรือระงับการให้สินเชื่อ หรือยกเลิกสัญญา</span>
                            </div>
                        </div>
                        <!-- ที่อยู่ -->
                        <div class="form-section-title">
                            <span class="form-section-title-icon">🏠</span> ข้อมูลที่อยู่
                        </div>

                        <div class="form-group col-12">
                            <label for="dwelling_type">ลักษณะที่อยู่อาศัย/ประเภทบ้านพักอาศัย</label>
                            <select id="dwelling_type" name="dwelling_type">
                                <option value="">เลือกประเภทที่อยู่</option>
                                <option value="บ้านเดี่ยว">บ้านเดี่ยว</option>
                                <option value="บ้านแฝด">บ้านแฝด</option>
                                <option value="บ้านตนเอง/คู่สมรส">บ้านตนเอง/คู่สมรส</option>
                                <option value="ทาวน์เฮาส์">ทาวน์เฮาส์</option>
                                <option value="อพาร์ทเม้นท์/แฟลต">อพาร์ทเม้นท์/แฟลต</option>
                                <option value="ห้องชุด">ห้องชุด</option>
                                <option value="อาคารพาณิช">อาคารพาณิช</option>
                                <option value="บ้านพักสวัสดิการ">บ้านพักสวัสดิการ</option>
                                <option value="อาศัยอยู่กับผู้อื่น">อาศัยอยู่กับผู้อื่น (ระบุ)</option>
                            </select>
                        </div>

                        <div class="form-group col-12 hidden" id="dwelling_type_other_wrapper">
                            <label for="dwelling_type_other">ระบุรายละเอียด</label>
                            <input type="text" id="dwelling_type_other" name="dwelling_type_other" placeholder="ระบุรายละเอียดที่อยู่">
                        </div>

                        <div class="form-group col-12">
                            <label for="residence_status">สถานที่อยู่</label>
                            <select id="residence_status" name="residence_status">
                                <option value="">เลือกสถานที่อยู่</option>
                                <option value="เช่า/ผ่อนชำระ">เช่า/ผ่อนชำระ</option>
                                <option value="ปลอดภาระ">ปลอดภาระ</option>
                                <option value="บ้านพักสวัสดิการ">บ้านพักสวัสดิการ</option>
                                <option value="อื่นๆ">อื่นๆ/Other (ระบุ)</option>
                            </select>
                        </div>

                        <div class="form-group col-12 hidden" id="residence_rent_wrapper">
                            <label for="residence_rent_amount">ยอดเช่า/ผ่อนต่อเดือน (บาท)</label>
                            <input type="number" id="residence_rent_amount" name="residence_rent_amount" placeholder="ระบุยอดเงิน">
                        </div>

                        <div class="form-group col-12 hidden" id="residence_status_other_wrapper">
                            <label for="residence_status_other">ระบุสถานที่อยู่</label>
                            <input type="text" id="residence_status_other" name="residence_status_other" placeholder="ระบุรายละเอียด">
                        </div>

                        <div class="form-group col-6">
                            <label for="residence_years">ที่อยู่อาศัยในปัจจุบัน อยู่อาศัยมาเป็นเวลา (ปี)</label>
                            <input type="number" id="residence_years" name="residence_years" placeholder="ระบุจำนวนปี" min="0">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_no">ที่อยู่ปัจจุบันเลขที่</label>
                            <input type="text" id="address_no" name="address_no" placeholder="เลขที่">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_floor">ชั้น</label>
                            <input type="text" id="address_floor" name="address_floor" placeholder="ชั้น">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_village">หมู่ ที่</label>
                            <input type="text" id="address_village" name="address_village" placeholder="หมู่ ที่">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_building">อาคาร/หมู่บ้าน</label>
                            <input type="text" id="address_building" name="address_building" placeholder="อาคาร/หมู่บ้าน">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_soi">ซอย</label>
                            <input type="text" id="address_soi" name="address_soi" placeholder="ซอย">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_road">ถนน</label>
                            <input type="text" id="address_road" name="address_road" placeholder="ถนน">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_subdistrict">แขวง/ตำบล</label>
                            <input type="text" id="address_subdistrict" name="address_subdistrict" placeholder="แขวง/ตำบล">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_district">เขต / อำเภอ</label>
                            <input type="text" id="address_district" name="address_district" placeholder="เขต / อำเภอ">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_province">จังหวัด</label>
                            <input type="text" id="address_province" name="address_province" placeholder="จังหวัด">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_postal">รหัสไปรษณีย์</label>
                            <input type="text" id="address_postal" name="address_postal" placeholder="รหัสไปรษณีย์" maxlength="5">
                        </div>

                        <div class="form-group col-6">
                            <label for="phone_home">หมายเลขโทรศัพท์บ้าน</label>
                            <input type="text" id="phone_home" name="phone_home" placeholder="หมายเลขโทรศัพท์บ้าน">
                        </div>

                        <div class="form-group col-6">
                            <label for="phone_mobile">หมายเลขโทรศัพท์มือถือ</label>
                            <input type="text" id="phone_mobile" name="phone_mobile" placeholder="หมายเลขโทรศัพท์มือถือ">
                        </div>

                        <div class="form-group col-6">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="example@email.com">
                        </div>

                        <div class="form-group col-6">
                            <label for="line_id">Line ID</label>
                            <input type="text" id="line_id" name="line_id" placeholder="Line ID">
                        </div>
                        <!-- /ที่อยู่ -->

                        <!-- สถานที่ทำงานปัจจุบัน -->
                        <div class="form-section-title">
                            <span class="form-section-title-icon">🏢</span> สถานที่ทำงานปัจจุบัน
                            <div class="checkbox-group">
                                <input type="checkbox" id="useHomeAddress" name="useHomeAddress">
                                <label for="useHomeAddress">ใช้งานที่อยู่เดียวกันกับ ข้อมูลที่อยู่</label>
                            </div>
                        </div>

                        <div class="form-group col-12">
                            <label for="companyType">ประเภทสถานที่ทำงาน</label>
                            <select id="companyType" name="companyType">
                                <option value="">เลือกประเภท</option>
                                <option value="บจก.">บจก.</option>
                                <option value="บมจ.">บมจ.</option>
                                <option value="หจก.">หจก.</option>
                                <option value="ร้านค้า/ทะเบียนพาณิชย์">ร้านค้า/ทะเบียนพาณิชย์</option>
                                <option value="อื่นๆ">อื่นๆ (ระบุ)</option>
                            </select>
                        </div>

                        <div class="form-group col-12 hidden" id="companyTypeOtherWrapper">
                            <label for="companyTypeOther">ระบุประเภทสถานที่ทำงาน</label>
                            <input type="text" id="companyTypeOther" name="companyTypeOther" placeholder="ระบุประเภท">
                        </div>

                        <div class="form-group col-12">
                            <label for="companyName">ชื่อกิจการ/ที่ทำงาน</label>
                            <input type="text" id="companyName" name="companyName" placeholder="ชื่อกิจการ/ที่ทำงาน">
                        </div>

                        <div class="form-group col-6">
                            <label for="businessType">ประเภทธุรกิจ</label>
                            <input type="text" id="businessType" name="businessType" placeholder="ประเภทธุรกิจ">
                        </div>

                        <div class="form-group col-6">
                            <label for="workOccupation">อาชีพ</label>
                            <input type="text" id="workOccupation" name="workOccupation" placeholder="อาชีพ">
                        </div>

                        <div class="form-group col-6">
                            <label for="workPosition">ตำแหน่ง</label>
                            <input type="text" id="workPosition" name="workPosition" placeholder="ตำแหน่ง">
                        </div>

                        <div class="form-group col-3">
                            <label for="workYears">อายุงาน (ปี)</label>
                            <input type="number" id="workYears" name="workYears" placeholder="ปี" min="0">
                        </div>

                        <div class="form-group col-3">
                            <label for="workMonths">อายุงาน (เดือน)</label>
                            <input type="number" id="workMonths" name="workMonths" placeholder="เดือน" min="0" max="11">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressNo">เลขที่อยู่</label>
                            <input type="text" id="workAddressNo" name="workAddressNo" placeholder="เลขที่อยู่">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressFloor">ชั้น</label>
                            <input type="text" id="workAddressFloor" name="workAddressFloor" placeholder="ชั้น">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressVillage">หมู่ ที่</label>
                            <input type="text" id="workAddressVillage" name="workAddressVillage" placeholder="หมู่ ที่">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressBuilding">อาคาร/หมู่บ้าน</label>
                            <input type="text" id="workAddressBuilding" name="workAddressBuilding" placeholder="อาคาร/หมู่บ้าน">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressSoi">ซอย</label>
                            <input type="text" id="workAddressSoi" name="workAddressSoi" placeholder="ซอย">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressRoad">ถนน</label>
                            <input type="text" id="workAddressRoad" name="workAddressRoad" placeholder="ถนน">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressSubdistrict">แขวง/ตำบล</label>
                            <input type="text" id="workAddressSubdistrict" name="workAddressSubdistrict" placeholder="แขวง/ตำบล">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressDistrict">เขต / อำเภอ</label>
                            <input type="text" id="workAddressDistrict" name="workAddressDistrict" placeholder="เขต / อำเภอ">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressProvince">จังหวัด</label>
                            <input type="text" id="workAddressProvince" name="workAddressProvince" placeholder="จังหวัด">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressPostal">รหัสไปรษณีย์</label>
                            <input type="text" id="workAddressPostal" name="workAddressPostal" placeholder="รหัสไปรษณีย์" maxlength="5">
                        </div>

                        <div class="form-group col-12">
                            <label for="workPhone">หมายเลขโทรศัพท์ (ที่ทำงาน)</label>
                            <input type="text" id="workPhone" name="workPhone" placeholder="หมายเลขโทรศัพท์">
                        </div>

                        <!-- ที่ทำงานเดิม (ถ้าอายุงาน < 1 ปี) -->
                        <div id="previousWorkSection" class="section4-container hidden">

                            <div class="form-group col-12 note-box">
                                <span>** หากอายุงานไม่ถึง 1 ปี โปรดระบุที่ทำงานเดิม</span>
                            </div>

                            <div class="form-group col-12">
                                <label for="previousCompanyName">ชื่อที่ทำงานเดิม</label>
                                <input type="text" id="previousCompanyName" name="previousCompanyName" placeholder="ชื่อที่ทำงานเดิม">
                            </div>

                            <div class="form-group col-6">
                                <label for="previousBusinessType">ประเภทธุรกิจ</label>
                                <input type="text" id="previousBusinessType" name="previousBusinessType" placeholder="ประเภทธุรกิจ">
                            </div>

                            <div class="form-group col-6">
                                <label for="previousPosition">ตำแหน่ง</label>
                                <input type="text" id="previousPosition" name="previousPosition" placeholder="ตำแหน่ง">
                            </div>

                            <div class="form-group col-6">
                                <label for="previousIncome">รายได้ต่อเดือน (บาท)</label>
                                <input type="number" id="previousIncome" name="previousIncome" placeholder="รายได้ต่อเดือน" min="0">
                            </div>

                            <div class="form-group col-6">
                                <label for="previousWorkYears">อายุงาน (ปี)</label>
                                <input type="number" id="previousWorkYears" name="previousWorkYears" placeholder="ปี" min="0">
                            </div>

                            <div class="form-group col-12">
                                <label for="previousPhone">หมายเลขโทรศัพท์</label>
                                <input type="text" id="previousPhone" name="previousPhone" placeholder="หมายเลขโทรศัพท์">
                            </div>
                        </div>

                        <!-- สถานที่ส่งเอกสาร -->
                        <div class="form-group col-12">
                            <label for="documentDelivery">สถานที่ส่งเอกสาร</label>
                            <select id="documentDelivery" name="documentDelivery">
                                <option value="">เลือกสถานที่</option>
                                <option value="ที่อยู่ปัจจุบัน">ที่อยู่ปัจจุบัน</option>
                                <option value="ที่ทำงาน">ที่ทำงาน</option>
                                <option value="E-mail">E-mail</option>
                            </select>
                        </div>

                        <div class="form-group col-12 hidden" id="documentEmailWrapper">
                            <label for="documentEmail">E-mail : ระบุ</label>
                            <input type="email" id="documentEmail" name="documentEmail" placeholder="example@email.com">
                        </div>

                        <!--  ข้อมูลบุคคลอ้างอิง -->
                        <div class="form-section-title">
                            <span class="form-section-title-icon">👥</span> ข้อมูลบุคคลอ้างอิง
                        </div>

                        <div class="form-group col-12">
                            <label for="refName">ชื่อ - นามสกุล</label>
                            <input type="text" id="refName" name="refName" placeholder="ชื่อ - นามสกุล">
                        </div>

                        <div class="form-group col-12">
                            <label for="refRelation">ความสัมพันธ์กับผู้กู้</label>
                            <input type="text" id="refRelation" name="refRelation" placeholder="ความสัมพันธ์กับผู้กู้">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressNo">ที่อยู่ปัจจุบัน เลขที่</label>
                            <input type="text" id="refAddressNo" name="refAddressNo" placeholder="เลขที่">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressFloor">ชั้น</label>
                            <input type="text" id="refAddressFloor" name="refAddressFloor" placeholder="ชั้น">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressVillage">หมู่ที่</label>
                            <input type="text" id="refAddressVillage" name="refAddressVillage" placeholder="หมู่ที่">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressBuilding">อาคาร/หมู่บ้าน</label>
                            <input type="text" id="refAddressBuilding" name="refAddressBuilding" placeholder="อาคาร/หมู่บ้าน">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressSoi">ซอย</label>
                            <input type="text" id="refAddressSoi" name="refAddressSoi" placeholder="ซอย">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressRoad">ถนน</label>
                            <input type="text" id="refAddressRoad" name="refAddressRoad" placeholder="ถนน">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressSubdistrict">แขวง/ตำบล</label>
                            <input type="text" id="refAddressSubdistrict" name="refAddressSubdistrict" placeholder="แขวง/ตำบล">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressDistrict">เขต/อำเภอ</label>
                            <input type="text" id="refAddressDistrict" name="refAddressDistrict" placeholder="เขต/อำเภอ">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressProvince">จังหวัด</label>
                            <input type="text" id="refAddressProvince" name="refAddressProvince" placeholder="จังหวัด">
                        </div>

                        <div class="form-group col-6">
                            <label for="refAddressPostal">รหัสไปรษณีย์</label>
                            <input type="text" id="refAddressPostal" name="refAddressPostal" placeholder="รหัสไปรษณีย์" maxlength="5">
                        </div>

                        <div class="form-group col-6">
                            <label for="refPhoneHome">หมายเลขโทรศัพท์บ้าน</label>
                            <input type="text" id="refPhoneHome" name="refPhoneHome" placeholder="หมายเลขโทรศัพท์บ้าน">
                        </div>

                        <div class="form-group col-6">
                            <label for="refPhoneMobile">หมายเลขโทรศัพท์มือถือ</label>
                            <input type="text" id="refPhoneMobile" name="refPhoneMobile" placeholder="หมายเลขโทรศัพท์มือถือ">
                        </div>

                        <div class="form-group col-6">
                            <label for="refEmail">E-mail</label>
                            <input type="email" id="refEmail" name="refEmail" placeholder="example@email.com">
                        </div>

                        <div class="form-group col-6">
                            <label for="refLineId">Line ID</label>
                            <input type="text" id="refLineId" name="refLineId" placeholder="Line ID">
                        </div>
                        <!--  /ข้อมูลบุคคลอ้างอิง -->

                        <!-- ความประสงค์ในการสมัครใช้สินเชื่อ -->
                        <div class="form-section-title">
                            <span class="form-section-title-icon">💰</span> ความประสงค์ในการสมัครใช้สินเชื่อ
                        </div>

                        <div class="form-group col-12">
                            <label for="loanTerm">ระยะเวลาผ่อนชำระคืน</label>
                            <select id="loanTerm" name="loanTerm">
                                <option value="">เลือกระยะเวลา</option>
                                <option value="12">12 เดือน</option>
                                <option value="24">24 เดือน</option>
                                <option value="36">36 เดือน</option>
                                <option value="48">48 เดือน</option>
                                <option value="50">50 เดือน</option>
                            </select>
                        </div>

                        <div class="form-group col-12">
                            <label for="loanAmountType">วงเงินสินเชื่อที่ต้องการ</label>
                            <select id="loanAmountType" name="loanAmountType">
                                <option value="">เลือกวงเงิน</option>
                                <option value="full">เต็มจำนวนตามที่บริษัทอนุมัติ</option>
                                <option value="custom">วงเงินที่ขอกู้/จำนวนทั้งสิ้น</option>
                            </select>
                        </div>

                        <div class="form-group col-12 hidden" id="customLoanAmountWrapper">
                            <label for="customLoanAmount">ระบุจำนวนเงินที่ขอกู้ (บาท)</label>
                            <input type="number" id="customLoanAmount" name="customLoanAmount" placeholder="ระบุจำนวนเงิน" min="0">
                        </div>

                        <div class="form-group col-12">
                            <label for="loanPurpose">วัตถุประสงค์ในการขอสินเชื่อ</label>
                            <input type="text" id="loanPurpose" name="loanPurpose" placeholder="วัตถุประสงค์ในการขอสินเชื่อ">
                        </div>

                        <div class="form-group col-12 note-box">
                            <span>(กรณีที่บริษัทไม่สามารถอนุมัติเงินตามที่ท่านเลือกได้ บริษัทจะอนุมัติวงเงินให้ท่านตามความเหมาะสม)</span>
                        </div>

                        <!-- สำหรับผู้ขอสินเชื่อที่ประสงค์ขอรับวงเงินกู้โดยวิธีการโอนเข้าบัญชีเงินฝาก -->
                        <div class="form-section-title">
                            <span class="form-section-title-icon">🏦</span> ข้อมูลบัญชีสำหรับรับโอนเงินกู้
                        </div>

                        <div class="form-group col-12">
                            <label for="bankName">ธนาคาร</label>
                            <input type="text" id="bankName" name="bankName" placeholder="ชื่อธนาคาร">
                        </div>

                        <div class="form-group col-6">
                            <label for="bankBranch">สาขา</label>
                            <input type="text" id="bankBranch" name="bankBranch" placeholder="สาขา">
                        </div>

                        <div class="form-group col-6">
                            <label for="accountName">ชื่อบัญชี</label>
                            <input type="text" id="accountName" name="accountName" placeholder="ชื่อบัญชี">
                        </div>

                        <div class="form-group col-6">
                            <label for="accountType">ประเภทบัญชี</label>
                            <input type="text" id="accountType" name="accountType" placeholder="ประเภทบัญชี (เช่น ออมทรัพย์, กระแสรายวัน)">
                        </div>

                        <div class="form-group col-6">
                            <label for="accountNumber">เลขที่บัญชี</label>
                            <input type="text" id="accountNumber" name="accountNumber" placeholder="เลขที่บัญชี">
                        </div>

                        <!-- Section 5: ข้อตกลงความยินยอม และเช็นยินยอม -->
                        <div class="form-section-title">
                            <span class="form-section-title-icon">📝</span> ส่วนที่ 5: ข้อความยินยอม
                        </div>

                        <div class="form-group col-12">
                            <p style="text-align: left; line-height: 1.8; margin-bottom: 1.5rem; text-indent: 3rem;">
                                ข้าพเจ้าขอรับรองว่าข้อมูลรายละเอียดที่ระบุไว้ข้างต้นเป็นความจริงทุกประการ และข้าพเจ้ารับทราบการมอบอำนาจให้ทางบริษัทติดต่อสอบถาม และ/หรือ ตรวจสอบข้อมูลรายละเอียดต่างๆ ของข้าพเจ้าในบัตรประชาชน และ/หรือ บุคคลที่เกี่ยวข้องได้จากบุคคล และ/หรือ นิติบุคคลอื่นใดและไม่ว่าด้วยวิธีใด นอกจากข้าพเจ้ารับทราบให้บริษัทมีสิทธิอย่างสมบูรณ์ที่จะปฏิเสธ หรืออนุมัติการขอสินเชื่อครั้งนี้ หรืออนุมัติเป็นอย่างอื่น รวมทั้งปฏิบัติตามข้อกำหนดและเงื่อนไขตามที่บริษัทเห็นสมควรทุกประการ และ/หรือ ที่บริษัทจะเปลี่ยนแปลงภายหลัง และข้าพเจ้ารับทราบที่จะเสียค่าธรรมเนียม ค่าใช้จ่ายต่างๆ ที่บริษัทกำหนดทุกประการ การดำเนินนการของบริษัทตามความประสงค์ของข้าพเจ้าในคำขอฉบับนี้ให้ถือว่าข้าพเจ้าได้รับสินเชื่อโดยชอบธรรมจากบริษัท (ผู้ได้รับผลประโยชน์ที่แท้จริงคือผู้ขอสินเชื่อ)
                            </p>
                        </div>

                        <div class="form-group col-12">
                            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 0.5rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
                                <h4 style="color: #856404; margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 700;">ข้อควรระวัง</h4>
                                <ul style="margin: 0; padding-left: 1.5rem; list-style-type: disc; color: #856404;">
                                    <li style="margin-bottom: 0.5rem;">หากท่านผิดนัดชำระหนี้ บริษัทจะคิดดอกเบี้ยสูงสุดตั้งแต่วันที่เริ่มผิดนัด และอาจจะมีค่าติดตามทวงถามหนี้ (คิดเมื่อครบกำหนดชำระ และมีการทวงถามหนี้แล้ว)</li>
                                    <li>กรุณาอ่านข้อกำหนด และเงื่อนไขที่สำคัญก่อนลงนาม หากมีข้อสงสัยสามารถติดต่อเจ้าหน้าที่ เบอร์โทรศัพท์ 082-257-7997</li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group col-12">
                            <p style="text-align: left; line-height: 1.8; margin-bottom: 1.5rem; text-indent: 3rem;">
                                ข้าพเจ้าทราบว่าบริษัทอาจเก็บรวบรวมข้อมูลส่วนบุคคลของข้าพเจ้าและบุคคลที่ข้าพเจ้าระบุไว้ในเอกสารนี้ เช่น ผู้กู้ร่วม ผู้ค้ำประกัน เพื่อใช้ในการบริหารความเสี่ยงของบริษัท และขอรับรองว่า ข้าพเจ้าได้แจ้งให้บุคคลดังกล่าวทราบถึงการเก็บรวบรวมข้อมูลส่วนบุคคลนี้ด้วย
                            </p>
                            <p style="text-align: left; line-height: 1.8; margin-bottom: 1.5rem; text-indent: 3rem;">
                                ข้าพเจ้าได้อ่านและทำความเข้าใจ รับทราบถึงเนื้อหาของประกาศความเป็นส่วนตัวของบริษัท ดังที่ปรากฏรายละเอียดหน้าเว็บไซต์ <a href="https://www.bigmoneyplus.co.th/การคุ้มครองข้อมูลส่วนบุคคล" target="_blank" style="color: #059669; text-decoration: underline;">www.bigmoneyplus.co.th/การคุ้มครองข้อมูลส่วนบุคคล</a> และรับทราบว่าบริษัทเก็บรวบรวมใช้ และ/หรือ เปิดเผยข้อมูลส่วนบุคคลภายใต้หรือเกี่ยวกับคำขอฉบับนี้เพื่อวัตถุประสงค์ตามที่ระบุไว้ในประกาศความเป็นส่วนตัวของบริษัท
                            </p>
                        </div>

                        <div class="form-group col-12" style="margin-bottom: 0; display: flex; flex-direction: column; align-items: center;">
                            <label>ลงชื่อผู้ขอสินเชื่อ</label>
                            <div style="border: 2px solid #e5e7eb; border-radius: 0.5rem; background: #ffffff; position: relative; overflow: hidden; width: 400px;">
                                <canvas id="signaturePad" style="width: 100%; height: 120px; cursor: crosshair;"></canvas>
                                <button type="button" id="clearSignatureBtn" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem 0.5rem; background: #ef4444; color: white; border: none; border-radius: 0.25rem; font-size: 0.75rem; cursor: pointer;">ล้าง</button>
                            </div>
                            <input type="hidden" id="signatureData" name="signatureData">
                            <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280; text-align: center;">วันที่เซ็น: {{ date('d/m/Y') }}</p>
                        </div>

                        <!-- /Section 5: ข้อตกลงความยินยอม และเช็นยินยอม -->
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
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

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
                            <td style="padding: 0.5rem; font-weight: 600;">แหละที่มาของรายได้พิเศษ:</td>
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
                    <h4 style="color: #0369a1; margin: 0 0 1rem 0; font-size: 1.01rem; font-weight: 700; border-bottom: 1px solid #0ea5e9; padding-bottom: 0.35rem;">การแจ้งการมีวงเงินสินเชื่อบุคล (เฉพาะผู้มีรายได้น้อยกว่า 30,000 บาท)</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr style="border-bottom: 1px solid #e0f2fe;">
                            <td style="padding: 0.5rem; font-weight: 600;">ในช่วง 2 เดือนที่ผ่านมาผู้กู้เคยได้รับอนุมัติสินเชื่อ หรือ ยื่นสมัครสินเชื่อบุคคล/สินเชื่อนาโนไฟแนนซ์/สินเชื่อสวัสดิการพนักงานกับสถาบันการเงินมากกว่า 2 แห่งหรือไม่:</td>
                            <td style="padding: 0.5rem;">${hasExistingLoan || '-'}</td>
                        </tr>
                    </table>
                    <div style="margin-top: 0.75rem; background: #fef9c3; border: 1px solid #facc15; padding: 0.6rem 0.85rem; border-radius: 0.375rem; font-size: 0.85rem; color: #854d0e;">
                        <strong>**</strong> กรณีพบว่ามีสินเชื่อบุคคล/สินเชื่อนาโน/สินเชื่อสวัสดิการพนักงานกับสถาบันการเงินตั้งแต่ 3 แห่งขึ้นไป บริษัทมีทีธิปฏิเสธการให้สินเชื่อ หรือระงับการให้สินเชื่อ หรือยกเลิกสัญญา
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
                        ข้าพเจ้าขอรับรองว่าข้อมูลรายละเอียดที่ระบุไว้ข้างต้นเป็นความจริงทุกประการ และข้าพเจ้ารับทราบการมอบอำนาจให้ทางบริษัทติดต่อสอบถาม และ/หรือ ตรวจสอบข้อมูลรายละเอียดต่างๆ ของข้าพเจ้าในบัตรประชาชน และ/หรือ บุคคลที่เกี่ยวข้องได้จากบุคคล และ/หรือ นิติบุคคลอื่นใดและไม่ว่าด้วยวิธีใด นอกจากข้าพเจ้ารับทราบให้บริษัทมีสิทธิอย่างสมบูรณ์ที่จะปฏิเสธ หรืออนุมัติการขอสินเชื่อครั้งนี้ หรืออนุมัติเป็นอย่างอื่น รวมทั้งปฏิบัติตามข้อกำหนดและเงื่อนไขตามที่บริษัทเห็นสมควรทุกประการ และ/หรือ ที่บริษัทจะเปลี่ยนแปลงภายหลัง และข้าพเจ้ารับทราบที่จะเสียค่าธรรมเนียม ค่าใช้จ่ายต่างๆ ที่บริษัทกำหนดทุกประการ การดำเนินนการของบริษัทตามความประสงค์ของข้าพเจ้าในคำขอฉบับนี้ให้ถือว่าข้าพเจ้าได้รับสินเชื่อโดยชอบธรรมจากบริษัท (ผู้ได้รับผลประโยชน์ที่แท้จริงคือผู้ขอสินเชื่อ)
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

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('consentModal');
            const openBtn = document.getElementById('openConsentModal');
            const closeBtn = document.getElementById('closeConsentModal');
            const cancelBtn = document.getElementById('cancelConsentModal');

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

                    if (['text', 'number', 'email', 'tel', 'search'].includes(el.type)) {
                        el.setAttribute('readonly', 'readonly');
                        el.addEventListener('focus', function() {
                            this.removeAttribute('readonly');
                        }, { once: true });
                    }
                });
            }

            disableFormAutofill(modal?.querySelector('.consent-form'));

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
                    residenceRentAmountInput.value = '';
                    residenceStatusOtherWrapper.classList.add('hidden');
                    residenceStatusOtherInput.removeAttribute('required');
                    residenceStatusOtherInput.value = '';

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

            // Initialize Signature Pad
            let signaturePad;
            function initSignaturePad() {
                const canvas = document.getElementById('signaturePad');
                if (!canvas) return;
                
                // Set canvas size correctly for high DPI
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width * window.devicePixelRatio;
                canvas.height = rect.height * window.devicePixelRatio;
                
                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)'
                });
                
                // Rescale context for high DPI
                const ctx = canvas.getContext('2d');
                ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
                
                // Save signature data to hidden input (as JSON)
                signaturePad.addEventListener('endStroke', function() {
                    document.getElementById('signatureData').value = JSON.stringify(signaturePad.toData());
                });
            }
            
            function clearSignature() {
                if (signaturePad) {
                    signaturePad.clear();
                    document.getElementById('signatureData').value = '';
                }
            }
            
            // Initialize signature pad when modal opens
            openBtn?.addEventListener('click', function() {
                setTimeout(() => {
                    initSignaturePad();
                    // Bind clear button after init
                    const clearBtn = document.getElementById('clearSignatureBtn');
                    if (clearBtn) {
                        clearBtn.addEventListener('click', clearSignature);
                    }
                }, 300); // Wait for modal to open
            });
            
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
