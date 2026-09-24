@extends('layouts.app', ['title' => __('customerhistory::messages.index.page_title')])

@section('content')
    <section class="dashboard customer-history-page">
        <div class="hero compact-hero hero-with-actions">
            <div class="hero-body">
                <h2>{{ __('customerhistory::messages.index.page_title') }}</h2>
                <p>{{ __('customerhistory::messages.index.subtitle') }}</p>
            </div>
            <div class="hero-actions">
                <button type="button" class="action-btn" id="openCustomerHistoryForm">
                    {{ __('customerhistory::messages.index.create_button') }}
                </button>
            </div>
        </div>
    </section>

    <div id="customerHistoryTermsModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('consent::messages.modal.pdf.title') }}</h3>
                <button type="button" class="close-btn" id="closeCustomerHistoryTerms" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body modal-body--pdf">
                <div class="pdf-content-padding">
                    <h4 class="pdf-section-title">{{ __('customerhistory::messages.index.sale_sheet') }}</h4>
                    <div class="pdf-iframe-wrapper">
                        <object data="{{ asset('file/Sale Sheet - BMPver. 2_Personal Loan_App - Eng.pdf') }}#view=FitH" type="application/pdf" class="iframe-reset" title="{{ __('customerhistory::messages.index.sale_sheet') }}">
                            <p>{{ __('customerhistory::messages.index.pdf_unavailable') }}</p>
                        </object>
                    </div>

                    <h4 class="pdf-section-title">{{ __('customerhistory::messages.index.application_terms') }}</h4>
                    <div class="pdf-iframe-wrapper">
                        <object data="{{ asset('file/ใบสมัคร BMPver. 2_Personal Loan_App - Eng 4-5.pdf') }}#view=FitH" type="application/pdf" class="iframe-reset" title="{{ __('customerhistory::messages.index.application_terms') }}">
                            <p>{{ __('customerhistory::messages.index.pdf_unavailable') }}</p>
                        </object>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-actions modal-form-actions">
                    <button type="button" class="action-btn outline" id="cancelCustomerHistoryTerms">
                        {{ __('customerhistory::messages.index.cancel') }}
                    </button>
                    <button type="button" class="action-btn" id="proceedCustomerHistoryForm">
                        {{ __('customerhistory::messages.index.proceed') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('customerhistory::customer_form_modal')

    <script>
        (() => {
            const modal = document.getElementById('customerHistoryTermsModal');
            const formModal = document.getElementById('customerHistoryFormModal');
            const openButton = document.getElementById('openCustomerHistoryForm');
            const closeButton = document.getElementById('closeCustomerHistoryTerms');
            const cancelButton = document.getElementById('cancelCustomerHistoryTerms');
            const proceedButton = document.getElementById('proceedCustomerHistoryForm');
            const closeFormButton = document.getElementById('closeCustomerHistoryForm');
            const cancelFormButton = document.getElementById('cancelCustomerHistoryForm');

            const openFormModal = () => {
                if (!formModal) return;
                formModal.style.display = 'flex';
                formModal.offsetHeight;
                formModal.classList.add('show');
            };

            const closeFormModal = () => {
                formModal?.classList.remove('show');
                if (formModal) formModal.style.display = 'none';
            };

            const closeModal = () => {
                modal?.classList.remove('show');
                if (modal) modal.style.display = 'none';
            };

            openButton?.addEventListener('click', () => {
                if (!modal) return;
                modal.style.display = 'flex';
                modal.offsetHeight;
                modal.classList.add('show');
            });
            closeButton?.addEventListener('click', closeModal);
            cancelButton?.addEventListener('click', closeModal);
            closeFormButton?.addEventListener('click', closeFormModal);
            cancelFormButton?.addEventListener('click', closeFormModal);
            proceedButton?.addEventListener('click', () => {
                closeModal();
                openFormModal();
            });
        })();
    </script>
@endsection