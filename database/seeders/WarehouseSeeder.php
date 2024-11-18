<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         //
         $warehouses = [
            [
                'title' => 'Warehouse KL',
                'description' => 'KL Branch',
                'status' => 10,
            ],
            [
                'title' => 'Warehouse Selangor',
                'description' => 'Selangor Branch',
                'status' => 10,
            ],
            [
                'title' => 'Warehouse Putrajaya',
                'description' => 'Putrajaya Branch',
                'status' => 10,
            ],
        ];

        foreach ($warehouses as $warehouse) {

            $warehouseAttribute = [
                'title' => $warehouse['title'],
                'description' => $warehouse['description'],
                'status' => $warehouse['status'],
            ];

            $createWarehouse = Warehouse::create($warehouseAttribute);
        }
    }
}
