<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('address_types', function (Blueprint $table) {
            $table->unsignedTinyInteger('AddressTypeCode')->primary();
            $table->string('AddressTypeDesc', 100);
            $table->unsignedTinyInteger('Score');
            $table->string('Remark', 255)->nullable();
            $table->string('NcbCommercialAddressType', 10)->nullable();
        });

        Schema::create('age_ranges', function (Blueprint $table) {
            $table->unsignedInteger('AgeRangeId')->primary();
            $table->unsignedTinyInteger('FromAge');
            $table->unsignedTinyInteger('ToAge');
            $table->string('Remark', 255)->nullable();
            $table->unsignedTinyInteger('Score');
        });

        Schema::create('banks', function (Blueprint $table) {
            $table->string('BankCode', 5)->primary();
            $table->string('BankDesc', 150);
            $table->boolean('SncSet');
            $table->boolean('Active');
            $table->boolean('RegisterBillPayment');
            $table->string('BillPaymentBankCode', 10)->nullable();
            $table->boolean('SpecialChannel');
            $table->boolean('IsBillPaymentGFMS');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->string('ProvinceCode', 2);
            $table->string('DistrictCode', 5);
            $table->string('DistrictDesc', 100);
            $table->primary(['ProvinceCode', 'DistrictCode']);
        });

        Schema::create('genders', function (Blueprint $table) {
            $table->unsignedTinyInteger('GenderId')->primary();
            $table->string('GenderDesc', 50);
            $table->unsignedTinyInteger('Score');
            $table->string('Remark', 255)->nullable();
            $table->string('GenderCodeName', 2);
        });

        Schema::create('identity_card_types', function (Blueprint $table) {
            $table->unsignedTinyInteger('IdentityCardTypeCode')->primary();
            $table->string('IdentityCardTypeDesc', 100);
            $table->string('NcbIDTypeCode', 10)->nullable();
            $table->string('BOTCode', 10)->nullable();
        });

        Schema::create('marital_statuses', function (Blueprint $table) {
            $table->unsignedTinyInteger('MaritalStatusCode')->primary();
            $table->string('MaritalStatusName', 100);
            $table->string('Remark', 255)->nullable();
            $table->unsignedTinyInteger('Score');
        });

        Schema::create('net_income_ranges', function (Blueprint $table) {
            $table->unsignedInteger('NetIncomeRangeId')->primary();
            $table->unsignedInteger('FromNetIncomeRange');
            $table->unsignedInteger('ToNetIncomeRange');
            $table->string('Remark', 255)->nullable();
            $table->unsignedTinyInteger('Score');
        });

        Schema::create('occupations', function (Blueprint $table) {
            $table->unsignedInteger('OccupationCode')->primary();
            $table->string('OccupationDesc', 255);
            $table->string('Remark', 255)->nullable();
            $table->unsignedTinyInteger('Score');
            $table->string('BOTCode', 10)->nullable();
            $table->unsignedInteger('TypeOfBusinessId')->nullable();
            $table->string('EmploymentConditions', 10)->nullable();
            $table->string('TypeOfBusinessName', 150)->nullable();
            $table->boolean('IsOtherOccupation');
            $table->index('TypeOfBusinessId');
        });

        Schema::create('phone_types', function (Blueprint $table) {
            $table->string('PhoneTypeCode', 2)->primary();
            $table->string('PhoneTypeDesc', 50);
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->string('ProvinceCode', 2)->primary();
            $table->string('ProvinceDesc', 100);
            $table->string('NcbProvinceCatalogId', 10)->nullable();
            $table->string('NcbProvinceCode', 10)->nullable();
        });

        Schema::create('sub_districts', function (Blueprint $table) {
            $table->string('ProvinceCode', 2);
            $table->string('DistrictCode', 5);
            $table->string('SubDistrictCode', 5);
            $table->string('SubDistrictDesc', 100);
            $table->string('Zipcode', 10)->nullable();
            $table->primary(['ProvinceCode', 'DistrictCode', 'SubDistrictCode']);
        });

        Schema::create('titles', function (Blueprint $table) {
            $table->unsignedTinyInteger('TitleCode')->primary();
            $table->string('TitleDesc', 100)->nullable();
            $table->string('TitleCodeName', 10)->nullable();
            $table->string('NcbRegistrationType', 10)->nullable();
            $table->unsignedTinyInteger('GenderCode');
            $table->string('GenderName', 50);
            $table->boolean('Active');
            $table->unsignedInteger('EventId')->nullable();
        });

        Schema::create('type_of_businesses', function (Blueprint $table) {
            $table->unsignedInteger('TypeOfBusinessId')->primary();
            $table->string('TypeOfBusinessName', 150);
            $table->string('BOTCode', 10)->nullable();
            $table->boolean('Active');
        });

        Schema::create('working_conditions', function (Blueprint $table) {
            $table->unsignedInteger('WorkingConditionId')->primary();
            $table->string('CodeBOT', 10)->nullable();
            $table->string('Description', 255);
            $table->boolean('IsRequireOccupation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_conditions');
        Schema::dropIfExists('type_of_businesses');
        Schema::dropIfExists('titles');
        Schema::dropIfExists('sub_districts');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('phone_types');
        Schema::dropIfExists('occupations');
        Schema::dropIfExists('net_income_ranges');
        Schema::dropIfExists('marital_statuses');
        Schema::dropIfExists('identity_card_types');
        Schema::dropIfExists('genders');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('banks');
        Schema::dropIfExists('age_ranges');
        Schema::dropIfExists('address_types');
    }
};