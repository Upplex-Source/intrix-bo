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
    ExpenseAccount,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExpenseAccountService
{

    public static function createExpenseAccount( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'expense_account.title' ),
            'description' => __( 'expense_account.description' ),
            'image' => __( 'expense_account.image' ),
            'thumbnail' => __( 'expense_account.thumbnail' ),
            'url_slug' => __( 'expense_account.url_slug' ),
            'structure' => __( 'expense_account.structure' ),
            'size' => __( 'expense_account.size' ),
            'phone_number' => __( 'expense_account.phone_number' ),
            'sort' => __( 'expense_account.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $expense_accountCreate = ExpenseAccount::create([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'expense_account/' . $expense_accountCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $expense_accountCreate->image = $target;
                   $expense_accountCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'expense_account/' . $expense_accountCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $expense_accountCreate->thumbnail = $target;
                   $expense_accountCreate->save();

                    $thumbnailFile->status = 10;
                    $thumbnailFile->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.expenses_accounts' ) ) ] ),
        ] );
    }
    
    public static function updateExpenseAccount( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

         
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'expense_account.title' ),
            'description' => __( 'expense_account.description' ),
            'image' => __( 'expense_account.image' ),
            'thumbnail' => __( 'expense_account.thumbnail' ),
            'url_slug' => __( 'expense_account.url_slug' ),
            'structure' => __( 'expense_account.structure' ),
            'size' => __( 'expense_account.size' ),
            'phone_number' => __( 'expense_account.phone_number' ),
            'sort' => __( 'expense_account.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateExpenseAccount = ExpenseAccount::find( $request->id );
    
            $updateExpenseAccount->title = $request->title;
            $updateExpenseAccount->description = $request->description;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'expense_account/' . $updateExpenseAccount->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateExpenseAccount->image = $target;
                   $updateExpenseAccount->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateExpenseAccount->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.expenses_accounts' ) ) ] ),
        ] );
    }

     public static function allExpenseAccounts( $request ) {

        $expenses_accounts = ExpenseAccount::select( 'expenses_accounts.*');

        $filterObject = self::filter( $request, $expenses_accounts );
        $expense_account = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $expense_account->orderBy( 'expenses_accounts.created_at', $dir );
                    break;
                case 2:
                    $expense_account->orderBy( 'expenses_accounts.title', $dir );
                    break;
                case 3:
                    $expense_account->orderBy( 'expenses_accounts.description', $dir );
                    break;
            }
        }

            $expense_accountCount = $expense_account->count();

            $limit = $request->length;
            $offset = $request->start;

            $expenses_accounts = $expense_account->skip( $offset )->take( $limit )->get();

            if ( $expenses_accounts ) {
                $expenses_accounts->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = ExpenseAccount::count();

            $data = [
                'expenses_accounts' => $expenses_accounts,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $expense_accountCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'expenses_accounts.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'expenses_accounts.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_expense_account)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_expense_account . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneExpenseAccount( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $expense_account = ExpenseAccount::find( $request->id );

        $expense_account->append( ['encrypted_id','image_path'] );
        
        return response()->json( $expense_account );
    }

    public static function deleteExpenseAccount( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'expense_account.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            ExpenseAccount::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.expenses_accounts' ) ) ] ),
        ] );
    }

    public static function updateExpenseAccountStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateExpenseAccount = ExpenseAccount::find( $request->id );
            $updateExpenseAccount->status = $updateExpenseAccount->status == 10 ? 20 : 10;

            $updateExpenseAccount->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'expense_account' => $updateExpenseAccount,
                    'message_key' => 'update_expense_account_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_expense_account_failed',
            ], 500 );
        }
    }

    public static function removeExpenseAccountGalleryImage( $request ) {

        $updateFarm = ExpenseAccount::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}