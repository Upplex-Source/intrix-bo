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

        if ( !empty( $request->status ) ) {
            $model->where( 'tyres.status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( function( $query ) {
                $query->where( 'tyres.name', 'LIKE', '%' . $request->custom_search . '%' );
                $query->orWhere( 'tyres.code', 'LIKE', '%' . $request->custom_search . '%' );
            } );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneTyre( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $vendor = Tyre::find( $request->id );

        if( $vendor ) {
            $vendor->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $vendor );
    }

    public static function createTyre( $request ) {

        $validator = Validator::make( $request->all(), [
            'code' => [ 'nullable', 'unique:tyres,code' ],
            'name' => [ 'required' ],
        ] );

        $attributeName = [
            'code' => __( 'tyre.code' ),
            'name' => __( 'tyre.name' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            Tyre::create( [
                'supplier_id' => 1,
                'code' => $request->code,
                'name' => $request->name,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.tyres' ) ) ] ),
        ] );
    }

    public static function updateTyre( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'code' => [ 'nullable', 'unique:tyres,code,' . $request->id ],
            'name' => [ 'required' ],
        ] );

        $attributeName = [
            'code' => __( 'tyre.code' ),
            'name' => __( 'tyre.name' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateTyre = Tyre::find( $request->id );
            $updateTyre->code = $request->code;
            $updateTyre->name = $request->name;
            $updateTyre->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.tyres' ) ) ] ),
        ] );
    }

    public static function updateTyreStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateTyre = Tyre::find( $request->id );
        $updateTyre->status = $request->status;
        $updateTyre->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.vendors' ) ) ] ),
        ] );
    }
}