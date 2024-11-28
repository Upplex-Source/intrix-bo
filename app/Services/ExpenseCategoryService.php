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
    ExpenseCategory,
    Booking,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExpenseCategoryService
{

    public static function createExpenseCategory( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
            'image' => [ 'nullable' ],
            'thumbnail' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'expense_category.title' ),
            'description' => __( 'expense_category.description' ),
            'image' => __( 'expense_category.image' ),
            'thumbnail' => __( 'expense_category.thumbnail' ),
            'url_slug' => __( 'expense_category.url_slug' ),
            'structure' => __( 'expense_category.structure' ),
            'size' => __( 'expense_category.size' ),
            'phone_number' => __( 'expense_category.phone_number' ),
            'sort' => __( 'expense_category.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $expense_categoryCreate = ExpenseCategory::create([
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

                    $target = 'expense_category/' . $expense_categoryCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $expense_categoryCreate->image = $target;
                   $expense_categoryCreate->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            if ( $thumbnailFiles ) {
                foreach ( $thumbnailFiles as $thumbnailFile ) {

                    $fileName = explode( '/', $thumbnailFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'expense_category/' . $expense_categoryCreate->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $thumbnailFile->file, $target );

                   $expense_categoryCreate->thumbnail = $target;
                   $expense_categoryCreate->save();

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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.expenses_categories' ) ) ] ),
        ] );
    }
    
    public static function updateExpenseCategory( $request ) {

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
            'title' => __( 'expense_category.title' ),
            'description' => __( 'expense_category.description' ),
            'image' => __( 'expense_category.image' ),
            'thumbnail' => __( 'expense_category.thumbnail' ),
            'url_slug' => __( 'expense_category.url_slug' ),
            'structure' => __( 'expense_category.structure' ),
            'size' => __( 'expense_category.size' ),
            'phone_number' => __( 'expense_category.phone_number' ),
            'sort' => __( 'expense_category.sort' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $updateExpenseCategory = ExpenseCategory::find( $request->id );
    
            $updateExpenseCategory->title = $request->title;
            $updateExpenseCategory->description = $request->description;

            $image = explode( ',', $request->image );
            $thumbnail = explode( ',', $request->thumbnail );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();
            $thumbnailFiles = FileManager::whereIn( 'id', $thumbnail )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'expense_category/' . $updateExpenseCategory->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateExpenseCategory->image = $target;
                   $updateExpenseCategory->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateExpenseCategory->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.expenses_categories' ) ) ] ),
        ] );
    }

     public static function allExpenseCategories( $request ) {

        $expenses_categories = ExpenseCategory::select( 'expenses_categories.*');

        $filterObject = self::filter( $request, $expenses_categories );
        $expense_category = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $expense_category->orderBy( 'expenses_categories.created_at', $dir );
                    break;
                case 2:
                    $expense_category->orderBy( 'expenses_categories.title', $dir );
                    break;
                case 3:
                    $expense_category->orderBy( 'expenses_categories.description', $dir );
                    break;
            }
        }

            $expense_categoryCount = $expense_category->count();

            $limit = $request->length;
            $offset = $request->start;

            $expenses_categories = $expense_category->skip( $offset )->take( $limit )->get();

            if ( $expenses_categories ) {
                $expenses_categories->append( [
                    'encrypted_id',
                    'image_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = ExpenseCategory::count();

            $data = [
                'expenses_categories' => $expenses_categories,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $expense_categoryCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'expenses_categories.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'expenses_categories.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_expense_category)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_expense_category . '%');
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

    public static function oneExpenseCategory( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $expense_category = ExpenseCategory::find( $request->id );

        $expense_category->append( ['encrypted_id','image_path'] );
        
        return response()->json( $expense_category );
    }

    public static function deleteExpenseCategory( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'expense_category.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            ExpenseCategory::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.expenses_categories' ) ) ] ),
        ] );
    }

    public static function updateExpenseCategoryStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateExpenseCategory = ExpenseCategory::find( $request->id );
            $updateExpenseCategory->status = $updateExpenseCategory->status == 10 ? 20 : 10;

            $updateExpenseCategory->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'expense_category' => $updateExpenseCategory,
                    'message_key' => 'update_expense_category_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_expense_category_failed',
            ], 500 );
        }
    }

    public static function removeExpenseCategoryGalleryImage( $request ) {

        $updateFarm = ExpenseCategory::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}