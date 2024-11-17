<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         //
         $units = [
            [
                'title' => 'Once',
                'description' => 'Once Info',
                'status' => 10,
            ],
            [
                'title' => 'Unit',
                'description' => 'Unit Info',
                'status' => 10,
            ],
            [
                'title' => 'Bundle',
                'description' => 'Bundle Info',
                'status' => 10,
            ],
        ];

        foreach ($units as $unit) {

            $unitAttribute = [
                'title' => $unit['title'],
                'description' => $unit['description'],
                'status' => $unit['status'],
            ];

            $createUnit = Unit::create($unitAttribute);
        }
    }
}
