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

use Helper;

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
            'driver',
            'vehicle',
        ] )->find( $request->id );

        if ( $booking ) {
            $booking->append( [
                'display_pickup_address',
                'display_drop_off_address',
            ] );
        }

        return response()->json( $booking );
    }

    public static function getLatestBookingIncrement() {

        $latestBooking = Booking::latest( 'id' )->first();
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
            'reference' => __( 'booking.reference' ),
            'customer_name' => __( 'booking.customer_name' ),
            'invoice_number' => __( 'booking.invoice_number' ),
            'invoice_date' => __( 'booking.invoice_date' ),
            'vehicle' => __( 'booking.vehicle' ),
            'delivery_order_number' => __( 'booking.delivery_order_number' ),
            'delivery_order_date' => __( 'booking.delivery_order_date' ),
            'pickup_address_address_1' => __( 'booking.address_1' ),
            'pickup_address_address_2' => __( 'booking.address_2' ),
            'pickup_address_city' => __( 'booking.city' ),
            'pickup_address_postcode' => __( 'booking.postcode' ),
            'pickup_address_state' => __( 'booking.state' ),
            'dropoff_address_destination' => __( 'booking.destination' ),
            'dropoff_address_address_1' => __( 'booking.address_1' ),
            'dropoff_address_address_2' => __( 'booking.address_2' ),
            'dropoff_address_city' => __( 'booking.city' ),
            'dropoff_address_postcode' => __( 'booking.postcode' ),
            'dropoff_address_state' => __( 'booking.state' ),
            'pickup_date' => __( 'booking.pickup_date' ),
            'dropoff_date' => __( 'booking.dropoff_date' ),
            'company' => __( 'booking.company' ),
            'customer_type' => __( 'booking.customer_type' ),
            'company' => __( 'booking.company' ),
            'customer_type' => __( 'booking.customer_type' ),
            'customer_quantity' => __( 'booking.quantity' ),
            'customer_uom' => __( 'booking.uom' ),
            'customer_rate' => __( 'booking.customer_rate' ),
            'driver' => __( 'booking.driver' ),
            'driver_quantity' => __( 'booking.quantity' ),
            'driver_uom' => __( 'booking.uom' ),
            'driver_rate' => __( 'booking.driver_rate' ),
            'driver_percentage' => __( 'booking.percentage' ),
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

    public static function updateBooking( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'reference' => [ 'required', 'unique:bookings,reference,' . $request->id ],
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
            'reference' => __( 'booking.reference' ),
            'customer_name' => __( 'booking.customer_name' ),
            'invoice_number' => __( 'booking.invoice_number' ),
            'invoice_date' => __( 'booking.invoice_date' ),
            'vehicle' => __( 'booking.vehicle' ),
            'delivery_order_number' => __( 'booking.delivery_order_number' ),
            'delivery_order_date' => __( 'booking.delivery_order_date' ),
            'pickup_address_address_1' => __( 'booking.address_1' ),
            'pickup_address_address_2' => __( 'booking.address_2' ),
            'pickup_address_city' => __( 'booking.city' ),
            'pickup_address_postcode' => __( 'booking.postcode' ),
            'pickup_address_state' => __( 'booking.state' ),
            'dropoff_address_destination' => __( 'booking.destination' ),
            'dropoff_address_address_1' => __( 'booking.address_1' ),
            'dropoff_address_address_2' => __( 'booking.address_2' ),
            'dropoff_address_city' => __( 'booking.city' ),
            'dropoff_address_postcode' => __( 'booking.postcode' ),
            'dropoff_address_state' => __( 'booking.state' ),
            'pickup_date' => __( 'booking.pickup_date' ),
            'dropoff_date' => __( 'booking.dropoff_date' ),
            'company' => __( 'booking.company' ),
            'customer_type' => __( 'booking.customer_type' ),
            'company' => __( 'booking.company' ),
            'customer_type' => __( 'booking.customer_type' ),
            'customer_quantity' => __( 'booking.quantity' ),
            'customer_uom' => __( 'booking.uom' ),
            'customer_rate' => __( 'booking.customer_rate' ),
            'driver' => __( 'booking.driver' ),
            'driver_quantity' => __( 'booking.quantity' ),
            'driver_uom' => __( 'booking.uom' ),
            'driver_rate' => __( 'booking.driver_rate' ),
            'driver_percentage' => __( 'booking.percentage' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateBooking = Booking::find( $request->id );
            $updateBooking->reference = $request->reference;
            $updateBooking->customer_name = $request->customer_name;
            $updateBooking->invoice_number = $request->invoice_number;
            $updateBooking->invoice_date = $request->invoice_date;
            $updateBooking->vehicle_id = $request->vehicle;
            $updateBooking->delivery_order_number = $request->delivery_order_number;
            $updateBooking->delivery_order_date = $request->delivery_order_date;
            $updateBooking->pickup_address = json_encode( [
                'a1' => $request->pickup_address_address_1,
                'a2' => $request->pickup_address_address_2,
                'c' => $request->pickup_address_city,
                'p' => $request->pickup_address_postcode,
                's' => $request->pickup_address_state,
            ] );
            $updateBooking->dropoff_address = json_encode( [
                'd' => $request->dropoff_address_destination,
                'a1' => $request->pickup_address_address_1,
                'a2' => $request->pickup_address_address_2,
                'c' => $request->pickup_address_city,
                'p' => $request->pickup_address_postcode,
                's' => $request->pickup_address_state,
            ] );
            $updateBooking->pickup_date = Carbon::createFromFormat( 'Y-m-d H:i', $request->pickup_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' );
            $updateBooking->dropoff_date = Carbon::createFromFormat( 'Y-m-d H:i', $request->dropoff_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' );
            $updateBooking->company_id = $request->company;
            $updateBooking->customer_type = $request->customer_type;
            $updateBooking->customer_quantity = $request->customer_quantity;
            $updateBooking->customer_unit_of_measurement = $request->customer_uom;
            $updateBooking->customer_rate = $request->customer_rate;
            $updateBooking->customer_total_amount = $request->customer_total_amount;
            $updateBooking->customer_remarks = $request->customer_remarks;
            $updateBooking->driver_id = $request->driver;
            $updateBooking->driver_quantity = $request->driver_quantity;
            $updateBooking->driver_unit_of_measurement = $request->driver_uom;
            $updateBooking->driver_rate = $request->driver_rate;
            $updateBooking->driver_total_amount = $request->driver_total_amount;
            $updateBooking->driver_percentage = $request->driver_percentage;
            $updateBooking->driver_final_amount = $request->driver_final_amount;
            $updateBooking->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.bookings' ) ) ] ),
        ] );
    }

    public static function exportBookings( $request ) {

        $bookings = self::allBookings( $request, true );

        $html = '<table>';
        $html .= '
        <thead>
            <tr>
                <th><strong>' .__( 'booking.e_ref_no' ). '</strong></th>
                <th><strong>' .__( 'booking.e_mth' ). '</strong></th>
                <th><strong>' .__( 'booking.e_trip_no' ). '</strong></th>
                <th><strong>' .__( 'booking.e_inv_no' ). '</strong></th>
                <th><strong>' .__( 'booking.e_inv_date' ). '</strong></th>
                <th><strong>' .__( 'booking.e_lorry_no' ). '</strong></th>
                <th><strong>' .__( 'booking.e_do_ref' ). '</strong></th>
                <th><strong>' .__( 'booking.e_do_date' ). '</strong></th>
                <th><strong>' .__( 'booking.e_customer' ). '</strong></th>
                <th><strong>' .__( 'booking.e_pickup_from' ). '</strong></th>
                <th><strong>' .__( 'booking.e_dropoff_to' ). '</strong></th>
                <th><strong>' .__( 'booking.e_destination' ). '</strong></th>
                <th><strong>' .__( 'booking.e_qty' ). '</strong></th>
                <th><strong>' .__( 'booking.e_uom' ). '</strong></th>
                <th><strong>' .__( 'booking.e_customer_rate' ). '</strong></th>
                <th><strong>' .__( 'booking.e_driver_rate' ). '</strong></th>
                <th><strong>' .__( 'booking.e_amount' ). '</strong></th>
                <th><strong>' .__( 'booking.e_type' ). '</strong></th>
                <th><strong>' .__( 'booking.e_company' ). '</strong></th>
                <th><strong>' .__( 'booking.e_remark_1' ). '</strong></th>
                <th><strong>' .__( 'booking.e_transporter' ). '</strong></th>
                <th><strong>' .__( 'booking.e_qty' ). '</strong></th>
                <th><strong>' .__( 'booking.e_uom' ). '</strong></th>
                <th><strong>' .__( 'booking.e_driver_rate' ). '</strong></th>
                <th><strong>' .__( 'booking.e_total' ). '</strong></th>
                <th><strong>%</strong></th>
                <th><strong>' .__( 'booking.e_driver_amount' ). '</strong></th>
            </tr>
        </thead>
        ';
        $html .= '<tbody>';

        $customerType = [
            '1' => strtoupper( __( 'booking.sewa' ) ),
            '2' => strtoupper( __( 'booking.logs' ) ),
            '3' => strtoupper( __( 'booking.others' ) ),
        ];
        $uom = [
            '1' => strtoupper( __( 'booking.ton' ) ),
            '2' => strtoupper( __( 'booking.trip' ) ),
            '3' => strtoupper( __( 'booking.pallets' ) ),
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