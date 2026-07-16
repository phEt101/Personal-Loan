<?php

namespace Database\Seeders;

use App\Models\PostCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostCodeSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = public_path('file/MS_Post code_R2.csv');

        if (!$this->canSeed($csvPath)) {
            return;
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            return;
        }

        DB::disableQueryLog();

        $header = fgetcsv($handle);
        if (is_array($header) && !empty($header)) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        }

        $batch = [];
        $batchSize = 1000;
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            $mapped = $this->mapRowToPayload($row, $now);
            if ($mapped === null) {
                continue;
            }

            $batch[] = $mapped;

            if (count($batch) >= $batchSize) {
                DB::table('post_codes')->insertOrIgnore($batch);
                $batch = [];
            }
        }

        fclose($handle);

        if (!empty($batch)) {
            DB::table('post_codes')->insertOrIgnore($batch);
        }
    }

    private function canSeed(string $csvPath): bool
    {
        return is_file($csvPath) && !PostCode::query()->exists();
    }

    private function mapRowToPayload(array $row, $now): ?array
    {
        if (count($row) < 5) {
            return null;
        }

        $postCode = trim((string) $row[0]);
        $district = trim((string) $row[1]);
        $city = trim((string) $row[2]);
        $province = trim((string) $row[3]);

        if ($postCode === '' || $district === '' || $city === '' || $province === '') {
            return null;
        }

        return [
            'post_code' => $postCode,
            'district' => $district,
            'city' => $city,
            'province' => $province,
            'country_code' => trim((string) $row[4]) ?: 'TH',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
