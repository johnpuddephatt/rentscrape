<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Outcode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OutcodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Outcode::truncate();
        District::truncate();
        $csvFile = fopen(base_path('database/data/outcodes.csv'), 'r');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 50, ',')) !== false) {
            if (! $firstline) {
                Outcode::create([
                    'outcode' => $data['1'],
                    'latitude' => $data['2'],
                    'longitude' => $data['3'],
                    'district' => preg_replace('/[^A-Z].*/', '', $data['1']),
                ]);

                District::updateOrCreate([
                    'district' => preg_replace('/[^A-Z].*/', '', $data['1']),
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}
