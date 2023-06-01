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

class ServiceService
{
    public static function get() {

        $services = Service::where( 'status', 10 )->get();

        return $services;
    }

    public static function allServices( $request ) {

        $service = Service::select( 'services.*' );

        $serviceObject = self::filterService( $request, $service );
        $service = $serviceObject['model'];
        $filter = $serviceObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $service->orderBy( 'services.created_at', $dir );
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

        $totalRecord = Service::count();

        $data = [
            'services' => $services,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $serviceCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filterService( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'services.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'services.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->service_name ) ) {
            $model->where( 'services.name', $request->service_name );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneService( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $service = Service::find( $request->id );

        return response()->json( $service );
    }

    public static function createService( $request ) {

        $validator = Validator::make( $request->all(), [
            'service_name' => [ 'required' ],
            'description' => [ 'required' ],
            'service_interval' => [ 'required' ],
            'reminder_activation' => [ 'required' ],
            'reminder_frequency' => [ 'required' ],
        ] );

        $attributeName = [
            'service_name' => __( 'service.service_name' ),
            'description' => __( 'service.description' ),
            'service_interval' => __( 'service.service_interval' ),
            'reminder_activation' => __( 'service.reminder_activation' ),
            'reminder_frequency' => __( 'service.reminder_frequency' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createService = Service::create( [
                'name' => $request->service_name,
                'description' => $request->description,
                'service_interval' => $request->service_interval,
                'reminder_activation' => $request->reminder_activation,
                'reminder_frequency' => $request->reminder_frequency,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.services' ) ) ] ),
        ] );
    }

    public static function updateService( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'service_name' => [ 'required' ],
            'description' => [ 'required' ],
            'service_interval' => [ 'required' ],
            'reminder_activation' => [ 'required' ],
            'reminder_frequency' => [ 'required' ],
        ] );

        $attributeName = [
            'service_name' => __( 'service.service_name' ),
            'description' => __( 'service.description' ),
            'service_interval' => __( 'service.service_interval' ),
            'reminder_activation' => __( 'service.reminder_activation' ),
            'reminder_frequency' => __( 'service.reminder_frequency' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateService = Service::find( $request->id );
            $updateService->name = $request->service_name;
            $updateService->description = $request->description;
            $updateService->service_interval = $request->service_interval;
            $updateService->reminder_activation = $request->reminder_activation;
            $updateService->reminder_frequency = $request->reminder_frequency;
            $updateService->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.services' ) ) ] ),
        ] );
    }

    public static function allServiceReminders( $request ) {

        $serviceReminder = ServiceReminder::select( 'service_reminders.*' );

        $serviceObject = self::filterServiceReminder( $request, $serviceReminder );
        $serviceReminder = $serviceObject['model'];
        $filter = $serviceObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $fuelExpense->orderBy( 'service_reminders.created_at', $dir );
                    break;
            }
        }

        $serviceReminderCount = $serviceReminder->count();

        $limit = $request->length;
        $offset = $request->start;

        $serviceReminders = $serviceReminder->skip( $offset )->take( $limit )->get();

        if ( $serviceReminders ) {
            $serviceReminders->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = ServiceReminder::count();

        $data = [
            'service_reminders' => $serviceReminders,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $serviceReminderCount : $totalRecord,
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

        if ( !empty( $request->vehicle ) ) {
            $model->where( 'service_reminders.vehicle_id', $request->vehicle );
            $filter = true;
        }

        if ( !empty( $request->service ) ) {
            $model->where( 'service_reminders.service_id', $request->service );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneServiceReminder( $request ) {
        
    }

    public static function createServiceReminder( $request ) {

    }

    public static function updateServiceReminder( $request ) {
        
    }
}