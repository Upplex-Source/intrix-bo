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
                    $vehicle->orderBy( 'employees.name', $dir );
                    break;
                case 3:
                    $vehicle->orderBy( 'vehicles.road_tax_expiry_date', $dir );
                    break;
                case 4:
                    $vehicle->orderBy( 'vehicles.insurance_expiry_date', $dir );
                    break;
                case 5:
                    $vehicle->orderBy( 'vehicles.inspection_expiry_date', $dir );
                    break;
                case 6:
                    $vehicle->orderBy( 'vehicles.permit_expiry_date', $dir );
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
                'local_road_tax_expiry_date',
                'local_insurance_expiry_date',
                'local_inspection_expiry_date',
                'local_permit_expiry_date',
                'local_permit_start_date',
                'local_road_tax_expiry_date_status',
                'local_insurance_expiry_date_status',
                'local_inspection_expiry_date_status',
                'local_permit_expiry_date_status',
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
            $model->where( 'employees.name', 'LIKE', '%' . $request->driver . '%' );
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'vehicles.name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->type ) ) {
            $model->where( 'vehicles.type', 'LIKE', '%' . $request->type . '%' );
            $filter = true;
        }

        if ( !empty( $request->license_plate ) ) {
            $model->where( 'vehicles.license_plate', 'LIKE', '%' . $request->license_plate . '%' );
            $filter = true;
        }

        if ( !empty( $request->road_tax_expiry_date ) ) {
            if ( str_contains( $request->road_tax_expiry_date, 'to' ) ) {
                $dates = explode( ' to ', $request->road_tax_expiry_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.road_tax_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->road_tax_expiry_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.road_tax_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->insurance_expiry_date ) ) {
            if ( str_contains( $request->insurance_expiry_date, 'to' ) ) {
                $dates = explode( ' to ', $request->insurance_expiry_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.insurance_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->road_tax_expiry_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.insurance_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->inspection_expiry_date ) ) {
            if ( str_contains( $request->inspection_expiry_date, 'to' ) ) {
                $dates = explode( ' to ', $request->inspection_expiry_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.inspection_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->inspection_expiry_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.inspection_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->permit_expiry_date ) ) {
            if ( str_contains( $request->permit_expiry_date, 'to' ) ) {
                $dates = explode( ' to ', $request->permit_expiry_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.permit_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->permit_expiry_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.permit_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->permit ) ) {
            if ( str_contains( $request->permit, 'to' ) ) {
                $dates = explode( ' to ', $request->permit );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.permit_start_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] )
                        ->orWhereBetween( 'vehicles.permit_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->permit );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'vehicles.permit_start_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] )
                        ->orWhereBetween( 'vehicles.permit_expiry_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
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
                $filter = true;
            } );
        }

        if ( !empty( $request->expiry_checking ) ) {

            $model->where( function ( $query ) use ( $request ) {
                $query->where( function( $query ) {
                    $query->whereDate( 'road_tax_expiry_date', '<=', Carbon::now()->addMonths( 1 ) )
                        ->orWhereDate( 'road_tax_expiry_date', '<', Carbon::now() );
                })
                ->orWhere( function( $query ) {
                    $query->whereDate( 'insurance_expiry_date', '<=', Carbon::now()->addMonths( 1 ) )
                        ->orWhereDate( 'insurance_expiry_date', '<', Carbon::now() );
                })
                ->orWhere( function( $query ) {
                    $query->whereDate( 'inspection_expiry_date', '<=', Carbon::now()->addMonths( 1 ) )
                        ->orWhereDate( 'inspection_expiry_date', '<', Carbon::now() );
                });
            });
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

        $vehicle = Vehicle::with( [
            'employee',
            'company',
        ] )->find( $request->id );

        if( $vehicle ) {
            $vehicle->append( [
                'path',
                'local_permit_start_date',
                'local_road_tax_expiry_date',
                'local_insurance_expiry_date',
                'local_permit_expiry_date',
                'local_inspection_expiry_date',
                'encrypted_id',
            ] );
        }

        return response()->json( $vehicle );
    }

    public static function createVehicle( $request ) {

        $validator = Validator::make( $request->all(), [
            // 'photo' => [ 'required' ],
            'driver' => [ 'required', 'exists:employees,id' ],
            'company' => [ 'required', 'exists:companies,id' ],
            'name' => [ 'required' ],
            'license_plate' => [ 'required' ],
            'type' => [ 'required' ],
            'permit' => [ 'required', 'in:1,2' ],
            'permit_start_date' => [ 'required' ],
            // 'in_service' => [ 'required', 'in:0,1' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'vehicle.model' ),
            'license_plate' => __( 'vehicle.license_plate' ),
            'trailer_number' => __( 'vehicle.trailer_number' ),
            'road_tax_number' => __( 'vehicle.road_tax_number' ),
            'insurance_number' => __( 'vehicle.insurance_number' ),
            'permit_number' => __( 'vehicle.permit_number' ),
            'permit' => __( 'vehicle.permit' ),
            'road_tax_expiry_date' => __( 'vehicle.road_tax_expiry_date' ),
            'insurance_expiry_date' => __( 'vehicle.insurance_expiry_date' ),
            'permit_start_date' => __( 'vehicle.permit_start_date' ),
            'permit_expiry_date' => __( 'vehicle.permit_expiry_date' ),
            'inspection_expiry_date' => __( 'vehicle.inspection_expiry_date' ),
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
                'company_id' => $request->company,
                'name' => $request->name,
                'license_plate' => $request->license_plate,
                'trailer_number' => $request->trailer_number,
                'road_tax_number' => $request->road_tax_number,
                'insurance_number' => $request->insurance_number,
                'permit_number' => null,
                'permit_type' => $request->permit,
                'road_tax_expiry_date' => $request->road_tax_expiry_date ? Carbon::createFromFormat( 'Y-m-d', $request->road_tax_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                'insurance_expiry_date' => $request->insurance_expiry_date ? Carbon::createFromFormat( 'Y-m-d', $request->insurance_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                'permit_start_date' => $request->permit_start_date ? Carbon::createFromFormat( 'Y-m-d', $request->permit_start_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                'permit_expiry_date' => $request->permit_expiry_date ? Carbon::createFromFormat( 'Y-m-d', $request->permit_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                'inspection_expiry_date' => $request->inspection_expiry_date ? Carbon::createFromFormat( 'Y-m-d', $request->inspection_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                'in_service' => 0,
                'type' => 1,
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
            'driver' => [ 'required', 'exists:employees,id' ],
            'company' => [ 'required', 'exists:companies,id' ],
            'name' => [ 'required' ],
            'license_plate' => [ 'required' ],
            'type' => [ 'required' ],
            'permit' => [ 'required', 'in:1,2' ],
            'permit_start_date' => [ 'required' ],
            // 'in_service' => [ 'required', 'in:0,1' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'vehicle.model' ),
            'license_plate' => __( 'vehicle.license_plate' ),
            'trailer_number' => __( 'vehicle.trailer_number' ),
            'road_tax_number' => __( 'vehicle.road_tax_number' ),
            'insurance_number' => __( 'vehicle.insurance_number' ),
            'permit_number' => __( 'vehicle.permit_number' ),
            'permit' => __( 'vehicle.permit' ),
            'road_tax_expiry_date' => __( 'vehicle.road_tax_expiry_date' ),
            'insurance_expiry_date' => __( 'vehicle.insurance_expiry_date' ),
            'permit_start_date' => __( 'vehicle.permit_start_date' ),
            'permit_expiry_date' => __( 'vehicle.permit_expiry_date' ),
            'inspection_expiry_date' => __( 'vehicle.inspection_expiry_date' ),
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
            $updateVehicle->company_id = $request->company;
            $updateVehicle->name = $request->name;
            $updateVehicle->license_plate = $request->license_plate;
            $updateVehicle->trailer_number = $request->trailer_number;
            $updateVehicle->road_tax_number = $request->road_tax_number;
            $updateVehicle->insurance_number = $request->insurance_number;
            $updateVehicle->permit_number = null;
            $updateVehicle->permit_type = $request->permit;
            $updateVehicle->road_tax_expiry_date = $request->road_tax_expiry_date ? Carbon::createFromFormat( 'Y-m-d', $request->road_tax_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            $updateVehicle->insurance_expiry_date = $request->insurance_expiry_date ? Carbon::createFromFormat( 'Y-m-d', $request->insurance_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            $updateVehicle->permit_start_date = $request->permit_start_date ? Carbon::createFromFormat( 'Y-m-d', $request->permit_start_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            $updateVehicle->permit_expiry_date = $request->permit_expiry_date ? Carbon::createFromFormat( 'Y-m-d', $request->permit_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            $updateVehicle->inspection_expiry_date = $request->inspection_expiry_date ? Carbon::createFromFormat( 'Y-m-d', $request->inspection_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            $updateVehicle->type = 1;
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

    public static function exportBookings( $request ) {

        $bookings = self::allBookings( $request, true );

        $html = '<table>';
        $html .= '
        <thead>
            <tr>
                <th><strong>' .__( 'vehicle.driver' ). '</strong></th>
                <th><strong>' .__( 'vehicle.license_plate' ). '</strong></th>
                <th><strong>' .__( 'vehicle.trailer_number' ). '</strong></th>
                <th><strong>' .__( 'vehicle.lorry_model' ). '</strong></th>
                <th><strong>' .__( 'vehicle.road_tax_number' ). '</strong></th>
                <th><strong>' .__( 'vehicle.insurance_number' ). '</strong></th>
                <th><strong>' .__( 'vehicle.permit_number' ). '</strong></th>
                <th><strong>' .__( 'vehicle.permit_type' ). '</strong></th>
                <th><strong>' .__( 'vehicle.insurance_expiry_date' ). '</strong></th>
                <th><strong>' .__( 'vehicle.inspection_expiry_date' ). '</strong></th>
                <th><strong>' .__( 'vehicle.permit_date' ). '</strong></th>
                <th><strong>' .__( 'vehicle.type' ). '</strong></th>
                <th><strong>' .__( 'vehicle.in_service' ). '</strong></th>
            </tr>
        </thead>
        ';
        $html .= '<tbody>';

        $permitType = [
            '1' => strtoupper( 'A' ),
            '2' => strtoupper( 'C'),
        ];
        $vehicleType = [
            '1' => strtoupper( __( 'vehicle.truck' ) ),
            '2' => strtoupper( __( 'vehicle.timber_jinker' ) ),
            '3' => strtoupper( __( 'vehicle.curtain_sider' ) ),
            '3' => strtoupper( __( 'vehicle.open_cargo' ) ),
        ];
        $inServiceType = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];

        foreach ( $bookings as $key => $booking ) {
            $refNo = explode( ' ', $booking->reference );
            $html .=
            '
            <tr>
                <td>' . $booking->reference . '</td>
                <td>' . $refNo[0] . '</td>
                <td>' . $refNo[1] . '</td>
                <td>' . $booking->invoice_number . '</td>
                <td>' . $booking->invoice_date . '</td>
                <td>' . $booking->vehicle->license_plate . '</td>
                <td>' . $booking->delivery_order_number . '</td>
                <td>' . date( 'd/m/Y', strtotime( $booking->delivery_order_date ) ) . '</td>
                <td>' . $booking->customer_name . '</td>
                <td>' . $booking->display_pickup_address->a1 . '</td>
                <td>' . $booking->display_dropoff_address->a1 . '</td>
                <td>' . $booking->display_dropoff_address->d . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->customer_quantity, 4 ) . '</td>
                <td>' . $uom[$booking->customer_unit_of_measurement] . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->customer_rate, 2 ) . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->driver_rate, 2 ) . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->customer_total_amount, 2 ) . '</td>
                <td>' . $customerType[$booking->customer_type] . '</td>
                <td>' . $booking->company->name . '</td>
                <td>' . $booking->customer_remarks . '</td>
                <td>' . $booking->driver->name . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->driver_quantity, 4 ) . '</td>
                <td>' . $uom[$booking->driver_unit_of_measurement] . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->driver_rate, 2 ) . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->driver_total_amount, 2 ) . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->driver_percentage, 2 ) . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->driver_final_amount, 2 ) . '</td>
            </tr>
            ';
        }

        $html .= '</tbody></table>';

        Helper::exportReport( $html, 'Booking' );
    }
}