<!-- Modal สำหรับสร้างใบยินยอม -->
<div id="consentModal" class="modal" data-next-app-no="{{ $nextAppNo }}" data-store-url="{{ route('consent.store') }}" data-update-base-url="{{ url('/consent') }}">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 id="consentModalTitle">สร้างใบยินยอมแบบละเอียด</h3>
            <button type="button" class="close-btn" id="closeConsentModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body scrollable">
            <form method="POST" action="{{ route('consent.store') }}" class="consent-form" id="consentForm" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="consentFormMethod" value="POST">
                <input type="hidden" name="consent_id" id="consent_id" value="">
                <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                    <input type="text" name="prevent_autofill" tabindex="-1" autocomplete="off">
                </div>
                <div class="form-grid">
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
                        <label for="id_card">เลขประจำตัวประชาชน / เลขทะเบียนนิติบุคคล <span class="required-asterisk">*</span></label>
                        <input type="text" id="id_card" name="id_card" placeholder="เลข 13 หลัก" maxlength="13" required>
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
                        <label for="extraIncomeSource">แหล่งที่มาของรายได้พิเศษ</label>
                        <input type="text" id="extraIncomeSource" name="extraIncomeSource" placeholder="แหล่งที่มาของรายได้พิเศษ">
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

                    <div class="form-section-title hidden" id="spouse_section_title">
                        <span class="form-section-title-icon">👨‍👩‍👧</span> ข้อมูลคู่สมรส
                    </div>

                    <div id="spouse_fields" class="hidden spouse-fields">
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
                            <span>** กรณีพบว่ามีสินเชื่อบุคคล/สินเชื่อนาโน/สินเชื่อสวัสดิการพนักงานกับสถาบันการเงินตั้งแต่ 3 แห่งขึ้นไป บริษัทมีสิทธิ์ปฏิเสธการให้สินเชื่อ หรือระงับการให้สินเชื่อ หรือยกเลิกสัญญา</span>
                        </div>
                    </div>

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
                        <label for="address_village">หมู่ที่</label>
                        <input type="text" id="address_village" name="address_village" placeholder="หมู่ที่">
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
                        <input type="text" id="address_subdistrict" name="address_subdistrict" placeholder="พิมพ์ค้นหา/เลือก แขวง/ตำบล" list="address_subdistrict_list">
                        <datalist id="address_subdistrict_list"></datalist>
                    </div>

                    <div class="form-group col-6">
                        <label for="address_district">เขต / อำเภอ</label>
                        <input type="text" id="address_district" name="address_district" placeholder="พิมพ์ค้นหา/เลือก เขต / อำเภอ" list="address_district_list">
                        <datalist id="address_district_list"></datalist>
                    </div>

                    <div class="form-group col-6">
                        <label for="address_province">จังหวัด</label>
                        <input type="text" id="address_province" name="address_province" placeholder="พิมพ์ค้นหา/เลือก จังหวัด" list="address_province_list">
                        <datalist id="address_province_list"></datalist>
                    </div>

                    <div class="form-group col-6">
                        <label for="address_postal">รหัสไปรษณีย์</label>
                        <input type="text" id="address_postal" name="address_postal" placeholder="พิมพ์ค้นหา/เลือก รหัสไปรษณีย์" list="address_postal_list">
                        <datalist id="address_postal_list"></datalist>
                    </div>

                    <div class="form-group col-6">
                        <label for="phone_home">หมายเลขโทรศัพท์บ้าน</label>
                        <input type="text" id="phone_home" name="phone_home" placeholder="หมายเลขโทรศัพท์บ้าน">
                    </div>

                    <div class="form-group col-6">
                        <label for="phone_mobile">หมายเลขโทรศัพท์มือถือ <span class="required-asterisk">*</span></label>
                        <input type="text" id="phone_mobile" name="phone_mobile" placeholder="0XXXXXXXXX" required>
                    </div>

                    <div class="form-group col-6">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" placeholder="example@email.com">
                    </div>

                    <div class="form-group col-6">
                        <label for="line_id">Line ID</label>
                        <input type="text" id="line_id" name="line_id" placeholder="Line ID">
                    </div>

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
                        <label for="workAddressVillage">หมู่ที่</label>
                        <input type="text" id="workAddressVillage" name="workAddressVillage" placeholder="หมู่ที่">
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

                    <div class="form-section-title">
                        <span class="form-section-title-icon">📝</span> ส่วนที่ 5: ข้อความยินยอม
                    </div>

                    <div class="form-group col-12">
                        <p style="text-align: left; line-height: 1.8; margin-bottom: 1.5rem; text-indent: 3rem;">
                            ข้าพเจ้าขอรับรองว่าข้อมูลรายละเอียดที่ระบุไว้ข้างต้นเป็นความจริงทุกประการ และข้าพเจ้ารับทราบการมอบอำนาจให้ทางบริษัทติดต่อสอบถาม และ/หรือ ตรวจสอบข้อมูลรายละเอียดต่างๆ ของข้าพเจ้าในบัตรประชาชน และ/หรือ บุคคลที่เกี่ยวข้องได้จากบุคคล และ/หรือ นิติบุคคลอื่นใดและไม่ว่าด้วยวิธีใด นอกจากข้าพเจ้ารับทราบให้บริษัทมีสิทธิอย่างสมบูรณ์ที่จะปฏิเสธ หรืออนุมัติการขอสินเชื่อครั้งนี้ หรืออนุมัติเป็นอย่างอื่น รวมทั้งปฏิบัติตามข้อกำหนดและเงื่อนไขตามที่บริษัทเห็นสมควรทุกประการ และ/หรือ ที่บริษัทจะเปลี่ยนแปลงภายหลัง และข้าพเจ้ารับทราบที่จะเสียค่าธรรมเนียม ค่าใช้จ่ายต่างๆ ที่บริษัทกำหนดทุกประการ การดำเนินการของบริษัทตามความประสงค์ของข้าพเจ้าในคำขอฉบับนี้ให้ถือว่าข้าพเจ้าได้รับสินเชื่อโดยชอบธรรมจากบริษัท (ผู้ได้รับผลประโยชน์ที่แท้จริงคือผู้ขอสินเชื่อ)
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

                    <div class="form-group col-12 signature-group">
                        <label>ลงชื่อผู้ขอสินเชื่อ</label>
                        <div class="signature-pad-box">
                            <canvas id="signaturePad" style="width: 100%; height: 120px; cursor: crosshair;"></canvas>
                            <button type="button" id="clearSignatureBtn" class="signature-clear-btn">ล้าง</button>
                        </div>
                        <input type="hidden" id="signatureData" name="signatureData">
                        <p class="signature-date">วันที่เซ็น: {{ date('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="form-actions modal-form-actions">
                    <button type="button" class="action-btn outline" id="cancelConsentModal">ยกเลิก</button>
                    <button type="submit" class="action-btn" id="consentSubmitBtn">บันทึกข้อมูลใบสมัคร</button>
                </div>
            </form>
        </div>
    </div>
</div>
