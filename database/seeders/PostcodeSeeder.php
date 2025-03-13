<?php

namespace Database\Seeders;

use App\Models\Postcode;
use App\Models\Subcode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostcodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Postcode::truncate();
        Subcode::truncate();
        $csvFile = fopen(base_path('database/data/postcodes.csv'), 'r');
        $firstline = true;
        $this->command->getOutput()->progressStart(count(file(base_path('database/data/postcodes.csv'))));

        while (($data = fgetcsv($csvFile, 50, ',')) !== false) {
            if (! $firstline) {
                Postcode::create([
                    'postcode' => $data['1'],
                    'latitude' => $data['2'],
                    'longitude' => $data['3'],

                    'district' =>
                    preg_replace('/[^A-Z].*/', '', $data['1']),
                    'outcode' => substr($data['1'], 0, -3),
                    'subcode' => substr($data['1'], 0, -2),
                ]);

                Subcode::updateOrCreate([
                    'subcode' => substr($data['1'], 0, -2),
                    'outcode' => substr($data['1'], 0, -3),
                    'district' =>
                    preg_replace('/[^A-Z].*/', '', $data['1']),
                ]);

                $this->command->getOutput()->progressAdvance();
            }
            $firstline = false;
        }
        $this->command->getOutput()->progressFinish();

        fclose($csvFile);
    }
}
