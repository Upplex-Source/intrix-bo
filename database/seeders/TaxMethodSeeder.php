<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxMethod;

class TaxMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $taxmethods = [
            [
                'title' => 'SST',
                'description' => 'SST',
                'tax_percentage' => 6,
                'status' => 10,
            ],
            [
                'title' => 'Service Tax',
                'description' => 'TaxMethod Info',
                'tax_percentage' => 10,
                'status' => 10,
            ],
        ];

        foreach ($taxmethods as $taxMethod) {

            $taxMethodAttribute = [
                'title' => $taxMethod['title'],
                'description' => $taxMethod['description'],
                'tax_percentage' => $taxMethod['tax_percentage'],
                'status' => $taxMethod['status'],
            ];

            $createTaxMethod = TaxMethod::create($taxMethodAttribute);
        }
    }
}
