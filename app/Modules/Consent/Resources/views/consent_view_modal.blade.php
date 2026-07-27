<!-- Modal สำหรับดูเอกสารใบยินยอม -->
<div id="viewConsentModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header view-modal-header">
            <h3 class="view-modal-title">
                {{ __('consent::messages.modal.view.title') }}
            </h3>
            <button type="button" class="close-btn view-modal-close" id="closeViewConsentModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body scrollable view-modal-body">
                <div id="viewConsentContent" class="view-consent-content">
                    <!-- Full template: placeholders will be filled by JS -->
                    <div class="consent-header">
                        <div>
                            <div class="consent-header__title">{{ __('consent::messages.modal.view.company_name') }}</div>
                            <div class="consent-header__subtitle">{{ __('consent::messages.modal.view.view_document') }}</div>
                            <div class="consent-header__meta">App No.: <span id="modal_app_no">-</span></div>
                        </div>
                        <div class="consent-header__right">
                            <div class="consent-meta">วันที่: <span id="modal_app_date">-</span></div>
                            <div class="consent-appno-row">
                                <span class="meta-label">App No.</span>
                                <div id="modal_app_no_boxes"></div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel--green">
                        <h4 class="section-title section-title--green">{{ __('consent::messages.modal.form.step1.sections.company_officer') }}</h4>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.officer_group') }}</td>
                                <td class="value" id="modal_officer_group">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.product_type') }}</td>
                                <td class="value" id="modal_product_type">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.officer_name') }}</td>
                                <td class="value" id="modal_officer_name">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.common.phone_number') }}</td>
                                <td class="value" id="modal_officer_phone">-</td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel">
                        <h4 class="section-title section-title--accent">{{ __('consent::messages.modal.form.step1.sections.personal_info') }}</h4>
                        <div class="consent-photo-inline">
                            <a id="modal_applicant_photo_link" class="consent-photo-inline__image-wrap" href="#" target="_blank" rel="noopener" title="{{ __('consent::messages.modal.form.attachment.applicant_photo.alt') }}">
                                <img id="modal_applicant_photo" class="consent-photo-inline__image" src="" alt="{{ __('consent::messages.modal.form.attachment.applicant_photo.alt') }}">
                            </a>
                        </div>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.title') }} - {{ __('consent::messages.modal.form.step1.fields.name_th') }}</td>
                                <td class="value"><span id="modal_name_th">-</span></td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.name_en') }}</td>
                                <td class="value value--uppercase" id="modal_name_en">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.id_card_number') }}</td>
                                <td class="value" id="modal_id_card">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.birthdate') }}</td>
                                <td class="value" id="modal_birthdate">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.nationality') }}</td>
                                <td class="value" id="modal_nationality">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.marital_status') }}</td>
                                <td class="value" id="modal_marital_status">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step1.fields.education') }}</td>
                                <td class="value" id="modal_education">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step3.fields.occupation') }}:</td>
                                <td class="value" id="modal_occupation">-</td>
                            </tr>
                            <tr id="modal_career_field_row" style="display:none;">
                                <td class="label">{{ __('consent::messages.modal.form.step3.fields.career_field') }}:</td>
                                <td class="value" id="modal_career_field">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step4.fields.income') }}:</td>
                                <td class="value value--strong" id="modal_income">-</td>
                            </tr>
                            <tr id="modal_extra_income_row" style="display:none;">
                                <td class="label">{{ __('consent::messages.modal.form.step4.placeholders.extra_income') }}:</td>
                                <td class="value" id="modal_extra_income">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step4.fields.extra_income_source') }}:</td>
                                <td class="value" id="modal_extra_income_source">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step4.fields.income_country') }}:</td>
                                <td class="value" id="modal_income_country">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step4.fields.has_other_debts') }}:</td>
                                <td class="value" id="modal_has_other_debts">-</td>
                            </tr>
                            <tr id="modal_other_debt_installment_row" style="display:none;">
                                <td class="label">{{ __('consent::messages.modal.form.step4.fields.other_debt_installment') }}</td>
                                <td class="value" id="modal_other_debt_installment">-</td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel panel--blue">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.step4.fields.existing_loan_disclosure') }}</h4>
                        <div class="consent-detail-block">
                            <p class="consent-claim"><strong>{{ __('consent::messages.modal.form.step4.questions.existing_loan') }}</strong></p>
                            <p class="consent-value" id="modal_has_existing_loan">-</p>
                            <div id="modal_existing_loan_details" style="display:none;">
                                <p><strong>{{ __('consent::messages.modal.form.step4.fields.existing_loan_institution_count') }}:</strong> <span id="modal_existing_loan_count">-</span></p>
                                <p><strong>{{ __('consent::messages.modal.form.step4.fields.existing_loan_total_amount') }}:</strong> <span id="modal_existing_loan_total">-</span></p>
                            </div>
                        </div>
                        <div class="panel panel--yellow panel--note">
                            <p class="consent-note">{{ __('consent::messages.modal.form.step4.notes.existing_loan_warning') }}</p>
                        </div>
                    </div>

                    <div class="panel panel--pink">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.step3.sections.address') }}</h4>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step2.fields.residence_status') }}</td>
                                <td class="value" id="modal_residence_status">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step2.sections.current_address') }}</td>
                                <td class="value" id="modal_current_address">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.common.phone_home') }}</td>
                                <td class="value" id="modal_phone_home">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.common.phone_number') }}</td>
                                <td class="value" id="modal_phone_mobile">-</td>
                            </tr>
                            <tr>
                                <td class="label">E-mail:</td>
                                <td class="value" id="modal_email">-</td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel panel--blue">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.step3.sections.current_workplace') }}</h4>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label label--w30">{{ __('consent::messages.modal.form.step3.fields.company_name') }}</td>
                                <td class="value" id="modal_company_name">-</td>
                            </tr>
                            <tr>
                                <td class="label label--w30">{{ __('consent::messages.modal.form.step3.fields.business_type') }}</td>
                                <td class="value" id="modal_business_type">-</td>
                            </tr>
                            <tr>
                                <td class="label label--w30">{{ __('consent::messages.modal.form.step3.fields.work_department') }}</td>
                                <td class="value" id="modal_work_department">-</td>
                            </tr>
                            <tr>
                                <td class="label label--w30">{{ __('consent::messages.modal.form.step3.fields.work_address') }}</td>
                                <td class="value" id="modal_work_address">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.common.phone_number') }}</td>
                                <td class="value" id="modal_work_phone">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step3.fields.work_experience') }}</td>
                                <td class="value" id="modal_work_experience">-</td>
                            </tr>
                        </table>
                    </div>

                    <div id="modal_previous_work_section" style="display:none;" class="panel panel--yellow">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.step3.sections.previous_workplace') }}</h4>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step3.fields.previous_company_name') }}</td>
                                <td class="value" id="modal_previous_company_name">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step3.fields.previous_position') }}</td>
                                <td class="value" id="modal_previous_position">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step3.fields.previous_income') }}</td>
                                <td class="value" id="modal_previous_income">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step3.fields.previous_work_address') }}</td>
                                <td class="value" id="modal_previous_work_address">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.common.phone_number') }}</td>
                                <td class="value" id="modal_previous_phone">-</td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel panel--green panel--compact">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.step2.fields.document_delivery') }}</h4>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step2.fields.document_delivery') }}</td>
                                <td class="value" id="modal_document_delivery">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step2.placeholders.document_address_text') }}</td>
                                <td class="value" id="modal_document_address_text">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.common.province') }}/{{ __('consent::messages.modal.form.common.postal') }}</td>
                                <td class="value" id="modal_document_address_province_postal">-</td>
                            </tr>
                            <tr id="modal_birthplace_row" style="display:none;">
                                <td class="label">{{ __('consent::messages.modal.form.step2.placeholders.birth_place_address') }}</td>
                                <td class="value" id="modal_birthplace">-</td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel panel--purple panel--compact">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.step5.sections.reference') }}</h4>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step5.fields.ref_name') }}</td>
                                <td class="value" id="modal_ref_name">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step5.fields.ref_relation') }}</td>
                                <td class="value" id="modal_ref_relation">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.steps.address') }}</td>
                                <td class="value" id="modal_ref_address">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.common.phone_number') }}</td>
                                <td class="value" id="modal_ref_phone_home">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.common.phone_number') }}</td>
                                <td class="value" id="modal_ref_phone_mobile">-</td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel panel--yellow panel--compact">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.step6.sections.loan_preference') }}</h4>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step6.fields.loan_term') }}</td>
                                <td class="value" id="modal_loan_term">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step6.fields.loan_amount_type') }}</td>
                                <td class="value" id="modal_loan_amount_type">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step6.fields.loan_purpose') }}</td>
                                <td class="value" id="modal_loan_purpose">-</td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel panel--green panel--compact">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.step6.sections.first_disbursement') }}</h4>
                        <p class="muted">{{ __('consent::messages.modal.form.step6.notes.first_disbursement') }}</p>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step6.fields.account_number') }}</td>
                                <td class="value" id="modal_account_number">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step6.fields.account_type') }}</td>
                                <td class="value" id="modal_account_type">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step6.fields.bank_name') }}</td>
                                <td class="value" id="modal_bank_name">-</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step6.fields.account_name') }}</td>
                                <td class="value" id="modal_account_name">-</td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel panel--blue panel--compact">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.step6.sections.payment_method') }}</h4>
                        <table class="consent-detail-table">
                            <tr>
                                <td class="label">{{ __('consent::messages.modal.form.step6.fields.payment_method') }}</td>
                                <td class="value" id="modal_payment_method">-</td>
                            </tr>
                            <tr id="modal_direct_debit_note_row" style="display:none;">
                                <td colspan="2"><div class="notice notice--warning">{!! __('consent::messages.modal.form.step6.notes.direct_debit') !!}</div></td>
                            </tr>
                        </table>
                    </div>

                    <div class="panel panel--light panel--compact">
                        <h4 class="section-title">{{ __('consent::messages.modal.form.attachment.section_title') }}</h4>
                        <div class="consent-detail-table">
                            <div class="file-attachments-list">
                                <div><strong>{{ __('consent::messages.modal.form.attachment.income_header') }}</strong></div>
                                <div id="modal_income_documents">-</div>
                            </div>
                            <div class="file-attachments-list" style="margin-top:0.75rem;">
                                <div><strong>{{ __('consent::messages.modal.form.attachment.identity_header') }}</strong></div>
                                <div id="modal_identity_documents">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="signature-row">
                        <div class="signature-box">
                            <canvas id="viewSignaturePad" class="signature-canvas"></canvas>
                            <p class="signature-label">{{ __('consent::messages.modal.form.step7.fields.signature') }}</p>
                            <p class="signature-muted">(<span id="modal_signature_owner">-</span>)</p>
                            <p class="signature-muted">{{ __('consent::messages.modal.form.step7.fields.signed_date') }} <span id="modal_signed_date">-</span></p>
                        </div>
                    </div>

                    <div class="view-consent-actions">
                        <button type="button" class="action-btn outline" id="viewDownloadAll">{{ __('consent::messages.modal.view.download_all') }}</button>
                    </div>

                </div>
            </div>
        <div class="modal-footer view-modal-footer">
            <button type="button" class="action-btn outline" id="closeViewConsentFooter">{{ __('consent::messages.modal.view.close') }}</button>
        </div>
    </div>
</div>
