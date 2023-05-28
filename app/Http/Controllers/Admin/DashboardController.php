<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    DashboardService,
};

class DashboardController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.dashboard' );
        $this->data['content'] = 'admin.dashboard.index';
        $this->data['data']['upcoming_bookings'] = DashboardService::upcomingBooking( $request );

        return view( 'admin.main' )->with( $this->data );
    }

    public function getDashboardData( Request $request ) {

        return DashboardService::getDashboardData( $request );
    }

    public function getExpensesStatistics( Request $request ) {

        return DashboardService::getExpensesStatistics( $request );

        return [
            'fuelData' => [
                1,2,3,
            ],
            'tollData' => [
                1,2,3,
            ],
            'xAxis' => [
                'A','B','C',
            ],
        ];
    }
}
