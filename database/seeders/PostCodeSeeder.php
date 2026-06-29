<?php

namespace Database\Seeders;

use App\Models\PostCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostCodeSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('resources/views/MS_Post code_R2.csv');

        if (!is_file($csvPath)) {
            return;
        }

        if (PostCode::query()->exists()) {
            return;
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            return;
        }

        DB::disableQueryLog();

        $header = fgetcsv($handle);
        if (is_array($header) && count($header) > 0) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        }

        $batch = [];
        $batchSize = 1000;
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            if (!is_array($row) || count($row) < 5) {
                continue;
            }

            $postCode = trim((string) $row[0]);
            $district = trim((string) $row[1]);
            $city = trim((string) $row[2]);
            $province = trim((string) $row[3]);
            $countryCode = trim((string) $row[4]) ?: 'TH';

            if ($postCode === '' || $district === '' || $city === '' || $province === '') {
                continue;
            }

            $batch[] = [
                'post_code' => $postCode,
                'district' => $district,
                'city' => $city,
                'province' => $province,
                'country_code' => $countryCode,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('post_codes')->insertOrIgnore($batch);
                $batch = [];
            }
        }

        fclose($handle);

        if (count($batch) > 0) {
            DB::table('post_codes')->insertOrIgnore($batch);
        }
    }
}

