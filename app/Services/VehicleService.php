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
    public static function get() {

        $vehicles = Vehicle::where( 'status', 10 )->get()->toArray();

        return $vehicles;
    }

    public static function allVehicles( $request ) {

        $vehicle = Vehicle::with( [
            'employee'
        ] )->select( 'vehicles.*' );

        $vehicle->leftJoin( 'employees', 'employees.id', '=', 'vehicles.driver_id' );

        $filterObject = self::filter( $request, $vehicle );
        $vehicle = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $vehicle->orderBy( 'vehicles.created_at', $dir );
                    break;
                case 3:
                    $vehicle->orderBy( 'employees.name', $dir );
                    break;
                case 4:
                    $vehicle->orderBy( 'vehicles.name', $dir );
                    break;
                case 5:
                    $vehicle->orderBy( 'vehicles.type', $dir );
                    break;
                case 6:
                    $vehicle->orderBy( 'vehicles.license_plate', $dir );
                    break;
                case 7:
                    $vehicle->orderBy( 'vehicles.status', $dir );
                    break;
            }
        }

        $vehicleCount = $vehicle->count();

        $limit = $request->length;
        $offset = $request->start;

        $vehicles = $vehicle->skip( $offset )->take( $limit )->get();

        if ( $vehicles ) {
            $vehicles->append( [
                'path',
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

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->driver ) ) {
            $model->where( 'employees.name', $request->driver );
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'vehicles.name', $request->name );
            $filter = true;
        }

        if ( !empty( $request->type ) ) {
            $model->where( 'vehicles.type', $request->type );
            $filter = true;
        }

        if ( !empty( $request->license_plate ) ) {
            $model->where( 'vehicles.license_plate', $request->license_plate );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'vehicles.status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( function( $query ) {
                $query->where( 'vehicles.name', 'LIKE', '%' . request( 'custom_search' ) . '%' );
                $query->orWhere( 'vehicles.license_plate', 'LIKE', '%' . request( 'custom_search' ) . '%' );
            } );
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

        $vehicle = Vehicle::with( [
            'employee',
        ] )->find( $request->id );

        if( $vehicle ) {
            $vehicle->append( [
                'path',
                'encrypted_id',
            ] );
        }

        return response()->json( $vehicle );
    }

    public static function createVehicle( $request ) {

        $validator = Validator::make( $request->all(), [
            // 'photo' => [ 'required' ],
            'driver' => [ 'required', 'exists:employees,id' ],
            'name' => [ 'required' ],
            'license_plate' => [ 'required' ],
            'type' => [ 'required' ],
            // 'in_service' => [ 'required', 'in:0,1' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'vehicle.name' ),
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
                'driver_id' => $request->driver,
                'name' => $request->name,
                'license_plate' => $request->license_plate,
                'in_service' => 0,
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

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            // 'photo' => [ 'required' ],
            'name' => [ 'required' ],
            'license_plate' => [ 'required' ],
            'type' => [ 'required' ],
            // 'in_service' => [ 'required', 'in:0,1' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'vehicle.name' ),
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
            $updateVehicle->driver_id = $request->driver;
            $updateVehicle->name = $request->name;
            $updateVehicle->license_plate = $request->license_plate;
            $updateVehicle->type = $request->type;
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

    public static function updateVehicleStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateVehicle = Vehicle::find( $request->id );
        $updateVehicle->status = $request->status;
        $updateVehicle->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.vehicles' ) ) ] ),
        ] );
    }
}