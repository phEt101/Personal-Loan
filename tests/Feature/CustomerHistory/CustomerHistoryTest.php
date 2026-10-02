<?php

namespace Tests\Feature\CustomerHistory;

use App\Models\User;
use Database\Seeders\MasterLookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterLookupSeeder::class);
    }

    public function test_authenticated_user_can_create_view_and_update_customer(): void
    {
        $user = User::factory()->create();

        $create = $this->actingAs($user)
            ->postJson('/customer-history', $this->customerPayload())
            ->assertCreated()
            ->assertJsonStructure(['message', 'customer_no']);

        $customerNo = $create->json('customer_no');
        $this->assertMatchesRegularExpression('/^00CU\d{12}$/', $customerNo);
        $this->assertDatabaseHas('customers', [
            'CustomerNo' => $customerNo,
            'CustomerRefNo' => $customerNo,
            'Firstname' => 'สมชาย',
            'sysInsertUserId' => $user->id,
        ]);
        $this->assertDatabaseHas('customer_addresses', ['CustomerNo' => $customerNo, 'AddressId' => 1]);
        $this->assertDatabaseHas('customer_phones', ['CustomerNo' => $customerNo, 'Phone' => '0812345678']);

        $this->getJson("/customer-history/{$customerNo}")
            ->assertOk()
            ->assertJsonPath('customer.Firstname', 'สมชาย')
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
        $html = $this->actingAs($user)->get('/customer-history')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<script type="application\/json" id="customerHistoryFormConfig">(.*?)<\/script>/s',
            $html
        );
        preg_match('/<script type="application\/json" id="customerHistoryFormConfig">(.*?)<\/script>/s', $html, $matches);

        $config = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('attachmentTypes', $config);
    }

    public function test_customer_number_is_generated_uniquely_by_the_system(): void
    {
        $user = User::factory()->create();

        $firstCustomerNo = $this->actingAs($user)
            ->postJson('/customer-history', $this->customerPayload())
            ->assertCreated()
            ->json('customer_no');

        $secondCustomerNo = $this->postJson('/customer-history', $this->customerPayload([
            'IdentityCardId' => 'P87654321',
            'Email' => 'second@example.com',
        ]))
            ->assertCreated()
            ->json('customer_no');

        $prefix = '00CU'.now()->format('ymd');
        $this->assertSame($prefix.'000001', $firstCustomerNo);
        $this->assertSame($prefix.'000002', $secondCustomerNo);
        $this->assertNotSame($firstCustomerNo, $secondCustomerNo);
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
                    'DocumentName' => 'สำเนาบัตรประชาชน',
                    'DocumentTypeId' => $nationalIdType,
                    'File' => UploadedFile::fake()->create('national-id.pdf', 100, 'application/pdf'),
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
            ->post('/customer-history', $payload)
            ->assertCreated()
            ->json('customer_no');

        $attachments = DB::table('customer_attachments')->where('CustomerNo', $customerNo)->get();
        $this->assertCount(2, $attachments);
        $this->assertSame('สำเนาบัตรประชาชน', $attachments->firstWhere('DocumentTypeId', $nationalIdType)->DocumentName);
        Storage::disk('local')->assertExists($attachments->pluck('FilePath')->all());

        $detail = $this->getJson("/customer-history/{$customerNo}")
            ->assertOk()
            ->assertJsonCount(2, 'attachments');
        $downloadUrl = $detail->json('attachments.0.download_url');
        $attachmentId = $detail->json('attachments.0.id');

        $this->get($downloadUrl)
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename=national-id.pdf');
        $this->actingAs($otherUser)->get($downloadUrl)->assertNotFound();

        $filePath = DB::table('customer_attachments')->where('id', $attachmentId)->value('FilePath');
        $this->actingAs($owner)
            ->withHeader('Accept', 'application/json')
            ->post("/customer-history/{$customerNo}", array_merge($this->customerPayload(), [
                '_method' => 'PUT',
                'RemoveAttachmentIds' => [$attachmentId],
            ]))
            ->assertOk();

        $this->assertDatabaseMissing('customer_attachments', ['id' => $attachmentId]);
        Storage::disk('local')->assertMissing($filePath);
    }

    public function test_customer_creation_rejects_invalid_or_incomplete_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/customer-history', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'TitleCode', 'Firstname', 'Lastname', 'GenderCode', 'BirthDate',
                'IdentityCardTypeCode', 'IdentityCardId', 'MaritalStatusCode',
                'WorkingConditionId', 'Addresses', 'Phones', 'Email',
            ]);

        $payload = $this->customerPayload(['IdentityCardAddressId' => 99]);
        $this->postJson('/customer-history', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('IdentityCardAddressId');
    }

    public function test_internal_user_can_search_filter_by_creator_and_paginate_customers(): void
    {
        $internal = User::factory()->create(['user_type' => 'internal']);
        $external = User::factory()->create(['user_type' => 'external']);
        $this->insertCustomer('MOCK000000000001', 'Needle', $external, '2026-09-10 10:00:00');

        foreach (range(2, 7) as $number) {
            $this->insertCustomer(
                'MOCK'.str_pad((string) $number, 12, '0', STR_PAD_LEFT),
                "Customer{$number}",
                $internal,
                "2026-09-{$number} 10:00:00"
            );
        }

        $this->actingAs($internal)
            ->get('/customer-history?q=Needle&creator_type=external&date_from=2026-09-01&date_to=2026-09-30')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 1
                && $customers->first()->Firstname === 'Needle');

        $this->get('/customer-history?per_page=5&page=2')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->perPage() === 5
                && $customers->currentPage() === 2
                && $customers->total() === 7);
    }

    public function test_external_user_only_sees_and_can_open_own_customers(): void
    {
        $external = User::factory()->create(['user_type' => 'external']);
        $other = User::factory()->create(['user_type' => 'external']);
        $this->insertCustomer('MOCK000000000001', 'Owned', $external, '2026-09-10 10:00:00');
        $this->insertCustomer('MOCK000000000002', 'Hidden', $other, '2026-09-11 10:00:00');

        $this->actingAs($external)
            ->get('/customer-history')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 1
                && $customers->first()->Firstname === 'Owned');

        $this->getJson('/customer-history/MOCK000000000001')->assertOk();
        $this->getJson('/customer-history/MOCK000000000002')->assertNotFound();
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
            'Firstname' => 'สมชาย',
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
