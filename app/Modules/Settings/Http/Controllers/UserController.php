<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        $perPageOptions = [5, 10, 25, 50, 100];
        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, $perPageOptions, true)) {
            $perPage = 10;
        }

        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    $nameParts = preg_split('/\s+/', $search, 2);
                    if (count($nameParts) === 2) {
                        $query->orWhere(function ($query) use ($nameParts) {
                            $query->where('first_name', 'like', "%{$nameParts[0]}%")
                                ->where('last_name', 'like', "%{$nameParts[1]}%");
                        });
                    }
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('settings::users.index', [
            'users' => $users,
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
            'paginationPages' => $this->paginationPages($users->currentPage(), $users->lastPage()),
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('settings::users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'user_type' => ['required', Rule::in(['internal', 'external'])],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated): void {
            $validated['employee_code'] = $this->nextEmployeeCode($validated['user_type']);
            User::create($validated);
        });

        return redirect()
            ->route('settings.users.index')
            ->with('status', __('settings::messages.user_created'));
    }

    public function edit(Request $request, User $user): View
    {
        $this->ensureAdmin($request);

        return view('settings::users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->employee_code === 'EMP0001') {
            $validated['role'] = User::ROLE_ADMIN;
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('settings.users.index')
            ->with('status', __('settings::messages.user_updated'));
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdmin($request);
        abort_if($user->employee_code === 'EMP0001', 422, __('settings::messages.cannot_disable_admin'));

        $user->update(['is_active' => !$user->is_active]);

        if (!$user->is_active) {
            DB::table('sessions')->where('user_id', $user->getKey())->delete();
        }

        return redirect()
            ->route('settings.users.index')
            ->with('status', $user->is_active
                ? __('settings::messages.user_enabled')
                : __('settings::messages.user_disabled'));
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === User::ROLE_ADMIN, 403);
    }

    private function nextEmployeeCode(string $userType): string
    {
        $prefix = $userType === 'internal' ? 'EMP' : 'EXT';
        $lastNumber = User::query()
            ->where('employee_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->pluck('employee_code')
            ->map(fn (string $code): int => (int) substr($code, strlen($prefix)))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }

    private function paginationPages(int $currentPage, int $lastPage): array
    {
        if ($lastPage <= 1) {
            return [1];
        }

        $pages = [1, $lastPage];

        for ($page = max(1, $currentPage - 2); $page <= min($lastPage, $currentPage + 2); $page++) {
            $pages[] = $page;
        }

        sort($pages);

        return array_values(array_unique($pages));
    }
}
