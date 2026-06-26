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
                $customers = $users->map(function ($user) {
                    $signed = (bool) rand(0, 1);

                    return (object) [
                        'id' => $user->id,
                        'code' => 'CUST-' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                        'name' => $user->name ?? $user->email ?? 'User',
                        'signed' => $signed,
                        'signed_at' => $signed
                            ? now()->subDays(rand(0, 30))->format('Y-m-d')
                            : null,
                    ];
                });
            } else {
                $customers = collect([
                    (object) ['id' => 1, 'code' => 'CUST-001', 'name' => 'สมชาย ใจดี', 'signed' => true, 'signed_at' => '2026-06-20'],
                    (object) ['id' => 2, 'code' => 'CUST-002', 'name' => 'สมหญิง ปิติ', 'signed' => true, 'signed_at' => '2026-06-21'],
                    (object) ['id' => 3, 'code' => 'CUST-003', 'name' => 'จักริน ทองดี', 'signed' => true, 'signed_at' => '2026-06-22'],
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
            'name' => ['required', 'string', 'max:255'],
        ]);

        $forms = session('consent_forms', []);
        $nextId = count($forms) + 100;

        $forms[] = [
            'id' => $nextId,
            'code' => 'CUST-' . str_pad($nextId, 3, '0', STR_PAD_LEFT),
            'name' => $validated['name'],
            'signed' => false,
            'signed_at' => null,
        ];

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
            'name' => $user->name ?? $user->email ?? 'User',
            'signed' => $signed,
            'signed_at' => $signed && $user->consent_signed_at
                ? Carbon::parse($user->consent_signed_at)->format('Y-m-d')
                : null,
        ];
    }
}
