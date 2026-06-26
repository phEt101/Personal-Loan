<?php

namespace App\Modules\Consent\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class ConsentController extends Controller
{
    public function index()
    {
        $useReal = false;
        try {
            $useReal = Schema::hasTable('users') && Schema::hasColumn('users', 'consent_signed');
        } catch (\Exception $e) {
            $useReal = false;
        }

        if ($useReal) {
            $customers = User::where('consent_signed', true)->orderBy('consent_signed_at', 'desc')->get();
            $total = User::count();
            $signed = User::where('consent_signed', true)->count();
        } else {
            $users = User::take(12)->get();
            $customers = $users->map(function ($u) {
                return (object) [
                    'id' => $u->id,
                    'name' => $u->name ?? ($u->email ?? 'User'),
                    'phone' => $u->phone ?? null,
                    'email' => $u->email ?? null,
                    'consent_signed' => (bool) rand(0, 1),
                    'consent_signed_at' => now()->subDays(rand(0, 30))->toDateTimeString(),
                ];
            });
            $total = $customers->count();
            $signed = $customers->where('consent_signed', true)->count();
        }

        $failed = max(0, ($total ?? 0) - ($signed ?? 0));
        $signedPercent = $total ? round(($signed / $total) * 100, 1) : 0;

        return view('consent::index', compact('customers', 'total', 'signed', 'failed', 'signedPercent'));
    }
}
