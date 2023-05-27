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
    Employee,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class EmployeeService
{
    public static function allEmployees( $request ) {

        $employee = Employee::select( 'employees.*' );

        $filterObject = self::filter( $request, $employee );
        $employee = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $employee->orderBy( 'created_at', $dir );
                    break;
                case 3:
                    $employee->orderBy( 'name', $dir );
                    break;
                case 4:
                    $employee->orderBy( 'phone_number', $dir );
                    break;
                case 5:
                    $employee->orderBy( 'identification_number', $dir );
                    break;
                case 6:
                    $employee->orderBy( 'designation', $dir );
                    break;
                case 7:
                    $vendor->orderBy( 'status', $dir );
                    break;
            }
        }

        $employeeCount = $employee->count();

        $limit = $request->length;
        $offset = $request->start;

        $employees = $employee->skip( $offset )->take( $limit )->get();

        if ( $employees ) {
            $employees->append( [
                'path',
                'encrypted_id',
            ] );
        }

        $totalRecord = Employee::count();

        $data = [
            'employees' => $employees,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $employeeCount : $totalRecord,
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

                $model->whereBetween( 'employees.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            } else {

                $dates = explode( '-', $request->created_date );

                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'employees.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'name', $request->name );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $model->where( 'phone_number', $request->phone_number );
            $filter = true;
        }

        if ( !empty( $request->identification_number ) ) {
            $model->where( 'identification_number', $request->identification_number );
            $filter = true;
        }

        if ( !empty( $request->designation ) ) {
            $model->where( 'designation', $request->designation );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( function( $query ) {
                $query->where( 'name', 'LIKE', '%' . request( 'custom_search' ) . '%' );
            } );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneEmployee( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $employee = Employee::find( $request->id );

        if( $employee ) {
            $employee->append( [
                'path',
                'encrypted_id',
            ] );
        }

        return response()->json( $employee );
    }

    public static function createEmployee( $request ) {

        $validator = Validator::make( $request->all(), [
            // 'photo' => [ 'required' ],
            'name' => [ 'required' ],
            'email' => [ 'required', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'digits_between:8,15' ],
            'identification_number' => [ 'required' ],
            'designation' => [ 'required', 'in:1,2' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'employee.name' ),
            'email' => __( 'employee.email' ),
            'phone_number' => __( 'employee.phone_number' ),
            'identification_number' => __( 'employee.identification_number' ),
            'designation' => __( 'employee.designation' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createEmployee = Employee::create( [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'identification_number' => $request->identification_number,
                'designation' => $request->designation,
                'remarks' => $request->remarks,
            ] );

            $file = FileManager::find( $request->photo );
            if ( $file ) {
                $fileName = explode( '/', $file->file );
                $target = 'employees/' . $createEmployee->id . '/' . $fileName[1];
                Storage::disk( 'public' )->move( $file->file, $target );

                $createEmployee->photo = $target;
                $createEmployee->save();

                $file->status = 10;
                $file->save();
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.employees' ) ) ] ),
        ] );
    }

    public static function updateEmployee( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            // 'photo' => [ 'required' ],
            'name' => [ 'required' ],
            'email' => [ 'required', 'bail', 'email', 'regex:/(.+)@(.+)\.(.+)/i', new CheckASCIICharacter ],
            'phone_number' => [ 'required', 'digits_between:8,15' ],
            'identification_number' => [ 'required' ],
            'designation' => [ 'required', 'in:1,2' ],
        ] );

        $attributeName = [
            'photo' => __( 'datatables.photo' ),
            'name' => __( 'employee.name' ),
            'email' => __( 'employee.email' ),
            'phone_number' => __( 'employee.phone_number' ),
            'identification_number' => __( 'employee.identification_number' ),
            'designation' => __( 'employee.designation' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateEmployee = Employee::find( $request->id );
            $updateEmployee->name = $request->name;
            $updateEmployee->email = $request->email;
            $updateEmployee->phone_number = $request->phone_number;
            $updateEmployee->identification_number = $request->identification_number;
            $updateEmployee->designation = $request->designation;
            $updateEmployee->remarks = $request->remarks;
            $updateEmployee->save();

            if ( $request->photo ) {
                $file = FileManager::find( $request->photo );
                if ( $file ) {

                    Storage::disk( 'public' )->delete( $updateEmployee->photo );

                    $fileName = explode( '/', $file->file );
                    $target = 'employees/' . $updateEmployee->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $file->file, $target );
    
                    $updateEmployee->photo = $target;
                    $updateEmployee->save();
    
                    $file->status = 10;
                    $file->save();
                }
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.employees' ) ) ] ),
        ] );
    }

    public static function updateEmployeeStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updateEmployee = Employee::find( $request->id );
        $updateEmployee->status = $request->status;
        $updateEmployee->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.employees' ) ) ] ),
        ] );
    }
}