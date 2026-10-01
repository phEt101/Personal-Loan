<div id="customerHistoryFormModal" class="modal">
    <div class="modal-content modal-lg customer-form-modal">
        <form id="customerHistoryForm" action="{{ route('customer-history.store') }}" method="POST" autocomplete="off" novalidate>
            @csrf
            <div class="modal-header">
                <h3 class="modal-title">{{ __('customerhistory::messages.form.title') }}</h3>
                <button type="button" class="close-btn" id="closeCustomerHistoryForm" aria-label="Close modal">&times;</button>
            </div>

            <div class="modal-body scrollable">
                <div class="wizard-steps customer-history-steps" aria-label="{{ __('customerhistory::messages.form.steps.label') }}">
                    <button type="button" class="wizard-step active" data-step="1">
                        <span class="wizard-step-icon">1</span>
                        <span class="wizard-step-label">{{ __('customerhistory::messages.form.steps.personal') }}</span>
                    </button>
                    <button type="button" class="wizard-step" data-step="2" disabled>
                        <span class="wizard-step-icon">2</span>
                        <span class="wizard-step-label">{{ __('customerhistory::messages.form.steps.address') }}</span>
                    </button>
                    <button type="button" class="wizard-step" data-step="3" disabled>
                        <span class="wizard-step-icon">3</span>
                        <span class="wizard-step-label">{{ __('customerhistory::messages.form.steps.contact') }}</span>
                    </button>
                    <button type="button" class="wizard-step" data-step="4" disabled>
                        <span class="wizard-step-icon">4</span>
                        <span class="wizard-step-label">{{ __('customerhistory::messages.form.steps.income') }}</span>
                    </button>
                    <button type="button" class="wizard-step" data-step="5" disabled>
                        <span class="wizard-step-icon">5</span>
                        <span class="wizard-step-label">{{ __('customerhistory::messages.form.steps.profile') }}</span>
                    </button>
                </div>

                <section class="customer-form-section customer-form-step is-active" data-customer-step="1">
                    <div class="form-grid customer-form-grid">
                        <div class="customer-fieldset-title col-12">
                            <span>{{ __('customerhistory::messages.form.sections.personal') }}</span>
                        </div>
                        <div class="form-group col-2">
                            <label for="customer_title">{{ __('customerhistory::messages.form.personal.title_label') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_title" name="TitleCode" required>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($titles as $title)
                                    <option value="{{ $title->TitleCode }}">
                                        {{ $title->TitleDesc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-5">
                            <label for="customer_firstname">{{ __('customerhistory::messages.form.personal.firstname') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_firstname" name="Firstname" type="text" required>
                        </div>
                        <div class="form-group col-5">
                            <label for="customer_lastname">{{ __('customerhistory::messages.form.personal.lastname') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_lastname" name="Lastname" type="text" required>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_nickname">{{ __('customerhistory::messages.form.personal.nickname') }}</label>
                            <input id="customer_nickname" name="Nickname" type="text" maxlength="10">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_gender">{{ __('customerhistory::messages.form.personal.gender') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_gender" name="GenderCode" required>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($genders as $gender)
                                    <option value="{{ $gender->GenderId }}">
                                        {{ $gender->GenderDesc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_birth_date">{{ __('customerhistory::messages.form.personal.birth_date') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_birth_date" name="BirthDate" type="date" required>
                        </div>
                        <div class="customer-fieldset-title col-12">
                            <span>{{ __('customerhistory::messages.form.sections.identity') }}</span>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_identity_type">{{ __('customerhistory::messages.form.personal.identity_type') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_identity_type" name="IdentityCardTypeCode" required>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($identityCardTypes as $identityCardType)
                                    <option value="{{ $identityCardType->IdentityCardTypeCode }}" @selected((int) $identityCardType->IdentityCardTypeCode === 1)>
                                        {{ $identityCardType->IdentityCardTypeDesc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_identity"><span id="customer_identity_label">{{ __('customerhistory::messages.form.personal.identity_card') }}</span> <span class="required-asterisk">*</span></label>
                            <input id="customer_identity" name="IdentityCardId" type="text" inputmode="numeric" maxlength="13" required>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_identity_issuer">{{ __('customerhistory::messages.form.personal.identity_issuer') }}</label>
                            <input id="customer_identity_issuer" name="IdentityCardIssuer" type="text">
                        </div>
                        <div class="form-group col-6">
                            <label for="customer_identity_effective">{{ __('customerhistory::messages.form.personal.identity_effective_date') }}</label>
                            <input id="customer_identity_effective" name="IdentityCardEffectiveDate" type="date">
                        </div>
                        <div class="form-group col-6">
                            <label for="customer_identity_expire">{{ __('customerhistory::messages.form.personal.identity_expire_date') }}</label>
                            <input id="customer_identity_expire" name="IdentityCardExpireDate" type="date">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_nationality">{{ __('customerhistory::messages.form.personal.nationality') }}</label>
                            <input id="customer_nationality" name="Nationality" type="text">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_race">{{ __('customerhistory::messages.form.personal.race') }}</label>
                            <input id="customer_race" name="Race" type="text">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_marital_status">{{ __('customerhistory::messages.form.personal.marital_status') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_marital_status" name="MaritalStatusCode" required>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($maritalStatuses as $maritalStatus)
                                    <option value="{{ $maritalStatus->MaritalStatusCode }}">
                                        {{ $maritalStatus->MaritalStatusName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="customer-fieldset-title col-12">
                            <span>{{ __('customerhistory::messages.form.sections.work') }}</span>
                        </div>
                        <div class="form-group col-4 customer-layout-col-1">
                            <label for="customer_working_condition">{{ __('customerhistory::messages.form.profile.working_condition') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_working_condition" name="WorkingConditionId" required>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($workingConditions as $workingCondition)
                                    <option
                                        value="{{ $workingCondition->WorkingConditionId }}"
                                        data-requires-occupation="{{ (int) $workingCondition->IsRequireOccupation }}"
                                    >
                                        {{ $workingCondition->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4 customer-layout-col-1" id="customer_occupation_group" hidden>
                            <label for="customer_occupation">{{ __('customerhistory::messages.form.profile.occupation') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_occupation" name="OccupationCode" required disabled>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($occupations as $occupation)
                                    <option
                                        value="{{ $occupation->OccupationCode }}"
                                        data-is-other-occupation="{{ (int) $occupation->IsOtherOccupation }}"
                                    >
                                        {{ $occupation->OccupationDesc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4 customer-layout-col-2" id="customer_business_type_group" hidden>
                            <label for="customer_business_type">{{ __('customerhistory::messages.form.profile.business_type') }}</label>
                            <select id="customer_business_type" name="TypeOfBusinessId" disabled>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($businessTypes as $businessType)
                                    <option value="{{ $businessType->TypeOfBusinessId }}">
                                        {{ $businessType->TypeOfBusinessName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4 customer-layout-col-3" id="customer_other_occupation_group" hidden>
                            <label for="customer_other_occupation">{{ __('customerhistory::messages.form.profile.other_occupation_desc') }}</label>
                            <input id="customer_other_occupation" name="OtherOccupationDesc" type="text" maxlength="255" data-conditional-required disabled>
                        </div>
                        <div class="customer-fieldset-title col-12">
                            <span>{{ __('customerhistory::messages.form.sections.additional') }}</span>
                        </div>
                        <div class="form-group col-4 customer-layout-col-1">
                            <label for="customer_address_type">{{ __('customerhistory::messages.form.address.type') }}</label>
                            <select id="customer_address_type" name="AddressTypeCode">
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($addressTypes as $addressType)
                                    <option value="{{ $addressType->AddressTypeCode }}">
                                        {{ $addressType->AddressTypeDesc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4 customer-layout-col-1">
                            <label for="customer_bank_code">{{ __('customerhistory::messages.form.bank.code') }}</label>
                            <select id="customer_bank_code" name="BankCode">
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->BankCode }}">
                                        {{ trim($bank->BankCode) }} - {{ $bank->BankDesc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4 customer-layout-col-2">
                            <label for="customer_bank_branch">{{ __('customerhistory::messages.form.bank.branch') }}</label>
                            <input id="customer_bank_branch" name="BankBookBranch" type="text" maxlength="50">
                        </div>
                        <div class="form-group col-4 customer-layout-col-3">
                            <label for="customer_bank_account">{{ __('customerhistory::messages.form.bank.account') }}</label>
                            <input id="customer_bank_account" name="BankBookCode" type="text" inputmode="numeric" maxlength="20">
                        </div>
                    </div>
                </section>

                <section class="customer-form-section customer-form-step" data-customer-step="2">
                    <div class="form-grid customer-form-grid">
                        <div class="col-12 customer-address-list">
                            <table>
                                <thead><tr><th>{{ __('customerhistory::messages.form.address.number') }}</th><th>{{ __('customerhistory::messages.form.address.address') }}</th><th>{{ __('customerhistory::messages.form.address.address_remark') }}</th><th>{{ __('customerhistory::messages.form.address.actions') }}</th></tr></thead>
                                <tbody id="customerAddressRows"><tr class="customer-address-empty"><td colspan="4">{{ __('customerhistory::messages.form.address.no_data') }}</td></tr></tbody>
                            </table>
                            <button type="button" class="action-btn customer-address-add" id="customerAddressAdd">+ {{ __('customerhistory::messages.form.address.add') }}</button>
                            <input id="customer_addresses" name="Addresses" type="hidden" required>
                        </div>
                        <div class="form-group col-6 customer-address-assignment">
                            <label for="customer_identity_card_address">{{ __('customerhistory::messages.form.address.identity_card') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_identity_card_address" name="IdentityCardAddressId" required disabled><option value="">{{ __('customerhistory::messages.form.select_option') }}</option></select>
                        </div>
                        <div class="form-group col-6 customer-address-assignment">
                            <label for="customer_house_registration_address">{{ __('customerhistory::messages.form.address.house_registration') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_house_registration_address" name="HouseRegistrationAddressId" required disabled><option value="">{{ __('customerhistory::messages.form.select_option') }}</option></select>
                        </div>
                        <div class="form-group col-6 customer-address-assignment">
                            <label for="customer_current_address">{{ __('customerhistory::messages.form.address.current') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_current_address" name="CurrentAddressId" required disabled><option value="">{{ __('customerhistory::messages.form.select_option') }}</option></select>
                        </div>
                        <div class="form-group col-6 customer-address-assignment">
                            <label for="customer_mailing_address">{{ __('customerhistory::messages.form.address.mailing') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_mailing_address" name="MailingAddressId" required disabled><option value="">{{ __('customerhistory::messages.form.select_option') }}</option></select>
                        </div>
                        <div class="customer-address-editor col-12" id="customerAddressEditor" hidden>
                            <div class="form-grid customer-form-grid">
                        <div class="form-group col-12">
                            <label for="customer_address_line1">{{ __('customerhistory::messages.form.address.line1') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_address_line1" type="text" required disabled>
                        </div>
                        <div class="form-group col-12">
                            <label for="customer_address_line2">{{ __('customerhistory::messages.form.address.line2') }}</label>
                            <input id="customer_address_line2" type="text" disabled>
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_province">{{ __('customerhistory::messages.form.address.province') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_province" required disabled>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->ProvinceCode }}" data-description="{{ $province->ProvinceDesc }}">
                                        {{ $province->ProvinceDesc }}
                                    </option>
                                @endforeach
                            </select>
                            <input id="customer_province_desc" type="hidden" disabled>
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_district">{{ __('customerhistory::messages.form.address.district') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_district" required disabled>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                            </select>
                            <input id="customer_district_desc" type="hidden" disabled>
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_subdistrict">{{ __('customerhistory::messages.form.address.subdistrict') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_subdistrict" required disabled>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                            </select>
                            <input id="customer_subdistrict_desc" type="hidden" disabled>
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_zipcode">{{ __('customerhistory::messages.form.address.zipcode') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_zipcode" type="text" inputmode="numeric" maxlength="10" required disabled>
                        </div>
                        <div class="form-group col-12">
                            <label for="customer_address_remark">{{ __('customerhistory::messages.form.address.address_remark') }}</label>
                            <textarea id="customer_address_remark" rows="3" disabled></textarea>
                        </div>
                                <div class="col-12 customer-address-editor-actions">
                                    <button type="button" class="action-btn outline" id="customerAddressCancel">{{ __('customerhistory::messages.form.address.close') }}</button>
                                    <button type="button" class="action-btn" id="customerAddressCommit">{{ __('customerhistory::messages.form.address.confirm_add') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="customer-form-section customer-form-step" data-customer-step="3">
                    <div class="form-grid customer-form-grid">
                        <div class="col-12 customer-phone-list">
                            <table>
                                <thead><tr><th>{{ __('customerhistory::messages.form.contact.number') }}</th><th>{{ __('customerhistory::messages.form.contact.phone') }}</th><th>{{ __('customerhistory::messages.form.contact.phone_type') }}</th><th>{{ __('customerhistory::messages.form.contact.phone_note') }}</th><th>{{ __('customerhistory::messages.form.contact.actions') }}</th></tr></thead>
                                <tbody id="customerPhoneRows"><tr class="customer-phone-empty"><td colspan="5">{{ __('customerhistory::messages.form.contact.no_data') }}</td></tr></tbody>
                            </table>
                            <button type="button" class="action-btn customer-phone-add" id="customerPhoneAdd">+ {{ __('customerhistory::messages.form.contact.add') }}</button>
                            <input id="customer_phones" name="Phones" type="hidden" required>
                        </div>
                        <div class="customer-phone-editor col-12" id="customerPhoneEditor" hidden>
                            <div class="form-grid customer-form-grid">
                        <div class="form-group col-6">
                            <label for="customer_mobile">{{ __('customerhistory::messages.form.contact.phone') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_mobile" type="tel" inputmode="numeric" maxlength="15" required disabled>
                        </div>
                        <div class="form-group col-6">
                            <label for="customer_phone_type">{{ __('customerhistory::messages.form.contact.phone_type') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_phone_type" required disabled>
                                <option value="">{{ __('customerhistory::messages.form.select_option') }}</option>
                                @foreach ($phoneTypes as $phoneType)
                                    <option value="{{ $phoneType->PhoneTypeCode }}">{{ $phoneType->PhoneTypeDesc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-12">
                            <label for="customer_phone_note">{{ __('customerhistory::messages.form.contact.phone_note') }}</label>
                            <textarea id="customer_phone_note" rows="2" disabled></textarea>
                        </div>
                                <div class="col-12 customer-phone-editor-actions">
                                    <button type="button" class="action-btn outline" id="customerPhoneCancel">{{ __('customerhistory::messages.form.contact.close') }}</button>
                                    <button type="button" class="action-btn" id="customerPhoneCommit">{{ __('customerhistory::messages.form.contact.confirm_add') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-12">
                            <label for="customer_primary_phone">{{ __('customerhistory::messages.form.contact.primary_phone') }} <span class="required-asterisk">*</span></label>
                            <select id="customer_primary_phone" name="MobileTelephoneId" required disabled><option value="">{{ __('customerhistory::messages.form.select_option') }}</option></select>
                        </div>
                        <div class="customer-contact-divider col-12" aria-hidden="true"></div>
                        <div class="form-group col-12">
                            <label for="customer_email">{{ __('customerhistory::messages.form.contact.email') }}</label>
                            <input id="customer_email" name="Email" type="email" maxlength="50">
                        </div>
                        <div class="form-group col-12">
                            <label for="customer_email_note">{{ __('customerhistory::messages.form.contact.email_note') }}</label>
                            <textarea id="customer_email_note" name="EmailRemark" rows="2"></textarea>
                        </div>
                    </div>
                </section>

                <section class="customer-form-section customer-form-step" data-customer-step="4">
                    <div class="form-grid customer-form-grid">
                        <div class="form-group col-12">
                            <label for="customer_workplace">{{ __('customerhistory::messages.form.income.workplace') }}</label>
                            <input id="customer_workplace" name="WorkPlace" type="text" maxlength="255">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_income">{{ __('customerhistory::messages.form.income.monthly_income') }}</label>
                            <div class="customer-input-unit">
                                <input id="customer_income" name="MonthlyIncomeAmount" type="text" inputmode="decimal">
                                <span>{{ __('customerhistory::messages.form.income.monthly_unit') }}</span>
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_expense">{{ __('customerhistory::messages.form.income.monthly_expense') }}</label>
                            <div class="customer-input-unit">
                                <input id="customer_expense" name="MonthlyExpenseAmount" type="text" inputmode="decimal">
                                <span>{{ __('customerhistory::messages.form.income.monthly_unit') }}</span>
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_bonus">{{ __('customerhistory::messages.form.income.yearly_bonus') }}</label>
                            <div class="customer-input-unit">
                                <input id="customer_bonus" name="YearlyBonusAmount" type="text" inputmode="decimal">
                                <span>{{ __('customerhistory::messages.form.income.yearly_unit') }}</span>
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_net_income">{{ __('customerhistory::messages.form.income.net_income') }}</label>
                            <div class="customer-input-unit">
                                <input id="customer_net_income" type="text" inputmode="decimal" readonly>
                                <span>{{ __('customerhistory::messages.form.income.yearly_unit') }}</span>
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_average_income">{{ __('customerhistory::messages.form.income.average_income') }}</label>
                            <div class="customer-input-unit">
                                <input id="customer_average_income" type="text" inputmode="decimal" readonly>
                                <span>{{ __('customerhistory::messages.form.income.monthly_unit') }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="customer-form-section customer-form-step" data-customer-step="5">
                    <div class="form-grid customer-form-grid">
                        <div class="form-group col-12">
                            <label for="customer_comment">{{ __('customerhistory::messages.form.profile.comment') }}</label>
                            <textarea id="customer_comment" name="Comment" rows="3"></textarea>
                        </div>
                    </div>
                </section>
            </div>

            <div class="modal-footer">
                <div class="form-actions modal-form-actions">
                    <button type="button" class="action-btn outline customer-form-cancel" id="cancelCustomerHistoryForm">
                        {{ __('customerhistory::messages.form.cancel') }}
                    </button>
                    <button type="button" class="action-btn outline customer-form-navigation" id="customerFormPrevious">
                        {{ __('customerhistory::messages.form.previous') }}
                    </button>
                    <button type="button" class="action-btn customer-form-navigation" id="customerFormNext">
                        {{ __('customerhistory::messages.form.next') }}
                    </button>
                    <button type="submit" class="action-btn customer-form-navigation" id="customerFormSave">
                        {{ __('customerhistory::messages.form.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('vendor/choices/choices.min.js') }}?v={{ filemtime(public_path('vendor/choices/choices.min.js')) }}"></script>
<script type="application/json" id="customerHistoryFormConfig">{!! json_encode($customerFormConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script>
    (() => {
        const configElement = document.getElementById('customerHistoryFormConfig');
        const config = JSON.parse(configElement?.textContent || '{}');
        const form = document.getElementById('customerHistoryForm');
        const modal = document.getElementById('customerHistoryFormModal');
        const modalTitle = modal?.querySelector('.modal-title');
        const createModalTitle = modalTitle?.textContent || '';
        const storeUrl = form?.action || '';
        const steps = Array.from(document.querySelectorAll('[data-customer-step]'));
        const indicators = Array.from(document.querySelectorAll('.customer-history-steps .wizard-step'));
        const cancelButton = document.getElementById('cancelCustomerHistoryForm');
        const previousButton = document.getElementById('customerFormPrevious');
        const nextButton = document.getElementById('customerFormNext');
        const saveButton = document.getElementById('customerFormSave');
        const identityType = document.getElementById('customer_identity_type');
        const identityInput = document.getElementById('customer_identity');
        const identityLabel = document.getElementById('customer_identity_label');
        const workingCondition = document.getElementById('customer_working_condition');
        const occupationGroup = document.getElementById('customer_occupation_group');
        const occupationInput = document.getElementById('customer_occupation');
        const otherOccupationGroup = document.getElementById('customer_other_occupation_group');
        const otherOccupationInput = document.getElementById('customer_other_occupation');
        const businessTypeGroup = document.getElementById('customer_business_type_group');
        const businessTypeInput = document.getElementById('customer_business_type');
        const provinceInput = document.getElementById('customer_province');
        const provinceDescInput = document.getElementById('customer_province_desc');
        const districtInput = document.getElementById('customer_district');
        const districtDescInput = document.getElementById('customer_district_desc');
        const subDistrictInput = document.getElementById('customer_subdistrict');
        const subDistrictDescInput = document.getElementById('customer_subdistrict_desc');
        const zipcodeInput = document.getElementById('customer_zipcode');
        const addressLine1Input = document.getElementById('customer_address_line1');
        const addressLine2Input = document.getElementById('customer_address_line2');
        const addressRemarkInput = document.getElementById('customer_address_remark');
        const addressesInput = document.getElementById('customer_addresses');
        const addressRows = document.getElementById('customerAddressRows');
        const addressEditor = document.getElementById('customerAddressEditor');
        const addressAddButton = document.getElementById('customerAddressAdd');
        const addressCancelButton = document.getElementById('customerAddressCancel');
        const addressCommitButton = document.getElementById('customerAddressCommit');
        const identityCardAddress = document.getElementById('customer_identity_card_address');
        const houseRegistrationAddress = document.getElementById('customer_house_registration_address');
        const currentAddress = document.getElementById('customer_current_address');
        const mailingAddress = document.getElementById('customer_mailing_address');
        const phoneInput = document.getElementById('customer_mobile');
        const phoneTypeInput = document.getElementById('customer_phone_type');
        const phoneRemarkInput = document.getElementById('customer_phone_note');
        const phonesInput = document.getElementById('customer_phones');
        const phoneRows = document.getElementById('customerPhoneRows');
        const phoneEditor = document.getElementById('customerPhoneEditor');
        const phoneAddButton = document.getElementById('customerPhoneAdd');
        const phoneCancelButton = document.getElementById('customerPhoneCancel');
        const phoneCommitButton = document.getElementById('customerPhoneCommit');
        const primaryPhoneInput = document.getElementById('customer_primary_phone');
        const monthlyIncomeInput = document.getElementById('customer_income');
        const monthlyExpenseInput = document.getElementById('customer_expense');
        const yearlyBonusInput = document.getElementById('customer_bonus');
        const netIncomeInput = document.getElementById('customer_net_income');
        const averageIncomeInput = document.getElementById('customer_average_income');
        const districtsUrl = config.urls.districts;
        const subDistrictsUrl = config.urls.subDistricts;
        const identityCardCheckUrl = config.urls.identityCardCheck;
        const searchableSelects = new Map();
        const searchableSelectOptions = {
            allowHTML: false,
            shouldSort: false,
            searchEnabled: true,
            searchFloor: 0,
            searchResultLimit: 100,
            searchFields: ['label'],
            fuseOptions: {
                threshold: 0,
                ignoreLocation: true,
            },
            position: 'bottom',
            itemSelectText: '',
            searchPlaceholderValue: config.messages.searchOption,
            noResultsText: config.messages.noSearchResults,
            noChoicesText: config.messages.noOptions,
        };

        [provinceInput, districtInput, subDistrictInput].forEach((select) => {
            if (select && window.Choices) {
                searchableSelects.set(select, new Choices(select, searchableSelectOptions));
            }
        });
        const identityLabels = config.identityNumberLabels;
        const requiredMessage = config.messages.required;
        const invalidNationalIdMessage = config.messages.invalidNationalId;
        const duplicateNationalIdMessage = config.messages.duplicateNationalId;
        const identityCheckFailedMessage = config.messages.identityCheckFailed;
        const saveFailedMessage = config.messages.saveFailed;
        const addressRequiredMessage = config.messages.addressRequired;
        const selectOptionText = config.messages.selectOption;
        const editAddressText = config.messages.editAddress;
        const deleteAddressText = config.messages.deleteAddress;
        const addAddressText = config.messages.addAddress;
        const saveAddressText = config.messages.saveAddress;
        const phoneRequiredMessage = config.messages.phoneRequired;
        const editPhoneText = config.messages.editPhone;
        const deletePhoneText = config.messages.deletePhone;
        const addPhoneText = config.messages.addPhone;
        const savePhoneText = config.messages.savePhone;
        let addresses = [];
        let editingAddressId = null;
        let phones = [];
        let editingPhoneId = null;
        let currentStep = 1;
        let maxReachedStep = 1;
        let isViewMode = false;
        let isEditMode = false;
        let editingCustomerNo = '';

        const setFieldError = (field, hasError, message = requiredMessage) => {
            const group = field.closest('.form-group');
            if (!group) {
                return;
            }

            group.classList.toggle('has-error', hasError);

            let errorMessage = group.querySelector('.customer-field-error');
            if (hasError && !errorMessage) {
                errorMessage = document.createElement('span');
                errorMessage.className = 'customer-field-error';
                group.querySelector('label')?.appendChild(errorMessage);
            }

            if (hasError && errorMessage) {
                errorMessage.textContent = message;
            } else if (!hasError) {
                errorMessage?.remove();
            }
        };

        const isValidThaiNationalId = (value) => {
            if (!/^\d{13}$/.test(value) || /^(\d)\1{12}$/.test(value)) {
                return false;
            }

            const digits = value.split('').map(Number);
            const sum = digits.slice(0, 12).reduce(
                (total, digit, index) => total + (digit * (13 - index)),
                0
            );

            return ((11 - (sum % 11)) % 10) === digits[12];
        };

        const validateIdentityNumber = () => {
            if (!identityInput) {
                return;
            }

            const isNationalId = identityType?.value === '1';
            const hasInvalidNationalId = isNationalId
                && identityInput.value !== ''
                && !isValidThaiNationalId(identityInput.value);

            identityInput.setCustomValidity(hasInvalidNationalId ? invalidNationalIdMessage : '');
        };

        const checkDuplicateNationalId = async () => {
            if (!identityInput || identityType?.value !== '1' || !identityInput.checkValidity()) {
                return true;
            }

            const url = new URL(identityCardCheckUrl, window.location.origin);
            url.searchParams.set('identity_card_id', identityInput.value);
            if (isEditMode && editingCustomerNo) {
                url.searchParams.set('customer_no', editingCustomerNo);
            }

            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(identityCheckFailedMessage);
            }

            const result = await response.json();
            const isDuplicate = Boolean(result.exists);
            identityInput.setCustomValidity(isDuplicate ? duplicateNationalIdMessage : '');
            setFieldError(identityInput, isDuplicate, duplicateNationalIdMessage);

            if (isDuplicate) {
                identityInput.focus();
            }

            return !isDuplicate;
        };

        const updateIdentityField = () => {
            const selectedType = identityType?.value || '';

            if (identityLabel) {
                identityLabel.textContent = identityLabels[selectedType]
                    || config.messages.identityDocumentNumber;
            }

            if (identityInput) {
                const isNationalId = selectedType === '1';
                identityInput.inputMode = isNationalId ? 'numeric' : 'text';
                identityInput.maxLength = isNationalId ? 13 : 20;
                if (isNationalId) {
                    identityInput.value = identityInput.value.replace(/\D/g, '').slice(0, 13);
                }
                validateIdentityNumber();

                if (identityInput.closest('.form-group')?.classList.contains('has-error')) {
                    setFieldError(
                        identityInput,
                        !identityInput.checkValidity(),
                        identityInput.validity.customError ? invalidNationalIdMessage : requiredMessage
                    );
                }
            }
        };

        const updateOccupationFields = () => {
            const selectedOption = workingCondition?.selectedOptions[0];
            const occupationRequired = Boolean(selectedOption?.value)
                && selectedOption.dataset.requiresOccupation === '1';

            [occupationGroup, businessTypeGroup].forEach((group) => {
                if (group) {
                    group.hidden = !occupationRequired;
                }
            });

            [occupationInput, businessTypeInput].forEach((input) => {
                if (input) {
                    input.disabled = !occupationRequired;
                    if (!occupationRequired) {
                        input.value = '';
                        setFieldError(input, false);
                    }
                }
            });

            updateOtherOccupationField();
        };

        const updateOtherOccupationField = () => {
            const selectedOccupation = occupationInput?.selectedOptions[0];
            const requiresDescription = !occupationInput?.disabled
                && Boolean(selectedOccupation?.value)
                && selectedOccupation.dataset.isOtherOccupation === '1';

            if (otherOccupationGroup) {
                otherOccupationGroup.hidden = !requiresDescription;
            }

            if (otherOccupationInput) {
                otherOccupationInput.disabled = !requiresDescription;
                otherOccupationInput.required = requiresDescription;
                if (!requiresDescription) {
                    otherOccupationInput.value = '';
                    setFieldError(otherOccupationInput, false);
                }
            }
        };

        const resetLocationSelect = (select) => {
            if (!select) return;
            select.options.length = 1;
            select.value = '';
            select.disabled = true;
            const choices = searchableSelects.get(select);
            choices?.refresh(false, false);
            choices?.disable();
            setFieldError(select, false);
        };

        const populateLocationSelect = (select, records, valueKey, labelKey, zipcodeKey = null) => {
            resetLocationSelect(select);
            records.forEach((record) => {
                const option = document.createElement('option');
                const zipcode = zipcodeKey ? (record[zipcodeKey] || '') : '';
                option.value = record[valueKey];
                option.textContent = zipcode
                    ? `${record[labelKey]} - ${zipcode}`
                    : record[labelKey];
                option.dataset.description = record[labelKey];
                if (zipcodeKey) option.dataset.zipcode = zipcode;
                select.appendChild(option);
            });
            select.disabled = false;
            const choices = searchableSelects.get(select);
            choices?.refresh(false, false);
            choices?.enable();
        };

        provinceInput?.addEventListener('change', async () => {
            provinceDescInput.value = provinceInput.selectedOptions[0]?.dataset.description || '';
            districtDescInput.value = '';
            subDistrictDescInput.value = '';
            zipcodeInput.value = '';
            resetLocationSelect(districtInput);
            resetLocationSelect(subDistrictInput);

            if (!provinceInput.value) return;

            const url = new URL(districtsUrl, window.location.origin);
            url.searchParams.set('province_code', provinceInput.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (response.ok) {
                populateLocationSelect(districtInput, await response.json(), 'DistrictCode', 'DistrictDesc');
            }
        });

        districtInput?.addEventListener('change', async () => {
            districtDescInput.value = districtInput.selectedOptions[0]?.dataset.description || '';
            subDistrictDescInput.value = '';
            zipcodeInput.value = '';
            resetLocationSelect(subDistrictInput);

            if (!provinceInput.value || !districtInput.value) return;

            const url = new URL(subDistrictsUrl, window.location.origin);
            url.searchParams.set('province_code', provinceInput.value);
            url.searchParams.set('district_code', districtInput.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (response.ok) {
                populateLocationSelect(
                    subDistrictInput,
                    await response.json(),
                    'SubDistrictCode',
                    'SubDistrictDesc',
                    'Zipcode'
                );
            }
        });

        subDistrictInput?.addEventListener('change', () => {
            const selectedOption = subDistrictInput.selectedOptions[0];
            subDistrictDescInput.value = selectedOption?.dataset.description || '';
            zipcodeInput.value = selectedOption?.dataset.zipcode || '';
            zipcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
        });

        const addressEditorFields = Array.from(addressEditor?.querySelectorAll('input, select, textarea') || []);

        const setAddressEditorOpen = (isOpen) => {
            addressEditor.hidden = !isOpen;
            addressEditorFields.forEach((field) => { field.disabled = !isOpen; });
            if (isOpen) {
                districtInput.disabled = !districtInput.value;
                subDistrictInput.disabled = !subDistrictInput.value;
                searchableSelects.get(provinceInput)?.enable();
                if (!districtInput.value) searchableSelects.get(districtInput)?.disable();
                if (!subDistrictInput.value) searchableSelects.get(subDistrictInput)?.disable();
            } else {
                [provinceInput, districtInput, subDistrictInput].forEach((field) => searchableSelects.get(field)?.disable());
            }
        };

        const resetAddressEditor = () => {
            editingAddressId = null;
            addressLine1Input.value = '';
            addressLine2Input.value = '';
            addressRemarkInput.value = '';
            zipcodeInput.value = '';
            provinceDescInput.value = '';
            districtDescInput.value = '';
            subDistrictDescInput.value = '';
            provinceInput.value = '';
            searchableSelects.get(provinceInput)?.refresh(false, false);
            resetLocationSelect(districtInput);
            resetLocationSelect(subDistrictInput);
            addressCommitButton.textContent = addAddressText;
            addressEditor?.querySelectorAll('.has-error').forEach((group) => group.classList.remove('has-error'));
        };

        const updateAddressAssignments = () => {
            [identityCardAddress, houseRegistrationAddress, currentAddress, mailingAddress].forEach((select) => {
                const selected = select.value;
                select.innerHTML = `<option value="">${selectOptionText}</option>`;
                addresses.forEach((address) => {
                    const option = document.createElement('option');
                    option.value = address.AddressId;
                    option.textContent = `${address.AddressId} - ${address.AddressLine1}`;
                    select.appendChild(option);
                });
                select.disabled = addresses.length === 0;
                if (addresses.some((address) => String(address.AddressId) === selected)) select.value = selected;
            });
        };

        const renderAddresses = () => {
            addressesInput.value = addresses.length ? JSON.stringify(addresses) : '';
            addressRows.innerHTML = '';

            if (addresses.length === 0) {
                const row = document.createElement('tr');
                row.className = 'customer-address-empty';
                row.innerHTML = `<td colspan="4">${config.messages.addressNoData}</td>`;
                addressRows.appendChild(row);
            } else {
                addresses.forEach((address) => {
                    const row = document.createElement('tr');
                    const numberCell = document.createElement('td');
                    const addressCell = document.createElement('td');
                    const remarkCell = document.createElement('td');
                    const actionsCell = document.createElement('td');
                    const actions = document.createElement('div');
                    const editButton = document.createElement('button');
                    const deleteButton = document.createElement('button');
                    numberCell.textContent = address.AddressId;
                    addressCell.textContent = [address.AddressLine1, address.AddressLine2, address.SubDistrictDesc, address.DistrictDesc, address.ProvinceDesc, address.ZipCode].filter(Boolean).join(' ');
                    remarkCell.textContent = address.Remark || '-';
                    actions.className = 'customer-address-row-actions';
                    editButton.type = deleteButton.type = 'button';
                    editButton.className = deleteButton.className = 'action-btn outline';
                    editButton.textContent = editAddressText;
                    deleteButton.textContent = deleteAddressText;
                    editButton.dataset.editAddress = address.AddressId;
                    deleteButton.dataset.deleteAddress = address.AddressId;
                    actions.append(editButton, deleteButton);
                    actionsCell.appendChild(actions);
                    row.append(numberCell, addressCell, remarkCell, actionsCell);
                    addressRows.appendChild(row);
                });
            }

            updateAddressAssignments();
        };

        const openAddressEditor = async (address = null) => {
            resetAddressEditor();
            setAddressEditorOpen(true);
            if (!address) return;

            editingAddressId = address.AddressId;
            addressCommitButton.textContent = saveAddressText;
            addressLine1Input.value = address.AddressLine1;
            addressLine2Input.value = address.AddressLine2 || '';
            addressRemarkInput.value = address.Remark || '';
            zipcodeInput.value = address.ZipCode;
            provinceDescInput.value = address.ProvinceDesc;
            districtDescInput.value = address.DistrictDesc;
            subDistrictDescInput.value = address.SubDistrictDesc;
            provinceInput.value = address.ProvinceCode;
            searchableSelects.get(provinceInput)?.refresh(false, false);

            const districtUrl = new URL(districtsUrl, window.location.origin);
            districtUrl.searchParams.set('province_code', address.ProvinceCode);
            const districtResponse = await fetch(districtUrl, { headers: { Accept: 'application/json' } });
            if (districtResponse.ok) {
                populateLocationSelect(districtInput, await districtResponse.json(), 'DistrictCode', 'DistrictDesc');
                districtInput.value = address.DistrictCode;
                searchableSelects.get(districtInput)?.refresh(false, false);
            }

            const subDistrictUrl = new URL(subDistrictsUrl, window.location.origin);
            subDistrictUrl.searchParams.set('province_code', address.ProvinceCode);
            subDistrictUrl.searchParams.set('district_code', address.DistrictCode);
            const subDistrictResponse = await fetch(subDistrictUrl, { headers: { Accept: 'application/json' } });
            if (subDistrictResponse.ok) {
                populateLocationSelect(subDistrictInput, await subDistrictResponse.json(), 'SubDistrictCode', 'SubDistrictDesc', 'Zipcode');
                subDistrictInput.value = address.SubDistrictCode;
                searchableSelects.get(subDistrictInput)?.refresh(false, false);
            }
        };

        addressAddButton?.addEventListener('click', () => openAddressEditor());
        addressCancelButton?.addEventListener('click', () => {
            resetAddressEditor();
            setAddressEditorOpen(false);
        });
        addressCommitButton?.addEventListener('click', () => {
            const requiredFields = addressEditor.querySelectorAll('[required]:not(:disabled)');
            const invalidFields = Array.from(requiredFields).filter((field) => {
                const invalid = !field.checkValidity();
                setFieldError(field, invalid);
                return invalid;
            });
            if (invalidFields.length) return invalidFields[0].focus();

            const address = {
                AddressId: editingAddressId || (Math.max(0, ...addresses.map((item) => item.AddressId)) + 1),
                AddressLine1: addressLine1Input.value.trim(),
                AddressLine2: addressLine2Input.value.trim(),
                ProvinceCode: provinceInput.value,
                ProvinceDesc: provinceDescInput.value,
                DistrictCode: districtInput.value,
                DistrictDesc: districtDescInput.value,
                SubDistrictCode: subDistrictInput.value,
                SubDistrictDesc: subDistrictDescInput.value,
                ZipCode: zipcodeInput.value.trim(),
                Remark: addressRemarkInput.value.trim(),
            };
            const index = addresses.findIndex((item) => item.AddressId === editingAddressId);
            if (index >= 0) addresses[index] = address; else addresses.push(address);
            renderAddresses();
            resetAddressEditor();
            setAddressEditorOpen(false);
        });
        addressRows?.addEventListener('click', (event) => {
            const editId = Number(event.target.dataset.editAddress || 0);
            const deleteId = Number(event.target.dataset.deleteAddress || 0);
            if (editId) openAddressEditor(addresses.find((address) => address.AddressId === editId));
            if (deleteId) {
                addresses = addresses.filter((address) => address.AddressId !== deleteId);
                renderAddresses();
            }
        });

        const phoneEditorFields = Array.from(phoneEditor?.querySelectorAll('input, select, textarea') || []);
        const setPhoneEditorOpen = (isOpen) => {
            phoneEditor.hidden = !isOpen;
            phoneEditorFields.forEach((field) => { field.disabled = !isOpen; });
        };
        const resetPhoneEditor = () => {
            editingPhoneId = null;
            phoneInput.value = '';
            phoneTypeInput.value = '';
            phoneRemarkInput.value = '';
            phoneCommitButton.textContent = addPhoneText;
            phoneEditor?.querySelectorAll('.has-error').forEach((group) => group.classList.remove('has-error'));
        };
        const renderPhones = () => {
            const selectedPrimary = primaryPhoneInput.value;
            phonesInput.value = phones.length ? JSON.stringify(phones) : '';
            phoneRows.innerHTML = '';
            primaryPhoneInput.innerHTML = `<option value="">${selectOptionText}</option>`;

            if (!phones.length) {
                phoneRows.innerHTML = `<tr class="customer-phone-empty"><td colspan="5">${config.messages.phoneNoData}</td></tr>`;
            }

            phones.forEach((phone) => {
                const row = document.createElement('tr');
                const typeText = phoneTypeInput.querySelector(`option[value="${CSS.escape(phone.PhoneType)}"]`)?.textContent || phone.PhoneType;
                row.innerHTML = `<td>${phone.PhoneId}</td><td></td><td></td><td></td><td></td>`;
                row.children[1].textContent = phone.Phone;
                row.children[2].textContent = typeText;
                row.children[3].textContent = phone.Remark || '-';
                const actions = document.createElement('div');
                actions.className = 'customer-phone-row-actions';
                const editButton = document.createElement('button');
                const deleteButton = document.createElement('button');
                editButton.type = deleteButton.type = 'button';
                editButton.className = deleteButton.className = 'action-btn outline';
                editButton.textContent = editPhoneText;
                deleteButton.textContent = deletePhoneText;
                editButton.dataset.editPhone = phone.PhoneId;
                deleteButton.dataset.deletePhone = phone.PhoneId;
                actions.append(editButton, deleteButton);
                row.children[4].appendChild(actions);
                phoneRows.appendChild(row);

                const option = document.createElement('option');
                option.value = phone.PhoneId;
                option.textContent = `${phone.PhoneId} - ${phone.Phone}`;
                primaryPhoneInput.appendChild(option);
            });

            primaryPhoneInput.disabled = phones.length === 0;
            if (phones.some((phone) => String(phone.PhoneId) === selectedPrimary)) primaryPhoneInput.value = selectedPrimary;
        };
        const openPhoneEditor = (phone = null) => {
            resetPhoneEditor();
            setPhoneEditorOpen(true);
            if (!phone) return;
            editingPhoneId = phone.PhoneId;
            phoneInput.value = phone.Phone;
            phoneTypeInput.value = phone.PhoneType;
            phoneRemarkInput.value = phone.Remark || '';
            phoneCommitButton.textContent = savePhoneText;
        };
        phoneAddButton?.addEventListener('click', () => openPhoneEditor());
        phoneCancelButton?.addEventListener('click', () => {
            resetPhoneEditor();
            setPhoneEditorOpen(false);
        });
        phoneCommitButton?.addEventListener('click', () => {
            const requiredFields = phoneEditor.querySelectorAll('[required]:not(:disabled)');
            const invalidFields = Array.from(requiredFields).filter((field) => {
                const invalid = !field.checkValidity();
                setFieldError(field, invalid);
                return invalid;
            });
            if (invalidFields.length) return invalidFields[0].focus();
            const phone = {
                PhoneId: editingPhoneId || (Math.max(0, ...phones.map((item) => item.PhoneId)) + 1),
                Phone: phoneInput.value.trim(),
                PhoneType: phoneTypeInput.value,
                Remark: phoneRemarkInput.value.trim(),
            };
            const index = phones.findIndex((item) => item.PhoneId === editingPhoneId);
            if (index >= 0) phones[index] = phone; else phones.push(phone);
            renderPhones();
            resetPhoneEditor();
            setPhoneEditorOpen(false);
        });
        phoneRows?.addEventListener('click', (event) => {
            const editId = Number(event.target.dataset.editPhone || 0);
            const deleteId = Number(event.target.dataset.deletePhone || 0);
            if (editId) openPhoneEditor(phones.find((phone) => phone.PhoneId === editId));
            if (deleteId) {
                phones = phones.filter((phone) => phone.PhoneId !== deleteId);
                renderPhones();
            }
        });

        const calculateIncome = () => {
            const sourceInputs = [monthlyIncomeInput, monthlyExpenseInput, yearlyBonusInput];
            const hasValue = sourceInputs.some((input) => input?.value !== '');

            if (!hasValue) {
                netIncomeInput.value = '';
                averageIncomeInput.value = '';
                return;
            }

            const parseAmount = (value) => Number(String(value || '0').replaceAll(',', '')) || 0;
            const monthlyIncome = parseAmount(monthlyIncomeInput?.value);
            const monthlyExpense = parseAmount(monthlyExpenseInput?.value);
            const yearlyBonus = parseAmount(yearlyBonusInput?.value);
            const yearlyNetIncome = ((monthlyIncome - monthlyExpense) * 12) + yearlyBonus;
            const amountFormatter = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            netIncomeInput.value = amountFormatter.format(yearlyNetIncome);
            averageIncomeInput.value = amountFormatter.format(yearlyNetIncome / 12);
        };

        [monthlyIncomeInput, monthlyExpenseInput, yearlyBonusInput].forEach((input) => {
            input?.addEventListener('input', () => {
                const rawValue = input.value.replaceAll(',', '').replace(/[^\d.]/g, '');
                const [integerPart = '', ...decimalParts] = rawValue.split('.');
                const decimalPart = decimalParts.join('').slice(0, 2);
                const formattedInteger = integerPart
                    ? Number(integerPart).toLocaleString('en-US')
                    : '';

                input.value = rawValue.includes('.')
                    ? `${formattedInteger}.${decimalPart}`
                    : formattedInteger;
                calculateIncome();
            });
        });

        const resetCustomerForm = () => {
            form.reset();
            addresses = [];
            phones = [];
            currentStep = 1;
            maxReachedStep = 1;
            editingAddressId = null;
            editingPhoneId = null;
            form.querySelectorAll('input, select, textarea').forEach((field) => { field.disabled = false; });
            resetAddressEditor();
            setAddressEditorOpen(false);
            renderAddresses();
            resetPhoneEditor();
            setPhoneEditorOpen(false);
            renderPhones();
            updateIdentityField();
            updateOccupationFields();
            calculateIncome();
            updateStep();
        };

        window.prepareCustomerHistoryCreateForm = () => {
            isViewMode = false;
            isEditMode = false;
            editingCustomerNo = '';
            form.action = storeUrl;
            modal?.classList.remove('is-view-mode');
            if (modalTitle) modalTitle.textContent = createModalTitle;
            resetCustomerForm();
        };

        window.openCustomerHistoryEdit = async (detailUrl, updateUrl) => {
            isViewMode = false;
            isEditMode = true;
            modal?.classList.remove('is-view-mode');
            if (modalTitle) modalTitle.textContent = config.messages.editTitle;
            modal.style.display = 'flex';
            modal.offsetHeight;
            modal.classList.add('show', 'is-loading');

            try {
                const response = await fetch(detailUrl, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error(config.messages.detailLoadFailed);

                const result = await response.json();
                resetCustomerForm();
                isViewMode = false;
                isEditMode = true;
                editingCustomerNo = String(result.customer.CustomerNo || '');
                form.action = updateUrl;
                if (modalTitle) modalTitle.textContent = config.messages.editTitle;

                Object.entries(result.customer).forEach(([name, value]) => {
                    const field = form.elements.namedItem(name);
                    if (!(field instanceof HTMLElement)) return;
                    field.value = field.type === 'date' && value ? String(value).slice(0, 10) : (value ?? '');
                });
                form.elements.namedItem('EmailRemark').value = result.email_remark ?? '';
                form.elements.namedItem('Comment').value = result.comment ?? '';
                addresses = result.addresses || [];
                phones = result.phones || [];
                renderAddresses();
                renderPhones();
                identityCardAddress.value = String(result.customer.IdentityCardAddressId ?? '');
                houseRegistrationAddress.value = String(result.customer.HouseRegistrationAddressId ?? '');
                currentAddress.value = String(result.customer.CurrentAddressId ?? '');
                mailingAddress.value = String(result.customer.MailingAddressId ?? '');
                primaryPhoneInput.value = String(result.customer.MobileTelephoneId ?? '');
                updateIdentityField();
                updateOccupationFields();
                calculateIncome();
                currentStep = 1;
                maxReachedStep = steps.length;
                updateStep();
            } catch (error) {
                modal.classList.remove('show');
                modal.style.display = 'none';
                window.alert(error.message || config.messages.detailLoadFailed);
            } finally {
                modal.classList.remove('is-loading');
            }
        };

        window.openCustomerHistoryDetail = async (url) => {
            isViewMode = true;
            isEditMode = false;
            editingCustomerNo = '';
            modal?.classList.add('is-view-mode');
            if (modalTitle) modalTitle.textContent = config.messages.detailTitle;
            modal.style.display = 'flex';
            modal.offsetHeight;
            modal.classList.add('show');
            modal.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!response.ok) throw new Error(config.messages.detailLoadFailed);

                const result = await response.json();
                resetCustomerForm();
                isViewMode = true;
                modal.classList.add('is-view-mode');
                if (modalTitle) modalTitle.textContent = config.messages.detailTitle;

                Object.entries(result.customer).forEach(([name, value]) => {
                    const field = form.elements.namedItem(name);
                    if (!(field instanceof HTMLElement)) return;
                    field.value = field.type === 'date' && value
                        ? String(value).slice(0, 10)
                        : (value ?? '');
                });

                form.elements.namedItem('EmailRemark').value = result.email_remark ?? '';
                form.elements.namedItem('Comment').value = result.comment ?? '';
                addresses = result.addresses || [];
                phones = result.phones || [];
                renderAddresses();
                renderPhones();
                identityCardAddress.value = String(result.customer.IdentityCardAddressId ?? '');
                houseRegistrationAddress.value = String(result.customer.HouseRegistrationAddressId ?? '');
                currentAddress.value = String(result.customer.CurrentAddressId ?? '');
                mailingAddress.value = String(result.customer.MailingAddressId ?? '');
                primaryPhoneInput.value = String(result.customer.MobileTelephoneId ?? '');
                updateIdentityField();
                updateOccupationFields();
                calculateIncome();
                form.querySelectorAll('input, select, textarea').forEach((field) => { field.disabled = true; });
                currentStep = 1;
                maxReachedStep = steps.length;
                updateStep();
            } catch (error) {
                modal.classList.remove('show');
                modal.style.display = 'none';
                window.alert(error.message || config.messages.detailLoadFailed);
            } finally {
                modal.classList.remove('is-loading');
            }
        };

        const updateStep = () => {
            steps.forEach((step) => {
                step.classList.toggle('is-active', Number(step.dataset.customerStep) === currentStep);
            });
            indicators.forEach((indicator) => {
                const stepNumber = Number(indicator.dataset.step);
                const isAccessible = isViewMode || stepNumber <= maxReachedStep;
                indicator.classList.toggle('active', stepNumber === currentStep);
                indicator.classList.toggle('completed', isAccessible && stepNumber !== currentStep);
                indicator.disabled = !isAccessible;
                indicator.setAttribute('aria-current', stepNumber === currentStep ? 'step' : 'false');
            });
            cancelButton.classList.toggle('is-hidden', currentStep > 1);
            previousButton.disabled = currentStep === 1;
            previousButton.classList.toggle('is-hidden', currentStep === 1);
            nextButton.classList.toggle('is-hidden', currentStep === steps.length);
            saveButton.classList.toggle('is-hidden', currentStep !== steps.length || isViewMode);
            document.querySelector('#customerHistoryFormModal .modal-body')?.scrollTo({ top: 0, behavior: 'smooth' });
        };

        nextButton?.addEventListener('click', async () => {
            if (currentStep === 2 && addresses.length === 0) {
                window.alert(addressRequiredMessage);
                return;
            }
            if (currentStep === 3 && phones.length === 0) {
                window.alert(phoneRequiredMessage);
                return;
            }
            const activeStep = steps.find((step) => Number(step.dataset.customerStep) === currentStep);
            const requiredFields = activeStep?.querySelectorAll('[required]:not(:disabled)') || [];
            const invalidFields = [];

            for (const field of requiredFields) {
                if (field === identityInput) validateIdentityNumber();
                const isInvalid = !field.checkValidity();
                setFieldError(
                    field,
                    isInvalid,
                    field === identityInput && field.validity.customError
                        ? invalidNationalIdMessage
                        : requiredMessage
                );
                if (isInvalid) invalidFields.push(field);
            }

            if (invalidFields.length > 0) {
                invalidFields[0].focus();
                return;
            }

            if (!isViewMode && currentStep === 1 && identityType?.value === '1') {
                nextButton.disabled = true;

                try {
                    if (!await checkDuplicateNationalId()) {
                        return;
                    }
                } catch (error) {
                    window.alert(error.message || identityCheckFailedMessage);
                    return;
                } finally {
                    nextButton.disabled = false;
                }
            }

            if (currentStep < steps.length) {
                currentStep += 1;
                maxReachedStep = Math.max(maxReachedStep, currentStep);
                updateStep();
            }
        });

        indicators.forEach((indicator) => {
            indicator.addEventListener('click', () => {
                const targetStep = Number(indicator.dataset.step);
                if (!isViewMode && targetStep > maxReachedStep) return;
                currentStep = targetStep;
                updateStep();
            });
        });

        previousButton?.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep -= 1;
                updateStep();
            }
        });

        identityType?.addEventListener('change', updateIdentityField);
        identityInput?.addEventListener('input', () => {
            if (identityType?.value === '1') {
                identityInput.value = identityInput.value.replace(/\D/g, '').slice(0, 13);
            }
            validateIdentityNumber();
            if (identityInput.closest('.form-group')?.classList.contains('has-error')) {
                setFieldError(
                    identityInput,
                    !identityInput.checkValidity(),
                    identityInput.validity.customError ? invalidNationalIdMessage : requiredMessage
                );
            }
        });
        workingCondition?.addEventListener('change', updateOccupationFields);
        occupationInput?.addEventListener('change', updateOtherOccupationField);
        form?.querySelectorAll('[required], [data-conditional-required]').forEach((field) => {
            const clearResolvedError = () => {
                if (field.disabled || field.checkValidity()) {
                    setFieldError(field, false);
                }
            };
            field.addEventListener('input', clearResolvedError);
            field.addEventListener('change', clearResolvedError);
        });
        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            saveButton.disabled = true;

            try {
                const formData = new FormData(form);
                if (isEditMode) formData.set('_method', 'PUT');
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        const [fieldName, messages] = Object.entries(result.errors)[0] || [];
                        const field = fieldName ? form.elements.namedItem(fieldName) : null;
                        if (field instanceof HTMLElement) {
                            setFieldError(field, true, messages?.[0] || requiredMessage);
                        }
                        throw new Error(messages?.[0] || result.message || saveFailedMessage);
                    }

                    throw new Error(result.message || saveFailedMessage);
                }

                window.alert(`${result.message}\nCustomerNo: ${result.customer_no}`);
                await window.refreshCustomerHistoryList?.(window.location.href);
                form.reset();
                addresses = [];
                phones = [];
                currentStep = 1;
                maxReachedStep = 1;
                resetAddressEditor();
                setAddressEditorOpen(false);
                renderAddresses();
                resetPhoneEditor();
                setPhoneEditorOpen(false);
                renderPhones();
                updateIdentityField();
                updateOccupationFields();
                updateStep();

                const formModal = document.getElementById('customerHistoryFormModal');
                formModal?.classList.remove('show');
                if (formModal) formModal.style.display = 'none';
            } catch (error) {
                window.alert(error.message || saveFailedMessage);
            } finally {
                saveButton.disabled = false;
            }
        });
        updateIdentityField();
        updateOccupationFields();
        setAddressEditorOpen(false);
        renderAddresses();
        setPhoneEditorOpen(false);
        renderPhones();
        updateStep();
    })();
</script>
