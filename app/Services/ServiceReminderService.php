<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Service,
    ServiceReminder,
};

use Helper;

use Carbon\Carbon;

class ServiceReminderService
{
    public static function allServiceReminders( $request ) {

        $service = ServiceReminder::with( [
            'vehicle',
            'service',
        ] )->select( 'service_reminders.*' );

        $serviceObject = self::filterServiceReminder( $request, $service );
        $service = $serviceObject['model'];
        $filter = $serviceObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $service->orderBy( 'service_reminders.created_at', $dir );
                    break;
            }
        }

        $serviceCount = $service->count();

        $limit = $request->length;
        $offset = $request->start;

        $services = $service->skip( $offset )->take( $limit )->get();

        if ( $services ) {
            $services->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = ServiceReminder::count();

        $data = [
            'services' => $services,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $serviceCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filterServiceReminder( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'service_reminders.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'service_reminders.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->service_name ) ) {
            $model->where( 'service_reminders.name', $request->service_name );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneServiceReminder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $service = ServiceReminder::with( [ 'service' ] )->find( $request->id );

        if ( $service ) {
            $service->service->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $service );
    }

    public static function createServiceReminder( $request ) {

        $validator = Validator::make( $request->all(), [
            'vehicle' => [ 'required' ],
            'service' => [ 'required' ],
            'service_date' => [ 'required' ],
            'due_date' => [ 'required' ],
        ] );

        $attributeName = [
            'vehicle' => __( 'service.vehicle' ),
            'service' => __( 'service.service' ),
            'service_date' => __( 'service.service_date' ),
            'due_date' => __( 'service.due_date' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createService = ServiceReminder::create( [
                'vehicle_id' => $request->vehicle,
                'service_id' => $request->service,
                'service_date' => $request->service_date,
                'due_date' => $request->due_date,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.service_reminders' ) ) ] ),
        ] );
    }

    public static function updateServiceReminder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
            'service' => Helper::decode( $request->service ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'vehicle' => [ 'required' ],
            'service' => [ 'required' ],
            'service_date' => [ 'required' ],
            'due_date' => [ 'required' ],
        ] );

        $attributeName = [
            'vehicle' => __( 'service.vehicle' ),
            'service' => __( 'service.service' ),
            'service_date' => __( 'service.service_date' ),
            'due_date' => __( 'service.due_date' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateServiceReminder = ServiceReminder::find( $request->id );
            $updateServiceReminder->vehicle_id = $request->vehicle;
            $updateServiceReminder->service_id = $request->service;
            $updateServiceReminder->service_date = $request->service_date;
            $updateServiceReminder->due_date = $request->due_date;
            $updateServiceReminder->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.service_reminders' ) ) ] ),
        ] );
    }
}