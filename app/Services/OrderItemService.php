<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    Order,
    OrderItem,
};

use Helper;

use Carbon\Carbon;

class OrderItemService
{
    public static function allOrderItems( $request, $export = false ) {

        $orderItem = OrderItem::with( [
            'order',
        ] )->select( 'order_items.*' );
            
        $filterObject = self::filter( $request, $orderItem );
        $orderItem = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $orderItem->orderBy( 'order_items.created_at', $dir );
                    break;
                case 2:
                    $orderItem->orderBy( 'order_items.order_id', $dir );
                    break;
                case 3:
                    $orderItem->orderBy( 'order_items.grade', $dir );
                    break;
                case 4:
                    $orderItem->orderBy( 'orders.status', $dir );
                    break;
            }
        }

        if ( $export == false ) {

            $orderItemCount = $orderItem->count();

            $limit = $request->length;
            $offset = $request->start;

            $orderItems = $orderItem->skip( $offset )->take( $limit )->get();

            if ( $orderItems ) {
                $orderItems->append( [
                    'encrypted_id',
                ] );
            }

            $totalRecord = OrderItem::count();

            $data = [
                'orders' => $orderItems,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $orderItemCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

        } else {

            return $orderItem->get();
        }        
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if (!empty( $request->order_date)) {
            if (str_contains( $request->order_date, 'to' )) {
                $dates = explode( ' to ', $request->order_date);
        
                $startDate = explode( '-', $dates[0]);
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
        
                $endDate = explode( '-', $dates[1]);
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );
        
                $model = OrderItem::whereHas( 'order', function ( $query ) use ( $start, $end ) {
                    $query->whereBetween( 'order_date', [
                        date( 'Y-m-d H:i:s', $start->timestamp ),
                        date( 'Y-m-d H:i:s', $end->timestamp )
                    ]);
                })->get();
            } else {
                $dates = explode( '-', $request->order_date);
        
                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );
        
                $model = OrderItem::whereHas( 'order', function ( $query ) use ( $start, $end ) {
                    $query->whereBetween( 'order_date', [
                        date( 'Y-m-d H:i:s', $start->timestamp ),
                        date( 'Y-m-d H:i:s', $end->timestamp )
                    ]);
                })->get();
            }
            $filter = true;
        }
        
        if ( !empty( $request->reference ) ) {
            $model = OrderItem::whereHas( 'order', function ( $query ) use ( $request) {
                $query->where( 'reference' , 'LIKE' , '%' . $request->reference . '%' );
            })->get();
        }

        if ( !empty( $request->grade ) ) {
            $model->where( 'grade', $request->grade );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneOrderItem( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $orderItem = OrderItem::with( [
            'order',
        ] )->find( $request->id );

        return response()->json( $orderItem );
    }
    
}