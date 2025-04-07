<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    GuideService,
};

use App\Models\{
    Guide,
    GuideCountry,
    GuideBranch,
};

class GuideCountryController extends Controller
{

    public function updateOrder( Request $request ) {
        foreach ( $request->order as $index => $id ) {
            Guide::where( 'id', $id )->update( [ 'sequence' => $index ] );
        }
        return response()->json( [ 'success' => true ] );
    }
    
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.guide_countries' );
        $this->data['content'] = 'admin.guide_country.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.guide_countries' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guide_countries' ) ) ] );
        $this->data['content'] = 'admin.guide_country.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guide_country.index' ),
                'text' => __( 'template.guide_countries' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guide_countries' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'guide_country.percentage' ),
            '2' => __( 'guide_country.fixed_amount' ),
            '3' => __( 'guide_country.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guide_countries' ) ) ] );
        $this->data['content'] = 'admin.guide_country.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guide_country.index' ),
                'text' => __( 'template.guide_countries' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guide_countries' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'guide_country.percentage' ),
            '2' => __( 'guide_country.fixed_amount' ),
            '3' => __( 'guide_country.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allGuideCountries( Request $request ) {

        return GuideService::allGuideCountries( $request );
    }

    public function oneGuideCountry( Request $request ) {

        return GuideService::oneGuideCountry( $request );
    }

    public function createGuideCountry( Request $request ) {

        return GuideService::createGuideCountry( $request );
    }

    public function updateGuideCountry( Request $request ) {

        return GuideService::updateGuideCountry( $request );
    }

    public function updateGuideCountryStatus( Request $request ) {

        return GuideService::updateGuideCountryStatus( $request );
    }

    public function removeGuideCountryGalleryImage( Request $request ) {

        return GuideService::removeGuideCountryGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return GuideService::ckeUpload( $request );
    }

    public function deleteGuideCountry( Request $request ) {

        return GuideService::deleteGuideCountry( $request );
    }
    
}
