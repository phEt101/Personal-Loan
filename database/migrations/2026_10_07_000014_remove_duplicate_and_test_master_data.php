<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (range(19, 36) as $duplicateId) {
            DB::table('customers')
                ->where('WorkingConditionId', $duplicateId)
                ->update(['WorkingConditionId' => $duplicateId - 18]);
        }

        DB::table('working_conditions')
            ->whereBetween('WorkingConditionId', [19, 36])
            ->delete();

        DB::table('sub_districts')->where('ProvinceCode', '99')->delete();
        DB::table('districts')->where('ProvinceCode', '99')->delete();
        DB::table('provinces')->whereIn('ProvinceCode', ['78', '99'])->delete();
    }

    public function down(): void
    {
        // Removed records were invalid or duplicated master data and should not be restored.
    }
};
