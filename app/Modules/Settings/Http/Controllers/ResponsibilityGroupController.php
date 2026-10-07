<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Models\ResponsibilityGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResponsibilityGroupController extends Controller
{
    public function index(): View
    {
        return view('settings::responsibility-groups.index', [
            'groups' => ResponsibilityGroup::query()
                ->with(['internalUsers.role', 'externalUsers.role'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new ResponsibilityGroup(['is_active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGroup($request);

        DB::transaction(function () use ($validated): void {
            $group = ResponsibilityGroup::query()->create([
                'name' => $validated['name'],
                'is_active' => $validated['is_active'],
            ]);
            $this->syncMembers($group, $validated);
        });

        return redirect()->route('settings.responsibility-groups.index')
            ->with('status', __('settings::messages.responsibility_group_created'));
    }

    public function edit(ResponsibilityGroup $responsibilityGroup): View
    {
        $responsibilityGroup->load(['internalUsers', 'externalUsers']);

        return $this->formView($responsibilityGroup);
    }

    public function update(Request $request, ResponsibilityGroup $responsibilityGroup): RedirectResponse
    {
        $validated = $this->validateGroup($request, $responsibilityGroup);

        DB::transaction(function () use ($responsibilityGroup, $validated): void {
            $responsibilityGroup->update([
                'name' => $validated['name'],
                'is_active' => $validated['is_active'],
            ]);
            $this->syncMembers($responsibilityGroup, $validated);
        });

        return redirect()->route('settings.responsibility-groups.index')
            ->with('status', __('settings::messages.responsibility_group_updated'));
    }

    private function formView(ResponsibilityGroup $group): View
    {
        return view('settings::responsibility-groups.form', [
            'group' => $group,
            'internalUsers' => User::query()->with('role')->where('user_type', 'internal')->orderBy('employee_code')->get(),
            'externalUsers' => User::query()
                ->with('role')
                ->where('user_type', 'external')
                ->where('role_id', $this->standardUserRoleId())
                ->orderBy('employee_code')
                ->get(),
            'externalAssignments' => User::query()
                ->where('user_type', 'external')
                ->whereNotNull('responsibility_group_id')
                ->pluck('responsibility_group_id', 'id'),
        ]);
    }

    private function validateGroup(Request $request, ?ResponsibilityGroup $group = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('responsibility_groups')->ignore($group?->getKey())],
            'is_active' => ['required', 'boolean'],
            'internal_user_ids' => ['nullable', 'array'],
            'internal_user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('user_type', 'internal')),
            ],
            'external_user_ids' => ['nullable', 'array'],
            'external_user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('user_type', 'external')
                    ->where('role_id', $this->standardUserRoleId())),
            ],
        ]);
    }

    private function standardUserRoleId(): int
    {
        return (int) Role::query()->where('slug', Role::USER_SLUG)->valueOrFail('id');
    }

    private function syncMembers(ResponsibilityGroup $group, array $validated): void
    {
        $this->syncUserTypeMembers($group, 'internal', $validated['internal_user_ids'] ?? []);
        $this->syncUserTypeMembers($group, 'external', $validated['external_user_ids'] ?? []);
    }

    private function syncUserTypeMembers(ResponsibilityGroup $group, string $userType, array $selectedIds): void
    {
        User::query()
            ->where('user_type', $userType)
            ->where('responsibility_group_id', $group->getKey())
            ->whereNotIn('id', $selectedIds ?: [0])
            ->update(['responsibility_group_id' => null]);

        User::query()
            ->where('user_type', $userType)
            ->whereIn('id', $selectedIds ?: [0])
            ->update(['responsibility_group_id' => $group->getKey()]);
    }
}
