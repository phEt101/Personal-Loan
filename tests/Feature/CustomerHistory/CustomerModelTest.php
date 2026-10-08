<?php

namespace Tests\Feature\CustomerHistory;

use App\Modules\CustomerHistory\Models\Customer;
use App\Modules\CustomerHistory\Models\CustomerAddress;
use App\Modules\CustomerHistory\Models\CustomerAttachment;
use App\Modules\CustomerHistory\Models\CustomerEmail;
use App\Modules\CustomerHistory\Models\CustomerPhone;
use App\Modules\CustomerHistory\Models\CustomerRemark;
use App\Modules\Settings\Models\ResponsibilityGroup;
use App\Modules\Settings\Models\User;
use App\Modules\WorkDelegation\Models\WorkDelegation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_relations_use_customer_number_and_audit_users(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Model team', 'is_active' => true]);
        $creator = User::factory()->create(['user_type' => 'external', 'responsibility_group_id' => $group->id]);
        $confirmer = User::factory()->create(['user_type' => 'internal']);
        $delegator = User::factory()->create(['user_type' => 'internal', 'responsibility_group_id' => $group->id]);
        $delegation = WorkDelegation::query()->create([
            'responsibility_group_id' => $group->id,
            'delegator_user_id' => $delegator->id,
            'delegate_user_id' => $confirmer->id,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'created_by' => $delegator->id,
        ]);
        $customer = Customer::query()->create([
            'CustomerNo' => 'MODEL-0000000001',
            'CustomerRefNo' => 'MODEL-0000000001',
            'Firstname' => 'Model',
            'Lastname' => 'Customer',
            'Email' => 'model@example.com',
            'sysInsertUserId' => $creator->id,
            'HmeterTransferStatus' => 'transferred',
            'HmeterTransferredBy' => $confirmer->id,
            'HmeterWorkDelegationId' => $delegation->id,
        ]);

        DB::table('address_types')->insert([
            'AddressTypeCode' => 1,
            'AddressTypeDesc' => 'Current address',
            'Score' => 0,
        ]);
        DB::table('phone_types')->insert([
            'PhoneTypeCode' => '01',
            'PhoneTypeDesc' => 'Mobile',
        ]);

        CustomerAddress::query()->create([
            'CustomerNo' => $customer->CustomerNo,
            'AddressId' => 1,
            'AddressLine1' => 'Bangkok',
            'ZipCode' => '10110',
        ]);
        CustomerPhone::query()->create([
            'CustomerNo' => $customer->CustomerNo,
            'PhoneId' => 1,
            'Phone' => '0890000000',
            'PhoneType' => '01',
        ]);
        CustomerEmail::query()->create([
            'CustomerNo' => $customer->CustomerNo,
            'EmailId' => 1,
            'Email' => 'model@example.com',
        ]);
        CustomerRemark::query()->create([
            'CustomerNo' => $customer->CustomerNo,
            'RemarkId' => 1,
            'Comment' => 'Model relation',
        ]);

        $customer->load(['creator', 'transferConfirmer', 'workDelegation', 'addresses', 'phones', 'emails', 'remarks']);

        $this->assertTrue($customer->creator->is($creator));
        $this->assertTrue($customer->transferConfirmer->is($confirmer));
        $this->assertTrue($customer->workDelegation->is($delegation));
        $this->assertInstanceOf(CustomerAddress::class, $customer->addresses->first());
        $this->assertInstanceOf(CustomerPhone::class, $customer->phones->first());
        $this->assertInstanceOf(CustomerEmail::class, $customer->emails->first());
        $this->assertInstanceOf(CustomerRemark::class, $customer->remarks->first());
        $this->assertSame($customer->CustomerNo, $customer->getRouteKey());
        $this->assertCount(0, $customer->attachments);
        $this->assertInstanceOf(CustomerAttachment::class, $customer->attachments()->getModel());
    }
}
