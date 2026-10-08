<?php

namespace App\Modules\CustomerHistory\Console\Commands;

use App\Modules\CustomerHistory\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeTransferredAttachments extends Command
{
    protected $signature = 'attachments:purge-transferred';

    protected $description = 'Purge customer attachments 30 days after transfer to H Meter';

    public function handle(): int
    {
        $purgedCustomers = 0;
        $failedCustomers = 0;

        Customer::query()
            ->where('HmeterTransferStatus', 'transferred')
            ->whereNull('AttachmentsPurgedAt')
            ->whereNotNull('AttachmentPurgeAfter')
            ->where('AttachmentPurgeAfter', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($customers) use (&$purgedCustomers, &$failedCustomers): void {
                foreach ($customers as $customer) {
                    $attachments = $customer->attachments()->get(['id', 'FilePath']);
                    $allDeleted = true;

                    foreach ($attachments as $attachment) {
                        if (Storage::disk('local')->exists($attachment->FilePath)
                            && ! Storage::disk('local')->delete($attachment->FilePath)) {
                            $allDeleted = false;
                        }
                    }

                    if (! $allDeleted) {
                        $failedCustomers++;

                        continue;
                    }

                    DB::transaction(function () use ($customer): void {
                        $customer->attachments()->delete();
                        $customer->update(['AttachmentsPurgedAt' => now()]);
                    });
                    $purgedCustomers++;
                }
            });

        $this->info("Purged attachments for {$purgedCustomers} customer(s). Failed: {$failedCustomers}.");

        return $failedCustomers === 0 ? self::SUCCESS : self::FAILURE;
    }
}
