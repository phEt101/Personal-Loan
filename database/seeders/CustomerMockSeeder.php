<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerMockSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            ['กิตติพงษ์', 'สุขใจ'],
            ['สุภาวดี', 'มีทรัพย์'],
            ['ธนกร', 'เจริญผล'],
            ['พิมพ์ชนก', 'แสงทอง'],
            ['ณัฐวุฒิ', 'รุ่งเรือง'],
            ['ชลธิชา', 'บุญมี'],
            ['ปกรณ์', 'มั่นคง'],
            ['วรัญญา', 'ศรีสุข'],
            ['อนุชา', 'ใจดี'],
            ['รัตนา', 'เพิ่มพูน'],
        ];

        $locations = DB::table('sub_districts as sub')
            ->join('districts as district', function ($join) {
                $join->on('district.ProvinceCode', '=', 'sub.ProvinceCode')
                    ->on('district.DistrictCode', '=', 'sub.DistrictCode');
            })
            ->join('provinces as province', 'province.ProvinceCode', '=', 'sub.ProvinceCode')
            ->whereNotNull('sub.Zipcode')
            ->orderBy('sub.ProvinceCode')
            ->orderBy('sub.DistrictCode')
            ->limit(20)
            ->get([
                'sub.ProvinceCode',
                'province.ProvinceDesc',
                'sub.DistrictCode',
                'district.DistrictDesc',
                'sub.SubDistrictCode',
                'sub.SubDistrictDesc',
                'sub.Zipcode',
            ]);

        if ($locations->count() < 20) {
            throw new \RuntimeException('Insufficient location master data for customer mocks.');
        }

        $titleCode = DB::table('titles')->where('Active', true)->orderBy('TitleCode')->value('TitleCode');
        $genderCode = DB::table('titles')->where('TitleCode', $titleCode)->value('GenderCode')
            ?? DB::table('genders')->orderBy('GenderId')->value('GenderId');
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
        $systemUserIds = DB::table('users')->orderBy('id')->pluck('id')->shuffle()->values();

        if ($systemUserIds->count() < count($names)) {
            throw new \RuntimeException('Insufficient users for assigning mock customer creators.');
        }
        $titleDesc = DB::table('titles')->where('TitleCode', $titleCode)->value('TitleDesc');

        $latestMockNumber = DB::table('customers')
            ->where('CustomerNo', 'like', 'MOCK%')
            ->orderByDesc('CustomerNo')
            ->value('CustomerNo');
        $nextMockNumber = $latestMockNumber ? ((int) substr($latestMockNumber, 4)) + 1 : 1;
        $mockInsertTimestamps = collect(range(1, count($names)))
            ->map(fn () => random_int(
                now()->subMonthNoOverflow()->day(25)->startOfDay()->timestamp,
                now()->timestamp
            ))
            ->sort()
            ->values();

        DB::transaction(function () use (
            $names,
            $locations,
            $titleCode,
            $titleDesc,
            $genderCode,
            $maritalStatus,
            $workingCondition,
            $occupation,
            $businessType,
            $addressType,
            $bank,
            $mobilePhoneType,
            $officePhoneType,
            $systemUserIds,
            $nextMockNumber,
            $mockInsertTimestamps
        ): void {
            foreach ($names as $index => [$firstName, $lastName]) {
                $insertedAt = Carbon::createFromTimestamp(
                    $mockInsertTimestamps[$index],
                    config('app.timezone')
                );
                $customerNo = 'MOCK'.str_pad((string) ($nextMockNumber + $index), 12, '0', STR_PAD_LEFT);
                $registeredLocation = $locations[$index * 2];
                $currentLocation = $locations[($index * 2) + 1];
                $mobile = '089'.str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT);
                $officePhone = '02'.str_pad((string) ($index + 1000000), 7, '0', STR_PAD_LEFT);
                $registeredLine1 = (string) ($index + 1).'/'.(string) ($index + 10).' อาคารตัวอย่าง';
                $registeredLine2 = 'หมู่ '.(($index % 9) + 1).' ถนนตัวอย่าง';
                $currentLine1 = (string) ($index + 21).'/'.(string) ($index + 30).' ห้อง '.($index + 1);
                $currentLine2 = 'ซอยตัวอย่าง '.($index + 1);
                $addressText = implode(' ', [
                    $currentLine1,
                    $currentLine2,
                    $currentLocation->SubDistrictDesc,
                    $currentLocation->DistrictDesc,
                    $currentLocation->ProvinceDesc,
                    $currentLocation->Zipcode,
                ]);
                $birthDate = now()->subYears(25 + $index)->subDays($index * 30)->toDateString();
                $age = Carbon::parse($birthDate)->age;
                $ageRangeScore = DB::table('age_ranges')
                    ->where('FromAge', '<=', $age)
                    ->where('ToAge', '>=', $age)
                    ->value('Score') ?? 0;
                $monthlyIncome = 20000 + ($index * 2500);
                $monthlyExpense = 5000 + ($index * 500);
                $yearlyBonus = 12000 + ($index * 1000);
                $monthlyNetIncome = max(0, $monthlyIncome - $monthlyExpense + ($yearlyBonus / 12));
                $netIncomeRangeScore = DB::table('net_income_ranges')
                    ->where('FromNetIncomeRange', '<=', $monthlyNetIncome)
                    ->where('ToNetIncomeRange', '>=', $monthlyNetIncome)
                    ->value('Score') ?? 0;

                DB::table('customers')->insert([
                    'QuickSearchKey' => "$firstName $lastName $mobile",
                    'CustomerNo' => $customerNo,
                    'CustomerRefNo' => $customerNo,
                    'Firstname' => $firstName,
                    'Lastname' => $lastName,
                    'Nickname' => mb_substr($firstName, 0, 8),
                    'TitleCode' => $titleCode,
                    'TitleDesc' => $titleDesc,
                    'BirthDate' => $birthDate,
                    'GenderCode' => $genderCode,
                    'IdentityCardTypeCode' => 1,
                    'IdentityCardId' => $this->thaiIdentityCardNumber($nextMockNumber + $index),
                    'IdentityCardIssuer' => 'สำนักงานเขตตัวอย่าง',
                    'IdentityCardEffectiveDate' => now()->subYears(8)->addDays($index)->toDateString(),
                    'IdentityCardExpireDate' => now()->addYears(2)->addDays($index)->toDateString(),
                    'Nationality' => 'ไทย',
                    'Race' => 'ไทย',
                    'MaritalStatusCode' => $maritalStatus->MaritalStatusCode,
                    'MaritalStatusDesc' => $maritalStatus->MaritalStatusName,
                    'MaritalStatusScore' => $maritalStatus->Score,
                    'WorkingConditionId' => $workingCondition->WorkingConditionId,
                    'OccupationCode' => $occupation?->OccupationCode,
                    'OccupationDesc' => $occupation?->OccupationDesc,
                    'OccupationScore' => $occupation?->Score,
                    'OtherOccupationDesc' => $occupation?->IsOtherOccupation ? 'อาชีพตัวอย่าง '.($index + 1) : null,
                    'TypeOfBusinessId' => $businessType?->TypeOfBusinessId,
                    'TypeOfBusinessName' => $businessType?->TypeOfBusinessName,
                    'TypeOfBusinessBotCode' => $businessType?->BOTCode,
                    'AddressTypeCode' => $addressType->AddressTypeCode,
                    'AddressTypeDesc' => $addressType->AddressTypeDesc,
                    'AddressTypeScore' => $addressType->Score,
                    'AgeRangeScore' => $ageRangeScore,
                    'NetIncomeRangeScore' => $netIncomeRangeScore,
                    'IdentityCardAddressId' => 1,
                    'HouseRegistrationAddressId' => 1,
                    'CurrentAddressId' => 2,
                    'MailingAddressId' => 2,
                    'CurrentAddressAsText' => $addressText,
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
                    'Mobile' => $mobile,
                    'Email' => 'mock'.($index + 1).'@example.com',
                    'WorkPlace' => 'บริษัทตัวอย่าง '.($index + 1),
                    'MonthlyIncomeAmount' => $monthlyIncome,
                    'MonthlyExpenseAmount' => $monthlyExpense,
                    'YearlyBonusAmount' => $yearlyBonus,
                    'BankCode' => $bank?->BankCode,
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
                    'InsertDate' => $insertedAt->toDateString(),
                    'sysInsertUserId' => $systemUserIds[$index],
                    'sysInsertDateTime' => $insertedAt,
                ]);

                $addresses = [
                    1 => [$registeredLine1, $registeredLine2, $registeredLocation, 'ที่อยู่ตามบัตรประชาชนและทะเบียนบ้าน'],
                    2 => [$currentLine1, $currentLine2, $currentLocation, 'ที่อยู่ปัจจุบันและที่อยู่ส่งจดหมาย'],
                ];

                foreach ($addresses as $addressId => [$line1, $line2, $location, $remark]) {
                    DB::table('customer_addresses')->insert([
                        'CustomerNo' => $customerNo,
                        'AddressId' => $addressId,
                        'AddressLine1' => $line1,
                        'AddressLine2' => $line2,
                        'ProvinceCode' => $location->ProvinceCode,
                        'ProvinceDesc' => $location->ProvinceDesc,
                        'DistrictCode' => $location->DistrictCode,
                        'DistrictDesc' => $location->DistrictDesc,
                        'SubDistrictCode' => $location->SubDistrictCode,
                        'SubDistrictDesc' => $location->SubDistrictDesc,
                        'ZipCode' => $location->Zipcode,
                        'AddressTypeCode' => $addressType->AddressTypeCode,
                        'Remark' => $remark,
                    ]);
                }

                DB::table('customer_phones')->insert([
                    ['CustomerNo' => $customerNo, 'PhoneId' => 1, 'Phone' => $mobile, 'PhoneType' => $mobilePhoneType, 'Remark' => 'เบอร์ติดต่อหลัก', 'PhoneSequense' => 1, 'Status' => true],
                    ['CustomerNo' => $customerNo, 'PhoneId' => 2, 'Phone' => $officePhone, 'PhoneType' => $officePhoneType, 'Remark' => 'เบอร์ที่ทำงาน', 'PhoneSequense' => 2, 'Status' => true],
                ]);

                DB::table('customer_emails')->insert([
                    'CustomerNo' => $customerNo,
                    'EmailId' => 1,
                    'Email' => 'mock'.($index + 1).'@example.com',
                    'CreateDateTime' => $insertedAt,
                    'CreateUserId' => 4,
                    'Remark' => 'อีเมลตัวอย่าง',
                ]);

                DB::table('customer_remarks')->insert([
                    'CustomerNo' => $customerNo,
                    'RemarkId' => 1,
                    'Comment' => 'ข้อมูลลูกค้า mock ลำดับที่ '.($index + 1),
                    'InsertDateTime' => $insertedAt,
                    'InsertUserId' => 4,
                ]);
            }
        });
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
