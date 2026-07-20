<!-- Modal สำหรับดูเอกสารใบยินยอม (ConsentReview local copy) -->
<div id="viewConsentModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header view-modal-header">
            <h3 class="view-modal-title">
                {{ __('consentreview::messages.modal.view.title') }}
            </h3>
            <button type="button" class="close-btn view-modal-close" id="closeViewConsentModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body scrollable view-modal-body">
            <div id="viewConsentContent" class="view-consent-content">
                <!-- Content populated by JS -->
            </div>
        </div>
        <div class="modal-footer view-modal-footer">
            <button type="button" class="action-btn outline" id="closeViewConsentFooter">{{ __('consentreview::messages.modal.view.close') }}</button>
        </div>
    </div>
</div>
