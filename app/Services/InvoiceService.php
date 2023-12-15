<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use Helper;

use App\Models\{
    Company,
    Customer,
    Invoice,
    Booking,
};
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceService
{
    public static function validateInputs( $request) {
        $validator = Validator::make( $request->all(), [
            'type' => [ 'nullable' ],
            'company_id' => [ 'required', 'exists:companies,id' ],
            'customer_id' => [ 'required', 'exists:customers,id' ],
            'invoice_number' => [ 'required' ],
            'invoice_date' => [ 'required' ],
            'delivery_order_number' => [ 'required' ],
        ] );

        // $attributeName = [
        //     'company_id' => __( 'invoice.company_id' ),
        //     'customer_id' => __( 'invoice.customer_id' ),
        //     'invoice_number' => __( 'invoice.invoice_number' ),
        //     'invoice_date' => __( 'invoice.invoice_date' ),
        //     'delivery_order_number' => __( 'invoice.delivery_order_number' ),
        // ];

        // foreach( $attributeName as $key => $aName ) {
        //     $attributeName[$key] = strtolower( $aName );
        // }

        // return $validator->setAttributeNames( $attributeName )->validate();

        return $validator->validate();
    }

    public static function createInvoice( $request ) {
        
        self::validateInputs($request);

        DB::beginTransaction();
        try {
            $invoiceCreate = Invoice::create([
                'invoice_date' => $request->invoice_date,
                'invoice_number' => $request->invoice_number,
                'company_id' => $request->company_id,
                'customer_id' => $request->customer_id,
                'do_number' => $request->delivery_order_number,
            ]);

            if( $invoiceCreate ){

                $deliveryOrderNumbers = explode( ',', $request->delivery_order_number );

                Booking::whereIn( 'delivery_order_number', $deliveryOrderNumbers )
                    ->update([
                        'invoice_date' => $invoiceCreate->invoice_date,
                        'invoice_number' => $invoiceCreate->invoice_number
                    ]);
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.invoices' ) ) ] ),
        ] );
    }
    
    public static function updateInvoice( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        self::validateInputs($request);

        DB::beginTransaction();

        try {
            $updateInvoice = Invoice::find( $request->id );

            $oldDo = $updateInvoice->do_number;
    
            $updateInvoice->company_id = $request->company_id;
            $updateInvoice->customer_id = $request->customer_id;
            $updateInvoice->invoice_number = $request->invoice_number;
            $updateInvoice->invoice_date = $request->invoice_date ? Carbon::createFromFormat( 'Y-m-d', $request->invoice_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            $updateInvoice->do_number = $request->delivery_order_number;
            $updateInvoice->save();

            if ( $updateInvoice ) {
                $deliveryOrderNumbers = explode( ',', $updateInvoice->do_number );
                $oldDeliveryOrderNumbers = explode( ',', $oldDo );
            
                Booking::whereIn( 'delivery_order_number', $oldDeliveryOrderNumbers )
                    ->update([
                        'invoice_date' => null,
                        'invoice_number' => null
                    ]);
            
                Booking::whereIn( 'delivery_order_number', $deliveryOrderNumbers )
                    ->update([
                        'invoice_date' => $updateInvoice->invoice_date,
                        'invoice_number' => $updateInvoice->invoice_number
                    ]);
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.invoices' ) ) ] ),
        ] );
    }

    public static function downloadInvoice( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'invoice.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate(); 

        $invoice = Invoice::find($request->id);
        $data = self::retrieveInvoiceDetail($invoice);
        $company = Company::find($invoice->company_id);
        $customer = Customer::find($invoice->customer_id);
        $invoice_detail['invoice_number'] = $invoice->invoice_number;
        $invoice_detail['invoice_date'] = $invoice->invoice_date;
        $grouped = [];
        $uom = [
            '1' => __( 'booking.ton' ),
            '2' => __( 'booking.trip' ),
            '3' => __( 'booking.pallets' ),
        ];

        foreach ($data as $item) {
            $licensePlate = $item->license_plate;
            
            if (!isset($grouped[$licensePlate])) {
                $grouped[$licensePlate] = [
                    'items' => [],
                    'total_amount' => 0,
                ];
            }

            $item->references = explode(',', $item->references);
            $item->customer_unit_of_measurement = $uom[$item->customer_unit_of_measurement];

            $item->pickup_address = DB::table('booking_addresses')->where(['booking_id' => $item->id, 'type' => 1])->first();
            $item->dropoff_address = DB::table('booking_addresses')->where(['booking_id' => $item->id, 'type' => 2])->first();
            $grouped[$licensePlate]['items'][] = $item;
            $grouped[$licensePlate]['total_amount'] += floatval($item->customer_total_amount);
        }
        $pdf = Pdf::loadView('admin.invoice.preview', compact('grouped', 'invoice_detail', 'company', 'customer'));
        return $pdf->download('invoice.pdf');
    }
    
    public static function retrieveInvoiceDetail($request){
        $delivery_order_numbers = array_map('trim', explode(',', $request->delivery_order_number ?? $request->do_number));

        DB::beginTransaction();
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $data = DB::table('bookings')
                ->select([
                    'bookings.id',
                    'delivery_order_date',
                    DB::raw("GROUP_CONCAT(DISTINCT reference) as `references`"),
                    'vehicles.license_plate',
                    DB::raw("SUM(bookings.customer_quantity) as customer_quantity"),
                    'bookings.customer_rate',
                    DB::raw("SUM(bookings.customer_total_amount) as customer_total_amount"),
                    'bookings.customer_unit_of_measurement',
                ])
                ->leftJoin('vehicles', 'vehicles.id', 'vehicle_id')
                ->whereIn('delivery_order_number', $delivery_order_numbers)
                ->whereDate('invoice_date', $request->invoice_date)
                ->where('invoice_number', $request->invoice_number)
                ->groupBy('delivery_order_date', 'license_plate', 'invoice_date')
                ->orderBy('delivery_order_date', 'asc')
                ->get();
        DB::rollback();

        return $data;
    }

     public static function allInvoices( $request, $export = false ) {

        $invoices = Invoice::with([
            'customer',
            'company',
        ])->select( 'invoices.*' )
        ->leftJoin( 'customers', 'customers.id', '=', 'invoices.customer_id' )
        ->leftJoin( 'companies', 'companies.id', '=', 'invoices.company_id' );

        $filterObject = self::filter( $request, $invoices );
        $invoice = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $invoice->orderBy( 'invoices.company_id', $dir );
                    break;
                case 2:
                    $invoice->orderBy( 'invoices.customer_id', $dir );
                    break;
                case 3:
                    $invoice->orderBy( 'invoices.invoice_number', $dir );
                    break;
                case 4:
                    $invoice->orderBy( 'invoices.invoice_date', $dir );
                    break;
                case 5:
                    $invoice->orderBy( 'invoices.delivery_order_number', $dir );
                    break;
            }
        }

        if ( $export == false ) {

            $invoiceCount = $invoice->count();

            $limit = $request->length;
            $offset = $request->start;

            $invoices = $invoice->skip( $offset )->take( $limit )->get();

            if ( $invoices ) {
                $invoices->append( [
                    'encrypted_id',
                ] );
            }

            $totalRecord = Invoice::count();

            $data = [
                'invoices' => $invoices,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $invoiceCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

        } else {

            return $invoice->get();
        }        
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->invoice_date ) ) {
            $model->whereDate( 'invoices.invoice_date', 'LIKE', '%' . $request->invoice_date . '%' );
            $filter = true;
        }

        if ( !empty( $request->customer ) ) {
            $model->where( 'customers.name', 'LIKE', '%' . $request->customer . '%' );
            $filter = true;
        }

        if ( !empty( $request->company ) ) {
            $model->where( 'companies.name', 'LIKE', '%' . $request->company . '%' );
            $filter = true;
        }

        if ( !empty( $request->invoice_number ) ) {
            $model->where( 'invoices.invoice_number', 'LIKE', '%' . $request->invoice_number . '%' );
            $filter = true;
        }

        if ( !empty( $request->delivery_order_number ) ) {
            $model->where( 'invoices.delivery_order_number', 'LIKE', '%' . $request->delivery_order_number . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function get() {

        $companies = Invoice::get()->toArray();

        return $companies;
    }

    public static function oneInvoice( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $booking = Invoice::with( [
            'customer',
            'company',
        ] )->find( $request->id );
        
        return response()->json( $booking );
    }

    public static function deleteInvoice( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'invoice.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Invoice::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.invoices' ) ) ] ),
        ] );
    }
}