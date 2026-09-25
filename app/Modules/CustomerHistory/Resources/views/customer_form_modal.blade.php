<div id="customerHistoryFormModal" class="modal">
    <div class="modal-content modal-lg customer-form-modal">
        <form id="customerHistoryForm" autocomplete="off">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title">{{ __('customerhistory::messages.form.title') }}</h3>
                <button type="button" class="close-btn" id="closeCustomerHistoryForm" aria-label="Close modal">&times;</button>
            </div>

            <div class="modal-body scrollable">
                <div class="wizard-steps customer-history-steps" aria-label="{{ __('customerhistory::messages.form.steps.label') }}">
                    <div class="wizard-step active" data-step="1">
                        <div class="wizard-step-icon">1</div>
                        <div class="wizard-step-label">{{ __('customerhistory::messages.form.steps.personal') }}</div>
                    </div>
                    <div class="wizard-step" data-step="2">
                        <div class="wizard-step-icon">2</div>
                        <div class="wizard-step-label">{{ __('customerhistory::messages.form.steps.address') }}</div>
                    </div>
                    <div class="wizard-step" data-step="3">
                        <div class="wizard-step-icon">3</div>
                        <div class="wizard-step-label">{{ __('customerhistory::messages.form.steps.contact') }}</div>
                    </div>
                    <div class="wizard-step" data-step="4">
                        <div class="wizard-step-icon">4</div>
                        <div class="wizard-step-label">{{ __('customerhistory::messages.form.steps.income') }}</div>
                    </div>
                    <div class="wizard-step" data-step="5">
                        <div class="wizard-step-icon">5</div>
                        <div class="wizard-step-label">{{ __('customerhistory::messages.form.steps.profile') }}</div>
                    </div>
                </div>

                <section class="customer-form-section customer-form-step is-active" data-customer-step="1">
                    <div class="form-grid customer-form-grid">
                        <div class="form-group col-2">
                            <label for="customer_title">{{ __('customerhistory::messages.form.personal.title_label') }}</label>
                            <input id="customer_title" name="TitleCode" type="text">
                        </div>
                        <div class="form-group col-5">
                            <label for="customer_firstname">{{ __('customerhistory::messages.form.personal.firstname') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_firstname" name="Firstname" type="text" required>
                        </div>
                        <div class="form-group col-5">
                            <label for="customer_lastname">{{ __('customerhistory::messages.form.personal.lastname') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_lastname" name="Lastname" type="text" required>
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_nickname">{{ __('customerhistory::messages.form.personal.nickname') }}</label>
                            <input id="customer_nickname" name="Nickname" type="text" maxlength="10">
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_birth_date">{{ __('customerhistory::messages.form.personal.birth_date') }}</label>
                            <input id="customer_birth_date" name="BirthDate" type="date">
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_gender">{{ __('customerhistory::messages.form.personal.gender') }}</label>
                            <input id="customer_gender" name="GenderCode" type="text">
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_nationality">{{ __('customerhistory::messages.form.personal.nationality') }}</label>
                            <input id="customer_nationality" name="Nationality" type="text" value="ไทย">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_identity">{{ __('customerhistory::messages.form.personal.identity_card') }}</label>
                            <input id="customer_identity" name="IdentityCardId" type="text" inputmode="numeric" maxlength="13">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_identity_type">{{ __('customerhistory::messages.form.personal.identity_type') }}</label>
                            <input id="customer_identity_type" name="IdentityCardTypeCode" type="text">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_identity_issuer">{{ __('customerhistory::messages.form.personal.identity_issuer') }}</label>
                            <input id="customer_identity_issuer" name="IdentityCardIssuer" type="text">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_identity_effective">{{ __('customerhistory::messages.form.personal.identity_effective_date') }}</label>
                            <input id="customer_identity_effective" name="IdentityCardEffectiveDate" type="date">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_identity_expire">{{ __('customerhistory::messages.form.personal.identity_expire_date') }}</label>
                            <input id="customer_identity_expire" name="IdentityCardExpireDate" type="date">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_marital_status">{{ __('customerhistory::messages.form.personal.marital_status') }}</label>
                            <input id="customer_marital_status" name="MaritalStatusCode" type="text">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_race">{{ __('customerhistory::messages.form.personal.race') }}</label>
                            <input id="customer_race" name="Race" type="text">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_working_condition">{{ __('customerhistory::messages.form.profile.working_condition') }}</label>
                            <input id="customer_working_condition" name="WorkingConditionId" type="text" inputmode="numeric">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_occupation">{{ __('customerhistory::messages.form.profile.occupation') }}</label>
                            <input id="customer_occupation" name="OccupationDesc" type="text">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_business_type">{{ __('customerhistory::messages.form.profile.business_type') }}</label>
                            <input id="customer_business_type" name="TypeOfBusinessName" type="text">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_address_type">{{ __('customerhistory::messages.form.address.type') }}</label>
                            <input id="customer_address_type" name="AddressTypeCode" type="text" inputmode="numeric">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_bank_code">{{ __('customerhistory::messages.form.bank.code') }}</label>
                            <input id="customer_bank_code" name="BankCode" type="text" maxlength="5">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_bank_branch">{{ __('customerhistory::messages.form.bank.branch') }}</label>
                            <input id="customer_bank_branch" name="BankBookBranch" type="text" maxlength="50">
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_bank_account">{{ __('customerhistory::messages.form.bank.account') }}</label>
                            <input id="customer_bank_account" name="BankBookCode" type="text" inputmode="numeric" maxlength="20">
                        </div>
                    </div>
                </section>

                <section class="customer-form-section customer-form-step" data-customer-step="2">
                    <div class="form-grid customer-form-grid">
                        <div class="form-group col-6">
                            <label for="customer_address_line1">{{ __('customerhistory::messages.form.address.line1') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_address_line1" name="AddressLine1" type="text" required>
                        </div>
                        <div class="form-group col-6">
                            <label for="customer_address_line2">{{ __('customerhistory::messages.form.address.line2') }} <span class="required-asterisk">*</span></label>
                            <input id="customer_address_line2" name="AddressLine2" type="text" required>
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_province">{{ __('customerhistory::messages.form.address.province') }}</label>
                            <input id="customer_province" name="ProvinceDesc" type="text">
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_district">{{ __('customerhistory::messages.form.address.district') }}</label>
                            <input id="customer_district" name="DistrictDesc" type="text">
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_subdistrict">{{ __('customerhistory::messages.form.address.subdistrict') }}</label>
                            <input id="customer_subdistrict" name="SubDistrictDesc" type="text">
                        </div>
                        <div class="form-group col-3">
                            <label for="customer_zipcode">{{ __('customerhistory::messages.form.address.zipcode') }}</label>
                            <input id="customer_zipcode" name="ZipCode" type="text" inputmode="numeric" maxlength="10">
                        </div>
                        <div class="form-group col-12">
                            <label for="customer_address_remark">{{ __('customerhistory::messages.form.address.address_remark') }}</label>
                            <textarea id="customer_address_remark" name="AddressRemark" rows="3"></textarea>
                        </div>
                    </div>
                </section>

                <section class="customer-form-section customer-form-step" data-customer-step="3">
                    <div class="form-grid customer-form-grid">
                        <div class="form-group col-6">
                            <label for="customer_mobile">{{ __('customerhistory::messages.form.contact.phone') }}</label>
                            <input id="customer_mobile" name="Mobile" type="tel" inputmode="numeric" maxlength="15">
                        </div>
                        <div class="form-group col-6">
                            <label for="customer_phone_type">{{ __('customerhistory::messages.form.contact.phone_type') }}</label>
                            <input id="customer_phone_type" name="PhoneType" type="text">
                        </div>
                        <div class="form-group col-12">
                            <label for="customer_phone_note">{{ __('customerhistory::messages.form.contact.phone_note') }}</label>
                            <textarea id="customer_phone_note" name="PhoneRemark" rows="2"></textarea>
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
                                <input id="customer_income" name="MonthlyIncomeAmount" type="number" min="0" step="0.01">
                                <span>{{ __('customerhistory::messages.form.income.monthly_unit') }}</span>
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_expense">{{ __('customerhistory::messages.form.income.monthly_expense') }}</label>
                            <div class="customer-input-unit">
                                <input id="customer_expense" name="MonthlyExpenseAmount" type="number" min="0" step="0.01">
                                <span>{{ __('customerhistory::messages.form.income.monthly_unit') }}</span>
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_bonus">{{ __('customerhistory::messages.form.income.yearly_bonus') }}</label>
                            <div class="customer-input-unit">
                                <input id="customer_bonus" name="YearlyBonusAmount" type="number" min="0" step="0.01">
                                <span>{{ __('customerhistory::messages.form.income.yearly_unit') }}</span>
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_net_income">{{ __('customerhistory::messages.form.income.net_income') }}</label>
                            <div class="customer-input-unit">
                                <input id="customer_net_income" type="number" min="0" step="0.01" readonly>
                                <span>{{ __('customerhistory::messages.form.income.yearly_unit') }}</span>
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <label for="customer_average_income">{{ __('customerhistory::messages.form.income.average_income') }}</label>
                            <div class="customer-input-unit">
                                <input id="customer_average_income" type="number" min="0" step="0.01" readonly>
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
                    <button type="button" class="action-btn outline" id="cancelCustomerHistoryForm">
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

<script>
    (() => {
        const form = document.getElementById('customerHistoryForm');
        const steps = Array.from(document.querySelectorAll('[data-customer-step]'));
        const indicators = Array.from(document.querySelectorAll('.customer-history-steps .wizard-step'));
        const previousButton = document.getElementById('customerFormPrevious');
        const nextButton = document.getElementById('customerFormNext');
        const saveButton = document.getElementById('customerFormSave');
        let currentStep = 1;

        const updateStep = () => {
            steps.forEach((step) => {
                step.classList.toggle('is-active', Number(step.dataset.customerStep) === currentStep);
            });
            indicators.forEach((indicator) => {
                const stepNumber = Number(indicator.dataset.step);
                indicator.classList.toggle('active', stepNumber === currentStep);
                indicator.classList.toggle('completed', stepNumber < currentStep);
            });
            previousButton.disabled = currentStep === 1;
            previousButton.classList.toggle('is-hidden', currentStep === 1);
            nextButton.classList.toggle('is-hidden', currentStep === steps.length);
            saveButton.classList.toggle('is-hidden', currentStep !== steps.length);
            document.querySelector('#customerHistoryFormModal .modal-body')?.scrollTo({ top: 0, behavior: 'smooth' });
        };

        nextButton?.addEventListener('click', () => {
            const activeStep = steps.find((step) => Number(step.dataset.customerStep) === currentStep);
            const requiredFields = activeStep?.querySelectorAll('[required]') || [];
            for (const field of requiredFields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return;
                }
            }
            if (currentStep < steps.length) {
                currentStep += 1;
                updateStep();
            }
        });

        previousButton?.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep -= 1;
                updateStep();
            }
        });

        form?.addEventListener('submit', (event) => event.preventDefault());
        updateStep();
    })();
</script>