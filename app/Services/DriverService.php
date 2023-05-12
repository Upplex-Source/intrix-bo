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
    Driver,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class DriverService
{
    public static function allDrivers( $request ) {

        $driver = Driver::select( 'drivers.*' );

        $filterObject = self::filter( $request, $driver );
        $driver = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $driver->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $driver->orderBy( 'name', $dir );
                    break;
                case 4:
                    $driver->orderBy( 'phone_number', $dir );
                    break;
                case 5:
                    $driver->orderBy( 'license_expiry_date', $dir );
                    break;
                case 6:
                    $driver->orderBy( 'employment_type', $dir );
                    break;
                case 7:
                    $vendor->orderBy( 'status', $dir );
                    break;
            }
        }

        $driverCount = $driver->count();

        $limit = $request->length;
        $offset = $request->start;

        $drivers = $driver->skip( $offset )->take( $limit )->get();

        foreach ( $drivers as $driver ) {
            $driver->append( [
                'path',
            ] );
        }

        if ( $drivers ) {
            $drivers->append( [
                'display_license_expiry_date',
                'encrypted_id',
            ] );
        }

        $totalRecord = Driver::count();

        $data = [
            'drivers' => $drivers,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $driverCount : $totalRecord,
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

                $model->whereBetween( 'drivers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->registered_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'drivers.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'name', $request->name );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $model->where( 'phone_number', $request->phone_number );
            $filter = true;
        }

        if ( !empty( $request->license_expiry_date ) ) {
            $model->where( 'license_expiry_date', $request->license_expiry_date );
            $filter = true;
        }

        if ( !empty( $request->employment_type ) ) {
            $model->where( 'employment_type', $request->employment_type );
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

    public static function oneDriver( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $driver = Driver::find( $request->id );

        if( $driver ) {
            $driver->append( [
                'path',
                'display_license_expiry_date',
                'encrypted_id',
            ] );
        }

        return response()->json( $driver );
    }

    public static function createDriver( $request ) {

        $validator = Validator::make( $request->all(), [
            'photo' => [ 'required' ],
            'name' => [ 'required' ],
            'email' => [ 'required', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'digits_between:8,15' ],
            'license_expiry_date' => [ 'required' ],
            'employment_type' => [ 'required', 'in:1,2' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'driver.name' ),
            'email' => __( 'driver.email' ),
            'phone_number' => __( 'driver.phone_number' ),
            'license_expiry_date' => __( 'driver.license_expiry_date' ),
            'employment_type' => __( 'driver.employment_type' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createDriver = Driver::create( [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'license_expiry_date' => $request->license_expiry_date,
                'employment_type' => $request->employment_type,
            ] );

            $file = FileManager::find( $request->photo );
            if ( $file ) {
                $fileName = explode( '/', $file->file );
                $target = 'drivers/' . $createDriver->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $file->file, $target );

                $createDriver->photo = $target;
                $createDriver->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.drivers' ) ) ] ),
        ] );
    }

    public static function updateDriver( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'photo' => [ 'required' ],
            'name' => [ 'required' ],
            'email' => [ 'required', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'digits_between:8,15' ],
            'license_expiry_date' => [ 'required' ],
            'employment_type' => [ 'required', 'in:1,2' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'driver.name' ),
            'email' => __( 'driver.email' ),
            'phone_number' => __( 'driver.phone_number' ),
            'license_expiry_date' => __( 'driver.license_expiry_date' ),
            'employment_type' => __( 'driver.employment_type' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateDriver = Driver::find( $request->id );
            $updateDriver->name = $request->name;
            $updateDriver->email = $request->email;
            $updateDriver->phone_number = $request->phone_number;
            $updateDriver->license_expiry_date = $request->license_expiry_date;
            $updateDriver->employment_type = $request->employment_type;
            $updateDriver->save();

            if ( $request->photo ) {
                $file = FileManager::find( $request->photo );
                if ( $file ) {

                    Storage::disk( 'public' )->delete( $updateDriver->photo );

                    $fileName = explode( '/', $file->file );
                    $target = 'drivers/' . $updateDriver->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );
    
                    $updateDriver->photo = $target;
                    $updateDriver->save();
    
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.drivers' ) ) ] ),
        ] );
    }

    public static function updateDriverStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateDriver = Driver::find( $request->id );
        $updateDriver->status = $request->status;
        $updateDriver->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.drivers' ) ) ] ),
        ] );
    }
}