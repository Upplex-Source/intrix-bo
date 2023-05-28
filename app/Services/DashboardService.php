<?php

namespace App\Services;

use App\Models\{
    Booking,
    Employee,
    Expense,
    Vehicle,
    Vendor,
};

use Helper;

use Carbon\Carbon;

class DashboardService
{
    public static function getDashboardData( $request ) {

        $totalDrivers = 0;
        $totalVehicles = 0;
        $totalVendors = 0;
        $totalBookings = 0;
        $totalIncomes = 0;
        $totalExpenses = 0;

        $totalDrivers = Employee::where( 'status', 10 )
            ->where( 'designation', 1 )
            ->count();

        $totalVehicles = Vehicle::where( 'status', 10 )
            ->count();

        $totalVendors = Vendor::where( 'status', 10 )
            ->count();

        $totalBookings = Booking::count();

        $totalExpenses = Expense::sum( 'amount' );

        return response()->json( [
            'total_drivers' => Helper::numberFormat( $totalDrivers, 0 ),
            'total_vehicles' => Helper::numberFormat( $totalVehicles, 0 ),
            'total_vendors' => Helper::numberFormat( $totalVendors, 0 ),
            'total_bookings' => Helper::numberFormat( $totalBookings, 0 ),
            'total_incomes' => Helper::numberFormat( $totalIncomes, 2 ),
            'total_expenses' => Helper::numberFormat( $totalExpenses, 2 ),
        ] );
    }

    public static function getExpensesStatistics( $request ) {

        $xAxis = []; // Weeks
        $fuelData = [];
        $tollData = [];
        for ( $x = 6; $x >= 0; $x-- ) {

            $day = strtotime( date( 'M d' ) . ' -' . $x . ' day' );
            $thisDay = new \DateTime( date( 'Y-m-d', $day ) );
            $thisDay = $thisDay->format( 'Y-m-d' );

            $thisDayFuel = Expense::where( 'type', 1 )->whereBetween( 'transaction_time', [ $thisDay . ' 00:00:00', $thisDay . ' 23:59:59' ] )->sum( 'amount' );
            $thisDayToll = Expense::where( 'type', 2 )->whereBetween( 'transaction_time', [ $thisDay . ' 00:00:00', $thisDay . ' 23:59:59' ] )->sum( 'amount' );

            array_push( $xAxis, date( 'M d', $day ) ); 
            array_push( $fuelData, $thisDayFuel );
            array_push( $tollData, $thisDayToll );
        }

        return response()->json( [
            'fuelData' => $fuelData,
            'tollData' => $tollData,
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