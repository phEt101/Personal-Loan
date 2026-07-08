<!-- Modal สำหรับสร้างใบยินยอม -->
<div id="consentModal" class="modal" data-next-app-no="{{ $nextAppNo }}">
    <div class="modal-content modal-lg">
        <form method="POST" action="#" class="consent-form-wrapper" id="consentForm" autocomplete="off" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="consent_id" id="consent_id" value="">
            
            <div class="modal-header">
                <h3 id="consentModalTitle">สร้างใบยินยอมแบบละเอียด</h3>
                <button type="button" class="close-btn" id="closeConsentModal" aria-label="Close modal">&times;</button>
            </div>
            
            <div class="modal-body scrollable">
                <!-- Step Indicator -->
                <div class="wizard-steps" id="wizardSteps">
                    <div class="wizard-step active" data-step="1">
                        <div class="wizard-step-icon">1</div>
                        <div class="wizard-step-label">ข้อมูลส่วนตัว</div>
                    </div>
                    <div class="wizard-step" data-step="2">
                        <div class="wizard-step-icon">2</div>
                        <div class="wizard-step-label">ที่อยู่</div>
                    </div>
                    <div class="wizard-step" data-step="3">
                        <div class="wizard-step-icon">3</div>
                        <div class="wizard-step-label">ข้อมูลอาชีพ</div>
                    </div>
                    <div class="wizard-step" data-step="4">
                        <div class="wizard-step-icon">4</div>
                        <div class="wizard-step-label">รายได้</div>
                    </div>
                    <div class="wizard-step" data-step="5">
                        <div class="wizard-step-icon">5</div>
                        <div class="wizard-step-label">บุคคลอ้างอิง</div>
                    </div>
                    <div class="wizard-step" data-step="6">
                        <div class="wizard-step-icon">6</div>
                        <div class="wizard-step-label">กู้/ชำระเงิน</div>
                    </div>
                    <div class="wizard-step" data-step="7">
                        <div class="wizard-step-icon">7</div>
                        <div class="wizard-step-label">ยินยอม</div>
                    </div>
                </div>

                <div class="hidden-accessible" aria-hidden="true">
                    <input type="text" name="prevent_autofill" tabindex="-1" autocomplete="off">
                </div>

                <!-- Step 1: ข้อมูลใบคำขอ + สำหรับเจ้าหน้าที่บริษัท + ข้อมูลส่วนตัว -->
                <div class="step-container active" data-step="1">
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
                            <input type="text" id="app_no" name="app_no" value="{{ $nextAppNo }}" maxlength="13" readonly class="input-readonly">
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
                            <label for="title">คำนำหน้านาม <span class="required-asterisk">*</span></label>
                            <select id="title" name="title" required>
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
                            <label for="name_en">ชื่อ - สกุล (ภาษาอังกฤษ)</label>
                            <input type="text" id="name_en" name="name_en" placeholder="NAME - SURNAME IN ENGLISH (UPPERCASE)">
                        </div>

                        <div class="form-group col-6">
                            <label for="birthdate">วัน / เดือน / ปีเกิด <span class="required-asterisk">*</span></label>
                            <input type="date" id="birthdate" name="birthdate" required>
                        </div>

                        <div class="form-group col-6">
                            <label for="nationality">สัญชาติ <span class="required-asterisk">*</span></label>
                            <input type="text" id="nationality" name="nationality" placeholder="สัญชาติ" required>
                        </div>

                        <div class="form-group col-3">
                            <label for="id_type">เอกสารระบุตัวตน <span class="required-asterisk">*</span></label>
                            <select id="id_type" name="id_type" required>
                                <option value="id_card">บัตรประจำตัวประชาชน</option>
                                <option value="passport">หนังสือเดินทาง (Passport)</option>
                            </select>
                        </div>
                        
                        <div class="form-group col-9" id="idNumberGroup">
                            <label for="id_card" id="idNumberLabel">เลขบัตรประจำตัวประชาชน <span class="required-asterisk">*</span></label>
                            <input type="text" id="id_card" name="id_card" placeholder="เลข 13 หลัก" maxlength="13" required>
                        </div>

                        <div class="form-group col-6">
                            <label for="education">การศึกษา <span class="required-asterisk">*</span></label>
                            <select id="education" name="education" required>
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
                            <label for="marital_status">สถานภาพสมรส <span class="required-asterisk">*</span></label>
                            <select id="marital_status" name="marital_status" required>
                                <option value="โสด">โสด</option>
                                <option value="สมรส">สมรส</option>
                                <option value="สมรสไม่จดทะเบียน">สมรสไม่จดทะเบียน</option>
                                <option value="หย่า">หย่า</option>
                                <option value="หม้าย">หม้าย</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 2: ที่อยู่ปัจจุบัน -->
                <div class="step-container" data-step="2">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            <span class="form-section-title-icon">📍</span> ที่อยู่ปัจจุบัน
                        </div>

                        <div class="form-group col-12">
                            <label for="residence_status">สถานะของการอยู่อาศัย <span class="required-asterisk">*</span></label>
                            <select id="residence_status" name="residence_status" required>
                                <option value="">เลือกสถานะของการอยู่อาศัย</option>
                                <option value="บ้านตนเองปลอดภาระ">บ้านตนเองปลอดภาระ</option>
                                <option value="บ้านตนเองกำลังผ่อน">บ้านตนเองกําลังผ่อน</option>
                                <option value="บ้านบิดา/มารดา">บ้านบิดา/มารดา</option>
                                <option value="บ้านญาติ/พี่น้อง/บุคคลอื่น">บ้านญาติ/พี่น้อง/บุคคลอื่น</option>
                                <option value="สวัสดิการ">สวัสดิการ</option>
                                <option value="เช่าอยู่">เช่าอยู่</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label for="address_building">ชื่อหมู่บ้าน/อาคาร</label>
                            <input type="text" id="address_building" name="address_building" placeholder="ชื่อหมู่บ้าน/อาคาร">
                        </div>

                        <div class="form-group col-3">
                            <label for="address_room">เลขที่ห้อง</label>
                            <input type="text" id="address_room" name="address_room" placeholder="เลขที่ห้อง">
                        </div>

                        <div class="form-group col-3">
                            <label for="address_floor">ชั้น</label>
                            <input type="text" id="address_floor" name="address_floor" placeholder="ชั้น">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_no">บ้านเลขที่</label>
                            <input type="text" id="address_no" name="address_no" placeholder="บ้านเลขที่">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_village">หมู่</label>
                            <input type="text" id="address_village" name="address_village" placeholder="หมู่">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_soi">ซอย</label>
                            <input type="text" id="address_soi" name="address_soi" placeholder="ซอย">
                        </div>

                        <div class="form-group col-6">
                            <label for="address_road">ถนน</label>
                            <input type="text" id="address_road" name="address_road" placeholder="ถนน">
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="address_subdistrict">แขวง/ตำบล</label>
                            <input type="text" id="address_subdistrict" name="address_subdistrict" placeholder="พิมพ์ค้นหา/เลือก แขวง/ตำบล" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="address_subdistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="address_district">เขต / อำเภอ</label>
                            <input type="text" id="address_district" name="address_district" placeholder="พิมพ์ค้นหา/เลือก เขต / อำเภอ" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="address_district_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="address_province">จังหวัด</label>
                            <input type="text" id="address_province" name="address_province" placeholder="พิมพ์ค้นหา/เลือก จังหวัด" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="address_province_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="address_postal">รหัสไปรษณีย์</label>
                            <input type="text" id="address_postal" name="address_postal" placeholder="พิมพ์ค้นหา/เลือก รหัสไปรษณีย์" autocomplete="off" inputmode="numeric">
                            <div class="address-search-dropdown hidden" id="address_postal_dropdown"></div>
                        </div>

                        <div class="form-group col-6">
                            <label for="phone_home">หมายเลขโทรศัพท์บ้าน</label>
                            <input type="text" id="phone_home" name="phone_home" placeholder="หมายเลขโทรศัพท์บ้าน">
                        </div>

                        <div class="form-group col-6">
                            <label for="phone_mobile">หมายเลขโทรศัพท์มือถือ</label>
                            <input type="text" id="phone_mobile" name="phone_mobile" placeholder="0XXXXXXXXX">
                        </div>

                        <div class="form-group col-6">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="example@email.com">
                        </div>

                        <div class="form-group col-12">
                            <label for="documentDelivery">ช่องทางการรับเอกสาร <span class="required-asterisk">*</span></label>
                            <select id="documentDelivery" name="documentDelivery" required>
                                <option value="">เลือกช่องทางการรับเอกสาร</option>
                                <option value="ประสงค์รับทางอีเมล">ประสงค์รับทางอีเมล</option>
                                <option value="ประสงค์รับทางไปรษณีย์">ประสงค์รับทางไปรษณีย์</option>
                                <option value="บริการแจ้งเตือนผ่าน SMS">บริการแจ้งเตือนผ่าน SMS</option>
                            </select>
                        </div>

                        <div class="form-group col-12">
                            <label for="documentAddressText">ที่อยู่ตามเอกสารสําคัญ (หากไม่เหมือนที่อยู่ปัจจุบันกรุณากรอก)</label>
                            <textarea id="documentAddressText" name="documentAddressText" rows="3" placeholder="กรอกที่อยู่ตามเอกสารสําคัญ"></textarea>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="documentAddressProvince">จังหวัด</label>
                            <input type="text" id="documentAddressProvince" name="documentAddressProvince" placeholder="พิมพ์ค้นหา/เลือก จังหวัด" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="documentAddressProvince_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="documentAddressPostal">รหัสไปรษณีย์</label>
                            <input type="text" id="documentAddressPostal" name="documentAddressPostal" placeholder="พิมพ์ค้นหา/เลือก รหัสไปรษณีย์" autocomplete="off" inputmode="numeric">
                            <div class="address-search-dropdown hidden" id="documentAddressPostal_dropdown"></div>
                        </div>

                        <div class="form-group col-12" id="birthPlaceAddressWrapper">
                            <label for="birthPlaceAddress">ที่อยู่บ้านเกิด (สำหรับชาวต่างชาติเท่านั้น)</label>
                            <textarea id="birthPlaceAddress" name="birthPlaceAddress" placeholder="กรุณากรอกที่อยู่บ้านเกิด"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 3: ข้อมูลอาชีพ/สถานที่ทำงาน -->
                <div class="step-container" data-step="3">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            <span class="form-section-title-icon">🏢</span> ข้อมูลอาชีพ/สถานที่ทำงาน
                            <div class="checkbox-group">
                                <input type="checkbox" id="useHomeAddress" name="useHomeAddress">
                                <label for="useHomeAddress">ใช้งานที่อยู่เดียวกันกับ ข้อมูลที่อยู่</label>
                            </div>
                        </div>

                        <div class="form-group col-6">
                            <label for="occupation">อาชีพ <span class="required-asterisk">*</span></label>
                            <select id="occupation" name="occupation" required>
                                <option value="">เลือกอาชีพ</option>
                                <option value="ข้าราชการ">ข้าราชการ</option>
                                <option value="พนักงานราชการ">พนักงานราชการ</option>
                                <option value="พนักงานรัฐวิสาหกิจ">พนักงานรัฐวิสาหกิจ</option>
                                <option value="พนักงานบริษัทเอกชน">พนักงานบริษัทเอกชน</option>
                                <option value="อาชีพอิสระ">อาชีพอิสระ</option>
                                <option value="เจ้าของกิจการที่จดทะเบียนพาณิชย์">เจ้าของกิจการที่จดทะเบียนพาณิชย์</option>
                                <option value="เจ้าของกิจการที่ไม่จดทะเบียนพาณิชย์">เจ้าของกิจการที่ไม่จดทะเบียนพาณิชย์</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>

                        <div class="form-group col-6 hidden" id="governmentLevelWrapper">
                            <label for="governmentLevel">ระดับ</label>
                            <input type="text" id="governmentLevel" name="governmentLevel" placeholder="ระดับข้าราชการ">
                        </div>

                        <div class="form-group col-6 hidden" id="occupationOtherWrapper">
                            <label for="occupationOther">ระบุ</label>
                            <input type="text" id="occupationOther" name="occupationOther" placeholder="ระบุอาชีพของคุณ">
                        </div>

                        <div class="form-group col-6">
                            <label for="careerField">สาขาอาชีพ <span class="required-asterisk">*</span></label>
                            <select id="careerField" name="careerField" required>
                                <option value="">เลือกสาขาอาชีพ</option>
                                <option value="ครู/อาจารย์">ครู/อาจารย์</option>
                                <option value="ตํารวจ/ทหาร">ตํารวจ/ทหาร</option>
                                <option value="แพทย์/ทันตแพทย์/สัตวแพยท์">แพทย์/ทันตแพทย์/สัตวแพยท์</option>
                                <option value="เภสัชกร">เภสัชกร</option>
                                <option value="พยาบาล">พยาบาล</option>
                                <option value="สถาปนิก">สถาปนิก</option>
                                <option value="วิศวกร">วิศวกร</option>
                                <option value="บัญชีการเงิน">บัญชีการเงิน</option>
                                <option value="พนักงานขาย">พนักงานขาย</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>

                        <div class="form-group col-6 hidden" id="careerFieldOtherWrapper">
                            <label for="careerFieldOther">ระบุ</label>
                            <input type="text" id="careerFieldOther" name="careerFieldOther" placeholder="ระบุสาขาอาชีพของคุณ">
                        </div>

                        <div class="form-group col-12">
                            <label for="companyName">ชื่อกิจการ/ที่ทำงาน</label>
                            <input type="text" id="companyName" name="companyName" placeholder="ชื่อกิจการ/ที่ทำงาน">
                        </div>

                        <div class="form-group col-6">
                            <label for="businessType">ประเภทธุรกิจ / Type of business <span class="required-asterisk">*</span></label>
                            <select id="businessType" name="businessType" required>
                                <option value="">เลือกประเภทธุรกิจ</option>
                                <option value="การศึกษา">การศึกษา</option>
                                <option value="รับเหมาก่อสร้าง">รับเหมาก่อสร้าง</option>
                                <option value="วัสดุก่อสร้าง / Construction materials">วัสดุก่อสร้าง / Construction materials</option>
                                <option value="บริการ">บริการ</option>
                                <option value="ฟอร์นิเจอร์/โรงเลื่อย">ฟอร์นิเจอร์/โรงเลื่อย</option>
                                <option value="สิ่งทอ">สิ่งทอ</option>
                                <option value="พลาสติก">พลาสติก</option>
                                <option value="เครื่องจักร/ผลิตภัณฑ์โลหะ">เครื่องจักร/ผลิตภัณฑ์โลหะ</option>
                                <option value="สาธารณูปโภค/ไฟฟ้า">สาธารณูปโภค/ไฟฟ้า</option>
                                <option value="ขนส่ง">ขนส่ง</option>
                                <option value="สาธารณูปโภค">สาธารณูปโภค</option>
                                <option value="ไฟฟ้า">ไฟฟ้า</option>
                                <option value="เวชภัณฑ์/โรงพยาบาล/คลินิก">เวชภัณฑ์/โรงพยาบาล/คลินิก</option>
                                <option value="อาหาร/เครื่องดื่ม">อาหาร/เครื่องดื่ม</option>
                                <option value="ร้านสะดวกซื้อ">ร้านสะดวกซื้อ</option>
                                <option value="โรงแรม/ร้านอาหาร">โรงแรม/ร้านอาหาร</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>

                        <div class="form-group col-6 hidden" id="businessTypeOtherWrapper">
                            <label for="businessTypeOther">โปรดระบุ</label>
                            <input type="text" id="businessTypeOther" name="businessTypeOther" placeholder="โปรดระบุประเภทธุรกิจ">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressBuilding">อาคาร</label>
                            <input type="text" id="workAddressBuilding" name="workAddressBuilding" placeholder="อาคาร">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressFloor">ชั้น</label>
                            <input type="text" id="workAddressFloor" name="workAddressFloor" placeholder="ชั้น">
                        </div>

                        <div class="form-group col-6">
                            <label for="workDepartment">แผนก/ฝ่าย</label>
                            <input type="text" id="workDepartment" name="workDepartment" placeholder="แผนก/ฝ่าย">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressNo">เลขที่</label>
                            <input type="text" id="workAddressNo" name="workAddressNo" placeholder="เลขที่">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressVillage">หมู่</label>
                            <input type="text" id="workAddressVillage" name="workAddressVillage" placeholder="หมู่">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressSoi">ซอย</label>
                            <input type="text" id="workAddressSoi" name="workAddressSoi" placeholder="ซอย">
                        </div>

                        <div class="form-group col-6">
                            <label for="workAddressRoad">ถนน</label>
                            <input type="text" id="workAddressRoad" name="workAddressRoad" placeholder="ถนน">
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="workAddressSubdistrict">แขวง/ตำบล</label>
                            <input type="text" id="workAddressSubdistrict" name="workAddressSubdistrict" placeholder="พิมพ์ค้นหา/เลือก แขวง/ตำบล" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="workAddressSubdistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="workAddressDistrict">เขต / อำเภอ</label>
                            <input type="text" id="workAddressDistrict" name="workAddressDistrict" placeholder="พิมพ์ค้นหา/เลือก เขต / อำเภอ" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="workAddressDistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="workAddressProvince">จังหวัด</label>
                            <input type="text" id="workAddressProvince" name="workAddressProvince" placeholder="พิมพ์ค้นหา/เลือก จังหวัด" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="workAddressProvince_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="workAddressPostal">รหัสไปรษณีย์</label>
                            <input type="text" id="workAddressPostal" name="workAddressPostal" placeholder="พิมพ์ค้นหา/เลือก รหัสไปรษณีย์" autocomplete="off" inputmode="numeric">
                            <div class="address-search-dropdown hidden" id="workAddressPostal_dropdown"></div>
                        </div>

                        <div class="form-group col-6">
                            <label for="workPhone">โทรศัพท์</label>
                            <input type="text" id="workPhone" name="workPhone" placeholder="โทรศัพท์">
                        </div>

                        <div class="form-group col-3">
                            <label for="workYears">อายุงานรวม (ปี)</label>
                            <input type="number" id="workYears" name="workYears" placeholder="ปี" min="0">
                        </div>

                        <div class="form-group col-3">
                            <label for="workMonths">อายุงานรวม (เดือน)</label>
                            <input type="number" id="workMonths" name="workMonths" placeholder="เดือน" min="0" max="11">
                        </div>

                        <div id="previousWorkSection" class="section4-container previous-work-section form-section-frame hidden">
                            <div class="form-group col-12 note-box">
                                <span>** หากอายุงานไม่ถึง 1 ปี (โปรดระบุที่ทำงานเดิม) <span class="required-asterisk">*</span></span>
                            </div>

                            <div class="form-group col-12">
                                <label for="previousCompanyName">ชื่อที่ทำงานเดิม</label>
                                <input type="text" id="previousCompanyName" name="previousCompanyName" placeholder="ชื่อที่ทำงานเดิม">
                            </div>

                            <div class="form-group col-6">
                                <label for="previousPosition">ตำแหน่ง</label>
                                <input type="text" id="previousPosition" name="previousPosition" placeholder="ตำแหน่ง">
                            </div>

                            <div class="form-group col-6">
                                <label for="previousIncome">รายได้ต่อเดือน</label>
                                <input type="number" id="previousIncome" name="previousIncome" placeholder="รายได้ต่อเดือน (บาท)" min="0">
                            </div>

                            <div class="form-group col-12">
                                <label for="previousWorkAddress">ที่อยู่ที่ทำงานเดิม</label>
                                <textarea id="previousWorkAddress" name="previousWorkAddress" rows="3" placeholder="ที่อยู่ที่ทำงานเดิม"></textarea>
                            </div>

                            <div class="form-group col-12">
                                <label for="previousPhone">หมายเลขโทรศัพท์</label>
                                <input type="text" id="previousPhone" name="previousPhone" placeholder="หมายเลขโทรศัพท์">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: รายได้ -->
                <div class="step-container" data-step="4">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            <span class="form-section-title-icon">💰</span> รายได้
                        </div>

                        <div class="form-group col-6">
                            <label for="income">รายได้รวมต่อเดือน <span class="required-asterisk">*</span></label>
                            <input type="number" id="income" name="income" min="0" placeholder="รายได้ต่อเดือน (บาท)" required>
                        </div>

                        <div class="form-group col-6">
                            <label for="extraIncome">รายได้อื่นๆ ต่อเดือน (ถ้ามี)</label>
                            <input type="number" id="extraIncome" name="extraIncome" min="0" placeholder="รายได้อื่นๆ ต่อเดือน (บาท)">
                        </div>

                        <div class="form-group col-6" id="extraIncomeSourceWrapper">
                            <label for="extraIncomeSource">แหล่งที่มาของรายได้ <span class="required-asterisk">*</span></label>
                            <select id="extraIncomeSource" name="extraIncomeSource" required>
                                <option value="">เลือกแหล่งที่มา</option>
                                <option value="รับจ้าง/เงินเดือน">รับจ้าง/เงินเดือน</option>
                                <option value="ค่าคอมมมิชั่น">ค่าคอมมมิชั่น</option>
                                <option value="โบนัส">โบนัส</option>
                                <option value="ธุรกิจส่วนตัว">ธุรกิจส่วนตัว</option>
                                <option value="อื่นๆ">อื่นๆ (โปรดระบุ)</option>
                            </select>
                        </div>

                        <div class="form-group col-6 hidden" id="extraIncomeSourceOtherWrapper">
                            <label for="extraIncomeSourceOther">โปรดระบุแหล่งที่มา</label>
                            <input type="text" id="extraIncomeSourceOther" name="extraIncomeSourceOther" placeholder="โปรดระบุแหล่งที่มา">
                        </div>

                        <div class="form-group col-6">
                            <label for="incomeCountry">ประเทศที่มาของรายได้</label>
                            <input type="text" id="incomeCountry" name="incomeCountry" placeholder="ประเทศที่มาของรายได้">
                        </div>

                        <div class="form-group col-12">
                            <label>แนบไฟล์หลักฐานการเงิน (PDF/JPG/PNG)</label>
                            <label for="incomeDocuments" class="custom-file-upload">
                                <i>📁</i>
                                <span>คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่</span>
                                <p>รองรับไฟล์ PDF, JPG, PNG (แนบได้หลายไฟล์)</p>
                            </label>
                            <input type="file" id="incomeDocuments" name="incomeDocuments[]" multiple accept=".pdf,.jpg,.jpeg,.png,image/*,application/pdf" class="file-input-hidden">
                        </div>

                        <div class="form-group col-12 hidden" id="incomeDocumentsSelectedWrapper">
                            <label class="uploaded-files-title">ไฟล์ที่เลือก</label>
                            <div id="incomeDocumentsSelectedList"></div>
                        </div>

                        <div class="form-group col-12 hidden" id="incomeDocumentsExistingWrapper">
                            <label class="uploaded-files-title">ไฟล์ที่แนบแล้ว</label>
                            <div id="incomeDocumentsExistingList"></div>
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

                        <div id="section4_container" class="section4-container form-section-frame">
                            <div class="form-group col-12">
                                <label>การชี้แจงการมีสินเชื่อบุคคล <span class="required-asterisk">*</span></label>
                                <span class="question-text">
                                    ปัจจุบันมีวงเงินสินเชื่อส่วนบุคคล และวงเงินที่อยู่ระหว่างขอยื่น/ขอเพิ่มตั้งแต่ 2 เดือนก่อนหน้าจนถึงปัจจุบัน จากสถาบันการเงิน/ผู้ประกอบธุรกิจสินเชื่อบุคคลที่ไม่ใช่สถาบันการเงินมากกว่า 2 แห่งหรือไม่
                                </span>
                            </div>

                            <div class="form-group col-12">
                                <select id="hasExistingLoan" name="hasExistingLoan" required>
                                    <option value="">เลือก</option>
                                    <option value="ใช่">ใช่</option>
                                    <option value="ไม่ใช่">ไม่ใช่</option>
                                </select>
                            </div>

                            <div class="form-group col-6 hidden" id="existingLoanInstitutionCountWrapper">
                                <label for="existingLoanInstitutionCount">จำนวนแห่ง</label>
                                <input type="number" id="existingLoanInstitutionCount" name="existingLoanInstitutionCount" min="0" placeholder="จำนวนแห่ง">
                            </div>

                            <div class="form-group col-6 hidden" id="existingLoanTotalAmountWrapper">
                                <label for="existingLoanTotalAmount">รวมทั้งสิ้น (บาท)</label>
                                <input type="number" id="existingLoanTotalAmount" name="existingLoanTotalAmount" min="0" placeholder="รวมทั้งสิ้น">
                            </div>

                            <div class="form-group col-12 note-box">
                                <span>( หมายเหตุ กรณีกรอกข้อมูลไม่ถูกต้องไม่ครบถ้วน และ/หรือมีรายได้หรือกระเเสเงินสดหมุนเวียนเข้าในบัญชีเงินฝากสถาบันการเงินโดยเฉลี่ยน้อยกว่า 30,000 บาทต่อเดือน โดยมีวงเงินสินเชื่อส่วนบุคคลรวมตั้งแต่ 3 แห่งขึ้นไป บริษัทมีสิทธิปฏิเสธการให้สินเชื่อ หรือกรณีที่ทําสัญญาเงินกู้ ให้ถือว่าบริษัทมีสิทธิลดหรือยกเลิกวงเงินได้ทันที )</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: ข้อมูลบุคคลอ้างอิง -->
                <div class="step-container" data-step="5">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            <span class="form-section-title-icon">👥</span> ข้อมูลบุคคลอ้างอิง
                        </div>

                        <div class="form-group col-12">
                            <label for="refName">ชื่อ - นามสกุล <span class="required-asterisk">*</span></label>
                            <input type="text" id="refName" name="refName" placeholder="ชื่อ - นามสกุล" required>
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

                        <div class="form-group col-6 address-search-field">
                            <label for="refAddressSubdistrict">แขวง/ตำบล</label>
                            <input type="text" id="refAddressSubdistrict" name="refAddressSubdistrict" placeholder="พิมพ์ค้นหา/เลือก แขวง/ตำบล" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="refAddressSubdistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="refAddressDistrict">เขต/อำเภอ</label>
                            <input type="text" id="refAddressDistrict" name="refAddressDistrict" placeholder="พิมพ์ค้นหา/เลือก เขต/อำเภอ" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="refAddressDistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="refAddressProvince">จังหวัด</label>
                            <input type="text" id="refAddressProvince" name="refAddressProvince" placeholder="พิมพ์ค้นหา/เลือก จังหวัด" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="refAddressProvince_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="refAddressPostal">รหัสไปรษณีย์</label>
                            <input type="text" id="refAddressPostal" name="refAddressPostal" placeholder="พิมพ์ค้นหา/เลือก รหัสไปรษณีย์" autocomplete="off" inputmode="numeric">
                            <div class="address-search-dropdown hidden" id="refAddressPostal_dropdown"></div>
                        </div>

                        <div class="form-group col-6">
                            <label for="refPhoneHome">หมายเลขโทรศัพท์บ้าน</label>
                            <input type="text" id="refPhoneHome" name="refPhoneHome" placeholder="หมายเลขโทรศัพท์บ้าน">
                        </div>

                        <div class="form-group col-6">
                            <label for="refPhoneMobile">หมายเลขโทรศัพท์มือถือ</label>
                            <input type="text" id="refPhoneMobile" name="refPhoneMobile" placeholder="หมายเลขโทรศัพท์มือถือ">
                        </div>

                        <div class="form-group col-12 note-box">
                            <span>( ข้าพเจ้าได้รับอนุญาตจากเจ้าของชือแล้ว และตกลงยินยอมให้บริษัทสามารถติดต่อกับบุคคลอ้างอิงดังกล่าวเพือการทวงถามหนีของข้าพเจ้าได้ )</span>
                        </div>
                    </div>
                </div>

                <!-- Step 6: ความประสงค์ในการสมัครใช้สินเชื่อ -->
                <div class="step-container" data-step="6">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            <span class="form-section-title-icon">💰</span> ความประสงค์ในการสมัครใช้สินเชื่อ
                        </div>

                        <div class="form-group col-12">
                            <label for="loanPurpose">วัตถุประสงค์ในการขอสินเชื่อ <span class="required-asterisk">*</span></label>
                            <input type="text" id="loanPurpose" name="loanPurpose" placeholder="วัตถุประสงค์ในการขอสินเชื่อ" required>
                        </div>

                        <div class="form-group col-12">
                            <label for="loanTerm">ระยะเวลาผ่อนชำระคืน <span class="required-asterisk">*</span></label>
                            <select id="loanTerm" name="loanTerm" required>
                                <option value="">เลือกระยะเวลา</option>
                                <option value="4">4 เดือน</option>
                                <option value="6">6 เดือน</option>
                                <option value="12">12 เดือน</option>
                                <option value="18">18 เดือน</option>
                                <option value="24">24 เดือน</option>
                                <option value="36">36 เดือน</option>
                                <option value="48">48 เดือน</option>
                                <option value="60">60 เดือน</option>
                            </select>
                        </div>

                        <div class="form-group col-12">
                            <label for="loanAmountType">วงเงินสินเชื่อที่ต้องการ <span class="required-asterisk">*</span></label>
                            <select id="loanAmountType" name="loanAmountType" required>
                                <option value="">เลือกวงเงิน</option>
                                <option value="full">เต็มจำนวนตามที่บริษัทอนุมัติ</option>
                                <option value="custom">วงเงินที่ขอกู้/จำนวนทั้งสิ้น</option>
                            </select>
                        </div>

                        <div class="form-group col-12 hidden" id="customLoanAmountWrapper">
                            <label for="customLoanAmount">ระบุจำนวนเงินที่ขอกู้ (บาท)</label>
                            <input type="number" id="customLoanAmount" name="customLoanAmount" placeholder="ระบุจำนวนเงิน" min="0">
                        </div>

                        <div class="form-group col-12 note-box">
                            <span>(กรณีที่บริษัทไม่สามารถอนุมัติเงินตามที่ท่านเลือกได้ บริษัทจะอนุมัติวงเงินให้ท่านตามความเหมาะสม)</span>
                        </div>

                        <div class="form-section-title">
                            <span class="form-section-title-icon">🏦</span> ความประสงค์ขอรับวงเงินกู้ครั้งแรกเข้าบัญชีเงินฝาก
                        </div>

                        <div class="form-group col-12">
                            <p class="mb-1 text-muted">
                                ในกรณที่บริษัทอนุมัติสินเชื่อ ข้าพเจ้ามีความประสงค์ให้บริษัทโอนเงินกู้เข้าบัญชีของข้าพเจ้า โดยโอนเข้าบัญชีเงินฝากเลขที่ (กรอกข้อมูล)
                            </p>
                        </div>

                        <div class="form-group col-6">
                            <label for="accountNumber">เลขที่บัญชี <span class="required-asterisk">*</span></label>
                            <input type="text" id="accountNumber" name="accountNumber" placeholder="เลขที่บัญชี" required>
                        </div>

                        <div class="form-group col-6">
                            <label for="accountType">ประเภทบัญชี <span class="required-asterisk">*</span></label>
                            <input type="text" id="accountType" name="accountType" placeholder="ประเภทบัญชี (เช่น ออมทรัพย์, กระแสรายวัน)" required>
                        </div>

                        <div class="form-group col-6">
                            <label for="bankName">ธนาคาร <span class="required-asterisk">*</span></label>
                            <input type="text" id="bankName" name="bankName" placeholder="ชื่อธนาคาร" required>
                        </div>

                        <div class="form-group col-6">
                            <label for="accountName">ชื่อบัญชี <span class="required-asterisk">*</span></label>
                            <input type="text" id="accountName" name="accountName" placeholder="ชื่อบัญชี" required>
                        </div>

                        <div class="form-section-title">
                            <span class="form-section-title-icon">💳</span> วิธีการชําระเงิน
                        </div>

                        <div class="form-group col-12">
                            <label for="paymentMethod">วิธีการชําระเงิน <span class="required-asterisk">*</span></label>
                            <select id="paymentMethod" name="paymentMethod" required>
                                <option value="">เลือกวิธีการชําระเงิน</option>
                                <option value="ชําระด้วยเงินสด">ชําระด้วยเงินสด</option>
                                <option value="ชําระโดยการหักบัญชี">ชําระโดยการหักบัญชี ( เฉพาะบัญชีเงินฝากของท่านที่มีอยู่กับสถาบันการเงินเท่านั้น )</option>
                            </select>
                        </div>

                        <div id="directDebitWrapper" class="hidden display-contents">
                            <div class="form-group col-12 note-box text-left line-height-relaxed mb-1">
                                <span>
                                    กรณียินยอมหักบัญชี ข้าพเจ้ายินยอมให้สถาบันการเงินหักเงินจากบัญชีเงินเดือนของข้าพเจ้าที่มีอยู่กับสถาบันการเงิน เป็นจํานวนเงิน <strong id="display_directDebitAmount">.....................................</strong> บาท/เดือน จากบัญชีเลขที่ <strong id="display_directDebitAccountNumber">.........................................</strong> เท่านั้น ณ วันครบกําหนดชําระตามที่บริษัทแจ้งให้ทราบ หรือทุกวันที่เงินเดือนออกในแต่ละเดือนแล้วแต่วันใดจะถึงก่อน เพื่อชําระเงินกู้รวมทั้งดอกเบี้ยจนกว่าจะชําระหนี้ให้แก่บริษัทจนเสร็จสิ้น หากบริษัทไม่สามารถหักเงินจากบัญชีดังกล่าวในวันดังกล่าวได้ ข้าพเจ้าตกลงยอมรับให้บริษัทถือว่าเป็นการผิดนัดชําระหนี้และขอรับรองว่าการที่บริษัทหักเงินจากบัญชีของข้าพเจ้าตามใบสมัครฉบับนี้เป็นไปตามคําร้องขอของข้าพเจ้า หากมีความเสียหายหรือผิดพลาดใดๆ เกิดขึ้นแก่บริษัท ข้าพเจ้าตกลงชดใช้ค่าเสียหายให้แก่บริษัททั้งจํานวนทันที
                                </span>
                            </div>
                            
                            <div class="form-group col-6">
                                <label for="directDebitAmount">จำนวนเงินหักบัญชีต่อเดือน (บาท)</label>
                                <input type="number" id="directDebitAmount" name="directDebitAmount" min="0" placeholder="จำนวนเงิน">
                            </div>

                            <div class="form-group col-6">
                                <label for="directDebitAccountNumber">จากบัญชีเลขที่</label>
                                <input type="text" id="directDebitAccountNumber" name="directDebitAccountNumber" placeholder="เลขที่บัญชีสำหรับหักเงิน">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 7: ข้อความยินยอม -->
                <div class="step-container" data-step="7">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            <span class="form-section-title-icon">📝</span> ข้อความยินยอม
                        </div>

                        <div class="form-group col-12">
                            <p class="text-left line-height-extra mb-1-5 text-indent-large">
                                ข้าพเจ้าขอรับรองว่าข้อความข้างต้นเป็นความจริงทุกประการ รวมทั้งได้รับทราบเงื่อนไขและหลักเกณฑ์ที่กำหนดในการใช้บริการสินเชื่อ โดยลงนามในใบสมัครสินเชื่อส่วนบุคคล (Personal Loan) นี้ และเมื่อบริษัทอนุมัติสินเชื่อดังกล่าวให้ข้าพเจ้าแล้ว ข้าพเจ้าตกลงปฏิบัติตามภาระผูกพันที่เกิดขึ้นตามสัญญาสินเชื่อส่วนบุคคล (Personal Loan) ที่ปรากฏอยู่ในใบสมัครนี้ รวมทั้งข้อกำหนด/เงื่อนไขในการใช้สินเชื่อภายใต้ชื่อสินเชื่อส่วนบุคคล (Personal Loan) ของบริษัท และให้ถือว่าใบสมัครสินเชื่อส่วนบุคคล (Personal Loan) นี้ เป็นส่วนหนึ่งของสัญญาสินเชื่อส่วนบุคคล (Personal Loan) ด้วย ข้าพเจ้าได้อ่านและเข้าใจข้อกำหนดและเงื่อนไขต่างๆ ที่เกี่ยวข้องถี่ถ้วนแล้ว พร้อมทั้งได้รับสำเนาสัญญาสินเชื่อส่วนบุคคล (Personal Loan) ไว้เรียบร้อยแล้ว จึงลงลายมือชื่อไว้เป็นหลักฐาน</p>
                        </div>

                        <div class="form-group col-12">
                            <div class="warning-box">
                                <h4 class="warning-title">ข้อควรระวัง</h4>
                                <ul class="warning-list">
                                    <li class="warning-item">บริษัทจะคิดดอกเบี้ยตั้งแต่วันที่ผู้ขอกู้ได้รับเงินกู้ กรณีผิดนัดชำระหรือชำระต่ำกว่ายอดชำระขั้นต่ำจะมีดอกเบี้ยและค่าใช้จ่ายในการติดตามทวงถามหนี้เพิ่ม</li>
                                    <li class="warning-item">โปรดทำความเข้าใจผลิตภัณฑ์และเงื่อนไขก่อนลงนาม หากมีข้อสงสัยหรือต้องการสอบถามข้อมูลเพิ่มเติม สามารถติดต่อได้ที่โทรศัพท์ 082-257-7997</li>
                                    <li>บริษัทอาจมอบหมายให้ผู้ที่รับมอบหมายจำเป็นที่ต้องดำเนินการทางกฎหมาย หากท่านผิดนัดชำระ หรือไม่ชำระค่างวดอย่างสม่ำเสมอ</li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group col-12 signature-group">
                            <label>ลงชื่อผู้ขอสินเชื่อ <span class="required-asterisk">*</span></label>
                            <div class="signature-pad-box">
                                <canvas id="signaturePad" class="signature-canvas"></canvas>
                                <button type="button" id="clearSignatureBtn" class="signature-clear-btn">ล้าง</button>
                            </div>
                            <input type="hidden" id="signatureData" name="signatureData">
                            <p class="signature-date">วันที่เซ็น: {{ date('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="wizard-actions">
                    <button type="button" class="action-btn outline hidden" id="prevStepBtn">ย้อนกลับ</button>
                    <button type="button" class="action-btn" id="nextStepBtn">ถัดไป</button>
                    <button type="submit" class="action-btn hidden" id="consentSubmitBtn">บันทึกข้อมูลสำเร็จ</button>
                </div>
            </div>
        </form>
    </div>
</div>
