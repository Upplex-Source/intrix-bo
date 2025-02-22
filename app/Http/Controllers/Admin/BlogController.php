<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index() {

        $this->data['header']['title'] = __( 'template.blogs' );
        $this->data['content'] = 'admin.blog.index';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.blogs' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.blogs' ),
        ];

        $type = \Helper::types();
        foreach( $type as $key => $t ) {
            $this->data['data']['types'][$key] = $t;
        }

        $this->data['data']['status'] = [
            '10' => __( 'datatables.activated' ),
            '20' => __( 'datatables.suspended' ),
        ];
        
        return view( 'admin.main' )->with( $this->data );
    }

    public function add() {

        $this->data['header']['title'] = __( 'template.blogs' );
        $this->data['content'] = 'admin.blog.add';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.blogs' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.blogs' ),
        ];

        $type = \Helper::types();
        $this->data['data']['types'] = $type;

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit() {

        $this->data['header']['title'] = __( 'template.blogs' );
        $this->data['content'] = 'admin.blog.edit';
        $this->data['breadcrumbs'] = [
            'enabled' => true,
            'main_title' => __( 'template.blogs' ),
            'title' => __( 'template.list' ),
            'mobile_title' => __( 'template.blogs' ),
        ];

        $type = \Helper::types();
        $this->data['data']['types'] = $type;

        return view( 'admin.main' )->with( $this->data );
    }

    public function allBlogs( Request $request ) {

        return BlogService::allBlogs( $request );
    }

    public function oneBlog( Request $request ) {
        return BlogService::oneBlog( $request );
    }

    public function createBlog( Request $request ) {

        return BlogService::createBlog( $request );
    }

    public function updateBlog( Request $request ) {

        return BlogService::updateBlog( $request );
    }

    public function updateBlogStatus( Request $request ) {
        
        return BlogService::updateBlogStatus( $request );
    }

    public function createBlogCategoryQuick( Request $request ) {

        return BlogService::createBlogCategoryQuick( $request );
    }

    public function allBlogCategories( Request $request ) {

        return BlogService::allBlogCategories( $request );
    }

    public function copyBlog( Request $request ) {
        
        return BlogService::copyBlog( $request );
    }
}
