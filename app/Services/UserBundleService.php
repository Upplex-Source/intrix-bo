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
    UserBundle,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock,
    Cart,
    CartMeta,
    Order,
    OrderMeta,
    ProductBundle,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class UserBundleService
{

    public static function createUserBundle( $request ) {

        $validator = Validator::make($request->all(), [
            'product_bundle' => ['required', 'exists:product_bundles,id'],
            'users' => ['required'],
            'quantity' => ['nullable', 'min:1'],
        ]);
        
        $attributeName = [
            'product_bundle' => __('user_bundle.title'),
            'user' => __('user_bundle.description'),
            'expired_date' => __('user_bundle.image'),
            'redeem_from' => __('user_bundle.code'),
            'secret_code' => __('user_bundle.price'),
        ];
        
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
        
        // Set attribute names for validation messages
        $validator->setAttributeNames($attributeName);
        
        // Perform validation
        $validator->validate();

        DB::beginTransaction();
        
        try {

            $users = explode( ',', $request->users );

            $productBundle = ProductBundle::find( $request->product_bundle );

            for( $x=0; $x < $request->quantity; $x++ ){

                foreach( $users as $user ){
                    $userBundle = UserBundle::create([
                        'user_id' => $user,
                        'product_bundle_id' => $productBundle->id,
                        'status' => 10,
                        'total_cups' => $productBundle->productBundleMetas->first()->quantity,
                        'cups_left' => $productBundle->productBundleMetas->first()->quantity,
                        'last_used' => null,
                    ]);
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.user_bundles' ) ) ] ),
        ] );
    }

    public static function allUserBundles( $request ) {

        $user_bundles = UserBundle::with( ['user','productBundle'] )->select( 'user_bundles.*');

        $filterObject = self::filter( $request, $user_bundles );
        $productBundle = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $productBundle->orderBy( 'user_bundles.created_at', $dir );
                    break;
                case 2:
                    $productBundle->orderBy( 'user_bundles.title', $dir );
                    break;
                case 3:
                    $productBundle->orderBy( 'user_bundles.description', $dir );
                    break;
            }
        }

            $productBundleCount = $productBundle->count();

            $limit = $request->length;
            $offset = $request->start;

        $user_bundles = $productBundle->skip( $offset )->take( $limit )->get();

        if ( $user_bundles ) {
            $user_bundles->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = UserBundle::count();

        $data = [
            'user_bundles' => $user_bundles,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $productBundleCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->id ) ) {
            $model->where( 'user_bundles.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->user)) {
            $model->whereHas('user', function ($query) use ($request) {
                $query->where('users.phone_number', 'LIKE', '%' . $request->user . '%');
            });
            $filter = true;
        }

        if (!empty($request->title)) {
            $model->whereHas('productBundle', function ($query) use ($request) {
                $query->where('product_bundles.title', 'LIKE', '%' . $request->title . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->productBundle_type ) ) {
            $model->whereHas('product_bundle', function ($query) use ($request) {
                $query->where( 'type', $request->productBundle_type );
            });
            $filter = true;
        }

        if ( !empty( $request->discount_type ) ) {
            $model->whereHas('product_bundle', function ($query) use ($request) {
                $query->where( 'discount_type', $request->discount_type );
            });
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }

        if ( !empty( $request->code ) ) {
            $model->where( 'code', 'LIKE', '%' . $request->code . '%' );
            $filter = true;
        }

        if ( !empty( $request->vending_machine_id ) ) {
            $vendingMachineUserBundles = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'product_bundle_id' );
            $model->whereNotIn( 'id', $vendingMachineUserBundles );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function updateUserBundleStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateUserBundle = UserBundle::find( $request->id );
            $updateUserBundle->status = $updateUserBundle->status == 10 ? 20 : 10;

            $updateUserBundle->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'product_bundle' => $updateUserBundle,
                    'message_key' => 'update_product_bundle_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_product_bundle_failed',
            ], 500 );
        }
    }

    public static function updateUserBundle( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make($request->all(), [
            'product_bundle' => ['required', 'exists:product_bundles,id'],
            'users' => ['required'],
            'quantity' => ['nullable', 'min:1'],
        ]);
        
        $attributeName = [
            'product_bundle' => __('user_bundle.title'),
            'user' => __('user_bundle.description'),
            'expired_date' => __('user_bundle.image'),
            'redeem_from' => __('user_bundle.code'),
            'secret_code' => __('user_bundle.price'),
        ];
        
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
        
        // Set attribute names for validation messages
        $validator->setAttributeNames($attributeName);
        
        // Perform validation
        $validator->validate();

        DB::beginTransaction();

        try {

            $users = explode( ',', $request->users );

            $productBundle = ProductBundle::find( $request->product_bundle );
            $updateUserBundle = UserBundle::find( $request->id );

            $updateUserBundle->product_bundle_id = $productBundle->id;

            $updateUserBundle->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'product_bundle' => $updateUserBundle,
                    'message_key' => 'update_product_bundle_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_product_bundle_failed',
            ], 500 );
        }
    }
}