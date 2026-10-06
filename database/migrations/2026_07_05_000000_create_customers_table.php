<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();

            // ระบบสร้างคำค้นจากชื่อเล่น ชื่อ นามสกุล เลขบัตร และเบอร์โทรศัพท์
            $table->string('QuickSearchKey', 150)->nullable();
            // เลขลูกค้าที่สร้างและจัดการภายในระบบ
            $table->string('CustomerNo', 16)->unique();

            // ลูกค้าบุคคลกำหนด CustomerTypeCode เป็น 1
            $table->unsignedInteger('CustomerTypeCode')->default(1);
            // ข้อมูลส่วนบุคคลที่กรอกจากระบบนี้
            $table->string('Firstname', 100);
            $table->string('Lastname', 50);
            $table->string('Nickname', 10)->nullable();
            $table->unsignedTinyInteger('TitleCode')->nullable();
            $table->date('BirthDate')->nullable();
            $table->string('IdentityCardId', 20)->nullable();
            $table->unsignedTinyInteger('IdentityCardTypeCode')->nullable();
            $table->string('IdentityCardIssuer', 100)->nullable();
            $table->string('Nationality', 50)->nullable();
            // อ้างอิงสถานภาพสมรสจากตาราง marital_statuses
            $table->unsignedTinyInteger('MaritalStatusCode')->nullable();
            // อ้างอิงรายละเอียดอาชีพและคะแนนจากตาราง occupations
            $table->unsignedInteger('OccupationCode')->nullable();
            $table->string('Mobile', 15)->nullable();
            // อ้างอิง AddressId จากตาราง customer_addresses
            $table->unsignedInteger('IdentityCardAddressId')->nullable();
            $table->unsignedInteger('HouseRegistrationAddressId')->nullable();
            $table->unsignedInteger('CurrentAddressId')->nullable();
            $table->unsignedInteger('MailingAddressId')->nullable();
            // ข้อมูลการทำงาน รายได้ เครดิต และบัญชีธนาคารที่กรอกจากระบบนี้
            $table->string('WorkPlace', 255)->nullable();
            $table->decimal('MonthlyIncomeAmount', 18, 2)->nullable();
            $table->decimal('MonthlyExpenseAmount', 18, 2)->nullable();
            $table->decimal('YearlyBonusAmount', 18, 2)->nullable();
            $table->decimal('Score', 18, 2)->nullable()->default(0);
            $table->decimal('CreditLimitAmount', 18, 2)->nullable()->default(0);
            $table->decimal('CreditUsedAmount', 18, 2)->nullable()->default(0);
            // ข้อมูลนิติบุคคลอยู่นอกขอบเขต จึงกำหนดเป็น 0
            $table->unsignedInteger('OrganizationBranchTypeCode')->default(0);
            $table->string('OrganizationBranchNo', 50)->nullable();
            $table->string('Email', 50);
            $table->string('LineUserId', 100)->nullable();
            $table->string('ContactPerson', 255)->nullable();
            // Legacy audit user identifier
            $table->unsignedInteger('InsertUserId')->nullable();
            $table->date('InsertDate')->nullable();
            $table->unsignedInteger('UpdateUserId')->nullable();
            $table->dateTime('UpdateDate')->nullable();
            $table->unsignedInteger('DeleteUserId')->nullable();
            $table->dateTime('DeleteDate')->nullable();
            $table->string('CustomerAvatar', 255)->nullable();
            $table->unsignedTinyInteger('GenderCode')->nullable();
            $table->string('CustomerGradeCode', 20)->nullable();
            // ระบบประกอบข้อความที่อยู่จากข้อมูลใน customer_addresses
            $table->text('CurrentAddressAsText')->nullable();
            // อ้างอิงประเภทที่อยู่และคะแนนจากตาราง address_types
            $table->unsignedTinyInteger('AddressTypeCode')->nullable();
            // คำนวณคะแนนจากตาราง age_ranges และ net_income_ranges
            $table->unsignedInteger('AgeRangeScore')->default(0);
            $table->unsignedInteger('NetIncomeRangeScore')->default(0);
            // คะแนนจากประเภทที่อยู่ อาชีพ และสถานภาพสมรส
            $table->unsignedInteger('AddressTypeScore')->nullable();
            $table->unsignedInteger('OccupationScore')->nullable();
            $table->unsignedInteger('MaritalStatusScore')->default(0);
            // สถานะและบทบาทลูกค้าที่กำหนดจากระบบนี้
            $table->boolean('IsCreditor')->default(false);
            $table->boolean('IsDebtor')->default(true);
            $table->unsignedInteger('CreditorCreditDay')->nullable()->default(0);
            $table->decimal('CreditorCreditAmount', 18, 2)->nullable()->default(0);
            $table->boolean('IsDealer')->default(false);
            $table->boolean('IsInsurer')->default(false);
            $table->boolean('IsOutsource')->default(false);
            $table->boolean('IsLawyer')->default(false);
            $table->string('BankCode', 5)->nullable();
            $table->string('BankBookCode', 20)->nullable();
            $table->string('BankBookBranch', 50)->nullable();
            $table->boolean('IsSalesRepresentative')->default(false);
            $table->string('TitleDesc', 100)->nullable();
            $table->string('RegistrationNo', 50)->nullable();
            $table->boolean('Status')->nullable()->default(null);
            $table->boolean('IsAuction')->default(false);
            $table->string('MaritalStatusDesc', 100)->nullable();
            $table->string('OccupationDesc', 255)->nullable();
            $table->string('AddressTypeDesc', 100)->nullable();
            $table->unsignedInteger('CustomerAddressLetterId')->default(0);
            $table->unsignedInteger('CustomerAddressDebtId')->default(0);
            $table->unsignedInteger('StatementAddressId')->default(0);
            $table->unsignedInteger('ReceiptAddressId')->default(0);
            $table->unsignedInteger('HomeTelephoneId')->default(0);
            $table->unsignedInteger('OfficeTelephoneId')->default(0);
            // อ้างอิง PhoneId ของเบอร์โทรหลักจากตาราง customer_phones
            $table->unsignedInteger('MobileTelephoneId')->nullable();
            $table->unsignedInteger('OtherTelephoneId')->default(0);
            $table->unsignedInteger('CollectionTelephoneId')->default(0);
            $table->unsignedInteger('FaxId')->default(0);
            $table->string('Race', 50)->nullable();
            // อ้างอิงข้อมูลประเภทธุรกิจจากตาราง type_of_businesses
            $table->unsignedInteger('TypeOfBusinessId')->nullable();
            $table->string('TypeOfBusinessName', 255)->nullable();
            $table->string('TypeOfBusinessBotCode', 50)->nullable();
            $table->string('OtherOccupationDesc', 255)->nullable();
            // อ้างอิงสภาพการทำงานจากตาราง working_conditions
            $table->unsignedInteger('WorkingConditionId')->nullable();
            $table->string('AccountApDeposit', 50)->nullable();
            $table->string('AccountAp', 50)->nullable();
            $table->string('AccountOtherSuspend', 50)->nullable();
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

            // ใช้เลขเดียวกับ CustomerNo สำหรับอ้างอิงลูกค้า
            $table->string('CustomerRefNo', 16)->nullable();

            // ผู้สร้างและผู้แก้ไขข้อมูลจากระบบ Personal Loan
            $table->foreignId('sysInsertUserId')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sysUpdateUserId')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('sysInsertDateTime')->nullable();
            $table->dateTime('sysUpdateDateTime')->nullable();

            // สถานะการนำข้อมูลเข้า H Meter และกำหนดอายุไฟล์แนบหลังการนำเข้า
            $table->string('HmeterTransferStatus', 20)->default('pending')->index();
            $table->dateTime('HmeterTransferredAt')->nullable();
            $table->foreignId('HmeterTransferredBy')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('AttachmentPurgeAfter')->nullable()->index();
            $table->dateTime('AttachmentsPurgedAt')->nullable();

            $table->index('IdentityCardId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
