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
            // สร้างเลขลูกค้าจาก H Meter ตาราง dbo.Branch, dbo.Organization และ dbo.Customer
            $table->string('CustomerNo', 16)->unique();

            // ลูกค้าบุคคลกำหนด CustomerTypeCode เป็น 1
            $table->unsignedInteger('CustomerTypeCode')->default(1);
            // ข้อมูลส่วนบุคคลที่กรอกจากระบบนี้
            $table->string('Firstname', 100);
            $table->string('Lastname', 50);
            $table->string('Nickname', 10)->nullable();
            $table->unsignedInteger('TitleCode')->nullable();
            $table->date('BirthDate')->nullable();
            $table->string('IdentityCardId', 20)->nullable();
            $table->unsignedInteger('IdentityCardTypeCode')->nullable();
            $table->string('IdentityCardIssuer', 100)->nullable();
            $table->string('Nationality', 50)->nullable();
            // ดึงสถานภาพสมรสจาก H Meter ตาราง dbo.MaritalStatus
            $table->unsignedInteger('MaritalStatusCode')->nullable();
            // ดึงรายละเอียดอาชีพและคะแนนจาก H Meter ตาราง dbo.Occupation
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
            $table->decimal('Score', 18, 2)->nullable();
            $table->decimal('CreditLimitAmount', 18, 2)->nullable();
            $table->decimal('CreditUsedAmount', 18, 2)->nullable();
            // ข้อมูลนิติบุคคลอยู่นอกขอบเขต จึงกำหนดเป็น 0
            $table->unsignedInteger('OrganizationBranchTypeCode')->default(0);
            $table->string('Email', 50)->nullable();
            // ตรวจสอบผู้บันทึกจาก H Meter ตาราง dbo.User
            $table->unsignedInteger('InsertUserId')->nullable();
            $table->date('InsertDate')->nullable();
            $table->unsignedInteger('GenderCode')->nullable();
            // ระบบประกอบข้อความที่อยู่จากข้อมูลใน customer_addresses
            $table->text('CurrentAddressAsText')->nullable();
            // ดึงประเภทที่อยู่และคะแนนจาก H Meter ตาราง dbo.AddressType
            $table->unsignedInteger('AddressTypeCode')->nullable();
            // คำนวณคะแนนจาก H Meter ตาราง dbo.AgeRange และ dbo.NetIncomeRange
            $table->unsignedInteger('AgeRangeScore')->nullable();
            $table->unsignedInteger('NetIncomeRangeScore')->nullable();
            // คะแนนจาก H Meter ตาราง dbo.AddressType, dbo.Occupation และ dbo.MaritalStatus
            $table->unsignedInteger('AddressTypeScore')->nullable();
            $table->unsignedInteger('OccupationScore')->nullable();
            $table->unsignedInteger('MaritalStatusScore')->nullable();
            // สถานะและบทบาทลูกค้าที่กำหนดจากระบบนี้
            $table->boolean('IsCreditor')->default(false);
            $table->boolean('IsDebtor')->default(false);
            $table->unsignedInteger('CreditorCreditDay')->nullable();
            $table->decimal('CreditorCreditAmount', 18, 2)->nullable();
            $table->boolean('IsDealer')->default(false);
            $table->boolean('IsInsurer')->default(false);
            $table->boolean('IsOutsource')->default(false);
            $table->boolean('IsLawyer')->default(false);
            $table->string('BankCode', 5)->nullable();
            $table->string('BankBookCode', 20)->nullable();
            $table->string('BankBookBranch', 50)->nullable();
            $table->string('TitleDesc', 100)->nullable();
            $table->string('MaritalStatusDesc', 100)->nullable();
            $table->string('OccupationDesc', 255)->nullable();
            // อ้างอิง PhoneId ของเบอร์โทรหลักจากตาราง customer_phones
            $table->unsignedInteger('MobileTelephoneId')->nullable();
            $table->string('Race', 50)->nullable();
            // ดึงข้อมูลประเภทธุรกิจจาก H Meter ตาราง dbo.TypeOfBusiness
            $table->unsignedInteger('TypeOfBusinessId')->nullable();
            $table->string('TypeOfBusinessName', 255)->nullable();
            $table->string('TypeOfBusinessBotCode', 50)->nullable();
            $table->string('OtherOccupationDesc', 255)->nullable();
            // ตรวจสอบสภาพการทำงานจาก H Meter ตาราง dbo.WorkingCondition
            $table->unsignedInteger('WorkingConditionId')->nullable();
            $table->unsignedInteger('CustomerRegistrationTypeCode')->default(0);
            $table->string('RegistrationTypeValue', 255)->nullable();
            $table->string('OrganizationNameNcb', 255)->nullable();

            $table->boolean('IsOutsourceLegal')->default(false);

            $table->date('IdentityCardEffectiveDate')->nullable();
            $table->date('IdentityCardExpireDate')->nullable();
            $table->boolean('IsSupplier')->default(false);
            $table->boolean('IsFinance')->default(false);

            // ใช้เลขเดียวกับ CustomerNo สำหรับอ้างอิงลูกค้า
            $table->string('CustomerRefNo', 16)->nullable();


            $table->index('IdentityCardId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
