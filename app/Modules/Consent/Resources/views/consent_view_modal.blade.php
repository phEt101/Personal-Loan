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
                    <!-- Minimal static template: JS will populate individual fields only -->
                    <section class="view-consent-summary">
                        <div class="row">
                            <div class="col-md-3 view-consent-photo">
                                <img id="viewApplicantPhoto" src="" alt="Applicant" />
                            </div>
                            <div class="col-md-9">
                                <h4 id="viewApplicantName">-</h4>
                                <p id="viewApplicantId">-</p>
                                <p id="viewApplicantBirthdate">-</p>
                                <p id="viewApplicantContact">-</p>
                            </div>
                        </div>
                    </section>

                    <section class="view-consent-details">
                        <h5>{{ __('consent::messages.modal.view.application_details') }}</h5>
                        <div id="viewApplicationDetails">-</div>
                    </section>

                    <section class="view-consent-documents">
                        <h5>{{ __('consent::messages.modal.view.documents') }}</h5>
                        <div id="viewDocumentsList">-</div>
                    </section>

                    <section class="view-consent-signature">
                        <h5>{{ __('consent::messages.modal.view.signature') }}</h5>
                        <canvas id="viewSignaturePad" width="600" height="150"></canvas>
                        <div id="viewSignedAt">-</div>
                    </section>

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
