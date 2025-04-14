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
    Guide,
    GuideCountry,
    GuideBranch,
    GuideState,
    Booking,
    FileManager,
    VendingMachine,
    VendingMachineStock,
    GuideUsage,
    Cart,
    CartMeta,
    Order,
    OrderMeta,
    UserGuide,
};

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class GuideService
{

    public static function createGuide( $request ) {

        $validator = Validator::make( $request->all(), [
            'country' => [ 'required' ],
            'file' => ['required', 'mimes:jpeg,jpg,png,pdf,mp4,mpeg,quicktime,avi,webm'],
        ] );

        $attributeName = [
            'file' => __( 'guide.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $guideCreate = Guide::create([
                'title' => null,
                'description' => null,
                'sequence' => 1,
                'status' => 10,
                'file_type' => $request->file_type ? $request->file_type : 1,
                'country_id' => ( $request->country != 'null' && $request->country != null ) ? $request->country : null,
            ]);

            $name = mb_substr( $request->file( 'file' )->getClientOriginalName(), 0, 255, 'UTF-8' );
            $path = $request->file( 'file' )->store( 'file-managers', [ 'disk' => 'public' ] );
            $type = $request->file( 'file' )->getClientOriginalExtension() == 'pdf' ? 1 : 2;

            $guideCreate->title = $name;
            $guideCreate->file = $path;
            $guideCreate->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.guides' ) ) ] ),
            'data' => [
                'id' => $guideCreate->id,
                'url' => $guideCreate->thumbnailPath,
                'title' => $guideCreate->title,
            ],
            'status' => 200
        ] );
    }
    
    public static function updateGuide( $request ) {

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
            'description' => [ 'nullable' ],
        ] );

        $attributeName = [
            'title' => __( 'guide.title' ),
            'description' => __( 'guide.description' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();
        
        DB::beginTransaction();

        try {
            $updateGuide = Guide::find( $request->id );
    
            $updateGuide->title = $request->title;
            $updateGuide->description = $request->description;
            $updateGuide->country_id = $request->country;
            
            $image = explode( ',', $request->image );

            $imageFiles = FileManager::whereIn( 'id', $image )->get();

            if ( $imageFiles ) {
                foreach ( $imageFiles as $imageFile ) {

                    $fileName = explode( '/', $imageFile->file );
                    $fileExtention = pathinfo($fileName[1])['extension'];

                    $target = 'guide/' . $updateGuide->id . '/' . $fileName[1];
                    Storage::disk( 'public' )->move( $imageFile->file, $target );

                   $updateGuide->file = $target;
                   $updateGuide->save();

                    $imageFile->status = 10;
                    $imageFile->save();

                }
            }

            $updateGuide->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.guides' ) ) ] ),
        ] );
    }

    public static function allGuides( $request ) {

        $guides = Guide::select( 'guides.*' )->orderBy( 'sequence' );

        $filterObject = self::filter( $request, $guides );
        $guide = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $guide->orderBy( 'guides.created_at', $dir );
                    break;
                case 2:
                    $guide->orderBy( 'guides.title', $dir );
                    break;
                case 3:
                    $guide->orderBy( 'guides.description', $dir );
                    break;
            }
        }

            $guideCount = $guide->count();

            $limit = $request->length;
            $offset = $request->start;

            $guides = $guide->skip( $offset )->take( $limit )->get();

            if ( $guides ) {
                $guides->append( [
                    'encrypted_id',
                    'file_path',
                    'thumbnail_path',
                ] );
            }

            $totalRecord = Guide::count();

            $data = [
                'guides' => $guides,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $guideCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

    }

    public static function allStocksGuides( $request ) {

        // Query all guides not in vending_machine_stocks
        $guides = Guide::select( 'guides.*' )
            ->whereNotIn('id', function ($query) {
                $query->select('guide_id')
                    ->from('vending_machine_stocks')
                    ->whereNotNull('guide_id');
            });
    
        $filterObject = self::filter( $request, $guides );
        $guide = $filterObject['model'];
        $filter = $filterObject['filter'];
    
        // Handle sorting
        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $guide->orderBy( 'guides.created_at', $dir );
                    break;
                case 3:
                    $guide->orderBy( 'guides.title', $dir );
                    break;
                case 4:
                    $guide->orderBy( 'guides.description', $dir );
                    break;
            }
        }
    
        $guideCount = $guide->count();
    
        $limit = $request->length;
        $offset = $request->start;
    
        // Paginate results
        $guides = $guide->skip( $offset )->take( $limit )->get();
    
        if ( $guides ) {
            $guides->append( [
                'encrypted_id',
                'image_path',
            ] );
        }
    
        $totalRecord = Guide::whereNotIn('id', function ($query) {
            $query->select('guide_id')
                ->from('vending_machine_stocks')
                ->whereNotNull('guide_id');
        })->count();
    
        $data = [
            'guides' => $guides,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $guideCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];
    
        return response()->json( $data );
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'guides.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'guides.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_guide)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_guide . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->guide_type ) ) {
            $model->where( 'type', $request->guide_type );
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
            $vendingMachineGuides = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'guide_id' );
            $model->whereNotIn( 'id', $vendingMachineGuides );
            $filter = true;
        }

        if ( !empty( $request->country_id ) ) {
            $model->where( 'country_id', $request->country_id );
            $filter = true;
        }

        if ( !empty( $request->file_type ) ) {
            $model->where( 'file_type', $request->file_type );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneGuide( $request ) {

        $guide = Guide::with( ['country'] )->find( $request->id );

        $guide->append( ['encrypted_id','file_path','thumbnail_path'] );
        
        return response()->json( $guide );
    }

    public static function oneGuideClient( $request ) {

        $guide = Guide::find( $request->id );

        $guide->append( ['encrypted_id','image_path'] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_guide_success',
            'data' => $guide,
        ] );
    }

    public static function deleteGuide( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'guide.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            Guide::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.guides' ) ) ] ),
        ] );
    }

    public static function updateGuideStatus( $request ) {

        DB::beginTransaction();

        try {

            $updateGuide = Guide::find( $request->id );
            $updateGuide->status = $updateGuide->status == 10 ? 20 : 10;

            $updateGuide->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'guide' => $updateGuide,
                    'message_key' => 'update_guide_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_guide_failed',
            ], 500 );
        }
    }

    public static function removeGuideGalleryImage( $request ) {

        $updateGuide = Guide::find( $request->id );

        Storage::delete( 'public/' . $updateGuide->file );
        $updateGuide->file = null;

        $updateGuide->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }

    public static function allGuidesForVendingMachine( $request ) {

        $guides = Guide::select( 'guides.*');

        $filterObject = self::filter( $request, $guides );
        $guide = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $guide->orderBy( 'guides.created_at', $dir );
                    break;
                case 2:
                    $guide->orderBy( 'guides.title', $dir );
                    break;
                case 3:
                    $guide->orderBy( 'guides.description', $dir );
                    break;
            }
        }

        $guideCount = $guide->count();

        $limit = $request->length;
        $offset = $request->start;

        $guides = $guide->skip( $offset )->take( $limit )->get();

        if ( $guides ) {

            $guides->append( [
                'encrypted_id',
                'image_path',
            ] );
        }

        $totalRecord = Guide::count();

        $data = [
            'guides' => $guides,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $guideCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
              
    }

    public static function ckeUpload( $request ) {

        $file = $request->file( 'file' )->store( 'vouhcer/ckeditor', [ 'disk' => 'public' ] );

        $data = [
            'url' => asset( 'storage/' . $file ),
        ];

        return response()->json( $data );
    }

    public static function getGuides( $request )
    {
        $guides = Guide::where('status', 10)
        ->orderBy( 'sequence' );

        $guides = $guides->get();

        foreach( $guides as $guide ) {
            $guide->append( ['image_path'] );
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'get_guide_success',
            'data' => $guides,
        ] );

    }

    public static function validateGuide( $request )
    {

        $validator = Validator::make( $request->all(), [
            'promo_code' => [ 'required' ],
        ] );

        $attributeName = [
            'promo_code' => __( 'guide.promo_code' ),
        ];
        
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $validator = Validator::make( $request->all(), [
            'cart' => [ 'required', function( $attribute, $value, $fail ) {
                $cart = Cart::find( $value )->where('status', 10);
                if ( !$cart ) {
                    $fail( __( 'validation.exists' ) );
                    return false;
                }
            } ]
        ] );

        $validator->stopOnFirstFailure( true )->validate();

        $guide = Guide::where('status', 10)
            ->where( 'id', $request->promo_code )
            ->orWhere('promo_code', $request->promo_code)
            ->where(function ( $query) {
                $query->where(function ( $q) {
                    $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', Carbon::now());
                })
                ->where(function ( $q) {
                    $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', Carbon::now());
                });
        })->first();

        if ( !$guide ) {
            return response()->json( [
                'message_key' => 'guide_not_available',
                'message' => __('guide.guide_not_available'),
                'errors' => [
                    'guide' => __('guide.guide_not_available')
                ]
            ], 422 );
        }

        // user's usage
        $user = auth()->user();
        $guideUsages = GuideUsage::where( 'guide_id', $guide->id )->where( 'user_id', $user->id )->get();

        if ( $guideUsages->count() > $guide->usable_amount ) {
            return response()->json( [
                'message_key' => 'guide_you_have_maximum_used',
                'message' => __('guide.guide_you_have_maximum_used'),
                'errors' => [
                    'guide' => __('guide.guide_you_have_maximum_used')
                ]
            ], 422 );
        }

        // total claimable
        if ( $guide->total_claimable <= 0 ) {
            return response()->json( [
                'message_key' => 'guide_fully_claimed',
                'message' => __('guide.guide_fully_claimed'),
                'errors' => [
                    'guide' => __('guide.guide_fully_claimed')
                ]
            ], 422 );
        }

        // check is user able to claim this
        $userGuide = UserGuide::where( 'guide_id', $guide->id )->where( 'user_id', $user->id )->first();
        if(!$userGuide){
            $userPoints = $user->wallets->where( 'type', 2 )->first();

            if ( $userPoints->balance < $guide->points_required ) {

                return response()->json( [
                    'message_key' => 'minimum_points_required',
                    'message' => 'Mininum of ' . $guide->points_required . ' points is required to claim this guide',
                    'errors' => [
                        'guide' => 'Mininum of ' . $guide->points_required . ' points is required to claim this guide',
                    ]
                ], 422 );
    
            }
        }

        $cart = Cart::find( $request->cart );

        if ( $guide->discount_type == 3 ) {

            $adjustment = json_decode( $guide->buy_x_get_y_adjustment );
            
            $x = $cart->cartMetas->whereIn( 'product_id', $adjustment->buy_products )->count();

            if ( $x < $adjustment->buy_quantity ) {
                return response()->json( [
                   'required_amount' => $adjustment->buy_quantity,
                   'message' => __( 'guide.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] ),
                   'message_key' => 'guide.min_quantity_of_x_' . $adjustment->buy_products[0] . '_' .  Product::find( $adjustment->buy_products[0] )->value( 'title' ) ,
                        'errors' => [
                            'guide' => __( 'guide.min_quantity_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id',  $adjustment->buy_products[0] )->value( 'title' ) ] )

                        ]
                ], 422 );
            }

        } else {

            $adjustment = json_decode( $guide->buy_x_get_y_adjustment );

            if ( $cart->total_price < $adjustment->buy_quantity ) {
                return response()->json( [
                    'required_amount' => $adjustment->buy_quantity,
                    'message' => __( 'guide.min_spend_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] ),
                    'message_key' => 'guide.min_spend_of_x',
                    'errors' => [
                        'guide' => __( 'guide.min_spend_of_x', [ 'title' => $adjustment->buy_quantity . ' ' . Product::where( 'id', $adjustment->buy_products[0] )->value( 'title' ) ] )
                    ]
                ], 422 );
            }

        }
    
        return response()->json( [
            'message' => 'guide.guide_validated',
        ] );
    }

    public static function claimGuide( $request )
    {

        $validator = Validator::make( $request->all(), [
            'guide_id' => [ 'required' ],
        ] );

        $attributeName = [
            'guide_id' => __( 'guide.guide_id' ),
        ];
        
        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        $guide = Guide::where( 'id', $request->guide_id )
        ->orWhere( 'promo_code', $request->guide_id )
            ->where(function ( $query) {
                $query->where(function ( $q) {
                    $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', Carbon::now());
                })
                ->where(function ( $q) {
                    $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', Carbon::now());
                });
        })
        ->where( 'type', 2 )
        ->where( 'status', 10 )->first();

        if ( !$guide ) {
            return response()->json( [
                'message_key' => 'guide_not_available',
                'message' => __('guide.guide_not_available'),
                'errors' => [
                    'guide' => __('guide.guide_not_available'),
                ]
            ], 422 );
        }
        $user = auth()->user();

        $guideUsages = GuideUsage::where( 'guide_id', $guide->id )->where( 'user_id', $user->id )->get();

        if ( $guideUsages->count() > $guide->usable_amount ) {
            return response()->json( [
                'message_key' => 'guide_fully_claimed',
                'message' => __('guide.guide_fully_claimed'),
                'errors' => [
                    'guide' => __('guide.guide_fully_claimed'),
                ]
            ], 422 );
        }

        $guideUserClaimed = UserGuide::where( 'guide_id', $guide->id )->where( 'user_id', $user->id )->count();

        if ( $guideUserClaimed >= $guide->claim_per_user ) {
            return response()->json( [
                'message_key' => 'guide_you_have_maximum_claimed',
                'message' => __('guide.guide_you_have_maximum_claimed'),
                'errors' => [
                    'guide' => __('guide.guide_you_have_maximum_claimed'),
                ]
            ], 422 );
        }
        
        $userPoints = $user->wallets->where( 'type', 2 )->first();

        if ( $userPoints->balance < $guide->points_required ) {

            return response()->json( [
                'required_amount' => $guide->points_required,
                'message' => 'Mininum of ' . $guide->points_required . ' points is required to claim this guide',
                'message_key' => 'minimum_points_is_required',
                'errors' => [
                    'guide' => 'Mininum of ' . $guide->points_required . ' points is required to claim this guide',
                ]
            ], 422 );

        }        
        
        if ( $guide->total_claimable <= 0 ) {
            return response()->json( [
                'message_key' => 'guide_fully_claimed',
                'message' => __('guide.guide_fully_claimed'),
                'errors' => [
                    'guide' => __('guide.guide_fully_claimed')
                ]
            ], 422 );
        }

        WalletService::transact( $userPoints, [
            'amount' => -$guide->points_required,
            'remark' => 'Claim Guide',
            'type' => $userPoints->type,
            'transaction_type' => 11,
        ] );

        $userGuide = UserGuide::create([
            'user_id' => $user->id,
            'guide_id' => $guide->id,
            'expired_date' => Carbon::now()->addDays($guide->validity_days),
            'status' => 10,
            'redeem_from' => 1,
            'total_left' => 1,
            'used_at' => null,
            'secret_code' => strtoupper( \Str::random( 8 ) ),
        ]);

        $guide->total_claimable -= 1;
        $guide->save();
    
        // notification
        UserService::createUserNotification(
            $user->id,
            'notification.user_guide_success',
            'notification.user_guide_success_content',
            'guide',
            'guide'
        );

        self::sendNotification( $order->user, 'guide', __( 'notification.user_guide_success_content' )  );

        return response()->json( [
            'message' => __('guide.guide_claimed'),
            'message_key' => 'guide_claimed',
            'data' => $userGuide->load(['guide'])
        ] );
    }

    private static function sendNotification( $user, $key, $message ) {

        $messageContent = array();

        $messageContent['key'] = $key;
        $messageContent['id'] = $user->id;
        $messageContent['message'] = $message;

        Helper::sendNotification( $affiliate->user_id, $messageContent );
        
    }

    // country
    public static function allGuideCountries( $request ) {

        $guides = GuideCountry::with( [ 'states', 'branches', 'guides' ] )->select( 'guide_countries.*' );

        $filterObject = self::filterGuideCountry( $request, $guides );
        $guide = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $guide->orderBy( 'guide_countries.created_at', $dir );
                    break;
                case 2:
                    $guide->orderBy( 'guide_countries.title', $dir );
                    break;
                case 3:
                    $guide->orderBy( 'guide_countries.description', $dir );
                    break;
            }
        }

            $guideCount = $guide->count();

            $limit = $request->length;
            $offset = $request->start;

            $guides = $guide->skip( $offset )->take( $limit )->get();

            if ( $guides ) {
                $guides->append( [
                    'encrypted_id',
                    'image_path',
                    'product_brochures_count',
                    'installation_guides_count',
                    'videos_count',
                ] );
            }

            $totalRecord = GuideCountry::count();

            $data = [
                'guide_countries' => $guides,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $guideCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

    }

    private static function filterGuideCountry( $request, $model ) {

        $filter = false;

        if ( !empty( $request->name ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_guide)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_guide . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->guide_type ) ) {
            $model->where( 'type', $request->guide_type );
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
            $vendingMachineGuides = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'guide_id' );
            $model->whereNotIn( 'id', $vendingMachineGuides );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function createGuideCountry( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
        ] );

        $attributeName = [
            'file' => __( 'guide.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {
            $guideCreate = GuideCountry::create([
                'name' => $request->name,
                'calling_code' => $request->calling_code,
                'status' => 10,
            ]);

            if( $request->states ){
                $states = explode( ',', $request->states );
                
                foreach( $states as $state ) {
                    GuideState::create( [
                        'name' => $state,
                        'country_id' => $guideCreate->id,
                        'status' => 10,
                    ] );
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
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.guide_countries' ) ) ] ),
        ] );
    }

    public static function updateGuideCountryStatus( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {
            $updateGuide = GuideCountry::find( $request->id );
            $updateGuide->status = $updateGuide->status == 10 ? 20 : 10;

            if( $updateGuide->status == 20 ){
                GuideBranch::where( 'country_id', $updateGuide->id )->update( [ 'status' => 20 ] );
                GuideState::where( 'country_id', $updateGuide->id )->update( [ 'status' => 20 ] );
                Guide::where( 'country_id', $updateGuide->id )->update( [ 'status' => 20 ] );
            } else {
                GuideBranch::where( 'country_id', $updateGuide->id )->update( [ 'status' => 10 ] );
                GuideState::where( 'country_id', $updateGuide->id )->update( [ 'status' => 10 ] );
                Guide::where( 'country_id', $updateGuide->id )->update( [ 'status' => 10 ] );
            }

            $updateGuide->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'guide' => $updateGuide,
                    'message_key' => 'update_guide_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_guide_failed',
            ], 500 );
        }
    }

    public static function oneGuideCountry( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $guide = GuideCountry::with( 'states' )->find( $request->id );

        $guide->append( ['encrypted_id','image_path'] );
        
        return response()->json( $guide );
    }

    public static function updateGuideCountry( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'name' => [ 'required' ],
        ] );

        $attributeName = [
            'file' => __( 'guide.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $guideCountryEdit = GuideCountry::find( $request->id );
            $guideCountryEdit->name = $request->name;
            $guideCountryEdit->calling_code = $request->calling_code;
            $guideCountryEdit->save();

            if( $request->states ){
                $states = json_decode( $request->states );

                $ids = collect( $states )
                    ->pluck( 'id' )
                    ->filter()
                    ->values()
                    ->toArray();

                // delete where not in id above
                GuideState::where( 'country_id', $guideCountryEdit->id )->whereNotIn( 'id', $ids )->delete();

                foreach( $states as $state ) {
                    if ( isset( $state->id ) && $state->id ) {
                        GuideState::where( 'id', $state->id )->update( [
                            'name' => $state->name,
                            'country_id' => $guideCountryEdit->id,
                            'status' => 10,
                        ] );
                    } else {
                        GuideState::create( [
                            'name' => $state->name,
                            'country_id' => $guideCountryEdit->id,
                            'status' => 10,
                        ] );
                    }
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.guide_countries' ) ) ] ),
        ] );
    }

    // state
    public static function allGuideStates( $request ) {

        $guides = GuideState::select( 'guide_states.*' );

        $filterObject = self::filterGuideState( $request, $guides );
        $guide = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $guide->orderBy( 'guide_states.created_at', $dir );
                    break;
                case 2:
                    $guide->orderBy( 'guide_states.title', $dir );
                    break;
                case 3:
                    $guide->orderBy( 'guide_states.description', $dir );
                    break;
            }
        }

            $guideCount = $guide->count();

            $limit = $request->length;
            $offset = $request->start;

            $guides = $guide->skip( $offset )->take( $limit )->get();

            if ( $guides ) {
                $guides->append( [
                    'encrypted_id',
                ] );
            }

            $totalRecord = GuideState::count();

            $data = [
                'states' => $guides,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $guideCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

    }

    private static function filterGuideState( $request, $model ) {

        $filter = false;

        if ( !empty( $request->name ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->title ) ) {
            $model->where( 'name', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_guide)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_guide . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->guide_type ) ) {
            $model->where( 'type', $request->guide_type );
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
            $vendingMachineGuides = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'guide_id' );
            $model->whereNotIn( 'id', $vendingMachineGuides );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    // branch
    public static function allGuideBranches( $request ) {

        $guides = GuideBranch::with( [ 'state', 'country' ] )->select( 'countries_branches.*' );

        $filterObject = self::filterGuideBranch( $request, $guides );
        $guide = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $guide->orderBy( 'countries_branches.created_at', $dir );
                    break;
                case 2:
                    $guide->orderBy( 'countries_branches.title', $dir );
                    break;
                case 3:
                    $guide->orderBy( 'countries_branches.description', $dir );
                    break;
            }
        }

            $guideCount = $guide->count();

            $limit = $request->length;
            $offset = $request->start;

            $guides = $guide->skip( $offset )->take( $limit )->get();

            if ( $guides ) {
                $guides->append( [
                    'encrypted_id',
                    'file_path',
                ] );
            }

            $totalRecord = GuideBranch::count();

            $data = [
                'guide_branches' => $guides,
                'draw' => $request->draw,
                'recordsFiltered' => $filter ? $guideCount : $totalRecord,
                'recordsTotal' => $totalRecord,
            ];

            return response()->json( $data );

    }

    private static function filterGuideBranch( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->phone_number ) ) {
            $model->where( 'phone_number', 'LIKE', '%' . $request->phone_number . '%' );
            $filter = true;
        }

        if ( !empty( $request->address ) ) {
            $model->where( 'address', 'LIKE', '%' . $request->address . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->country)) {
            $model->whereHas('country', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->country . '%');
            });
            $filter = true;
        }

        if (!empty($request->state)) {
            $model->whereHas('state', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->state . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->guide_type ) ) {
            $model->where( 'type', $request->guide_type );
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
            $vendingMachineGuides = VendingMachineStock::where( 'vending_machine_id', $request->vending_machine_id )->pluck( 'guide_id' );
            $model->whereNotIn( 'id', $vendingMachineGuides );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function createGuideBranch( $request ) {
        
        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
        ] );

        $attributeName = [
            'file' => __( 'guide.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();
        
        try {

            $state = $request->state ? GuideState::find( $request->state ) : null;

            $guideCreate = GuideBranch::create([
                'state_id' => $state ? $state->id : null,
                'country_id' => $state ? $state->country_id : null,
                'title' => $request->title,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => 10,
            ]);

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.guide_branches' ) ) ] ),
        ] );
    }

    public static function updateGuideBranchStatus( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {
            $updateGuide = GuideBranch::find( $request->id );
            $updateGuide->status = $updateGuide->status == 10 ? 20 : 10;

            $updateGuide->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'guide' => $updateGuide,
                    'message_key' => 'update_guide_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_guide_failed',
            ], 500 );
        }
    }

    public static function oneGuideBranch( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $guide = GuideBranch::with( 'state' )->find( $request->id );

        $guide->append( ['encrypted_id','file_path'] );
        
        return response()->json( $guide );
    }

    public static function updateGuideBranch( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'title' => [ 'required' ],
        ] );

        $attributeName = [
            'file' => __( 'guide.image' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            $guideBranchEdit = GuideBranch::find( $request->id );
            $guideBranchEdit->title = $request->title;
            $guideBranchEdit->phone_number = $request->phone_number;
            $guideBranchEdit->address = $request->address;
            $guideBranchEdit->latitude = $request->latitude;
            $guideBranchEdit->longitude = $request->longitude;

            if ( $request->state ) {
                $state = $request->state ? GuideState::find( $request->state ) : null;
                $guideBranchEdit->state_id = $state->id;
                $guideBranchEdit->country_id = $state->country_id;

            }

            $guideBranchEdit->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.guide_branches' ) ) ] ),
        ] );
    }

    // member
    public static function getCountries( $request )
    {
        $guideCountries = GuideCountry::with( [ 'states.branches' ] )->where('status', 10)
        ->when( $request->country, function ( $query ) use ( $request ) {
            $query->where( 'id', $request->country )
                ->orWhere( 'name', $request->country );
        })->get();

        $guideCountries->makeHidden( [ 'image','currency_symbol','iso_alpha2_code','image','iso_alpha3_code','calling_code','status','created_at','updated_at','title','description' ] );

        foreach( $guideCountries as $guideCountry ){
            foreach( $guideCountry->states as $state ){

                $state->makeHidden( [ 'image','currency_symbol','iso_alpha2_code','image','iso_alpha3_code','calling_code','status','created_at','updated_at','title','description' ] );

                if( $state->branches ){
                    $state->branches->makeHidden( [ 'image','currency_symbol','iso_alpha2_code','image','iso_alpha3_code','calling_code','status','created_at','updated_at','title','description' ] );
                }
            }
        }

        return response()->json( [
            'message' => '',
            'message_key' => 'get_countries_success',
            'countries' => $guideCountries,
        ] );

    }

    // member
    public static function getStates( $request )
    {
        $guideStates = GuideState::where('status', 10)
        ->when( $request->country, function ( $query ) use ( $request ) {
            $query->whereHas( 'country', function ( $q ) use ( $request ) {
                $q->where( 'id', $request->country )
                  ->orWhere( 'name', $request->country );
            });
        })->get();

        $guideStates->makeHidden( [ 'image','currency_symbol','iso_alpha2_code','image','iso_alpha3_code','calling_code','status','created_at','updated_at','title','description' ] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_states_success',
            'states' => $guideStates,
        ] );

    }

    // member
    public static function getBranches( $request )
    {
        $guideBranches = GuideBranch::where('status', 10)
        ->when( $request->state, function ( $query ) use ( $request ) {
            $query->whereHas( 'state', function ( $q ) use ( $request ) {
                $q->where( 'id', $request->state )
                  ->orWhere( 'name', $request->state );
            });
        })->when( $request->title, function ( $query ) use ( $request ) {
            $query->where( 'title', $request->title );
        })->get();

        $guideBranches->makeHidden( [ 'image','currency_symbol','iso_alpha2_code','image','iso_alpha3_code','calling_code','status','created_at','updated_at','title','description' ] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_branches_success',
            'states' => $guideBranches,
        ] );

    }

    // member
    public static function getInstallationGuides( $request )
    {
        $guideInstallations = Guide::where('status', 10)
        ->where( 'file_type', 2 )
        ->orderBy( 'sequence' )
        ->when( $request->country, function ( $query ) use ( $request ) {
            $query->whereHas( 'country', function ( $q ) use ( $request ) {
                $q->where( 'id', $request->country )
                  ->orWhere( 'name', $request->country );
            });
        })->get();

        $guideInstallations->makeHidden( [ 'image','currency_symbol','iso_alpha2_code','image','iso_alpha3_code','calling_code','status','created_at','updated_at','title','description' ] );
        $guideInstallations->append( [ 'filePath' ] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_installation_guides_success',
            'installtion_guides' => $guideInstallations,
        ] );

    }

    // member
    public static function getProductBrochures( $request )
    {
        $guideProductBrochures = Guide::where('status', 10)
        ->where( 'file_type', 1 )
        ->orderBy( 'sequence' )
        ->when( $request->country, function ( $query ) use ( $request ) {
            $query->whereHas( 'country', function ( $q ) use ( $request ) {
                $q->where( 'id', $request->country )
                  ->orWhere( 'name', $request->country );
            });
        })->get();

        $guideProductBrochures->makeHidden( [ 'image','currency_symbol','iso_alpha2_code','image','iso_alpha3_code','calling_code','status','created_at','updated_at','title','description' ] );
        $guideProductBrochures->append( [ 'filePath' ] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_product_brochures_success',
            'product_brochures' => $guideProductBrochures,
        ] );

    }

    // member
    public static function getVideos( $request )
    {
        $guideVideos = Guide::where('status', 10)
        ->where( 'file_type', 3 )
        ->orderBy( 'sequence' )
        ->when( $request->country, function ( $query ) use ( $request ) {
            $query->whereHas( 'country', function ( $q ) use ( $request ) {
                $q->where( 'id', $request->country )
                  ->orWhere( 'name', $request->country );
            });
        })->get();

        $guideVideos->makeHidden( [ 'image','currency_symbol','iso_alpha2_code','image','iso_alpha3_code','calling_code','status','created_at','updated_at','title','description' ] );

        $guideVideos->append( [ 'filePath' ] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_videos_success',
            'videos' => $guideVideos,
        ] );

    } 
    
    public static function getGuideAndResources( $request )
    {
        $guideVideos = Guide::where('status', 10)
        ->when( $request->file_type, function ( $query ) use ( $request ) {
            $query->where( 'file_type', $request->file_type );
        })
        ->when( $request->country, function ( $query ) use ( $request ) {
            $query->whereHas( 'country', function ( $q ) use ( $request ) {
                $q->where( 'id', $request->country )
                  ->orWhere( 'name', $request->country );
            });
        })
        ->orderBy( 'sequence' )
        ->get();

        $guideVideos->makeHidden( [ 'image','currency_symbol','iso_alpha2_code','image','iso_alpha3_code','calling_code','status','created_at','updated_at','title','description' ] );

        $guideVideos->append( [ 'filePath' ] );

        return response()->json( [
            'message' => '',
            'message_key' => 'get_guide_and_resources_success',
            'guides_and_resources' => $guideVideos,
        ] );

    }

}