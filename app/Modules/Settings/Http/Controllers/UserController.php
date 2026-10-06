<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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
        $perPageOptions = [5, 10, 25, 50, 100];
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 10;
        }

        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $sortableColumns = ['employee_code', 'full_name', 'email', 'user_type', 'role', 'status', 'created_at'];
        $sort = in_array($request->query('sort'), $sortableColumns, true)
            ? (string) $request->query('sort')
            : 'employee_code';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
        $usersQuery = User::query()
            ->with('role')
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
            });

        match ($sort) {
            'full_name' => $usersQuery->orderBy('first_name', $direction)->orderBy('last_name', $direction),
            'role' => $usersQuery->orderBy(
                Role::query()->select('name')->whereColumn('roles.id', 'users.role_id'),
                $direction
            ),
            'status' => $usersQuery->orderBy('is_active', $direction),
            default => $usersQuery->orderBy($sort, $direction),
        };

        $users = $usersQuery
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('settings::users.index', [
            'users' => $users,
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
            'sort' => $sort,
            'direction' => $direction,
            'paginationPages' => $this->paginationPages($users->currentPage(), $users->lastPage()),
        ]);
    }

    public function create(Request $request): View
    {
        return view('settings::users.create', [
            'roles' => $this->activeRoles(),
            'defaultRoleId' => Role::query()->where('slug', Role::USER_SLUG)->value('id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_type' => ['required', Rule::in(['internal', 'external'])],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereIn('slug', Role::SYSTEM_SLUGS)),
            ],
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
        $user->load('role');

        return view('settings::users.edit', [
            'user' => $user,
            'roles' => Role::query()
                ->where('is_active', true)
                ->whereIn('slug', Role::SYSTEM_SLUGS)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereIn('slug', Role::SYSTEM_SLUGS)),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->employee_code === 'EMP0001') {
            $validated['role_id'] = Role::query()
                ->where('slug', Role::ADMIN_SLUG)
                ->valueOrFail('id');
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
        abort_if($user->employee_code === 'EMP0001', 422, __('settings::messages.cannot_disable_admin'));

        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->getKey())->delete();
        }

        return redirect()
            ->route('settings.users.index')
            ->with('status', $user->is_active
                ? __('settings::messages.user_enabled')
                : __('settings::messages.user_disabled'));
    }

    private function activeRoles(): Collection
    {
        return Role::query()
            ->where('is_active', true)
            ->whereIn('slug', Role::SYSTEM_SLUGS)
            ->orderBy('name')
            ->get();
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
