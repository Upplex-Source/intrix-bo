<?php

namespace App\Services;

use App\Models\{
    Order,
    Owner,
    Farm,
    Buyer,
    Administrator,
};

use Helper;

use Carbon\Carbon;

class DashboardService
{
    public static function getDashboardData( $request ) {

        $totalOwners = Helper::numberFormat( Administrator::where( 'role', 3 )->count(), 0 );
        $totalFarms = Helper::numberFormat( Farm::where( 'status', 10 )->count(), 0 );
        $totalBuyers = Helper::numberFormat( Buyer::where( 'status', 10 )->count(), 0 );
        $totalOrders = Helper::numberFormat( Order::where( 'status', 10 )->count(), 0 );

        return response()->json( [
            'total_owners' => $totalOwners,
            'total_farms'  => $totalFarms,
            'total_buyers' => $totalBuyers,
            'total_orders' => $totalOrders,
        ] );
    }

    public static function getExpensesStatistics( $request ) {

        $xAxis = []; // Weeks
        $orderData = [];
        for ( $x = 6; $x >= 0; $x-- ) {

            $day = strtotime( date( 'M d' ) . ' -' . $x . ' day' );
            $thisDay = new \DateTime( date( 'Y-m-d', $day ) );
            $thisDay = $thisDay->format( 'Y-m-d' );

            $thisDayOrder = Order::where( 'status', 10 )->whereBetween( 'order_date', [ $thisDay . ' 00:00:00', $thisDay . ' 23:59:59' ] )->count();

            array_push( $xAxis, date( 'M d', $day ) ); 
            array_push( $orderData, $thisDayOrder );
        }

        return response()->json( [
            'orderData' => $orderData,
            'xAxis' => $xAxis,
        ] );
    }

    public static function upcomingBooking( $request ) {

        $start = Carbon::now();
        $end = Carbon::now()->addDays( 7 );

        $bookings = Booking::where( function( $query ) use ( $start, $end ) {
            $query->where( 'pickup_date', '>=', $start );
            $query->where( 'pickup_date', '<=', $end );
        } )->orWhere( function( $query ) use ( $start, $end ) {
            $query->where( 'dropoff_date', '>=', $start );
            $query->where( 'dropoff_date', '<=', $end );
        } )->orderBy( 'pickup_date', 'ASC' )->get();

        if ( $bookings ) {
            $bookings->append( [
                'display_drop_off_address',
                'encrypted_id',
            ] );
        }

        return $bookings;
    }
}