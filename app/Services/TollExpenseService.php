<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Expense,
    TollExpense,
};

use Helper;

use Carbon\Carbon;

class TollExpenseService
{
    public static function allTollExpenses( $request ) {

        $tollExpense = TollExpense::with( [
            'vehicle',
        ] )->select( 'toll_expenses.*' );

        $tollExpense->leftJoin( 'vehicles', 'vehicles.id', '=', 'toll_expenses.vehicle_id' );

        $filterObject = self::filter( $request, $tollExpense );
        $tollExpense = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $tollExpense->orderBy( 'toll_expenses.transaction_time', $dir );
                    break;
            }
        }

        $tollExpenseCount = $tollExpense->count();

        $limit = $request->length;
        $offset = $request->start;

        $tollExpenses = $tollExpense->skip( $offset )->take( $limit )->get();

        if ( $tollExpenses ) {
            $tollExpenses->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = TollExpense::count();

        $data = [
            'toll_expenses' => $tollExpenses,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $tollExpenseCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->transaction_time ) ) {
            if ( str_contains( $request->transaction_time, 'to' ) ) {
                $dates = explode( ' to ', $request->transaction_time );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'toll_expenses.transaction_time', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->transaction_time );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'toll_expenses.transaction_time', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->entry_location ) ) {
            $model->where( 'toll_expenses.entry_location', $request->entry_location );
            $filter = true;
        }

        if ( !empty( $request->entry_sp ) ) {
            $model->where( 'toll_expenses.entry_sp', $request->entry_sp );
            $filter = true;
        }
        
        if ( !empty( $request->exit_location ) ) {
            $model->where( 'toll_expenses.exit_location', $request->exit_location );
            $filter = true;
        }

        if ( !empty( $request->exit_sp ) ) {
            $model->where( 'toll_expenses.exit_sp', $request->exit_sp );
            $filter = true;
        }

        if ( !empty( $request->reload_location ) ) {
            $model->where( 'toll_expenses.reload_location', $request->reload_location );
            $filter = true;
        }

        if ( !empty( $request->type ) ) {
            $model->where( 'toll_expenses.type', $request->type );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneTollExpense( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $tollExpense = TollExpense::with( [
            'vehicle',
        ] )->find( $request->id );

        return response()->json( $tollExpense );
    }

    public static function createTollExpense( $request ) {

        $validator = Validator::make( $request->all(), [
            'transaction_number' => [ 'required' ],
            'transaction_time' => [ 'required' ],
            'posted_date' => [ 'required' ],
            'transaction_type' => [ 'required' ],
            'reload_location' => [ 'required_if:transaction_type,2' ],
            'entry_location' => [ 'required' ],
            'entry_sp' => [ 'required' ],
            'exit_location' => [ 'required' ],
            'exit_sp' => [ 'required' ],
            'amount' => [ 'required' ],
            'balance' => [ 'required' ],
            'class' => [ 'required', 'in:0,1,2,3' ],
            'tag_number' => [ 'required' ],
            'vehicle' => [ 'required', 'exists:vehicles,id' ],
        ] );

        $attributeName = [
            'transaction_number' => __( 'expenses.transaction_number' ),
            'transaction_time' => __( 'expenses.transaction_time' ),
            'posted_date' => __( 'expenses.posted_date' ),
            'transaction_type' => __( 'expenses.transaction_type' ),
            'reload_location' => __( 'expenses.reload_location' ),
            'entry_location' => __( 'expenses.entry_location' ),
            'entry_sp' => __( 'expenses.entry_sp' ),
            'exit_location' => __( 'expenses.exit_location' ),
            'exit_sp' => __( 'expenses.exit_sp' ),
            'amount' => __( 'expenses.amount' ),
            'balance' => __( 'expenses.balance' ),
            'class' => __( 'expenses.class' ),
            'tag_number' => __( 'expenses.tag_number' ),
            'vehicle' => __( 'expenses.vehicle' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $transactionTime = Carbon::createFromFormat( 'Y-m-d H:i:s', $request->transaction_time, 'Asia/Kuala_Lumpur' )
                ->setTimezone( 'UTC' );

            $createTollExpense = TollExpense::create( [
                'vehicle_id' => $request->vehicle,
                'transaction_number' => $request->transaction_number,
                'entry_location' => $request->entry_location,
                'entry_sp' => $request->entry_sp,
                'exit_location' => $request->exit_location,
                'exit_sp' => $request->exit_sp,
                'reload_location' => $request->reload_location,
                'tag_number' => $request->tag_number,
                'remarks' => $request->remarks,
                'day' => $transactionTime->format( 'd' ),
                'month' => $transactionTime->format( 'm' ),
                'year' => $transactionTime->format( 'Y' ),
                'posted_date' => $request->posted_date,
                'amount' => $request->amount,
                'balance' => $request->balance,
                'class' => $request->class,
                'type' => $request->transaction_type,
                'transaction_time' => $transactionTime->format( 'Y-m-d H:i:s' ),
            ] );

            Expense::create( [
                'toll_expense_id' => $createTollExpense->id,
                'amount' => $request->amount,
                'type' => 2,
                'transaction_time' => $transactionTime->format( 'Y-m-d H:i:s' ),
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.toll_expenses' ) ) ] ),
        ] );
    }

    public static function updateTollExpense( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'transaction_number' => [ 'required' ],
            'transaction_time' => [ 'required' ],
            'posted_date' => [ 'required' ],
            'transaction_type' => [ 'required' ],
            'reload_location' => [ 'required_if:transaction_type,2' ],
            'entry_location' => [ 'required' ],
            'entry_sp' => [ 'required' ],
            'exit_location' => [ 'required' ],
            'exit_sp' => [ 'required' ],
            'amount' => [ 'required' ],
            'balance' => [ 'required' ],
            'class' => [ 'required', 'in:0,1,2,3' ],
            'tag_number' => [ 'required' ],
            'vehicle' => [ 'required', 'exists:vehicles,id' ],
        ] );

        $attributeName = [
            'transaction_number' => __( 'expenses.transaction_number' ),
            'transaction_time' => __( 'expenses.transaction_time' ),
            'posted_date' => __( 'expenses.posted_date' ),
            'transaction_type' => __( 'expenses.transaction_type' ),
            'reload_location' => __( 'expenses.reload_location' ),
            'entry_location' => __( 'expenses.entry_location' ),
            'entry_sp' => __( 'expenses.entry_sp' ),
            'exit_location' => __( 'expenses.exit_location' ),
            'exit_sp' => __( 'expenses.exit_sp' ),
            'amount' => __( 'expenses.amount' ),
            'balance' => __( 'expenses.balance' ),
            'class' => __( 'expenses.class' ),
            'tag_number' => __( 'expenses.tag_number' ),
            'vehicle' => __( 'expenses.vehicle' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $transactionTime = Carbon::createFromFormat( 'Y-m-d H:i:s', $request->transaction_time, 'Asia/Kuala_Lumpur' )
                ->setTimezone( 'UTC' );

            $updateTollExpense = TollExpense::find( $request->id );
            $updateTollExpense->vehicle_id = $request->vehicle;
            $updateTollExpense->transaction_number = $request->transaction_number;
            $updateTollExpense->entry_location = $request->entry_location;
            $updateTollExpense->entry_sp = $request->entry_sp;
            $updateTollExpense->exit_location = $request->exit_location;
            $updateTollExpense->exit_sp = $request->exit_sp;
            if ( $request->transaction_type == 2 ) {
                $updateTollExpense->reload_location = $request->reload_location;
            } else {
                $updateTollExpense->reload_location = null;
            }
            $updateTollExpense->tag_number = $request->tag_number;
            $updateTollExpense->remarks = $request->remarks;
            $updateTollExpense->day = $transactionTime->format( 'd' );
            $updateTollExpense->month = $transactionTime->format( 'm' );
            $updateTollExpense->year = $transactionTime->format( 'Y' );
            $updateTollExpense->posted_date = $request->posted_date;
            $updateTollExpense->amount = $request->amount;
            $updateTollExpense->balance = $request->balance;
            $updateTollExpense->class = $request->class;
            $updateTollExpense->type = $request->transaction_type;
            $updateTollExpense->transaction_time = $transactionTime->format( 'Y-m-d H:i:s' );
            $updateTollExpense->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.toll_expenses' ) ) ] ),
        ] );
    }
}