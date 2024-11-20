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
    MeasurementUnit,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MeasurementUnitService
{

    public static function createMeasurementUnit( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'tax_percentage' => [ 'nullable', 'numeric', 'min:0' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'measurement_unit.title' ),
            'description' => __( 'measurement_unit.description' ),
            'image' => __( 'measurement_unit.image' ),
            'thumbnail' => __( 'measurement_unit.thumbnail' ),
            'url_slug' => __( 'measurement_unit.url_slug' ),
            'structure' => __( 'measurement_unit.structure' ),
            'size' => __( 'measurement_unit.size' ),
            'phone_number' => __( 'measurement_unit.phone_number' ),
            'sort' => __( 'measurement_unit.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $measurementUnitCreate = MeasurementUnit::create([
                'title' => $request->title,
                'description' => $request->description,
                'tax_percentage' => $request->tax_percentage,
            ]);

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'measurement_unit/' . $measurementUnitCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $measurementUnitCreate->image = $target;
                   $measurementUnitCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'measurement_unit/' . $measurementUnitCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $measurementUnitCreate->thumbnail = $target;
                   $measurementUnitCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.measurement_units' ) ) ] ),
        ] );
    }
    
    public static function updateMeasurementUnit( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'tax_percentage' => [ 'nullable', 'numeric', 'min:0' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'measurement_unit.title' ),
            'description' => __( 'measurement_unit.description' ),
            'image' => __( 'measurement_unit.image' ),
            'thumbnail' => __( 'measurement_unit.thumbnail' ),
            'url_slug' => __( 'measurement_unit.url_slug' ),
            'structure' => __( 'measurement_unit.structure' ),
            'size' => __( 'measurement_unit.size' ),
            'phone_number' => __( 'measurement_unit.phone_number' ),
            'sort' => __( 'measurement_unit.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateMeasurementUnit = MeasurementUnit::find( $request->id );
    
            $updateMeasurementUnit->title = $request->title;
            $updateMeasurementUnit->description = $request->description;
            $updateMeasurementUnit->tax_percentage = $request->tax_percentage;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'measurement_unit/' . $updateMeasurementUnit->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateMeasurementUnit->image = $target;
                   $updateMeasurementUnit->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateMeasurementUnit->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.measurement_units' ) ) ] ),
        ] );
    }

     public static function allMeasurementUnits( $request ) {

        $measurementUnits = MeasurementUnit::select( 'measurement_units.*');

        $filterObject = self::filter( $request, $measurementUnits );
        $measurementUnit = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $measurementUnit->orderBy( 'measurement_units.created_at', $dir );
                    break;
                case 2:
                    $measurementUnit->orderBy( 'measurement_units.title', $dir );
                    break;
                case 3:
                    $measurementUnit->orderBy( 'measurement_units.description', $dir );
                    break;
            }
        }

            $measurementUnitCount = $measurementUnit->count();

            $limit = $request->length;
            $offset = $request->start;

            $measurementUnits = $measurementUnit->skip( $offset )->take( $limit )->get();

            if ( $measurementUnits ) {
                $measurementUnits->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = MeasurementUnit::count();

            $data = [
                'measurement_units' => $measurementUnits,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $measurementUnitCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'measurement_units.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'measurement_units.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_measurement_unit)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_measurement_unit . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'measurement_unit.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneMeasurementUnit( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $measurementUnit = MeasurementUnit::find( $request->id );

        $measurementUnit->append( ['encrypted_id','image_path'] );
        
        return response()->json( $measurementUnit );
    }

    public static function deleteMeasurementUnit( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'measurement_unit.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            MeasurementUnit::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.measurement_units' ) ) ] ),
        ] );
    }

    public static function updateMeasurementUnitStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateMeasurementUnit = MeasurementUnit::find( $request->id );
            $updateMeasurementUnit->status = $updateMeasurementUnit->status == 10 ? 20 : 10;

            $updateMeasurementUnit->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'measurement_unit' => $updateMeasurementUnit,
                    'message_key' => 'update_measurement_unit_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_measurement_unit_failed',
            ], 500 );
        }
    }

    public static function removeMeasurementUnitGalleryImage( $request ) {

        $updateFarm = MeasurementUnit::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}