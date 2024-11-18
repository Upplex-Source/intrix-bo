<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $suppliers = [
            [
                'title' => 'Supplier One',
                'description' => 'Supplier Info',
                'status' => 10,
            ],
            [
                'title' => 'Supplier Two',
                'description' => 'Supplier Info',
                'status' => 10,
            ],
        ];

        foreach ($suppliers as $Supplier) {

            $SupplierAttribute = [
                'title' => $Supplier['title'],
                'description' => $Supplier['description'],
                'status' => $Supplier['status'],
            ];

            $createSupplier = Supplier::create($SupplierAttribute);
        }
    }
}
