<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('consent_forms', function (Blueprint $table) {
            $table->id();
            $table->date('app_date')->nullable();
            $table->string('app_no', 50)->nullable();
            $table->string('officer_name', 100)->nullable();
            $table->string('officer_phone', 20)->nullable();
            
            // Personal
            $table->string('title', 50)->nullable();
            $table->string('name', 150);
            $table->string('name_en', 150)->nullable();
            $table->date('dob')->nullable();
            $table->string('id_card', 20)->nullable();
            $table->string('gender', 10)->nullable();
            $table->integer('age')->nullable();
            $table->string('nationality', 50)->nullable();
            $table->string('marital_status', 50)->nullable();
            $table->string('education', 50)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->decimal('income', 12, 2)->nullable();
            $table->decimal('extra_income', 12, 2)->nullable();
            $table->string('extra_income_source', 100)->nullable();
            $table->decimal('business_income', 12, 2)->nullable();
            $table->decimal('average_monthly_income', 12, 2)->nullable();
            $table->string('has_other_debts', 10)->nullable();
            $table->decimal('other_debt_installment', 12, 2)->nullable();
            $table->string('has_existing_loan', 10)->nullable();
            
            // Spouse
            $table->string('spouse_title', 50)->nullable();
            $table->string('spouse_name', 150)->nullable();
            $table->string('spouse_phone', 20)->nullable();
            $table->string('spouse_mobile', 20)->nullable();
            $table->string('spouse_education', 50)->nullable();
            $table->string('spouse_occupation', 100)->nullable();
            $table->string('spouse_company', 100)->nullable();
            $table->decimal('spouse_income', 12, 2)->nullable();
            
            // Address
            $table->string('dwelling_type', 100)->nullable();
            $table->string('residence_status', 100)->nullable();
            $table->decimal('residence_rent_amount', 12, 2)->nullable();
            $table->integer('residence_years')->nullable();
            $table->string('address_no', 50)->nullable();
            $table->string('address_floor', 30)->nullable();
            $table->string('address_village', 100)->nullable();
            $table->string('address_building', 100)->nullable();
            $table->string('address_soi', 100)->nullable();
            $table->string('address_road', 100)->nullable();
            $table->string('address_subdistrict', 100)->nullable();
            $table->string('address_district', 100)->nullable();
            $table->string('address_province', 100)->nullable();
            $table->string('address_postal', 20)->nullable();
            $table->string('phone_home', 20)->nullable();
            $table->string('phone_mobile', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('line_id', 100)->nullable();
            
            // Work
            $table->boolean('use_home_address')->default(false);
            $table->string('company_type', 100)->nullable();
            $table->string('company_name', 100)->nullable();
            $table->string('business_type', 100)->nullable();
            $table->string('work_occupation', 100)->nullable();
            $table->string('work_position', 100)->nullable();
            $table->integer('work_years')->nullable();
            $table->integer('work_months')->nullable();
            $table->string('work_address_no', 50)->nullable();
            $table->string('work_address_floor', 30)->nullable();
            $table->string('work_address_village', 100)->nullable();
            $table->string('work_address_building', 100)->nullable();
            $table->string('work_address_soi', 100)->nullable();
            $table->string('work_address_road', 100)->nullable();
            $table->string('work_address_subdistrict', 100)->nullable();
            $table->string('work_address_district', 100)->nullable();
            $table->string('work_address_province', 100)->nullable();
            $table->string('work_address_postal', 20)->nullable();
            $table->string('work_phone', 20)->nullable();
            
            // Previous work
            $table->string('previous_company_name', 100)->nullable();
            $table->string('previous_business_type', 100)->nullable();
            $table->string('previous_position', 100)->nullable();
            $table->decimal('previous_income', 12, 2)->nullable();
            $table->integer('previous_work_years')->nullable();
            $table->string('previous_phone', 20)->nullable();
            
            // Document delivery
            $table->string('document_delivery', 50)->nullable();
            $table->string('document_email', 100)->nullable();
            
            // Reference
            $table->string('ref_name', 150)->nullable();
            $table->string('ref_relation', 100)->nullable();
            $table->string('ref_address_no', 50)->nullable();
            $table->string('ref_address_floor', 30)->nullable();
            $table->string('ref_address_village', 100)->nullable();
            $table->string('ref_address_building', 100)->nullable();
            $table->string('ref_address_soi', 100)->nullable();
            $table->string('ref_address_road', 100)->nullable();
            $table->string('ref_address_subdistrict', 100)->nullable();
            $table->string('ref_address_district', 100)->nullable();
            $table->string('ref_address_province', 100)->nullable();
            $table->string('ref_address_postal', 20)->nullable();
            $table->string('ref_phone_home', 20)->nullable();
            $table->string('ref_phone_mobile', 20)->nullable();
            $table->string('ref_email', 100)->nullable();
            $table->string('ref_line_id', 100)->nullable();
            
            // Loan
            $table->integer('loan_term')->nullable();
            $table->string('loan_amount_type', 20)->nullable();
            $table->decimal('custom_loan_amount', 12, 2)->nullable();
            $table->string('loan_purpose', 150)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_branch', 100)->nullable();
            $table->string('account_name', 150)->nullable();
            $table->string('account_type', 50)->nullable();
            $table->string('account_number', 50)->nullable();
            
            // Consent & Signature
            $table->boolean('signed')->default(false);
            $table->dateTime('signed_at')->nullable();
            $table->text('signature_data')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('consent_forms');
    }
};
