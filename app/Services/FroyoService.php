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
    Froyo,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FroyoService
{

    public static function createFroyo( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'code' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'ingredients' => [ 'nullable' ],
            'nutritional_values' => [ 'nullable' ],
            'price' => [ 'required', 'min:0' ],
        ] );

        $attributeName = [
            'title' => __( 'froyo.title' ),
            'description' => __( 'froyo.description' ),
            'image' => __( 'froyo.image' ),
            'code' => __( 'froyo.code' ),
            'ingredients' => __( 'froyo.ingredients' ),
            'nutritional_values' => __( 'froyo.nutritional_values' ),
            'price' => __( 'froyo.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $froyoCreate = Froyo::create([
                'title' => $request->title,
                'description' => $request->description,
                'code' => $request->code,
                'price' => $request->price,
                'ingredients' => $request->ingredients,
                'nutritional_values' => $request->nutritional_values,
            ]);

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'froyo/' . $froyoCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $froyoCreate->image = $target;
                   $froyoCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.froyos' ) ) ] ),
        ] );
    }
    
    public static function updateFroyo( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'code' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'ingredients' => [ 'nullable' ],
            'nutritional_values' => [ 'nullable' ],
            'price' => [ 'required', 'min:0' ],
        ] );

        $attributeName = [
            'title' => __( 'froyo.title' ),
            'description' => __( 'froyo.description' ),
            'image' => __( 'froyo.image' ),
            'code' => __( 'froyo.code' ),
            'ingredients' => __( 'froyo.ingredients' ),
            'nutritional_values' => __( 'froyo.nutritional_values' ),
            'price' => __( 'froyo.price' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateFroyo = Froyo::find( $request->id );
    
            $updateFroyo->title = $request->title;
            $updateFroyo->description = $request->description;
            $updateFroyo->ingredients = $request->ingredients;
            $updateFroyo->nutritional_values = $request->nutritional_values;
            $updateFroyo->code = $request->code;
            $updateFroyo->price = $request->price;

            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'froyo/' . $updateFroyo->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateFroyo->image = $target;
                   $updateFroyo->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateFroyo->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.froyos' ) ) ] ),
        ] );
    }

    public static function allFroyos( $request ) {

        $froyos = Froyo::select( 'froyos.*');

        $filterObject = self::filter( $request, $froyos );
        $froyo = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $froyo->orderBy( 'froyos.created_at', $dir );
                    break;
                case 2:
                    $froyo->orderBy( 'froyos.title', $dir );
                    break;
                case 3:
                    $froyo->orderBy( 'froyos.description', $dir );
                    break;
            }
        }

            $froyoCount = $froyo->count();

            $limit = $request->length;
            $offset = $request->start;

            $froyos = $froyo->skip( $offset )->take( $limit )->get();

            if ( $froyos ) {
                $froyos->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = Froyo::count();

            $data = [
                'froyos' => $froyos,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $froyoCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    public static function allStocksFroyos( $request ) {

        // Query all froyos not in vending_machine_stocks
        $froyos = Froyo::select( 'froyos.*' )
            ->whereNotIn('id', function ($query) {
                $query->select('froyo_id')
                    ->from('vending_machine_stocks')
                    ->whereNotNull('froyo_id');
            });
    
        $filterObject = self::filter( $request, $froyos );
        $froyo = $filterObject['model'];
        $filter = $filterObject['filter'];
    
        // Handle sorting
        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $froyo->orderBy( 'froyos.created_at', $dir );
                    break;
                case 3:
                    $froyo->orderBy( 'froyos.title', $dir );
                    break;
                case 4:
                    $froyo->orderBy( 'froyos.description', $dir );
                    break;
            }
        }
    
        $froyoCount = $froyo->count();
    
        $limit = $request->length;
        $offset = $request->start;
    
        // Paginate results
        $froyos = $froyo->skip( $offset )->take( $limit )->get();
    
        if ( $froyos ) {
            $froyos->append( [
                'encrypted_id',
                'image_path',
            ] );
        }
    
        $totalRecord = Froyo::whereNotIn('id', function ($query) {
            $query->select('froyo_id')
                ->from('vending_machine_stocks')
                ->whereNotNull('froyo_id');
        })->count();
    
        $data = [
            'froyos' => $froyos,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $froyoCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];
    
        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'froyos.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'froyos.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_froyo)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_froyo . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }

        if ( !empty( $request->vending_machine_id ) ) {
            $vendingMachineFroyos = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'froyo_id' );
            $model->whereNotIn( 'id', $vendingMachineFroyos );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneFroyo( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $froyo = Froyo::find( $request->id );

        $froyo->append( ['encrypted_id','image_path'] );
        
        return response()->json( $froyo );
    }

    public static function deleteFroyo( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'froyo.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Froyo::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.froyos' ) ) ] ),
        ] );
    }

    public static function updateFroyoStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateFroyo = Froyo::find( $request->id );
            $updateFroyo->status = $updateFroyo->status == 10 ? 20 : 10;

            $updateFroyo->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'froyo' => $updateFroyo,
                    'message_key' => 'update_froyo_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_froyo_failed',
            ], 500 );
        }
    }

    public static function removeFroyoGalleryImage( $request ) {

        $updateFarm = Froyo::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }

    public static function allFroyosForVendingMachine( $request ) {

        $froyos = Froyo::select( 'froyos.*');

        $filterObject = self::filter( $request, $froyos );
        $froyo = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $froyo->orderBy( 'froyos.created_at', $dir );
                    break;
                case 2:
                    $froyo->orderBy( 'froyos.title', $dir );
                    break;
                case 3:
                    $froyo->orderBy( 'froyos.description', $dir );
                    break;
            }
        }

        $froyoCount = $froyo->count();

        $limit = $request->length;
        $offset = $request->start;

        $froyos = $froyo->skip( $offset )->take( $limit )->get();

        if ( $froyos ) {

            $froyos->append( [
                'encrypted_id',
                'image_path',
            ] );
        }

        $totalRecord = Froyo::count();

        $data = [
            'froyos' => $froyos,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $froyoCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
              
    }

    public static function getFroyoStock($request)
    {
        // Validate the incoming request
        $request->validate([
            'vending_machine_id' => 'required|exists:vending_machines,id',
            'froyo_id' => 'nullable|exists:froyos,id',
        ]);
    
        $vendingMachineId = $request->input('vending_machine_id');
        $froyoId = $request->input('froyo_id');
    
        // Base query to retrieve stock details for the vending machine
        $query = DB::table('vending_machine_stocks')
            ->where('vending_machine_id', $vendingMachineId);
    
        // If a Froyo ID is provided, add it to the query
        if ($froyoId) {
            $query->where('froyo_id', $froyoId);
        }
    
        // Retrieve stock details
        $stock = $query->select('froyo_id', 'quantity', 'last_stock_check')->first();
    
        // Prepare response
        if ($stock) {
            return response()->json([
                'success' => true,
                'current_stock' => $stock->quantity,
                'data' => $stock,
            ]);
        } else {
            return response()->json([
                'success' => true,  // Return success even if stock is empty
                'current_stock' => 0,  // Set stock to 0 if not found
                'message' => __('template.stock_not_found'),
            ]);
        }
    }
    

}