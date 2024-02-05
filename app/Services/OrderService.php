<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    FileManager,
    Option,
    Order,
    OrderItem,
};

use Helper;

use Carbon\Carbon;

class OrderService
{
    public static function calendarAllOrders( $request ) {

        $orders = Order::where( 'invoice_date', '>=', $request->start )
            ->where( 'invoice_date', '<=', $request->end )
            ->orderBy( 'invoice_date', 'ASC' )
            ->get();

        $currentOrders = [];
        foreach ( $orders as $order ) {

            $plateNumber = $order->vehicle ? $order->vehicle->license_plate : '-';
            $notes = $order->notes ? $order->notes : '-';

            array_push( $currentOrders, [
                'id' => Helper::encode( $order->id ),
                'allDay' => true,
                'start' => $order->invoice_date . ' 00:00:00',
                'end' => $order->invoice_date . ' 23:59:59',
                'title' => [
                    'html' => 'Reference:' . $order->reference . '<br>Plate Number:' . $plateNumber . '<br>Notes:' . $notes,
                ],
                'color' => '#9769ff',
            ] );
        }

        return response()->json( $currentOrders );
    }

    public static function allOrders( $request, $export = false ) {

        $order = Order::with( [
            'farm',
            'farm.owner',
            'buyer',
            'orderItems',
        ] )->select( 'orders.*' );
            
        $filterObject = self::filter( $request, $order );
        $order = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $order->orderBy( 'orders.order_date', $dir );
                    break;
                case 2:
                    $order->orderBy( 'orders.reference', $dir );
                    break;
                case 3:
                    $order->orderBy( 'orders.owner_id', $dir );
                    break;
                case 4:
                    $order->orderBy( 'orders.farm_id', $dir );
                    break;
                case 5:
                    $order->orderBy( 'orders.buyer_id', $dir );
                    break;
                case 6:
                    $order->orderBy( 'orders.status', $dir );
                    break;
            }
        }

        if ( $export == false ) {

            $orderCount = $order->count();

            $limit = $request->length;
            $offset = $request->start;

            $orders = $order->skip( $offset )->take( $limit )->get();

            if ( $orders ) {
                $orders->append( [
                    'encrypted_id',
                ] );
            }

            $totalRecord = Order::count();

            $data = [
                'orders' => $orders,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $orderCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

        } else {

            return $order->get();
        }        
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->order_date ) ) {
            if ( str_contains( $request->order_date, 'to' ) ) {
                $dates = explode( ' to ', $request->order_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'orders.order_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->order_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'orders.order_date', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'orders.reference', 'LIKE', '%' . $request->customer . '%' );
            $filter = true;
        }

        if ( !empty( $request->owner ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'owner', function ( $q ) use ( $request ) {
                    $q->where( 'fullname', 'LIKE', '%' . $request->owner . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->farm ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'farm', function ( $q ) use ( $request ) {
                    $q->where( 'title', 'LIKE', '%' . $request->farm . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->buyer ) ) {
            $model->where( function ( $query ) use ( $request ) {
                $query->whereHas( 'buyer', function ( $q ) use ( $request ) {
                    $q->where( 'name', 'LIKE', '%' . $request->buyer . '%' );
                });
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'orders.status', $request->status );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneOrder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $order = Order::with( [
            'farm',
            'farm.owner',
            'buyer',
            'orderItems',
        ] )->find( $request->id );

        return response()->json( $order );
    }

    public static function getLatestOrderIncrement() {

        $latestOrder = Order::where( 'reference', 'LIKE', '%' . date( 'Y/m' ) . '%' )
            ->orderBy( 'reference', 'DESC' )
            ->first();

        if ( $latestOrder ) {
            $parts = explode( ' ', $latestOrder->reference );
            return $parts[1];
        }

        return 0;
    }

    public static function createOrder( $request ) {

        $request->merge( [
            'order_items' => json_decode( $request->order_items, true ),
        ] );

        $validator = Validator::make( $request->all(), [
            'reference' => [ 'required', 'unique:orders' ],
            'farm' => [ 'required', 'exists:farms,id'  ],
            'buyer' => [ 'required', 'exists:buyers,id'  ],
            'order_items' => [ 'nullable' ],
            'grade' => [ 'nullable' ],
            'weight' => [ 'nullable' ],
            'rate' => [ 'nullable' ],
            'total' => [ 'nullable' ],
            'subtotal' => [ 'nullable' ],

            'order_items' => ['nullable', 'array'],
            'order_items.*.weight' => ['nullable', 'numeric', 'min:0'],
            'order_items.*.rate' => ['nullable', 'numeric', 'min:0'],

        ] );

        $attributeName = [
            'reference' => __( 'order.reference' ),
            'farm' => __( 'order.farm' ),
            'buyer' => __( 'order.buyer' ),
            'grade' => __( 'order.grade' ),
            'weight' => __( 'order.weight' ),
            'rate' => __( 'order.rate' ),
            'total' => __( 'order.total' ),
            'subtotal' => __( 'order.subtotal' ),

            'order_items.*.weight' => __( 'order.order_items' ),
            'order_items.*.rate' => __( 'order.order_items' ),
            'order_items' => __( 'order.order_items' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $createOrder = Order::create( [
                'reference' => $request->reference,
                'farm_id' => $request->farm,
                'buyer_id' => $request->buyer,
                'order_date' => $request->order_date ? Carbon::createFromFormat( 'Y-m-d', $request->order_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null,
                'subtotal' => $request->subtotal,
                'total' => $request->total,
            ] );

            foreach ( $request->order_items as $orderItems ) {

                OrderItem::create( [
                    'order_id' => $createOrder->id,
                    'grade' => $orderItems['grade'],
                    'weight' => $orderItems['weight'] != null ? $orderItems['weight'] : 0,
                    'rate' => $orderItems['rate'] != null ? $orderItems['rate'] : 0,
                ] );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ),
        ] );
    }

    public static function updateOrder( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
            'order_items' => json_decode( $request->order_items, true ),
        ] );

        $validator = Validator::make( $request->all(), [
            'reference' => [ 'required', 'unique:orders,reference,' . $request->id ],
            'farm' => [ 'required', 'exists:farms,id'  ],
            'buyer' => [ 'required', 'exists:buyers,id'  ],
            'order_items' => [ 'nullable' ],
            'grade' => [ 'nullable' ],
            'weight' => [ 'nullable' ],
            'rate' => [ 'nullable' ],
            'total' => [ 'nullable' ],
            'subtotal' => [ 'nullable' ],
        ] );

        $attributeName = [
            'reference' => __( 'order.reference' ),
            'farm' => __( 'order.farm' ),
            'buyer' => __( 'order.buyer' ),
            'grade' => __( 'order.grade' ),
            'weight' => __( 'order.weight' ),
            'rate' => __( 'order.rate' ),
            'total' => __( 'order.total' ),
            'subtotal' => __( 'order.subtotal' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateOrder = Order::find( $request->id );
            $updateOrder->reference = $request->reference;
            $updateOrder->buyer_id = $request->buyer;
            $updateOrder->order_date = $request->order_date ? Carbon::createFromFormat( 'Y-m-d', $request->order_date, 'Asia/Kuala_Lumpur' )->setTimezone( 'UTC' )->format( 'Y-m-d H:i:s' ) : null;
            $updateOrder->subtotal = $request->subtotal;
            $updateOrder->total = $request->total;
            $updateOrder->save();

            OrderItem::where( 'order_id', $updateOrder->id )->delete();

            foreach ( $request->order_items as $orderItems ) {

                OrderItem::create( [
                    'order_id' => $updateOrder->id,
                    'grade' => $orderItems['grade'],
                    'weight' => $orderItems['weight'] != null ? $orderItems['weight'] : 0,
                    'rate' => $orderItems['rate'] != null ? $orderItems['rate'] : 0,
                ] );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.orders' ) ) ] ),
        ] );
    }

    public static function exportOrders($request)
    {
        $orders = self::allOrders($request, true);

        $grades = [
            'A',
            'B',
            'C',
            'D',
        ];

        $grandSubtotalTotal = $grandTotalTotal = 0;
        $grandRates['A']['rates'] = 0;
        $grandRates['A']['weight'] = 0;
        $grandRates['B']['rates'] = 0;
        $grandRates['B']['weight'] = 0;
        $grandRates['C']['rates'] = 0;
        $grandRates['C']['weight'] = 0;
        $grandRates['D']['rates'] = 0;
        $grandRates['D']['weight'] = 0;
    
        $html = '<table>';
    
        $html .= '
            <thead>
                <tr>
                    <th colspan="6"></th>
                    <th colspan="' . (count($grades) * 3) . '" class="text-center"><strong>' . __('order.order_items') . '</strong></th>
                    <th colspan="2"></th>
                <tr>
                    <th><strong>' . __('datatables.no') . '</strong></th>
                    <th><strong>' . __('order.reference') . '</strong></th>
                    <th><strong>' . __('order.order_date') . '</strong></th>
                    <th><strong>' . __('order.owner') . '</strong></th>
                    <th><strong>' . __('order.farm') . '</strong></th>
                    <th><strong>' . __('order.buyer') . '</strong></th>';
    
        foreach ($grades as $grade) {
            $html .= '<th><strong>' . __('order.grade') . '</strong></th>';
            $html .= '<th><strong>' . __('order.rate') . '</strong></th>';
            $html .= '<th><strong>' . __('order.weight') . '</strong></th>';
        }
    
        $html .= '<th><strong>' . __('order.subtotal') . '</strong></th>';
        $html .= '<th><strong>' . __('order.total') . '</strong></th>';
        $html .= '</tr>
            </thead>';
        $html .= '<tbody>';
    
        foreach ($orders as $key => $order) {
    
            $html .= '
                <tr>
                    <td>' . (intval($key) + 1) . '</td>
                    <td>' . $order['reference'] . '</td>
                    <td>' . $order['order_date'] . '</td>
                    <td>' . ($order->farm->owner->name ?? '-') . '</td>
                    <td>' . ($order->farm->title ?? '-') . '</td>
                    <td>' . ($order->buyer->name ?? '-') . '</td>';
    
            foreach ($grades as $grade) {

                if (isset($order->orderItems)) {
                    $foundGrade = false;
        
                    foreach ($order->orderItems as $orderItem) {
                        if (isset($orderItem['grade']) && $orderItem['grade'] == $grade) {
                            $foundGrade = true;
                            $html .= '<td>' . $orderItem['grade'] . '</td>';
                            $html .= '<td>' . $orderItem['rate'] . '</td>';
                            $html .= '<td>' . $orderItem['weight'] . '</td>';
                            $grandRates[$grade]['rates'] += $orderItem['rate'];
                            $grandRates[$grade]['weight'] += $orderItem['weight'];
                        }
                    }
        
                    if (!$foundGrade) {
                        $html .= '<td>-</td>';
                        $html .= '<td>-</td>';
                        $html .= '<td>-</td>';
                    }
                }
        
            }
    
            $html .= '<td>' . $order['subtotal'] . '</td>';
            $html .= '<td>' . $order['total'] . '</td>';
    
            $grandTotalTotal += $order['total'];
            $grandSubtotalTotal += $order['subtotal'];
    
            $html .= '</tr>';
        }
    
        $html .= '
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">' . __('datatables.grand_total') . '</td>';
    
        foreach ($grades as $grade) {
            $html .= '<td colspan=""></td>';
            $html .= '<td>' . $grandRates[$grade]['rates'] . '</td>';
            $html .= '<td>' . $grandRates[$grade]['weight'] . '</td>';
        }
    
        $html .= '<td>' . $grandSubtotalTotal . '</td>';
        $html .= '<td>' . $grandTotalTotal . '</td>';
        $html .= '</tr>
            </tfoot>';
    
        $html .= '</table>';
    
        Helper::exportReport($html, 'Order');
    }

    public static function salesReport( $request ) {

        $date = $request->date ? $request->date : date( 'Y m' );

        $start = Carbon::createFromFormat( 'Y m', $date, 'Asia/Kuala_Lumpur' )->startOfMonth()->timezone( 'UTC' );

        $end = Carbon::createFromFormat( 'Y m', $date, 'Asia/Kuala_Lumpur' )->endOfMonth()->timezone( 'UTC' );

        $currenctPeriodSales = [];

        $salesRecords = Order::with( [
            'farm.owner',
            'buyer',
            'orderItems',
        ] )->where( 'created_at', '>=', $start->format( 'Y-m-d H:i:s' ) )
            ->where( 'created_at', '<=', $end->format( 'Y-m-d H:i:s' ) )
            ->get()
            ->toArray();

        return [
            'orders' => $salesRecords,
        ];
    }
    
}