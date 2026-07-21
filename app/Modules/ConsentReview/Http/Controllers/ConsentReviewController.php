<?php

namespace App\Modules\ConsentReview\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Consent\Models\ConsentApplication;

class ConsentReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsentApplication::query()->with(['applicant:id,application_id,name', 'loanRequest']);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('app_no', 'like', '%' . $q . '%')
                    ->orWhereHas('applicant', function ($appQuery) use ($q) {
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

        if ($request->filled('officer_group')) {
            $query->where('officer_group', $request->input('officer_group'));
        }

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->input('product_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $customers = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return view('consentreview::index', compact('customers'));
    }

    public function data(\App\Modules\Consent\Models\ConsentApplication $consent)
    {
        $consent->load([
            'applicant',
            'contact',
            'incomeDocuments',
            'homeAddress',
            'workAddress',
            'documentAddress',
            'referenceAddress',
            'employment',
            'previousEmployment',
            'reference',
            'loanRequest',
            'disbursementAccount',
        ]);

        return response()->json((object) $this->toFrontendData($consent));
    }

    public function modalView()
    {
        return view('consentreview::consent_view_modal');
    }

    private function toFrontendData(\App\Modules\Consent\Models\ConsentApplication $consent): array
    {
        $applicant = $consent->applicant;
        $contact = $consent->contact;
        $home = $consent->homeAddress;
        $work = $consent->workAddress;
        $documentAddress = $consent->documentAddress;
        $referenceAddress = $consent->referenceAddress;
        $employment = $consent->employment;
        $previousEmployment = $consent->previousEmployment;
        $reference = $consent->reference;
        $loan = $consent->loanRequest;
        $account = $consent->disbursementAccount;

        [$hasOtherDebts, $hasExistingLoan] = $this->resolveDebtAndLoanLabels($applicant);
        $idType = $this->resolveIdType($applicant);
        $incomeDocuments = $this->buildIncomeDocuments($consent);

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
            'hasOtherDebts' => $hasOtherDebts,
            'hasExistingLoan' => $hasExistingLoan,
            'incomeDocuments' => $incomeDocuments,
        ]));
    }

    private function resolveDebtAndLoanLabels($applicant): array
    {
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

    private function resolveIdType($applicant): string
    {
        if ($applicant?->passport && !$applicant?->id_card) {
            return 'passport';
        }

        return 'id_card';
    }

    private function buildIncomeDocuments(\App\Modules\Consent\Models\ConsentApplication $consent): array
    {
        return $consent
            ->incomeDocuments()
            ->orderBy('id')
            ->get()
            ->map(function (\App\Modules\Consent\Models\ConsentDocumentFile $document) use ($consent) {
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

    private function buildFrontendPayload(\App\Modules\Consent\Models\ConsentApplication $consent, array $context): array
    {
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

        return [
            'id' => $consent->encrypted_id,
            'transaction_date' => $consent->created_at?->format('d/m/Y'),
            'signed_date' => $consent->signed_at?->format('Y-m-d'),

            'app_date' => $consent->app_date?->format('Y-m-d'),
            'app_no' => $consent->app_no,

            'officer_name' => $consent->officer_name,
            'officer_phone' => $consent->officer_phone,

            'title' => $applicant?->title,
            'name' => $applicant?->name,
            'name_en' => $applicant?->name_en,
            'birthdate' => $applicant?->birthdate?->format('Y-m-d'),
            'nationality' => $applicant?->nationality,
            'id_type' => $idType,
            'id_card' => $applicant?->id_card ?: $applicant?->passport,
            'education' => $applicant?->education,
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
            'incomeCountry' => $applicant?->income_country,
            'incomeDocuments' => $incomeDocuments,
            'hasOtherDebts' => $hasOtherDebts,
            'otherDebtInstallment' => $applicant?->other_debt_installment,
            'hasExistingLoan' => $hasExistingLoan,
            'existingLoanInstitutionCount' => $applicant?->existing_loan_institution_count,
            'existingLoanTotalAmount' => $applicant?->existing_loan_total_amount,

            'refName' => $reference?->ref_name,
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

            'loanPurpose' => $loan?->loan_purpose,
            'loanTerm' => $loan?->loan_term,
            'loanAmountType' => $loan?->loan_amount_type,
            'customLoanAmount' => $loan?->custom_loan_amount,

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

    private function getDocumentTypeLabel(?string $documentType): string
    {
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
