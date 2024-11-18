<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $brands = [
            [
                'title' => 'Brand One',
                'description' => 'Brand Info',
                'status' => 10,
            ],
            [
                'title' => 'Brand Two',
                'description' => 'Brand Info',
                'status' => 10,
            ],
        ];

        foreach ($brands as $Brand) {

            $BrandAttribute = [
                'title' => $Brand['title'],
                'description' => $Brand['description'],
                'status' => $Brand['status'],
            ];

            $createBrand = Brand::create($BrandAttribute);
        }
    }
}
