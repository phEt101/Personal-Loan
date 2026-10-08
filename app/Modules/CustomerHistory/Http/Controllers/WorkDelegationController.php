<?php

namespace App\Modules\CustomerHistory\Http\Controllers;

use App\Models\ResponsibilityGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkDelegation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkDelegationController extends Controller
{
    public function index(Request $request): View
    {
        $query = WorkDelegation::query()
            ->with(['group', 'delegator', 'delegate'])
            ->orderByDesc('starts_at');

        if (! $request->user()->isAdmin()) {
            $query->where(function ($query) use ($request) {
                $query->where('delegator_user_id', $request->user()->id)
                    ->orWhere('delegate_user_id', $request->user()->id);
            });
        }

        return view('customerhistory::work-delegations.index', [
            'delegations' => $query->paginate(10),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->formView($request, new WorkDelegation());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $this->ensureNoOverlap($validated);

        WorkDelegation::query()->create($validated + ['created_by' => $request->user()->id]);

        return redirect()->route('work-delegations.index')
            ->with('status', __('messages.delegation.created'));
    }

    public function edit(Request $request, WorkDelegation $workDelegation): View
    {
        $this->authorizeManagement($request, $workDelegation);
        abort_if($workDelegation->cancelled_at !== null, 422, __('messages.delegation.cancelled_edit_error'));

        return $this->formView($request, $workDelegation);
    }

    public function update(Request $request, WorkDelegation $workDelegation): RedirectResponse
    {
        $this->authorizeManagement($request, $workDelegation);
        abort_if($workDelegation->cancelled_at !== null, 422, __('messages.delegation.cancelled_edit_error'));

        $validated = $this->validatedData($request, $workDelegation);
        $this->ensureNoOverlap($validated, $workDelegation);
        $workDelegation->update($validated);

        return redirect()->route('work-delegations.index')
            ->with('status', __('messages.delegation.updated'));
    }

    public function cancel(Request $request, WorkDelegation $workDelegation): RedirectResponse
    {
        $this->authorizeManagement($request, $workDelegation);

        if ($workDelegation->cancelled_at === null) {
            $workDelegation->update([
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('work-delegations.index')
            ->with('status', __('messages.delegation.cancelled'));
    }

    private function formView(Request $request, WorkDelegation $delegation): View
    {
        $user = $request->user();
        $groups = ResponsibilityGroup::query()->where('is_active', true);

        if (! $user->isAdmin()) {
            $groups->whereKey($user->responsibility_group_id);
        }

        return view('customerhistory::work-delegations.form', [
            'delegation' => $delegation,
            'groups' => $groups->orderBy('name')->get(),
            'delegators' => User::query()
                ->where('user_type', 'internal')
                ->where('is_active', true)
                ->whereNotNull('responsibility_group_id')
                ->orderBy('employee_code')
                ->get(),
            'delegates' => User::query()
                ->where('user_type', 'internal')
                ->where('is_active', true)
                ->where('role_id', '<>', $this->adminRoleId())
                ->when(! $user->isAdmin(), fn ($query) => $query->where('id', '<>', $user->id))
                ->orderBy('employee_code')
                ->get(),
            'isAdmin' => $user->isAdmin(),
            'currentUser' => $user,
        ]);
    }

    private function validatedData(Request $request, ?WorkDelegation $delegation = null): array
    {
        $user = $request->user();
        $validated = $request->validate([
            'responsibility_group_id' => ['required', 'integer', Rule::exists('responsibility_groups', 'id')->where('is_active', true)],
            'delegator_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('user_type', 'internal')->where('is_active', true)],
            'delegate_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('user_type', 'internal')
                    ->where('is_active', true)
                    ->where('role_id', '<>', $this->adminRoleId())),
            ],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $delegatorId = $user->isAdmin()
            ? (int) ($validated['delegator_user_id'] ?? 0)
            : (int) $user->id;
        $groupId = (int) $validated['responsibility_group_id'];

        if ($delegatorId === 0 || $delegatorId === (int) $validated['delegate_user_id']) {
            throw ValidationException::withMessages([
                'delegate_user_id' => __('messages.delegation.delegate_must_differ'),
            ]);
        }

        $delegatorBelongsToGroup = User::query()
            ->whereKey($delegatorId)
            ->where('user_type', 'internal')
            ->where('responsibility_group_id', $groupId)
            ->exists();

        if (! $delegatorBelongsToGroup) {
            throw ValidationException::withMessages([
                'responsibility_group_id' => __('messages.delegation.invalid_group'),
            ]);
        }

        return [
            'responsibility_group_id' => $groupId,
            'delegator_user_id' => $delegatorId,
            'delegate_user_id' => (int) $validated['delegate_user_id'],
            'starts_at' => Carbon::parse($validated['starts_at'], config('app.local_timezone'))->utc(),
            'ends_at' => Carbon::parse($validated['ends_at'], config('app.local_timezone'))->utc(),
            'note' => $validated['note'] ?? null,
        ];
    }

    private function ensureNoOverlap(array $data, ?WorkDelegation $delegation = null): void
    {
        $overlaps = WorkDelegation::query()
            ->whereNull('cancelled_at')
            ->where('responsibility_group_id', $data['responsibility_group_id'])
            ->where('delegate_user_id', $data['delegate_user_id'])
            ->where('starts_at', '<', $data['ends_at'])
            ->where('ends_at', '>', $data['starts_at'])
            ->when($delegation, fn ($query) => $query->where('id', '<>', $delegation->id))
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'starts_at' => __('messages.delegation.overlap'),
            ]);
        }
    }

    private function authorizeManagement(Request $request, WorkDelegation $delegation): void
    {
        abort_unless(
            $request->user()->isAdmin() || $delegation->delegator_user_id === $request->user()->id,
            403
        );
    }

    private function adminRoleId(): int
    {
        return (int) Role::query()->where('slug', Role::ADMIN_SLUG)->valueOrFail('id');
    }
}
