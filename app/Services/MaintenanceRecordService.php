<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    ServiceRecord,
    ServiceRecordItem,
};

use Helper;

use Carbon\Carbon;

class MaintenanceRecordService
{
    public static function allServiceRecords( $request ) {

        $serviceRecord = ServiceRecord::with( [
            'vehicle',
        ] )->select( 'service_records.*' );

        $serviceObject = self::filterServiceRecord( $request, $serviceRecord );
        $serviceRecord = $serviceObject['model'];
        $filter = $serviceObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $serviceRecord->orderBy( 'service_records.service_date', $dir );
                    break;
                case 2:
                    $serviceRecord->orderBy( 'service_records.vehicle_id', $dir );
                    break;
                case 3:
                    $serviceRecord->orderBy( 'service_records.workshop', $dir );
                    break;
                case 4:
                    $serviceRecord->orderBy( 'service_records.document_reference', $dir );
                    break;
                case 5:
                    $serviceRecord->orderBy( 'service_records.meter_reading', $dir );
                    break;
            }
        }

        $serviceRecordCount = $serviceRecord->count();

        $limit = $request->length;
        $offset = $request->start;

        $serviceRecords = $serviceRecord->skip( $offset )->take( $limit )->get();

        if ( $serviceRecords ) {
            $serviceRecords->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = ServiceRecord::count();

        $data = [
            'records' => $serviceRecords,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $serviceRecordCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filterServiceRecord( $request, $model ) {

        $filter = false;

        if ( !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'service_records.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'service_records.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->workshop ) ) {
            $model->where( 'service_records.workshop', $request->workshop );
            $filter = true;
        }

        if ( !empty( $request->document_reference ) ) {
            $model->where( 'service_records.document_reference', $request->document_reference );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneServiceRecord( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $serviceRecord = ServiceRecord::with( [
            'items',
            'vehicle',
        ] )->find( $request->id );

        if ( $serviceRecord ) {
            foreach( $serviceRecord->items as $item ) {
                $item->append( [
                    'meta_object',
                ] );
            }
        }

        return $serviceRecord;
    }

    public static function validateItemServiceRecord( $request ) {

        if ( $request->type == 1 ) {
            $validator = Validator::make( $request->all(), [
                'grades' => [ 'required' ],
                'qty' => [ 'required', 'numeric' ],
                'next_service' => [ 'required' ],
            ] );
        } else {
            $validator = Validator::make( $request->all(), [
                'description' => [ 'required' ],
            ] );
        }

        $attributeName = [
            'grades' => __( 'maintenance_record.grades' ),
            'qty' => __( 'maintenance_record.grades' ),
            'next_service' => __( 'maintenance_record.next_service' ),
            'description' => __( 'maintenance_record.description' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
    }

    public function createServiceRecord( $request ) {

        $validator = Validator::make( $request->all(), [
            'vehicle' => [ 'required', 'exists:vehicles,id' ],
            'company' => [ 'required', 'exists:companies,id' ],
            'service_date' => [ 'required' ],
            'workshop' => [ 'required' ],
            'meter_reading' => [ 'required', 'numeric' ],
            'document_reference' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
        ] );

        $attributeName = [
            'vehicle' => __( 'maintenance_record.vehicle' ),
            'company' => __( 'maintenance_record.company' ),
            'service_date' => __( 'datatables.service_date' ),
            'workshop' => __( 'maintenance_record.workshop' ),
            'meter_reading' => __( 'maintenance_record.meter_reading' ),
            'document_reference' => __( 'maintenance_record.document_reference' ),
            'remarks' => __( 'maintenance_record.remarks' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createServiceRecord = ServiceRecord::create( [
                'vehicle_id' => $request->vehicle,
                'company_id' => $request->company,
                'service_date' => $request->service_date,
                'workshop' => $request->workshop,
                'meter_reading' => $request->meter_reading,
                'document_reference' => $request->document_reference,
                'remarks' => $request->remarks,
            ] );

            $items = json_decode( $request->items );
            foreach ( $items as $item ) {
                ServiceRecordItem::create( [
                    'service_record_id' => $createServiceRecord->id,
                    'type' => $item->type,
                    'meta' => $item->type == 1 ? json_encode( $item->description ) : json_encode( [
                        'description' => $item->description
                    ] ),
                ] );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.service_records' ) ) ] ),
        ] );
    }

    public function updateServiceRecord( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'vehicle' => [ 'required', 'exists:vehicles,id' ],
            'company' => [ 'required', 'exists:companies,id' ],
            'service_date' => [ 'required' ],
            'workshop' => [ 'required' ],
            'meter_reading' => [ 'required', 'numeric' ],
            'document_reference' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
        ] );

        $attributeName = [
            'vehicle' => __( 'maintenance_record.vehicle' ),
            'company' => __( 'maintenance_record.company' ),
            'service_date' => __( 'datatables.service_date' ),
            'workshop' => __( 'maintenance_record.workshop' ),
            'meter_reading' => __( 'maintenance_record.meter_reading' ),
            'document_reference' => __( 'maintenance_record.document_reference' ),
            'remarks' => __( 'maintenance_record.remarks' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateServiceRecord = ServiceRecord::find( $request->id );
            $updateServiceRecord->vehicle_id = $request->vehicle;
            $updateServiceRecord->company_id = $request->company;
            $updateServiceRecord->service_date = $request->service_date;
            $updateServiceRecord->workshop = $request->workshop;
            $updateServiceRecord->meter_reading = $request->meter_reading;
            $updateServiceRecord->document_reference = $request->document_reference;
            $updateServiceRecord->remarks = $request->remarks;
            $updateServiceRecord->save();

            ServiceRecordItem::where( 'service_record_id', $updateServiceRecord->id )->delete();

            $items = json_decode( $request->items );
            foreach ( $items as $item ) {
                ServiceRecordItem::create( [
                    'service_record_id' => $updateServiceRecord->id,
                    'type' => $item->type,
                    'meta' => $item->type == 1 ? json_encode( $item->description ) : json_encode( [
                        'description' => $item->description
                    ] ),
                ] );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.service_records' ) ) ] ),
        ] );
    }
}