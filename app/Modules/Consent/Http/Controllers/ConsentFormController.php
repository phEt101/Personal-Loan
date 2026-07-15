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
                        'officer_group' => $validated['officer_group'] ?? null,
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
            'officer_group' => 'กลุ่มเจ้าหน้าที่',
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
            1 => [
                // ข้อมูลใบคำขอ + ข้อมูลส่วนตัว
                'app_date' => ['nullable', 'date'],
                'app_no' => ['nullable', 'string', 'max:13'],
                'officer_name' => ['nullable', 'string', 'max:255'],
                'officer_phone' => ['nullable', 'string', 'max:20'],
                'officer_group' => ['nullable', 'string', 'max:50'],
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
                'incomeDocuments' => ['nullable', 'array'],
                'incomeDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
                'identityDocuments' => ['nullable', 'array'],
                'identityDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
            ],
            default => [],
        };
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
            'officer_group' => $validated['officer_group'] ?? $consent->officer_group,
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
    }

    private function handleStep2(ConsentApplication $consent, array $validated, Request $request): void {
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
    }

    private function handleStep3(ConsentApplication $consent, array $validated, Request $request): void {
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
    }

    private function handleStep4(ConsentApplication $consent, array $validated, Request $request): void {
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
    }

    private function handleStep5(ConsentApplication $consent, array $validated, Request $request): void {
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
    }

    private function handleStep6(ConsentApplication $consent, array $validated, Request $request): void {
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
            if (!$request->hasFile($fieldName)) {
                continue;
            }

            $files = (array) $request->file($fieldName, []);
            Log::debug("Consent updateConsentByStep: Processing " . count($files) . " documents (always zipping)", [
                'field' => $fieldName,
                'document_type' => $documentType,
            ]);

            // Always create a ZIP archive for this field (even when a single file)
            if (count($files) > 0) {
                $disk = Storage::disk('local');
                $targetDir = $disk->path('consent/' . $consent->id . '/documents');
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                $zipName = $fieldName . '_' . time() . '.zip';
                $zipPath = $targetDir . DIRECTORY_SEPARATOR . $zipName;

                $zip = new \ZipArchive();
                    if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                    foreach ($files as $file) {
                        if (!$file) {
                            continue;
                        }
                        $original = (string) ($file->getClientOriginalName() ?: 'file');
                        $safeOriginal = preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
                        $uniqueName = time() . '_' . uniqid() . '_' . $safeOriginal;
                        $contents = file_get_contents($file->getRealPath());
                        $zip->addFromString($uniqueName, $contents);
                    }
                    $zip->close();

                    // create DB record pointing to the zip
                    ConsentDocumentFile::create([
                        'application_id' => $consent->id,
                        'document_type' => $documentType,
                        'disk' => 'local',
                        'path' => 'consent/' . $consent->id . '/documents/' . $zipName,
                        'original_name' => $zipName,
                        'mime_type' => 'application/zip',
                        'size' => filesize($zipPath),
                    ]);
                } else {
                    Log::warning('Consent updateConsentByStep: Failed to create zip for ' . $fieldName, ['consent_id' => $consent->id]);
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
