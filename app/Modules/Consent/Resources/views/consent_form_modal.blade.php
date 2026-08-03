<!-- Modal สำหรับสร้างใบยินยอม -->
<div id="consentModal" class="modal" data-next-app-no="{{ $nextAppNo }}">
    <div class="modal-content modal-lg">
        
        <form method="POST" action="#" class="consent-form-wrapper" id="consentForm" autocomplete="off" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="consent_id" id="consent_id" value="">
            
            <div class="modal-header">
                <h3 id="consentModalTitle">{{ __('consent::messages.modal.form.create_title') }}</h3>
                <button type="button" class="close-btn" id="closeConsentModal" aria-label="Close modal">&times;</button>
            </div>
            
            <div class="modal-body scrollable">
                <!-- Step Indicator -->
                <div class="wizard-steps" id="wizardSteps">
                    <div class="wizard-step active" data-step="1">
                        <div class="wizard-step-icon">1</div>
                        <div class="wizard-step-label">{{ __('consent::messages.modal.form.steps.personal') }}</div>
                    </div>
                    <div class="wizard-step" data-step="2">
                        <div class="wizard-step-icon">2</div>
                        <div class="wizard-step-label">{{ __('consent::messages.modal.form.steps.address') }}</div>
                    </div>
                    <div class="wizard-step" data-step="3">
                        <div class="wizard-step-icon">3</div>
                        <div class="wizard-step-label">{{ __('consent::messages.modal.form.steps.occupation') }}</div>
                    </div>
                    <div class="wizard-step" data-step="4">
                        <div class="wizard-step-icon">4</div>
                        <div class="wizard-step-label">{{ __('consent::messages.modal.form.steps.income') }}</div>
                    </div>
                    <div class="wizard-step" data-step="5">
                        <div class="wizard-step-icon">5</div>
                        <div class="wizard-step-label">{{ __('consent::messages.modal.form.steps.reference') }}</div>
                    </div>
                    <div class="wizard-step" data-step="6">
                        <div class="wizard-step-icon">6</div>
                        <div class="wizard-step-label">{{ __('consent::messages.modal.form.steps.loan') }}</div>
                    </div>
                    <div class="wizard-step" data-step="7">
                        <div class="wizard-step-icon">7</div>
                        <div class="wizard-step-label">{{ __('consent::messages.modal.form.steps.consent') }}</div>
                    </div>
                    <div class="wizard-step" data-step="8">
                        <div class="wizard-step-icon">8</div>
                        <div class="wizard-step-label">{{ __('consent::messages.modal.form.steps.attachments') }}</div>
                    </div>
                </div>

                <!-- Step 1: Application + Officer + Personal Info -->
                <div class="step-container active" data-step="1">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin with-app-no">
                            <div>{{ __('consent::messages.modal.form.step1.sections.application_and_officer') }}</div>
                        </div>

                        <div class="form-group col-6">
                            <label for="app_date">{{ __('consent::messages.modal.form.step1.fields.app_date') }}</label>
                            <input type="date" id="app_date" name="app_date" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="form-group col-6">
                            <label for="app_no">{{ __('consent::messages.modal.form.step1.fields.app_no') }}</label>
                            <input type="text" id="app_no" name="app_no" value="{{ $nextAppNo }}" maxlength="13" readonly class="input-readonly">
                        </div>

                        <div class="form-group col-3">
                            <label for="officer_name">{{ __('consent::messages.modal.form.step1.fields.officer_name') }} <span class="required-asterisk">*</span></label>
                            <input type="text" id="officer_name" name="officer_name" placeholder="{{ __('consent::messages.modal.form.step1.placeholders.officer_name') }}" required>
                        </div>

                        <div class="form-group col-3">
                            <label for="officer_phone">{{ __('consent::messages.modal.form.common.phone_number') }}</label>
                            <input type="tel" id="officer_phone" name="officer_phone" placeholder="{{ __('consent::messages.modal.form.common.phone_number') }}" maxlength="10" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" oninput="this.value = this.value.replace(/\D/g,'')">
                        </div>

                        <div class="form-group col-3">
                            <label for="officer_group_id">{{ __('consent::messages.modal.form.step1.fields.officer_group') }} <span class="required-asterisk">*</span></label>
                            <select id="officer_group_id" name="officer_group_id" required>
                                <option value="">{{ __('consent::messages.modal.form.step1.options.officer_group_prompt') }}</option>
                                @foreach($officerGroups as $group)
                                    <option value="{{ $group->id }}">{{ app()->getLocale() === 'th' ? $group->name_th : $group->name_en }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-3">
                            <label for="loan_product_id">{{ __('consent::messages.modal.form.step1.fields.product_type') }} <span class="required-asterisk">*</span></label>
                            <select id="loan_product_id" name="loan_product_id" required>
                                <option value="">{{ __('consent::messages.modal.form.step1.options.product_type_prompt') }}</option>
                                @foreach($loanProducts as $product)
                                    <option value="{{ $product->id }}">{{ app()->getLocale() === 'th' ? $product->name_th : $product->name_en }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-section-title">
                            {{ __('consent::messages.modal.form.step1.sections.personal_info') }}
                        </div>

                        <div class="form-group col-3 applicant-photo-group">
                            <label for="applicantPhoto">{{ __('consent::messages.modal.form.attachment.applicant_photo.label') }}</label>
                            <div class="applicant-photo-upload" id="applicantPhotoWidget" data-default-src="data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'%3E%3Crect width='160' height='160' rx='80' fill='%23ffffff'/%3E%3Ccircle cx='80' cy='58' r='36' fill='none' stroke='%23000000' stroke-width='8'/%3E%3Cpath d='M42 138c4-22 20-34 38-34h0c18 0 34 12 38 34' fill='none' stroke='%23000000' stroke-width='8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
                                <div class="applicant-photo-preview">
                                    <img
                                        id="applicantPhotoAvatarImg"
                                        src="data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'%3E%3Crect width='160' height='160' rx='80' fill='%23ffffff'/%3E%3Ccircle cx='80' cy='58' r='36' fill='none' stroke='%23000000' stroke-width='8'/%3E%3Cpath d='M42 138c4-22 20-34 38-34h0c18 0 34 12 38 34' fill='none' stroke='%23000000' stroke-width='8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"
                                        data-placeholder="data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'%3E%3Crect width='160' height='160' rx='80' fill='%23ffffff'/%3E%3Ccircle cx='80' cy='58' r='36' fill='none' stroke='%23000000' stroke-width='8'/%3E%3Cpath d='M42 138c4-22 20-34 38-34h0c18 0 34 12 38 34' fill='none' stroke='%23000000' stroke-width='8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"
                                        alt="avatar"
                                    >
                                    <button type="button" class="applicant-photo-action applicant-photo-edit" id="applicantPhotoEditBtn" title="{{ __('consent::messages.modal.form.attachment.applicant_photo.change') }}">✎</button>
                                    <button type="button" class="applicant-photo-action applicant-photo-remove hidden" id="applicantPhotoRemoveBtn" title="{{ __('consent::messages.modal.form.attachment.applicant_photo.remove') }}">×</button>
                                </div>
                                <div class="applicant-photo-hint">{{ __('consent::messages.modal.form.attachment.applicant_photo.hint_full') }}</div>
                                <input type="file" id="applicantPhoto" name="applicantPhoto" accept="image/jpeg,image/png,.jpg,.jpeg,.png" class="hidden">
                                <div class="hidden" id="applicantPhotoSelectedWrapper"></div>
                                <div class="hidden" id="applicantPhotoExistingWrapper"></div>
                            </div>
                        </div>

                        <div class="form-group col-9">
                            <div class="form-grid">
                                <div class="form-group col-3" id="titleGroup">
                                    <label for="title">{{ __('consent::messages.modal.form.step1.fields.title') }} <span class="required-asterisk">*</span></label>
                                    <select id="title" name="title" required>
                                        <option value="">{{ __('consent::messages.modal.form.step1.placeholders.title_prompt') }}</option>
                                        <option value="นาย">{{ __('consent::messages.modal.form.step1.options.title_mr') }}</option>
                                        <option value="นาง">{{ __('consent::messages.modal.form.step1.options.title_mrs') }}</option>
                                        <option value="นางสาว">{{ __('consent::messages.modal.form.step1.options.title_ms') }}</option>
                                        <option value="อื่นๆ">{{ __('consent::messages.modal.form.step1.options.title_other') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-3 hidden" id="title_other_wrapper">
                                    <label for="title_other">{{ __('consent::messages.modal.form.step1.fields.title_other') }}</label>
                                    <input type="text" id="title_other" name="title_other" placeholder="{{ __('consent::messages.modal.form.step1.placeholders.title_other') }}">
                                </div>

                                <div class="form-group col-9" id="nameGroup">
                                    <label for="name">{{ __('consent::messages.modal.form.step1.fields.name_th') }}</label>
                                    <input type="text" id="name" name="name" placeholder="{{ __('consent::messages.modal.form.step1.placeholders.name_th') }}" >
                                </div>

                                <div class="form-group col-8">
                                    <label for="name_en">{{ __('consent::messages.modal.form.step1.fields.name_en') }}</label>
                                    <input type="text" id="name_en" name="name_en" placeholder="{{ __('consent::messages.modal.form.step1.placeholders.name_en') }}">
                                </div>

                                <div class="form-group col-4">
                                    <label for="birthdate">{{ __('consent::messages.modal.form.step1.fields.birthdate') }} <span class="required-asterisk">*</span></label>
                                    <input type="date" id="birthdate" name="birthdate" required>
                                </div>
                            </div>
                        </div>


                        <div class="form-group col-4">
                            <label for="nationality">{{ __('consent::messages.modal.form.step1.fields.nationality') }} <span class="required-asterisk">*</span></label>
                            <input type="text" id="nationality" name="nationality" placeholder="{{ __('consent::messages.modal.form.step1.placeholders.nationality') }}" required>
                        </div>

                         <div class="form-group col-4">
                            <label for="education">{{ __('consent::messages.modal.form.step1.fields.education') }} <span class="required-asterisk">*</span></label>
                            <select id="education" name="education" required>
                                <option value="">{{ __('consent::messages.modal.form.step1.options.education_prompt') }}</option>
                                <option value="มัธยมต้น">{{ __('consent::messages.modal.form.step1.options.education_lower_secondary') }}</option>
                                <option value="มัธยมปลาย">{{ __('consent::messages.modal.form.step1.options.education_upper_secondary') }}</option>
                                <option value="อุดมศึกษา">{{ __('consent::messages.modal.form.step1.options.education_higher') }}</option>
                                <option value="ปริญญาตรี">{{ __('consent::messages.modal.form.step1.options.education_bachelor') }}</option>
                                <option value="ปริญญาโท">{{ __('consent::messages.modal.form.step1.options.education_master') }}</option>
                                <option value="ปริญญาเอก">{{ __('consent::messages.modal.form.step1.options.education_doctorate') }}</option>
                                <option value="อื่นๆ">{{ __('consent::messages.modal.form.common.other') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-4 hidden" id="educationOtherWrapper">
                            <label for="educationOther">{{ __('consent::messages.modal.form.step1.fields.education_other') }}</label>
                            <input type="text" id="educationOther" name="educationOther" placeholder="{{ __('consent::messages.modal.form.step1.placeholders.education_other') }}">
                        </div>

                        <div class="form-group col-4">
                            <label for="marital_status">{{ __('consent::messages.modal.form.step1.fields.marital_status') }} <span class="required-asterisk">*</span></label>
                            <select id="marital_status" name="marital_status" required>
                                <option value="">{{ __('consent::messages.modal.form.step1.options.marital_prompt') }}</option>
                                <option value="โสด">{{ __('consent::messages.modal.form.step1.options.marital_single') }}</option>
                                <option value="สมรส">{{ __('consent::messages.modal.form.step1.options.marital_married') }}</option>
                                <option value="สมรสไม่จดทะเบียน">{{ __('consent::messages.modal.form.step1.options.marital_common_law') }}</option>
                                <option value="หย่า">{{ __('consent::messages.modal.form.step1.options.marital_divorced') }}</option>
                                <option value="หม้าย">{{ __('consent::messages.modal.form.step1.options.marital_widowed') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-3">
                            <label for="id_type">{{ __('consent::messages.modal.form.step1.fields.id_type') }} <span class="required-asterisk">*</span></label>
                            <select id="id_type" name="id_type" required>
                                <option value="id_card">{{ __('consent::messages.modal.form.step1.options.id_card') }}</option>
                                <option value="passport">{{ __('consent::messages.modal.form.step1.options.passport') }}</option>
                            </select>
                        </div>
                        
                        <div class="form-group col-9" id="idNumberGroup">
                            <label for="id_card" id="idNumberLabel">{{ __('consent::messages.modal.form.step1.fields.id_card_number') }} <span class="required-asterisk">*</span></label>
                            <input type="text" id="id_card" name="id_card" placeholder="{{ __('consent::messages.modal.form.step1.placeholders.id_card_number') }}" maxlength="13" required>
                        </div>

                       
                    </div>
                </div>

                <!-- Step 2: Current Address -->
                <div class="step-container" data-step="2">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            {{ __('consent::messages.modal.form.step2.sections.current_address') }}
                        </div>

                        <div class="form-group col-12">
                            <label for="residence_status">{{ __('consent::messages.modal.form.step2.fields.residence_status') }} <span class="required-asterisk">*</span></label>
                            <select id="residence_status" name="residence_status" required>
                                <option value="">{{ __('consent::messages.modal.form.step2.options.residence_prompt') }}</option>
                                <option value="บ้านตนเองปลอดภาระ">{{ __('consent::messages.modal.form.step2.options.residence_own_paid') }}</option>
                                <option value="บ้านตนเองกำลังผ่อน">{{ __('consent::messages.modal.form.step2.options.residence_own_installment') }}</option>
                                <option value="บ้านบิดา/มารดา">{{ __('consent::messages.modal.form.step2.options.residence_parents') }}</option>
                                <option value="บ้านญาติ/พี่น้อง/บุคคลอื่น">{{ __('consent::messages.modal.form.step2.options.residence_relatives') }}</option>
                                <option value="สวัสดิการ">{{ __('consent::messages.modal.form.step2.options.residence_welfare') }}</option>
                                <option value="เช่าอยู่">{{ __('consent::messages.modal.form.step2.options.residence_rent') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label for="address_building">{{ __('consent::messages.modal.form.step2.fields.address_building') }}</label>
                            <input type="text" id="address_building" name="address_building" placeholder="{{ __('consent::messages.modal.form.step2.placeholders.address_building') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="address_room">{{ __('consent::messages.modal.form.step2.fields.address_room') }}</label>
                            <input type="text" id="address_room" name="address_room" placeholder="{{ __('consent::messages.modal.form.step2.placeholders.address_room') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="address_floor">{{ __('consent::messages.modal.form.common.address_floor') }}</label>
                            <input type="text" id="address_floor" name="address_floor" placeholder="{{ __('consent::messages.modal.form.common.address_floor') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="address_no">{{ __('consent::messages.modal.form.common.no') }}</label>
                            <input type="text" id="address_no" name="address_no" placeholder="{{ __('consent::messages.modal.form.common.no') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="address_village">{{ __('consent::messages.modal.form.common.address_village') }}</label>
                            <input type="text" id="address_village" name="address_village" placeholder="{{ __('consent::messages.modal.form.common.address_village') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="address_soi">{{ __('consent::messages.modal.form.common.address_soi') }}</label>
                            <input type="text" id="address_soi" name="address_soi" placeholder="{{ __('consent::messages.modal.form.common.address_soi') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="address_road">{{ __('consent::messages.modal.form.common.address_road') }}</label>
                            <input type="text" id="address_road" name="address_road" placeholder="{{ __('consent::messages.modal.form.common.address_road') }}">
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="address_subdistrict">{{ __('consent::messages.modal.form.common.subdistrict') }}</label>
                            <input type="text" id="address_subdistrict" name="address_subdistrict" placeholder="{{ __('consent::messages.modal.form.common.search_subdistrict') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="address_subdistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="address_district">{{ __('consent::messages.modal.form.common.address_district') }}</label>
                            <input type="text" id="address_district" name="address_district" placeholder="{{ __('consent::messages.modal.form.common.search_district') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="address_district_dropdown"></div>
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="address_province">{{ __('consent::messages.modal.form.common.province') }}</label>
                            <input type="text" id="address_province" name="address_province" placeholder="{{ __('consent::messages.modal.form.common.search_province') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="address_province_dropdown"></div>
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="address_postal">{{ __('consent::messages.modal.form.common.postal') }}</label>
                            <input type="text" id="address_postal" name="address_postal" placeholder="{{ __('consent::messages.modal.form.common.search_postal') }}" autocomplete="off" inputmode="numeric">
                            <div class="address-search-dropdown hidden" id="address_postal_dropdown"></div>
                        </div>

                        <div class="form-group col-4">
                            <label for="phone_home">{{ __('consent::messages.modal.form.common.phone_home') }}</label>
                            <input type="tel" id="phone_home" name="phone_home" placeholder="{{ __('consent::messages.modal.form.common.phone_home') }}" maxlength="10"  inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/\D/g,'')">
                        </div>

                        <div class="form-group col-4">
                            <label for="phone_mobile">{{ __('consent::messages.modal.form.common.phone_number') }}</label>
                            <input type="tel" id="phone_mobile" name="phone_mobile" placeholder="0XXXXXXXXX" maxlength="10" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/\D/g,'')">
                        </div>

                        <div class="form-group col-4">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="example@email.com">
                        </div>

                        <div class="form-group col-12">
                            <label for="documentDelivery">{{ __('consent::messages.modal.form.step2.fields.document_delivery') }} <span class="required-asterisk">*</span></label>
                            <select id="documentDelivery" name="documentDelivery" required>
                                <option value="">{{ __('consent::messages.modal.form.step2.options.document_delivery_prompt') }}</option>
                                <option value="ประสงค์รับทางอีเมล">{{ __('consent::messages.modal.form.step2.options.document_delivery_email') }}</option>
                                <option value="ประสงค์รับทางไปรษณีย์">{{ __('consent::messages.modal.form.step2.options.document_delivery_post') }}</option>
                                <option value="บริการแจ้งเตือนผ่าน SMS">{{ __('consent::messages.modal.form.step2.options.document_delivery_sms') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-12">
                            <label for="documentAddressText">{{ __('consent::messages.modal.form.step2.fields.document_address_text') }}</label>
                            <textarea id="documentAddressText" name="documentAddressText" rows="3" placeholder="{{ __('consent::messages.modal.form.step2.placeholders.document_address_text') }}"></textarea>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="documentAddressProvince">{{ __('consent::messages.modal.form.common.province') }}</label>
                            <input type="text" id="documentAddressProvince" name="documentAddressProvince" placeholder="{{ __('consent::messages.modal.form.common.search_province') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="documentAddressProvince_dropdown"></div>
                        </div>

                        <div class="form-group col-6 address-search-field">
                            <label for="documentAddressPostal">{{ __('consent::messages.modal.form.common.postal') }}</label>
                            <input type="text" id="documentAddressPostal" name="documentAddressPostal" placeholder="{{ __('consent::messages.modal.form.common.search_postal') }}" autocomplete="off" inputmode="numeric">
                            <div class="address-search-dropdown hidden" id="documentAddressPostal_dropdown"></div>
                        </div>

                        <div class="form-group col-12" id="birthPlaceAddressWrapper">
                            <label for="birthPlaceAddress">{{ __('consent::messages.modal.form.step2.fields.birth_place_address') }}</label>
                            <textarea id="birthPlaceAddress" name="birthPlaceAddress" placeholder="{{ __('consent::messages.modal.form.step2.placeholders.birth_place_address') }}"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Occupation / Workplace -->
                <div class="step-container" data-step="3">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            {{ __('consent::messages.modal.form.step3.sections.occupation_workplace') }}
                            <div class="checkbox-group">
                                <input type="checkbox" id="useHomeAddress" name="useHomeAddress">
                                <label for="useHomeAddress">{{ __('consent::messages.modal.form.step3.fields.use_home_address') }}</label>
                            </div>
                        </div>

                        <div class="form-group col-6">
                            <label for="occupation">{{ __('consent::messages.modal.form.step3.fields.occupation') }} <span class="required-asterisk">*</span></label>
                            <select id="occupation" name="occupation" required>
                                <option value="">{{ __('consent::messages.modal.form.step3.options.occupation_prompt') }}</option>
                                <option value="ข้าราชการ">{{ __('consent::messages.modal.form.step3.options.occupation_civil_servant') }}</option>
                                <option value="พนักงานราชการ">{{ __('consent::messages.modal.form.step3.options.occupation_gov_employee') }}</option>
                                <option value="พนักงานรัฐวิสาหกิจ">{{ __('consent::messages.modal.form.step3.options.occupation_state_enterprise') }}</option>
                                <option value="พนักงานบริษัทเอกชน">{{ __('consent::messages.modal.form.step3.options.occupation_private_employee') }}</option>
                                <option value="อาชีพอิสระ">{{ __('consent::messages.modal.form.step3.options.occupation_freelance') }}</option>
                                <option value="เจ้าของกิจการที่จดทะเบียนพาณิชย์">{{ __('consent::messages.modal.form.step3.options.occupation_registered_owner') }}</option>
                                <option value="เจ้าของกิจการที่ไม่จดทะเบียนพาณิชย์">{{ __('consent::messages.modal.form.step3.options.occupation_unregistered_owner') }}</option>
                                <option value="อื่นๆ">{{ __('consent::messages.modal.form.common.other') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-6 hidden" id="governmentLevelWrapper">
                            <label for="governmentLevel">{{ __('consent::messages.modal.form.step3.fields.government_level') }}</label>
                            <input type="text" id="governmentLevel" name="governmentLevel" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.government_level') }}">
                        </div>

                        <div class="form-group col-6 hidden" id="occupationOtherWrapper">
                            <label for="occupationOther">{{ __('consent::messages.modal.form.step3.fields.occupation_other') }}</label>
                            <input type="text" id="occupationOther" name="occupationOther" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.occupation_other') }}">
                        </div>

                        <div class="form-group col-6">
                            <label for="careerField">{{ __('consent::messages.modal.form.step3.fields.career_field') }} <span class="required-asterisk">*</span></label>
                            <select id="careerField" name="careerField" required>
                                <option value="">{{ __('consent::messages.modal.form.step3.options.career_prompt') }}</option>
                                <option value="ครู/อาจารย์">{{ __('consent::messages.modal.form.step3.options.career_teacher') }}</option>
                                <option value="ตํารวจ/ทหาร">{{ __('consent::messages.modal.form.step3.options.career_police_military') }}</option>
                                <option value="แพทย์/ทันตแพทย์/สัตวแพยท์">{{ __('consent::messages.modal.form.step3.options.career_medical') }}</option>
                                <option value="เภสัชกร">{{ __('consent::messages.modal.form.step3.options.career_pharmacist') }}</option>
                                <option value="พยาบาล">{{ __('consent::messages.modal.form.step3.options.career_nurse') }}</option>
                                <option value="สถาปนิก">{{ __('consent::messages.modal.form.step3.options.career_architect') }}</option>
                                <option value="วิศวกร">{{ __('consent::messages.modal.form.step3.options.career_engineer') }}</option>
                                <option value="บัญชีการเงิน">{{ __('consent::messages.modal.form.step3.options.career_finance') }}</option>
                                <option value="พนักงานขาย">{{ __('consent::messages.modal.form.step3.options.career_sales') }}</option>
                                <option value="อื่นๆ">{{ __('consent::messages.modal.form.common.other') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-6 hidden" id="careerFieldOtherWrapper">
                            <label for="careerFieldOther">{{ __('consent::messages.modal.form.step3.fields.career_field_other') }}</label>
                            <input type="text" id="careerFieldOther" name="careerFieldOther" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.career_field_other') }}">
                        </div>

                        <div class="form-group col-6">
                            <label for="companyName">{{ __('consent::messages.modal.form.step3.fields.company_name') }}</label>
                            <input type="text" id="companyName" name="companyName" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.company_name') }}">
                        </div>

                        <div class="form-group col-6">
                            <label for="businessType">{{ __('consent::messages.modal.form.step3.fields.business_type') }} <span class="required-asterisk">*</span></label>
                            <select id="businessType" name="businessType" required>
                                <option value="">{{ __('consent::messages.modal.form.step3.options.business_prompt') }}</option>
                                <option value="การศึกษา">{{ __('consent::messages.modal.form.step3.options.business_education') }}</option>
                                <option value="รับเหมาก่อสร้าง">{{ __('consent::messages.modal.form.step3.options.business_construction') }}</option>
                                <option value="วัสดุก่อสร้าง">{{ __('consent::messages.modal.form.step3.options.business_construction_material') }}</option>
                                <option value="บริการ">{{ __('consent::messages.modal.form.step3.options.business_service') }}</option>
                                <option value="ฟอร์นิเจอร์/โรงเลื่อย">{{ __('consent::messages.modal.form.step3.options.business_furniture') }}</option>
                                <option value="สิ่งทอ">{{ __('consent::messages.modal.form.step3.options.business_textile') }}</option>
                                <option value="พลาสติก">{{ __('consent::messages.modal.form.step3.options.business_plastic') }}</option>
                                <option value="เครื่องจักร/ผลิตภัณฑ์โลหะ">{{ __('consent::messages.modal.form.step3.options.business_machinery_metal') }}</option>
                                <option value="สาธารณูปโภค/ไฟฟ้า">{{ __('consent::messages.modal.form.step3.options.business_utility_electric') }}</option>
                                <option value="ขนส่ง">{{ __('consent::messages.modal.form.step3.options.business_transport') }}</option>
                                <option value="สาธารณูปโภค">{{ __('consent::messages.modal.form.step3.options.business_utility') }}</option>
                                <option value="ไฟฟ้า">{{ __('consent::messages.modal.form.step3.options.business_electric') }}</option>
                                <option value="เวชภัณฑ์/โรงพยาบาล/คลินิก">{{ __('consent::messages.modal.form.step3.options.business_medical') }}</option>
                                <option value="อาหาร/เครื่องดื่ม">{{ __('consent::messages.modal.form.step3.options.business_food_beverage') }}</option>
                                <option value="ร้านสะดวกซื้อ">{{ __('consent::messages.modal.form.step3.options.business_convenience_store') }}</option>
                                <option value="โรงแรม/ร้านอาหาร">{{ __('consent::messages.modal.form.step3.options.business_hotel_restaurant') }}</option>
                                <option value="อื่นๆ">{{ __('consent::messages.modal.form.common.other') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-3 hidden" id="businessTypeOtherWrapper">
                            <label for="businessTypeOther">{{ __('consent::messages.modal.form.step3.fields.business_type_other') }}</label>
                            <input type="text" id="businessTypeOther" name="businessTypeOther" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.business_type_other') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="workDepartment">{{ __('consent::messages.modal.form.step3.fields.work_department') }}</label>
                            <input type="text" id="workDepartment" name="workDepartment" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.work_department') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="workAddressBuilding">{{ __('consent::messages.modal.form.step3.fields.work_building') }}</label>
                            <input type="text" id="workAddressBuilding" name="workAddressBuilding" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.work_building') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="workAddressFloor">{{ __('consent::messages.modal.form.common.address_floor') }}</label>
                            <input type="text" id="workAddressFloor" name="workAddressFloor" placeholder="{{ __('consent::messages.modal.form.common.address_floor') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="workAddressNo">{{ __('consent::messages.modal.form.common.no') }}</label>
                            <input type="text" id="workAddressNo" name="workAddressNo" placeholder="{{ __('consent::messages.modal.form.common.no') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="workAddressVillage">{{ __('consent::messages.modal.form.common.address_village') }}</label>
                            <input type="text" id="workAddressVillage" name="workAddressVillage" placeholder="{{ __('consent::messages.modal.form.common.address_village') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="workAddressSoi">{{ __('consent::messages.modal.form.common.address_soi') }}</label>
                            <input type="text" id="workAddressSoi" name="workAddressSoi" placeholder="{{ __('consent::messages.modal.form.common.address_soi') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="workAddressRoad">{{ __('consent::messages.modal.form.common.address_road') }}</label>
                            <input type="text" id="workAddressRoad" name="workAddressRoad" placeholder="{{ __('consent::messages.modal.form.common.address_road') }}">
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="workAddressSubdistrict">{{ __('consent::messages.modal.form.common.subdistrict') }}</label>
                            <input type="text" id="workAddressSubdistrict" name="workAddressSubdistrict" placeholder="{{ __('consent::messages.modal.form.common.search_subdistrict') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="workAddressSubdistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="workAddressDistrict">{{ __('consent::messages.modal.form.common.address_district') }}</label>
                            <input type="text" id="workAddressDistrict" name="workAddressDistrict" placeholder="{{ __('consent::messages.modal.form.common.search_district') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="workAddressDistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="workAddressProvince">{{ __('consent::messages.modal.form.common.province') }}</label>
                            <input type="text" id="workAddressProvince" name="workAddressProvince" placeholder="{{ __('consent::messages.modal.form.common.search_province') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="workAddressProvince_dropdown"></div>
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="workAddressPostal">{{ __('consent::messages.modal.form.common.postal') }}</label>
                            <input type="text" id="workAddressPostal" name="workAddressPostal" placeholder="{{ __('consent::messages.modal.form.common.search_postal') }}" autocomplete="off" inputmode="numeric">
                            <div class="address-search-dropdown hidden" id="workAddressPostal_dropdown"></div>
                        </div>

                        <div class="form-group col-3">
                            <label for="workPhone">{{ __('consent::messages.modal.form.common.phone_number') }}</label>
                            <input type="tel" id="workPhone" name="workPhone" placeholder="{{ __('consent::messages.modal.form.common.phone_number') }}" maxlength="10" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/\D/g,'')">
                        </div>

                        <div class="form-group col-4">
                            <label for="workYears">{{ __('consent::messages.modal.form.step3.fields.work_years') }}</label>
                            <input type="number" id="workYears" name="workYears" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.work_years') }}" min="0">
                        </div>

                        <div class="form-group col-4">
                            <label for="workMonths">{{ __('consent::messages.modal.form.step3.fields.work_months') }}</label>
                            <input type="number" id="workMonths" name="workMonths" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.work_months') }}" min="0" max="11">
                        </div>

                        <div id="previousWorkSection" class="section4-container previous-work-section form-section-frame hidden">
                            <div class="form-group col-12 note-box">
                                <span>{{ __('consent::messages.modal.form.step3.notes.previous_work_required') }} <span class="required-asterisk">*</span></span>
                            </div>

                            <div class="form-group col-6">
                                <label for="previousCompanyName">{{ __('consent::messages.modal.form.step3.fields.previous_company_name') }}</label>
                                <input type="text" id="previousCompanyName" name="previousCompanyName" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.previous_company_name') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="previousPosition">{{ __('consent::messages.modal.form.step3.fields.previous_position') }}</label>
                                <input type="text" id="previousPosition" name="previousPosition" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.previous_position') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="previousIncome">{{ __('consent::messages.modal.form.step3.fields.previous_income') }}</label>
                                <input type="number" id="previousIncome" name="previousIncome" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.previous_income') }}" min="0">
                            </div>

                            <div class="form-group col-12">
                                <label for="previousWorkAddress">{{ __('consent::messages.modal.form.step3.fields.previous_work_address') }}</label>
                                <textarea id="previousWorkAddress" name="previousWorkAddress" rows="3" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.previous_work_address') }}"></textarea>
                            </div>

                            <div class="form-group col-3">
                                <label for="previousPhone">{{ __('consent::messages.modal.form.common.phone_number') }}</label>
                                <input type="tel" id="previousPhone" name="previousPhone" placeholder="{{ __('consent::messages.modal.form.common.phone_number') }}" maxlength="10">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Income -->
                <div class="step-container" data-step="4">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            {{ __('consent::messages.modal.form.step4.sections.income') }}
                        </div>

                        <div class="form-group col-3">
                            <label for="income">{{ __('consent::messages.modal.form.step4.fields.income') }} <span class="required-asterisk">*</span></label>
                            <input type="number" id="income" name="income" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.income') }}" required>
                        </div>

                        <div class="form-group col-3">
                            <label for="extraIncome">{{ __('consent::messages.modal.form.step4.fields.extra_income') }}</label>
                            <input type="number" id="extraIncome" name="extraIncome" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.extra_income') }}">
                        </div>

                        <div class="form-group col-3" id="extraIncomeSourceWrapper">
                            <label for="extraIncomeSource">{{ __('consent::messages.modal.form.step4.fields.extra_income_source') }} <span class="required-asterisk">*</span></label>
                            <select id="extraIncomeSource" name="extraIncomeSource" required>
                                <option value="">{{ __('consent::messages.modal.form.step4.options.extra_income_source_prompt') }}</option>
                                <option value="รับจ้าง/เงินเดือน">{{ __('consent::messages.modal.form.step4.options.extra_income_source_salary') }}</option>
                                <option value="ค่าคอมมมิชั่น">{{ __('consent::messages.modal.form.step4.options.extra_income_source_commission') }}</option>
                                <option value="โบนัส">{{ __('consent::messages.modal.form.step4.options.extra_income_source_bonus') }}</option>
                                <option value="ธุรกิจส่วนตัว">{{ __('consent::messages.modal.form.step4.options.extra_income_source_business') }}</option>
                                <option value="อื่นๆ">{{ __('consent::messages.modal.form.step4.options.extra_income_source_other') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-3 hidden" id="extraIncomeSourceOtherWrapper">
                            <label for="extraIncomeSourceOther">{{ __('consent::messages.modal.form.step4.fields.extra_income_source_other') }}</label>
                            <input type="text" id="extraIncomeSourceOther" name="extraIncomeSourceOther" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.extra_income_source_other') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="incomeCountry">{{ __('consent::messages.modal.form.step4.fields.income_country') }}</label>
                            <input type="text" id="incomeCountry" name="incomeCountry" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.income_country') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="hasOtherDebts">{{ __('consent::messages.modal.form.step4.fields.has_other_debts') }} <span class="required-asterisk">*</span></label>
                            <select id="hasOtherDebts" name="hasOtherDebts" required>
                                <option value="">{{ __('consent::messages.modal.form.step4.options.select_prompt') }}</option>
                                <option value="0">{{ __('consent::messages.modal.form.step4.options.no') }}</option>
                                <option value="1">{{ __('consent::messages.modal.form.step4.options.yes') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-3 hidden" id="otherDebtInstallmentWrapper">
                            <label for="otherDebtInstallment">{{ __('consent::messages.modal.form.step4.fields.other_debt_installment') }}</label>
                            <input type="number" id="otherDebtInstallment" name="otherDebtInstallment" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.other_debt_installment') }}">
                        </div>

                        <div id="section4_container" class="section4-container form-section-frame">
                            <div class="form-group col-12">
                                <label for="hasExistingLoan">{{ __('consent::messages.modal.form.step4.fields.existing_loan_disclosure') }} <span class="required-asterisk">*</span></label>
                                    <span class="question-text">
                                        {{ __('consent::messages.modal.form.step4.questions.existing_loan') }}
                                    </span>
                            </div>

                            <div class="form-group col-4">
                                <label for="hasExistingLoan" aria-hidden="true">&nbsp;</label>
                                <select id="hasExistingLoan" name="hasExistingLoan" required>
                                    <option value="">{{ __('consent::messages.modal.form.step4.options.select_prompt') }}</option>
                                    <option value="1">{{ __('consent::messages.modal.form.step4.options.existing_loan_yes') }}</option>
                                    <option value="0">{{ __('consent::messages.modal.form.step4.options.existing_loan_no') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-4 hidden" id="existingLoanInstitutionCountWrapper">
                                <label for="existingLoanInstitutionCount">{{ __('consent::messages.modal.form.step4.fields.existing_loan_institution_count') }}</label>
                                <input type="number" id="existingLoanInstitutionCount" name="existingLoanInstitutionCount" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.existing_loan_institution_count') }}">
                            </div>

                            <div class="form-group col-4 hidden" id="existingLoanTotalAmountWrapper">
                                <label for="existingLoanTotalAmount">{{ __('consent::messages.modal.form.step4.fields.existing_loan_total_amount') }}</label>
                                <input type="number" id="existingLoanTotalAmount" name="existingLoanTotalAmount" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.existing_loan_total_amount') }}">
                            </div>

                            <div class="form-group col-12 note-box">
                                <span>{{ __('consent::messages.modal.form.step4.notes.existing_loan_warning') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Reference Person -->
                <div class="step-container" data-step="5">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            {{ __('consent::messages.modal.form.step5.sections.reference') }}
                        </div>

                        <div class="form-group col-6">
                            <label for="refType">{{ __('consent::messages.modal.form.step5.sections.ref_type') }} <span class="required-asterisk">*</span></label>
                            <select id="refType" name="refType" required>
                                <option value="">{{ __('consent::messages.modal.form.step5.options.reference_prompt') }}</option>
                                <option value="reference">{{ __('consent::messages.modal.form.step5.options.reference') }}</option>
                                <option value="guarantor">{{ __('consent::messages.modal.form.step5.options.guarantor') }}</option>
                            </select>
                        </div>


                        <div class="form-group col-6">
                            <label for="refName">{{ __('consent::messages.modal.form.step5.fields.ref_name') }} <span class="required-asterisk">*</span></label>
                            <input type="text" id="refName" name="refName" placeholder="{{ __('consent::messages.modal.form.step5.placeholders.ref_name') }}" required>
                        </div>

                        <div class="form-group col-6">
                            <label for="refRelation">{{ __('consent::messages.modal.form.step5.fields.ref_relation') }}</label>
                            <input type="text" id="refRelation" name="refRelation" placeholder="{{ __('consent::messages.modal.form.step5.placeholders.ref_relation') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="refAddressNo">{{ __('consent::messages.modal.form.step5.fields.ref_address_no') }}</label>
                            <input type="text" id="refAddressNo" name="refAddressNo" placeholder="{{ __('consent::messages.modal.form.step5.placeholders.ref_address_no') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="refAddressFloor">{{ __('consent::messages.modal.form.common.address_floor') }}</label>
                            <input type="text" id="refAddressFloor" name="refAddressFloor" placeholder="{{ __('consent::messages.modal.form.common.address_floor') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="refAddressVillage">{{ __('consent::messages.modal.form.common.address_village') }}</label>
                            <input type="text" id="refAddressVillage" name="refAddressVillage" placeholder="{{ __('consent::messages.modal.form.common.address_village') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="refAddressBuilding">{{ __('consent::messages.modal.form.step5.fields.ref_address_building') }}</label>
                            <input type="text" id="refAddressBuilding" name="refAddressBuilding" placeholder="{{ __('consent::messages.modal.form.step5.placeholders.ref_address_building') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="refAddressSoi">{{ __('consent::messages.modal.form.common.address_soi') }}</label>
                            <input type="text" id="refAddressSoi" name="refAddressSoi" placeholder="{{ __('consent::messages.modal.form.common.address_soi') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="refAddressRoad">{{ __('consent::messages.modal.form.common.address_road') }}</label>
                            <input type="text" id="refAddressRoad" name="refAddressRoad" placeholder="{{ __('consent::messages.modal.form.common.address_road') }}">
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="refAddressSubdistrict">{{ __('consent::messages.modal.form.common.subdistrict') }}</label>
                            <input type="text" id="refAddressSubdistrict" name="refAddressSubdistrict" placeholder="{{ __('consent::messages.modal.form.common.search_subdistrict') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="refAddressSubdistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="refAddressDistrict">{{ __('consent::messages.modal.form.common.search_district') }}</label>
                            <input type="text" id="refAddressDistrict" name="refAddressDistrict" placeholder="{{ __('consent::messages.modal.form.common.search_district') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="refAddressDistrict_dropdown"></div>
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="refAddressProvince">{{ __('consent::messages.modal.form.common.province') }}</label>
                            <input type="text" id="refAddressProvince" name="refAddressProvince" placeholder="{{ __('consent::messages.modal.form.common.search_province') }}" autocomplete="off">
                            <div class="address-search-dropdown hidden" id="refAddressProvince_dropdown"></div>
                        </div>

                        <div class="form-group col-3 address-search-field">
                            <label for="refAddressPostal">{{ __('consent::messages.modal.form.common.postal') }}</label>
                            <input type="text" id="refAddressPostal" name="refAddressPostal" placeholder="{{ __('consent::messages.modal.form.common.search_postal') }}" autocomplete="off" inputmode="numeric">
                            <div class="address-search-dropdown hidden" id="refAddressPostal_dropdown"></div>
                        </div>

                        <div class="form-group col-3">
                            <label for="refPhoneHome">{{ __('consent::messages.modal.form.common.phone_home') }}</label>
                            <input type="tel" id="refPhoneHome" name="refPhoneHome" placeholder="{{ __('consent::messages.modal.form.common.phone_home') }}" maxlength="10" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/\D/g,'')">
                        </div>

                        <div class="form-group col-3">
                            <label for="refPhoneMobile">{{ __('consent::messages.modal.form.common.phone_number') }}</label>
                            <input type="tel" id="refPhoneMobile" name="refPhoneMobile" placeholder="{{ __('consent::messages.modal.form.common.phone_number') }}" maxlength="10" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/\D/g,'')">
                        </div>

                        
                        <!-- Personal fields (no section header as requested) -->
                        <div class="form-group col-3">
                            <label for="refBirthdate">{{ __('consent::messages.modal.form.step1.fields.birthdate') }}</label>
                            <input type="date" id="refBirthdate" name="refBirthdate">
                        </div>

                        <div class="form-group col-3">
                            <label for="refNationality">{{ __('consent::messages.modal.form.step1.fields.nationality') }}</label>
                            <input type="text" id="refNationality" name="refNationality" placeholder="{{ __('consent::messages.modal.form.step1.placeholders.nationality') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="refEducation">{{ __('consent::messages.modal.form.step1.fields.education') }}</label>
                            <select id="refEducation" name="refEducation">
                                <option value="">{{ __('consent::messages.modal.form.step1.options.education_prompt') }}</option>
                                <option value="มัธยมต้น">{{ __('consent::messages.modal.form.step1.options.education_lower_secondary') }}</option>
                                <option value="มัธยมปลาย">{{ __('consent::messages.modal.form.step1.options.education_upper_secondary') }}</option>
                                <option value="อุดมศึกษา">{{ __('consent::messages.modal.form.step1.options.education_higher') }}</option>
                                <option value="ปริญญาตรี">{{ __('consent::messages.modal.form.step1.options.education_bachelor') }}</option>
                                <option value="ปริญญาโท">{{ __('consent::messages.modal.form.step1.options.education_master') }}</option>
                                <option value="ปริญญาเอก">{{ __('consent::messages.modal.form.step1.options.education_doctorate') }}</option>
                                <option value="อื่นๆ">{{ __('consent::messages.modal.form.common.other') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-3 hidden" id="refEducationOtherWrapper">
                            <label for="refEducationOther">{{ __('consent::messages.modal.form.step1.fields.education_other') }}</label>
                            <input type="text" id="refEducationOther" name="refEducationOther" placeholder="{{ __('consent::messages.modal.form.step1.placeholders.education_other') }}">
                        </div>

                        <div class="form-group col-3">
                            <label for="refMaritalStatus">{{ __('consent::messages.modal.form.step1.fields.marital_status') }}</label>
                            <select id="refMaritalStatus" name="refMaritalStatus">
                                <option value="">{{ __('consent::messages.modal.form.step1.options.marital_prompt') }}</option>
                                <option value="โสด">{{ __('consent::messages.modal.form.step1.options.marital_single') }}</option>
                                <option value="สมรส">{{ __('consent::messages.modal.form.step1.options.marital_married') }}</option>
                                <option value="สมรสไม่จดทะเบียน">{{ __('consent::messages.modal.form.step1.options.marital_common_law') }}</option>
                                <option value="หย่า">{{ __('consent::messages.modal.form.step1.options.marital_divorced') }}</option>
                                <option value="หม้าย">{{ __('consent::messages.modal.form.step1.options.marital_widowed') }}</option>
                            </select>
                        </div>

                        <!-- Occupation: ข้อมูลอาชีพ -->
                        <!-- Workplace address fields (reuse step3 address placeholders) -->
                        <div class="form-group col-12">
                            <div class="form-section-title col-12" data-section="ref-occupation">
                                {{ __('consent::messages.modal.form.step3.sections.occupation_workplace') }}
                                <div class="checkbox-group">
                                    <input type="hidden" name="refUseHomeAddress" value="0">
                                    <input type="checkbox" id="refUseHomeAddress" name="refUseHomeAddress" value="1">
                                    <label for="refUseHomeAddress">{{ __('consent::messages.modal.form.step3.fields.use_home_address') }}</label>
                                </div>
                            </div>
                            <div class="form-group col-6">
                                <label for="refOccupation">{{ __('consent::messages.modal.form.step3.fields.occupation') }}</label>
                                <select id="refOccupation" name="refOccupation">
                                    <option value="">{{ __('consent::messages.modal.form.step3.options.occupation_prompt') }}</option>
                                    <option value="ข้าราชการ">{{ __('consent::messages.modal.form.step3.options.occupation_civil_servant') }}</option>
                                    <option value="พนักงานราชการ">{{ __('consent::messages.modal.form.step3.options.occupation_gov_employee') }}</option>
                                    <option value="พนักงานรัฐวิสาหกิจ">{{ __('consent::messages.modal.form.step3.options.occupation_state_enterprise') }}</option>
                                    <option value="พนักงานบริษัทเอกชน">{{ __('consent::messages.modal.form.step3.options.occupation_private_employee') }}</option>
                                    <option value="อาชีพอิสระ">{{ __('consent::messages.modal.form.step3.options.occupation_freelance') }}</option>
                                    <option value="เจ้าของกิจการที่จดทะเบียนพาณิชย์">{{ __('consent::messages.modal.form.step3.options.occupation_registered_owner') }}</option>
                                    <option value="เจ้าของกิจการที่ไม่จดทะเบียนพาณิชย์">{{ __('consent::messages.modal.form.step3.options.occupation_unregistered_owner') }}</option>
                                    <option value="อื่นๆ">{{ __('consent::messages.modal.form.common.other') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-6 hidden" id="refOccupationOtherWrapper">
                                <label for="refOccupationOther">{{ __('consent::messages.modal.form.step3.fields.occupation_other') }}</label>
                                <input type="text" id="refOccupationOther" name="refOccupationOther" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.occupation_other') }}">
                            </div>

                            <div class="form-group col-6 hidden" id="refGovernmentLevelWrapper">
                                <label for="refGovernmentLevel">{{ __('consent::messages.modal.form.step3.fields.government_level') }}</label>
                                <input type="text" id="refGovernmentLevel" name="refGovernmentLevel" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.government_level') }}">
                            </div>

                            <div class="form-group col-6">
                                <label for="refCareerField">{{ __('consent::messages.modal.form.step3.fields.career_field') }}</label>
                                <select id="refCareerField" name="refCareerField">
                                    <option value="">{{ __('consent::messages.modal.form.step3.options.career_prompt') }}</option>
                                    <option value="ครู/อาจารย์">{{ __('consent::messages.modal.form.step3.options.career_teacher') }}</option>
                                    <option value="ตํารวจ/ทหาร">{{ __('consent::messages.modal.form.step3.options.career_police_military') }}</option>
                                    <option value="แพทย์/ทันตแพทย์/สัตวแพยท์">{{ __('consent::messages.modal.form.step3.options.career_medical') }}</option>
                                    <option value="เภสัชกร">{{ __('consent::messages.modal.form.step3.options.career_pharmacist') }}</option>
                                    <option value="พยาบาล">{{ __('consent::messages.modal.form.step3.options.career_nurse') }}</option>
                                    <option value="สถาปนิก">{{ __('consent::messages.modal.form.step3.options.career_architect') }}</option>
                                    <option value="วิศวกร">{{ __('consent::messages.modal.form.step3.options.career_engineer') }}</option>
                                    <option value="บัญชีการเงิน">{{ __('consent::messages.modal.form.step3.options.career_finance') }}</option>
                                    <option value="พนักงานขาย">{{ __('consent::messages.modal.form.step3.options.career_sales') }}</option>
                                    <option value="อื่นๆ">{{ __('consent::messages.modal.form.common.other') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-6 hidden" id="refCareerFieldOtherWrapper">
                                <label for="refCareerFieldOther">{{ __('consent::messages.modal.form.step3.fields.career_field_other') }}</label>
                                <input type="text" id="refCareerFieldOther" name="refCareerFieldOther" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.career_field_other') }}">
                            </div>



                            <div class="form-group col-6">
                                <label for="refWorkCompany">{{ __('consent::messages.modal.form.step3.fields.company_name') }}</label>
                                <input type="text" id="refWorkCompany" name="refWorkCompany" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.company_name') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkDepartment">{{ __('consent::messages.modal.form.step3.fields.work_department') }}</label>
                                <input type="text" id="refWorkDepartment" name="refWorkDepartment" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.work_department') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refBusinessType">{{ __('consent::messages.modal.form.step3.fields.business_type') }}</label>
                                <select id="refBusinessType" name="refBusinessType">
                                    <option value="">{{ __('consent::messages.modal.form.step3.options.business_prompt') }}</option>
                                    <option value="การศึกษา">{{ __('consent::messages.modal.form.step3.options.business_education') }}</option>
                                    <option value="รับเหมาก่อสร้าง">{{ __('consent::messages.modal.form.step3.options.business_construction') }}</option>
                                    <option value="วัสดุก่อสร้าง">{{ __('consent::messages.modal.form.step3.options.business_construction_material') }}</option>
                                    <option value="บริการ">{{ __('consent::messages.modal.form.step3.options.business_service') }}</option>
                                    <option value="ฟอร์นิเจอร์/โรงเลื่อย">{{ __('consent::messages.modal.form.step3.options.business_furniture') }}</option>
                                    <option value="สิ่งทอ">{{ __('consent::messages.modal.form.step3.options.business_textile') }}</option>
                                    <option value="พลาสติก">{{ __('consent::messages.modal.form.step3.options.business_plastic') }}</option>
                                    <option value="เครื่องจักร/ผลิตภัณฑ์โลหะ">{{ __('consent::messages.modal.form.step3.options.business_machinery_metal') }}</option>
                                    <option value="สาธารณูปโภค/ไฟฟ้า">{{ __('consent::messages.modal.form.step3.options.business_utility_electric') }}</option>
                                    <option value="ขนส่ง">{{ __('consent::messages.modal.form.step3.options.business_transport') }}</option>
                                    <option value="สาธารณูปโภค">{{ __('consent::messages.modal.form.step3.options.business_utility') }}</option>
                                    <option value="ไฟฟ้า">{{ __('consent::messages.modal.form.step3.options.business_electric') }}</option>
                                    <option value="เวชภัณฑ์/โรงพยาบาล/คลินิก">{{ __('consent::messages.modal.form.step3.options.business_medical') }}</option>
                                    <option value="อาหาร/เครื่องดื่ม">{{ __('consent::messages.modal.form.step3.options.business_food_beverage') }}</option>
                                    <option value="ร้านสะดวกซื้อ">{{ __('consent::messages.modal.form.step3.options.business_convenience_store') }}</option>
                                    <option value="โรงแรม/ร้านอาหาร">{{ __('consent::messages.modal.form.step3.options.business_hotel_restaurant') }}</option>
                                    <option value="อื่นๆ">{{ __('consent::messages.modal.form.common.other') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-3 hidden" id="refBusinessTypeOtherWrapper">
                                <label for="refBusinessTypeOther">{{ __('consent::messages.modal.form.step3.fields.business_type_other') }}</label>
                                <input type="text" id="refBusinessTypeOther" name="refBusinessTypeOther" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.business_type_other') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkNo">{{ __('consent::messages.modal.form.common.no') }}</label>
                                <input type="text" id="refWorkNo" name="refWorkNo" placeholder="{{ __('consent::messages.modal.form.common.no') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkRoom">{{ __('consent::messages.modal.form.step2.fields.address_room') }}</label>
                                <input type="text" id="refWorkRoom" name="refWorkRoom" placeholder="{{ __('consent::messages.modal.form.step2.placeholders.address_room') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkFloor">{{ __('consent::messages.modal.form.common.address_floor') }}</label>
                                <input type="text" id="refWorkFloor" name="refWorkFloor" placeholder="{{ __('consent::messages.modal.form.common.address_floor') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkBuildingName">{{ __('consent::messages.modal.form.step3.fields.work_building') }}</label>
                                <input type="text" id="refWorkBuildingName" name="refWorkBuildingName" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.work_building') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkVillage">{{ __('consent::messages.modal.form.common.address_village') }}</label>
                                <input type="text" id="refWorkVillage" name="refWorkVillage" placeholder="{{ __('consent::messages.modal.form.common.address_village') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkSoi">{{ __('consent::messages.modal.form.common.address_soi') }}</label>
                                <input type="text" id="refWorkSoi" name="refWorkSoi" placeholder="{{ __('consent::messages.modal.form.common.address_soi') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkRoad">{{ __('consent::messages.modal.form.common.address_road') }}</label>
                                <input type="text" id="refWorkRoad" name="refWorkRoad" placeholder="{{ __('consent::messages.modal.form.common.address_road') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkDistrict">{{ __('consent::messages.modal.form.common.address_district') }}</label>
                                <input type="text" id="refWorkDistrict" name="refWorkDistrict" placeholder="{{ __('consent::messages.modal.form.common.address_district') }}" autocomplete="off">
                                <div class="address-search-dropdown hidden" id="refWorkDistrict_dropdown"></div>
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkProvince">{{ __('consent::messages.modal.form.common.province') }}</label>
                                <input type="text" id="refWorkProvince" name="refWorkProvince" placeholder="{{ __('consent::messages.modal.form.common.province') }}" autocomplete="off">
                                <div class="address-search-dropdown hidden" id="refWorkProvince_dropdown"></div>
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkPostal">{{ __('consent::messages.modal.form.common.postal') }}</label>
                                <input type="text" id="refWorkPostal" name="refWorkPostal" placeholder="{{ __('consent::messages.modal.form.common.postal') }}" autocomplete="off" inputmode="numeric">
                                <div class="address-search-dropdown hidden" id="refWorkPostal_dropdown"></div>
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkPhone">{{ __('consent::messages.modal.form.common.phone_number') }}</label>
                                <input type="tel" id="refWorkPhone" name="refWorkPhone" placeholder="{{ __('consent::messages.modal.form.common.phone_number') }}" accept="[0-9]*" maxlength="10" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/\D/g,'')">
                            </div>

                            <div class="form-group col-3">
                                <label for="refWorkYears">{{ __('consent::messages.modal.form.step3.fields.work_years') }}</label>
                                <input type="number" id="refWorkYears" name="refWorkYears" min="0" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.work_years') }}">
                            </div>

                            <div class="form-group col-4">
                                <label for="refWorkMonths">{{ __('consent::messages.modal.form.step3.fields.work_months') }}</label>
                                <input type="number" id="refWorkMonths" name="refWorkMonths" placeholder="{{ __('consent::messages.modal.form.step3.placeholders.work_months') }}" min="0" max="11">
                            </div>
                        </div>

                        <!-- Income: ข้อมูลรายได้ -->
                        <div class="form-group col-12">
                            <div class="form-section-title col-12" data-section="ref-income">
                                {{ __('consent::messages.modal.form.step4.sections.income') }}
                            </div>
                            <div class="form-group col-4">
                                <label for="refIncome">{{ __('consent::messages.modal.form.step4.fields.income') }}</label>
                                <input type="number" id="refIncome" name="refIncome" step="0.01" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.income') }}">
                            </div>

                            <div class="form-group col-4">
                                <label for="refExtraIncome">{{ __('consent::messages.modal.form.step4.fields.extra_income') }}</label>
                                <input type="number" id="refExtraIncome" name="refExtraIncome" step="0.01" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.extra_income') }}">
                            </div>

                            <div class="form-group col-4" id="refExtraIncomeSourceWrapper">
                                <label for="refExtraIncomeSource">{{ __('consent::messages.modal.form.step4.fields.extra_income_source') }}</label>
                                <select id="refExtraIncomeSource" name="refExtraIncomeSource">
                                    <option value="">{{ __('consent::messages.modal.form.step4.options.extra_income_source_prompt') }}</option>
                                    <option value="รับจ้าง/เงินเดือน">{{ __('consent::messages.modal.form.step4.options.extra_income_source_salary') }}</option>
                                    <option value="ค่าคอมมมิชั่น">{{ __('consent::messages.modal.form.step4.options.extra_income_source_commission') }}</option>
                                    <option value="โบนัส">{{ __('consent::messages.modal.form.step4.options.extra_income_source_bonus') }}</option>
                                    <option value="ธุรกิจส่วนตัว">{{ __('consent::messages.modal.form.step4.options.extra_income_source_business') }}</option>
                                    <option value="อื่นๆ">{{ __('consent::messages.modal.form.step4.options.extra_income_source_other') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-4 hidden" id="refExtraIncomeSourceOtherWrapper">
                                <label for="refExtraIncomeSourceOther">{{ __('consent::messages.modal.form.step4.fields.extra_income_source_other') }}</label>
                                <input type="text" id="refExtraIncomeSourceOther" name="refExtraIncomeSourceOther" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.extra_income_source_other') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refHasOtherDebts">{{ __('consent::messages.modal.form.step4.fields.has_other_debts') }}</label>
                                <select id="refHasOtherDebts" name="refHasOtherDebts">
                                    <option value="">{{ __('consent::messages.modal.form.step4.options.select_prompt') }}</option>
                                    <option value="0">{{ __('consent::messages.modal.form.step4.options.no') }}</option>
                                    <option value="1">{{ __('consent::messages.modal.form.step4.options.yes') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-3 hidden" id="refOtherDebtInstallmentWrapper">
                                <label for="refOtherDebtInstallment">{{ __('consent::messages.modal.form.step4.fields.other_debt_installment') }}</label>
                                <input type="number" id="refOtherDebtInstallment" name="refOtherDebtInstallment" step="0.01" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.other_debt_installment') }}">
                            </div>

                            <div class="form-group col-3">
                                <label for="refHasExistingLoan">{{ __('consent::messages.modal.form.step4.fields.existing_loan_disclosure') }}</label>
                                <select id="refHasExistingLoan" name="refHasExistingLoan">
                                    <option value="">{{ __('consent::messages.modal.form.step4.options.select_prompt') }}</option>
                                    <option value="1">{{ __('consent::messages.modal.form.step4.options.existing_loan_yes') }}</option>
                                    <option value="0">{{ __('consent::messages.modal.form.step4.options.existing_loan_no') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-3 hidden" id="refExistingLoanInstitutionCountWrapper">
                                <label for="refExistingLoanInstitutionCount">{{ __('consent::messages.modal.form.step4.fields.existing_loan_institution_count') }}</label>
                                <input type="number" id="refExistingLoanInstitutionCount" name="refExistingLoanInstitutionCount" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.existing_loan_institution_count') }}">
                            </div>

                            <div class="form-group col-3 hidden" id="refExistingLoanTotalAmountWrapper">
                                <label for="refExistingLoanTotalAmount">{{ __('consent::messages.modal.form.step4.fields.existing_loan_total_amount') }}</label>
                                <input type="number" id="refExistingLoanTotalAmount" name="refExistingLoanTotalAmount" step="0.01" min="0" placeholder="{{ __('consent::messages.modal.form.step4.placeholders.existing_loan_total_amount') }}">
                            </div>
                        </div>

                        <!-- Reference / Guarantor documents upload (visible when refType=guarantor) -->
                        <div class="form-group col-12" id="referenceDocumentsSection">
                            <div class="form-section-title">
                                {{ __('consent::messages.modal.form.attachment.reference_header') }}
                            </div>
                            <div class="col-12">
                                <div class="document-upload-table-wrap">
                                    <table class="document-upload-table">
                                        <thead>
                                            <tr>
                                                <th class="document-upload-label-cell">{{ __('consent::messages.modal.form.attachment.reference_documents') }}</th>
                                                <th class="document-upload-input-cell">{{ __('consent::messages.modal.form.attachment.common.upload') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="document-upload-label-cell">
                                                    <div class="reference-doc-list-wrapper">
                                                        <p class="muted">{{ __('consent::messages.modal.form.attachment.reference_documents_hint') }}</p>
                                                    </div>
                                                </td>
                                                <td class="document-upload-input-cell">
                                                    <div class="upload-stack">
                                                        <label for="referenceDocuments" class="custom-file-upload compact">
                                                            <span>{{ __('consent::messages.modal.form.attachment.common.upload') }}</span>
                                                            <p>{{ __('consent::messages.modal.form.attachment.common.allowed_types') }}: PDF, JPG, PNG — {{ __('consent::messages.modal.form.attachment.common.per_file_max_10mb') }}</p>
                                                        </label>
                                                        <input type="file" id="referenceDocuments" name="referenceDocuments[]" multiple accept=".pdf,.jpg,.jpeg,.png,image/*,application/pdf" class="file-input-hidden">

                                                        <div class="hidden" id="referenceDocumentsSelectedWrapper">
                                                            <label for="referenceDocuments" class="uploaded-files-title">{{ __('consent::messages.modal.form.attachment.common.selected_files') }}</label>
                                                            <div id="referenceDocumentsSelectedList"></div>
                                                        </div>

                                                        <div class="hidden" id="referenceDocumentsExistingWrapper">
                                                            <label for="referenceDocuments" class="uploaded-files-title">{{ __('consent::messages.modal.form.attachment.common.existing_files') }}</label>
                                                            <div id="referenceDocumentsExistingList"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-12 note-box">
                            <span>{{ __('consent::messages.modal.form.step5.notes.reference_consent') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Loan Preference -->
                <div class="step-container" data-step="6">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            {{ __('consent::messages.modal.form.step6.sections.loan_preference') }}
                        </div>

                        <div class="form-group col-6">
                            <label for="loanPurpose">{{ __('consent::messages.modal.form.step6.fields.loan_purpose') }} <span class="required-asterisk">*</span></label>
                            <input type="text" id="loanPurpose" name="loanPurpose" placeholder="{{ __('consent::messages.modal.form.step6.placeholders.loan_purpose') }}" required>
                        </div>

                        <div class="form-group col-3">
                            <label for="loanTerm">{{ __('consent::messages.modal.form.step6.fields.loan_term') }} <span class="required-asterisk">*</span></label>
                            <select id="loanTerm" name="loanTerm" required>
                                <option value="">{{ __('consent::messages.modal.form.step6.options.loan_term_prompt') }}</option>
                                <option value="4">{{ __('consent::messages.modal.form.step6.options.loan_term_4') }}</option>
                                <option value="6">{{ __('consent::messages.modal.form.step6.options.loan_term_6') }}</option>
                                <option value="12">{{ __('consent::messages.modal.form.step6.options.loan_term_12') }}</option>
                                <option value="18">{{ __('consent::messages.modal.form.step6.options.loan_term_18') }}</option>
                                <option value="24">{{ __('consent::messages.modal.form.step6.options.loan_term_24') }}</option>
                                <option value="36">{{ __('consent::messages.modal.form.step6.options.loan_term_36') }}</option>
                                <option value="48">{{ __('consent::messages.modal.form.step6.options.loan_term_48') }}</option>
                                <option value="60">{{ __('consent::messages.modal.form.step6.options.loan_term_60') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-3">
                            <label for="loanAmountType">{{ __('consent::messages.modal.form.step6.fields.loan_amount_type') }} <span class="required-asterisk">*</span></label>
                            <select id="loanAmountType" name="loanAmountType" required>
                                <option value="">{{ __('consent::messages.modal.form.step6.options.loan_amount_prompt') }}</option>
                                <option value="full">{{ __('consent::messages.modal.form.step6.options.loan_amount_full') }}</option>
                                <option value="custom">{{ __('consent::messages.modal.form.step6.options.loan_amount_custom') }}</option>
                            </select>
                        </div>

                        <div class="form-group col-3 hidden" id="customLoanAmountWrapper">
                            <label for="customLoanAmount">{{ __('consent::messages.modal.form.step6.fields.custom_loan_amount') }}</label>
                            <input type="number" id="customLoanAmount" name="customLoanAmount" placeholder="{{ __('consent::messages.modal.form.step6.placeholders.custom_loan_amount') }}" min="0">
                        </div>

                        <div class="form-group col-12 note-box">
                            <span>{{ __('consent::messages.modal.form.step6.notes.loan_amount') }}</span>
                        </div>

                        <div class="form-section-title">
                            {{ __('consent::messages.modal.form.step6.sections.first_disbursement') }}
                        </div>

                        <div class="form-group col-12">
                            <p class="mb-1 text-muted">
                                {{ __('consent::messages.modal.form.step6.notes.first_disbursement') }}
                            </p>
                        </div>

                        <div class="form-group col-4">
                            <label for="accountNumber">{{ __('consent::messages.modal.form.step6.fields.account_number') }} <span class="required-asterisk">*</span></label>
                            <input type="text" id="accountNumber" name="accountNumber" placeholder="{{ __('consent::messages.modal.form.step6.placeholders.account_number') }}" required>
                        </div>

                        <div class="form-group col-4">
                            <label for="accountType">{{ __('consent::messages.modal.form.step6.fields.account_type') }} <span class="required-asterisk">*</span></label>
                            <input type="text" id="accountType" name="accountType" placeholder="{{ __('consent::messages.modal.form.step6.placeholders.account_type') }}" required>
                        </div>

                        <div class="form-group col-4">
                            <label for="bankName">{{ __('consent::messages.modal.form.step6.fields.bank_name') }} <span class="required-asterisk">*</span></label>
                            <input type="text" id="bankName" name="bankName" placeholder="{{ __('consent::messages.modal.form.step6.placeholders.bank_name') }}" required>
                        </div>

                        <div class="form-group col-4">
                            <label for="accountName">{{ __('consent::messages.modal.form.step6.fields.account_name') }} <span class="required-asterisk">*</span></label>
                            <input type="text" id="accountName" name="accountName" placeholder="{{ __('consent::messages.modal.form.step6.placeholders.account_name') }}" required>
                        </div>

                        <div class="form-section-title">
                            {{ __('consent::messages.modal.form.step6.sections.payment_method') }}
                        </div>

                        <div class="form-group col-12">
                            <label for="paymentMethod">{{ __('consent::messages.modal.form.step6.fields.payment_method') }} <span class="required-asterisk">*</span></label>
                            <select id="paymentMethod" name="paymentMethod" required>
                                <option value="">{{ __('consent::messages.modal.form.step6.options.payment_method_prompt') }}</option>
                                <option value="ชําระด้วยเงินสด">{{ __('consent::messages.modal.form.step6.options.payment_method_cash') }}</option>
                                <option value="ชําระโดยการหักบัญชี">{{ __('consent::messages.modal.form.step6.options.payment_method_debit') }}</option>
                            </select>
                        </div>

                        <div id="directDebitWrapper" class="hidden display-contents">
                            <div class="form-group col-12 note-box text-left line-height-relaxed mb-1">
                                <span>
                                    {!! __('consent::messages.modal.form.step6.notes.direct_debit') !!}
                                </span>
                            </div>
                            
                            <div class="form-group col-6">
                                <label for="directDebitAmount">{{ __('consent::messages.modal.form.step6.fields.direct_debit_amount') }}</label>
                                <input type="number" id="directDebitAmount" name="directDebitAmount" min="0" placeholder="{{ __('consent::messages.modal.form.step6.placeholders.direct_debit_amount') }}">
                            </div>

                            <div class="form-group col-6">
                                <label for="directDebitAccountNumber">{{ __('consent::messages.modal.form.step6.fields.direct_debit_account_number') }}</label>
                                <input type="text" id="directDebitAccountNumber" name="directDebitAccountNumber" placeholder="{{ __('consent::messages.modal.form.step6.placeholders.direct_debit_account_number') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 7: Consent Statement -->
                <div class="step-container" data-step="7">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            {{ __('consent::messages.modal.form.step7.sections.consent') }}
                        </div>

                        <div class="form-group col-12">
                            <p class="text-left line-height-extra mb-1-5 text-indent-large">
                                {{ __('consent::messages.modal.form.step7.paragraphs.pdpa_consent') }}</p>
                        </div>

                        <div class="form-group col-12">
                            <p class="text-left line-height-extra mb-1-5 text-indent-large">
                                {{ __('consent::messages.modal.form.step7.paragraphs.consent_statement') }}</p>
                        </div>

                        <div class="form-group col-12">
                            <div class="warning-box">
                                <h4 class="warning-title">{{ __('consent::messages.modal.form.step7.warning.title') }}</h4>
                                <ul class="warning-list">
                                    <li class="warning-item">{{ __('consent::messages.modal.form.step7.warning.item_1') }}</li>
                                    <li class="warning-item">{{ __('consent::messages.modal.form.step7.warning.item_2') }}</li>
                                    <li>{{ __('consent::messages.modal.form.step7.warning.item_3') }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group col-12 signature-group">
                            <div id="signatureLabel" class="signature-label">{{ __('consent::messages.modal.form.step7.fields.signature') }} <span class="required-asterisk">*</span></div>
                            <div class="signature-pad-box" aria-describedby="signatureLabel">
                                <canvas id="signaturePad" class="signature-canvas" aria-hidden="false"></canvas>
                                <button type="button" id="clearSignatureBtn" class="signature-clear-btn">{{ __('consent::messages.modal.form.step7.actions.clear_signature') }}</button>
                            </div>
                            <input type="hidden" id="signatureData" name="signatureData">
                            <p class="signature-date">{{ __('consent::messages.modal.form.step7.fields.signed_date') }}: {{ date('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Step 8: แนบไฟล์หลักฐานการเงินและเอกสารแสดงตัวตน -->
                <div class="step-container" data-step="8">
                    <div class="form-grid">
                        <div class="form-section-title form-section-title--no-top-margin">
                            {{ __('consent::messages.modal.form.attachment.section_title') }}
                        </div>
                        <div class="col-12">
                            <div class="document-upload-table-wrap">
                                <table class="document-upload-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('consent::messages.modal.form.attachment.identity_header') }}</th>
                                            <th>{{ __('consent::messages.modal.form.attachment.borrower_header') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="document-upload-label-cell">
                                                <div class="identity-doc-list-wrapper">
                                                    <p class="uploaded-files-title">{{ __('consent::messages.modal.form.attachment.identity_header') }}</p>
                                                    <ul class="identity-doc-list">
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.identity_id_card_copy') }} </li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.passport') }} </li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.house_registration_copy') }} </li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.work_permit') }} </li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.name_change_copy_optional') }} </li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td class="document-upload-input-cell">
                                                <div class="upload-stack">
                                                    <label for="identityDocuments" class="custom-file-upload compact">
                                                        <span>{{ __('consent::messages.modal.form.attachment.common.upload') }}</span>
                                                        <p>{{ __('consent::messages.modal.form.attachment.common.allowed_types') }}: PDF, JPG, PNG — {{ __('consent::messages.modal.form.attachment.common.per_file_max_10mb') }}</p>
                                                    </label>
                                                    <input type="file" id="identityDocuments" name="identityDocuments[]" multiple accept=".pdf,.jpg,.jpeg,.png,image/*,application/pdf" class="file-input-hidden">

                                                    <div class="hidden" id="identityDocumentsSelectedWrapper">
                                                        <label for="identityDocuments" class="uploaded-files-title">{{ __('consent::messages.modal.form.attachment.common.selected_files') }}</label>
                                                        <div id="identityDocumentsSelectedList"></div>
                                                    </div>

                                                    <div class="hidden" id="identityDocumentsExistingWrapper">
                                                        <label for="identityDocuments" class="uploaded-files-title">{{ __('consent::messages.modal.form.attachment.common.existing_files') }}</label>
                                                        <div id="identityDocumentsExistingList"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="document-upload-section-row">
                                            <th scope="col">{{ __('consent::messages.modal.form.attachment.income_header') }}</th>
                                            <th scope="col">{{ __('consent::messages.modal.form.attachment.borrower_header') }}</th>
                                        </tr>

                                        <tr>
                                            <td class="document-upload-label-cell">
                                                <div class="income-doc-list-wrapper">
                                                    <p class="uploaded-files-title">{{ __('consent::messages.modal.form.attachment.income_header') }}</p>
                                                    <ul class="income-doc-list">
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.salary_slip_latest') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.salary_certificate') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.tax_50_tawi_latest_or_6m') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.bank_statement_6m_salary') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.supplementary_slips') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.registered_certificate') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.shareholder_list_copy') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.trade_registration') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.bank_statement_1y') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.lease_contract_optional') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.tax_documents') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.purchase_sales_invoices_optional') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.business_photos') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.tax_docs_50_tawi_with_stamp') }}</li>
                                                        <li>{{ __('consent::messages.modal.form.attachment.documents.pnd_90_91_94') }}</li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td class="document-upload-input-cell">
                                                <div class="upload-stack">
                                                    <label for="incomeDocuments" class="custom-file-upload compact">
                                                        <span>{{ __('consent::messages.modal.form.attachment.common.upload') }}</span>
                                                        <p>{{ __('consent::messages.modal.form.attachment.common.allowed_types') }}: PDF, JPG, PNG — {{ __('consent::messages.modal.form.attachment.common.per_file_max_10mb') }}</p>
                                                    </label>
                                                    <input type="file" id="incomeDocuments" name="incomeDocuments[]" multiple accept=".pdf,.jpg,.jpeg,.png,image/*,application/pdf" class="file-input-hidden">

                                                    <div class="hidden" id="incomeDocumentsSelectedWrapper">
                                                        <label for="incomeDocuments" class="uploaded-files-title">{{ __('consent::messages.modal.form.attachment.common.selected_files') }}</label>
                                                        <div id="incomeDocumentsSelectedList"></div>
                                                    </div>

                                                    <div class="hidden" id="incomeDocumentsExistingWrapper">
                                                        <label for="incomeDocuments" class="uploaded-files-title">{{ __('consent::messages.modal.form.attachment.common.existing_files') }}</label>
                                                        <div id="incomeDocumentsExistingList"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="wizard-actions">
                    <button type="button" class="action-btn outline hidden" id="prevStepBtn">{{ __('consent::messages.modal.form.buttons.prev') }}</button>
                    <button type="button" class="action-btn" id="nextStepBtn">{{ __('consent::messages.modal.form.buttons.next') }}</button>
                    <button type="submit" class="action-btn hidden" id="consentSubmitBtn">{{ __('consent::messages.modal.form.buttons.submit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
