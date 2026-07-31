<?php

namespace App\Modules\Consent\Http\Controllers;

use App\Modules\Consent\Models\ConsentApplication;
use App\Modules\Consent\Models\ConsentDocumentFile;
use App\Modules\Consent\Models\LoanProduct;
use App\Modules\Consent\Models\OfficerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConsentController extends ConsentFormController
{
    public function modalConsentForm() {
        $nextAppNo = $this->getNextAppNo();
        $loanProducts = LoanProduct::all();
        $officerGroups = OfficerGroup::where('is_active', true)->get();

        return view('consent::consent_form_modal', compact('nextAppNo', 'loanProducts', 'officerGroups'));
    }

    public function modalConsentView() {
        return view('consent::consent_view_modal');
    }

    public function postCodeOptions(Request $request) {
        if (!Schema::hasTable('post_codes')) {
            return response()->json([
                'provinces' => [],
                'cities' => [],
                'districts' => [],
                'post_codes' => [],
            ]);
        }

        $country = (string) ($request->query('country_code') ?: 'TH');

        $selectedProvince = (string) ($request->query('province') ?: '');
        $selectedCity = (string) ($request->query('city') ?: '');
        $selectedDistrict = (string) ($request->query('district') ?: '');
        $selectedPostCode = (string) ($request->query('post_code') ?: '');

        $qProvince = (string) ($request->query('q_province') ?: '');
        $qCity = (string) ($request->query('q_city') ?: '');
        $qDistrict = (string) ($request->query('q_district') ?: '');
        $qPostCode = (string) ($request->query('q_post_code') ?: '');

        $limit = 200;

        $provincesQuery = DB::table('post_codes')
            ->where('country_code', $country)
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->where('province', '!=', 'N/A')
            ->where('province', '!=', '#N/A')
            ->when($selectedCity !== '', fn($q) => $q->where('city', $selectedCity))
            ->when($selectedDistrict !== '', fn($q) => $q->where('district', $selectedDistrict))
            ->when($selectedPostCode !== '', fn($q) => $q->where('post_code', $selectedPostCode))
            ->when($qProvince !== '', fn($q) => $q->where('province', 'like', '%' . $qProvince . '%'))
            ->select('province')
            ->distinct()
            ->orderBy('province')
            ->limit($limit);

        $citiesQuery = DB::table('post_codes')
            ->where('country_code', $country)
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->where('province', '!=', 'N/A')
            ->where('province', '!=', '#N/A')
            ->when($selectedProvince !== '', fn($q) => $q->where('province', $selectedProvince))
            ->when($selectedDistrict !== '', fn($q) => $q->where('district', $selectedDistrict))
            ->when($selectedPostCode !== '', fn($q) => $q->where('post_code', $selectedPostCode))
            ->when($qCity !== '', fn($q) => $q->where('city', 'like', '%' . $qCity . '%'))
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->limit($limit);

        $districtsQuery = DB::table('post_codes')
            ->where('country_code', $country)
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->where('province', '!=', 'N/A')
            ->where('province', '!=', '#N/A')
            ->when($selectedProvince !== '', fn($q) => $q->where('province', $selectedProvince))
            ->when($selectedCity !== '', fn($q) => $q->where('city', $selectedCity))
            ->when($selectedPostCode !== '', fn($q) => $q->where('post_code', $selectedPostCode))
            ->when($qDistrict !== '', fn($q) => $q->where('district', 'like', '%' . $qDistrict . '%'))
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->limit($limit);

        $postCodesQuery = DB::table('post_codes')
            ->where('country_code', $country)
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->where('province', '!=', 'N/A')
            ->where('province', '!=', '#N/A')
            ->when($selectedProvince !== '', fn($q) => $q->where('province', $selectedProvince))
            ->when($selectedCity !== '', fn($q) => $q->where('city', $selectedCity))
            ->when($selectedDistrict !== '', fn($q) => $q->where('district', $selectedDistrict))
            ->when($qPostCode !== '', fn($q) => $q->where('post_code', 'like', '%' . $qPostCode . '%'))
            ->select('post_code')
            ->distinct()
            ->orderBy('post_code')
            ->limit($limit);

        return response()->json([
            'provinces' => $provincesQuery->pluck('province')->values(),
            'cities' => $citiesQuery->pluck('city')->values(),
            'districts' => $districtsQuery->pluck('district')->values(),
            'post_codes' => $postCodesQuery->pluck('post_code')->values(),
        ]);
    }

    public function index(Request $request) {
        $query = ConsentApplication::query()
            ->with(['applicants' => function ($q) {
                $q->select('id','application_id','name','applicant_order')->orderBy('applicant_order');
            }]);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('app_no', 'like', '%' . $q . '%')
                    ->orWhereHas('applicants', function ($appQuery) use ($q) {
                        $appQuery->where('name', 'like', '%' . $q . '%')
                            ->orWhere('id_card', 'like', '%' . $q . '%')
                            ->orWhere('passport', 'like', '%' . $q . '%');
                    })
                    ->orWhereHas('contact', function ($contactQuery) use ($q) {
                        $contactQuery->where('phone_mobile', 'like', '%' . $q . '%')
                            ->orWhere('phone_home', 'like', '%' . $q . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('officer_group_id')) {
            $query->where('officer_group_id', $request->input('officer_group_id'));
        }

        if ($request->filled('loan_product_id')) {
            $query->where('loan_product_id', $request->input('loan_product_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $customers = $query->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $total = ConsentApplication::count();
        $approved = ConsentApplication::where('status', 'ผ่าน')->count();
        $rejected = ConsentApplication::where('status', 'ไม่ผ่าน')->count();

        $nextAppNo = $this->getNextAppNo();

        $loanProducts = LoanProduct::all();
        $officerGroups = OfficerGroup::where('is_active', true)->get();

        return view('consent::index', compact('customers', 'total', 'approved', 'rejected', 'nextAppNo', 'loanProducts', 'officerGroups'));
    }

    public function data(ConsentApplication $consent) {
        $consent->load([
            'applicants',
            'contact',
            'incomeDocuments',
            'homeAddress',
            'workAddress',
            'documentAddress',
            'referenceAddress',
            'referenceWorkAddress',
            'employment',
            'previousEmployment',
            'reference',
            'loanRequest',
            'disbursementAccount',
        ]);

        return response()->json((object) $this->toFrontendData($consent));
    }

    /**
     * Return next application number for frontend (JSON)
     */
    public function nextAppNo(Request $request) {
        // Attempt to obtain next app no; use lock flag to be safe when possible
        $next = $this->getNextAppNo(false);

        return response()->json(['nextAppNo' => $next]);
    }

    public function destroy(ConsentApplication $consent){
        $consent->load(['applicants' => function($q){ $q->select('id','application_id','name','applicant_order')->orderBy('applicant_order'); }]);
        $name = $consent->applicants->sortBy('applicant_order')->first()?->name;
        $appNo = $consent->app_no;

        $consent->delete();

        if (request()->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'ลบใบยินยอมเลขที่ ' . ($appNo ?: '-') . ' ของ ' . $name . ' เรียบร้อยแล้ว'
            ]);
        }

        return redirect()
            ->route('consent.index')
            ->with('success', 'ลบใบยินยอมเลขที่ ' . ($appNo ?: '-') . ' ของ ' . $name . ' เรียบร้อยแล้ว');
    }

    private function toFrontendData(ConsentApplication $consent): array {
        $applicant = $consent->applicants->sortBy('applicant_order')->first();
        $contact = $applicant?->contact;
        $home = $applicant?->addresses()->where('kind', 'home')->first();
        $work = $applicant?->addresses()->where('kind', 'work')->first();
        $documentAddress = $applicant?->addresses()->where('kind', 'document')->first();
        $referenceAddress = $applicant?->addresses()->where('kind', 'reference/guarantor')->first();
        $employment = $consent->employment;
        $previousEmployment = $consent->previousEmployment;
        $reference = $consent->reference;
        $loan = $consent->loanRequest;
        $account = $consent->disbursementAccount;
        $applicantPhoto = $this->buildApplicantPhoto($consent);

        $idType = $this->resolveIdType($applicant);
        // boolean flags for frontend (use '1'/'0' strings for consistency with JS)
        $hasOtherDebtsFlag = $applicant?->has_other_debts ? '1' : '0';
        $hasExistingLoanFlag = $applicant?->has_existing_loan ? '1' : '0';
        // human-readable labels
        [$hasOtherDebtsLabel, $hasExistingLoanLabel] = $this->resolveDebtAndLoanLabels($applicant);
        $incomeDocuments = $this->buildIncomeDocuments($consent);
        $referenceDocuments = $this->buildReferenceDocuments($consent);

        return array_merge($consent->toArray(), $this->buildFrontendPayload($consent, [
            'applicant' => $applicant,
            'contact' => $contact,
            'home' => $home,
            'work' => $work,
            'documentAddress' => $documentAddress,
            'referenceAddress' => $referenceAddress,
            'employment' => $employment,
            'previousEmployment' => $previousEmployment,
            'reference' => $reference,
            'loan' => $loan,
            'account' => $account,
            'idType' => $idType,
            'hasOtherDebts' => $hasOtherDebtsFlag,
            'hasOtherDebtsLabel' => $hasOtherDebtsLabel,
            'hasExistingLoan' => $hasExistingLoanFlag,
            'hasExistingLoanLabel' => $hasExistingLoanLabel,
            'incomeDocuments' => $incomeDocuments,
            'referenceDocuments' => $referenceDocuments,
            // debug counts
            'internalIncomeDocumentsCount' => $consent->incomeDocuments()->count(),
            'internalReferenceDocumentsCount' => $consent->incomeDocuments()->where('document_type', 'reference_document')->count(),
            'applicantPhoto' => $applicantPhoto,
        ]));
    }

    private function buildApplicantPhoto(ConsentApplication $consent): ?array
    {
        $document = $consent->incomeDocuments()
            ->where('document_type', 'applicant_photo')
            ->orderByDesc('id')
            ->first();

        if (!$document) {
            return null;
        }

        return [
            'id' => $document->id,
            'originalName' => $document->original_name,
            'mimeType' => $document->mime_type,
            'size' => $document->size,
            'downloadUrl' => route('consent.applicant-photo.download', [
                'consent' => $consent->encrypted_id,
                'document' => $document->id,
            ]),
            'destroyUrl' => route('consent.applicant-photo.destroy', [
                'consent' => $consent->encrypted_id,
                'document' => $document->id,
            ]),
        ];
    }

    private function resolveDebtAndLoanLabels($applicant): array {
        $hasOtherDebts = null;
        if ($applicant?->has_other_debts !== null) {
            $hasOtherDebts = $applicant->has_other_debts ? 'มี' : 'ไม่มี';
        }

        $hasExistingLoan = null;
        if ($applicant?->has_existing_loan !== null) {
            $hasExistingLoan = $applicant->has_existing_loan ? 'ใช่' : 'ไม่ใช่';
        }

        return [$hasOtherDebts, $hasExistingLoan];
    }

    private function resolveIdType($applicant): string {
        if ($applicant?->passport && !$applicant?->id_card) {
            return 'passport';
        }

        return 'id_card';
    }

    private function buildIncomeDocuments(ConsentApplication $consent): array {
        return $consent
            ->incomeDocuments()
            ->orderBy('id')
            ->get()
            ->map(function (ConsentDocumentFile $document) use ($consent) {
                return [
                    'id' => $document->id,
                    'originalName' => $document->original_name,
                    'documentType' => $document->document_type,
                    'documentTypeLabel' => $this->getDocumentTypeLabel($document->document_type),
                    'mimeType' => $document->mime_type,
                    'size' => $document->size,
                    'downloadUrl' => route('consent.income-documents.download', [
                        'consent' => $consent->encrypted_id,
                        'document' => $document->id,
                    ]),
                    'destroyUrl' => route('consent.income-documents.destroy', [
                        'consent' => $consent->encrypted_id,
                        'document' => $document->id,
                    ]),
                ];
            })
            ->values()
            ->all();
    }

    private function buildReferenceDocuments(ConsentApplication $consent): array {
        return $consent
            ->incomeDocuments()
            ->where('document_type', 'reference_document')
            ->orderBy('id')
            ->get()
            ->map(function (ConsentDocumentFile $document) use ($consent) {
                return [
                    'id' => $document->id,
                    'originalName' => $document->original_name,
                    'documentType' => $document->document_type,
                    'documentTypeLabel' => $this->getDocumentTypeLabel($document->document_type),
                    'mimeType' => $document->mime_type,
                    'size' => $document->size,
                    'downloadUrl' => route('consent.income-documents.download', [
                        'consent' => $consent->encrypted_id,
                        'document' => $document->id,
                    ]),
                    'destroyUrl' => route('consent.income-documents.destroy', [
                        'consent' => $consent->encrypted_id,
                        'document' => $document->id,
                    ]),
                ];
            })
            ->values()
            ->all();
    }

    private function buildFrontendPayload(ConsentApplication $consent, array $context): array {
        $applicant = $context['applicant'] ?? null;
        $contact = $context['contact'] ?? null;
        $home = $context['home'] ?? null;
        $work = $context['work'] ?? null;
        $documentAddress = $context['documentAddress'] ?? null;
        $referenceAddress = $context['referenceAddress'] ?? null;
        $employment = $context['employment'] ?? null;
        $previousEmployment = $context['previousEmployment'] ?? null;
        $reference = $context['reference'] ?? null;
        $loan = $context['loan'] ?? null;
        $account = $context['account'] ?? null;
        $idType = $context['idType'] ?? 'id_card';
        $hasOtherDebts = $context['hasOtherDebts'] ?? null;
        $hasExistingLoan = $context['hasExistingLoan'] ?? null;
        $incomeDocuments = $context['incomeDocuments'] ?? [];
        $applicantPhoto = $context['applicantPhoto'] ?? null;

        return [
            'id' => $consent->encrypted_id,
            'transaction_date' => $consent->created_at?->format('d/m/Y'),
            'signed_date' => $consent->signed_at?->format('Y-m-d'),

            'app_date' => $consent->app_date?->format('Y-m-d'),
            'app_no' => $consent->app_no,

            'officer_name' => $consent->officer_name,
            'officer_phone' => $consent->officer_phone,
            'officer_group_id' => $consent->officer_group_id,
            'loan_product_id' => $consent->loan_product_id,
            'officer_group_name' => app()->getLocale() === 'th' ? $consent->officerGroup?->name_th : $consent->officerGroup?->name_en,
            'loan_product_name' => app()->getLocale() === 'th' ? $consent->loanProduct?->name_th : $consent->loanProduct?->name_en,
            'interest_rate_cap' => $consent->loanProduct?->interest_rate_cap,
            'fee_rate' => $consent->loanProduct?->fee_rate,
            'late_penalty_rate' => $consent->loanProduct?->late_fee,

            'title' => $applicant?->title,
            'name' => $applicant?->name,
            'name_en' => $applicant?->name_en,
            'birthdate' => $applicant?->birthdate?->format('Y-m-d'),
            'nationality' => $applicant?->nationality,
            'id_type' => $idType,
            'id_card' => $applicant?->id_card ?: $applicant?->passport,
            'education' => $applicant?->education,
            'educationOther' => $applicant?->education_other,
            'marital_status' => $applicant?->marital_status,

            'residence_status' => $home?->residence_status,
            'address_building' => $home?->address_building,
            'address_room' => $home?->address_room,
            'address_floor' => $home?->address_floor,
            'address_no' => $home?->address_no,
            'address_village' => $home?->address_village,
            'address_soi' => $home?->address_soi,
            'address_road' => $home?->address_road,
            'address_subdistrict' => $home?->address_subdistrict,
            'address_district' => $home?->address_district,
            'address_province' => $home?->address_province,
            'address_postal' => $home?->address_postal,
            'phone_home' => $contact?->phone_home,
            'phone_mobile' => $contact?->phone_mobile,
            'email' => $contact?->email,
            'documentDelivery' => $consent->document_delivery,
            'documentAddressText' => $documentAddress?->address_text,
            'documentAddressProvince' => $documentAddress?->address_province,
            'documentAddressPostal' => $documentAddress?->address_postal,
            'birthPlaceAddress' => $documentAddress?->birth_place_address,

            'useHomeAddress' => $employment?->use_home_address,
            'occupation' => $applicant?->occupation,
            'governmentLevel' => $applicant?->government_level,
            'occupationOther' => $applicant?->occupation_other,
            'careerField' => $applicant?->career_field,
            'careerFieldOther' => $applicant?->career_field_other,
            'companyName' => $employment?->company_name,
            'businessType' => $employment?->business_type,
            'businessTypeOther' => $employment?->business_type_other,
            'workAddressBuilding' => $work?->address_building,
            'workAddressFloor' => $work?->address_floor,
            'workDepartment' => $employment?->work_department,
            'workAddressNo' => $work?->address_no,
            'workAddressVillage' => $work?->address_village,
            'workAddressSoi' => $work?->address_soi,
            'workAddressRoad' => $work?->address_road,
            'workAddressSubdistrict' => $work?->address_subdistrict,
            'workAddressDistrict' => $work?->address_district,
            'workAddressProvince' => $work?->address_province,
            'workAddressPostal' => $work?->address_postal,
            'workPhone' => $employment?->work_phone,
            'workYears' => $employment?->work_years,
            'workMonths' => $employment?->work_months,

            'previousCompanyName' => $previousEmployment?->previous_company_name,
            'previousPosition' => $previousEmployment?->previous_position,
            'previousIncome' => $previousEmployment?->previous_income,
            'previousWorkAddress' => $previousEmployment?->previous_address,
            'previousPhone' => $previousEmployment?->previous_phone,

            'income' => $applicant?->income,
            'extraIncome' => $applicant?->extra_income,
            'extraIncomeSource' => $applicant?->extra_income_source,
            'extraIncomeSourceOther' => $applicant?->extra_income_source_other,
            'incomeCountry' => $applicant?->income_country,
            'incomeDocuments' => $incomeDocuments,
            'applicantPhoto' => $applicantPhoto,
            'hasOtherDebts' => $hasOtherDebts,
            'otherDebtInstallment' => $applicant?->other_debt_installment,
            'hasExistingLoan' => $hasExistingLoan,
            'existingLoanInstitutionCount' => $applicant?->existing_loan_institution_count,
            'existingLoanTotalAmount' => $applicant?->existing_loan_total_amount,

            'refName' => $reference?->ref_name,
            'refType' => $reference?->ref_type,
            'refRelation' => $reference?->ref_relation,
            'refAddressNo' => $referenceAddress?->address_no,
            'refAddressFloor' => $referenceAddress?->address_floor,
            'refAddressVillage' => $referenceAddress?->address_village,
            'refAddressBuilding' => $referenceAddress?->address_building,
            'refAddressSoi' => $referenceAddress?->address_soi,
            'refAddressRoad' => $referenceAddress?->address_road,
            'refAddressSubdistrict' => $referenceAddress?->address_subdistrict,
            'refAddressDistrict' => $referenceAddress?->address_district,
            'refAddressProvince' => $referenceAddress?->address_province,
            'refAddressPostal' => $referenceAddress?->address_postal,
            'refPhoneHome' => $reference?->ref_phone_home,
            'refPhoneMobile' => $reference?->ref_phone_mobile,

            // Reference / guarantor personal fields
            'refBirthdate' => $reference?->birthdate?->format('Y-m-d'),
            'refNationality' => $reference?->nationality,
            'refEducation' => $reference?->education,
            'refEducationOther' => $reference?->education_other,
            'refMaritalStatus' => $reference?->marital_status,
            'refOccupation' => $reference?->occupation,
            'refCareerField' => $reference?->career_field,
            'refOccupationOther' => $reference?->occupation_other,
            'refCareerFieldOther' => $reference?->career_field_other,
            'refIncome' => $reference?->income,
            'refExtraIncome' => $reference?->extra_income,
            'refExtraIncomeSource' => $reference?->extra_income_source,
            'refExtraIncomeSourceOther' => $reference?->extra_income_source_other,
            'refIncomeCountry' => $reference?->income_country,
            // map debt/loan flags to form select values ('1'/'0') used in the modal
            'refHasOtherDebts' => isset($reference->has_other_debts) ? ($reference->has_other_debts ? '1' : '0') : null,
            'refOtherDebtInstallment' => $reference?->other_debt_installment,
            'refHasExistingLoan' => isset($reference->has_existing_loan) ? ($reference->has_existing_loan ? '1' : '0') : null,
            'refExistingLoanInstitutionCount' => $reference?->existing_loan_institution_count,
            'refExistingLoanTotalAmount' => $reference?->existing_loan_total_amount,

            // Reference work/employment and work address (use single relation `referenceWorkAddress`)
            'refUseHomeAddress' => (function() use ($reference) {
                if ($reference?->id) {
                    $refEmp = \App\Modules\Consent\Models\ConsentEmployment::where('reference_id', $reference->id)->first();
                    if ($refEmp) return $refEmp->use_home_address;
                }
                return null;
            })(),
            'refWorkCompany' => (function() use ($reference) {
                if ($reference?->id) {
                    $refEmp = \App\Modules\Consent\Models\ConsentEmployment::where('reference_id', $reference->id)->first();
                    if ($refEmp) return $refEmp->company_name;
                }
                return null;
            })(),
            // refBusinessType should come from employment record linked to the reference (reference_id),
            // fallback to the applicant's employment.business_type if no reference employment exists
            'refBusinessType' => (function() use ($reference, $employment) {
                if ($reference?->id) {
                    $refEmp = \App\Modules\Consent\Models\ConsentEmployment::where('reference_id', $reference->id)->first();
                    if ($refEmp) return $refEmp->business_type;
                }
                return $employment?->business_type ?? null;
            })(),
            'refBusinessTypeOther' => (function() use ($reference, $employment) {
                if ($reference?->id) {
                    $refEmp = \App\Modules\Consent\Models\ConsentEmployment::where('reference_id', $reference->id)->first();
                    if ($refEmp) return $refEmp->business_type_other;
                }
                return $employment?->business_type_other ?? null;
            })(),
            'refWorkDepartment' => (function() use ($reference) {
                if ($reference?->id) {
                    $refEmp = \App\Modules\Consent\Models\ConsentEmployment::where('reference_id', $reference->id)->first();
                    if ($refEmp) return $refEmp->work_department;
                }
                return null;
            })(),
            'refWorkBuildingName' => $consent->referenceWorkAddress?->address_building ?? null,
            'refWorkNo' => $consent->referenceWorkAddress?->address_no ?? null,
            'refWorkRoom' => $consent->referenceWorkAddress?->address_room ?? null,
            'refWorkFloor' => $consent->referenceWorkAddress?->address_floor ?? null,
            'refWorkVillage' => $consent->referenceWorkAddress?->address_village ?? null,
            'refWorkSoi' => $consent->referenceWorkAddress?->address_soi ?? null,
            'refWorkRoad' => $consent->referenceWorkAddress?->address_road ?? null,
            'refWorkSubdistrict' => $consent->referenceWorkAddress?->address_subdistrict ?? null,
            'refWorkDistrict' => $consent->referenceWorkAddress?->address_district ?? null,
            'refWorkProvince' => $consent->referenceWorkAddress?->address_province ?? null,
            'refWorkPostal' => $consent->referenceWorkAddress?->address_postal ?? null,
            'refWorkPhone' => (function() use ($reference) {
                if ($reference?->id) {
                    $refEmp = \App\Modules\Consent\Models\ConsentEmployment::where('reference_id', $reference->id)->first();
                    if ($refEmp) return $refEmp->work_phone;
                }
                return null;
            })(),
            'refWorkYears' => (function() use ($reference) {
                if ($reference?->id) {
                    $refEmp = \App\Modules\Consent\Models\ConsentEmployment::where('reference_id', $reference->id)->first();
                    if ($refEmp) return $refEmp->work_years;
                }
                return null;
            })(),
            'refWorkMonths' => (function() use ($reference) {
                if ($reference?->id) {
                    $refEmp = \App\Modules\Consent\Models\ConsentEmployment::where('reference_id', $reference->id)->first();
                    if ($refEmp) return $refEmp->work_months;
                }
                return null;
            })(),
            // total months = years*12 + months (if available)
            'refWorkTotalMonths' => (function() use ($reference) {
                if ($reference?->id) {
                    $refEmp = \App\Modules\Consent\Models\ConsentEmployment::where('reference_id', $reference->id)->first();
                    if ($refEmp && ($refEmp->work_years !== null || $refEmp->work_months !== null)) {
                        $years = (int)($refEmp->work_years ?? 0);
                        $months = (int)($refEmp->work_months ?? 0);
                        return ($years * 12) + $months;
                    }
                }
                return null;
            })(),

            'loanPurpose' => $loan?->loan_purpose,
            'loanTerm' => $loan?->loan_term,
            'loanAmountType' => $loan?->loan_amount_type,
            'customLoanAmount' => $loan?->custom_loan_amount,
            'calculatedEligibleAmount' => $loan?->calculated_eligible_amount,

            'accountNumber' => $account?->account_number,
            'accountType' => $account?->account_type,
            'bankName' => $account?->bank_name,
            'accountName' => $account?->account_name,

            'paymentMethod' => $account?->payment_method,
            'directDebitAmount' => $account?->direct_debit_amount,
            'directDebitAccountNumber' => $account?->direct_debit_account_number,

            'signature_data' => $consent->signature_data,
            'signatureData' => $consent->signature_data,
        ];
    }

    private function getDocumentTypeLabel(?string $documentType): string {
        return match ($documentType) {
            'identity_document' => 'เอกสารแสดงตน',
            'income_document' => 'เอกสารแสดงรายได้',
            'id_card' => 'สำเนาบัตรประชาชน',
            'passport' => 'หนังสือเดินทาง',
            'house_registration' => 'สำเนาทะเบียนบ้าน',
            'work_permit' => 'ใบอนุญาตทำงาน',
            'name_change' => 'สำเนาเปลี่ยนชื่อ-นามสกุล',
            'income_salary_slip' => 'สลิปเงินเดือนล่าสุด',
            'income_salary_certificate' => 'หนังสือรับรองเงินเดือน',
            'income_salary_50tawi' => 'เอกสาร 50 ทวิ',
            'income_salary_statement_6m' => 'รายการเดินบัญชีย้อนหลัง 6 เดือน (เงินเดือน)',
            'income_supplementary_slip' => 'สลิปเงินเดือน/คอมมิชชัน/ค่าล่วงเวลา',
            'income_supplementary_statement_6m' => 'รายการเดินบัญชีย้อนหลัง 6 เดือน (รายได้เสริม)',
            'income_registered_corporate_cert' => 'หนังสือรับรองการจดทะเบียนนิติบุคคล',
            'income_registered_shareholder_list' => 'สำเนารายชื่อผู้ถือหุ้น',
            'income_registered_trade_registration' => 'ใบทะเบียนการค้า',
            'income_registered_statement_1y' => 'รายการเดินบัญชีย้อนหลัง 1 ปี (จดทะเบียน)',
            'income_unregistered_lease' => 'สัญญาเช่า',
            'income_unregistered_tax' => 'เอกสารการเสียภาษี (ไม่จดทะเบียน)',
            'income_unregistered_statement_1y' => 'รายการเดินบัญชีย้อนหลัง 1 ปี (ไม่จดทะเบียน)',
            'income_unregistered_invoice' => 'บิลซื้อ/บิลขาย (ไม่จดทะเบียน)',
            'income_unregistered_business_photo' => 'รูปถ่ายกิจการ (ไม่จดทะเบียน)',
            'income_self_individual_tax' => 'เอกสารการเสียภาษี/50 ทวิ (บุคคลธรรมดา)',
            'income_self_individual_pnd' => 'แบบยื่นภาษี ภ.ง.ด. 90/91/94',
            'income_self_individual_statement_1y' => 'รายการเดินบัญชีย้อนหลัง 1 ปี (บุคคลธรรมดา)',
            'income_self_business_tax' => 'เอกสารการเสียภาษี (ผู้ประกอบการ)',
            'income_self_business_statement_1y' => 'รายการเดินบัญชีย้อนหลัง 1 ปี (ผู้ประกอบการ)',
            'income_self_business_invoice' => 'บิลซื้อ/บิลขาย (ผู้ประกอบการ)',
            'income_self_business_photo' => 'รูปถ่ายกิจการ (ผู้ประกอบการ)',
            'income_salary' => 'ผู้มีรายได้ประจำ',
            'income_salary_supplement' => 'รายได้เสริมกรณีเงินเดือนไม่ถึงเกณฑ์',
            'income_business_registered' => 'เจ้าของกิจการ / กรณีจดทะเบียน',
            'income_business_unregistered' => 'กรณีไม่จดทะเบียน',
            'income_self_employed_individual' => 'เจ้าของกิจการ/อาชีพอิสระ (บุคคลธรรมดา)',
            'income_self_employed_business' => 'เจ้าของกิจการ/อาชีพอิสระ (ผู้ประกอบการ)',
            'income_proof' => 'หลักฐานการเงิน',
            default => 'เอกสารแนบ',
        };
    }

}
