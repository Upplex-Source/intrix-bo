<?php

namespace App\Services;

use App\Models\{
    Administrator,
};

class AdministratorService
{
    public static function allAdmins( $request ) {

        $administrator = Administrator::select( 'administrators.*' );

        $filterObject = self::filter( $request, $administrator );
        $administrator = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $administrator->orderBy( 'created_at', $dir );
                    break;
                case 2:
                    $administrator->orderBy( 'name', $dir );
                    break;
                case 3:
                    $administrator->orderBy( 'email', $dir );
                    break;
                case 4:
                    $administrator->orderBy( 'role', $dir );
                    break;
            }
        }

        $administratorCount = $administrator->count();

        $limit = $request->length;
        $offset = $request->start;

        $administrators = $administrator->skip( $offset )->take( $limit )->get();

        $totalRecord = Administrator::count();

        $data = [
            'administrators' => $administrators,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $administratorCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->registered_date ) ) {
            if ( str_contains( $request->registered_date, 'to' ) ) {
                $dates = explode( ' to ', $request->registered_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'admins.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->registered_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'admins.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->username ) ) {
            $model->where( 'name', $request->username );
            $filter = true;
        }

        if ( !empty( $request->email ) ) {
            $model->where( 'email', $request->email );
            $filter = true;
        }

        if ( !empty( $request->role ) ) {
            $model->where( 'roles.id', $request->role );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }
}