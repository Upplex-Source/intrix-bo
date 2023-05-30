<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    BookingService,
    CompanyService,
};

class BookingController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.bookings' );
        $this->data['content'] = 'admin.booking.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.bookings' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '1' => __( 'datatables.pending' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.bookings' ) ) ] );
        $this->data['content'] = 'admin.booking.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.booking.index' ),
                'text' => __( 'template.bookings' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.bookings' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['company'] = CompanyService::get();
        $this->data['data']['type'] = [
            '1' => __( 'booking.sewa' ),
            '2' => __( 'booking.logs' ),
            '3' => __( 'booking.others' ),
        ];
        $this->data['data']['uom'] = [
            '1' => __( 'booking.ton' ),
            '2' => __( 'booking.trip' ),
            '3' => __( 'booking.pallets' ),
        ];
        $this->data['data']['booking_increment'] = BookingService::getLatestBookingIncrement();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.bookings' ) ) ] );
        $this->data['content'] = 'admin.booking.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.booking.index' ),
                'text' => __( 'template.bookings' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.bookings' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['company'] = CompanyService::get();
        $this->data['data']['type'] = [
            '1' => __( 'booking.sewa' ),
            '2' => __( 'booking.logs' ),
            '3' => __( 'booking.others' ),
        ];
        $this->data['data']['uom'] = [
            '1' => __( 'booking.ton' ),
            '2' => __( 'booking.trip' ),
            '3' => __( 'booking.pallets' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allBookings( Request $request ) {

        return BookingService::allBookings( $request );
    }

    public function oneBooking( Request $request ) {

        return BookingService::oneBooking( $request );
    }

    public function createBooking( Request $request ) {

        return BookingService::createBooking( $request );
    }

    public function updateBooking( Request $request ) {

        return BookingService::updateBooking( $request );
    }

    public function updateBookingStatus( Request $request ) {

        return BookingService::updateBookingStatus( $request );
    }

    public function export( Request $request ) {

        return BookingService::exportBookings( $request );
    }
}
