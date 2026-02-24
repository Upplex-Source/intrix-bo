<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    GuideService,
};

use App\Models\{
    Guide,
    GuideState,
    GuideCountry,
};

class GuideStateController extends Controller
{

    public function updateOrder( Request $request ) {
        foreach ( $request->order as $index => $id ) {
            Guide::where( 'id', $id )->update( [ 'sequence' => $index ] );
        }
        return response()->json( [ 'success' => true ] );
    }
    
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.guide_states' );
        $this->data['content'] = 'admin.guide_state.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.guide_states' ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
            '21' => __( 'datatables.expired' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guide_states' ) ) ] );
        $this->data['content'] = 'admin.guide_state.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guide_state.index' ),
                'text' => __( 'template.guide_states' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guide_states' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'guide_state.percentage' ),
            '2' => __( 'guide_state.fixed_amount' ),
            '3' => __( 'guide_state.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guide_states' ) ) ] );
        $this->data['content'] = 'admin.guide_state.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guide_state.index' ),
                'text' => __( 'template.guide_states' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guide_states' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'guide_state.percentage' ),
            '2' => __( 'guide_state.fixed_amount' ),
            '3' => __( 'guide_state.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allGuideStates( Request $request ) {

        return GuideService::allGuideStates( $request );
    }

    public function oneGuideState( Request $request ) {

        return GuideService::oneGuideState( $request );
    }

    public function createGuideState( Request $request ) {

        return GuideService::createGuideState( $request );
    }

    public function updateGuideState( Request $request ) {

        return GuideService::updateGuideState( $request );
    }

    public function updateGuideStateStatus( Request $request ) {

        return GuideService::updateGuideStateStatus( $request );
    }

    public function removeGuideStateGalleryImage( Request $request ) {

        return GuideService::removeGuideStateGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return GuideService::ckeUpload( $request );
    }

    public function deleteGuideState( Request $request ) {

        return GuideService::deleteGuideState( $request );
    }
    
}
