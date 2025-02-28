<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Crypt,
    Hash,
    Http,
    Storage
};

use App\Services\{
    BlogService,
    UserService,
};

use App\Models\{
    User,
};

use Helper;

class BlogController extends Controller {

    /**
     * 1. Get all blogs
     * 
     * <strong>type</strong></br>
     * 2: insights<br>
     * 3: partners<br>
     * 
     * @group Blog API
     * 
     * @bodyParam length integer required The length of the table. Example: 10
     * @bodyParam start integer required The start of the record of the table. Example: 0
     * @bodyParam type integer The type of the filter. Example: 1
     * @bodyParam category string The category of the blog ( id, title ) . Example: test
     * @bodyParam created_date string The date of the filter. Example: 2024-09-25 to 2024-09-27
     * 
     */ 
    public function allBlogs( Request $request ) {
        
        return BlogService::_allBlogs( $request );
    }

    /**
     * 2. Get one blog detail
     * 
     * @group Blog API
     * @bodyParam id string required The encrypted id the blog. Example: 1
     * 
     */ 
    public function oneBlog( Request $request ) {
        
        return BlogService::_oneBlog( $request );
    }

    /**
     * 3. Get one blog detail By slug
     * 
     * @group Blog API
     * 
     * @bodyParam slug string required The slug the blog. Example: 1
     * 
     */ 
    public function oneBlogBySlug( Request $request ) {
        
        return BlogService::oneBlogBySlug( $request );
    }

    /**
     * 4. Get Blog Categories
     * 
     * @group Blog API
     * 
     * 
     */ 
    public function getBlogCategories( Request $request ) {
        
        return BlogService::getBlogCategories( $request );
    }
}