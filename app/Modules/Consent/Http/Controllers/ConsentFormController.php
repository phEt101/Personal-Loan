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
use Illuminate\Support\Facades\Storage;

class ConsentFormController extends Controller {
    private const OPTION_OTHER = 'อื่นๆ';

    public function saveStep(Request $request){
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
                        'officer_group_id' => $validated['officer_group_id'] ?? null,
                        'loan_product_id' => $validated['loan_product_id'] ?? null,
                        'status' => 'กำลังดำเนินการ',
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
                    throw new Exception("ไม่พบข้อมูลใบคำขอหลัก");
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
                    'incomeDocuments',
                    'identityDocuments',
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

    private function getValidationMessages(): array{
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

    private function getValidationAttributes(): array{
        return [
            'app_date' => 'วันที่เขียนคำขอ',
            'app_no' => 'เลขที่ใบคำขอ',
            'officer_name' => 'เจ้าหน้าที่สินเชื่อ',
            'officer_phone' => 'เบอร์ติดต่อเจ้าหน้าที่',
            'officer_group_id' => 'กลุ่มเจ้าหน้าที่',
            'loan_product_id' => 'ประเภทผลิตภัณฑ์',
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

    private function getStepRules(int $step, Request $request): array {
        return match ($step) {
            1 => $this->getStep1Rules($request),
            2 => $this->getStep2Rules(),
            3 => $this->getStep3Rules(),
            4 => $this->getStep4Rules(),
            5 => $this->getStep5Rules(),
            6 => $this->getStep6Rules($request),
            7 => $this->getStep7Rules(),
            8 => $this->getStep8Rules(),
            default => [],
        };
    }

    private function getStep1Rules(Request $request): array {
        return [
            // ข้อมูลใบคำขอ + ข้อมูลส่วนตัว
            'app_date' => ['nullable', 'date'],
            'app_no' => ['nullable', 'string', 'max:13'],
            'officer_name' => ['nullable', 'string', 'max:255'],
            'officer_phone' => ['nullable', 'string', 'max:20'],
            'officer_group_id' => ['nullable', 'integer', 'exists:officer_groups,id'],
            'loan_product_id' => ['nullable', 'integer', 'exists:loan_products,id'],
            'title' => ['required', 'string', 'max:50'],
            'title_other' => ['nullable', 'required_if:title,' . self::OPTION_OTHER, 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['required', 'date'],
            'nationality' => ['required', 'string', 'max:50'],
            'id_type' => ['required', 'in:id_card,passport'],
            'id_card' => $this->getIdCardRule($request),
            'education' => ['required', 'string', 'max:50'],
            'marital_status' => ['required', 'string', 'max:50'],
        ];
    }

    private function getIdCardRule(Request $request): array {
        return [
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
        ];
    }

    private function getStep2Rules(): array {
        return [
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
        ];
    }

    private function getStep3Rules(): array {
        return [
            // ข้อมูลอาชีพ/สถานที่ทำงาน
            'useHomeAddress' => ['nullable', 'boolean'],
            'occupation' => ['required', 'string', 'max:100'],
            'governmentLevel' => ['nullable', 'required_if:occupation,ข้าราชการ', 'string', 'max:100'],
            'occupationOther' => ['nullable', 'required_if:occupation,' . self::OPTION_OTHER, 'string', 'max:100'],
            'careerField' => ['required', 'string', 'max:100'],
            'careerFieldOther' => ['nullable', 'required_if:careerField,' . self::OPTION_OTHER, 'string', 'max:100'],
            'companyName' => ['nullable', 'string', 'max:255'],
            'businessType' => ['required', 'string', 'max:255'],
            'businessTypeOther' => ['nullable', 'required_if:businessType,' . self::OPTION_OTHER, 'string', 'max:255'],
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
        ];
    }

    private function getStep4Rules(): array {
        return [
            // รายได้
            'income' => ['required', 'numeric', 'min:0'],
            'extraIncome' => ['nullable', 'numeric', 'min:0'],
            'extraIncomeSource' => ['required', 'string', 'max:255'],
            'extraIncomeSourceOther' => ['nullable', 'required_if:extraIncomeSource,' . self::OPTION_OTHER, 'string', 'max:255'],
            'incomeCountry' => ['nullable', 'string', 'max:100'],
            'hasOtherDebts' => ['required', 'string', 'max:10'],
            'otherDebtInstallment' => ['nullable', 'required_if:hasOtherDebts,มี', 'numeric', 'min:0'],
            'hasExistingLoan' => ['required', 'string', 'in:ใช่,ไม่ใช่'],
            'existingLoanInstitutionCount' => ['nullable', 'required_if:hasExistingLoan,ใช่', 'integer', 'min:1'],
            'existingLoanTotalAmount' => ['nullable', 'required_if:hasExistingLoan,ใช่', 'numeric', 'min:0'],
        ];
    }

    private function getStep5Rules(): array {
        return [
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
        ];
    }

    private function getStep6Rules(Request $request): array {
        return [
            // ความประสงค์กู้/การชำระเงิน
            'loanPurpose' => ['required', 'string', 'max:255'],
            'loanTerm' => $this->getLoanTermRule($request),
            'loanAmountType' => ['required', 'string', 'in:full,custom'],
            'customLoanAmount' => ['nullable', 'required_if:loanAmountType,custom', 'numeric', 'min:0'],
            'accountNumber' => ['required', 'string', 'max:255'],
            'accountType' => ['required', 'string', 'max:255'],
            'bankName' => ['required', 'string', 'max:255'],
            'accountName' => ['required', 'string', 'max:255'],
            'paymentMethod' => ['required', 'string', 'max:255'],
            'directDebitAmount' => ['nullable', 'required_if:paymentMethod,ชําระโดยการหักบัญชี', 'numeric', 'min:0'],
            'directDebitAccountNumber' => ['nullable', 'required_if:paymentMethod,ชําระโดยการหักบัญชี', 'string', 'max:50'],
        ];
    }

    private function getLoanTermRule(Request $request): array {
        return [
            'required', 'integer',
            function ($attribute, $value, $fail) use ($request) {
                $consentId = $request->input('consent_id');
                $consent = null;
                if ($consentId) {
                    $id = \Illuminate\Support\Facades\Crypt::decryptString($consentId);
                    $consent = \App\Modules\Consent\Models\ConsentApplication::find($id);
                }

                $product = $consent?->loanProduct;
                if ($product && $product->max_loan_term && (int)$value > (int)$product->max_loan_term) {
                    $fail("ระยะเวลาผ่อนชำระสูงสุดสำหรับสินเชื่อประเภทนี้คือ {$product->max_loan_term} เดือน");
                }
            },
        ];
    }

    private function getStep7Rules(): array {
        return [
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
        ];
    }

    private function getStep8Rules(): array {
        return [
            // แนบไฟล์หลักฐานการเงินและเอกสารแสดงตัวตน
            'incomeDocuments' => ['nullable', 'array'],
            'incomeDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'identityDocuments' => ['nullable', 'array'],
            'identityDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    private function updateConsentByStep(ConsentApplication $consent, int $step, array $validated, Request $request): void {
        Log::debug("Consent updateConsentByStep: Dispatching step {$step}", ['id' => $consent->id]);

        match ($step) {
            1 => $this->handleStep1($consent, $validated, $request),
            2 => $this->handleStep2($consent, $validated, $request),
            3 => $this->handleStep3($consent, $validated, $request),
            4 => $this->handleStep4($consent, $validated, $request),
            5 => $this->handleStep5($consent, $validated, $request),
            6 => $this->handleStep6($consent, $validated, $request),
            7 => $this->handleStep7($consent, $validated, $request),
            8 => $this->handleStep8($consent, $validated, $request),
            default => null,
        };
    }

    private function handleStep1(ConsentApplication $consent, array $validated, Request $request): void {
        $consent->update([
            'app_date' => $validated['app_date'] ?? $consent->app_date,
            'app_no' => $validated['app_no'] ?? $consent->app_no,
            'officer_name' => $validated['officer_name'] ?? $consent->officer_name,
            'officer_phone' => $validated['officer_phone'] ?? $consent->officer_phone,
            'officer_group_id' => $validated['officer_group_id'] ?? $consent->officer_group_id,
            'loan_product_id' => $validated['loan_product_id'] ?? $consent->loan_product_id,
        ]);

        $title = $validated['title'] ?? null;
        if ($title === self::OPTION_OTHER && $request->filled('title_other')) {
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

        $this->processApplicantPhoto($consent, $request);
    }

    private function handleStep2(ConsentApplication $consent, array $validated, Request $request): void {
        $applicant = $consent->applicants()->orderBy('applicant_order')->first();

        $homeAddr = ConsentAddress::updateOrCreate(
            ['applicant_id' => $applicant?->id, 'kind' => 'home'],
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
            [
                'applicant_id' => $applicant?->id,
                'phone_home' => $validated['phone_home'] ?? null,
                'phone_mobile' => $validated['phone_mobile'] ?? null,
                'email' => $validated['email'] ?? null,
            ]
        );
        
        $consent->update(['document_delivery' => $validated['documentDelivery'] ?? null]);
        
        $docAddr = ConsentAddress::updateOrCreate(
            [
                'applicant_id' => $applicant?->id,
                'kind' => 'document',
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
    }

    private function handleStep3(ConsentApplication $consent, array $validated, Request $request): void {
        $occupation = $validated['occupation'] ?? null;
        if ($occupation === self::OPTION_OTHER && $request->filled('occupationOther')) {
            $occupation = $request->occupationOther;
        }
        $careerField = $validated['careerField'] ?? null;
        if ($careerField === self::OPTION_OTHER && $request->filled('careerFieldOther')) {
            $careerField = $request->careerFieldOther;
        }
        $businessType = $validated['businessType'] ?? null;
        if ($businessType === self::OPTION_OTHER && $request->filled('businessTypeOther')) {
            $businessType = $request->businessTypeOther;
        }

        $consent->applicants()->orderBy('applicant_order')->first()->update([
            'occupation' => $occupation,
            'government_level' => $validated['governmentLevel'] ?? null,
            'career_field' => $careerField,
        ]);

        $applicant = $consent->applicants()->orderBy('applicant_order')->first();

        $employment = ConsentEmployment::updateOrCreate(
            ['applicant_id' => $applicant?->id],
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
            ['applicant_id' => $applicant?->id, 'kind' => 'work'],
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
            ['applicant_id' => $applicant?->id],
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
    }

    private function handleStep4(ConsentApplication $consent, array $validated, Request $request): void {
        $extraIncomeSource = $validated['extraIncomeSource'] ?? null;
        if ($extraIncomeSource === 'อื่นๆ' && $request->filled('extraIncomeSourceOther')) {
            $extraIncomeSource = $request->extraIncomeSourceOther;
        }
        
        // Convert to boolean for database consistency
        $hasOtherDebts = $validated['hasOtherDebts'] === 'มี';
        $hasExistingLoan = $validated['hasExistingLoan'] === 'ใช่';

        $consent->applicants()->orderBy('applicant_order')->first()->update([
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
    }

    private function handleStep5(ConsentApplication $consent, array $validated, Request $request): void {
        $applicant = $consent->applicants()->orderBy('applicant_order')->first();

        $ref = ConsentReference::updateOrCreate(
            ['applicant_id' => $applicant?->id],
            [
                'ref_name' => $validated['refName'] ?? null,
                'ref_relation' => $validated['refRelation'] ?? null,
                'ref_phone_home' => $validated['refPhoneHome'] ?? null,
                'ref_phone_mobile' => $validated['refPhoneMobile'] ?? null,
            ]
        );
        
        $applicant = $consent->applicants()->orderBy('applicant_order')->first();

        $refAddr = ConsentAddress::updateOrCreate(
            ['applicant_id' => $applicant?->id, 'kind' => 'reference'],
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
    }

    private function handleStep6(ConsentApplication $consent, array $validated, Request $request): void {
        $product = $consent->loanProduct;
        $applicant = $consent->applicants()->orderBy('applicant_order')->first();
        $calculatedAmount = 0;

        if ($product && $applicant) {
            $totalIncome = (float)($applicant->income ?? 0) + (float)($applicant->extra_income ?? 0);

            // 1. คำนวณตามตัวคูณถ้าระบุเกณฑ์รายได้ (เช่น P-Loan)
            if ($product->income_threshold) {
                if ($totalIncome < (float)$product->income_threshold) {
                    $multiplier = (float)($product->multiplier_low_income ?? 1.5);
                } else {
                    $multiplier = (float)($product->multiplier_high_income ?? 5.0);
                }
                $calculatedAmount = $totalIncome * $multiplier;
            }

            // 2. จำกัดวงเงินสูงสุดถ้ามีการระบุ Max Loan Amount (เช่น Nano Finance 100,000 หรือ Cap สำหรับ P-Loan)
            if ($product->max_loan_amount) {
                $maxCap = (float)$product->max_loan_amount;
                if ($calculatedAmount <= 0) {
                    $calculatedAmount = $maxCap;
                } else {
                    $calculatedAmount = min($calculatedAmount, $maxCap);
                }
            }
        }

        $loanReq = ConsentLoanRequest::updateOrCreate(
            ['application_id' => $consent->id],
            [
                'loan_purpose' => $validated['loanPurpose'] ?? null,
                'loan_term' => $validated['loanTerm'] ?? null,
                'loan_amount_type' => $validated['loanAmountType'] ?? null,
                'custom_loan_amount' => $validated['customLoanAmount'] ?? null,
                'calculated_eligible_amount' => $calculatedAmount,
            ]
        );
        
        $applicant = $consent->applicants()->orderBy('applicant_order')->first();

        $account = ConsentDisbursementAccount::updateOrCreate(
            ['applicant_id' => $applicant?->id],
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
    }

    private function handleStep7(ConsentApplication $consent, array $validated, Request $request): void {
        $consent->update([
            'signature_data' => $validated['signatureData'],
            'signed' => true,
            'signed_at' => now(),
        ]);
        Log::info("Consent updateConsentByStep: Step 7 completed (Signature saved)", ['id' => $consent->id]);
    }

    private function handleStep8(ConsentApplication $consent, array $validated, Request $request): void {
        $uploadFields = [
            'incomeDocuments' => 'income_document',
            'identityDocuments' => 'identity_document',
        ];

        foreach ($uploadFields as $fieldName => $documentType) {
            $this->processStep8UploadField($consent, $request, $fieldName, $documentType);
        }

        $status = $this->determineStep8Status($consent);
        $consent->update(['status' => $status]);
        $consent->update(['loan_status' => 'รอวิเคราะห์ 1/2']);

        Log::info("Consent updateConsentByStep: Step 8 completed. Final status: {$status}", ['id' => $consent->id]);
    }

    private function processApplicantPhoto(ConsentApplication $consent, Request $request): void {
        if (!$request->hasFile('applicantPhoto')) {
            return;
        }

        $file = $request->file('applicantPhoto');
        if (!$file) {
            return;
        }

        $disk = Storage::disk('local');
        ConsentDocumentFile::query()
            ->where('document_type', 'applicant_photo')
            ->whereHas('applicant', function ($q) use ($consent) {
                $q->where('application_id', $consent->id);
            })
            ->get()
            ->each(function (ConsentDocumentFile $existingDocument) use ($disk) {
                if ($disk->exists($existingDocument->path)) {
                    $disk->delete($existingDocument->path);
                }
                $existingDocument->delete();
            });

        $fileName = 'applicant-photo-' . now()->format('YmdHis') . '-' . uniqid() . '.' . strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $file->storeAs("consent/{$consent->encrypted_id}/photos", $fileName, 'local');

        $applicant = $consent->applicants()->orderBy('applicant_order')->first();

        ConsentDocumentFile::create([
            'applicant_id' => $applicant->id,
            'document_type' => 'applicant_photo',
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    private function processStep8UploadField(ConsentApplication $consent, Request $request, string $fieldName, string $documentType): void {
        if (!$request->hasFile($fieldName)) {
            return;
        }

        $files = (array) $request->file($fieldName, []);
        Log::debug("Consent updateConsentByStep: Processing " . count($files) . " documents (always zipping)", [
            'field' => $fieldName,
            'document_type' => $documentType,
        ]);

        if (count($files) === 0) {
            return;
        }

        // If there is an existing ZIP document for this document type, append files to it (rezip).
        $existingZip = $consent->incomeDocuments()
            ->where('document_type', $documentType)
            ->where('mime_type', 'application/zip')
            ->orderByDesc('id')
            ->first();

        if ($existingZip) {
            $success = $this->appendToExistingZip($consent, $existingZip, $files);
            if (!$success) {
                Log::warning('Consent updateConsentByStep: Failed to append to existing zip for ' . $fieldName, ['consent_id' => $consent->id, 'document_id' => $existingZip->id]);
            }
            return;
        }

        $zipData = $this->createStep8Zip($consent, $fieldName, $files);
        if (!$zipData) {
            Log::warning('Consent updateConsentByStep: Failed to create zip for ' . $fieldName, ['consent_id' => $consent->id]);
            return;
        }

        $applicant = $consent->applicants()->orderBy('applicant_order')->first();

        ConsentDocumentFile::create([
            'applicant_id' => $applicant->id,
            'document_type' => $documentType,
            'disk' => 'local',
            'path' => 'consent/' . $consent->encrypted_id . '/documents/' . $zipData['zipName'],
            'original_name' => $zipData['zipName'],
            'mime_type' => 'application/zip',
            'size' => filesize($zipData['zipPath']),
        ]);
    }

    /**
     * Append uploaded files into an existing zip stored by ConsentDocumentFile.
     * Returns true on success.
     */
    private function appendToExistingZip(ConsentApplication $consent, ConsentDocumentFile $existingZip, array $files): bool
    {
        $disk = Storage::disk($existingZip->disk);
        if (!$disk->exists($existingZip->path)) {
            return false;
        }

        $origPath = $disk->path($existingZip->path);
        $zip = new \ZipArchive();
        if ($zip->open($origPath) !== true) {
            return false;
        }

        // Collect existing entry names to avoid collisions
        $existingNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) continue;
            $entry = $stat['name'];
            if (substr($entry, -1) === '/') continue;
            $existingNames[] = $entry;
        }

        // Add new files with index-based prefix to avoid collisions
        $startIndex = count($existingNames) + 1;
        foreach ($files as $index => $file) {
            if (!$file) continue;
            $original = (string) ($file->getClientOriginalName() ?: 'file');
            $entryName = sprintf('%02d_%s', $startIndex + $index, $this->normalizeZipEntryName($original));
            $contents = file_get_contents($file->getRealPath());
            // If entry already exists (unlikely), append a uniq suffix
            $tryName = $entryName;
            $k = 1;
            while (in_array($tryName, $existingNames, true)) {
                $tryName = $entryName . '_' . $k;
                $k++;
            }
            $zip->addFromString($tryName, $contents);
            $existingNames[] = $tryName;
        }

        $zip->close();

        // update model metadata
        try {
            $newSize = filesize($origPath);
            $existingZip->size = $newSize;
            $existingZip->mime_type = 'application/zip';
            $existingZip->save();
        } catch (\Exception $e) {
            Log::warning('Consent appendToExistingZip: failed to update metadata', ['err' => $e->getMessage()]);
        }

        return true;
    }

    private function createStep8Zip(ConsentApplication $consent, string $fieldName, array $files): ?array {
        $disk = Storage::disk('local');
        $targetDir = $disk->path('consent/' . $consent->encrypted_id . '/documents');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $zipName = $fieldName . '_' . time() . '.zip';
        $zipPath = $targetDir . DIRECTORY_SEPARATOR . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            return null;
        }

        foreach ($files as $index => $file) {
            if (!$file) {
                continue;
            }

            $original = (string) ($file->getClientOriginalName() ?: 'file');
            $entryName = sprintf('%02d_%s', $index + 1, $this->normalizeZipEntryName($original));
            $contents = file_get_contents($file->getRealPath());
            $zip->addFromString($entryName, $contents);
        }

        $zip->close();

        return [
            'zipName' => $zipName,
            'zipPath' => $zipPath,
        ];
    }

    private function normalizeZipEntryName(string $name): string
    {
        $name = trim($name);
        $name = str_replace(["\0", '/', '\\'], '_', $name);
        $name = preg_replace('/[[:cntrl:]]+/u', '', $name);
        $name = preg_replace('/\s+/u', ' ', $name);
        $name = preg_replace('/\.+$/u', '', $name);

        return $name !== '' ? $name : 'file';
    }

    private function determineStep8Status(ConsentApplication $consent): string {
        $applicant = $consent->applicants()->orderBy('applicant_order')->first();
        $age = $applicant?->birthdate ? Carbon::parse($applicant->birthdate)->age : 0;
        $income = (float) ($applicant?->income ?? 0);
        $otherDebtInstallment = (float) ($applicant?->other_debt_installment ?? 0);

        $isRejected = $age < 20
            || $age > 50
            || $income < 15000
            || ($income > 0 && $otherDebtInstallment > ($income / 2));

        return $isRejected ? 'ไม่ผ่าน' : 'ผ่าน';
    }

    protected function getNextAppNo(bool $lock = false): string {
        $query = ConsentApplication::withTrashed()->whereNotNull('app_no');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $this->incrementAppNo($query->orderByDesc('id')->value('app_no'));
    }

    private function incrementAppNo(?string $appNo): string {
        return $appNo
            ? str_pad((int) $appNo + 1, 13, '0', STR_PAD_LEFT)
            : '0000000000001';
    }
}
