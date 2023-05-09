<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    FileManager,
    Vehicle,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class VehicleService
{
    public static function allVehicles( $request ) {

        $vehicle = Vehicle::select( 'vehicles.*' );

        $filterObject = self::filter( $request, $vehicle );
        $vehicle = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $vehicle->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $vehicle->orderBy( 'maker', $dir );
                    break;
                case 4:
                    $vehicle->orderBy( 'model', $dir );
                    break;
                case 5:
                    $vehicle->orderBy( 'type', $dir );
                    break;
                case 6:
                    $vehicle->orderBy( 'color', $dir );
                    break;
                case 7:
                    $vehicle->orderBy( 'license_plate', $dir );
                    break;
                case 8:
                    $vehicle->orderBy( 'in_service', $dir );
                    break;
                case 9:
                    $vehicle->orderBy( 'status', $dir );
                    break;
            }
        }

        $vehicleCount = $vehicle->count();

        $limit = $request->length;
        $offset = $request->start;

        $vehicles = $vehicle->skip( $offset )->take( $limit )->get();

        foreach ( $vehicles as $vehicle ) {
            $vehicle->append( [
                'path',
            ] );
        }

        if ( $vehicles ) {
            $vehicles->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Vehicle::count();

        $data = [
            'vehicles' => $vehicles,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $vehicleCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->registered_date ) ) {
            if ( str_contains( $request->registered_date, 'to' ) ) {
                $dates = explode( ' to ', $request->registered_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->registered_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->maker ) ) {
            $model->where( 'maker', $request->maker );
            $filter = true;
        }

        if ( !empty( $request->model ) ) {
            $model->where( 'model', $request->model );
            $filter = true;
        }

        if ( !empty( $request->type ) ) {
            $model->where( 'type', $request->type );
            $filter = true;
        }

        if ( !empty( $request->color ) ) {
            $model->where( 'color', $request->color );
            $filter = true;
        }

        if ( !empty( $request->license_plate ) ) {
            $model->where( 'license_plate', $request->license_plate );
            $filter = true;
        }

        if ( !empty( $request->in_service ) ) {
            $model->where( 'in_service', $request->in_service );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneVehicle( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $vendor = Vehicle::find( $request->id );

        if( $vendor ) {
            $vendor->append( [
                'path',
                'encrypted_id',
            ] );
        }

        return response()->json( $vendor );
    }

    public static function createVehicle( $request ) {

        $validator = Validator::make( $request->all(), [
            'photo' => [ 'required' ],
            'maker' => [ 'required' ],
            'model' => [ 'required' ],
            'color' => [ 'required' ],
            'license_plate' => [ 'required' ],
            'type' => [ 'required' ],
            'in_service' => [ 'required', 'in:0,1' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'maker' => __( 'vehicle.maker' ),
            'model' => __( 'vehicle.model' ),
            'color' => __( 'vehicle.color' ),
            'license_plate' => __( 'vehicle.license_plate' ),
            'in_service' => __( 'vehicle.in_service' ),
            'type' => __( 'vehicle.type' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createVehicle = Vehicle::create( [
                'maker' => $request->maker,
                'model' => $request->model,
                'color' => $request->color,
                'license_plate' => $request->license_plate,
                'in_service' => $request->in_service,
                'type' => $request->type,
            ] );

            $file = FileManager::find( $request->photo );
            if ( $file ) {
                $fileName = explode( '/', $file->file );
                $target = 'vehicles/' . $createVehicle->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $file->file, $target );

                $createVehicle->photo = $target;
                $createVehicle->save();

                $file->status = 10;
                $file->save();
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.vehicles' ) ) ] ),
        ] );
    }

    public static function updateVehicle( $request ) {

        $validator = Validator::make( $request->all(), [
            'photo' => [ 'required' ],
            'maker' => [ 'required' ],
            'model' => [ 'required' ],
            'color' => [ 'required' ],
            'license_plate' => [ 'required' ],
            'type' => [ 'required' ],
            'in_service' => [ 'required', 'in:0,1' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'maker' => __( 'vehicle.maker' ),
            'model' => __( 'vehicle.model' ),
            'color' => __( 'vehicle.color' ),
            'license_plate' => __( 'vehicle.license_plate' ),
            'in_service' => __( 'vehicle.in_service' ),
            'type' => __( 'vehicle.type' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateVehicle = Vehicle::find( $request->id );
            $updateVehicle->maker = $request->maker;
            $updateVehicle->model = $request->model;
            $updateVehicle->color = $request->color;
            $updateVehicle->license_plate = $request->license_plate;
            $updateVehicle->type = $request->type;
            $updateVehicle->in_service = $request->in_service;
            $updateVehicle->save();

            if ( $request->photo ) {
                $file = FileManager::find( $request->photo );
                if ( $file ) {

                    Storage::disk( 'public' )->delete( $updateVehicle->photo );

                    $fileName = explode( '/', $file->file );
                    $target = 'vehicles/' . $updateVehicle->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );
    
                    $updateVehicle->photo = $target;
                    $updateVehicle->save();
    
                    $file->status = 10;
                    $file->save();
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.vehicles' ) ) ] ),
        ] );
    }
}