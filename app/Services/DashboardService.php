<?php

namespace App\Services;

use App\Models\{
    Booking,
    Employee,
    Vehicle,
    Vendor,
};

use Helper;

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

        return response()->json( [
            'total_drivers' => Helper::numberFormat( $totalDrivers, 0 ),
            'total_vehicles' => Helper::numberFormat( $totalVehicles, 0 ),
            'total_vendors' => Helper::numberFormat( $totalVendors, 0 ),
            'total_bookings' => Helper::numberFormat( $totalBookings, 0 ),
            'total_incomes' => Helper::numberFormat( $totalIncomes, 2 ),
            'total_expenses' => Helper::numberFormat( $totalExpenses, 2 ),
        ] );
    }
}