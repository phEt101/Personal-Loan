<?php

namespace App\Modules\Consent\Http\Controllers;

use App\Models\User;
use App\Modules\Consent\Models\ConsentForm;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ConsentController extends Controller
{
    public function index()
    {
        $customers = ConsentForm::orderBy('id', 'desc')->get()->map(function ($form) {
            return (object) array_merge($form->toArray(), [
                'code' => 'CUST-' . str_pad($form->id, 3, '0', STR_PAD_LEFT),
                'signed_date' => $form->signed_at?->format('Y-m-d'),
                'signatureData' => $form->signature_data,
                // Map camelCase from form to snake_case from DB
                'extraIncome' => $form->extra_income,
                'extraIncomeSource' => $form->extra_income_source,
                'businessIncome' => $form->business_income,
                'averageMonthlyIncome' => $form->average_monthly_income,
                'hasOtherDebts' => $form->has_other_debts,
                'otherDebtInstallment' => $form->other_debt_installment,
                'hasExistingLoan' => $form->has_existing_loan,
                'spouseTitle' => $form->spouse_title,
                'spouseName' => $form->spouse_name,
                'spousePhone' => $form->spouse_phone,
                'spouseMobile' => $form->spouse_mobile,
                'spouseEducation' => $form->spouse_education,
                'spouseOccupation' => $form->spouse_occupation,
                'spouseCompany' => $form->spouse_company,
                'spouseIncome' => $form->spouse_income,
                'dwellingType' => $form->dwelling_type,
                'residenceStatus' => $form->residence_status,
                'residenceRentAmount' => $form->residence_rent_amount,
                'residenceYears' => $form->residence_years,
                'addressNo' => $form->address_no,
                'addressFloor' => $form->address_floor,
                'addressVillage' => $form->address_village,
                'addressBuilding' => $form->address_building,
                'addressSoi' => $form->address_soi,
                'addressRoad' => $form->address_road,
                'addressSubdistrict' => $form->address_subdistrict,
                'addressDistrict' => $form->address_district,
                'addressProvince' => $form->address_province,
                'addressPostal' => $form->address_postal,
                'phoneHome' => $form->phone_home,
                'phoneMobile' => $form->phone_mobile,
                'lineId' => $form->line_id,
                'useHomeAddress' => $form->use_home_address,
                'companyType' => $form->company_type,
                'companyName' => $form->company_name,
                'businessType' => $form->business_type,
                'workOccupation' => $form->work_occupation,
                'workPosition' => $form->work_position,
                'workYears' => $form->work_years,
                'workMonths' => $form->work_months,
                'workAddressNo' => $form->work_address_no,
                'workAddressFloor' => $form->work_address_floor,
                'workAddressVillage' => $form->work_address_village,
                'workAddressBuilding' => $form->work_address_building,
                'workAddressSoi' => $form->work_address_soi,
                'workAddressRoad' => $form->work_address_road,
                'workAddressSubdistrict' => $form->work_address_subdistrict,
                'workAddressDistrict' => $form->work_address_district,
                'workAddressProvince' => $form->work_address_province,
                'workAddressPostal' => $form->work_address_postal,
                'workPhone' => $form->work_phone,
                'previousCompanyName' => $form->previous_company_name,
                'previousBusinessType' => $form->previous_business_type,
                'previousPosition' => $form->previous_position,
                'previousIncome' => $form->previous_income,
                'previousWorkYears' => $form->previous_work_years,
                'previousPhone' => $form->previous_phone,
                'documentDelivery' => $form->document_delivery,
                'documentEmail' => $form->document_email,
                'refName' => $form->ref_name,
                'refRelation' => $form->ref_relation,
                'refAddressNo' => $form->ref_address_no,
                'refAddressFloor' => $form->ref_address_floor,
                'refAddressVillage' => $form->ref_address_village,
                'refAddressBuilding' => $form->ref_address_building,
                'refAddressSoi' => $form->ref_address_soi,
                'refAddressRoad' => $form->ref_address_road,
                'refAddressSubdistrict' => $form->ref_address_subdistrict,
                'refAddressDistrict' => $form->ref_address_district,
                'refAddressProvince' => $form->ref_address_province,
                'refAddressPostal' => $form->ref_address_postal,
                'refPhoneHome' => $form->ref_phone_home,
                'refPhoneMobile' => $form->ref_phone_mobile,
                'refEmail' => $form->ref_email,
                'refLineId' => $form->ref_line_id,
                'loanTerm' => $form->loan_term,
                'loanAmountType' => $form->loan_amount_type,
                'customLoanAmount' => $form->custom_loan_amount,
                'loanPurpose' => $form->loan_purpose,
                'bankName' => $form->bank_name,
                'bankBranch' => $form->bank_branch,
                'accountName' => $form->account_name,
                'accountType' => $form->account_type,
                'accountNumber' => $form->account_number,
            ]);
        });

        $total = $customers->count();
        $signed = $customers->where('signed', true)->count();
        $failed = max(0, $total - $signed);

        return view('consent::index', compact('customers', 'total', 'signed', 'failed'));
    }

    public function create()
    {
        return view('consent::create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'app_date' => ['nullable', 'date'],
            'app_no' => ['nullable', 'string', 'max:13'],
            'officer_name' => ['nullable', 'string', 'max:255'],
            'officer_phone' => ['nullable', 'string', 'max:20'],
            'title' => ['nullable', 'string', 'max:50'],
            'title_other' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
            'id_card' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'max:10'],
            'age' => ['nullable', 'integer'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'education' => ['nullable', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'occupationOther' => ['nullable', 'string', 'max:100'],
            'income' => ['nullable', 'numeric'],
            'extraIncome' => ['nullable', 'numeric'],
            'extraIncomeSource' => ['nullable', 'string', 'max:255'],
            'businessIncome' => ['nullable', 'numeric'],
            'averageMonthlyIncome' => ['nullable', 'numeric'],
            'hasOtherDebts' => ['nullable', 'string', 'max:10'],
            'otherDebtInstallment' => ['nullable', 'numeric'],
            'hasExistingLoan' => ['nullable', 'string', 'max:10'],
            'existingLoanInstallment' => ['nullable', 'numeric'],
            // Spouse fields
            'spouse_title' => ['nullable', 'string', 'max:50'],
            'spouse_title_other' => ['nullable', 'string', 'max:50'],
            'spouse_name' => ['nullable', 'string', 'max:255'],
            'spouse_phone' => ['nullable', 'string', 'max:20'],
            'spouse_mobile' => ['nullable', 'string', 'max:20'],
            'spouse_education' => ['nullable', 'string', 'max:50'],
            'spouse_occupation' => ['nullable', 'string', 'max:100'],
            'spouseOccupationOther' => ['nullable', 'string', 'max:100'],
            'spouse_company' => ['nullable', 'string', 'max:255'],
            'spouse_income' => ['nullable', 'numeric'],
            // Address fields
            'dwelling_type' => ['nullable', 'string', 'max:255'],
            'dwelling_type_other' => ['nullable', 'string', 'max:255'],
            'residence_status' => ['nullable', 'string', 'max:255'],
            'residence_rent_amount' => ['nullable', 'numeric'],
            'residence_status_other' => ['nullable', 'string', 'max:255'],
            'residence_years' => ['nullable', 'integer'],
            'address_no' => ['nullable', 'string', 'max:255'],
            'address_floor' => ['nullable', 'string', 'max:255'],
            'address_village' => ['nullable', 'string', 'max:255'],
            'address_building' => ['nullable', 'string', 'max:255'],
            'address_soi' => ['nullable', 'string', 'max:255'],
            'address_road' => ['nullable', 'string', 'max:255'],
            'address_subdistrict' => ['nullable', 'string', 'max:255'],
            'address_district' => ['nullable', 'string', 'max:255'],
            'address_province' => ['nullable', 'string', 'max:255'],
            'address_postal' => ['nullable', 'string', 'max:255'],
            'phone_home' => ['nullable', 'string', 'max:255'],
            'phone_mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'line_id' => ['nullable', 'string', 'max:255'],
            // Work address fields
            'useHomeAddress' => ['nullable', 'boolean'],
            'companyType' => ['nullable', 'string', 'max:255'],
            'companyTypeOther' => ['nullable', 'string', 'max:255'],
            'companyName' => ['nullable', 'string', 'max:255'],
            'businessType' => ['nullable', 'string', 'max:255'],
            'workOccupation' => ['nullable', 'string', 'max:255'],
            'workPosition' => ['nullable', 'string', 'max:255'],
            'workYears' => ['nullable', 'integer', 'min:0'],
            'workMonths' => ['nullable', 'integer', 'min:0', 'max:11'],
            'workAddressNo' => ['nullable', 'string', 'max:255'],
            'workAddressFloor' => ['nullable', 'string', 'max:255'],
            'workAddressVillage' => ['nullable', 'string', 'max:255'],
            'workAddressBuilding' => ['nullable', 'string', 'max:255'],
            'workAddressSoi' => ['nullable', 'string', 'max:255'],
            'workAddressRoad' => ['nullable', 'string', 'max:255'],
            'workAddressSubdistrict' => ['nullable', 'string', 'max:255'],
            'workAddressDistrict' => ['nullable', 'string', 'max:255'],
            'workAddressProvince' => ['nullable', 'string', 'max:255'],
            'workAddressPostal' => ['nullable', 'string', 'max:255'],
            'workPhone' => ['nullable', 'string', 'max:255'],
            // Previous work fields
            'previousCompanyName' => ['nullable', 'string', 'max:255'],
            'previousBusinessType' => ['nullable', 'string', 'max:255'],
            'previousPosition' => ['nullable', 'string', 'max:255'],
            'previousIncome' => ['nullable', 'numeric'],
            'previousWorkYears' => ['nullable', 'integer', 'min:0'],
            'previousPhone' => ['nullable', 'string', 'max:255'],
            // Document delivery fields
            'documentDelivery' => ['nullable', 'string', 'max:255'],
            'documentEmail' => ['nullable', 'email', 'max:255'],
            // Reference person fields
            'refName' => ['nullable', 'string', 'max:255'],
            'refRelation' => ['nullable', 'string', 'max:255'],
            'refAddressNo' => ['nullable', 'string', 'max:255'],
            'refAddressFloor' => ['nullable', 'string', 'max:255'],
            'refAddressVillage' => ['nullable', 'string', 'max:255'],
            'refAddressBuilding' => ['nullable', 'string', 'max:255'],
            'refAddressSoi' => ['nullable', 'string', 'max:255'],
            'refAddressRoad' => ['nullable', 'string', 'max:255'],
            'refAddressSubdistrict' => ['nullable', 'string', 'max:255'],
            'refAddressDistrict' => ['nullable', 'string', 'max:255'],
            'refAddressProvince' => ['nullable', 'string', 'max:255'],
            'refAddressPostal' => ['nullable', 'string', 'max:255'],
            'refPhoneHome' => ['nullable', 'string', 'max:255'],
            'refPhoneMobile' => ['nullable', 'string', 'max:255'],
            'refEmail' => ['nullable', 'email', 'max:255'],
            'refLineId' => ['nullable', 'string', 'max:255'],
            // Loan request fields
            'loanTerm' => ['nullable', 'integer', 'in:12,24,36,48,50'],
            'loanAmountType' => ['nullable', 'string', 'in:full,custom'],
            'customLoanAmount' => ['nullable', 'numeric', 'min:0'],
            'loanPurpose' => ['nullable', 'string', 'max:255'],
            'bankName' => ['nullable', 'string', 'max:255'],
            'bankBranch' => ['nullable', 'string', 'max:255'],
            'accountName' => ['nullable', 'string', 'max:255'],
            'accountType' => ['nullable', 'string', 'max:255'],
            'accountNumber' => ['nullable', 'string', 'max:255'],
        ]);

        // Handle dwelling type other
        if (isset($validated['dwelling_type']) && $validated['dwelling_type'] === 'อาศัยอยู่กับผู้อื่น' && !empty($request->dwelling_type_other)) {
            $validated['dwelling_type'] = 'อาศัยอยู่กับผู้อื่น: ' . $request->dwelling_type_other;
        }

        // Handle residence status other
        if (isset($validated['residence_status']) && $validated['residence_status'] === 'อื่นๆ' && !empty($request->residence_status_other)) {
            $validated['residence_status'] = $request->residence_status_other;
        }

        // Handle company type other
        if (isset($validated['companyType']) && $validated['companyType'] === 'อื่นๆ' && !empty($request->companyTypeOther)) {
            $validated['companyType'] = $request->companyTypeOther;
        }

        if (isset($validated['title']) && $validated['title'] === 'อื่นๆ' && !empty($request->title_other)) {
            $validated['title'] = $request->title_other;
        }

        // Handle applicant's other occupation
        if (isset($validated['occupation']) && $validated['occupation'] === 'อื่นๆ' && !empty($request->occupationOther)) {
            $validated['occupation'] = $request->occupationOther;
        }

        // Handle spouse's other title
        if (isset($validated['spouse_title']) && $validated['spouse_title'] === 'อื่นๆ' && !empty($request->spouse_title_other)) {
            $validated['spouse_title'] = $request->spouse_title_other;
        }

        // Handle spouse's other occupation
        if (isset($validated['spouse_occupation']) && $validated['spouse_occupation'] === 'อื่นๆ' && !empty($request->spouseOccupationOther)) {
            $validated['spouse_occupation'] = $request->spouseOccupationOther;
        }

        // Map camelCase to snake_case for DB
        $data = [
            'app_date' => $validated['app_date'] ?? null,
            'app_no' => $validated['app_no'] ?? null,
            'officer_name' => $validated['officer_name'] ?? null,
            'officer_phone' => $validated['officer_phone'] ?? null,
            'title' => $validated['title'] ?? null,
            'name' => $validated['name'],
            'name_en' => $validated['name_en'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'id_card' => $validated['id_card'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'age' => $validated['age'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'education' => $validated['education'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'income' => $validated['income'] ?? null,
            'extra_income' => $validated['extraIncome'] ?? null,
            'extra_income_source' => $validated['extraIncomeSource'] ?? null,
            'business_income' => $validated['businessIncome'] ?? null,
            'average_monthly_income' => $validated['averageMonthlyIncome'] ?? null,
            'has_other_debts' => $validated['hasOtherDebts'] ?? null,
            'other_debt_installment' => $validated['otherDebtInstallment'] ?? null,
            'has_existing_loan' => $validated['hasExistingLoan'] ?? null,
            'spouse_title' => $validated['spouse_title'] ?? null,
            'spouse_name' => $validated['spouse_name'] ?? null,
            'spouse_phone' => $validated['spouse_phone'] ?? null,
            'spouse_mobile' => $validated['spouse_mobile'] ?? null,
            'spouse_education' => $validated['spouse_education'] ?? null,
            'spouse_occupation' => $validated['spouse_occupation'] ?? null,
            'spouse_company' => $validated['spouse_company'] ?? null,
            'spouse_income' => $validated['spouse_income'] ?? null,
            'dwelling_type' => $validated['dwelling_type'] ?? null,
            'residence_status' => $validated['residence_status'] ?? null,
            'residence_rent_amount' => $validated['residence_rent_amount'] ?? null,
            'residence_years' => $validated['residence_years'] ?? null,
            'address_no' => $validated['address_no'] ?? null,
            'address_floor' => $validated['address_floor'] ?? null,
            'address_village' => $validated['address_village'] ?? null,
            'address_building' => $validated['address_building'] ?? null,
            'address_soi' => $validated['address_soi'] ?? null,
            'address_road' => $validated['address_road'] ?? null,
            'address_subdistrict' => $validated['address_subdistrict'] ?? null,
            'address_district' => $validated['address_district'] ?? null,
            'address_province' => $validated['address_province'] ?? null,
            'address_postal' => $validated['address_postal'] ?? null,
            'phone_home' => $validated['phone_home'] ?? null,
            'phone_mobile' => $validated['phone_mobile'] ?? null,
            'email' => $validated['email'] ?? null,
            'line_id' => $validated['line_id'] ?? null,
            'use_home_address' => $validated['useHomeAddress'] ?? false,
            'company_type' => $validated['companyType'] ?? null,
            'company_name' => $validated['companyName'] ?? null,
            'business_type' => $validated['businessType'] ?? null,
            'work_occupation' => $validated['workOccupation'] ?? null,
            'work_position' => $validated['workPosition'] ?? null,
            'work_years' => $validated['workYears'] ?? null,
            'work_months' => $validated['workMonths'] ?? null,
            'work_address_no' => $validated['workAddressNo'] ?? null,
            'work_address_floor' => $validated['workAddressFloor'] ?? null,
            'work_address_village' => $validated['workAddressVillage'] ?? null,
            'work_address_building' => $validated['workAddressBuilding'] ?? null,
            'work_address_soi' => $validated['workAddressSoi'] ?? null,
            'work_address_road' => $validated['workAddressRoad'] ?? null,
            'work_address_subdistrict' => $validated['workAddressSubdistrict'] ?? null,
            'work_address_district' => $validated['workAddressDistrict'] ?? null,
            'work_address_province' => $validated['workAddressProvince'] ?? null,
            'work_address_postal' => $validated['workAddressPostal'] ?? null,
            'work_phone' => $validated['workPhone'] ?? null,
            'previous_company_name' => $validated['previousCompanyName'] ?? null,
            'previous_business_type' => $validated['previousBusinessType'] ?? null,
            'previous_position' => $validated['previousPosition'] ?? null,
            'previous_income' => $validated['previousIncome'] ?? null,
            'previous_work_years' => $validated['previousWorkYears'] ?? null,
            'previous_phone' => $validated['previousPhone'] ?? null,
            'document_delivery' => $validated['documentDelivery'] ?? null,
            'document_email' => $validated['documentEmail'] ?? null,
            'ref_name' => $validated['refName'] ?? null,
            'ref_relation' => $validated['refRelation'] ?? null,
            'ref_address_no' => $validated['refAddressNo'] ?? null,
            'ref_address_floor' => $validated['refAddressFloor'] ?? null,
            'ref_address_village' => $validated['refAddressVillage'] ?? null,
            'ref_address_building' => $validated['refAddressBuilding'] ?? null,
            'ref_address_soi' => $validated['refAddressSoi'] ?? null,
            'ref_address_road' => $validated['refAddressRoad'] ?? null,
            'ref_address_subdistrict' => $validated['refAddressSubdistrict'] ?? null,
            'ref_address_district' => $validated['refAddressDistrict'] ?? null,
            'ref_address_province' => $validated['refAddressProvince'] ?? null,
            'ref_address_postal' => $validated['refAddressPostal'] ?? null,
            'ref_phone_home' => $validated['refPhoneHome'] ?? null,
            'ref_phone_mobile' => $validated['refPhoneMobile'] ?? null,
            'ref_email' => $validated['refEmail'] ?? null,
            'ref_line_id' => $validated['refLineId'] ?? null,
            'loan_term' => $validated['loanTerm'] ?? null,
            'loan_amount_type' => $validated['loanAmountType'] ?? null,
            'custom_loan_amount' => $validated['customLoanAmount'] ?? null,
            'loan_purpose' => $validated['loanPurpose'] ?? null,
            'bank_name' => $validated['bankName'] ?? null,
            'bank_branch' => $validated['bankBranch'] ?? null,
            'account_name' => $validated['accountName'] ?? null,
            'account_type' => $validated['accountType'] ?? null,
            'account_number' => $validated['accountNumber'] ?? null,
            'signed' => true,
            'signed_at' => now(),
            'signature_data' => $request->signatureData ?? null,
        ];

        ConsentForm::create($data);

        return redirect()
            ->route('consent.index')
            ->with('success', 'สร้างใบยินยอมสำหรับ ' . $validated['name'] . ' เรียบร้อยแล้ว');
    }
}