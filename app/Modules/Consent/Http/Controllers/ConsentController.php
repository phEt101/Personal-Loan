<?php

namespace App\Modules\Consent\Http\Controllers;

use App\Models\User;
use App\Modules\Consent\Models\ConsentForm;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConsentController extends Controller
{
    public function modalConsentForm()
    {
        $lastAppNo = ConsentForm::whereNotNull('app_no')->orderBy('id', 'desc')->value('app_no');
        $nextAppNo = $lastAppNo ? str_pad((int) $lastAppNo + 1, 13, '0', STR_PAD_LEFT) : '0000000000001';

        return view('consent::consent_form_modal', compact('nextAppNo'));
    }

    public function modalConsentView()
    {
        return view('consent::consent_view_modal');
    }

    public function postCodeOptions(Request $request)
    {
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

    public function postCodeProvinces(Request $request)
    {
        if (!Schema::hasTable('post_codes')) {
            return response()->json([]);
        }

        $country = (string) ($request->query('country_code') ?: 'TH');

        $items = DB::table('post_codes')
            ->where('country_code', $country)
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->where('province', '!=', 'N/A')
            ->where('province', '!=', '#N/A')
            ->select('province')
            ->distinct()
            ->orderBy('province')
            ->pluck('province')
            ->values();

        return response()->json($items);
    }

    public function postCodeCities(Request $request)
    {
        if (!Schema::hasTable('post_codes')) {
            return response()->json([]);
        }

        $country = (string) ($request->query('country_code') ?: 'TH');
        $province = (string) ($request->query('province') ?: '');

        if ($province === '') {
            return response()->json([]);
        }

        $items = DB::table('post_codes')
            ->where('country_code', $country)
            ->where('province', $province)
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->values();

        return response()->json($items);
    }

    public function postCodeDistricts(Request $request)
    {
        if (!Schema::hasTable('post_codes')) {
            return response()->json([]);
        }

        $country = (string) ($request->query('country_code') ?: 'TH');
        $province = (string) ($request->query('province') ?: '');
        $city = (string) ($request->query('city') ?: '');

        if ($province === '' || $city === '') {
            return response()->json([]);
        }

        $items = DB::table('post_codes')
            ->where('country_code', $country)
            ->where('province', $province)
            ->where('city', $city)
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->pluck('district')
            ->values();

        return response()->json($items);
    }

    public function postCodePostCodes(Request $request)
    {
        if (!Schema::hasTable('post_codes')) {
            return response()->json([]);
        }

        $country = (string) ($request->query('country_code') ?: 'TH');
        $province = (string) ($request->query('province') ?: '');
        $city = (string) ($request->query('city') ?: '');
        $district = (string) ($request->query('district') ?: '');

        if ($province === '' || $city === '' || $district === '') {
            return response()->json([]);
        }

        $items = DB::table('post_codes')
            ->where('country_code', $country)
            ->where('province', $province)
            ->where('city', $city)
            ->where('district', $district)
            ->select('post_code')
            ->distinct()
            ->orderBy('post_code')
            ->pluck('post_code')
            ->values();

        return response()->json($items);
    }

    public function index()
    {
        $customers = ConsentForm::orderBy('id', 'desc')->get()->map(function ($form) {
            return (object) array_merge($form->toArray(), [
                'transaction_date' => $form->created_at?->format('d/m/Y'),
                'signed_date' => $form->signed_at?->format('Y-m-d'),
                'signatureData' => $form->signature_data,
                'status' => $form->status,
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
        $approved = $customers->where('status', 'approved')->count();
        $rejected = $customers->where('status', 'rejected')->count();

        // Generate next app_no
        $lastAppNo = ConsentForm::whereNotNull('app_no')->orderBy('id', 'desc')->value('app_no');
        $nextAppNo = $lastAppNo ? str_pad((int)$lastAppNo + 1, 13, '0', STR_PAD_LEFT) : '0000000000001';

        return view('consent::index', compact('customers', 'total', 'approved', 'rejected', 'nextAppNo'));
    }

    public function store(Request $request)
    {
        $consent = $this->saveConsent($request);

        return redirect()
            ->route('consent.index')
            ->with('success', 'สร้างใบยินยอมสำหรับ ' . $consent->name . ' เรียบร้อยแล้ว (สถานะ: ' . ($consent->status === 'approved' ? 'ผ่าน' : 'ไม่ผ่าน') . ')');
    }

    public function update(Request $request, ConsentForm $consent)
    {
        $consent = $this->saveConsent($request, $consent);

        return redirect()
            ->route('consent.index')
            ->with('success', 'แก้ไขใบยินยอมของ ' . $consent->name . ' เรียบร้อยแล้ว (สถานะ: ' . ($consent->status === 'approved' ? 'ผ่าน' : 'ไม่ผ่าน') . ')');
    }

    public function data(ConsentForm $consent)
    {
        return response()->json((object) array_merge($consent->toArray(), [
            'transaction_date' => $consent->created_at?->format('d/m/Y'),
            'signed_date' => $consent->signed_at?->format('Y-m-d'),
            'signatureData' => $consent->signature_data,
            'status' => $consent->status,
            'extraIncome' => $consent->extra_income,
            'extraIncomeSource' => $consent->extra_income_source,
            'businessIncome' => $consent->business_income,
            'averageMonthlyIncome' => $consent->average_monthly_income,
            'hasOtherDebts' => $consent->has_other_debts,
            'otherDebtInstallment' => $consent->other_debt_installment,
            'hasExistingLoan' => $consent->has_existing_loan,
            'spouseTitle' => $consent->spouse_title,
            'spouseName' => $consent->spouse_name,
            'spousePhone' => $consent->spouse_phone,
            'spouseMobile' => $consent->spouse_mobile,
            'spouseEducation' => $consent->spouse_education,
            'spouseOccupation' => $consent->spouse_occupation,
            'spouseCompany' => $consent->spouse_company,
            'spouseIncome' => $consent->spouse_income,
            'dwellingType' => $consent->dwelling_type,
            'residenceStatus' => $consent->residence_status,
            'residenceRentAmount' => $consent->residence_rent_amount,
            'residenceYears' => $consent->residence_years,
            'addressNo' => $consent->address_no,
            'addressFloor' => $consent->address_floor,
            'addressVillage' => $consent->address_village,
            'addressBuilding' => $consent->address_building,
            'addressSoi' => $consent->address_soi,
            'addressRoad' => $consent->address_road,
            'addressSubdistrict' => $consent->address_subdistrict,
            'addressDistrict' => $consent->address_district,
            'addressProvince' => $consent->address_province,
            'addressPostal' => $consent->address_postal,
            'phoneHome' => $consent->phone_home,
            'phoneMobile' => $consent->phone_mobile,
            'lineId' => $consent->line_id,
            'useHomeAddress' => $consent->use_home_address,
            'companyType' => $consent->company_type,
            'companyName' => $consent->company_name,
            'businessType' => $consent->business_type,
            'workOccupation' => $consent->work_occupation,
            'workPosition' => $consent->work_position,
            'workYears' => $consent->work_years,
            'workMonths' => $consent->work_months,
            'workAddressNo' => $consent->work_address_no,
            'workAddressFloor' => $consent->work_address_floor,
            'workAddressVillage' => $consent->work_address_village,
            'workAddressBuilding' => $consent->work_address_building,
            'workAddressSoi' => $consent->work_address_soi,
            'workAddressRoad' => $consent->work_address_road,
            'workAddressSubdistrict' => $consent->work_address_subdistrict,
            'workAddressDistrict' => $consent->work_address_district,
            'workAddressProvince' => $consent->work_address_province,
            'workAddressPostal' => $consent->work_address_postal,
            'workPhone' => $consent->work_phone,
            'previousCompanyName' => $consent->previous_company_name,
            'previousBusinessType' => $consent->previous_business_type,
            'previousPosition' => $consent->previous_position,
            'previousIncome' => $consent->previous_income,
            'previousWorkYears' => $consent->previous_work_years,
            'previousPhone' => $consent->previous_phone,
            'documentDelivery' => $consent->document_delivery,
            'documentEmail' => $consent->document_email,
            'refName' => $consent->ref_name,
            'refRelation' => $consent->ref_relation,
            'refAddressNo' => $consent->ref_address_no,
            'refAddressFloor' => $consent->ref_address_floor,
            'refAddressVillage' => $consent->ref_address_village,
            'refAddressBuilding' => $consent->ref_address_building,
            'refAddressSoi' => $consent->ref_address_soi,
            'refAddressRoad' => $consent->ref_address_road,
            'refAddressSubdistrict' => $consent->ref_address_subdistrict,
            'refAddressDistrict' => $consent->ref_address_district,
            'refAddressProvince' => $consent->ref_address_province,
            'refAddressPostal' => $consent->ref_address_postal,
            'refPhoneHome' => $consent->ref_phone_home,
            'refPhoneMobile' => $consent->ref_phone_mobile,
            'refEmail' => $consent->ref_email,
            'refLineId' => $consent->ref_line_id,
            'loanTerm' => $consent->loan_term,
            'loanAmountType' => $consent->loan_amount_type,
            'customLoanAmount' => $consent->custom_loan_amount,
            'loanPurpose' => $consent->loan_purpose,
            'bankName' => $consent->bank_name,
            'bankBranch' => $consent->bank_branch,
            'accountName' => $consent->account_name,
            'accountType' => $consent->account_type,
            'accountNumber' => $consent->account_number,
        ]));
    }

    public function destroy(ConsentForm $consent)
    {
        $name = $consent->name;
        $appNo = $consent->app_no;

        $consent->delete();

        return redirect()
            ->route('consent.index')
            ->with('success', 'ลบใบยินยอมเลขที่ ' . ($appNo ?: '-') . ' ของ ' . $name . ' เรียบร้อยแล้ว');
    }

    private function saveConsent(Request $request, ?ConsentForm $consent = null): ConsentForm
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
            'id_card' => ['required', 'string', 'size:13', 'regex:/^\\d{13}$/'],
            'gender' => ['nullable', 'string', 'max:10'],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'education' => ['nullable', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'occupationOther' => ['nullable', 'string', 'max:100'],
            'income' => ['required', 'numeric', 'min:0'],
            'extraIncome' => ['nullable', 'numeric'],
            'extraIncomeSource' => ['nullable', 'string', 'max:255'],
            'businessIncome' => ['nullable', 'string', 'max:255'],
            'averageMonthlyIncome' => ['nullable', 'numeric'],
            'hasOtherDebts' => ['required', 'string', 'max:10'],
            'otherDebtInstallment' => ['nullable', 'required_if:hasOtherDebts,มี', 'numeric', 'min:0'],
            'hasExistingLoan' => ['nullable', 'string', 'max:10'],
            'existingLoanInstallment' => ['nullable', 'numeric'],
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
            'phone_mobile' => ['nullable', 'string', 'max:20', 'regex:/^\\d{9,10}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'line_id' => ['nullable', 'string', 'max:255'],
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
            'previousCompanyName' => ['nullable', 'string', 'max:255'],
            'previousBusinessType' => ['nullable', 'string', 'max:255'],
            'previousPosition' => ['nullable', 'string', 'max:255'],
            'previousIncome' => ['nullable', 'numeric'],
            'previousWorkYears' => ['nullable', 'integer', 'min:0'],
            'previousPhone' => ['nullable', 'string', 'max:255'],
            'documentDelivery' => ['nullable', 'string', 'max:255'],
            'documentEmail' => ['nullable', 'email', 'max:255'],
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
            'loanTerm' => ['nullable', 'integer', 'in:12,24,36,48,50'],
            'loanAmountType' => ['nullable', 'string', 'in:full,custom'],
            'customLoanAmount' => ['nullable', 'numeric', 'min:0'],
            'loanPurpose' => ['nullable', 'string', 'max:255'],
            'bankName' => ['nullable', 'string', 'max:255'],
            'bankBranch' => ['nullable', 'string', 'max:255'],
            'accountName' => ['nullable', 'string', 'max:255'],
            'accountType' => ['nullable', 'string', 'max:255'],
            'accountNumber' => ['nullable', 'string', 'max:255'],
            'signatureData' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded) || count($decoded) === 0) {
                        $fail('กรุณาเซ็นลายเซ็นผู้ขอสินเชื่อก่อนบันทึก');
                    }
                },
            ],
        ], [
            'signatureData.required' => 'กรุณาเซ็นลายเซ็นผู้ขอสินเชื่อก่อนบันทึก',
        ]);

        if (($validated['dwelling_type'] ?? null) === 'อาศัยอยู่กับผู้อื่น' && $request->filled('dwelling_type_other')) {
            $validated['dwelling_type'] = 'อาศัยอยู่กับผู้อื่น: ' . $request->dwelling_type_other;
        }

        if (($validated['residence_status'] ?? null) === 'อื่นๆ' && $request->filled('residence_status_other')) {
            $validated['residence_status'] = $request->residence_status_other;
        }

        if (($validated['companyType'] ?? null) === 'อื่นๆ' && $request->filled('companyTypeOther')) {
            $validated['companyType'] = $request->companyTypeOther;
        }

        if (($validated['title'] ?? null) === 'อื่นๆ' && $request->filled('title_other')) {
            $validated['title'] = $request->title_other;
        }

        if (($validated['occupation'] ?? null) === 'อื่นๆ' && $request->filled('occupationOther')) {
            $validated['occupation'] = $request->occupationOther;
        }

        if (($validated['spouse_title'] ?? null) === 'อื่นๆ' && $request->filled('spouse_title_other')) {
            $validated['spouse_title'] = $request->spouse_title_other;
        }

        if (($validated['spouse_occupation'] ?? null) === 'อื่นๆ' && $request->filled('spouseOccupationOther')) {
            $validated['spouse_occupation'] = $request->spouseOccupationOther;
        }

        if (($validated['hasOtherDebts'] ?? null) === 'ไม่มี') {
            $validated['otherDebtInstallment'] = 0;
        }

        $status = 'approved';
        $age = (int) ($validated['age'] ?? 0);
        $income = (float) ($validated['income'] ?? 0);
        $otherDebtInstallment = (float) ($validated['otherDebtInstallment'] ?? 0);

        if ($age < 20 || $age > 50) {
            $status = 'rejected';
        }

        if ($income < 15000) {
            $status = 'rejected';
        }

        if ($income > 0 && $otherDebtInstallment > ($income / 2)) {
            $status = 'rejected';
        }

        $data = [
            'app_date' => $validated['app_date'] ?? null,
            'app_no' => $validated['app_no'] ?? $consent?->app_no,
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
            'signed_at' => $consent?->signed_at ?? now(),
            'signature_data' => $request->input('signatureData'),
            'status' => $status,
        ];

        if ($consent) {
            $consent->update($data);
            return $consent->fresh();
        }

        return ConsentForm::create($data);
    }
}
