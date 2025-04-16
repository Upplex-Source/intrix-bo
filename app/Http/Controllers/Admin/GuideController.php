<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Services\{
    GuideService,
};

use App\Models\{
    Guide,
    GuideCountry,
};

use Helper;

class GuideController extends Controller
{

    public function updateOrder( Request $request ) {
        // Step 1: Group by country_id and file_type
        $grouped = collect( $request->order )
            ->map( function ( $id ) {
                return Guide::select( 'id', 'country_id', 'file_type' )->find( $id );
            } )
            ->filter()
            ->groupBy( fn ( $item ) => $item->country_id . '-' . $item->file_type );
    
        // Step 2: Loop through each group and update sequence
        foreach ( $grouped as $group ) {
            foreach ( $group as $index => $guide ) {
                Guide::where( 'id', $guide->id )->update( [ 'sequence' => $index ] );
            }
        }
    
        return response()->json( [ 'success' => true ] );
    }    
    
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.guides' );
        $this->data['content'] = 'admin.guide.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.guides' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'datatables.expired' ),
        ];
        
        $this->data['data']['guides'] = Guide::where( 'status', 10 )->orderBy( 'sequence' )->get();

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guides' ) ) ] );
        $this->data['content'] = 'admin.guide.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guide.index' ),
                'text' => __( 'template.guides' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guides' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'guide.percentage' ),
            '2' => __( 'guide.fixed_amount' ),
            '3' => __( 'guide.free_cup' ),
        ];

        switch ( Route::currentRouteName() ) {
            case 'admin.guide.addProductBrochures':
                $this->data['data']['type'] = __( 'guide.product_brochures' );
                $this->data['action'] = 'addProductBrochures';
                $type = 1;
                $this->data['data']['file_type'] = 1;
                break;
            case 'admin.guide.addInstallationGuides':
                $this->data['data']['type'] = __( 'guide.installation_guides' );
                $this->data['action'] = 'addInstallationGuides';
                $type = 2;
                $this->data['data']['file_type'] = 2;

                break;
            case 'admin.guide.addVideos':
                $this->data['data']['type'] = __( 'guide.videos' );
                $this->data['action'] = 'addVideos';
                $type = 3;
                $this->data['data']['file_type'] = 3;

                break;
            
            default:
                $this->data['data']['type'] = __( 'guide.product_brochures' );
                $this->data['data']['file_type'] = 1;
                $this->data['action'] = 'addProductBrochures';
                break;
        }

        $this->data['data']['country'] = $request->id ? GuideCountry::find( Helper::decode( $request->id ) ) : null;

        $guides = collect();

        if ( $request->id && $request->id != 'undefined') {
            $decodedId = Helper::decode( $request->id );
        
            if ( $decodedId ) { // Only proceed if decoding is successful
                $guides = Guide::where( 'status', 10 )
                    ->where( 'file_type', $type )
                    ->where( 'country_id', $decodedId )
                    ->orderBy( 'sequence' )
                    ->get();
            }
        }

        if ( $guides->isNotEmpty() ) {
            $guides->append( [
                'encrypted_id',
                'file_path',
                'thumbnail_path',
            ] );
        }

        $this->data['data']['guides'] = $guides;

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guides' ) ) ] );
        $this->data['content'] = 'admin.guide.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guide.index' ),
                'text' => __( 'template.guides' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guides' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'guide.percentage' ),
            '2' => __( 'guide.fixed_amount' ),
            '3' => __( 'guide.free_cup' ),
        ];

        $this->data['data']['guides'] = Guide::where( 'status', 10 )->orderBy( 'sequence' )->get();

        return view( 'admin.main' )->with( $this->data );
    }

    public function allGuides( Request $request ) {
        return GuideService::allGuides( $request );
    }

    public function oneGuide( Request $request ) {

        return GuideService::oneGuide( $request );
    }

    public function createGuide( Request $request ) {

        return GuideService::createGuide( $request );
    }

    public function updateGuide( Request $request ) {

        return GuideService::updateGuide( $request );
    }

    public function updateGuideStatus( Request $request ) {

        return GuideService::updateGuideStatus( $request );
    }

    public function removeGuideGalleryImage( Request $request ) {

        return GuideService::removeGuideGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return GuideService::ckeUpload( $request );
    }

    public function deleteGuide( Request $request ) {

        return GuideService::deleteGuide( $request );
    }
    
}
