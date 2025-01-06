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
    VendingMachine,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VendingMachineService
{

    public static function createVendingMachine( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'vending_machine.title' ),
            'description' => __( 'vending_machine.description' ),
            'image' => __( 'vending_machine.image' ),
            'thumbnail' => __( 'vending_machine.thumbnail' ),
            'url_slug' => __( 'vending_machine.url_slug' ),
            'structure' => __( 'vending_machine.structure' ),
            'size' => __( 'vending_machine.size' ),
            'phone_number' => __( 'vending_machine.phone_number' ),
            'sort' => __( 'vending_machine.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $vendingmachineCreate = VendingMachine::create([
                'title' => $request->title,
                'description' => $request->description,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
            ]);

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'vending_machine/' . $vendingmachineCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $vendingmachineCreate->image = $target;
                   $vendingmachineCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'vending_machine/' . $vendingmachineCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $vendingmachineCreate->thumbnail = $target;
                   $vendingmachineCreate->save();

                    $thumbnailFile->status = 10;
                    $thumbnailFile->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.vending_machines' ) ) ] ),
        ] );
    }
    
    public static function updateVendingMachine( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'address_1' => [ 'nullable' ],
            'address_2' => [ 'nullable' ],
            'city' => [ 'nullable' ],
            'state' => [ 'nullable' ],
            'postcode' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'vending_machine.title' ),
            'description' => __( 'vending_machine.description' ),
            'image' => __( 'vending_machine.image' ),
            'thumbnail' => __( 'vending_machine.thumbnail' ),
            'url_slug' => __( 'vending_machine.url_slug' ),
            'structure' => __( 'vending_machine.structure' ),
            'size' => __( 'vending_machine.size' ),
            'phone_number' => __( 'vending_machine.phone_number' ),
            'sort' => __( 'vending_machine.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateVendingMachine = VendingMachine::find( $request->id );
    
            $updateVendingMachine->title = $request->title;
            $updateVendingMachine->description = $request->description;
            $updateVendingMachine->address_1 = $request->address_1;
            $updateVendingMachine->address_2 = $request->address_2;
            $updateVendingMachine->city = $request->city;
            $updateVendingMachine->state = $request->state;
            $updateVendingMachine->postcode = $request->postcode;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'vending_machine/' . $updateVendingMachine->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateVendingMachine->image = $target;
                   $updateVendingMachine->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateVendingMachine->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.vending_machines' ) ) ] ),
        ] );
    }

     public static function allVendingMachines( $request ) {

        $vendingmachines = VendingMachine::select( 'vending_machines.*');

        $filterObject = self::filter( $request, $vendingmachines );
        $vendingmachine = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $vendingmachine->orderBy( 'vending_machines.created_at', $dir );
                    break;
                case 2:
                    $vendingmachine->orderBy( 'vending_machines.title', $dir );
                    break;
                case 3:
                    $vendingmachine->orderBy( 'vending_machines.description', $dir );
                    break;
            }
        }

            $vendingmachineCount = $vendingmachine->count();

            $limit = $request->length;
            $offset = $request->start;

            $vendingmachines = $vendingmachine->skip( $offset )->take( $limit )->get();

            if ( $vendingmachines ) {
                $vendingmachines->append( [
                    'encrypted_id',
                    'image_path',
                ] );
            }

            $totalRecord = VendingMachine::count();

            $data = [
                'vending_machines' => $vendingmachines,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $vendingmachineCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'vending_machines.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'vending_machines.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_vending_machine)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_vending_machine . '%');
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
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneVendingMachine( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $vendingmachine = VendingMachine::find( $request->id );

        $vendingmachine->append( ['encrypted_id','image_path'] );
        
        return response()->json( $vendingmachine );
    }

    public static function deleteVendingMachine( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'vending_machine.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            VendingMachine::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.vending_machines' ) ) ] ),
        ] );
    }

    public static function updateVendingMachineStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateVendingMachine = VendingMachine::find( $request->id );
            $updateVendingMachine->status = $updateVendingMachine->status == 10 ? 20 : 10;

            $updateVendingMachine->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'vending_machine' => $updateVendingMachine,
                    'message_key' => 'update_vending_machine_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_vending_machine_failed',
            ], 500 );
        }
    }

    public static function removeVendingMachineGalleryImage( $request ) {

        $updateFarm = VendingMachine::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}