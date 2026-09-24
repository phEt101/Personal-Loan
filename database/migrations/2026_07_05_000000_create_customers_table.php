<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // H Meter source: Branch + Organization + Customer sequence.
            $table->string('CustomerNo', 16)->unique();
            $table->string('CustomerRefNo', 16)->nullable();
            $table->string('QuickSearchKey', 150)->nullable();

            // H Meter source: dbo.Customer personal and contact fields.
            $table->string('Firstname', 1000);
            $table->string('Lastname', 50);
            $table->string('Nickname', 10)->nullable();
            $table->string('Mobile', 15)->nullable();
            $table->string('Email', 50)->nullable();
            $table->string('CustNote', 100)->nullable();
            $table->unsignedInteger('CurrentAddressId')->nullable();
            $table->unsignedInteger('MobileTelephoneId')->nullable();
            $table->text('CurrentAddressAsText')->nullable();

            // H Meter source: dbo.Customer and dbo.User for the external audit user.
            $table->unsignedInteger('CustomerTypeCode')->nullable();
            $table->unsignedInteger('TitleCode')->nullable();
            $table->string('TitleDesc', 100)->nullable();
            $table->date('BirthDate')->nullable();
            $table->string('IdentityCardId', 20)->nullable();
            $table->unsignedInteger('MaritalStatusCode')->nullable();
            $table->string('MaritalStatusDesc', 100)->nullable();
            $table->string('Nationality', 50)->nullable();
            $table->unsignedInteger('InsertUserId')->nullable();
            $table->unsignedInteger('GenderCode')->nullable();
            $table->unsignedInteger('AddressTypeCode')->nullable();
            $table->unsignedInteger('IdentityCardTypeCode')->nullable();
            $table->unsignedInteger('IdentityCardAddressId')->nullable();
            $table->unsignedInteger('HouseRegistrationAddressId')->nullable();
            $table->unsignedInteger('MailingAddressId')->nullable();

            // H Meter lookup sources: AgeRange, AddressType, MaritalStatus, Occupation, NetIncomeRange.
            $table->unsignedInteger('AgeRangeScore')->nullable();
            $table->unsignedInteger('AddressTypeScore')->nullable();
            $table->unsignedInteger('MaritalStatusScore')->nullable();
            $table->unsignedInteger('OccupationCode')->nullable();
            $table->unsignedInteger('OccupationScore')->nullable();
            $table->string('OccupationDesc', 255)->nullable();
            $table->string('OtherOccupationDesc', 255)->nullable();
            $table->string('WorkPlace', 255)->nullable();
            $table->decimal('MonthlyIncomeAmount', 18, 2)->nullable();
            $table->decimal('MonthlyExpenseAmount', 18, 2)->nullable();
            $table->decimal('Score', 18, 2)->nullable();
            $table->decimal('CreditLimitAmount', 18, 2)->nullable();
            $table->decimal('CreditUsedAmount', 18, 2)->nullable();
            $table->decimal('YearlyBonusAmount', 18, 2)->nullable();
            $table->unsignedInteger('NetIncomeRangeScore')->nullable();

            // H Meter source: dbo.Customer role and business fields.
            $table->unsignedInteger('CreditorCreditDay')->nullable();
            $table->decimal('CreditorCreditAmount', 18, 2)->nullable();
            $table->boolean('IsCreditor')->default(false);
            $table->boolean('IsDebtor')->default(false);
            $table->boolean('IsDealer')->default(false);
            $table->boolean('IsInsurer')->default(false);
            $table->boolean('IsOutsource')->default(false);
            $table->boolean('IsLawyer')->default(false);
            $table->string('Race', 50)->nullable();
            $table->unsignedInteger('TypeOfBusinessId')->nullable();
            $table->string('TypeOfBusinessName', 255)->nullable();
            $table->string('TypeOfBusinessBotCode', 50)->nullable();
            $table->unsignedInteger('WorkingConditionId')->nullable();

            // H Meter source: dbo.Customer organization and registration fields.
            $table->decimal('AccountApDeposit', 18, 2)->nullable();
            $table->decimal('AccountAp', 18, 2)->nullable();
            $table->decimal('AccountOtherSuspend', 18, 2)->nullable();
            $table->unsignedInteger('CustomerRegistrationTypeCode')->default(0);
            $table->string('RegistrationTypeValue', 255)->nullable();
            $table->string('OrganizationNameNcb', 255)->nullable();
            $table->boolean('IsOutsourceLegal')->default(false);
            $table->date('IdentityCardEffectiveDate')->nullable();
            $table->date('IdentityCardExpireDate')->nullable();
            $table->boolean('IsSupplier')->default(false);
            $table->boolean('IsFinance')->default(false);
            $table->date('OrganizationRegisteredDate')->nullable();
            $table->decimal('OrganizationRegisteredCapital', 18, 2)->nullable();
            $table->string('HeadquartersBranchNo', 50)->nullable();
            $table->string('HeadquartersBranchDesc', 255)->nullable();
            $table->unsignedInteger('OrganizationBranchTypeCode')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('IdentityCardId');
            $table->index('Mobile');
            $table->index('Email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
