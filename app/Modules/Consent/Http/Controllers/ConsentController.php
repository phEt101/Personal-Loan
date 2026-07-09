<?php

namespace App\Modules\Consent\Http\Controllers;

use App\Modules\Consent\Models\ConsentAddress;
use App\Modules\Consent\Models\ConsentApplicant;
use App\Modules\Consent\Models\ConsentApplication;
use App\Modules\Consent\Models\ConsentContact;
use App\Modules\Consent\Models\ConsentDisbursementAccount;
use App\Modules\Consent\Models\ConsentEmployment;
use App\Modules\Consent\Models\ConsentDocumentFile;
use App\Modules\Consent\Models\ConsentLoanRequest;
use App\Modules\Consent\Models\ConsentPreviousEmployment;
use App\Modules\Consent\Models\ConsentReference;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ConsentController extends Controller
{
    public function modalConsentForm()
    {
        $nextAppNo = $this->getNextAppNo();

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

    public function index(Request $request)
    {
        $query = ConsentApplication::query()
            ->with(['applicant:id,application_id,name']);

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
        $approved = ConsentApplication::where('status', 'approved')->count();
        $rejected = ConsentApplication::where('status', 'rejected')->count();

        $nextAppNo = $this->getNextAppNo();

        return view('consent::index', compact('customers', 'total', 'approved', 'rejected', 'nextAppNo'));
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

    public function downloadIncomeDocument(ConsentApplication $consent, ConsentDocumentFile $document)
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

    public function destroyIncomeDocument(ConsentApplication $consent, ConsentDocumentFile $document)
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

    public function saveStep(Request $request)
    {
        $step = (int) $request->input('step', 1);
        $consentId = $request->input('consent_id');
        $consent = null;

        Log::info("Consent saveStep: Start step {$step}", [
            'consent_id' => $consentId,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload_keys' => array_keys($request->except(['_token', 'signatureData'])),
        ]);

        if ($consentId) {
            $consent = ConsentApplication::where('encrypted_id', $consentId)->first();
            if (!$consent) {
                Log::error("Consent saveStep: Consent not found", ['consent_id' => $consentId]);
                return response()->json(['ok' => false, 'message' => 'ไม่พบข้อมูลใบคำขอ'], 404);
            }
        }

        try {
            $rules = $this->getStepRules($step, $request);
            $messages = $this->getValidationMessages();
            $attributes = $this->getValidationAttributes();
            
            $validated = $request->validate($rules, $messages, $attributes);

            return DB::transaction(function () use ($request, $step, $consent, $validated) {
                if (!$consent && $step === 1) {
                    $consent = ConsentApplication::create([
                        'app_date' => $validated['app_date'] ?? now(),
                        'app_no' => $this->getNextAppNo(true),
                        'officer_name' => $validated['officer_name'] ?? null,
                        'officer_phone' => $validated['officer_phone'] ?? null,
                        'status' => 'draft',
                    ]);
                    Log::info("Consent saveStep: Created new root draft record", [
                        'id' => $consent->id,
                        'app_no' => $consent->app_no,
                        'encrypted_id' => $consent->encrypted_id
                    ]);
                }

                if ($consent) {
                    $this->updateConsentByStep($consent, $step, $validated, $request);
                    Log::info("Consent saveStep: Step {$step} saved successfully", [
                        'id' => $consent->id,
                        'step' => $step,
                        'app_no' => $consent->app_no
                    ]);
                } else {
                    Log::warning("Consent saveStep: No consent record found or created for step {$step}");
                    throw new \Exception("ไม่พบข้อมูลใบคำขอหลัก");
                }

                return response()->json([
                    'ok' => true,
                    'consent_id' => $consent?->encrypted_id,
                    'app_no' => $consent?->app_no,
                    'step' => $step,
                    'status' => $consent?->status,
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Consent saveStep: Validation failed for step {$step}", [
                'consent_id' => $consentId,
                'errors' => $e->errors(),
                'input' => $request->except([
                    '_token',
                    'signatureData',
                    'incomeSalarySlipDocuments',
                    'incomeSalaryCertificateDocuments',
                    'incomeSalary50TawiDocuments',
                    'incomeSalaryStatement6mDocuments',
                    'incomeSupplementarySlipDocuments',
                    'incomeSupplementaryStatement6mDocuments',
                    'incomeRegisteredCertDocuments',
                    'incomeRegisteredShareholderDocuments',
                    'incomeRegisteredTradeDocuments',
                    'incomeRegisteredStatement1yDocuments',
                    'incomeUnregisteredLeaseDocuments',
                    'incomeUnregisteredTaxDocuments',
                    'incomeUnregisteredStatement1yDocuments',
                    'incomeUnregisteredInvoiceDocuments',
                    'incomeUnregisteredBusinessPhotoDocuments',
                    'incomeSelfIndividualTax50Documents',
                    'incomeSelfIndividualPndDocuments',
                    'incomeSelfIndividualStatement1yDocuments',
                    'incomeSelfBusinessTaxDocuments',
                    'incomeSelfBusinessStatement1yDocuments',
                    'incomeSelfBusinessInvoiceDocuments',
                    'incomeSelfBusinessPhotoDocuments',
                    'identityIdCardDocuments',
                    'identityPassportDocuments',
                    'identityHouseRegistrationDocuments',
                    'identityWorkPermitDocuments',
                    'identityNameChangeDocuments',
                ]),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error("Consent saveStep: Exception in step {$step}", [
                'consent_id' => $consentId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getValidationMessages(): array
    {
        return [
            'required' => 'กรุณากรอกข้อมูล :attribute',
            'required_if' => 'กรุณากรอกข้อมูล :attribute',
            'in' => ':attribute ไม่ถูกต้อง',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
            'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
            'date' => ':attribute รูปแบบวันที่ไม่ถูกต้อง',
            'email' => ':attribute รูปแบบอีเมลไม่ถูกต้อง',
            'max' => ':attribute ต้องไม่เกิน :max ตัวอักษร',
            'min' => ':attribute ต้องไม่น้อยกว่า :min',
            'mimes' => ':attribute ต้องเป็นไฟล์ประเภท :values เท่านั้น',
            'uploaded' => ':attribute อัปโหลดไม่สำเร็จ กรุณาลองใหม่ หรือเลือกไฟล์ขนาดเล็กลง',
            'regex' => ':attribute รูปแบบไม่ถูกต้อง',
        ];
    }

    private function getValidationAttributes(): array
    {
        return [
            'app_date' => 'วันที่เขียนคำขอ',
            'app_no' => 'เลขที่ใบคำขอ',
            'officer_name' => 'เจ้าหน้าที่สินเชื่อ',
            'officer_phone' => 'เบอร์ติดต่อเจ้าหน้าที่',
            'title' => 'คำนำหน้านาม',
            'title_other' => 'คำนำหน้านามอื่นๆ',
            'name' => 'ชื่อ - สกุล',
            'name_en' => 'ชื่อ - สกุล (ภาษาอังกฤษ)',
            'birthdate' => 'วัน / เดือน / ปีเกิด',
            'nationality' => 'สัญชาติ',
            'id_type' => 'เอกสารระบุตัวตน',
            'id_card' => 'เลขบัตรประจำตัวประชาชน/พาสปอร์ต',
            'education' => 'การศึกษา',
            'marital_status' => 'สถานภาพสมรส',
            'residence_status' => 'สถานะของการอยู่อาศัย',
            'phone_mobile' => 'หมายเลขโทรศัพท์มือถือ',
            'documentDelivery' => 'ช่องทางการรับเอกสาร',
            'occupation' => 'อาชีพ',
            'governmentLevel' => 'ระดับข้าราชการ',
            'occupationOther' => 'อาชีพอื่นๆ',
            'careerField' => 'สาขาอาชีพ',
            'careerFieldOther' => 'สาขาอาชีพอื่นๆ',
            'businessType' => 'ประเภทธุรกิจ',
            'businessTypeOther' => 'ประเภทธุรกิจอื่นๆ',
            'income' => 'รายได้หลัก',
            'extraIncome' => 'รายได้เสริม',
            'extraIncomeSource' => 'แหล่งที่มาของรายได้',
            'hasOtherDebts' => 'ภาระหนี้สินอื่น',
            'otherDebtInstallment' => 'ค่างวดหนี้สินอื่น',
            'hasExistingLoan' => 'การชี้แจงการมีสินเชื่อบุคคล',
            'existingLoanInstitutionCount' => 'จำนวนสถาบันการเงิน',
            'existingLoanTotalAmount' => 'วงเงินสินเชื่อรวม',
            'refName' => 'ชื่อบุคคลอ้างอิง',
            'refRelation' => 'ความสัมพันธ์',
            'loanPurpose' => 'วัตถุประสงค์ในการขอกู้',
            'loanTerm' => 'ระยะเวลาผ่อนชำระ',
            'loanAmountType' => 'ประเภทวงเงินที่ขอ',
            'customLoanAmount' => 'ระบุวงเงินที่ขอ',
            'accountNumber' => 'เลขที่บัญชี',
            'accountType' => 'ประเภทบัญชี',
            'bankName' => 'ธนาคาร',
            'accountName' => 'ชื่อบัญชี',
            'paymentMethod' => 'วิธีการชำระเงิน',
            'signatureData' => 'ลายเซ็น',
        ];
    }

    private function getStepRules(int $step, Request $request): array
    {
        return match ($step) {
            1 => [
                // ข้อมูลใบคำขอ + ข้อมูลส่วนตัว
                'app_date' => ['nullable', 'date'],
                'app_no' => ['nullable', 'string', 'max:13'],
                'officer_name' => ['nullable', 'string', 'max:255'],
                'officer_phone' => ['nullable', 'string', 'max:20'],
                'title' => ['required', 'string', 'max:50'],
                'title_other' => ['nullable', 'required_if:title,อื่นๆ', 'string', 'max:50'],
                'name' => ['nullable', 'string', 'max:255'],
                'name_en' => ['nullable', 'string', 'max:255'],
                'birthdate' => ['required', 'date'],
                'nationality' => ['required', 'string', 'max:50'],
                'id_type' => ['required', 'in:id_card,passport'],
                'id_card' => [
                    'required', 'string', 'max:20',
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
                'education' => ['required', 'string', 'max:50'],
                'marital_status' => ['required', 'string', 'max:50'],
            ],
            2 => [
                // ที่อยู่ปัจจุบัน
                'residence_status' => ['required', 'string', 'max:255'],
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
                'phone_mobile' => ['nullable', 'string', 'max:20', 'regex:/^\d{9,10}$/'],
                'email' => ['nullable', 'email', 'max:255'],
                'documentDelivery' => ['required', 'string', 'max:255'],
                'documentAddressText' => ['nullable', 'string'],
                'documentAddressProvince' => ['nullable', 'string', 'max:255'],
                'documentAddressPostal' => ['nullable', 'string', 'max:255'],
                'birthPlaceAddress' => ['nullable', 'string'],
            ],
            3 => [
                // ข้อมูลอาชีพ/สถานที่ทำงาน
                'useHomeAddress' => ['nullable', 'boolean'],
                'occupation' => ['required', 'string', 'max:100'],
                'governmentLevel' => ['nullable', 'required_if:occupation,ข้าราชการ', 'string', 'max:100'],
                'occupationOther' => ['nullable', 'required_if:occupation,อื่นๆ', 'string', 'max:100'],
                'careerField' => ['required', 'string', 'max:100'],
                'careerFieldOther' => ['nullable', 'required_if:careerField,อื่นๆ', 'string', 'max:100'],
                'companyName' => ['nullable', 'string', 'max:255'],
                'businessType' => ['required', 'string', 'max:255'],
                'businessTypeOther' => ['nullable', 'required_if:businessType,อื่นๆ', 'string', 'max:255'],
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
                'previousCompanyName' => ['nullable', 'string', 'max:255'],
                'previousPosition' => ['nullable', 'string', 'max:255'],
                'previousIncome' => ['nullable', 'numeric'],
                'previousWorkAddress' => ['nullable', 'string'],
                'previousPhone' => ['nullable', 'string', 'max:255'],
            ],
            4 => [
                // รายได้
                'income' => ['required', 'numeric', 'min:0'],
                'extraIncome' => ['nullable', 'numeric', 'min:0'],
                'extraIncomeSource' => ['required', 'string', 'max:255'],
                'extraIncomeSourceOther' => ['nullable', 'required_if:extraIncomeSource,อื่นๆ', 'string', 'max:255'],
                'incomeCountry' => ['nullable', 'string', 'max:100'],
                'hasOtherDebts' => ['required', 'string', 'max:10'],
                'otherDebtInstallment' => ['nullable', 'required_if:hasOtherDebts,มี', 'numeric', 'min:0'],
                'hasExistingLoan' => ['required', 'string', 'in:ใช่,ไม่ใช่'],
                'existingLoanInstitutionCount' => ['nullable', 'required_if:hasExistingLoan,ใช่', 'integer', 'min:1'],
                'existingLoanTotalAmount' => ['nullable', 'required_if:hasExistingLoan,ใช่', 'numeric', 'min:0'],
            ],
            5 => [
                // บุคคลอ้างอิง
                'refName' => ['required', 'string', 'max:255'],
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
            ],
            6 => [
                // ความประสงค์กู้/การชำระเงิน
                'loanPurpose' => ['required', 'string', 'max:255'],
                'loanTerm' => ['required', 'integer', 'in:4,6,12,18,24,36,48,60'],
                'loanAmountType' => ['required', 'string', 'in:full,custom'],
                'customLoanAmount' => ['nullable', 'required_if:loanAmountType,custom', 'numeric', 'min:0'],
                'accountNumber' => ['required', 'string', 'max:255'],
                'accountType' => ['required', 'string', 'max:255'],
                'bankName' => ['required', 'string', 'max:255'],
                'accountName' => ['required', 'string', 'max:255'],
                'paymentMethod' => ['required', 'string', 'max:255'],
                'directDebitAmount' => ['nullable', 'required_if:paymentMethod,ชําระโดยการหักบัญชี', 'numeric', 'min:0'],
                'directDebitAccountNumber' => ['nullable', 'required_if:paymentMethod,ชําระโดยการหักบัญชี', 'string', 'max:50'],
            ],
            7 => [
                // ลายเซ็น
                'signatureData' => [
                    'required', 'string',
                    function ($attribute, $value, $fail) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded) || count($decoded) === 0) {
                            $fail('กรุณาเซ็นลายเซ็นผู้ขอสินเชื่อก่อนบันทึก');
                        }
                    },
                ],
            ],
            8 => [
                // แนบไฟล์หลักฐานการเงินและเอกสารแสดงตัวตน
                'incomeSalarySlipDocuments' => ['nullable', 'array'],
                'incomeSalarySlipDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSalaryCertificateDocuments' => ['nullable', 'array'],
                'incomeSalaryCertificateDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSalary50TawiDocuments' => ['nullable', 'array'],
                'incomeSalary50TawiDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSalaryStatement6mDocuments' => ['nullable', 'array'],
                'incomeSalaryStatement6mDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSupplementarySlipDocuments' => ['nullable', 'array'],
                'incomeSupplementarySlipDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSupplementaryStatement6mDocuments' => ['nullable', 'array'],
                'incomeSupplementaryStatement6mDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeRegisteredCertDocuments' => ['nullable', 'array'],
                'incomeRegisteredCertDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeRegisteredShareholderDocuments' => ['nullable', 'array'],
                'incomeRegisteredShareholderDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeRegisteredTradeDocuments' => ['nullable', 'array'],
                'incomeRegisteredTradeDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeRegisteredStatement1yDocuments' => ['nullable', 'array'],
                'incomeRegisteredStatement1yDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeUnregisteredLeaseDocuments' => ['nullable', 'array'],
                'incomeUnregisteredLeaseDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeUnregisteredTaxDocuments' => ['nullable', 'array'],
                'incomeUnregisteredTaxDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeUnregisteredStatement1yDocuments' => ['nullable', 'array'],
                'incomeUnregisteredStatement1yDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeUnregisteredInvoiceDocuments' => ['nullable', 'array'],
                'incomeUnregisteredInvoiceDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeUnregisteredBusinessPhotoDocuments' => ['nullable', 'array'],
                'incomeUnregisteredBusinessPhotoDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSelfIndividualTax50Documents' => ['nullable', 'array'],
                'incomeSelfIndividualTax50Documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSelfIndividualPndDocuments' => ['nullable', 'array'],
                'incomeSelfIndividualPndDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSelfIndividualStatement1yDocuments' => ['nullable', 'array'],
                'incomeSelfIndividualStatement1yDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSelfBusinessTaxDocuments' => ['nullable', 'array'],
                'incomeSelfBusinessTaxDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSelfBusinessStatement1yDocuments' => ['nullable', 'array'],
                'incomeSelfBusinessStatement1yDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSelfBusinessInvoiceDocuments' => ['nullable', 'array'],
                'incomeSelfBusinessInvoiceDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'incomeSelfBusinessPhotoDocuments' => ['nullable', 'array'],
                'incomeSelfBusinessPhotoDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'identityIdCardDocuments' => ['nullable', 'array'],
                'identityIdCardDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'identityPassportDocuments' => ['nullable', 'array'],
                'identityPassportDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'identityHouseRegistrationDocuments' => ['nullable', 'array'],
                'identityHouseRegistrationDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'identityWorkPermitDocuments' => ['nullable', 'array'],
                'identityWorkPermitDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'identityNameChangeDocuments' => ['nullable', 'array'],
                'identityNameChangeDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
            ],
            default => [],
        };
    }

    private function updateConsentByStep(ConsentApplication $consent, int $step, array $validated, Request $request): void
    {
        Log::debug("Consent updateConsentByStep: Processing step {$step}", ['id' => $consent->id]);

        switch ($step) {
            case 1:
                $consent->update([
                    'app_date' => $validated['app_date'] ?? $consent->app_date,
                    'app_no' => $validated['app_no'] ?? $consent->app_no,
                    'officer_name' => $validated['officer_name'] ?? $consent->officer_name,
                    'officer_phone' => $validated['officer_phone'] ?? $consent->officer_phone,
                ]);

                $title = $validated['title'] ?? null;
                if ($title === 'อื่นๆ' && $request->filled('title_other')) {
                    $title = $request->title_other;
                }
                
                $applicant = ConsentApplicant::updateOrCreate(
                    ['application_id' => $consent->id],
                    [
                        'title' => $title,
                        'name' => $validated['name'],
                        'name_en' => $validated['name_en'] ?? null,
                        'birthdate' => $validated['birthdate'] ?? null,
                        'nationality' => $validated['nationality'] ?? null,
                        'id_card' => $validated['id_type'] === 'id_card' ? preg_replace('/\D+/', '', $validated['id_card']) : null,
                        'passport' => $validated['id_type'] === 'passport' ? strtoupper(trim($validated['id_card'])) : null,
                        'education' => $validated['education'] ?? null,
                        'marital_status' => $validated['marital_status'] ?? null,
                    ]
                );
                Log::debug("Consent updateConsentByStep: Step 1 models updated", ['applicant_id' => $applicant->id]);
                break;

            case 2:
                $homeAddr = ConsentAddress::updateOrCreate(
                    ['application_id' => $consent->id, 'kind' => 'home'],
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
                
                $contact = ConsentContact::updateOrCreate(
                    ['application_id' => $consent->id],
                    [
                        'phone_home' => $validated['phone_home'] ?? null,
                        'phone_mobile' => $validated['phone_mobile'] ?? null,
                        'email' => $validated['email'] ?? null,
                    ]
                );
                
                $consent->update(['document_delivery' => $validated['documentDelivery'] ?? null]);
                
                $docAddr = ConsentAddress::updateOrCreate(
                    ['application_id' => $consent->id, 'kind' => 'document'],
                    [
                        'address_text' => $validated['documentAddressText'] ?? null,
                        'address_province' => $validated['documentAddressProvince'] ?? null,
                        'address_postal' => $validated['documentAddressPostal'] ?? null,
                        'birth_place_address' => $validated['birthPlaceAddress'] ?? null,
                    ]
                );
                Log::debug("Consent updateConsentByStep: Step 2 models updated", [
                    'home_addr_id' => $homeAddr->id,
                    'contact_id' => $contact->id,
                    'doc_addr_id' => $docAddr->id
                ]);
                break;

            case 3:
                $occupation = $validated['occupation'] ?? null;
                if ($occupation === 'อื่นๆ' && $request->filled('occupationOther')) {
                    $occupation = $request->occupationOther;
                }
                $careerField = $validated['careerField'] ?? null;
                if ($careerField === 'อื่นๆ' && $request->filled('careerFieldOther')) {
                    $careerField = $request->careerFieldOther;
                }
                $businessType = $validated['businessType'] ?? null;
                if ($businessType === 'อื่นๆ' && $request->filled('businessTypeOther')) {
                    $businessType = $request->businessTypeOther;
                }

                $consent->applicant()->update([
                    'occupation' => $occupation,
                    'government_level' => $validated['governmentLevel'] ?? null,
                    'career_field' => $careerField,
                ]);

                $employment = ConsentEmployment::updateOrCreate(
                    ['application_id' => $consent->id],
                    [
                        'use_home_address' => (bool) ($validated['useHomeAddress'] ?? false),
                        'company_name' => $validated['companyName'] ?? null,
                        'business_type' => $businessType,
                        'work_department' => $validated['workDepartment'] ?? null,
                        'work_phone' => $validated['workPhone'] ?? null,
                        'work_years' => $validated['workYears'] ?? null,
                        'work_months' => $validated['workMonths'] ?? null,
                    ]
                );
                
                $workAddr = ConsentAddress::updateOrCreate(
                    ['application_id' => $consent->id, 'kind' => 'work'],
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
                
                $prevEmployment = ConsentPreviousEmployment::updateOrCreate(
                    ['application_id' => $consent->id],
                    [
                        'previous_company_name' => $validated['previousCompanyName'] ?? null,
                        'previous_position' => $validated['previousPosition'] ?? null,
                        'previous_income' => $validated['previousIncome'] ?? null,
                        'previous_address' => $validated['previousWorkAddress'] ?? null,
                        'previous_phone' => $validated['previousPhone'] ?? null,
                    ]
                );
                Log::debug("Consent updateConsentByStep: Step 3 models updated", [
                    'employment_id' => $employment->id,
                    'work_addr_id' => $workAddr->id,
                    'prev_employment_id' => $prevEmployment->id
                ]);
                break;

            case 4:
                $extraIncomeSource = $validated['extraIncomeSource'] ?? null;
                if ($extraIncomeSource === 'อื่นๆ' && $request->filled('extraIncomeSourceOther')) {
                    $extraIncomeSource = $request->extraIncomeSourceOther;
                }
                
                // Convert to boolean for database consistency
                $hasOtherDebts = $validated['hasOtherDebts'] === 'มี';
                $hasExistingLoan = $validated['hasExistingLoan'] === 'ใช่';

                $consent->applicant()->update([
                    'income' => $validated['income'],
                    'extra_income' => $validated['extraIncome'] ?? null,
                    'extra_income_source' => $extraIncomeSource,
                    'income_country' => $validated['incomeCountry'] ?? null,
                    'has_other_debts' => $hasOtherDebts,
                    'other_debt_installment' => $hasOtherDebts ? $validated['otherDebtInstallment'] : 0,
                    'has_existing_loan' => $hasExistingLoan,
                    'existing_loan_institution_count' => $hasExistingLoan ? $validated['existingLoanInstitutionCount'] : 0,
                    'existing_loan_total_amount' => $hasExistingLoan ? $validated['existingLoanTotalAmount'] : 0,
                ]);

                Log::debug("Consent updateConsentByStep: Step 4 applicant data updated", [
                    'income' => $validated['income'],
                    'has_existing_loan' => $hasExistingLoan
                ]);
                break;

            case 5:
                $ref = ConsentReference::updateOrCreate(
                    ['application_id' => $consent->id],
                    [
                        'ref_name' => $validated['refName'] ?? null,
                        'ref_relation' => $validated['refRelation'] ?? null,
                        'ref_phone_home' => $validated['refPhoneHome'] ?? null,
                        'ref_phone_mobile' => $validated['refPhoneMobile'] ?? null,
                    ]
                );
                
                $refAddr = ConsentAddress::updateOrCreate(
                    ['application_id' => $consent->id, 'kind' => 'reference'],
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
                Log::debug("Consent updateConsentByStep: Step 5 models updated", [
                    'reference_id' => $ref->id,
                    'ref_addr_id' => $refAddr->id
                ]);
                break;

            case 6:
                $loanReq = ConsentLoanRequest::updateOrCreate(
                    ['application_id' => $consent->id],
                    [
                        'loan_purpose' => $validated['loanPurpose'] ?? null,
                        'loan_term' => $validated['loanTerm'] ?? null,
                        'loan_amount_type' => $validated['loanAmountType'] ?? null,
                        'custom_loan_amount' => $validated['customLoanAmount'] ?? null,
                    ]
                );
                
                $account = ConsentDisbursementAccount::updateOrCreate(
                    ['application_id' => $consent->id],
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
                Log::debug("Consent updateConsentByStep: Step 6 models updated", [
                    'loan_req_id' => $loanReq->id,
                    'account_id' => $account->id
                ]);
                break;

            case 7:
                $consent->update([
                    'signature_data' => $validated['signatureData'],
                    'signed' => true,
                    'signed_at' => now(),
                ]);
                Log::info("Consent updateConsentByStep: Step 7 completed (Signature saved)", ['id' => $consent->id]);
                break;

            case 8:
                $uploadFields = [
                    'incomeSalarySlipDocuments' => 'income_salary_slip',
                    'incomeSalaryCertificateDocuments' => 'income_salary_certificate',
                    'incomeSalary50TawiDocuments' => 'income_salary_50tawi',
                    'incomeSalaryStatement6mDocuments' => 'income_salary_statement_6m',
                    'incomeSupplementarySlipDocuments' => 'income_supplementary_slip',
                    'incomeSupplementaryStatement6mDocuments' => 'income_supplementary_statement_6m',
                    'incomeRegisteredCertDocuments' => 'income_registered_corporate_cert',
                    'incomeRegisteredShareholderDocuments' => 'income_registered_shareholder_list',
                    'incomeRegisteredTradeDocuments' => 'income_registered_trade_registration',
                    'incomeRegisteredStatement1yDocuments' => 'income_registered_statement_1y',
                    'incomeUnregisteredLeaseDocuments' => 'income_unregistered_lease',
                    'incomeUnregisteredTaxDocuments' => 'income_unregistered_tax',
                    'incomeUnregisteredStatement1yDocuments' => 'income_unregistered_statement_1y',
                    'incomeUnregisteredInvoiceDocuments' => 'income_unregistered_invoice',
                    'incomeUnregisteredBusinessPhotoDocuments' => 'income_unregistered_business_photo',
                    'incomeSelfIndividualTax50Documents' => 'income_self_individual_tax',
                    'incomeSelfIndividualPndDocuments' => 'income_self_individual_pnd',
                    'incomeSelfIndividualStatement1yDocuments' => 'income_self_individual_statement_1y',
                    'incomeSelfBusinessTaxDocuments' => 'income_self_business_tax',
                    'incomeSelfBusinessStatement1yDocuments' => 'income_self_business_statement_1y',
                    'incomeSelfBusinessInvoiceDocuments' => 'income_self_business_invoice',
                    'incomeSelfBusinessPhotoDocuments' => 'income_self_business_photo',
                    'identityIdCardDocuments' => 'id_card',
                    'identityPassportDocuments' => 'passport',
                    'identityHouseRegistrationDocuments' => 'house_registration',
                    'identityWorkPermitDocuments' => 'work_permit',
                    'identityNameChangeDocuments' => 'name_change',
                ];

                foreach ($uploadFields as $fieldName => $documentType) {
                    if (!$request->hasFile($fieldName)) {
                        continue;
                    }

                    $files = (array) $request->file($fieldName, []);
                    Log::debug("Consent updateConsentByStep: Processing " . count($files) . " documents", [
                        'field' => $fieldName,
                        'document_type' => $documentType,
                    ]);

                    foreach ($files as $file) {
                        if ($file) {
                            $this->storeUploadedDocument($consent, $file, $documentType);
                        }
                    }
                }

                $applicant = $consent->applicant;
                $age = $applicant?->birthdate ? Carbon::parse($applicant->birthdate)->age : 0;
                $income = (float) ($applicant?->income ?? 0);
                $otherDebtInstallment = (float) ($applicant?->other_debt_installment ?? 0);

                $status = 'approved';
                if ($age < 20 || $age > 50 || $income < 15000 || ($income > 0 && $otherDebtInstallment > ($income / 2))) {
                    $status = 'rejected';
                }

                $consent->update(['status' => $status]);
                Log::info("Consent updateConsentByStep: Step 8 completed. Final status: {$status}", ['id' => $consent->id]);
                break;
        }
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

    private function storeUploadedDocument(ConsentApplication $consent, mixed $file, string $documentType): void
    {
        $disk = 'local';
        $path = $file->store('consent/' . $consent->id . '/documents', $disk);

        ConsentDocumentFile::create([
            'application_id' => $consent->id,
            'document_type' => $documentType,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    private function getDocumentTypeLabel(?string $documentType): string
    {
        return match ($documentType) {
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

    private function getNextAppNo(bool $lock = false): string
    {
        $query = ConsentApplication::withTrashed()->whereNotNull('app_no');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $this->incrementAppNo($query->orderByDesc('id')->value('app_no'));
    }

    private function incrementAppNo(?string $appNo): string
    {
        return $appNo
            ? str_pad((int) $appNo + 1, 13, '0', STR_PAD_LEFT)
            : '0000000000001';
    }
}
