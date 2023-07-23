<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Tyre,
};

use Helper;

use Carbon\Carbon;

class TyreService
{
    public static function allTyres( $request ) {

        $tyre = Tyre::select( 'tyres.*' );

        $filterObject = self::filter( $request, $tyre );
        $tyre = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $tyre->orderBy( 'tyres.created_at', $dir );
                    break;
            }
        }

        $tyreCount = $tyre->count();

        $limit = $request->length;
        $offset = $request->start;

        $tyres = $tyre->skip( $offset )->take( $limit )->get();

        if ( $tyres ) {
            $tyres->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Tyre::count();

        $data = [
            'tyres' => $tyres,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $tyreCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
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

                $model->whereBetween( 'tyres.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'tyres.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( function( $query ) {
                $query->where( 'tyres.name', 'LIKE', '%' . request( 'custom_search' ) . '%' );
                $query->orWhere( 'tyres.code', 'LIKE', '%' . request( 'custom_search' ) . '%' );
            } );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }
}