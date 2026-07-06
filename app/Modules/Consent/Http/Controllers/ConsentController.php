<?php

namespace App\Modules\Consent\Http\Controllers;

use App\Modules\Consent\Models\ConsentAddress;
use App\Modules\Consent\Models\ConsentApplicant;
use App\Modules\Consent\Models\ConsentApplication;
use App\Modules\Consent\Models\ConsentContact;
use App\Modules\Consent\Models\ConsentDisbursementAccount;
use App\Modules\Consent\Models\ConsentDocumentDelivery;
use App\Modules\Consent\Models\ConsentEmployment;
use App\Modules\Consent\Models\ConsentLoanRequest;
use App\Modules\Consent\Models\ConsentPreviousEmployment;
use App\Modules\Consent\Models\ConsentReference;
use App\Modules\Consent\Models\ConsentSpouse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConsentController extends Controller
{
    public function modalConsentForm()
    {
        $lastAppNo = ConsentApplication::whereNotNull('app_no')->orderBy('id', 'desc')->value('app_no');
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
        $customers = ConsentApplication::query()
            ->with(['applicant:id,application_id,name'])
            ->orderByDesc('id')
            ->paginate(10);

        $total = ConsentApplication::count();
        $approved = ConsentApplication::where('status', 'approved')->count();
        $rejected = ConsentApplication::where('status', 'rejected')->count();

        $lastAppNo = ConsentApplication::whereNotNull('app_no')->orderBy('id', 'desc')->value('app_no');
        $nextAppNo = $lastAppNo ? str_pad((int)$lastAppNo + 1, 13, '0', STR_PAD_LEFT) : '0000000000001';

        return view('consent::index', compact('customers', 'total', 'approved', 'rejected', 'nextAppNo'));
    }

    public function store(Request $request)
    {
        $consent = $this->saveConsent($request);

        return redirect()
            ->route('consent.index')
            ->with('success', 'สร้างใบยินยอมสำหรับ ' . ($consent->applicant?->name ?: '-') . ' เรียบร้อยแล้ว (สถานะ: ' . ($consent->status === 'approved' ? 'ผ่าน' : 'ไม่ผ่าน') . ')');
    }

    public function update(Request $request, ConsentApplication $consent)
    {
        $consent = $this->saveConsent($request, $consent);

        return redirect()
            ->route('consent.index')
            ->with('success', 'แก้ไขใบยินยอมของ ' . ($consent->applicant?->name ?: '-') . ' เรียบร้อยแล้ว (สถานะ: ' . ($consent->status === 'approved' ? 'ผ่าน' : 'ไม่ผ่าน') . ')');
    }

    public function data(ConsentApplication $consent)
    {
        $consent->load([
            'applicant',
            'spouse',
            'contact',
            'homeAddress',
            'workAddress',
            'referenceAddress',
            'employment',
            'previousEmployment',
            'documentDelivery',
            'reference',
            'loanRequest',
            'disbursementAccount',
        ]);

        return response()->json((object) $this->toFrontendData($consent));
    }

    public function destroy(ConsentApplication $consent)
    {
        $consent->load('applicant:id,application_id,name');
        $name = $consent->applicant?->name;
        $appNo = $consent->app_no;

        $consent->delete();

        return redirect()
            ->route('consent.index')
            ->with('success', 'ลบใบยินยอมเลขที่ ' . ($appNo ?: '-') . ' ของ ' . $name . ' เรียบร้อยแล้ว');
    }

    private function saveConsent(Request $request, ?ConsentApplication $consent = null): ConsentApplication
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

        $hasOtherDebts = ($validated['hasOtherDebts'] ?? null) === 'มี'
            ? true
            : (($validated['hasOtherDebts'] ?? null) === 'ไม่มี' ? false : null);

        $hasExistingLoan = ($validated['hasExistingLoan'] ?? null) === 'มี'
            ? true
            : (($validated['hasExistingLoan'] ?? null) === 'ไม่มี' ? false : null);

        $applicationPayload = [
            'app_date' => $validated['app_date'] ?? null,
            'app_no' => $validated['app_no'] ?? $consent?->app_no,
            'officer_name' => $validated['officer_name'] ?? null,
            'officer_phone' => $validated['officer_phone'] ?? null,
            'signed' => true,
            'signed_at' => $consent?->signed_at ?? now(),
            'signature_data' => $request->input('signatureData'),
            'status' => $status,
        ];

        return DB::transaction(function () use ($consent, $applicationPayload, $validated, $hasOtherDebts, $hasExistingLoan) {
            $application = $consent
                ? tap($consent)->update($applicationPayload)
                : ConsentApplication::create($applicationPayload);

            ConsentApplicant::updateOrCreate(
                ['application_id' => $application->id],
                [
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
                    'has_other_debts' => $hasOtherDebts,
                    'other_debt_installment' => $validated['otherDebtInstallment'] ?? null,
                    'has_existing_loan' => $hasExistingLoan,
                ]
            );

            $spousePayload = [
                'spouse_title' => $validated['spouse_title'] ?? null,
                'spouse_name' => $validated['spouse_name'] ?? null,
                'spouse_phone' => $validated['spouse_phone'] ?? null,
                'spouse_mobile' => $validated['spouse_mobile'] ?? null,
                'spouse_education' => $validated['spouse_education'] ?? null,
                'spouse_occupation' => $validated['spouse_occupation'] ?? null,
                'spouse_company' => $validated['spouse_company'] ?? null,
                'spouse_income' => $validated['spouse_income'] ?? null,
            ];

            $hasSpouseData = false;
            foreach ($spousePayload as $value) {
                if ($value !== null && $value !== '') {
                    $hasSpouseData = true;
                    break;
                }
            }

            if ($hasSpouseData) {
                ConsentSpouse::updateOrCreate(['application_id' => $application->id], $spousePayload);
            } else {
                ConsentSpouse::where('application_id', $application->id)->delete();
            }

            ConsentContact::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'phone_home' => $validated['phone_home'] ?? null,
                    'phone_mobile' => $validated['phone_mobile'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'line_id' => $validated['line_id'] ?? null,
                ]
            );

            ConsentAddress::updateOrCreate(
                ['application_id' => $application->id, 'kind' => 'home'],
                [
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
                ]
            );

            ConsentEmployment::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'use_home_address' => (bool) ($validated['useHomeAddress'] ?? false),
                    'company_type' => $validated['companyType'] ?? null,
                    'company_name' => $validated['companyName'] ?? null,
                    'business_type' => $validated['businessType'] ?? null,
                    'work_occupation' => $validated['workOccupation'] ?? null,
                    'work_position' => $validated['workPosition'] ?? null,
                    'work_years' => $validated['workYears'] ?? null,
                    'work_months' => $validated['workMonths'] ?? null,
                    'work_phone' => $validated['workPhone'] ?? null,
                ]
            );

            ConsentAddress::updateOrCreate(
                ['application_id' => $application->id, 'kind' => 'work'],
                [
                    'address_no' => $validated['workAddressNo'] ?? null,
                    'address_floor' => $validated['workAddressFloor'] ?? null,
                    'address_village' => $validated['workAddressVillage'] ?? null,
                    'address_building' => $validated['workAddressBuilding'] ?? null,
                    'address_soi' => $validated['workAddressSoi'] ?? null,
                    'address_road' => $validated['workAddressRoad'] ?? null,
                    'address_subdistrict' => $validated['workAddressSubdistrict'] ?? null,
                    'address_district' => $validated['workAddressDistrict'] ?? null,
                    'address_province' => $validated['workAddressProvince'] ?? null,
                    'address_postal' => $validated['workAddressPostal'] ?? null,
                ]
            );

            ConsentPreviousEmployment::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'previous_company_name' => $validated['previousCompanyName'] ?? null,
                    'previous_business_type' => $validated['previousBusinessType'] ?? null,
                    'previous_position' => $validated['previousPosition'] ?? null,
                    'previous_income' => $validated['previousIncome'] ?? null,
                    'previous_work_years' => $validated['previousWorkYears'] ?? null,
                    'previous_phone' => $validated['previousPhone'] ?? null,
                ]
            );

            ConsentDocumentDelivery::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'document_delivery' => $validated['documentDelivery'] ?? null,
                    'document_email' => $validated['documentEmail'] ?? null,
                ]
            );

            ConsentReference::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'ref_name' => $validated['refName'] ?? null,
                    'ref_relation' => $validated['refRelation'] ?? null,
                    'ref_phone_home' => $validated['refPhoneHome'] ?? null,
                    'ref_phone_mobile' => $validated['refPhoneMobile'] ?? null,
                    'ref_email' => $validated['refEmail'] ?? null,
                    'ref_line_id' => $validated['refLineId'] ?? null,
                ]
            );

            ConsentAddress::updateOrCreate(
                ['application_id' => $application->id, 'kind' => 'reference'],
                [
                    'address_no' => $validated['refAddressNo'] ?? null,
                    'address_floor' => $validated['refAddressFloor'] ?? null,
                    'address_village' => $validated['refAddressVillage'] ?? null,
                    'address_building' => $validated['refAddressBuilding'] ?? null,
                    'address_soi' => $validated['refAddressSoi'] ?? null,
                    'address_road' => $validated['refAddressRoad'] ?? null,
                    'address_subdistrict' => $validated['refAddressSubdistrict'] ?? null,
                    'address_district' => $validated['refAddressDistrict'] ?? null,
                    'address_province' => $validated['refAddressProvince'] ?? null,
                    'address_postal' => $validated['refAddressPostal'] ?? null,
                ]
            );

            ConsentLoanRequest::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'loan_term' => $validated['loanTerm'] ?? null,
                    'loan_amount_type' => $validated['loanAmountType'] ?? null,
                    'custom_loan_amount' => $validated['customLoanAmount'] ?? null,
                    'loan_purpose' => $validated['loanPurpose'] ?? null,
                ]
            );

            ConsentDisbursementAccount::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'bank_name' => $validated['bankName'] ?? null,
                    'bank_branch' => $validated['bankBranch'] ?? null,
                    'account_name' => $validated['accountName'] ?? null,
                    'account_type' => $validated['accountType'] ?? null,
                    'account_number' => $validated['accountNumber'] ?? null,
                ]
            );

            return $application->fresh()->load('applicant');
        });
    }

    private function toFrontendData(ConsentApplication $consent): array
    {
        $applicant = $consent->applicant;
        $spouse = $consent->spouse;
        $contact = $consent->contact;
        $home = $consent->homeAddress;
        $work = $consent->workAddress;
        $referenceAddress = $consent->referenceAddress;
        $employment = $consent->employment;
        $previousEmployment = $consent->previousEmployment;
        $documentDelivery = $consent->documentDelivery;
        $reference = $consent->reference;
        $loan = $consent->loanRequest;
        $account = $consent->disbursementAccount;

        $hasOtherDebtsValue = $applicant?->has_other_debts;
        $hasOtherDebts = $hasOtherDebtsValue === null ? null : ($hasOtherDebtsValue ? 'มี' : 'ไม่มี');

        $hasExistingLoanValue = $applicant?->has_existing_loan;
        $hasExistingLoan = $hasExistingLoanValue === null ? null : ($hasExistingLoanValue ? 'มี' : 'ไม่มี');

        return array_merge($consent->toArray(), [
            'id' => $consent->encrypted_id,
            'transaction_date' => $consent->created_at?->format('d/m/Y'),
            'signed_date' => $consent->signed_at?->format('Y-m-d'),
            'signatureData' => $consent->signature_data,

            'title' => $applicant?->title,
            'name' => $applicant?->name,
            'name_en' => $applicant?->name_en,
            'dob' => $applicant?->dob?->format('Y-m-d'),
            'id_card' => $applicant?->id_card,
            'gender' => $applicant?->gender,
            'age' => $applicant?->age,
            'nationality' => $applicant?->nationality,
            'marital_status' => $applicant?->marital_status,
            'education' => $applicant?->education,
            'occupation' => $applicant?->occupation,
            'income' => $applicant?->income,
            'extra_income' => $applicant?->extra_income,
            'extra_income_source' => $applicant?->extra_income_source,
            'business_income' => $applicant?->business_income,
            'average_monthly_income' => $applicant?->average_monthly_income,
            'has_other_debts' => $hasOtherDebts,
            'other_debt_installment' => $applicant?->other_debt_installment,
            'has_existing_loan' => $hasExistingLoan,

            'spouse_title' => $spouse?->spouse_title,
            'spouse_name' => $spouse?->spouse_name,
            'spouse_phone' => $spouse?->spouse_phone,
            'spouse_mobile' => $spouse?->spouse_mobile,
            'spouse_education' => $spouse?->spouse_education,
            'spouse_occupation' => $spouse?->spouse_occupation,
            'spouse_company' => $spouse?->spouse_company,
            'spouse_income' => $spouse?->spouse_income,

            'dwelling_type' => $home?->dwelling_type,
            'residence_status' => $home?->residence_status,
            'residence_rent_amount' => $home?->residence_rent_amount,
            'residence_years' => $home?->residence_years,
            'address_no' => $home?->address_no,
            'address_floor' => $home?->address_floor,
            'address_village' => $home?->address_village,
            'address_building' => $home?->address_building,
            'address_soi' => $home?->address_soi,
            'address_road' => $home?->address_road,
            'address_subdistrict' => $home?->address_subdistrict,
            'address_district' => $home?->address_district,
            'address_province' => $home?->address_province,
            'address_postal' => $home?->address_postal,

            'phone_home' => $contact?->phone_home,
            'phone_mobile' => $contact?->phone_mobile,
            'email' => $contact?->email,
            'line_id' => $contact?->line_id,

            'use_home_address' => $employment?->use_home_address,
            'company_type' => $employment?->company_type,
            'company_name' => $employment?->company_name,
            'business_type' => $employment?->business_type,
            'work_occupation' => $employment?->work_occupation,
            'work_position' => $employment?->work_position,
            'work_years' => $employment?->work_years,
            'work_months' => $employment?->work_months,
            'work_phone' => $employment?->work_phone,

            'previous_company_name' => $previousEmployment?->previous_company_name,
            'previous_business_type' => $previousEmployment?->previous_business_type,
            'previous_position' => $previousEmployment?->previous_position,
            'previous_income' => $previousEmployment?->previous_income,
            'previous_work_years' => $previousEmployment?->previous_work_years,
            'previous_phone' => $previousEmployment?->previous_phone,

            'document_delivery' => $documentDelivery?->document_delivery,
            'document_email' => $documentDelivery?->document_email,

            'ref_name' => $reference?->ref_name,
            'ref_relation' => $reference?->ref_relation,
            'ref_phone_home' => $reference?->ref_phone_home,
            'ref_phone_mobile' => $reference?->ref_phone_mobile,
            'ref_email' => $reference?->ref_email,
            'ref_line_id' => $reference?->ref_line_id,

            'loan_term' => $loan?->loan_term,
            'loan_amount_type' => $loan?->loan_amount_type,
            'custom_loan_amount' => $loan?->custom_loan_amount,
            'loan_purpose' => $loan?->loan_purpose,

            'bank_name' => $account?->bank_name,
            'bank_branch' => $account?->bank_branch,
            'account_name' => $account?->account_name,
            'account_type' => $account?->account_type,
            'account_number' => $account?->account_number,

            'extraIncome' => $applicant?->extra_income,
            'extraIncomeSource' => $applicant?->extra_income_source,
            'businessIncome' => $applicant?->business_income,
            'averageMonthlyIncome' => $applicant?->average_monthly_income,
            'hasOtherDebts' => $hasOtherDebts,
            'otherDebtInstallment' => $applicant?->other_debt_installment,
            'hasExistingLoan' => $hasExistingLoan,
            'lineId' => $contact?->line_id,
            'useHomeAddress' => $employment?->use_home_address,
            'companyType' => $employment?->company_type,
            'companyName' => $employment?->company_name,
            'businessType' => $employment?->business_type,
            'workOccupation' => $employment?->work_occupation,
            'workPosition' => $employment?->work_position,
            'workYears' => $employment?->work_years,
            'workMonths' => $employment?->work_months,
            'workAddressNo' => $work?->address_no,
            'workAddressFloor' => $work?->address_floor,
            'workAddressVillage' => $work?->address_village,
            'workAddressBuilding' => $work?->address_building,
            'workAddressSoi' => $work?->address_soi,
            'workAddressRoad' => $work?->address_road,
            'workAddressSubdistrict' => $work?->address_subdistrict,
            'workAddressDistrict' => $work?->address_district,
            'workAddressProvince' => $work?->address_province,
            'workAddressPostal' => $work?->address_postal,
            'workPhone' => $employment?->work_phone,
            'previousCompanyName' => $previousEmployment?->previous_company_name,
            'previousBusinessType' => $previousEmployment?->previous_business_type,
            'previousPosition' => $previousEmployment?->previous_position,
            'previousIncome' => $previousEmployment?->previous_income,
            'previousWorkYears' => $previousEmployment?->previous_work_years,
            'previousPhone' => $previousEmployment?->previous_phone,
            'documentDelivery' => $documentDelivery?->document_delivery,
            'documentEmail' => $documentDelivery?->document_email,
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
            'refEmail' => $reference?->ref_email,
            'refLineId' => $reference?->ref_line_id,
            'loanTerm' => $loan?->loan_term,
            'loanAmountType' => $loan?->loan_amount_type,
            'customLoanAmount' => $loan?->custom_loan_amount,
            'loanPurpose' => $loan?->loan_purpose,
            'bankName' => $account?->bank_name,
            'bankBranch' => $account?->bank_branch,
            'accountName' => $account?->account_name,
            'accountType' => $account?->account_type,
            'accountNumber' => $account?->account_number,
        ]);
    }
}
