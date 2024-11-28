<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    Company,
    Customer,
    Expense,
    ExpenseAccount,
    ExpenseCategory,
    Booking,
    FileManager,
    Product,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExpenseService
{

    public static function createExpense( $request ) {
        $validator = Validator::make( $request->all(), [
            'expense_date' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'expenses_account' => [ 'nullable', 'exists:expenses_accounts,id' ],
            'expenses_category' => [ 'nullable', 'exists:expenses_categories,id' ],
            'amount' => [ 'nullable', 'numeric' ,'min:0' ],
        ] );

        $attributeName = [
            'title' => __( 'expense.title' ),
            'description' => __( 'expense.description' ),
            'image' => __( 'expense.image' ),
            'thumbnail' => __( 'expense.thumbnail' ),
            'url_slug' => __( 'expense.url_slug' ),
            'structure' => __( 'expense.structure' ),
            'size' => __( 'expense.size' ),
            'phone_number' => __( 'expense.phone_number' ),
            'sort' => __( 'expense.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $expenseCreate = Expense::create([
                'causer_id' => auth()->user()->id,
                'expenses_account_id' => $request->expenses_account,
                'expenses_category_id' => $request->expenses_category,
                'remarks' => $request->remarks,
                'title' => $request->title,
                'reference' => Helper::generateExpenseNumber(),
                'expenses_date' => $request->expense_date,
                'amount' => $request->amount,
                'final_amount' => $request->amount,
                'status' => 10,
            ]);

            $attachment = explode( ',', $request->attachment );
            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'expense/' . $expenseCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $expenseCreate->attachment = $target;
                   $expenseCreate->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.expenses' ) ) ] ),
        ] );
    }
    
    public static function updateExpense( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'expense_date' => [ 'nullable' ],
            'remarks' => [ 'nullable' ],
            'title' => [ 'nullable' ],
            'attachment' => [ 'nullable' ],
            'expenses_account' => [ 'nullable', 'exists:expenses_accounts,id' ],
            'expenses_category' => [ 'nullable', 'exists:expenses_categories,id' ],
            'amount' => [ 'nullable', 'numeric' ,'min:0' ],
        ] );

        $attributeName = [
            'title' => __( 'expense.title' ),
            'description' => __( 'expense.description' ),
            'image' => __( 'expense.image' ),
            'thumbnail' => __( 'expense.thumbnail' ),
            'url_slug' => __( 'expense.url_slug' ),
            'structure' => __( 'expense.structure' ),
            'size' => __( 'expense.size' ),
            'phone_number' => __( 'expense.phone_number' ),
            'sort' => __( 'expense.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateExpense = Expense::find( $request->id );

            $updateExpense->title = $request->title ?? $updateExpense->title;
            $updateExpense->remarks = $request->remarks ?? $updateExpense->remarks;
            $updateExpense->causer_id = auth()->user()->id;
            $updateExpense->expenses_account_id = $request->expenses_account ?? $updateExpense->expenses_account_id;
            $updateExpense->expenses_category_id = $request->expenses_account ?? $updateExpense->expenses_category_id;
            $updateExpense->expenses_date = $request->expenses_date ?? $updateExpense->expenses_date;
            $updateExpense->amount = $request->amount ?? $updateExpense->amount;
            $updateExpense->final_amount = $request->amount ?? $updateExpense->final_amount;

            $attachment = explode( ',', $request->attachment );

            $attachmentFiles = FileManager::whereIn( 'id', $attachment )->get();

            if ( $attachmentFiles ) {
                foreach ( $attachmentFiles as $attachmentFile ) {

                    $fileName = explode( '/', $attachmentFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'expense/' . $updateExpense->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $attachmentFile->file, $target );

                   $updateExpense->attachment = $target;
                   $updateExpense->save();

                    $attachmentFile->status = 10;
                    $attachmentFile->save();

                }
            }

            $updateExpense->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.expenses' ) ) ] ),
        ] );
    }

     public static function allExpenses( $request ) {

        $expenses = Expense::with( [ 'expensesAccount', 'expensesCategory'] )->select( 'expenses.*');

        $filterObject = self::filter( $request, $expenses );
        $expense = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $expense->orderBy( 'expenses.created_at', $dir );
                    break;
                case 2:
                    $expense->orderBy( 'expenses.title', $dir );
                    break;
                case 3:
                    $expense->orderBy( 'expenses.description', $dir );
                    break;
            }
        }

            $expenseCount = $expense->count();

            $limit = $request->length;
            $offset = $request->start;

            $expenses = $expense->skip( $offset )->take( $limit )->get();

            if ( $expenses ) {
                $expenses->append( [
                    'encrypted_id',
                    'attachment_path',
                ] );
            }

            $totalRecord = Expense::count();

            $data = [
                'expenses' => $expenses,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $expenseCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'expenses.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->reference ) ) {
            $model->where( 'expenses.reference', 'LIKE', '%' . $request->reference . '%' );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'expenses.title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        if ( !empty( $request->id ) ) {
            $model->where( 'expenses.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->expenses_account)) {
            $model->whereHas('expensesAccount', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->expenses_account . '%');
            });
            $filter = true;
        }

        if (!empty($request->expenses_category)) {
            $model->whereHas('expensesCategory', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->expenses_category . '%');
            });
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneExpense( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $expense = Expense::with( [ 'expensesCategory', 'expensesAccount'] )->find( $request->id );

        $expense->append( [
            'encrypted_id',
            'attachment_path',
        ] );
        
        return response()->json( $expense );
    }

    public static function deleteExpense( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'expense.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Expense::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.expenses' ) ) ] ),
        ] );
    }

    public static function updateExpenseStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateExpense = Expense::find( $request->id );
            $updateExpense->status = $updateExpense->status == 10 ? 20 : 10;

            $updateExpense->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'expense' => $updateExpense,
                    'message_key' => 'update_expense_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_expense_failed',
            ], 500 );
        }
    }

    public static function removeExpenseAttachment( $request ) {

        $updateFarm = Expense::find( Helper::decode($request->id) );
        $updateFarm->attachment = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'expense.attachment' ) ) ] ),
        ] );
    }

    public static function oneExpenseCategory( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $expense = ExpenseCategory::with( [ 'expense', 'account'] )->find( $request->id );

        $expense->append( [
            'encrypted_id',
        ] );
        
        return response()->json( $expense );
    }

    public static function createExpenseCategory( $request ) {

        $validator = Validator::make( $request->all(), [
            'expense' => [ 'nullable', 'exists:expenses,id' ],
            'account' => [ 'nullable', 'exists:expenses_accounts,id' ],
            'paid_amount' => [ 'nullable', 'numeric' ,'min:0' ],
            'paid_by' => [ 'nullable' ],

        ] );

        $attributeName = [
            'title' => __( 'expense.title' ),
            'description' => __( 'expense.description' ),
            'image' => __( 'expense.image' ),
            'thumbnail' => __( 'expense.thumbnail' ),
            'url_slug' => __( 'expense.url_slug' ),
            'structure' => __( 'expense.structure' ),
            'size' => __( 'expense.size' ),
            'phone_number' => __( 'expense.phone_number' ),
            'sort' => __( 'expense.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $expenseCreate = ExpenseCategory::create([
                'expense_id' => $request->expense,
                'account_id' => $request->account,
                'reference' => Helper::generateExpenseCategoryNumber(),
                'remarks' => $request->remarks,
                'paid_amount' => $request->paid_amount,
                'paid_by' => $request->paid_by,
                'status' => 10,
            ]);

            $expense = Expense::find($request->expense);
            $expense->paid_amount += $request->paid_amount;
            $expense->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.expense_transactions' ) ) ] ),
        ] );
    }

    public static function updateExpenseCategoryStatus( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateExpenseCategory = ExpenseCategory::find( $request->id );
            $updateExpenseCategory->status = $updateExpense->status == 10 ? 20 : 10;
            $updateExpenseCategory->save();

            $expense = Expense::find($updateExpense->expense_id);
            if( $updateExpenseCategory->status == 10 ) {
                $expense->paid_amount += $updateExpenseCategory->paid_amount;
            }else{
                $expense->paid_amount -= $updateExpenseCategory->paid_amount;
            }
            $expense->save();

            DB::commit();

            return response()->json( [
                'data' => [
                    'expense' => $updateExpense,
                    'message_key' => 'update_expense_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'update_expense_success',
            ], 500 );
        }
    }
}