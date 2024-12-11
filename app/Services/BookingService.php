<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    Booking,
    BookingAddress,
    FileManager,
    Option,
};

use Helper;

use Carbon\Carbon;

class BookingService
{
    public static function calendarAllBookings( $request ) {

        $bookings = Booking::where( 'invoice_date', '>=', $request->start )
            ->where( 'invoice_date', '<=', $request->end )
            ->orderBy( 'invoice_date', 'ASC' )
            ->get();

        $currentBookings = [];
        foreach ( $bookings as $booking ) {

            $plateNumber = $booking->vehicle ? $booking->vehicle->license_plate : '-';
            $notes = $booking->notes ? $booking->notes : '-';

            array_push( $currentBookings, [
                'id' => Helper::encode( $booking->id ),
                'allDay' => true,
                'start' => $booking->invoice_date . ' 00:00:00',
                'end' => $booking->invoice_date . ' 23:59:59',
                'title' => [
                    'html' => 'Reference:' . $booking->reference . '<br>Plate Number:' . $plateNumber . '<br>Notes:' . $notes,
                ],
                'color' => '#aad418',
            ] );
        }

        return response()->json( $currentBookings );
    }

    public static function allBookings( $request, $export = false ) {

        $booking = Booking::with( [
            'driver',
            'vehicle',
            'pickupAddresses',
            'dropoffAddresses',
        ] )->select( 'bookings.*' );

        $booking->leftJoin( 'vehicles', 'vehicles.id', '=', 'bookings.vehicle_id' );
        $booking->leftJoin( 'employees', 'employees.id', '=', 'bookings.driver_id' );
        $booking->leftJoin( 'booking_addresses AS pickup_addresses', 'pickup_addresses.booking_id', '=', 'bookings.id' )->where( 'pickup_addresses.type', '=', 1 );
        $booking->leftJoin( 'booking_addresses AS dropoff_addresses', 'dropoff_addresses.booking_id', '=', 'bookings.id' )->where( 'dropoff_addresses.type', '=', 2 );
            
        $filterObject = self::filter( $request, $booking );
        $booking = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $booking->orderBy( 'bookings.delivery_order_date', $dir );
                    break;
                case 2:
                    $booking->orderBy( 'vehicles.license_plate', $dir );
                    break;
                case 3:
                    $booking->orderBy( 'bookings.customer_name', $dir );
                    break;
                case 4:
                    $booking->orderBy( 'pickup_addresses.city', $dir );
                    break;
                case 5:
                    $booking->orderBy( 'dropoff_addresses.city', $dir );
                    break;
                case 5:
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
                    'delivery_order_date'
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

        if ( !empty( $request->delivery_order_date ) ) {
            if ( str_contains( $request->delivery_order_date, 'to' ) ) {
                $dates = explode( ' to ', $request->delivery_order_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'bookings.delivery_order_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'bookings.delivery_order_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->vehicle ) ) {

            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'vehicle', function ( $q ) use ( $request ) {
                    $q->where( 'license_plate', 'LIKE', '%' . $request->vehicle . '%' );
                });
            });

            $filter = true;
        }

        if ( !empty( $request->customer ) ) {
            $model->where( 'bookings.customer_name', 'LIKE', '%' . $request->customer . '%' );
            $filter = true;
        }

        if ( !empty( $request->pickup_city ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'pickupAddresses', function ( $q ) use ( $request ) {
                    $q->where( 'type', 1 )->where( 'city', 'LIKE', '%' . $request->pickup_city . '%' );
                });
            });

            $filter = true;
        }

        if ( !empty( $request->dropoff_city ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'dropoffAddresses', function ( $q ) use ( $request ) {
                    $q->where( 'type', 2 )->where( 'city', 'LIKE', '%' . $request->dropoff_city . '%' );
                });
            });

            $filter = true;
        }

        if ( !empty( $request->driver ) ) {
            $model->where( 'employees.name', 'LIKE', '%' . $request->driver . '%' );
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
            'pickupAddresses',
            'dropoffAddresses',
        ] )->find( $request->id );

        if ( $booking ) {
            $booking->append( [
                'delivery_order_image_path',
                'display_pickup_address',
                'display_drop_off_address',
            ] );
        }

        return response()->json( $booking );
    }

    public static function getLatestBookingIncrement() {

        $latestBooking = Booking::where( 'reference', 'LIKE', '%' . date( 'Y/m' ) . '%' )
            ->orderBy( 'reference', 'DESC' )
            ->first();

        if ( $latestBooking ) {
            $parts = explode( ' ', $latestBooking->reference );
            return $parts[1];
        }

        return 0;
    }

    public static function createBooking( $request ) {

        $request->merge( [
            'pickup_addresses' => json_decode( $request->pickup_addresses, true ),
            'dropoff_addresses' => json_decode( $request->dropoff_addresses, true ),
        ] );

        $validator = Validator::make( $request->all(), [
            'reference' => [ 'required', 'unique:bookings' ],
            // 'customer_name' => [ 'required' ],
            'vehicle' => [ 'required', 'exists:vehicles,id' ],
            // 'delivery_order_number' => [ 'required' ],
            // 'delivery_order_date' => [ 'required' ],
            // 'pickup_address_address_1' => [ 'required' ],
            // 'pickup_address_city' => [ 'required' ],
            // 'pickup_address_state' => [ 'required' ],
            'pickup_addresses.*.pickup_address_postcode' => [ 'nullable', 'digits:5' ],
            // 'dropoff_address_destination' => [ 'required' ],
            // 'dropoff_address_address_1' => [ 'required' ],
            // 'dropoff_address_city' => [ 'required' ],
            // 'dropoff_address_state' => [ 'required' ],
            'dropoff_addresses.*.dropoff_address_postcode' => [ 'nullable', 'digits:5' ],
            'pickup_date' => [ 'required' ],
            'dropoff_date' => [ 'required' ],
            'company' => [ 'nullable' ],
            'customer_type' => [ 'nullable' ],
            'customer_quantity' => [ 'required' ],
            'customer_uom' => [ 'nullable' ],
            'customer_rate' => [ 'required' ],
            'driver' => [ 'nullable', 'exists:employees,id' ],
            'driver_quantity' => [ 'required' ],
            'driver_uom' => [ 'nullable' ],
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
            'delivery_order_image' => __( 'booking.delivery_order_image' ),
            'pickup_address_address_1' => __( 'booking.address_1' ),
            'pickup_address_address_2' => __( 'booking.address_2' ),
            'pickup_address_city' => __( 'booking.city' ),
            'pickup_address_postcode' => __( 'booking.postcode' ),
            'pickup_address_state' => __( 'booking.state' ),
            'pickup_addresses.*.pickup_address_postcode' => __( 'booking.postcode' ),
            'dropoff_address_destination' => __( 'booking.destination' ),
            'dropoff_address_address_1' => __( 'booking.address_1' ),
            'dropoff_address_address_2' => __( 'booking.address_2' ),
            'dropoff_address_city' => __( 'booking.city' ),
            'dropoff_address_postcode' => __( 'booking.postcode' ),
            'dropoff_address_state' => __( 'booking.state' ),
            'dropoff_addresses.*.dropoff_address_postcode' => __( 'booking.postcode' ),
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
            $createBooking = Booking::create( [
                'reference' => $request->reference,
                'customer_name' => $request->customer_name,
                'notes' => $request->notes,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date ? Carbon::createFromFormat( 'Y-m-d', $request->invoice_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                'vehicle_id' => $request->vehicle,
                'delivery_order_number' => $request->delivery_order_number,
                'delivery_order_date' => $request->delivery_order_date ? Carbon::createFromFormat( 'Y-m-d', $request->delivery_order_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                // 'pickup_address' => json_encode( [
                //     'a1' => $request->pickup_address_address_1,
                //     'a2' => $request->pickup_address_address_2,
                //     'c' => $request->pickup_address_city,
                //     'p' => $request->pickup_address_postcode,
                //     's' => $request->pickup_address_state,
                // ] ),
                // 'dropoff_address' => json_encode( [
                //     'd' => $request->dropoff_address_destination,
                //     'a1' => $request->pickup_address_address_1,
                //     'a2' => $request->pickup_address_address_2,
                //     'c' => $request->pickup_address_city,
                //     'p' => $request->pickup_address_postcode,
                //     's' => $request->pickup_address_state,
                // ] ),
                'pickup_date' => $request->pickup_date ? Carbon::createFromFormat( 'Y-m-d H:i', $request->pickup_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                'dropoff_date' => $request->dropoff_date ? Carbon::createFromFormat( 'Y-m-d H:i', $request->dropoff_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                'company_id' => $request->company,
                'customer_type' => $request->customer_type,
                'customer_quantity' => $request->customer_quantity,
                'customer_unit_of_measurement' => $request->customer_uom,
                'customer_rate' => $request->customer_rate,
                'customer_total_amount' => str_replace(',', '', $request->customer_total_amount),
                'customer_remarks' => $request->customer_remarks,
                'driver_id' => $request->driver,
                'driver_quantity' => $request->driver_quantity,
                'driver_unit_of_measurement' => $request->driver_uom,
                'driver_rate' => $request->driver_rate,
                'driver_total_amount' => str_replace(',', '', $request->driver_total_amount),
                'driver_percentage' => $request->driver_percentage,
                'driver_final_amount' => $request->driver_final_amount,
            ] );

            foreach ( $request->pickup_addresses as $pickupAddress ) {

                BookingAddress::create( [
                    'booking_id' => $createBooking->id,
                    'address_1' => $pickupAddress['pickup_address_address_1'],
                    'address_2' => $pickupAddress['pickup_address_address_2'],
                    'city' => $pickupAddress['pickup_address_city'],
                    'state' => $pickupAddress['pickup_address_state'],
                    'postcode' => $pickupAddress['pickup_address_postcode'],
                    'type' => 1,
                ] );
            }

            foreach ( $request->dropoff_addresses as $dropoffAddress ) {
                
                BookingAddress::create( [
                    'booking_id' => $createBooking->id,
                    'address_1' => $dropoffAddress['dropoff_address_address_1'],
                    'address_2' => $dropoffAddress['dropoff_address_address_2'],
                    'city' => $dropoffAddress['dropoff_address_city'],
                    'state' => $dropoffAddress['dropoff_address_state'],
                    'postcode' => $dropoffAddress['dropoff_address_postcode'],
                    'destination' => $dropoffAddress['dropoff_address_destination'],
                    'type' => 2,
                ] );
            }

            $file = FileManager::find( $request->delivery_order_image );
            if ( $file ) {
                $fileName = explode( '/', $file->file );
                $target = 'bookings/' . $createBooking->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $file->file, $target );

                $createBooking->delivery_order_image = $target;
                $createBooking->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.bookings' ) ) ] ),
        ] );
    }

    public static function updateBooking( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
            'pickup_addresses' => json_decode( $request->pickup_addresses, true ),
            'dropoff_addresses' => json_decode( $request->dropoff_addresses, true ),
        ] );

        $validator = Validator::make( $request->all(), [
            'reference' => [ 'required', 'unique:bookings,reference,' . $request->id ],
            // 'customer_name' => [ 'required' ],
            'vehicle' => [ 'required', 'exists:vehicles,id' ],
            // 'delivery_order_number' => [ 'required' ],
            // 'delivery_order_date' => [ 'required' ],
            // 'pickup_address_address_1' => [ 'required' ],
            // 'pickup_address_city' => [ 'required' ],
            // 'pickup_address_state' => [ 'required' ],
            'pickup_addresses.*.pickup_address_postcode' => [ 'nullable', 'digits:5' ],
            // 'dropoff_address_destination' => [ 'required' ],
            // 'dropoff_address_address_1' => [ 'required' ],
            // 'dropoff_address_city' => [ 'required' ],
            // 'dropoff_address_state' => [ 'required' ],
            'dropoff_addresses.*.dropoff_address_postcode' => [ 'nullable', 'digits:5' ],
            'pickup_date' => [ 'required' ],
            'dropoff_date' => [ 'required' ],
            'company' => [ 'nullable' ],
            'customer_type' => [ 'nullable' ],
            'customer_quantity' => [ 'required' ],
            'customer_uom' => [ 'nullable' ],
            'customer_rate' => [ 'required' ],
            'driver' => [ 'nullable', 'exists:employees,id' ],
            'driver_quantity' => [ 'required' ],
            'driver_uom' => [ 'nullable' ],
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
            'delivery_order_image' => __( 'booking.delivery_order_image' ),
            'pickup_address_address_1' => __( 'booking.address_1' ),
            'pickup_address_address_2' => __( 'booking.address_2' ),
            'pickup_address_city' => __( 'booking.city' ),
            'pickup_address_postcode' => __( 'booking.postcode' ),
            'pickup_address_state' => __( 'booking.state' ),
            'pickup_addresses.*.pickup_address_postcode' => __( 'booking.postcode' ),
            'dropoff_address_destination' => __( 'booking.destination' ),
            'dropoff_address_address_1' => __( 'booking.address_1' ),
            'dropoff_address_address_2' => __( 'booking.address_2' ),
            'dropoff_address_city' => __( 'booking.city' ),
            'dropoff_address_postcode' => __( 'booking.postcode' ),
            'dropoff_address_state' => __( 'booking.state' ),
            'dropoff_addresses.*.dropoff_address_postcode' => __( 'booking.postcode' ),
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
            $updateBooking->notes = $request->notes;
            $updateBooking->invoice_number = $request->invoice_number;
            $updateBooking->invoice_date = $request->invoice_date ? Carbon::createFromFormat( 'Y-m-d', $request->invoice_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            $updateBooking->vehicle_id = $request->vehicle;
            $updateBooking->delivery_order_number = $request->delivery_order_number;
            $updateBooking->delivery_order_date = $request->delivery_order_date ? Carbon::createFromFormat( 'Y-m-d', $request->delivery_order_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            // $updateBooking->pickup_address = json_encode( [
            //     'a1' => $request->pickup_address_address_1,
            //     'a2' => $request->pickup_address_address_2,
            //     'c' => $request->pickup_address_city,
            //     'p' => $request->pickup_address_postcode,
            //     's' => $request->pickup_address_state,
            // ] );
            // $updateBooking->dropoff_address = json_encode( [
            //     'd' => $request->dropoff_address_destination,
            //     'a1' => $request->pickup_address_address_1,
            //     'a2' => $request->pickup_address_address_2,
            //     'c' => $request->pickup_address_city,
            //     'p' => $request->pickup_address_postcode,
            //     's' => $request->pickup_address_state,
            // ] );
            $updateBooking->pickup_date = $request->pickup_date ? Carbon::createFromFormat( 'Y-m-d H:i', $request->pickup_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            $updateBooking->dropoff_date = $request->dropoff_date ? Carbon::createFromFormat( 'Y-m-d H:i', $request->dropoff_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
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

            BookingAddress::where( 'booking_id', $updateBooking->id )->delete();

            foreach ( $request->pickup_addresses as $pickupAddress ) {

                BookingAddress::create( [
                    'booking_id' => $updateBooking->id,
                    'address_1' => $pickupAddress['pickup_address_address_1'],
                    'address_2' => $pickupAddress['pickup_address_address_2'],
                    'city' => $pickupAddress['pickup_address_city'],
                    'state' => $pickupAddress['pickup_address_state'],
                    'postcode' => $pickupAddress['pickup_address_postcode'],
                    'type' => 1,
                ] );
            }

            foreach ( $request->dropoff_addresses as $dropoffAddress ) {
                
                BookingAddress::create( [
                    'booking_id' => $updateBooking->id,
                    'address_1' => $dropoffAddress['dropoff_address_address_1'],
                    'address_2' => $dropoffAddress['dropoff_address_address_2'],
                    'city' => $dropoffAddress['dropoff_address_city'],
                    'state' => $dropoffAddress['dropoff_address_state'],
                    'postcode' => $dropoffAddress['dropoff_address_postcode'],
                    'destination' => $dropoffAddress['dropoff_address_destination'],
                    'type' => 2,
                ] );
            }

            if ( $request->delivery_order_image ) {
                $file = FileManager::find( $request->delivery_order_image );
                if ( $file ) {

                    Storage::disk( 'public' )->delete( $updateBooking->delivery_order_image );

                    $fileName = explode( '/', $file->file );
                    $target = 'bookings/' . $updateBooking->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );
    
                    $updateBooking->delivery_order_image = $target;
                    $updateBooking->save();
    
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
                <td>' . ( $booking->pickup_address ? $booking->pickup_address->address_1 : '-' ) . '</td>
                <td>' . ( $booking->dropoff_address ? $booking->dropoff_address->address_1 : '-' ) . '</td>
                <td>' . ( $booking->dropoff_address ? $booking->dropoff_address->address_1 : '-' ). '</td>
                <td>' . Helper::numberFormatNoComma( $booking->customer_quantity, 4 ) . '</td>
                <td>' . ( $booking->customer_unit_of_measurement ? $uom[$booking->customer_unit_of_measurement] : '-' ) . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->customer_rate, 2 ) . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->driver_rate, 2 ) . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->customer_total_amount, 2 ) . '</td>
                <td>' . ( $booking->customer_type ? $customerType[$booking->customer_type] : '-' ) . '</td>
                <td>' . ( $booking->company ? $booking->company->name : '-' ) . '</td>
                <td>' . $booking->customer_remarks . '</td>
                <td>' . ( $booking->driver ? $booking->driver->name : '-' ) . '</td>
                <td>' . Helper::numberFormatNoComma( $booking->driver_quantity, 4 ) . '</td>
                <td>' . ( $booking->driver_unit_of_measurement ? $uom[$booking->driver_unit_of_measurement] : '-' ) . '</td>
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