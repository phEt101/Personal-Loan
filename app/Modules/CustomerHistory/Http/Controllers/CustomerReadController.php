<?php

namespace App\Modules\CustomerHistory\Http\Controllers;

use App\Modules\CustomerHistory\Models\Customer;
use App\Modules\CustomerHistory\Models\CustomerAttachment;
use App\Modules\Settings\Services\CustomerAccessService;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomerReadController extends Controller
{
    private const TRANSFER_PENDING = 'pending';

    public function __construct(private readonly CustomerAccessService $customerAccess) {}

    public function districts(Request $request): JsonResponse
    {
        $provinceCode = (string) $request->query('province_code', '');

        return response()->json(
            DB::table('districts')
                ->where('ProvinceCode', $provinceCode)
                ->orderBy('DistrictDesc')
                ->get(['DistrictCode', 'DistrictDesc'])
        );
    }

    public function subDistricts(Request $request): JsonResponse
    {
        $provinceCode = (string) $request->query('province_code', '');
        $districtCode = (string) $request->query('district_code', '');

        return response()->json(
            DB::table('sub_districts')
                ->where('ProvinceCode', $provinceCode)
                ->where('DistrictCode', $districtCode)
                ->orderBy('SubDistrictDesc')
                ->get(['SubDistrictCode', 'SubDistrictDesc', 'Zipcode'])
        );
    }

    public function checkIdentityCard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identity_card_id' => ['required', 'digits:13'],
        ]);

        $query = Customer::query()
            ->where('IdentityCardTypeCode', 1)
            ->where('IdentityCardId', $validated['identity_card_id']);

        if ($request->filled('customer_no')) {
            $query->where('CustomerNo', '<>', (string) $request->query('customer_no'));
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function previewAttachment(Request $request, int $attachment)
    {
        $record = CustomerAttachment::query()->findOrFail($attachment);
        $customerQuery = Customer::query()->where('CustomerNo', $record->CustomerNo);
        $this->customerAccess->applyReadScope($customerQuery, $request->user());
        $customerQuery->firstOrFail();

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($disk->exists($record->FilePath), 404);

        return $disk->response($record->FilePath, $record->OriginalName, [], 'inline');
    }

    public function downloadAllAttachments(Request $request, string $customerNo)
    {
        $customerQuery = Customer::query()->where('CustomerNo', $customerNo);
        $this->customerAccess->applyReadScope($customerQuery, $request->user());
        $customerQuery->firstOrFail();

        $attachments = CustomerAttachment::query()
            ->where('CustomerNo', $customerNo)
            ->orderBy('id')
            ->get();
        abort_if($attachments->isEmpty(), 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $zipPath = tempnam(sys_get_temp_dir(), 'customer-attachments-');
        abort_if($zipPath === false, 500);

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            abort(500);
        }

        $fileCount = 0;
        foreach ($attachments as $index => $attachment) {
            if (! $disk->exists($attachment->FilePath)) {
                continue;
            }

            $originalName = basename(str_replace('\\', '/', $attachment->OriginalName));
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $documentName = trim((string) preg_replace(
                '/[\\/:*?"<>|\x00-\x1F]+/u',
                '-',
                $attachment->DocumentName ?: pathinfo($originalName, PATHINFO_FILENAME)
            ));
            $documentName = trim((string) preg_replace('/\s+/u', ' ', $documentName), '. ');
            $zipName = sprintf(
                '%02d-%s%s',
                $index + 1,
                $documentName ?: 'attachment-'.$attachment->id,
                $extension !== '' ? '.'.strtolower($extension) : ''
            );

            if ($zip->addFile($disk->path($attachment->FilePath), $zipName)) {
                $fileCount++;
            }
        }

        $zip->close();
        if ($fileCount === 0) {
            @unlink($zipPath);
            abort(404);
        }

        return response()
            ->download($zipPath, "customer-{$customerNo}-documents.zip")
            ->deleteFileAfterSend(true);
    }

    public function show(Request $request, string $customerNo): JsonResponse
    {
        $user = $request->user();
        $customerQuery = Customer::query()
            ->from('customers as customer')
            ->leftJoin('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
            ->leftJoin('users as transfer_user', 'transfer_user.id', '=', 'customer.HmeterTransferredBy')
            ->leftJoin('identity_card_types as identity_type', 'identity_type.IdentityCardTypeCode', '=', 'customer.IdentityCardTypeCode')
            ->leftJoin('genders as gender', 'gender.GenderId', '=', 'customer.GenderCode')
            ->leftJoin('working_conditions as working_condition', 'working_condition.WorkingConditionId', '=', 'customer.WorkingConditionId')
            ->leftJoin('banks as bank', 'bank.BankCode', '=', 'customer.BankCode')
            ->where('customer.CustomerNo', $customerNo)
            ->select([
                'customer.*',
                'identity_type.IdentityCardTypeDesc',
                'gender.GenderDesc',
                'working_condition.Description as WorkingConditionDesc',
                'bank.BankDesc',
                'creator.employee_code as CreatorEmployeeCode',
                'creator.first_name as CreatorFirstname',
                'creator.last_name as CreatorLastname',
                'transfer_user.first_name as HmeterTransferredByFirstname',
                'transfer_user.last_name as HmeterTransferredByLastname',
            ]);
        $this->customerAccess->applyReadScope($customerQuery, $user, 'customer');
        $customer = $customerQuery->firstOrFail();

        $email = $customer->emails()->orderBy('EmailId')->first();
        $remark = $customer->remarks()->orderBy('RemarkId')->first();

        return response()->json([
            'customer' => $customer,
            'addresses' => $customer->addresses()->orderBy('AddressId')->get(),
            'phones' => $customer->phones()->orderBy('PhoneId')->get(),
            'email_remark' => $email?->Remark,
            'comment' => $remark?->Comment,
            'hmeter_transfer' => [
                'status' => $customer->HmeterTransferStatus,
                'transferred_at' => $this->utcDateTime($customer->HmeterTransferredAt),
                'transferred_by' => trim(($customer->HmeterTransferredByFirstname ?? '').' '.($customer->HmeterTransferredByLastname ?? '')) ?: null,
                'attachment_purge_after' => $this->utcDateTime($customer->AttachmentPurgeAfter),
                'attachments_purged_at' => $this->utcDateTime($customer->AttachmentsPurgedAt),
                'can_confirm' => $user->user_type === 'internal' && $customer->HmeterTransferStatus === self::TRANSFER_PENDING,
                'confirm_url' => route('customer-history.hmeter-transfer', $customerNo),
                'visible' => $user->user_type === 'internal',
            ],
            'download_all_attachments_url' => route('customer-history.attachments.download-all', $customerNo),
            'attachments' => CustomerAttachment::query()
                ->from('customer_attachments as attachment')
                ->join('document_types as document_type', 'document_type.id', '=', 'attachment.DocumentTypeId')
                ->where('attachment.CustomerNo', $customerNo)
                ->orderBy('document_type.SortOrder')
                ->orderBy('attachment.id')
                ->select('attachment.*')
                ->get()
                ->map(fn ($attachment) => [
                    'id' => $attachment->id,
                    'document_name' => $attachment->DocumentName ?: $attachment->OriginalName,
                    'document_type_id' => $attachment->DocumentTypeId,
                    'original_name' => $attachment->OriginalName,
                    'mime_type' => $attachment->MimeType,
                    'file_size' => $attachment->FileSize,
                    'preview_url' => route('customer-history.attachments.preview', $attachment->id),
                ]),
        ]);
    }

    private function utcDateTime(mixed $value): ?string
    {
        return $value ? Carbon::parse((string) $value, 'UTC')->toISOString() : null;
    }
}
