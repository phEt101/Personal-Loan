<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerMockSeeder extends Seeder
{
    private const CUSTOMER_CREATOR_CODES = ['EXT0004', 'EXT0005', 'EXT0006', 'EXT0007'];

    public function run(): void
    {
        $names = $this->customerNames();
        $context = $this->loadSeedContext(count($names));
        $timestamps = $this->mockInsertTimestamps(count($names));

        DB::transaction(fn () => $this->seedCustomers($names, $context, $timestamps));
    }

    private function customerNames(): array
    {
        return [
            ['กิตติพงษ์', 'สุขใจ', 1, 1],
            ['สุภาวดี', 'มีทรัพย์', 2, 2],
            ['ธนกร', 'เจริญผล', 1, 1],
            ['พิมพ์ชนก', 'แสงทอง', 2, 2],
            ['ณัฐวุฒิ', 'รุ่งเรือง', 1, 1],
            ['ชลธิชา', 'บุญมี', 2, 2],
            ['ปกรณ์', 'มั่นคง', 1, 1],
            ['วรัญญา', 'ศรีสุข', 2, 2],
            ['อนุชา', 'ใจดี', 1, 1],
            ['รัตนา', 'เพิ่มพูน', 2, 2],
            ['ศุภชัย', 'มั่งมี', 1, 1],
            ['กัญญารัตน์', 'แสนสุข', 2, 2],
            ['ธีรภัทร', 'วัฒนา', 1, 1],
            ['ปวีณา', 'รุ่งกิจ', 2, 2],
            ['จักรพันธ์', 'คงมั่น', 1, 1],
            ['สิริพร', 'พรเจริญ', 2, 2],
            ['นรินทร์', 'ทรัพย์สมบูรณ์', 1, 1],
        ];
    }

    private function loadSeedContext(int $customerCount): array
    {
        $locations = DB::table('sub_districts as sub')
            ->join('districts as district', function ($join) {
                $join->on('district.ProvinceCode', '=', 'sub.ProvinceCode')
                    ->on('district.DistrictCode', '=', 'sub.DistrictCode');
            })
            ->join('provinces as province', 'province.ProvinceCode', '=', 'sub.ProvinceCode')
            ->whereNotNull('sub.Zipcode')
            ->orderBy('sub.ProvinceCode')
            ->orderBy('sub.DistrictCode')
            ->limit($customerCount * 2)
            ->get([
                'sub.ProvinceCode',
                'province.ProvinceDesc',
                'sub.DistrictCode',
                'district.DistrictDesc',
                'sub.SubDistrictCode',
                'sub.SubDistrictDesc',
                'sub.Zipcode',
            ]);

        if ($locations->count() < $customerCount * 2) {
            throw new CustomerMockSeederException('Insufficient location master data for customer mocks.');
        }

        $titles = DB::table('titles')
            ->whereIn('TitleCode', [1, 2])
            ->pluck('TitleDesc', 'TitleCode');

        if ($titles->count() !== 2) {
            throw new CustomerMockSeederException('Required title master data is missing for customer mocks.');
        }

        $maritalStatus = DB::table('marital_statuses')->orderBy('MaritalStatusCode')->first();
        $workingCondition = DB::table('working_conditions')->where('IsRequireOccupation', true)->orderBy('WorkingConditionId')->first()
            ?? DB::table('working_conditions')->orderBy('WorkingConditionId')->first();
        $occupation = DB::table('occupations')->orderBy('OccupationCode')->first();
        $businessType = DB::table('type_of_businesses')->orderBy('TypeOfBusinessId')->first();
        $addressType = DB::table('address_types')->orderBy('AddressTypeCode')->first();
        $bank = DB::table('banks')->where('BankCode', 'BAAC')->first()
            ?? DB::table('banks')->where('Active', true)->orderBy('BankCode')->first();
        $mobilePhoneType = DB::table('phone_types')->where('PhoneTypeCode', '03')->value('PhoneTypeCode')
            ?? DB::table('phone_types')->orderBy('PhoneTypeCode')->value('PhoneTypeCode');
        $officePhoneType = DB::table('phone_types')->where('PhoneTypeCode', '02')->value('PhoneTypeCode')
            ?? DB::table('phone_types')->where('PhoneTypeCode', '<>', $mobilePhoneType)->orderBy('PhoneTypeCode')->value('PhoneTypeCode')
            ?? $mobilePhoneType;
        $externalUserIds = DB::table('users as user')
            ->join('roles as role', 'role.id', '=', 'user.role_id')
            ->where('user.user_type', 'external')
            ->where('role.slug', Role::USER_SLUG)
            ->whereIn('user.employee_code', self::CUSTOMER_CREATOR_CODES)
            ->orderBy('user.employee_code')
            ->pluck('user.id')
            ->values();

        if ($externalUserIds->count() !== count(self::CUSTOMER_CREATOR_CODES)) {
            throw new CustomerMockSeederException('The four configured external users are required for assigning mock customer creators.');
        }

        return compact(
            'locations',
            'titles',
            'maritalStatus',
            'workingCondition',
            'occupation',
            'businessType',
            'addressType',
            'bank',
            'mobilePhoneType',
            'officePhoneType',
            'externalUserIds'
        );
    }

    private function mockInsertTimestamps(int $customerCount): Collection
    {
        return collect(range(1, $customerCount))
            ->map(fn () => random_int(
                now(config('app.local_timezone'))->subMonthNoOverflow()->day(25)->startOfDay()->timestamp,
                now(config('app.local_timezone'))->timestamp
            ))
            ->sort()
            ->values();
    }

    private function seedCustomers(array $names, array $context, Collection $timestamps): void
    {
        foreach ($names as $index => $name) {
            $this->seedCustomer($index, $name, $context, $timestamps[$index]);
        }
    }

    private function seedCustomer(int $index, array $name, array $context, int $timestamp): void
    {
        [$firstName, $lastName, $titleCode, $genderCode] = $name;
        $insertedAt = Carbon::createFromTimestamp($timestamp, config('app.timezone'));
        $customerNo = $this->nextCustomerNo($insertedAt);
        $data = $this->customerDerivedData($index, $context);

        DB::table('customers')->insert([
            'QuickSearchKey' => "$firstName $lastName {$data['mobile']}",
            'CustomerNo' => $customerNo,
            'CustomerRefNo' => $customerNo,
            'Firstname' => $firstName,
            'Lastname' => $lastName,
            'Nickname' => mb_substr($firstName, 0, 8),
            'TitleCode' => $titleCode,
            'TitleDesc' => $context['titles'][$titleCode],
            'BirthDate' => $data['birthDate'],
            'GenderCode' => $genderCode,
            'IdentityCardTypeCode' => 1,
            'IdentityCardId' => $this->thaiIdentityCardNumber($index + 1),
            'IdentityCardIssuer' => 'สำนักงานเขตตัวอย่าง',
            'IdentityCardEffectiveDate' => $data['localNow']->copy()->subYears(8)->addDays($index)->toDateString(),
            'IdentityCardExpireDate' => $data['localNow']->copy()->addYears(2)->addDays($index)->toDateString(),
            'Nationality' => 'ไทย',
            'Race' => 'ไทย',
            'MaritalStatusCode' => $context['maritalStatus']->MaritalStatusCode,
            'MaritalStatusDesc' => $context['maritalStatus']->MaritalStatusName,
            'MaritalStatusScore' => $context['maritalStatus']->Score,
            'WorkingConditionId' => $context['workingCondition']->WorkingConditionId,
            'OccupationCode' => $context['occupation']?->OccupationCode,
            'OccupationDesc' => $context['occupation']?->OccupationDesc,
            'OccupationScore' => $context['occupation']?->Score,
            'OtherOccupationDesc' => $context['occupation']?->IsOtherOccupation ? 'อาชีพตัวอย่าง '.($index + 1) : null,
            'TypeOfBusinessId' => $context['businessType']?->TypeOfBusinessId,
            'TypeOfBusinessName' => $context['businessType']?->TypeOfBusinessName,
            'TypeOfBusinessBotCode' => $context['businessType']?->BOTCode,
            'AddressTypeCode' => $context['addressType']->AddressTypeCode,
            'AddressTypeDesc' => $context['addressType']->AddressTypeDesc,
            'AddressTypeScore' => $context['addressType']->Score,
            'AgeRangeScore' => $data['ageRangeScore'],
            'NetIncomeRangeScore' => $data['netIncomeRangeScore'],
            'IdentityCardAddressId' => 1,
            'HouseRegistrationAddressId' => 1,
            'CurrentAddressId' => 2,
            'MailingAddressId' => 2,
            'CurrentAddressAsText' => $data['addressText'],
            'MobileTelephoneId' => 1,
            'CustomerAddressLetterId' => 0,
            'CustomerAddressDebtId' => 0,
            'StatementAddressId' => 0,
            'ReceiptAddressId' => 0,
            'HomeTelephoneId' => 0,
            'OfficeTelephoneId' => 0,
            'OtherTelephoneId' => 0,
            'CollectionTelephoneId' => 0,
            'FaxId' => 0,
            'Mobile' => $data['mobile'],
            'Email' => 'mock'.($index + 1).'@example.com',
            'WorkPlace' => 'บริษัทตัวอย่าง '.($index + 1),
            'MonthlyIncomeAmount' => $data['monthlyIncome'],
            'MonthlyExpenseAmount' => $data['monthlyExpense'],
            'YearlyBonusAmount' => $data['yearlyBonus'],
            'BankCode' => $context['bank']?->BankCode,
            'BankBookBranch' => 'สาขาตัวอย่าง '.($index + 1),
            'BankBookCode' => '1'.str_pad((string) ($index + 1), 9, '0', STR_PAD_LEFT),
            'Score' => 0,
            'CreditLimitAmount' => 0,
            'CreditUsedAmount' => 0,
            'CreditorCreditDay' => 0,
            'CreditorCreditAmount' => 0,
            'IsDebtor' => true,
            'Status' => null,
            'InsertUserId' => 4,
            'InsertDate' => $insertedAt->copy()->timezone(config('app.local_timezone'))->toDateString(),
            'sysInsertUserId' => $context['externalUserIds']->random(),
            'sysInsertDateTime' => $insertedAt,
        ]);

        $this->insertCustomerAddresses($customerNo, $data, $context['addressType']);
        $this->insertCustomerContacts($customerNo, $index, $insertedAt, $data, $context);
    }

    private function customerDerivedData(int $index, array $context): array
    {
        $registeredLocation = $context['locations'][$index * 2];
        $currentLocation = $context['locations'][($index * 2) + 1];
        $mobile = '089'.str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT);
        $officePhone = '02'.str_pad((string) ($index + 1000000), 7, '0', STR_PAD_LEFT);
        $registeredLine1 = (string) ($index + 1).'/'.(string) ($index + 10).' อาคารตัวอย่าง';
        $registeredLine2 = 'หมู่ '.(($index % 9) + 1).' ถนนตัวอย่าง';
        $currentLine1 = (string) ($index + 21).'/'.(string) ($index + 30).' ห้อง '.($index + 1);
        $currentLine2 = 'ซอยตัวอย่าง '.($index + 1);
        $addressText = implode(' ', [$currentLine1, $currentLine2, $currentLocation->SubDistrictDesc,
            $currentLocation->DistrictDesc, $currentLocation->ProvinceDesc, $currentLocation->Zipcode]);
        $localNow = now(config('app.local_timezone'));
        $birthDate = $localNow->copy()->subYears(25 + $index)->subDays($index * 30)->toDateString();
        $ageRangeScore = DB::table('age_ranges')->where('FromAge', '<=', Carbon::parse($birthDate)->age)
            ->where('ToAge', '>=', Carbon::parse($birthDate)->age)->value('Score') ?? 0;
        $monthlyIncome = 20000 + ($index * 2500);
        $monthlyExpense = 5000 + ($index * 500);
        $yearlyBonus = 12000 + ($index * 1000);
        $monthlyNetIncome = max(0, $monthlyIncome - $monthlyExpense + ($yearlyBonus / 12));
        $netIncomeRangeScore = DB::table('net_income_ranges')->where('FromNetIncomeRange', '<=', $monthlyNetIncome)
            ->where('ToNetIncomeRange', '>=', $monthlyNetIncome)->value('Score') ?? 0;

        return compact('registeredLocation', 'currentLocation', 'mobile', 'officePhone', 'registeredLine1',
            'registeredLine2', 'currentLine1', 'currentLine2', 'addressText', 'localNow', 'birthDate',
            'ageRangeScore', 'monthlyIncome', 'monthlyExpense', 'yearlyBonus', 'netIncomeRangeScore');
    }

    private function insertCustomerAddresses(string $customerNo, array $data, object $addressType): void
    {
        $addresses = [
            1 => [$data['registeredLine1'], $data['registeredLine2'], $data['registeredLocation'], 'ที่อยู่ตามบัตรประชาชนและทะเบียนบ้าน'],
            2 => [$data['currentLine1'], $data['currentLine2'], $data['currentLocation'], 'ที่อยู่ปัจจุบันและที่อยู่ส่งจดหมาย'],
        ];

        foreach ($addresses as $addressId => [$line1, $line2, $location, $remark]) {
            DB::table('customer_addresses')->insert([
                'CustomerNo' => $customerNo, 'AddressId' => $addressId, 'AddressLine1' => $line1,
                'AddressLine2' => $line2, 'ProvinceCode' => $location->ProvinceCode,
                'ProvinceDesc' => $location->ProvinceDesc, 'DistrictCode' => $location->DistrictCode,
                'DistrictDesc' => $location->DistrictDesc, 'SubDistrictCode' => $location->SubDistrictCode,
                'SubDistrictDesc' => $location->SubDistrictDesc, 'ZipCode' => $location->Zipcode,
                'AddressTypeCode' => $addressType->AddressTypeCode, 'Remark' => $remark,
            ]);
        }
    }

    private function insertCustomerContacts(string $customerNo, int $index, Carbon $insertedAt, array $data, array $context): void
    {
        DB::table('customer_phones')->insert([
            ['CustomerNo' => $customerNo, 'PhoneId' => 1, 'Phone' => $data['mobile'], 'PhoneType' => $context['mobilePhoneType'], 'Remark' => 'เบอร์ติดต่อหลัก', 'PhoneSequense' => 1, 'Status' => true],
            ['CustomerNo' => $customerNo, 'PhoneId' => 2, 'Phone' => $data['officePhone'], 'PhoneType' => $context['officePhoneType'], 'Remark' => 'เบอร์ที่ทำงาน', 'PhoneSequense' => 2, 'Status' => true],
        ]);
        DB::table('customer_emails')->insert([
            'CustomerNo' => $customerNo, 'EmailId' => 1, 'Email' => 'mock'.($index + 1).'@example.com',
            'CreateDateTime' => $insertedAt, 'CreateUserId' => 4, 'Remark' => 'อีเมลตัวอย่าง',
        ]);
        DB::table('customer_remarks')->insert([
            'CustomerNo' => $customerNo, 'RemarkId' => 1, 'Comment' => 'ข้อมูลลูกค้า mock ลำดับที่ '.($index + 1),
            'InsertDateTime' => $insertedAt, 'InsertUserId' => 4,
        ]);
    }

    private function nextCustomerNo(Carbon $customerNumberDate): string
    {
        $prefix = '00CU'.$customerNumberDate->copy()->timezone(config('app.local_timezone'))->format('ymd');
        $latestCustomerNo = DB::table('customers')
            ->where('CustomerNo', 'like', $prefix.'%')
            ->orderByDesc('CustomerNo')
            ->lockForUpdate()
            ->value('CustomerNo');
        $sequence = $latestCustomerNo ? ((int) substr($latestCustomerNo, 10)) + 1 : 1;

        if ($sequence > 999999) {
            throw new CustomerMockSeederException('Daily customer number range is exhausted.');
        }

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    private function thaiIdentityCardNumber(int $sequence): string
    {
        $firstTwelveDigits = '11017000'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        $sum = 0;

        foreach (str_split($firstTwelveDigits) as $index => $digit) {
            $sum += ((int) $digit) * (13 - $index);
        }

        return $firstTwelveDigits.((11 - ($sum % 11)) % 10);
    }
}

final class CustomerMockSeederException extends \RuntimeException
{
}
