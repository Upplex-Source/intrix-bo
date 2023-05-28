<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Expense,
    FuelExpense,
};

use Helper;

use Carbon\Carbon;

class FuelExpenseService
{
    public static function allFuelExpenses( $request ) {

        $fuelExpense = FuelExpense::with( [
            'company',
            'vehicle',
        ] )->select( 'fuel_expenses.*' );

        $fuelExpense->leftJoin( 'companies', 'companies.id', '=', 'fuel_expenses.company_id' );
        $fuelExpense->leftJoin( 'vehicles', 'vehicles.id', '=', 'fuel_expenses.vehicle_id' );

        $filterObject = self::filter( $request, $fuelExpense );
        $fuelExpense = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $fuelExpense->orderBy( 'fuel_expenses.transaction_time', $dir );
                    break;
            }
        }

        $fuelExpenseCount = $fuelExpense->count();

        $limit = $request->length;
        $offset = $request->start;

        $fuelExpenses = $fuelExpense->skip( $offset )->take( $limit )->get();

        if ( $fuelExpenses ) {
            $fuelExpenses->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = FuelExpense::count();

        $data = [
            'fuel_expenses' => $fuelExpenses,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $fuelExpenseCount : $totalRecord,
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

                $model->whereBetween( 'fuel_expenses.transaction_time', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->transaction_time );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'fuel_expenses.transaction_time', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->station ) ) {
            $model->where( 'fuel_expenses.station', $request->station );
            $filter = true;
        }

        if ( !empty( $request->company ) ) {
            $model->where( 'fuel_expenses.company_id', $request->company );
            $filter = true;
        }

        if ( !empty( $request->license_plate ) ) {
            $model->where( 'vehicles.license_plate', $request->license_plate );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneFuelExpense( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $fuelExpense = FuelExpense::with( [
            'vehicle',
        ] )->find( $request->id );

        return response()->json( $fuelExpense );
    }

    public static function createFuelExpense( $request ) {

        $validator = Validator::make( $request->all(), [
            'company' => [ 'required', 'exists:companies,id' ],
            'vehicle' => [ 'required', 'exists:vehicles,id' ],
            'location' => [ 'required' ],
            'amount' => [ 'required' ],
            'station' => [ 'required' ],
            'transaction_time' => [ 'required' ],
        ] );

        $attributeName = [
            'company' => __( 'expenses.company' ),
            'vehicle' => __( 'expenses.vehicle' ),
            'location' => __( 'expenses.location' ),
            'amount' => __( 'expenses.amount' ),
            'station' => __( 'expenses.station' ),
            'transaction_time' => __( 'datatables.transaction_time' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $transactionTime = Carbon::createFromFormat( 'Y-m-d H:i:s', $request->transaction_time, 'Asia/Kuala_Lumpur' )
                ->setTimezone( 'UTC' );

            $createFuelExpense = FuelExpense::create( [
                'company_id' => $request->company,
                'vehicle_id' => $request->vehicle,
                'location' => $request->location,
                'day' => $transactionTime->format( 'd' ),
                'month' => $transactionTime->format( 'm' ),
                'year' => $transactionTime->format( 'Y' ),
                'amount' => $request->amount,
                'station' => $request->station,
                'transaction_time' => $transactionTime->format( 'Y-m-d H:i:s' ),
            ] );

            Expense::create( [
                'fuel_expense_id' => $createFuelExpense->id,
                'amount' => $request->amount,
                'type' => 1,
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.fuel_expenses' ) ) ] ),
        ] );
    }

    public static function updateFuelExpense( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'company' => [ 'required', 'exists:companies,id' ],
            'vehicle' => [ 'required', 'exists:vehicles,id' ],
            'location' => [ 'required' ],
            'amount' => [ 'required' ],
            'station' => [ 'required' ],
            'transaction_time' => [ 'required' ],
        ] );

        $attributeName = [
            'company' => __( 'expenses.company' ),
            'vehicle' => __( 'expenses.vehicle' ),
            'location' => __( 'expenses.location' ),
            'amount' => __( 'expenses.amount' ),
            'station' => __( 'expenses.station' ),
            'transaction_time' => __( 'datatables.transaction_time' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $transactionTime = Carbon::createFromFormat( 'Y-m-d H:i:s', $request->transaction_time, 'Asia/Kuala_Lumpur' )
                ->setTimezone( 'UTC' );

            $updateFuelExpense = FuelExpense::find( $request->id );
            $updateFuelExpense->company_id = $request->company;
            $updateFuelExpense->vehicle_id = $request->vehicle;
            $updateFuelExpense->location = $request->location;
            $updateFuelExpense->day = $transactionTime->format( 'd' );
            $updateFuelExpense->month = $transactionTime->format( 'm' );
            $updateFuelExpense->year = $transactionTime->format( 'Y' );
            $updateFuelExpense->amount = $request->amount;
            $updateFuelExpense->station = $request->station;
            $updateFuelExpense->transaction_time = $transactionTime->format( 'Y-m-d H:i:s' );
            $updateFuelExpense->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.fuel_expenses' ) ) ] ),
        ] );
    }
}