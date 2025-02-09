<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

use Illuminate\Support\Facades\{
    DB,
};

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            [
                'code' => '5-IN-1', 
                'title' => 'Intrix One Tap 5 in 1', 
                'description' => 'Intrix One Tap Lite',
                'price' => 7500,
                'image' => null,
                'discount_price' => 7500,
                'status' => 10,
            ],
            [
                'code' => '4-IN-1', 
                'title' => 'Intrix One Tap 4 in 1', 
                'description' => 'Intrix One Tap Lite',
                'price' => 5200,
                'image' => null,
                'discount_price' => 5200,
                'status' => 10,
            ],
            [
                'code' => '2-IN-1', 
                'title' => 'Intrix One Tap 2 in 1', 
                'description' => 'Intrix One Tap Lite',
                'price' => 4500,
                'image' => null,
                'discount_price' => 4500,
                'status' => 10,
            ],
            [
                'code' => 'LITE', 
                'title' => 'Intrix One Tap Lite', 
                'description' => 'Intrix One Tap Lite',
                'price' => 3988,
                'image' => null,
                'discount_price' => 3988,
                'status' => 10,
            ],
        ];

        DB::beginTransaction();

        $insertedProducts = [];

        foreach ($products as $product) {
            $inserted = Product::create($product);
            $insertedProducts[] = $inserted;
        }
        
        foreach ($insertedProducts as  $index => $product) {

            switch ($product) {
                case $index == 0:

                    for ($i=0; $i < 2; $i++) { 

                        $color = $i == 0 ? 'CHROME' : 'MATTE BLACK';

                        ProductVariant::create([
                            'product_id' => $product->id,
                            'title' => $color,
                            'description' => $color . ' Tap',
                            'color' =>$color,
                            'image' => null,
                            'price' => 7500,
                            'discount_price' => 7500,
                            'installment_price' => 7500,
                            'installment_rate' => 5,
                            'status' => 10,
                            'brochure' => null,
                            'sku' => null,
                            'specification' => null,
                            'features' => null,
                            'whats_included' => null,
                            'upfront' => 7500,
                            'monthly' => 7500,
                            'outright' => 7500,
                        ]);
                    }

                break;

                case $index == 1:

                    for ($i=0; $i < 4; $i++) { 

                        $color = 'CHROME';

                        switch ($i) {
                            case 0:
                                $color = 'CHROME';
                                break;

                            case 1:
                                $color = 'SATIN GOLD';
                                break;
                                
                            case 2:
                                $color = 'GUNMETAL GREY';
                                break;

                            case 3:
                                $color = 'MATTE BLACK';
                                break;
                            
                            default:
                                # code...
                                break;
                        }

                        ProductVariant::create([
                            'product_id' => $product->id,
                            'title' => $color,
                            'description' => $color . ' Tap',
                            'color' =>$color,
                            'image' => null,
                            'price' => 5200,
                            'discount_price' => 5200,
                            'installment_price' => 5200,
                            'installment_rate' => 5,
                            'status' => 10,
                            'brochure' => null,
                            'sku' => null,
                            'specification' => null,
                            'features' => null,
                            'whats_included' => null,
                            'upfront' => 5200,
                            'monthly' => 5200,
                            'outright' => 5200,
                        ]);
                    }

                break;

                case $index == 2:

                    for ($i=0; $i < 2; $i++) { 

                        $color = 'CHROME';

                        switch ($i) {
                            case 0:
                                $color = 'CHROME';
                                break;

                            case 1:
                                $color = 'MATTE BLACK';
                                break;
                            
                            default:
                                # code...
                                break;
                        }

                        ProductVariant::create([
                            'product_id' => $product->id,
                            'title' => $color,
                            'description' => $color . ' Tap',
                            'color' =>$color,
                            'image' => null,
                            'price' => 4500,
                            'discount_price' => 4500,
                            'installment_price' => 4500,
                            'installment_rate' => 5,
                            'status' => 10,
                            'brochure' => null,
                            'sku' => null,
                            'specification' => null,
                            'features' => null,
                            'whats_included' => null,
                            'upfront' => 4500,
                            'monthly' => 4500,
                            'outright' => 4500,
                        ]);
                    }

                break;

                case $index == 3:

                    $color = 'CHROME';

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'title' => $color,
                        'description' => $color . ' Tap',
                        'color' =>$color,
                        'image' => null,
                        'price' => 3988,
                        'discount_price' => 3988,
                        'installment_price' => 3988,
                        'installment_rate' => 5,
                        'status' => 10,
                        'brochure' => null,
                        'sku' => null,
                        'specification' => null,
                        'features' => null,
                        'whats_included' => null,
                        'upfront' => 3988,
                        'monthly' => 3988,
                        'outright' => 3988,
                    ]);

                break;
            
                default:
                    # code...
                    break;
            }

        }

        DB::commit();
    }
}
