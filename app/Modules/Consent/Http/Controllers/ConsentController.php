<?php

namespace App\Modules\Consent\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ConsentController extends Controller
{
    public function index()
    {
        $useReal = false;

        try {
            $useReal = Schema::hasTable('users')
                && Schema::hasColumn('users', 'consent_signed');
        } catch (\Exception $e) {
            $useReal = false;
        }

        if ($useReal) {
            $users = User::orderBy('id')->get();
            $customers = $users->map(fn ($user) => $this->mapUser($user));
            $total = $users->count();
            $signed = $users->where('consent_signed', true)->count();
        } else {
            $users = User::take(12)->get();

            if ($users->isNotEmpty()) {
                $customers = $users->map(function ($user, $index) {
                    $signed = (bool) rand(0, 1);
                    $firstNameTh = ['สมชาย', 'สมหญิง', 'จักริน', 'ณิชา', 'ปกรณ์', 'วิชัย', 'อนงค์', 'เกรียงไกร'];
                    $lastNameTh = ['ใจดี', 'ปิติ', 'ทองดี', 'พูนทรัพย์', 'รัตนวิจิตร', 'แสนเก๋', 'เจริญดี', 'สิงห์โต'];
                    $nameTh = ($firstNameTh[$index % count($firstNameTh)]) . ' ' . ($lastNameTh[$index % count($lastNameTh)]);

                    return (object) [
                        'id' => $user->id,
                        'code' => 'CUST-' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                        'app_date' => now()->subDays($index)->format('Y-m-d'),
                        'app_no' => 'APP2026' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                        'officer_name' => 'สมเกียรติ มุ่งมั่น',
                        'officer_phone' => '0891112222',
                        'title' => ($index % 2 == 0) ? 'นาย' : 'นางสาว',
                        'name' => $nameTh,
                        'name_en' => 'MOCK CUSTOMER ' . $user->id,
                        'dob' => '199' . ($index % 9) . '-08-20',
                        'id_card' => '1100' . ($index + 1) . '002' . ($index + 3) . '456',
                        'gender' => ($index % 2 == 0) ? 'ชาย' : 'หญิง',
                        'age' => 25 + ($index * 2),
                        'nationality' => 'ไทย',
                        'marital_status' => ($index % 3 == 0) ? 'โสด' : 'สมรส',
                        'address' => '99/5 หมู่ ' . ($index + 1) . ' ถ.พหลโยธิน แขวงลาดยาว เขตจตุจักร กรุงเทพฯ 10900',
                        'mobile' => '08' . ($index) . '1234567',
                        'email' => $user->email ?? 'mock_' . $user->id . '@example.com',
                        'line_id' => 'mock.line.' . $user->id,
                        'company_name' => 'บริษัท ตัวอย่างการค้า จำกัด',
                        'occupation' => 'พนักงานบริษัท',
                        'position' => 'ผู้ประสานงานทั่วไป',
                        'working_years' => ($index % 5) + 1,
                        'income' => 15000 + ($index * 3000),
                        'loan_amount' => 10000 + ($index * 5000),
                        'loan_term' => 24,
                        'bank_name' => 'กสิกรไทย',
                        'bank_account' => '020-1-234' . $index . '-9',
                        'bank_account_name' => $nameTh,
                        'signed' => $signed,
                        'signed_at' => $signed
                            ? now()->subDays(rand(0, 30))->format('Y-m-d')
                            : null,
                    ];
                });
            } else {
                $customers = collect([
                    (object) [
                        'id' => 1, 'code' => 'CUST-001', 'app_date' => '2026-06-20', 'app_no' => '1002003004005',
                        'officer_name' => 'นพดล สุขดี', 'officer_phone' => '0817778888',
                        'title' => 'นาย', 'name' => 'สมชาย ใจดี', 'name_en' => 'SOMCHAI JAIDEE',
                        'dob' => '1990-06-12', 'id_card' => '1100800123456', 'gender' => 'ชาย', 'age' => 36, 'nationality' => 'ไทย',
                        'marital_status' => 'โสด', 'address' => '12/3 ถ.สุขุมวิท เขตวัฒนา กรุงเทพฯ 10110', 'mobile' => '0812345678',
                        'email' => 'somchai@example.com', 'line_id' => 'somchai.jd', 'company_name' => 'บริษัท ก้าวหน้า จำกัด',
                        'occupation' => 'พนักงานบริษัท', 'position' => 'พนักงานขาย', 'working_years' => 4, 'income' => 22000,
                        'loan_amount' => 30000, 'loan_term' => 24, 'bank_name' => 'กสิกรไทย', 'bank_account' => '123-4-56789-0',
                        'bank_account_name' => 'สมชาย ใจดี', 'signed' => true, 'signed_at' => '2026-06-20'
                    ],
                    (object) [
                        'id' => 2, 'code' => 'CUST-002', 'app_date' => '2026-06-21', 'app_no' => '1002003004006',
                        'officer_name' => 'สุรชัย เรียนรู้', 'officer_phone' => '0895556666',
                        'title' => 'นางสาว', 'name' => 'สมหญิง ปิติ', 'name_en' => 'SOMYING PITI',
                        'dob' => '1995-12-05', 'id_card' => '3100200852369', 'gender' => 'หญิง', 'age' => 30, 'nationality' => 'ไทย',
                        'marital_status' => 'โสด', 'address' => '45/8 ถ.พญาไท เขตราชเทวี กรุงเทพฯ 10400', 'mobile' => '0898765432',
                        'email' => 'somying@example.com', 'line_id' => 'somy_piti', 'company_name' => 'บมจ. แสนสบาย',
                        'occupation' => 'พนักงานบริษัท', 'position' => 'นักพัฒนาซอฟต์แวร์', 'working_years' => 2, 'income' => 45000,
                        'loan_amount' => 100000, 'loan_term' => 36, 'bank_name' => 'ไทยพาณิชย์', 'bank_account' => '456-7-89012-3',
                        'bank_account_name' => 'สมหญิง ปิติ', 'signed' => true, 'signed_at' => '2026-06-21'
                    ],
                    (object) [
                        'id' => 3, 'code' => 'CUST-003', 'app_date' => '2026-06-22', 'app_no' => '1002003004007',
                        'officer_name' => 'เกียรติศักดิ์ พาสุข', 'officer_phone' => '0861113333',
                        'title' => 'นาย', 'name' => 'จักริน ทองดี', 'name_en' => 'JAKRIN THONGDEE',
                        'dob' => '1988-02-18', 'id_card' => '3501200147852', 'gender' => 'ชาย', 'age' => 38, 'nationality' => 'ไทย',
                        'marital_status' => 'สมรส', 'address' => '78 ถ.สีลม เขตบางรัก กรุงเทพฯ 10500', 'mobile' => '0854443333',
                        'email' => 'jakrin@example.com', 'line_id' => 'jakrin.td', 'company_name' => 'ร้านขายอาหารทองดี',
                        'occupation' => 'เจ้าของกิจการ', 'position' => 'เจ้าของกิจการ', 'working_years' => 8, 'income' => 60000,
                        'loan_amount' => 150000, 'loan_term' => 48, 'bank_name' => 'กรุงเทพ', 'bank_account' => '789-0-12345-6',
                        'bank_account_name' => 'จักริน ทองดี', 'signed' => true, 'signed_at' => '2026-06-22'
                    ],
                ]);
            }

            $total = $customers->count();
            $signed = $customers->where('signed', true)->count();
        }

        $sessionForms = collect(session('consent_forms', []))->map(fn ($form) => (object) $form);
        $customers = $sessionForms->concat($customers->values());
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
            'address' => ['nullable', 'string', 'max:500'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'line_id' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'working_years' => ['nullable', 'integer'],
            'income' => ['nullable', 'numeric'],
            'loan_amount' => ['nullable', 'numeric'],
            'loan_term' => ['nullable', 'integer'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['title']) && $validated['title'] === 'อื่นๆ' && !empty($request->title_other)) {
            $validated['title'] = $request->title_other;
        }

        $forms = session('consent_forms', []);
        $nextId = count($forms) + 100;

        $forms[] = array_merge([
            'id' => $nextId,
            'code' => 'CUST-' . str_pad($nextId, 3, '0', STR_PAD_LEFT),
            'signed' => true, // Auto sign when form is created through detailed consent form
            'signed_at' => now()->format('Y-m-d'),
        ], $validated);

        session(['consent_forms' => $forms]);

        return redirect()
            ->route('consent.index')
            ->with('success', 'สร้างใบยินยอมสำหรับ ' . $validated['name'] . ' เรียบร้อยแล้ว');
    }

    private function mapUser(User $user): object
    {
        $signed = (bool) $user->consent_signed;

        return (object) [
            'id' => $user->id,
            'code' => 'CUST-' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
            'app_date' => now()->format('Y-m-d'),
            'app_no' => '1002003004' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
            'officer_name' => 'เจ้าหน้าที่ สินเชื่อดี',
            'officer_phone' => '02-123-4567',
            'title' => 'นาย',
            'name' => $user->name ?? $user->email ?? 'User',
            'name_en' => 'USER NAME',
            'dob' => '1995-05-15',
            'id_card' => '1234567890123',
            'gender' => 'ชาย',
            'age' => '31',
            'nationality' => 'ไทย',
            'marital_status' => 'โสด',
            'address' => '123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110',
            'mobile' => '0812345678',
            'email' => $user->email ?? 'customer@example.com',
            'line_id' => 'customer_line',
            'company_name' => 'บริษัท ตัวอย่าง จำกัด',
            'occupation' => 'พนักงานบริษัท',
            'position' => 'ผู้จัดการฝ่ายขาย',
            'working_years' => '3',
            'income' => '35000',
            'loan_amount' => '50000',
            'loan_term' => '24',
            'bank_name' => 'กสิกรไทย',
            'bank_account' => '123-4-56789-0',
            'bank_account_name' => $user->name ?? 'User Name',
            'signed' => $signed,
            'signed_at' => $signed && $user->consent_signed_at
                ? Carbon::parse($user->consent_signed_at)->format('Y-m-d')
                : null,
        ];
    }
}
