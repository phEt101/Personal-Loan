<?php

namespace App\Modules\Consent\Http\Controllers;

use App\Modules\Consent\Models\ConsentAddress;
use App\Modules\Consent\Models\ConsentApplicant;
use App\Modules\Consent\Models\ConsentApplication;
use App\Modules\Consent\Models\ConsentContact;
use App\Modules\Consent\Models\ConsentDisbursementAccount;
use App\Modules\Consent\Models\ConsentEmployment;
use App\Modules\Consent\Models\ConsentIncomeDocument;
use App\Modules\Consent\Models\ConsentLoanRequest;
use App\Modules\Consent\Models\ConsentPreviousEmployment;
use App\Modules\Consent\Models\ConsentReference;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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

    public function downloadIncomeDocument(ConsentApplication $consent, ConsentIncomeDocument $document)
    {
        if ((int) $document->application_id !== (int) $consent->id) {
            abort(404);
        }

        $disk = Storage::disk($document->disk);
        if (!$disk->exists($document->path)) {
            abort(404);
        }

        $mimeType = $document->mime_type ?: $disk->mimeType($document->path) ?: 'application/octet-stream';
        $fileName = $document->original_name;

        $stream = $disk->readStream($document->path);
        if ($stream === false) {
            abort(404);
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '', $fileName) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroyIncomeDocument(ConsentApplication $consent, ConsentIncomeDocument $document)
    {
        if ((int) $document->application_id !== (int) $consent->id) {
            abort(404);
        }

        $disk = Storage::disk($document->disk);
        if ($disk->exists($document->path)) {
            $disk->delete($document->path);
        }

        $document->delete();

        return response()->json(['ok' => true]);
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
            // 1. ข้อมูลใบคำขอ
            'app_date' => ['nullable', 'date'],
            'app_no' => ['nullable', 'string', 'max:13'],

            // 2. สำหรับเจ้าหน้าที่บริษัท
            'officer_name' => ['nullable', 'string', 'max:255'],
            'officer_phone' => ['nullable', 'string', 'max:20'],

            // 3. ข้อมูลส่วนตัวผู้ขอสินเชื่อ
            'title' => ['nullable', 'string', 'max:50'],
            'title_other' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'id_type' => ['required', 'in:id_card,passport'],
            'id_card' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    $documentType = $request->input('id_type', 'id_card');
                    $normalizedValue = strtoupper(trim((string) $value));

                    if ($documentType === 'passport') {
                        if (!preg_match('/^[A-Z0-9]{6,20}$/', $normalizedValue)) {
                            $fail('กรุณากรอกเลขหนังสือเดินทางเป็นตัวอักษรภาษาอังกฤษหรือตัวเลข 6-20 หลัก');
                        }

                        return;
                    }

                    if (!preg_match('/^\d{13}$/', $normalizedValue)) {
                        $fail('กรุณากรอกเลขบัตรประจำตัวประชาชน 13 หลัก');
                    }
                },
            ],
            'education' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:50'],

            // 4. ที่อยู่ปัจจุบัน
            'residence_status' => ['nullable', 'string', 'max:255'],
            'address_building' => ['nullable', 'string', 'max:255'],
            'address_room' => ['nullable', 'string', 'max:255'],
            'address_floor' => ['nullable', 'string', 'max:255'],
            'address_no' => ['nullable', 'string', 'max:255'],
            'address_village' => ['nullable', 'string', 'max:255'],
            'address_soi' => ['nullable', 'string', 'max:255'],
            'address_road' => ['nullable', 'string', 'max:255'],
            'address_subdistrict' => ['nullable', 'string', 'max:255'],
            'address_district' => ['nullable', 'string', 'max:255'],
            'address_province' => ['nullable', 'string', 'max:255'],
            'address_postal' => ['nullable', 'string', 'max:255'],
            'phone_home' => ['nullable', 'string', 'max:255'],
            'phone_mobile' => ['nullable', 'string', 'max:20', 'regex:/^\\d{9,10}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'documentDelivery' => ['nullable', 'string', 'max:255'],
            'documentAddressText' => ['nullable', 'string'],
            'documentAddressProvince' => ['nullable', 'string', 'max:255'],
            'documentAddressPostal' => ['nullable', 'string', 'max:255'],
            'birthPlaceAddress' => ['nullable', 'string'],

            // 5. ข้อมูลอาชีพ/สถานที่ทำงาน
            'useHomeAddress' => ['nullable', 'boolean'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'governmentLevel' => ['nullable', 'string', 'max:100'],
            'occupationOther' => ['nullable', 'string', 'max:100'],
            'careerField' => ['nullable', 'string', 'max:100'],
            'careerFieldOther' => ['nullable', 'string', 'max:100'],
            'companyName' => ['nullable', 'string', 'max:255'],
            'businessType' => ['required', 'string', 'max:255'],
            'businessTypeOther' => ['nullable', 'string', 'max:255'],
            'workAddressBuilding' => ['nullable', 'string', 'max:255'],
            'workAddressFloor' => ['nullable', 'string', 'max:255'],
            'workDepartment' => ['nullable', 'string', 'max:255'],
            'workAddressNo' => ['nullable', 'string', 'max:255'],
            'workAddressVillage' => ['nullable', 'string', 'max:255'],
            'workAddressSoi' => ['nullable', 'string', 'max:255'],
            'workAddressRoad' => ['nullable', 'string', 'max:255'],
            'workAddressSubdistrict' => ['nullable', 'string', 'max:255'],
            'workAddressDistrict' => ['nullable', 'string', 'max:255'],
            'workAddressProvince' => ['nullable', 'string', 'max:255'],
            'workAddressPostal' => ['nullable', 'string', 'max:255'],
            'workPhone' => ['nullable', 'string', 'max:255'],
            'workYears' => ['nullable', 'integer', 'min:0'],
            'workMonths' => ['nullable', 'integer', 'min:0', 'max:11'],

            // 6. ที่ทำงานเดิม
            'previousCompanyName' => ['nullable', 'string', 'max:255'],
            'previousPosition' => ['nullable', 'string', 'max:255'],
            'previousIncome' => ['nullable', 'numeric'],
            'previousWorkAddress' => ['nullable', 'string'],
            'previousPhone' => ['nullable', 'string', 'max:255'],

            // 7. รายได้
            'income' => ['required', 'numeric', 'min:0'],
            'extraIncome' => ['nullable', 'numeric', 'min:0'],
            'extraIncomeSource' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value === 'อื่นๆ' && !$request->filled('extraIncomeSourceOther')) {
                        $fail('กรุณาระบุแหล่งที่มาของรายได้');
                    }
                },
            ],
            'extraIncomeSourceOther' => ['nullable', 'string', 'max:255'],
            'incomeCountry' => ['nullable', 'string', 'max:100'],
            'incomeDocuments' => ['nullable', 'array'],
            'incomeDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
            'hasOtherDebts' => ['required', 'string', 'max:10'],
            'otherDebtInstallment' => ['nullable', 'required_if:hasOtherDebts,มี', 'numeric', 'min:0'],
            'hasExistingLoan' => ['nullable', 'string', 'in:ใช่,ไม่ใช่'],
            'existingLoanInstitutionCount' => ['nullable', 'required_if:hasExistingLoan,ใช่', 'integer', 'min:1'],
            'existingLoanTotalAmount' => ['nullable', 'required_if:hasExistingLoan,ใช่', 'numeric', 'min:0'],

            // 8. ข้อมูลบุคคลอ้างอิง
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

            // 9. ความประสงค์ในการสมัครใช้สินเชื่อ
            'loanPurpose' => ['nullable', 'string', 'max:255'],
            'loanTerm' => ['nullable', 'integer', 'in:4,6,12,18,24,36,48,60'],
            'loanAmountType' => ['nullable', 'string', 'in:full,custom'],
            'customLoanAmount' => ['nullable', 'numeric', 'min:0'],

            // 10. ความประสงค์ขอรับวงเงินกู้ครั้งแรกเข้าบัญชีเงินฝาก
            'accountNumber' => ['nullable', 'string', 'max:255'],
            'accountType' => ['nullable', 'string', 'max:255'],
            'bankName' => ['nullable', 'string', 'max:255'],
            'accountName' => ['nullable', 'string', 'max:255'],

            // 11. วิธีการชําระเงิน
            'paymentMethod' => ['nullable', 'string', 'max:255'],
            'directDebitAmount' => ['nullable', 'required_if:paymentMethod,ชําระโดยการหักบัญชี', 'numeric', 'min:0'],
            'directDebitAccountNumber' => ['nullable', 'required_if:paymentMethod,ชําระโดยการหักบัญชี', 'string', 'max:50'],

            // 12. ลายเซ็น
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

        if (($validated['id_type'] ?? 'id_card') === 'passport') {
            $validated['passport'] = strtoupper(trim((string) ($validated['id_card'] ?? '')));
            $validated['id_card'] = null;
        } else {
            $validated['id_card'] = preg_replace('/\D+/', '', (string) ($validated['id_card'] ?? ''));
            $validated['passport'] = null;
        }

        if (($validated['title'] ?? null) === 'อื่นๆ' && $request->filled('title_other')) {
            $validated['title'] = $request->title_other;
        }

        if (($validated['occupation'] ?? null) === 'อื่นๆ' && $request->filled('occupationOther')) {
            $validated['occupation'] = $request->occupationOther;
        }

        if (($validated['careerField'] ?? null) === 'อื่นๆ' && $request->filled('careerFieldOther')) {
            $validated['careerField'] = $request->careerFieldOther;
        }

        if (($validated['businessType'] ?? null) === 'อื่นๆ' && $request->filled('businessTypeOther')) {
            $validated['businessType'] = $request->businessTypeOther;
        }

        if (($validated['extraIncomeSource'] ?? null) === 'อื่นๆ' && $request->filled('extraIncomeSourceOther')) {
            $validated['extraIncomeSource'] = $request->extraIncomeSourceOther;
        } elseif (trim((string) ($validated['extraIncomeSource'] ?? '')) === '') {
            $validated['extraIncomeSource'] = null;
        }

        if (($validated['hasOtherDebts'] ?? null) === 'ไม่มี') {
            $validated['otherDebtInstallment'] = 0;
        }

        $status = 'approved';
        $age = 0;
        if (!empty($validated['birthdate'])) {
            $age = Carbon::parse($validated['birthdate'])->age;
        }

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

        $hasExistingLoan = ($validated['hasExistingLoan'] ?? null) === 'ใช่'
            ? true
            : (($validated['hasExistingLoan'] ?? null) === 'ไม่ใช่' ? false : null);

        if (!$hasExistingLoan) {
            $validated['existingLoanInstitutionCount'] = null;
            $validated['existingLoanTotalAmount'] = null;
        }

        $applicationPayload = [
            'app_date' => $validated['app_date'] ?? null,
            'app_no' => $validated['app_no'] ?? $consent?->app_no,
            'officer_name' => $validated['officer_name'] ?? null,
            'officer_phone' => $validated['officer_phone'] ?? null,
            'document_delivery' => $validated['documentDelivery'] ?? null,
            'signed' => true,
            'signed_at' => $consent?->signed_at ?? now(),
            'signature_data' => $request->input('signatureData'),
            'status' => $status,
        ];

        return DB::transaction(function () use ($request, $consent, $applicationPayload, $validated, $hasOtherDebts, $hasExistingLoan) {
            $application = $consent
                ? tap($consent)->update($applicationPayload)
                : ConsentApplication::create($applicationPayload);

            ConsentApplicant::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'title' => $validated['title'] ?? null,
                    'name' => $validated['name'],
                    'name_en' => $validated['name_en'] ?? null,
                    'birthdate' => $validated['birthdate'] ?? null,
                    'nationality' => $validated['nationality'] ?? null,
                    'id_card' => $validated['id_card'] ?? null,
                    'passport' => $validated['passport'] ?? null,
                    'education' => $validated['education'] ?? null,
                    'marital_status' => $validated['marital_status'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                    'government_level' => $validated['governmentLevel'] ?? null,
                    'occupation_other' => $validated['occupationOther'] ?? null,
                    'career_field' => $validated['careerField'] ?? null,
                    'career_field_other' => $validated['careerFieldOther'] ?? null,
                    'income' => $validated['income'] ?? null,
                    'extra_income' => $validated['extraIncome'] ?? null,
                    'extra_income_source' => $validated['extraIncomeSource'] ?? null,
                    'income_country' => $validated['incomeCountry'] ?? null,
                    'has_other_debts' => $hasOtherDebts,
                    'other_debt_installment' => $validated['otherDebtInstallment'] ?? null,
                    'has_existing_loan' => $hasExistingLoan,
                    'existing_loan_institution_count' => $validated['existingLoanInstitutionCount'] ?? null,
                    'existing_loan_total_amount' => $validated['existingLoanTotalAmount'] ?? null,
                ]
            );

            if ($request->hasFile('incomeDocuments')) {
                foreach ((array) $request->file('incomeDocuments', []) as $file) {
                    if (!$file) {
                        continue;
                    }

                    $disk = 'local';
                    $path = $file->store('consent/' . $application->id . '/income-documents', $disk);

                    ConsentIncomeDocument::create([
                        'application_id' => $application->id,
                        'disk' => $disk,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            ConsentAddress::updateOrCreate(
                ['application_id' => $application->id, 'kind' => 'home'],
                [
                    'residence_status' => $validated['residence_status'] ?? null,
                    'address_building' => $validated['address_building'] ?? null,
                    'address_room' => $validated['address_room'] ?? null,
                    'address_floor' => $validated['address_floor'] ?? null,
                    'address_no' => $validated['address_no'] ?? null,
                    'address_village' => $validated['address_village'] ?? null,
                    'address_soi' => $validated['address_soi'] ?? null,
                    'address_road' => $validated['address_road'] ?? null,
                    'address_subdistrict' => $validated['address_subdistrict'] ?? null,
                    'address_district' => $validated['address_district'] ?? null,
                    'address_province' => $validated['address_province'] ?? null,
                    'address_postal' => $validated['address_postal'] ?? null,
                ]
            );

            ConsentContact::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'phone_home' => $validated['phone_home'] ?? null,
                    'phone_mobile' => $validated['phone_mobile'] ?? null,
                    'email' => $validated['email'] ?? null,
                ]
            );

            ConsentAddress::updateOrCreate(
                ['application_id' => $application->id, 'kind' => 'document'],
                [
                    'address_text' => $validated['documentAddressText'] ?? null,
                    'address_province' => $validated['documentAddressProvince'] ?? null,
                    'address_postal' => $validated['documentAddressPostal'] ?? null,
                    'birth_place_address' => $validated['birthPlaceAddress'] ?? null,
                ]
            );

            ConsentEmployment::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'use_home_address' => (bool) ($validated['useHomeAddress'] ?? false),
                    'company_name' => $validated['companyName'] ?? null,
                    'business_type' => $validated['businessType'] ?? null,
                    'work_department' => $validated['workDepartment'] ?? null,
                    'work_phone' => $validated['workPhone'] ?? null,
                    'work_years' => $validated['workYears'] ?? null,
                    'work_months' => $validated['workMonths'] ?? null,
                ]
            );

            ConsentAddress::updateOrCreate(
                ['application_id' => $application->id, 'kind' => 'work'],
                [
                    'address_building' => $validated['workAddressBuilding'] ?? null,
                    'address_floor' => $validated['workAddressFloor'] ?? null,
                    'address_no' => $validated['workAddressNo'] ?? null,
                    'address_village' => $validated['workAddressVillage'] ?? null,
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
                    'previous_position' => $validated['previousPosition'] ?? null,
                    'previous_income' => $validated['previousIncome'] ?? null,
                    'previous_address' => $validated['previousWorkAddress'] ?? null,
                    'previous_phone' => $validated['previousPhone'] ?? null,
                ]
            );

            ConsentReference::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'ref_name' => $validated['refName'] ?? null,
                    'ref_relation' => $validated['refRelation'] ?? null,
                    'ref_phone_home' => $validated['refPhoneHome'] ?? null,
                    'ref_phone_mobile' => $validated['refPhoneMobile'] ?? null,
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
                    'loan_purpose' => $validated['loanPurpose'] ?? null,
                    'loan_term' => $validated['loanTerm'] ?? null,
                    'loan_amount_type' => $validated['loanAmountType'] ?? null,
                    'custom_loan_amount' => $validated['customLoanAmount'] ?? null,
                ]
            );

            ConsentDisbursementAccount::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'account_number' => $validated['accountNumber'] ?? null,
                    'account_type' => $validated['accountType'] ?? null,
                    'bank_name' => $validated['bankName'] ?? null,
                    'account_name' => $validated['accountName'] ?? null,
                    'payment_method' => $validated['paymentMethod'] ?? null,
                    'direct_debit_amount' => $validated['directDebitAmount'] ?? null,
                    'direct_debit_account_number' => $validated['directDebitAccountNumber'] ?? null,
                ]
            );

            return $application->fresh()->load('applicant');
        });
    }

    private function toFrontendData(ConsentApplication $consent): array
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

        $hasOtherDebtsValue = $applicant?->has_other_debts;
        $hasOtherDebts = $hasOtherDebtsValue === null ? null : ($hasOtherDebtsValue ? 'มี' : 'ไม่มี');

        $hasExistingLoanValue = $applicant?->has_existing_loan;
        $hasExistingLoan = $hasExistingLoanValue === null ? null : ($hasExistingLoanValue ? 'ใช่' : 'ไม่ใช่');

        $incomeDocuments = $consent
            ->incomeDocuments()
            ->orderBy('id')
            ->get()
            ->map(function (ConsentIncomeDocument $document) use ($consent) {
                return [
                    'id' => $document->id,
                    'originalName' => $document->original_name,
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

        return array_merge($consent->toArray(), [
            'id' => $consent->encrypted_id,
            'transaction_date' => $consent->created_at?->format('d/m/Y'),
            'signed_date' => $consent->signed_at?->format('Y-m-d'),

            // 1. ข้อมูลใบคำขอ
            'app_date' => $consent->app_date?->format('Y-m-d'),
            'app_no' => $consent->app_no,

            // 2. สำหรับเจ้าหน้าที่บริษัท
            'officer_name' => $consent->officer_name,
            'officer_phone' => $consent->officer_phone,

            // 3. ข้อมูลส่วนตัวผู้ขอสินเชื่อ
            'title' => $applicant?->title,
            'name' => $applicant?->name,
            'name_en' => $applicant?->name_en,
            'birthdate' => $applicant?->birthdate?->format('Y-m-d'),
            'nationality' => $applicant?->nationality,
            'id_type' => $applicant?->id_card ? 'id_card' : ($applicant?->passport ? 'passport' : 'id_card'),
            'id_card' => $applicant?->id_card ?: $applicant?->passport,
            'education' => $applicant?->education,
            'marital_status' => $applicant?->marital_status,

            // 4. ที่อยู่ปัจจุบัน
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

            // 5. ข้อมูลอาชีพ/สถานที่ทำงาน
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

            // 6. ที่ทำงานเดิม
            'previousCompanyName' => $previousEmployment?->previous_company_name,
            'previousPosition' => $previousEmployment?->previous_position,
            'previousIncome' => $previousEmployment?->previous_income,
            'previousWorkAddress' => $previousEmployment?->previous_address,
            'previousPhone' => $previousEmployment?->previous_phone,

            // 7. รายได้
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

            // 8. ข้อมูลบุคคลอ้างอิง
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

            // 9. ความประสงค์ในการสมัครใช้สินเชื่อ
            'loanPurpose' => $loan?->loan_purpose,
            'loanTerm' => $loan?->loan_term,
            'loanAmountType' => $loan?->loan_amount_type,
            'customLoanAmount' => $loan?->custom_loan_amount,

            // 10. ความประสงค์ขอรับวงเงินกู้ครั้งแรกเข้าบัญชีเงินฝาก
            'accountNumber' => $account?->account_number,
            'accountType' => $account?->account_type,
            'bankName' => $account?->bank_name,
            'accountName' => $account?->account_name,

            // 11. วิธีการชําระเงิน
            'paymentMethod' => $account?->payment_method,
            'directDebitAmount' => $account?->direct_debit_amount,
            'directDebitAccountNumber' => $account?->direct_debit_account_number,

            // 12. ลายเซ็น
            'signature_data' => $consent->signature_data,
            'signatureData' => $consent->signature_data,
        ]);
    }
}
