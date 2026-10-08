<?php

namespace App\Modules\CustomerHistory\Http\Controllers;

use App\Modules\CustomerHistory\Models\Customer;
use App\Modules\Settings\Models\User;
use App\Modules\Settings\Services\CustomerAccessService;
use App\Modules\WorkDelegation\Models\WorkDelegation;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomerHmeterTransferController extends Controller
{
    private const TRANSFER_PENDING = 'pending';

    private const TRANSFERRED = 'transferred';

    private const ATTACHMENT_RETENTION_DAYS = 30;

    public function __construct(private readonly CustomerAccessService $customerAccess) {}

    public function confirm(Request $request, string $customerNo): JsonResponse
    {
        abort_unless($request->user()->user_type === 'internal', 403);

        $now = now();
        $customer = Customer::query()
            ->from('customers as customer')
            ->leftJoin('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
            ->where('customer.CustomerNo', $customerNo)
            ->where('customer.HmeterTransferStatus', self::TRANSFER_PENDING)
            ->select('customer.CustomerNo', 'creator.responsibility_group_id as CreatorGroupId');
        $this->customerAccess->applyReadScope($customer, $request->user(), 'customer');
        $customer = $customer->first();

        if (! $customer) {
            $accessibleCustomer = Customer::query()->where('CustomerNo', $customerNo);
            $this->customerAccess->applyReadScope($accessibleCustomer, $request->user());
            abort_unless($accessibleCustomer->exists(), 404);

            return response()->json([
                'message' => __('customerhistory::messages.transfer.already_transferred'),
            ], 409);
        }

        $delegationId = $this->activeDelegationId(
            $request->user(),
            $customer->CreatorGroupId,
            $now
        );
        $customerQuery = Customer::query()
            ->where('CustomerNo', $customerNo)
            ->where('HmeterTransferStatus', self::TRANSFER_PENDING);
        $this->customerAccess->applyReadScope($customerQuery, $request->user());

        $updated = $customerQuery->update([
            'HmeterTransferStatus' => self::TRANSFERRED,
            'HmeterTransferredAt' => $now,
            'HmeterTransferredBy' => $request->user()->getAuthIdentifier(),
            'HmeterWorkDelegationId' => $delegationId,
            'AttachmentPurgeAfter' => $now->copy()->addDays(self::ATTACHMENT_RETENTION_DAYS),
            'AttachmentsPurgedAt' => null,
        ]);

        if ($updated === 0) {
            $accessibleCustomer = Customer::query()->where('CustomerNo', $customerNo);
            $this->customerAccess->applyReadScope($accessibleCustomer, $request->user());
            abort_unless($accessibleCustomer->exists(), 404);

            return response()->json([
                'message' => __('customerhistory::messages.transfer.already_transferred'),
            ], 409);
        }

        return response()->json([
            'message' => __('customerhistory::messages.transfer.confirmed'),
            'hmeter_transfer' => [
                'status' => self::TRANSFERRED,
                'transferred_at' => $now->toISOString(),
                'transferred_by' => $request->user()->full_name,
                'attachment_purge_after' => $now->copy()->addDays(self::ATTACHMENT_RETENTION_DAYS)->toISOString(),
                'attachments_purged_at' => null,
                'can_confirm' => false,
                'confirm_url' => route('customer-history.hmeter-transfer', $customerNo),
                'visible' => true,
            ],
        ]);
    }

    private function activeDelegationId(User $user, ?int $groupId, CarbonInterface $at): ?int
    {
        if ($groupId === null || $user->isAdmin() || (int) $user->responsibility_group_id === $groupId) {
            return null;
        }

        $delegationId = WorkDelegation::query()
            ->where('responsibility_group_id', $groupId)
            ->where('delegate_user_id', $user->getAuthIdentifier())
            ->whereNull('cancelled_at')
            ->where('starts_at', '<=', $at)
            ->where('ends_at', '>=', $at)
            ->value('id');

        return $delegationId === null ? null : (int) $delegationId;
    }
}
