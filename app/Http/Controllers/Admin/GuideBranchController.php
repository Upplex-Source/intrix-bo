<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    GuideService,
};

use App\Models\{
    Guide,
    GuideBranch,
    GuideCountry,
};

class GuideBranchController extends Controller
{

    public function updateOrder( Request $request ) {
        foreach ( $request->order as $index => $id ) {
            Guide::where( 'id', $id )->update( [ 'sequence' => $index ] );
        }
        return response()->json( [ 'success' => true ] );
    }
    
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.guide_branches' );
        $this->data['content'] = 'admin.guide_branch.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.guide_branches' ),
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

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guide_branches' ) ) ] );
        $this->data['content'] = 'admin.guide_branch.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guide_branch.index' ),
                'text' => __( 'template.guide_branches' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.guide_branches' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'guide_branch.percentage' ),
            '2' => __( 'guide_branch.fixed_amount' ),
            '3' => __( 'guide_branch.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guide_branches' ) ) ] );
        $this->data['content'] = 'admin.guide_branch.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.module_parent.guide_branch.index' ),
                'text' => __( 'template.guide_branches' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.guide_branches' ) ) ] ),
                'class' => 'active',
            ],
        ];

        $this->data['data']['discount_types'] = [
            '1' => __( 'guide_branch.percentage' ),
            '2' => __( 'guide_branch.fixed_amount' ),
            '3' => __( 'guide_branch.free_cup' ),
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function allGuideBranches( Request $request ) {

        return GuideService::allGuideBranches( $request );
    }

    public function oneGuideBranch( Request $request ) {

        return GuideService::oneGuideBranch( $request );
    }

    public function createGuideBranch( Request $request ) {

        return GuideService::createGuideBranch( $request );
    }

    public function updateGuideBranch( Request $request ) {

        return GuideService::updateGuideBranch( $request );
    }

    public function updateGuideBranchStatus( Request $request ) {

        return GuideService::updateGuideBranchStatus( $request );
    }

    public function removeGuideBranchGalleryImage( Request $request ) {

        return GuideService::removeGuideBranchGalleryImage( $request );
    }

    public function ckeUpload( Request $request ) {

        return GuideService::ckeUpload( $request );
    }

    public function deleteGuideBranch( Request $request ) {

        return GuideService::deleteGuideBranch( $request );
    }
    
}
