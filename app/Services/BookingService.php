<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Booking,
    Option,
};

use Carbon\Carbon;

class BookingService
{
    public static function allBookings( $request, $export = false ) {

        $booking = Booking::with( [
            'driver',
            'vehicle',
        ] )->select( 'bookings.*' );

        $booking->leftJoin( 'vehicles', 'vehicles.id', '=', 'bookings.vehicle_id' );
        $booking->leftJoin( 'employees', 'employees.id', '=', 'bookings.driver_id' );

        $filterObject = self::filter( $request, $booking );
        $booking = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $booking->orderBy( 'bookings.created_at', $dir );
                    break;
                case 2:
                    $booking->orderBy( 'bookings.reference', $dir );
                    break;
                case 3:
                    $booking->orderBy( 'bookings.invoice_number', $dir );
                    break;
                case 4:
                    $booking->orderBy( 'bookings.delivery_order_number', $dir );
                    break;
                case 5:
                    $booking->orderBy( 'vehicles.license_plate', $dir );
                    break;
                case 6:
                    $booking->orderBy( 'employees.name', $dir );
                    break;
            }
        }

        if ( $export == false ) {

            $bookingCount = $booking->count();

            $limit = $request->length;
            $offset = $request->start;

            $bookings = $booking->skip( $offset )->take( $limit )->get();

            if ( $bookings ) {
                $bookings->append( [
                    'encrypted_id',
                ] );
            }

            $totalRecord = Booking::count();

            $data = [
                'bookings' => $bookings,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $bookingCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

        } else {

            return $booking->get();
        }        
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

                $model->whereBetween( 'bookings.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'bookings.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'bookings.reference', $request->reference );
            $filter = true;
        }

        if ( !empty( $request->invoice_number ) ) {
            $model->where( 'bookings.invoice_number', $request->invoice_number );
            $filter = true;
        }

        if ( !empty( $request->delivery_order_number ) ) {
            $model->where( 'bookings.delivery_order_number', $request->delivery_order_number );
            $filter = true;
        }

        if ( !empty( $request->license_plate ) ) {
            $model->where( 'vehicles.license_plate', $request->license_plate );
            $filter = true;
        }

        if ( !empty( $request->driver ) ) {
            $model->where( 'employees.name', $request->driver );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneBooking( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $booking = Booking::with( [
            'employee',
        ] )->find( $request->id );

        return response()->json( $booking );
    }

    public static function getLatestBookingIncrement() {

        $latestBooking = Booking::latest()->first();
        if ( $latestBooking ) {
            $parts = explode( ' ', $latestBooking->reference );
            return $parts[1];
        }

        return 0;
    }

    public static function createBooking( $request ) {

        $validator = Validator::make( $request->all(), [
            'reference' => [ 'required', 'unique:bookings' ],
            'customer_name' => [ 'required' ],
            'vehicle' => [ 'required', 'exists:vehicles,id' ],
            'delivery_order_number' => [ 'required' ],
            'delivery_order_date' => [ 'required' ],
            'pickup_address_address_1' => [ 'required' ],
            'pickup_address_city' => [ 'required' ],
            'pickup_address_state' => [ 'required' ],
            'dropoff_address_destination' => [ 'required' ],
            'dropoff_address_address_1' => [ 'required' ],
            'dropoff_address_city' => [ 'required' ],
            'dropoff_address_state' => [ 'required' ],
            'pickup_date' => [ 'required' ],
            'dropoff_date' => [ 'required' ],
            'company' => [ 'required' ],
            'customer_type' => [ 'required' ],
            'customer_quantity' => [ 'required' ],
            'customer_uom' => [ 'required' ],
            'customer_rate' => [ 'required' ],
            'driver' => [ 'required', 'exists:employees,id' ],
            'driver_quantity' => [ 'required' ],
            'driver_uom' => [ 'required' ],
            'driver_rate' => [ 'required' ],
            'driver_percentage' => [ 'required' ],
        ] );

        $attributeName = [

        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            Booking::create( [
                'reference' => $request->reference,
                'customer_name' => $request->customer_name,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'vehicle_id' => $request->vehicle,
                'delivery_order_number' => $request->delivery_order_number,
                'delivery_order_date' => $request->delivery_order_date,
                'pickup_address' => json_encode( [
                    'a1' => $request->pickup_address_address_1,
                    'a2' => $request->pickup_address_address_2,
                    'c' => $request->pickup_address_city,
                    'p' => $request->pickup_address_postcode,
                    's' => $request->pickup_address_state,
                ] ),
                'dropoff_address' => json_encode( [
                    'd' => $request->dropoff_address_destination,
                    'a1' => $request->pickup_address_address_1,
                    'a2' => $request->pickup_address_address_2,
                    'c' => $request->pickup_address_city,
                    'p' => $request->pickup_address_postcode,
                    's' => $request->pickup_address_state,
                ] ),
                'pickup_date' => Carbon::createFromFormat( 'Y-m-d H:i', $request->pickup_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ),
                'dropoff_date' => Carbon::createFromFormat( 'Y-m-d H:i', $request->dropoff_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ),
                'company_id' => $request->company,
                'customer_type' => $request->customer_type,
                'customer_quantity' => $request->customer_quantity,
                'customer_unit_of_measurement' => $request->customer_uom,
                'customer_rate' => $request->customer_rate,
                'customer_total_amount' => $request->customer_total_amount,
                'customer_remarks' => $request->customer_remarks,
                'driver_id' => $request->driver,
                'driver_quantity' => $request->driver_quantity,
                'driver_unit_of_measurement' => $request->driver_uom,
                'driver_rate' => $request->driver_rate,
                'driver_total_amount' => $request->driver_total_amount,
                'driver_percentage' => $request->driver_percentage,
                'driver_final_amount' => $request->driver_final_amount,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.bookings' ) ) ] ),
        ] );
    }
}