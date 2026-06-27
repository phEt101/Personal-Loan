<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentForm extends Model
{
    protected $fillable = [
        'app_date', 'app_no', 'officer_name', 'officer_phone',
        'title', 'name', 'name_en', 'dob', 'id_card', 'gender', 'age', 'nationality', 'marital_status',
        'education', 'occupation', 'income', 'extra_income', 'extra_income_source', 'business_income',
        'average_monthly_income', 'has_other_debts', 'other_debt_installment', 'has_existing_loan',
        // Spouse
        'spouse_title', 'spouse_name', 'spouse_phone', 'spouse_mobile', 'spouse_education',
        'spouse_occupation', 'spouse_company', 'spouse_income',
        // Address
        'dwelling_type', 'residence_status', 'residence_rent_amount', 'residence_years',
        'address_no', 'address_floor', 'address_village', 'address_building', 'address_soi',
        'address_road', 'address_subdistrict', 'address_district', 'address_province', 'address_postal',
        'phone_home', 'phone_mobile', 'email', 'line_id',
        // Work
        'use_home_address', 'company_type', 'company_name', 'business_type',
        'work_occupation', 'work_position', 'work_years', 'work_months', 'work_address_no',
        'work_address_floor', 'work_address_village', 'work_address_building', 'work_address_soi',
        'work_address_road', 'work_address_subdistrict', 'work_address_district',
        'work_address_province', 'work_address_postal', 'work_phone',
        // Previous work
        'previous_company_name', 'previous_business_type', 'previous_position',
        'previous_income', 'previous_work_years', 'previous_phone',
        // Document delivery
        'document_delivery', 'document_email',
        // Reference
        'ref_name', 'ref_relation', 'ref_address_no', 'ref_address_floor',
        'ref_address_village', 'ref_address_building', 'ref_address_soi', 'ref_address_road',
        'ref_address_subdistrict', 'ref_address_district', 'ref_address_province',
        'ref_address_postal', 'ref_phone_home', 'ref_phone_mobile', 'ref_email', 'ref_line_id',
        // Loan
        'loan_term', 'loan_amount_type', 'custom_loan_amount', 'loan_purpose', 'bank_name',
        'bank_branch', 'account_name', 'account_type', 'account_number',
        // Consent & Signature
        'signed', 'signed_at', 'signature_data',
        // Status
        'status'
    ];

    protected $casts = [
        'signed' => 'boolean',
        'signed_at' => 'date',
        'app_date' => 'date',
        'dob' => 'date',
        'use_home_address' => 'boolean'
    ];
}
