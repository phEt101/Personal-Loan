@extends('layouts.app', ['title' => __('customerhistory::messages.index.page_title')])

@section('content')
    <section class="dashboard customer-history-page">
        <div class="hero compact-hero hero-with-actions">
            <div class="hero-body">
                <h2>{{ __('customerhistory::messages.index.page_title') }}</h2>
                <p>{{ __('customerhistory::messages.index.subtitle') }}</p>
            </div>
            <div class="hero-actions">
                @if ($canCreateCustomer)
                    <button type="button" class="action-btn" id="openCustomerHistoryForm">
                        {{ __('customerhistory::messages.index.create_button') }}
                    </button>
                @endif
            </div>
        </div>

        <div id="customerHistoryListContent" class="customer-history-ajax-region">
            <div class="customer-history-loading" role="status" aria-live="polite" data-error-message="{{ __('customerhistory::messages.index.load_failed') }}">
                <span class="customer-history-spinner" aria-hidden="true"></span>
            </div>
            <div id="customerHistoryListPartial">
                @include('customerhistory::_customer_list')
            </div>
        </div>
    </section>

    <div id="customerHistoryTermsModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('customerhistory::messages.index.terms_title') }}</h3>
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
            const listRegion = document.getElementById('customerHistoryListContent');
            const listPartial = document.getElementById('customerHistoryListPartial');
            const listLoading = listRegion?.querySelector('.customer-history-loading');

            window.refreshCustomerHistoryList = async (url = window.location.href, updateHistory = false) => {
                if (!listRegion || !listPartial) return false;

                const requestUrl = new URL(url, window.location.origin);
                requestUrl.searchParams.set('partial', '1');
                listRegion.classList.add('is-loading');
                listRegion.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(requestUrl, {
                        headers: {
                            Accept: 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) throw new Error(listLoading?.dataset.errorMessage || 'Unable to load data');
                    listPartial.innerHTML = await response.text();

                    if (updateHistory) {
                        requestUrl.searchParams.delete('partial');
                        window.history.pushState({}, '', requestUrl);
                    }

                    return true;
                } catch (error) {
                    window.alert(error.message || listLoading?.dataset.errorMessage);
                    return false;
                } finally {
                    listRegion.classList.remove('is-loading');
                    listRegion.removeAttribute('aria-busy');
                }
            };

            listRegion?.addEventListener('submit', (event) => {
                const form = event.target.closest('.customer-history-filter-form, .customer-history-per-page-form');
                if (!form) return;
                event.preventDefault();
                const url = new URL(form.action, window.location.origin);
                new FormData(form).forEach((value, key) => url.searchParams.set(key, value));
                window.refreshCustomerHistoryList(url, true);
            });

            listRegion?.addEventListener('change', (event) => {
                if (event.target.matches('.customer-history-per-page-form select')) {
                    event.target.form?.requestSubmit();
                }
            });

            listRegion?.addEventListener('click', (event) => {
                const editButton = event.target.closest('.customer-edit-button');
                if (editButton) {
                    event.preventDefault();
                    window.openCustomerHistoryEdit?.(editButton.dataset.detailUrl, editButton.dataset.updateUrl);
                    return;
                }

                const viewButton = event.target.closest('.customer-view-button');
                if (viewButton) {
                    event.preventDefault();
                    window.openCustomerHistoryDetail?.(viewButton.dataset.detailUrl);
                    return;
                }

                const link = event.target.closest('.pagination-link, .customer-history-filter-clear, .customer-history-sort-link');
                if (!link || link.classList.contains('is-disabled')) return;
                event.preventDefault();
                window.refreshCustomerHistoryList(link.href, true);
            });

            window.addEventListener('popstate', () => window.refreshCustomerHistoryList(window.location.href));

            const openFormModal = () => {
                if (!formModal) return;
                window.prepareCustomerHistoryCreateForm?.();
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
