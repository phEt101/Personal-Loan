<?php

namespace Tests\Feature\CustomerHistory;

use App\Modules\Settings\Models\User;
use Carbon\Carbon;
use Database\Seeders\MasterLookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerHistoryTest extends TestCase
{
    use RefreshDatabase;

    private const CUSTOMER_HISTORY_PATH = '/customer-history';

    private const CUSTOMER_FIRST_NAME = 'สมชาย';

    private const NATIONAL_ID_DOCUMENT_NAME = 'สำเนาบัตรประชาชน';

    private const PDF_MIME_TYPE = 'application/pdf';

    private const SEPTEMBER_TENTH_AT_TEN = '2026-09-10 10:00:00';

    private const SEPTEMBER_ELEVENTH_AT_TEN = '2026-09-11 10:00:00';

    private const OCTOBER_FIRST_AT_TEN = '2026-10-01 10:00:00';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterLookupSeeder::class);
    }

    public function test_authenticated_user_can_create_view_and_update_customer(): void
    {
        $user = User::factory()->create();

        $create = $this->actingAs($user)
            ->postJson(self::CUSTOMER_HISTORY_PATH, $this->customerPayload())
            ->assertCreated()
            ->assertJsonStructure(['message', 'customer_no']);

        $customerNo = $create->json('customer_no');
        $this->assertMatchesRegularExpression('/^00CU\d{12}$/', $customerNo);
        $this->assertDatabaseHas('customers', [
            'CustomerNo' => $customerNo,
            'CustomerRefNo' => $customerNo,
            'Firstname' => self::CUSTOMER_FIRST_NAME,
            'sysInsertUserId' => $user->id,
        ]);
        $this->assertDatabaseHas('customer_addresses', ['CustomerNo' => $customerNo, 'AddressId' => 1]);
        $this->assertDatabaseHas('customer_phones', ['CustomerNo' => $customerNo, 'Phone' => '0812345678']);

        $this->getJson("/customer-history/{$customerNo}")
            ->assertOk()
            ->assertJsonPath('customer.Firstname', self::CUSTOMER_FIRST_NAME)
            ->assertJsonCount(1, 'addresses')
            ->assertJsonCount(1, 'phones');

        $payload = $this->customerPayload([
            'Firstname' => 'สมหญิง',
            'Email' => 'updated@example.com',
            'Comment' => 'updated remark',
        ]);

        $this->putJson("/customer-history/{$customerNo}", $payload)
            ->assertOk()
            ->assertJsonPath('customer_no', $customerNo);

        $this->assertDatabaseHas('customers', [
            'CustomerNo' => $customerNo,
            'Firstname' => 'สมหญิง',
            'Email' => 'updated@example.com',
            'sysUpdateUserId' => $user->id,
        ]);
        $this->assertDatabaseHas('customer_remarks', ['CustomerNo' => $customerNo, 'Comment' => 'updated remark']);
    }

    public function test_customer_form_config_is_rendered_as_valid_json(): void
    {
        $user = User::factory()->create();
        $html = $this->actingAs($user)->get(self::CUSTOMER_HISTORY_PATH)->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<script type="application\/json" id="customerHistoryFormConfig">(.*?)<\/script>/s',
            $html
        );
        preg_match('/<script type="application\/json" id="customerHistoryFormConfig">(.*?)<\/script>/s', $html, $matches);

        $config = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('attachmentTypes', $config);
        $this->assertSame(
            __('customerhistory::messages.form.attachments.purged_empty'),
            $config['messages']['attachmentsPurgedEmpty']
        );
    }

    public function test_customer_number_is_generated_uniquely_by_the_system(): void
    {
        $user = User::factory()->create();

        $firstCustomerNo = $this->actingAs($user)
            ->postJson(self::CUSTOMER_HISTORY_PATH, $this->customerPayload())
            ->assertCreated()
            ->json('customer_no');

        $secondCustomerNo = $this->postJson(self::CUSTOMER_HISTORY_PATH, $this->customerPayload([
            'IdentityCardId' => 'P87654321',
            'Email' => 'second@example.com',
        ]))
            ->assertCreated()
            ->json('customer_no');

        $prefix = '00CU'.now(config('app.local_timezone'))->format('ymd');
        $this->assertSame($prefix.'000001', $firstCustomerNo);
        $this->assertSame($prefix.'000002', $secondCustomerNo);
        $this->assertNotSame($firstCustomerNo, $secondCustomerNo);
    }

    public function test_customer_number_uses_the_local_business_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-07 18:30:00', 'UTC'));

        try {
            $user = User::factory()->create();
            $customerNo = $this->actingAs($user)
                ->postJson(self::CUSTOMER_HISTORY_PATH, $this->customerPayload())
                ->assertCreated()
                ->json('customer_no');

            $this->assertSame('00CU261008000001', $customerNo);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_customer_attachments_can_be_uploaded_downloaded_and_removed(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['user_type' => 'external']);
        $otherUser = User::factory()->create(['user_type' => 'external']);
        $nationalIdType = DB::table('document_types')->where('DocumentTypeNameEn', 'National ID copy')->value('id');
        $salarySlipType = DB::table('document_types')->where('DocumentTypeNameEn', 'Salary slip')->value('id');
        $payload = $this->customerPayload([
            'NewAttachments' => [
                [
                    'DocumentName' => self::NATIONAL_ID_DOCUMENT_NAME,
                    'DocumentTypeId' => $nationalIdType,
                    'File' => UploadedFile::fake()->create('national-id.pdf', 100, self::PDF_MIME_TYPE),
                ],
                [
                    'DocumentName' => 'สลิปเดือนล่าสุด',
                    'DocumentTypeId' => $salarySlipType,
                    'File' => UploadedFile::fake()->image('salary-slip.jpg'),
                ],
            ],
        ]);

        $customerNo = $this->actingAs($owner)
            ->withHeader('Accept', 'application/json')
            ->post(self::CUSTOMER_HISTORY_PATH, $payload)
            ->assertCreated()
            ->json('customer_no');

        $attachments = DB::table('customer_attachments')->where('CustomerNo', $customerNo)->get();
        $this->assertCount(2, $attachments);
        $this->assertSame(self::NATIONAL_ID_DOCUMENT_NAME, $attachments->firstWhere('DocumentTypeId', $nationalIdType)->DocumentName);
        foreach ($attachments->pluck('FilePath') as $path) {
            $this->assertTrue(Storage::disk('local')->exists($path));
        }

        $detail = $this->getJson("/customer-history/{$customerNo}")
            ->assertOk()
            ->assertJsonCount(2, 'attachments');
        $previewUrl = $detail->json('attachments.0.preview_url');
        $downloadAllUrl = $detail->json('download_all_attachments_url');
        $attachmentId = $detail->json('attachments.0.id');
        $this->assertSame(self::PDF_MIME_TYPE, $detail->json('attachments.0.mime_type'));

        $this->get($previewUrl)
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename=national-id.pdf');
        $download = $this->get($downloadAllUrl)
            ->assertOk()
            ->assertHeader('content-disposition', "attachment; filename=customer-{$customerNo}-documents.zip");
        $zip = new \ZipArchive;
        $zipPath = tempnam(sys_get_temp_dir(), 'customer-documents-');
        file_put_contents($zipPath, $download->streamedContent());
        $this->assertTrue($zip->open($zipPath));
        $this->assertSame(2, $zip->numFiles);
        $this->assertSame('01-'.self::NATIONAL_ID_DOCUMENT_NAME.'.pdf', $zip->getNameIndex(0));
        $this->assertSame('02-สลิปเดือนล่าสุด.jpg', $zip->getNameIndex(1));
        $zip->close();
        unlink($zipPath);
        $this->actingAs($otherUser)->get($previewUrl)->assertNotFound();
        $this->actingAs($otherUser)->get($downloadAllUrl)->assertNotFound();

        $filePath = DB::table('customer_attachments')->where('id', $attachmentId)->value('FilePath');
        $this->actingAs($owner)
            ->withHeader('Accept', 'application/json')
            ->post("/customer-history/{$customerNo}", array_merge($this->customerPayload(), [
                '_method' => 'PUT',
                'RemoveAttachmentIds' => [$attachmentId],
            ]))
            ->assertOk();

        $this->assertDatabaseMissing('customer_attachments', ['id' => $attachmentId]);
        $this->assertFalse(Storage::disk('local')->exists($filePath));
    }

    public function test_download_all_cannot_be_bypassed_when_customer_has_no_available_files(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['user_type' => 'external']);
        $customerNo = 'MOCK000000000104';
        $this->insertCustomer($customerNo, 'NoFiles', $owner, '2026-10-06 10:00:00');
        $downloadUrl = route('customer-history.attachments.download-all', $customerNo);

        $this->actingAs($owner)->get($downloadUrl)->assertNotFound();

        DB::table('customer_attachments')->insert([
            'CustomerNo' => $customerNo,
            'DocumentName' => 'ไฟล์ที่ไม่มีอยู่จริง',
            'DocumentTypeId' => DB::table('document_types')->value('id'),
            'OriginalName' => 'missing.pdf',
            'FilePath' => "customer-attachments/{$customerNo}/missing.pdf",
            'MimeType' => self::PDF_MIME_TYPE,
            'FileSize' => 100,
            'UploadedBy' => $owner->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get($downloadUrl)->assertNotFound();
    }

    public function test_customer_creation_rejects_invalid_or_incomplete_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(self::CUSTOMER_HISTORY_PATH, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'TitleCode', 'Firstname', 'Lastname', 'GenderCode', 'BirthDate',
                'IdentityCardTypeCode', 'IdentityCardId', 'MaritalStatusCode',
                'WorkingConditionId', 'Addresses', 'Phones', 'Email',
            ]);

        $payload = $this->customerPayload(['IdentityCardAddressId' => 99]);
        $this->postJson(self::CUSTOMER_HISTORY_PATH, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('IdentityCardAddressId');
    }

    public function test_internal_user_can_search_filter_by_creator_and_paginate_customers(): void
    {
        $internal = User::factory()->create(['user_type' => 'internal']);
        $external = User::factory()->create(['user_type' => 'external']);
        $groupId = DB::table('responsibility_groups')->insertGetId([
            'name' => 'Search Team',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        User::query()->whereKey([$internal->id, $external->id])
            ->update(['responsibility_group_id' => $groupId]);
        $internal->refresh();
        $this->insertCustomer('MOCK000000000001', 'Needle', $external, self::SEPTEMBER_TENTH_AT_TEN);

        foreach (range(2, 7) as $number) {
            $this->insertCustomer(
                'MOCK'.str_pad((string) $number, 12, '0', STR_PAD_LEFT),
                "Customer{$number}",
                $internal,
                "2026-09-{$number} 10:00:00"
            );
        }

        $this->actingAs($internal)
            ->get('/customer-history?q=Needle&creator_type=external&creator_id='.$external->id.'&date_from=2026-09-01&date_to=2026-09-30')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 1
                && $customers->first()->Firstname === 'Needle')
            ->assertViewHas('creatorOptions', fn ($creators) => $creators->pluck('id')->contains($external->id));

        $this->get('/customer-history?per_page=5&page=2')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->perPage() === 5
                && $customers->currentPage() === 2
                && $customers->total() === 7);
    }

    public function test_customer_list_can_sort_columns_in_both_directions(): void
    {
        $internal = User::factory()->create(['user_type' => 'internal']);
        $this->insertCustomer('MOCK000000000003', 'Charlie', $internal, '2026-09-03 10:00:00');
        $this->insertCustomer('MOCK000000000001', 'Alpha', $internal, '2026-09-01 10:00:00');
        $this->insertCustomer('MOCK000000000002', 'Bravo', $internal, '2026-09-02 10:00:00');

        $this->actingAs($internal)
            ->get(self::CUSTOMER_HISTORY_PATH)
            ->assertOk()
            ->assertSee('aria-sort="descending"', false)
            ->assertViewHas('customers', fn ($customers) => $customers->pluck('CustomerNo')->all() === [
                'MOCK000000000003',
                'MOCK000000000002',
                'MOCK000000000001',
            ]);

        $this->get('/customer-history?sort=customer_no&direction=asc')
            ->assertOk()
            ->assertSee('aria-sort="ascending"', false)
            ->assertViewHas('customers', fn ($customers) => $customers->pluck('CustomerNo')->all() === [
                'MOCK000000000001',
                'MOCK000000000002',
                'MOCK000000000003',
            ]);

        $this->get('/customer-history?sort=customer_no&direction=desc')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->pluck('CustomerNo')->all() === [
                'MOCK000000000003',
                'MOCK000000000002',
                'MOCK000000000001',
            ]);
    }

    public function test_customer_date_filter_uses_local_day_boundaries(): void
    {
        $internal = User::factory()->create(['user_type' => 'internal']);
        $this->insertCustomer('MOCK-LOCAL-DATE-1', 'ThaiMorning', $internal, '2026-10-07 18:30:00');
        $this->insertCustomer('MOCK-LOCAL-DATE-2', 'PreviousDay', $internal, '2026-10-07 16:30:00');

        $this->actingAs($internal)
            ->get('/customer-history?date_from=2026-10-08&date_to=2026-10-08')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->pluck('CustomerNo')->all() === [
                'MOCK-LOCAL-DATE-1',
            ]);
    }

    public function test_external_user_only_sees_and_can_open_own_customers(): void
    {
        $external = User::factory()->create(['user_type' => 'external']);
        $other = User::factory()->create(['user_type' => 'external']);
        $this->insertCustomer('MOCK000000000001', 'Owned', $external, self::SEPTEMBER_TENTH_AT_TEN);
        $this->insertCustomer('MOCK000000000002', 'Hidden', $other, self::SEPTEMBER_ELEVENTH_AT_TEN);

        $this->actingAs($external)
            ->get(self::CUSTOMER_HISTORY_PATH)
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 1
                && $customers->first()->Firstname === 'Owned');

        $this->getJson('/customer-history/MOCK000000000001')->assertOk();
        $this->getJson('/customer-history/MOCK000000000002')->assertNotFound();
    }

    public function test_internal_user_only_accesses_external_customers_in_assigned_groups(): void
    {
        $internal = User::factory()->create(['user_type' => 'internal']);
        $assignedExternal = User::factory()->create(['user_type' => 'external']);
        $otherExternal = User::factory()->create(['user_type' => 'external']);
        $otherInternal = User::factory()->create(['user_type' => 'internal']);
        $groupId = DB::table('responsibility_groups')->insertGetId([
            'name' => 'Team A',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        User::query()->whereKey([$internal->id, $assignedExternal->id])
            ->update(['responsibility_group_id' => $groupId]);
        $internal->refresh();

        $this->insertCustomer('MOCK000000000301', 'Assigned', $assignedExternal, self::SEPTEMBER_TENTH_AT_TEN);
        $this->insertCustomer('MOCK000000000302', 'Hidden', $otherExternal, self::SEPTEMBER_ELEVENTH_AT_TEN);
        $this->insertCustomer('MOCK000000000303', 'Internal', $otherInternal, '2026-09-12 10:00:00');

        $this->actingAs($internal)
            ->get(self::CUSTOMER_HISTORY_PATH)
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->pluck('CustomerNo')->sort()->values()->all() === [
                'MOCK000000000301',
                'MOCK000000000303',
            ]);

        $this->getJson('/customer-history/MOCK000000000301')->assertOk();
        $this->getJson('/customer-history/MOCK000000000302')->assertNotFound();
        $this->getJson('/customer-history/MOCK000000000303')->assertOk();
        $this->patchJson('/customer-history/MOCK000000000302/hmeter-transfer')->assertNotFound();
    }

    public function test_external_manager_sees_all_external_customers_and_creator_but_not_internal_customers(): void
    {
        $manager = User::factory()->create([
            'user_type' => 'external',
            'role_id' => DB::table('roles')->where('slug', 'manager')->value('id'),
        ]);
        $external = User::factory()->create(['user_type' => 'external']);
        $internal = User::factory()->create(['user_type' => 'internal']);
        $this->insertCustomer('MOCK000000000201', 'ManagerOwned', $manager, self::SEPTEMBER_TENTH_AT_TEN);
        $this->insertCustomer('MOCK000000000202', 'ExternalOwned', $external, self::SEPTEMBER_ELEVENTH_AT_TEN);
        $this->insertCustomer('MOCK000000000203', 'InternalOwned', $internal, '2026-09-12 10:00:00');

        $this->actingAs($manager)
            ->get(self::CUSTOMER_HISTORY_PATH)
            ->assertOk()
            ->assertDontSee('id="openCustomerHistoryForm"', false)
            ->assertViewHas('canViewCreator', true)
            ->assertViewHas('creatorOptions', fn ($creators) => $creators->pluck('id')->sort()->values()->all() === collect([$manager->id, $external->id])->sort()->values()->all())
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 2
                && $customers->pluck('Firstname')->sort()->values()->all() === ['ExternalOwned', 'ManagerOwned']
                && $customers->every(fn ($customer) => filled($customer->CreatorEmployeeCode)));

        $this->get('/customer-history?creator_id='.$external->id)
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 1
                && $customers->first()->Firstname === 'ExternalOwned');

        $this->getJson('/customer-history/MOCK000000000202')->assertOk();
        $this->getJson('/customer-history/MOCK000000000203')->assertNotFound();
        $this->putJson('/customer-history/MOCK000000000202', $this->customerPayload([
            'Firstname' => 'ManagerUpdated',
            'IdentityCardId' => 'PMANAGER202',
            'Email' => 'manager-updated@example.com',
        ]))->assertForbidden();
        $this->assertDatabaseHas('customers', [
            'CustomerNo' => 'MOCK000000000202',
            'Firstname' => 'ExternalOwned',
        ]);
        $this->postJson(self::CUSTOMER_HISTORY_PATH, $this->customerPayload([
            'IdentityCardId' => 'PMANAGERNEW',
            'Email' => 'manager-new@example.com',
        ]))->assertForbidden();
        $this->assertDatabaseMissing('customers', ['Email' => 'manager-new@example.com']);
    }

    public function test_internal_user_can_confirm_hmeter_transfer_and_customer_becomes_read_only(): void
    {
        Carbon::setTestNow('2026-10-06 10:30:00');
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'first_name' => 'ทัศนีย์',
            'last_name' => 'จุฑารัตน์จรัส',
        ]);
        $customerNo = 'MOCK000000000101';
        $this->insertCustomer($customerNo, 'Transfer', $internal, self::OCTOBER_FIRST_AT_TEN);

        $this->actingAs($internal)
            ->patchJson("/customer-history/{$customerNo}/hmeter-transfer")
            ->assertOk()
            ->assertJsonPath('hmeter_transfer.status', 'transferred')
            ->assertJsonPath('hmeter_transfer.transferred_by', 'ทัศนีย์ จุฑารัตน์จรัส')
            ->assertJsonPath('hmeter_transfer.attachment_purge_after', '2026-11-05T10:30:00.000000Z');

        $this->assertDatabaseHas('customers', [
            'CustomerNo' => $customerNo,
            'HmeterTransferStatus' => 'transferred',
            'HmeterTransferredBy' => $internal->id,
            'HmeterTransferredAt' => '2026-10-06 10:30:00',
            'AttachmentPurgeAfter' => '2026-11-05 10:30:00',
        ]);

        $this->getJson("/customer-history/{$customerNo}")
            ->assertOk()
            ->assertJsonPath('hmeter_transfer.transferred_at', '2026-10-06T10:30:00.000000Z')
            ->assertJsonPath('hmeter_transfer.attachment_purge_after', '2026-11-05T10:30:00.000000Z');

        $this->patchJson("/customer-history/{$customerNo}/hmeter-transfer")->assertConflict();
        $this->putJson("/customer-history/{$customerNo}", [])->assertConflict();
        Carbon::setTestNow();
    }

    public function test_external_user_cannot_confirm_hmeter_transfer(): void
    {
        $external = User::factory()->create(['user_type' => 'external']);
        $customerNo = 'MOCK000000000102';
        $this->insertCustomer($customerNo, 'External', $external, self::OCTOBER_FIRST_AT_TEN);

        $this->actingAs($external)
            ->patchJson("/customer-history/{$customerNo}/hmeter-transfer")
            ->assertForbidden();

        $this->assertDatabaseHas('customers', [
            'CustomerNo' => $customerNo,
            'HmeterTransferStatus' => 'pending',
        ]);
    }

    public function test_due_transferred_customer_attachments_are_purged(): void
    {
        Storage::fake('local');
        Carbon::setTestNow('2026-11-06 02:00:00');
        $internal = User::factory()->create(['user_type' => 'internal']);
        $customerNo = 'MOCK000000000103';
        $this->insertCustomer($customerNo, 'Purge', $internal, self::OCTOBER_FIRST_AT_TEN);
        $documentTypeId = DB::table('document_types')->value('id');
        $filePath = "customer-attachments/{$customerNo}/identity.pdf";
        Storage::disk('local')->put($filePath, 'test document');
        DB::table('customer_attachments')->insert([
            'CustomerNo' => $customerNo,
            'DocumentName' => self::NATIONAL_ID_DOCUMENT_NAME,
            'DocumentTypeId' => $documentTypeId,
            'OriginalName' => 'identity.pdf',
            'FilePath' => $filePath,
            'MimeType' => self::PDF_MIME_TYPE,
            'FileSize' => 13,
            'UploadedBy' => $internal->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('customers')->where('CustomerNo', $customerNo)->update([
            'HmeterTransferStatus' => 'transferred',
            'HmeterTransferredAt' => '2026-10-06 01:00:00',
            'HmeterTransferredBy' => $internal->id,
            'AttachmentPurgeAfter' => '2026-11-05 01:00:00',
        ]);

        $this->artisan('attachments:purge-transferred')->assertSuccessful();

        $this->assertFalse(Storage::disk('local')->exists($filePath));
        $this->assertDatabaseMissing('customer_attachments', ['CustomerNo' => $customerNo]);
        $this->assertNotNull(DB::table('customers')->where('CustomerNo', $customerNo)->value('AttachmentsPurgedAt'));
        Carbon::setTestNow();
    }

    private function customerPayload(array $overrides = []): array
    {
        $title = DB::table('titles')->where('Active', true)->first();
        $identityType = DB::table('identity_card_types')->where('IdentityCardTypeCode', '<>', 1)->first()
            ?? DB::table('identity_card_types')->first();
        $marital = DB::table('marital_statuses')->first();
        $working = DB::table('working_conditions')->where('IsRequireOccupation', false)->first();
        $addressType = DB::table('address_types')->first();
        $phoneType = DB::table('phone_types')->first();
        $location = DB::table('sub_districts as sub')
            ->join('districts as district', function ($join) {
                $join->on('district.ProvinceCode', '=', 'sub.ProvinceCode')
                    ->on('district.DistrictCode', '=', 'sub.DistrictCode');
            })
            ->join('provinces as province', 'province.ProvinceCode', '=', 'sub.ProvinceCode')
            ->first([
                'sub.ProvinceCode', 'province.ProvinceDesc', 'sub.DistrictCode',
                'district.DistrictDesc', 'sub.SubDistrictCode', 'sub.SubDistrictDesc', 'sub.Zipcode',
            ]);

        $address = [[
            'AddressId' => 1,
            'AddressLine1' => '99 Test Road',
            'ProvinceCode' => $location->ProvinceCode,
            'ProvinceDesc' => $location->ProvinceDesc,
            'DistrictCode' => $location->DistrictCode,
            'DistrictDesc' => $location->DistrictDesc,
            'SubDistrictCode' => $location->SubDistrictCode,
            'SubDistrictDesc' => $location->SubDistrictDesc,
            'ZipCode' => $location->Zipcode ?? '10100',
        ]];
        $phones = [[
            'PhoneId' => 1,
            'Phone' => '0812345678',
            'PhoneType' => $phoneType->PhoneTypeCode,
        ]];

        return array_merge([
            'TitleCode' => $title->TitleCode,
            'Firstname' => self::CUSTOMER_FIRST_NAME,
            'Lastname' => 'ทดสอบ',
            'GenderCode' => $title->GenderCode,
            'BirthDate' => '1990-01-01',
            'IdentityCardTypeCode' => $identityType->IdentityCardTypeCode,
            'IdentityCardId' => 'P12345678',
            'MaritalStatusCode' => $marital->MaritalStatusCode,
            'WorkingConditionId' => $working->WorkingConditionId,
            'AddressTypeCode' => $addressType->AddressTypeCode,
            'Addresses' => json_encode($address, JSON_UNESCAPED_UNICODE),
            'IdentityCardAddressId' => 1,
            'HouseRegistrationAddressId' => 1,
            'CurrentAddressId' => 1,
            'MailingAddressId' => 1,
            'Phones' => json_encode($phones, JSON_UNESCAPED_UNICODE),
            'MobileTelephoneId' => 1,
            'Email' => 'customer@example.com',
            'MonthlyIncomeAmount' => '25,000',
            'MonthlyExpenseAmount' => '5,000',
        ], $overrides);
    }

    private function insertCustomer(string $customerNo, string $firstName, User $creator, string $createdAt): void
    {
        DB::table('customers')->insert([
            'CustomerNo' => $customerNo,
            'Firstname' => $firstName,
            'Lastname' => 'Test',
            'Email' => strtolower($firstName).'@example.com',
            'sysInsertUserId' => $creator->id,
            'sysInsertDateTime' => $createdAt,
        ]);
    }
}
