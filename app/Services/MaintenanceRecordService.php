<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    Document,
    FileManager,
    PartRecord,
    ServiceRecord,
    ServiceRecordItem,
    Tyre,
    TyreRecord,
    TyreRecordItem,
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
                'display_meter_reading',
                'local_service_date',
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

        if ( !empty( $request->service_date ) ) {
            if ( str_contains( $request->service_date, 'to' ) ) {
                $dates = explode( ' to ', $request->service_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'service_records.service_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->service_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'service_records.service_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->workshop ) ) {
            $model->where( 'service_records.workshop', 'LIKE', '%' . $request->workshop . '%' );
            $filter = true;
        }

        if ( !empty( $request->document_reference ) ) {
            $model->where( 'service_records.document_reference', 'LIKE', '%' . $request->document_reference . '%' );
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
            'documents',
        ] )->find( $request->id );

        if ( $serviceRecord ) {

            $serviceRecord->append( [
                'local_service_date',
            ] );

            foreach( $serviceRecord->items as $item ) {
                $item->append( [
                    'meta_object',
                ] );
            }

            foreach( $serviceRecord->documents as $document ) {
                $document->append( [
                    'path',
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

    public static function createServiceRecord( $request ) {

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
                'service_date' => Carbon::createFromFormat( 'Y-m-d', $request->service_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ),
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

            if ( !empty( $request->documents ) ) {

                $documents = explode( ',', $request->documents );

                foreach ( $documents as $document ) {

                    $file = FileManager::find( $document );
                    if ( $file ) {
                        $fileName = explode( '/', $file->file );

                        $target = 'service_records/' . $createServiceRecord->id . '/documents/' . $fileName[1];
                        Storage::disk( 'public' )->move( $file->file, $target );

                        Document::create( [
                            'module_id' => $createServiceRecord->id,
                            'module' => 'App\Models\ServiceRecord',
                            'name' => $file->name,
                            'file' => $target,
                            'type' => $file->type,
                        ] );

                        $file->status = 10;
                        $file->save();
                    }
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.service_records' ) ) ] ),
        ] );
    }

    public static function updateServiceRecord( $request ) {

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
            $updateServiceRecord->service_date = Carbon::createFromFormat( 'Y-m-d', $request->service_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' );
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

            if ( !empty( $request->to_be_delete_documents ) ) {

                $tbds = explode( ',', $request->to_be_delete_documents );

                foreach ( $tbds as $tbd ) {
                    $tbdDocument = Document::where( 'id', $tbd )->first();
                    Storage::disk( 'public' )->delete( $tbdDocument->file );
                    $tbdDocument->delete();
                }
            }

            if ( !empty( $request->documents ) ) {

                $documents = explode( ',', $request->documents );

                foreach ( $documents as $document ) {

                    $file = FileManager::find( $document );
                    if ( $file ) {
                        $fileName = explode( '/', $file->file );

                        $target = 'service_records/' . $updateServiceRecord->id . '/documents/' . $fileName[1];
                        Storage::disk( 'public' )->move( $file->file, $target );

                        Document::create( [
                            'module_id' => $updateServiceRecord->id,
                            'module' => 'App\Models\ServiceRecord',
                            'name' => $file->name,
                            'file' => $target,
                            'type' => $file->type,
                        ] );

                        $file->status = 10;
                        $file->save();
                    }
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.service_records' ) ) ] ),
        ] );
    }

    public static function allTyreRecords( $request ) {

        $tyreRecord = TyreRecord::with( [
            'vehicle',
        ] )->select( 'tyre_records.*' );

        $tyreObject = self::filterTyreRecord( $request, $tyreRecord );
        $tyreRecord = $tyreObject['model'];
        $filter = $tyreObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $tyreRecord->orderBy( 'tyre_records.purchase_date', $dir );
                    break;
                case 2:
                    $tyreRecord->orderBy( 'tyre_records.purchase_bill_reference', $dir );
                    break;
                case 3:
                    $tyreRecord->orderBy( 'tyre_records.vehicle_id', $dir );
                    break;
            }
        }

        $tyreRecordCount = $tyreRecord->count();

        $limit = $request->length;
        $offset = $request->start;

        $tyreRecords = $tyreRecord->skip( $offset )->take( $limit )->get();

        if ( $tyreRecords ) {
            $tyreRecords->append( [
                'local_purchase_date',
                'encrypted_id',
            ] );
        }

        $totalRecord = TyreRecord::count();

        $data = [
            'records' => $tyreRecords,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $tyreRecordCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filterTyreRecord( $request, $model ) {

        $filter = false;

        if ( !empty( $request->purchase_date ) ) {
            if ( str_contains( $request->purchase_date, 'to' ) ) {
                $dates = explode( ' to ', $request->purchase_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'tyre_records.purchase_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->purchase_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'tyre_records.purchase_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->purchase_bill_reference ) ) {
            $model->where( 'tyre_records.purchase_bill_reference', 'LIKE', '%' . $request->purchase_bill_reference . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function validateItemTyreRecord( $request ) {

        $validator = Validator::make( $request->all(), [
            'tyre' => [ 'required', 'exists:tyres,id' ],
            'serial_number' => [ 'required' ],
        ] );

        $attributeName = [
            'tyre' => __( 'maintenance_record.tyre' ),
            'serial_number' => __( 'maintenance_record.serial_number' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $tyre = Tyre::with( [
            'vendor',
        ] )->find( $request->tyre );

        return response()->json( [
            'data' => $tyre,
        ] );
    }

    public static function oneTyreRecord( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $tyreRecord = TyreRecord::with( [
            'items',
            'items.tyre',
            'items.tyre.vendor',
            'vehicle',
            'documents',
        ] )->find( $request->id );

        if ( $tyreRecord ) {
            $tyreRecord->append( [
                'local_purchase_date',
            ] );

            foreach( $tyreRecord->documents as $document ) {
                $document->append( [
                    'path',
                ] );
            }
        }

        return $tyreRecord;
    }

    public static function createTyreRecord( $request ) {

        $validator = Validator::make( $request->all(), [
            'vehicle' => [ 'nullable', 'exists:vehicles,id' ],
            'purchase_date' => [ 'required' ],
            'purchase_bill_reference' => [ 'required' ],
        ] );

        $attributeName = [
            'vehicle' => __( 'maintenance_record.vehicle' ),
            'purchase_date' => __( 'datatables.purchase_date' ),
            'purchase_bill_reference' => __( 'maintenance_record.purchase_bill_reference' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createTyreRecord = TyreRecord::create( [
                'vehicle_id' => $request->vehicle ? $request->vehicle : null,
                'purchase_date' => Carbon::createFromFormat( 'Y-m-d', $request->purchase_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ),
                'purchase_bill_reference' => $request->purchase_bill_reference,
            ] );

            $items = json_decode( $request->items );
            foreach ( $items as $item ) {

                $tyre = Tyre::find( $item->tyre );

                TyreRecordItem::create( [
                    'tyre_record_id' => $createTyreRecord->id,
                    'tyre_id' => $item->tyre,
                    'vendor_id' => $tyre->vendor_id,
                    'serial_number' => $item->serial_number,
                ] );
            }

            if ( !empty( $request->documents ) ) {

                $documents = explode( ',', $request->documents );

                foreach ( $documents as $document ) {

                    $file = FileManager::find( $document );
                    if ( $file ) {
                        $fileName = explode( '/', $file->file );

                        $target = 'tyre_records/' . $createTyreRecord->id . '/documents/' . $fileName[1];
                        Storage::disk( 'public' )->move( $file->file, $target );

                        Document::create( [
                            'module_id' => $createTyreRecord->id,
                            'module' => 'App\Models\TyreRecord',
                            'name' => $file->name,
                            'file' => $target,
                            'type' => $file->type,
                        ] );

                        $file->status = 10;
                        $file->save();
                    }
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.tyre_records' ) ) ] ),
        ] );
    }

    public static function updateTyreRecord( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'vehicle' => [ 'nullable', 'exists:vehicles,id' ],
            'purchase_date' => [ 'required' ],
            'purchase_bill_reference' => [ 'required' ],
        ] );

        $attributeName = [
            'vehicle' => __( 'maintenance_record.vehicle' ),
            'purchase_date' => __( 'datatables.purchase_date' ),
            'purchase_bill_reference' => __( 'maintenance_record.purchase_bill_reference' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateTyreRecord = TyreRecord::find( $request->id );
            $updateTyreRecord->vehicle_id = $request->vehicle ? $request->vehicle : null;
            $updateTyreRecord->purchase_date = Carbon::createFromFormat( 'Y-m-d', $request->purchase_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' );
            $updateTyreRecord->purchase_bill_reference = $request->purchase_bill_reference;
            $updateTyreRecord->save();

            TyreRecordItem::where('tyre_record_id', $updateTyreRecord->id )->delete();

            $items = json_decode( $request->items );
            foreach ( $items as $item ) {

                $tyre = Tyre::find( $item->tyre );

                TyreRecordItem::create( [
                    'tyre_record_id' => $updateTyreRecord->id,
                    'tyre_id' => $item->tyre,
                    'vendor_id' => $tyre->vendor_id,
                    'serial_number' => $item->serial_number,
                ] );
            }

            if ( !empty( $request->to_be_delete_documents ) ) {

                $tbds = explode( ',', $request->to_be_delete_documents );

                foreach ( $tbds as $tbd ) {
                    $tbdDocument = Document::where( 'id', $tbd )->first();
                    Storage::disk( 'public' )->delete( $tbdDocument->file );
                    $tbdDocument->delete();
                }
            }

            if ( !empty( $request->documents ) ) {

                $documents = explode( ',', $request->documents );

                foreach ( $documents as $document ) {

                    $file = FileManager::find( $document );
                    if ( $file ) {
                        $fileName = explode( '/', $file->file );

                        $target = 'tyre_records/' . $updateTyreRecord->id . '/documents/' . $fileName[1];
                        Storage::disk( 'public' )->move( $file->file, $target );

                        Document::create( [
                            'module_id' => $updateTyreRecord->id,
                            'module' => 'App\Models\TyreRecord',
                            'name' => $file->name,
                            'file' => $target,
                            'type' => $file->type,
                        ] );

                        $file->status = 10;
                        $file->save();
                    }
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.tyre_records' ) ) ] ),
        ] );
    }

    public static function allPartRecords( $request ) {

        $partRecord = PartRecord::with( [
            'vendor',
            'part',
        ] )->select( 'part_records.*' );

        $partObject = self::filterPartRecord( $request, $partRecord );
        $partRecord = $partObject['model'];
        $filter = $partObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $partRecord->orderBy( 'part_records.part_date', $dir );
                    break;
            }
        }

        $partRecordCount = $partRecord->count();

        $limit = $request->length;
        $offset = $request->start;

        $partRecords = $partRecord->skip( $offset )->take( $limit )->get();

        if ( $partRecords ) {
            $partRecords->append( [
                'local_part_date',
                'encrypted_id',
            ] );
        }

        $totalRecord = PartRecord::count();

        $data = [
            'records' => $partRecords,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $partRecordCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filterPartRecord( $request, $model ) {

        $filter = false;

        if ( !empty( $request->part_date ) ) {
            if ( str_contains( $request->part_date, 'to' ) ) {
                $dates = explode( ' to ', $request->part_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'part_records.part_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->part_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'part_records.part_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function onePartRecord( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $partRecord = PartRecord::with( [
            'vendor',
            'part',
            'documents',
        ] )->find( $request->id );
        
        if ( $partRecord ) {

            $partRecord->append( [
                'local_part_date',
            ] );

            foreach( $partRecord->documents as $document ) {
                $document->append( [
                    'path',
                ] );
            }
        }

        return $partRecord;
    }

    public static function createPartRecord( $request ) {

        $validator = Validator::make( $request->all(), [
            'part_date' => [ 'required' ],
            'reference' => [ 'required' ],
            'vendor' => [ 'required', 'exists:vendors,id' ],
            'part' => [ 'required', 'exists:parts,id' ],
            'unit_price' => [ 'required' ],
        ] );

        $attributeName = [
            'part_date' => __( 'datatables.part_date' ),
            'reference' => __( 'maintenance_record.reference' ),
            'vendor' => __( 'maintenance_record.vendor' ),
            'part' => __( 'maintenance_record.part' ),
            'unit_price' => __( 'maintenance_record.unit_price' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createPartRecord = PartRecord::create( [
                'part_date' => Carbon::createFromFormat( 'Y-m-d', $request->part_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' ),
                'reference' => $request->reference,
                'vendor_id' => $request->vendor ? $request->vendor : null,
                'part_id' => $request->part ? $request->part : null,
                'unit_price' => $request->unit_price,
            ] );

            if ( !empty( $request->documents ) ) {

                $documents = explode( ',', $request->documents );

                foreach ( $documents as $document ) {

                    $file = FileManager::find( $document );
                    if ( $file ) {
                        $fileName = explode( '/', $file->file );

                        $target = 'part_records/' . $createPartRecord->id . '/documents/' . $fileName[1];
                        Storage::disk( 'public' )->move( $file->file, $target );

                        Document::create( [
                            'module_id' => $createPartRecord->id,
                            'module' => 'App\Models\PartRecord',
                            'name' => $file->name,
                            'file' => $target,
                            'type' => $file->type,
                        ] );

                        $file->status = 10;
                        $file->save();
                    }
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.part_records' ) ) ] ),
        ] );
    }

    public static function updatePartRecord( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'part_date' => [ 'required' ],
            'reference' => [ 'required' ],
            'vendor' => [ 'required', 'exists:vendors,id' ],
            'part' => [ 'required', 'exists:parts,id' ],
            'unit_price' => [ 'required' ],
        ] );

        $attributeName = [
            'part_date' => __( 'datatables.part_date' ),
            'reference' => __( 'maintenance_record.reference' ),
            'vendor' => __( 'maintenance_record.vendor' ),
            'part' => __( 'maintenance_record.part' ),
            'unit_price' => __( 'maintenance_record.unit_price' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updatePartRecord = PartRecord::find( $request->id );
            $updatePartRecord->part_date = Carbon::createFromFormat( 'Y-m-d', $request->part_date, 'Asia/Kuala_Lumpur' )->startOfDay()->timezone( 'UTC' )->format( 'Y-m-d H:i:s' );
            $updatePartRecord->reference = $request->reference;
            $updatePartRecord->vendor_id = $request->vendor;
            $updatePartRecord->part_id = $request->part;
            $updatePartRecord->unit_price = $request->unit_price;
            $updatePartRecord->save();

            if ( !empty( $request->to_be_delete_documents ) ) {

                $tbds = explode( ',', $request->to_be_delete_documents );

                foreach ( $tbds as $tbd ) {
                    $tbdDocument = Document::where( 'id', $tbd )->first();
                    Storage::disk( 'public' )->delete( $tbdDocument->file );
                    $tbdDocument->delete();
                }
            }

            if ( !empty( $request->documents ) ) {

                $documents = explode( ',', $request->documents );

                foreach ( $documents as $document ) {

                    $file = FileManager::find( $document );
                    if ( $file ) {
                        $fileName = explode( '/', $file->file );

                        $target = 'part_records/' . $updatePartRecord->id . '/documents/' . $fileName[1];
                        Storage::disk( 'public' )->move( $file->file, $target );

                        Document::create( [
                            'module_id' => $updatePartRecord->id,
                            'module' => 'App\Models\PartRecord',
                            'name' => $file->name,
                            'file' => $target,
                            'type' => $file->type,
                        ] );

                        $file->status = 10;
                        $file->save();
                    }
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.part_records' ) ) ] ),
        ] );
    }
}