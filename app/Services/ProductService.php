<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    Company,
    Customer,
    Product,
    ProductGallery,
    Booking,
    FileManager,
    ProductVariant,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ProductService
{

    public static function createProduct( $request ) {

        $validator = Validator::make( $request->all(), [
            'product_type' => [ 'nullable' ],
            'product_code' => [ 'nullable', 'unique:products,product_code' ],
            'title' => [ 'nullable' ],
            'workmanship' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'sale_unit' => [ 'nullable' ],
            'purchase_unit' => [ 'nullable' ],
            'cost' => [ 'nullable', 'numeric', 'min:0' ],
            'price' => [ 'nullable', 'numeric', 'min:0' ],
            'alert_quantity' => [ 'nullable', 'numeric', 'min:0' ],
            'quantity' => [ 'nullable', 'numeric', 'min:0' ],
            'tax_method' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'featured' => [ 'nullable' ],
            'warehouse' => [ 'nullable' ],
            'warehouse.*.price' => [ 'nullable', 'numeric', 'min:0' ],
            'imei' => [ 'nullable' ],
            'serial_number' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'variants' => [ 'nullable' ],
            'variants.*.name' => [ 'required' ],
            'variants.*.price' => [ 'nullable', 'numeric', 'min:0' ],
            'variants.*.quantity' => [ 'nullable', 'numeric', 'min:0' ],
            'has_promotion' => [ 'nullable' ],
            'promotion_start' => [ 'nullable' ],
            'promotion_end' => [ 'nullable' ],
            'promotion_price' => [ 'nullable', 'numeric', 'min:0' ],
            'brand' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Brand::find($value)) {
                    $fail(__('The selected brand is invalid.'));
                }
            }],
            'supplier' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Supplier::find($value)) {
                    $fail(__('The selected supplier is invalid.'));
                }
            }],
            'category' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Category::find($value)) {
                    $fail(__('The selected category is invalid.'));
                }
            }],
            'unit' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Unit::find($value)) {
                    $fail(__('The selected unit is invalid.'));
                }
            }],
            'tax_method' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\TaxMethod::find($value)) {
                    $fail(__('The selected unit is invalid.'));
                }
            }],
            'workmanship' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Workmanship::find($value)) {
                    $fail(__('The selected unit is invalid.'));
                }
            }],
        ] );

        $attributeName = [
            'product_type' => __('product.product_type'),
            'product_code' => __('product.product_code'),
            'title' => __('product.title'),
            'workmanship' => __('product.workmanship'),
            'address_1' => __('product.address_1'),
            'address_2' => __('product.address_2'),
            'state' => __('product.state'),
            'city' => __('product.city'),
            'postcode' => __('product.postcode'),
            'sale_unit' => __('product.sale_unit'),
            'purchase_unit' => __('product.purchase_unit'),
            'cost' => __('product.cost'),
            'price' => __('product.price'),
            'alert_quantity' => __('product.alert_quantity'),
            'quantity' => __('product.quantity'),
            'tax_method' => __('product.tax_method'),
            'description' => __('product.description'),
            'featured' => __('product.featured'),
            'warehouse' => __('product.warehouse'),
            'imei' => __('product.imei'),
            'serial_number' => __('product.serial_number'),
            'image' => __('product.image'),
            'variants' => __('product.variants'),
            'has_promotion' => __('product.has_promotion'),
            'promotion_start' => __('product.promotion_start'),
            'promotion_end' => __('product.promotion_end'),
            'promotion_price' => __('product.promotion_price'),
            'brand' => __('product.brand'),
            'supplier' => __('product.supplier'),
            'category' => __('product.category'),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        if( $request->brand == 'null' ){
            $request->merge( ['brand' => null] );
        }

        if( $request->supplier == 'null' ){
            $request->merge( ['supplier' => null] );
        }

        if( $request->category == 'null' ){
            $request->merge( ['category' => null] );
        }

        if( $request->unit == 'null' ){
            $request->merge( ['unit' => null] );
        }

        if( $request->workmanship == 'null' ){
            $request->merge( ['workmanship' => null] );
        }


        if( $request->tax_method == 'null' ){
            $request->merge( ['tax_method' => null] );
        }


        try {
            $productCreate = Product::create([
                'brand_id' => $request->brand,
                'supplier_id' => $request->supplier,
                'unit_id' => $request->unit,
                'tax_method_id' => $request->tax_method,
                'workmanship_id' => $request->workmanship,
                'title' => $request->title,
                'description' => $request->description,
                'product_code' => $request->product_code ?? Carbon::now()->timestamp,
                'barcode_symbology' => $request->barcode,
                'workmanship' => $request->workmanship,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'postcode' => $request->postcode,
                'state' => $request->state,
                'type' => $request->product_type,
                'purchase_unit' => $request->purchase_unit,
                'sale_unit' => $request->sale_unit,
                'price' => $request->price,
                'promotional_price' => $request->promotion_price,
                'promotion_start' => $request->promotion_start,
                'promotion_end' => $request->promotion_end,
                'cost' => $request->cost,
                'alert_quantity' => $request->alert_quantity,
                'quantity' => $request->quantity,
                'tax_method' => $request->tax_method ?? 1,
                'featured' => $request->featured,
                'imei' => $request->imei,
                'serial_number' => $request->serial_number,
                'status' => 10,
            ]);

            $productGalleries = explode( ',', $request->image );

            $files = FileManager::whereIn( 'id', $productGalleries )->get();

            if ( $files ) {
                foreach ( $files as $file ) {

                    $fileName = explode( '/', $file->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product-galleries/' . $productCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );

                    $createProductGallery = ProductGallery::create( [
                        'product_id' => $productCreate->id,
                        'image' => $target,
                        'status' => 10,
                    ] );

                    $file->status = 10;
                    $file->save();

                }
            }

            if( $request->warehouse != null ) {
                foreach ( $request->warehouse as $key => $wh ) {
                    if (!$productCreate->warehouses()->where('warehouse_id', $key)->exists()) {
                        $productCreate->warehouses()->attach($key, ['price' => $wh['price'] ]);
                    }
                }
            }

            if( $request->category != null ) {
                if( is_array( $request->category ) ) {
                    foreach ( $request->category as $category ) {
                        if (!$productCreate->categories()->where('category_id', $category)->exists()) {
                            $productCreate->categories()->attach($category);
                        }
                    }
                }else{
                    $productCreate->categories()->attach($request->category);
                }
            }

            if( $request->variants ) {
                foreach ( $request->variants as $variant ) {
                    ProductVariant::create([
                        'product_id' => $productCreate->id,
                        'title' => $variant['name'],
                        'price' => $variant['price'],
                        'quantity' => $variant['quantity'],
                        'status' => 10,
                    ]);
                }
            }
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.products' ) ) ] ),
        ] );
    }
    
    public static function updateProduct( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
    
        $validator = Validator::make( $request->all(), [
            'product_type' => [ 'nullable' ],
            'product_code' => [ 'nullable', 'unique:products,product_code,' . $request->id ],
            'title' => [ 'nullable' ],
            'workmanship' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'sale_unit' => [ 'nullable' ],
            'purchase_unit' => [ 'nullable' ],
            'cost' => [ 'nullable', 'numeric', 'min:0' ],
            'price' => [ 'nullable', 'numeric', 'min:0' ],
            'alert_quantity' => [ 'nullable', 'numeric', 'min:0' ],
            'quantity' => [ 'nullable', 'numeric', 'min:0' ],
            'tax_method' => [ 'nullable' ],
            'description' => [ 'nullable' ],
            'featured' => [ 'nullable' ],
            'warehouse' => [ 'nullable' ],
            'warehouse.*.price' => [ 'nullable', 'numeric', 'min:0' ],
            'imei' => [ 'nullable' ],
            'serial_number' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'variants' => [ 'nullable' ],
            'variants.*.name' => [ 'required' ],
            'variants.*.price' => [ 'nullable', 'numeric', 'min:0' ],
            'variants.*.quantity' => [ 'nullable', 'numeric', 'min:0' ],
            'has_promotion' => [ 'nullable' ],
            'promotion_start' => [ 'nullable' ],
            'promotion_end' => [ 'nullable' ],
            'promotion_price' => [ 'nullable', 'numeric', 'min:0' ],
            'brand' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Brand::find($value)) {
                    $fail(__('The selected brand is invalid.'));
                }
            }],
            'supplier' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Supplier::find($value)) {
                    $fail(__('The selected supplier is invalid.'));
                }
            }],
            'category' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Category::find($value)) {
                    $fail(__('The selected category is invalid.'));
                }
            }],
            'unit' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Unit::find($value)) {
                    $fail(__('The selected unit is invalid.'));
                }
            }],
            'tax_method' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\TaxMethod::find($value)) {
                    $fail(__('The selected unit is invalid.'));
                }
            }],
            'workmanship' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'null' && $value !== '' && !\App\Models\Workmanship::find($value)) {
                    $fail(__('The selected unit is invalid.'));
                }
            }],
        ] );

        $attributeName = [
            'product_type' => __('product.product_type'),
            'product_code' => __('product.product_code'),
            'title' => __('product.title'),
            'workmanship' => __('product.workmanship'),
            'address_1' => __('product.address_1'),
            'address_2' => __('product.address_2'),
            'state' => __('product.state'),
            'city' => __('product.city'),
            'postcode' => __('product.postcode'),
            'sale_unit' => __('product.sale_unit'),
            'purchase_unit' => __('product.purchase_unit'),
            'cost' => __('product.cost'),
            'price' => __('product.price'),
            'alert_quantity' => __('product.alert_quantity'),
            'quantity' => __('product.quantity'),
            'tax_method' => __('product.tax_method'),
            'description' => __('product.description'),
            'featured' => __('product.featured'),
            'warehouse' => __('product.warehouse'),
            'imei' => __('product.imei'),
            'serial_number' => __('product.serial_number'),
            'image' => __('product.image'),
            'variants' => __('product.variants'),
            'has_promotion' => __('product.has_promotion'),
            'promotion_start' => __('product.promotion_start'),
            'promotion_end' => __('product.promotion_end'),
            'promotion_price' => __('product.promotion_price'),
            'brand' => __('product.brand'),
            'supplier' => __('product.supplier'),
            'category' => __('product.category'),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            if( $request->brand == 'null' ){
                $request->merge( ['brand' => null] );
            }
    
            if( $request->supplier == 'null' ){
                $request->merge( ['supplier' => null] );
            }
    
            if( $request->category == 'null' ){
                $request->merge( ['category' => null] );
            }
    
            if( $request->unit == 'null' ){
                $request->merge( ['unit' => null] );
            }

            if( $request->workmanship == 'null' ){
                $request->merge( ['workmanship' => null] );
            }
            
            if( $request->tax_method == 'null' ){
                $request->merge( ['tax_method' => null] );
            }
            
            $updateProduct = Product::find( $request->id );

            $updateProduct->brand_id = $request->brand ?? $updateProduct->brand_id;
            $updateProduct->supplier_id = $request->supplier ?? $updateProduct->supplier_id;
            $updateProduct->unit_id = $request->unit ?? $updateProduct->unit_id;
            $updateProduct->title = $request->title ?? $updateProduct->title;
            $updateProduct->description = $request->description ?? $updateProduct->description;
            $updateProduct->product_code = $request->product_code ?? $updateProduct->product_code;
            $updateProduct->barcode_symbology = $request->barcode ?? $updateProduct->barcode_symbology;
            $updateProduct->workmanship = $request->workmanship ?? $updateProduct->workmanship;
            $updateProduct->address_1 = $request->address_1 ?? $updateProduct->address_1;
            $updateProduct->address_2 = $request->address_2 ?? $updateProduct->address_2;
            $updateProduct->city = $request->city ?? $updateProduct->city;
            $updateProduct->postcode = $request->postcode ?? $updateProduct->postcode;
            $updateProduct->state = $request->state ?? $updateProduct->state;
            $updateProduct->type = $request->product_type ?? $updateProduct->type;
            $updateProduct->purchase_unit = $request->purchase_unit ?? $updateProduct->purchase_unit;
            $updateProduct->sale_unit = $request->sale_unit ?? $updateProduct->sale_unit;
            $updateProduct->price = $request->price ?? $updateProduct->price;
            $updateProduct->promotional_price = $request->promotion_price ?? $updateProduct->promotional_price;
            $updateProduct->promotion_start = $request->promotion_start ?? $updateProduct->promotion_start;
            $updateProduct->promotion_end = $request->promotion_end ?? $updateProduct->promotion_end;
            $updateProduct->cost = $request->cost ?? $updateProduct->cost;
            $updateProduct->alert_quantity = $request->alert_quantity ?? $updateProduct->alert_quantity;
            $updateProduct->quantity = $request->quantity ?? $updateProduct->quantity;
            $updateProduct->tax_method = $request->tax_method ?? $updateProduct->tax_method ?? 1;
            $updateProduct->featured = $request->featured ?? $updateProduct->featured;
            $updateProduct->imei = $request->imei ?? $updateProduct->imei;
            $updateProduct->serial_number = $request->serial_number ?? $updateProduct->serial_number;
            $updateProduct->tax_method_id = $request->tax_method ?? $updateProduct->tax_method_id;
            $updateProduct->workmanship_id = $request->tax_method ?? $updateProduct->workmanship_id;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $file ) {

                    $fileName = explode( '/', $file->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'product-galleries/' . $updateProduct->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );

                    $createProductGallery = ProductGallery::create( [
                        'product_id' => $updateProduct->id,
                        'image' => $target,
                        'status' => 10,
                    ] );

                    $file->status = 10;
                    $file->save();

                }
            }

            if( $request->warehouse != null ) {

                $updateProduct->warehouses()->detach();

                foreach ( $request->warehouse as $key => $wh ) {
                    if (!$updateProduct->warehouses()->where('warehouse_id', $key)->exists()) {
                        $updateProduct->warehouses()->attach($key, ['price' => $wh['price'] ]);
                    }
                }
            }

            if( $request->category != null ) {

                $updateProduct->categories()->detach();

                if( is_array( $request->category ) ) {
                    foreach ( $request->category as $category ) {
                        if (!$updateProduct->categories()->where('category_id', $category)->exists()) {
                            $updateProduct->categories()->attach($category);
                        }
                    }
                }else{
                    $updateProduct->categories()->attach($request->category);
                }
            }

            if( $request->variants ) {

                $productVariantIds = $updateProduct->variants->pluck('id')->toArray();

                $requestVariantIds = collect($request->variants)->pluck('id')->toArray();

                $missingVariants = array_diff($productVariantIds, $requestVariantIds);

                if (!empty($missingVariants)) {
                    foreach ($missingVariants as $missingVariantId) {
                        $variantToDelete = $updateProduct->variants()->find($missingVariantId);
                        if ($variantToDelete) {
                            $variantToDelete->delete();
                        }
                    }
                }

                
                foreach ( $request->variants as $variant ) {

                    if( $variant['id'] == 0 ) {
                        ProductVariant::create([
                            'product_id' => $updateProduct->id,
                            'title' => $variant['name'],
                            'price' => $variant['price'],
                            'quantity' => $variant['quantity'],
                            'status' => 10,
                        ]);
                    }else {
                        $updateProductVariant = ProductVariant::find( $variant['id'] );
                        $updateProductVariant->title = $variant['name'];
                        $updateProductVariant->price = $variant['price'];
                        $updateProductVariant->quantity = $variant['quantity'];
                        $updateProductVariant->save();
                    }

                }
            }

            $updateProduct->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.products' ) ) ] ),
        ] );
    }

     public static function allProducts( $request ) {

        $products = Product::select( 'products.*' )->with(['variants','bundles', 'categories', 'warehouses', 'galleries','brand','supplier', 'unit']);

        $filterObject = self::filter( $request, $products );
        $product = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $product->orderBy( 'products.created_at', $dir );
                    break;
                case 1:
                    $product->orderBy( 'products.id', $dir );
                    break;
                case 3:
                    $product->orderBy( 'products.title', $dir );
                    break;
                case 4:
                    $product->orderBy( 'products.description', $dir );
                    break;
            }
        }

            $productCount = $product->count();

            $limit = $request->length;
            $offset = $request->start;

            $products = $product->skip( $offset )->take( $limit )->get();

            if ( $products ) {
                $products->append( [
                    'encrypted_id',
                    'stock_worth'
                ] );

                foreach ($products as $product) {
                    if( $product->galleries ){
                        $product->galleries->append( [
                            'image_path',
                        ] );
                    }
                }
            }

            $totalRecord = Product::count();

            $data = [
                'products' => $products,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $productCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'products.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'products.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->product_code ) ) {
            $model->where( 'products.product_code', 'LIKE', '%' . $request->product_code . '%' );
            $filter = true;
        }
        
        if ( !empty( $request->id ) ) {
            $model->where( 'products.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->brand)) {
            $model->whereHas('brand', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->brand . '%');
            });
            $filter = true;
        }

        if (!empty($request->category)) {
            $model->whereHas('categories', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->category . '%');
            });
            $filter = true;
        }

        if (!empty($request->unit)) {
            $model->whereHas('unit', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->unit . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'products.status', $request->status );
            $filter = true;
        }

        

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneProduct( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $product = Product::select( 'products.*' )->with(['variants','bundles', 'categories', 'warehouses', 'galleries','brand','supplier', 'unit','workmanship','taxMethod'])
        ->find( $request->id );

        if ( $product ) {
            $product->append( [
                'encrypted_id',
                'stock_worth'
            ] );

            if( $product->galleries ){
                $product->galleries->append( [
                    'image_path',
                ] );
            }
        }
        
        return response()->json( $product );
    }

    public static function deleteProduct( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'product.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Product::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.products' ) ) ] ),
        ] );
    }

    public static function updateProductStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateProduct = Product::find( $request->id );
            $updateProduct->status = $updateProduct->status == 10 ? 20 : 10;

            $updateProduct->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'product' => $updateProduct,
                    'message_key' => 'update_product_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_product_failed',
            ], 500 );
        }
    }

    public static function removeProductGalleryImage( $request ) {

        $updateProduct = ProductGallery::find( $request->id );
        $updateProduct->delete();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'product.galleries' ) ) ] ),
        ] );
    }

    public static function ckeUpload( $request ) {

        $file = $request->file( 'file' )->store( 'product/ckeditor', [ 'disk' => 'public' ] );

        $data = [
            'url' => asset( 'storage/' . $file ),
        ];

        return response()->json( $data );
    }

    public static function generateProductCode()
    {
        return response()->json(['product_code' => Carbon::now()->timestamp]);
    }
}