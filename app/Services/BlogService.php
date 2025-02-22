<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    Blog,
    BlogImage,
    BlogTag,
    BlogTransaction,
    BlogCategory,
};

use Helper;

use Carbon\Carbon;

class BlogService
{
    public static function allBlogs( $request ) {

        $blog = Blog::with( [
            'images',
            'tag',
        ] )->select( 'blogs.*' );

        $filterObject = self::filterBlog( $request, $blog );
        $blog = $filterObject['model'];
        $filter = $filterObject['filter'];

        $blog->orderBy( 'blogs.created_at', 'DESC' );

        $blogCount = $blog->count();

        $limit = $request->length;
        $offset = $request->start;

        $blogs = $blog->skip( $offset )->take( $limit )->get();

        $blog = Blog::select(
            DB::raw( 'COUNT(blogs.id) as total'
        ) );

        $filterObject = self::filterBlog( $request, $blog );
        $blog = $filterObject['model'];
        $filter = $filterObject['filter'];

        $blog = $blog->first();

        $data = [
            'blogs' => $blogs,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $blogCount : $blog->total,
            'recordsTotal' => $filter ? Blog::count() : $blogCount,
        ];

        return $data;
    }

    private static function filterBlog( $request, $model ) {

        $filter = false;

        if (  !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'blogs.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );                
            } else {

                $dates = explode( '-', $request->created_date );
    
                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'blogs.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }

            $filter = true;
        }

        if( !empty( $request->type ) ){
            $model->where( 'blogs.type', $request->type );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    private static function filterBlogCategory( $request, $model ) {

        $filter = false;

        if (  !empty( $request->created_date ) ) {
            if ( str_contains( $request->created_date, 'to' ) ) {
                $dates = explode( ' to ', $request->created_date );

                $startDate = explode( '-', $dates[0] );
                $start = Carbon::create( $startDate[0], $startDate[1], $startDate[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                
                $endDate = explode( '-', $dates[1] );
                $end = Carbon::create( $endDate[0], $endDate[1], $endDate[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'blog_categories.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );                
            } else {

                $dates = explode( '-', $request->created_date );
    
                $start = Carbon::create( $dates[0], $dates[1], $dates[2], 0, 0, 0, 'Asia/Kuala_Lumpur' );
                $end = Carbon::create( $dates[0], $dates[1], $dates[2], 23, 59, 59, 'Asia/Kuala_Lumpur' );

                $model->whereBetween( 'blog_categories.created_at', [ date( 'Y-m-d H:i:s', $start->timestamp ), date( 'Y-m-d H:i:s', $end->timestamp ) ] );
            }

            $filter = true;
        }

        if( !empty( $request->title ) ){
            $model->where( 'blog_categories.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneBlog( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $blog = Blog::with( [
            'images',
            'tag',
            'author',
        ] )->find( $request->id );

        if( $blog ) {
            $blog->append( ['categories_metas'] );
        }

        return response()->json( $blog );
    }
    
    public static function createBlog( $request ) {

        $validator = Validator::make( $request->all(), [
            'main_title' => [ 'required' ],
            'subtitle' => [ 'nullable' ],
            'type' => [ 'required' ],
            'text' => [ 'nullable' ],
            'slug' => [ 'required', 'unique:blogs,slug', 'regex:/^[a-zA-Z0-9_-]+$/' ],
            'image' => [ 'required' ],
            'meta_title' => [ 'required' ],
            'meta_desc' => [ 'required' ],
            'publish_date' => [ 'required' ],
            'tag' => [ 'nullable' ],
            'gallery' => [ 'nullable' ],
            'min_of_read' => [ 'nullable' ],
        ] );

        $attributeName = [
            'main_title' => __( 'blog.main_title' ),
            'subtitle' => __( 'blog.subtitle' ),
            'type' => __( 'blog.type' ),
            'slug' => __( 'blog.slug' ),
            'text' => __( 'blog.content' ),
            'image' => __( 'blog.thumbnail' ),
            'meta_title' => __( 'blog.meta_title' ),
            'meta_desc' => __( 'blog.meta_desc' ),
            'publish_date' => __( 'blog.publish_date' ),
            'tag' => __( 'blog.tag' ),
            'gallery' => __( 'blog.gallery' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createBlog = Blog::create( [
                'author_id' => ($request->author && $request->author != 'null') ? $request->author : auth()->user()->id,
                'main_title' => $request->main_title,
                'subtitle' => $request->subtitle,
                'type' => $request->type,
                'slug' => $request->slug,
                'text' => $request->text,
                'image' => $request->image,
                'meta_title' => $request->meta_title,
                'meta_desc' => $request->meta_desc,
                'publish_date' => Carbon::parse($request->publish_date)
                ->subHours(8)
                ->timezone('Asia/Kuala_Lumpur')
                ->format('Y-m-d H:i:s'),
                'min_of_read' => $request->min_of_read,
                'categories' => json_encode($request->category),
                'status' => 10,
            ] );

            if( !empty( $request->tag ) ) {
                if ( str_contains( $request->tag, ',' ) ) {
                    $tags = explode( ',', $request->tag );
                    foreach( $tags as $tag ) {
                        $createBlogTag = BlogTag::create( [
                            'blog_id' => $createBlog->id,
                            'tag' => $tag,
                        ] );
                    }
                }else{
                    $createBlogTag = BlogTag::create( [
                        'blog_id' => $createBlog->id,
                        'tag' => $request->tag,
                    ] );
                }
            }

            if( !empty( $request->gallery ) ) {
                $galleries = json_decode( $request->gallery );
                foreach( $galleries as $gallery ) {
                    $createBlogImage = BlogImage::create( [
                        'blog_id' => $createBlog->id,
                        'path' => $gallery,
                    ] );
                }
            }
            
            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.blogs' ) ) ] ),
        ] );
    }

    public static function updateBlog( $request ) {

        $request->merge( [
            'id' => \Helper::decode( $request->id )
        ] );

        $validator = Validator::make( $request->all(), [
            'main_title' => [ 'required' ],
            'subtitle' => [ 'nullable' ],
            'type' => [ 'required' ],
            'text' => [ 'nullable' ],
            'slug' => [ 'required', 'unique:blogs,slug,' . $request->id , 'regex:/^[a-zA-Z0-9_-]+$/' ],
            'image' => [ 'required' ],
            'meta_title' => [ 'required' ],
            'meta_desc' => [ 'required' ],
            'publish_date' => [ 'required' ],
            'tag' => [ 'nullable' ],
            'gallery' => [ 'nullable' ],
        ] );

        $attributeName = [
            'main_title' => __( 'blog.main_title' ),
            'subtitle' => __( 'blog.subtitle' ),
            'type' => __( 'blog.type' ),
            'text' => __( 'blog.content' ),
            'slug' => __( 'blog.slug' ),
            'image' => __( 'blog.thumbnail' ),
            'meta_title' => __( 'blog.meta_title' ),
            'meta_desc' => __( 'blog.meta_desc' ),
            'publish_date' => __( 'blog.publish_date' ),
            'tag' => __( 'blog.tag' ),
            'gallery' => __( 'blog.gallery' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updateBlog = Blog::find( $request->id );
            $updateBlog->author_id = ($request->author && $request->author != 'null') ? $request->author : null;
            $updateBlog->main_title = $request->main_title;
            $updateBlog->subtitle = $request->subtitle;
            $updateBlog->slug = $request->slug;
            $updateBlog->type = $request->type;
            $updateBlog->text = $request->text;
            $updateBlog->image = $request->image;
            $updateBlog->meta_title = $request->meta_title;
            $updateBlog->meta_desc = $request->meta_desc;
            $updateBlog->publish_date = Carbon::parse($request->publish_date)
            ->subHours(8)
            ->timezone('Asia/Kuala_Lumpur')
            ->format('Y-m-d H:i:s');
            $updateBlog->min_of_read = $request->min_of_read;
            
            $updateBlog->categories = json_encode($request->category);
            $updateBlog->save();

            $deleteTag = BlogTag::where( 'blog_id', $updateBlog->id )
                ->get();
            foreach( $deleteTag as $delete ) {
                $delete->delete();
            }

            $deleteImage = BlogImage::where( 'blog_id', $updateBlog->id )
                ->get();
            foreach( $deleteImage as $delete ) {
                Storage::disk( 'public' )->delete( $delete->path );
                $delete->delete();
            }
            
            if( !empty( $request->tag ) ) {
                if ( str_contains( $request->tag, ',' ) ) {
                    $tags = explode( ',', $request->tag );
                    foreach( $tags as $tag ) {
                        $createBlogTag = BlogTag::create( [
                            'blog_id' => $updateBlog->id,
                            'tag' => $tag,
                        ] );
                    }
                }else{
                    $createBlogTag = BlogTag::create( [
                        'blog_id' => $updateBlog->id,
                        'tag' => $request->tag,
                    ] );
                }
            }

            if( !empty( $request->gallery ) ) {
                $galleries = json_decode( $request->gallery );
                foreach( $galleries as $gallery ) {
                    $createBlogImage = BlogImage::create( [
                        'blog_id' => $updateBlog->id,
                        'path' => $gallery,
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
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.blogs' ) ) ] ),
        ] );
    }

    public static function updateBlogStatus( $request ) {

        DB::beginTransaction();

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'status' => [ 'required', 'in:10,20' ],
        ] );
        
        $validator->validate();

        try {

            $updateBlog = Blog::lockForUpdate()->find( $request->id );
            $updateBlog->status = $updateBlog->status == 10 ? 20 : 10;
            $updateBlog->save();

            DB::commit();
            
            return response()->json( [
                'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.blogs' ) ) ] ),
            ] );

        } catch ( \Throwable $th ) {

            DB::rollBack();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine()
            ], 500 );
        }
    }

    // client
    public static function _allBlogs( $request ) {

        $now = Carbon::now('Asia/Kuala_Lumpur');

        $blog = Blog::with( [
            'images',
            'tag',
            'author',
        ] )->select( 'blogs.*' )
        ->whereDate( 'publish_date', '<=', $now )
        ->where( 'status', 10 );

        $filterObject = self::filterBlog( $request, $blog );
        $blog = $filterObject['model'];
        $filter = $filterObject['filter'];

        $blog->orderBy( 'publish_date', 'DESC' );

        $blogCount = $blog->count();

        $limit = $request->length ? $request->length : 10;
        $offset = $request->start ? $request->start : 0;

        $blogs = $blog->skip($offset)->take($limit)->get()->map(function ($blog) {
            if($blog->author){
                $blog->author->profile_pic_path = $blog->profile_pic
                ? asset('storage/' . $blog->profile_pic)
                : asset('admin/images/placeholder.png') . Helper::assetVersion();
            }

            $blog->append( ['category'] );

            return $blog;
        });

        if ($request->category) {
            $filteredBlogs = $blogs->filter(function ($blog) use ($request) {
                // Check if categories_metas exists and is not empty
                $categoriesMetas = $blog->categories_metas;
        
                if ($categoriesMetas && $categoriesMetas->isNotEmpty()) {
                    // Check if any category title matches the request category
                    $filteredCategories = $categoriesMetas->filter(function ($category) use ($request) {
                        return stripos($category->title, $request->category) !== false;
                    });
        
                    // If matching categories are found, return true to include this blog
                    if ($filteredCategories->isNotEmpty()) {
                        return true;  // Include this blog
                    }
                }
        
                // Exclude the blog if no matching category is found
                return false;  // Exclude this blog
            });
        }
        
        $blog = Blog::select(
            DB::raw( 'COUNT(blogs.id) as total'
        ) )->whereDate( 'publish_date', '<=', $now )
        ->where( 'status', 10 );

        $filterObject = self::filterBlog( $request, $blog );
        $blog = $filterObject['model'];
        $filter = $filterObject['filter'];

        $blog = $blog->first();

        $data = [
            'blogs' => $request->category ? $filteredBlogs : $blogs,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $blogCount : $blog->total,
            'recordsTotal' => $filter ? Blog::count() : $blogCount,
        ];

        return $data;
    }

    public static function _oneBlog( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $blog = Blog::with( [
            'images',
            'tag',
            'author',
        ] )->find( $request->id );

        if( $blog ) {
            $blog->append( ['category'] );
        }

        if( $blog->author ) {
            $blog->author->append( ['profile_pic_path'] );
        }

        return response()->json( $blog );
    }

    public static function getBlogCategories( $request ) {

        $blog = BlogCategory::where( 'status', 10 )->get();
        return response()->json( [ 'categories' => $blog] );
    }

    public static function oneBlogBySlug( $request ) {

        $blog = Blog::with( [
            'images',
            'tag',
            'author'
        ] )->where( 'slug', $request->slug )->first();

        if( $blog ) {
            $blog->append( ['category'] );
        }
        
        if( $blog->author ) {
            $blog->author->append( ['profile_pic_path'] );
        }

        return response()->json( $blog );
    }

    public static function createBlogCategoryQuick( $request ) {

        $validator = Validator::make( $request->all(), [
            'categoryTitle' => [ 'required' ],
        ] );

        $attributeName = [
            'categoryTitle' => __( 'blog.category' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $createBlogCategoryObject = [
                'title' => $request->categoryTitle,
                'status' => 10,
            ];

            $createBlogCategory = BlogCategory::create( $createBlogCategoryObject );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json([
            'status' => 'success',
            'category' => [
                'id' => $createBlogCategory->id,
                'title' => $createBlogCategory->title
            ]
        ]);
    }

    public static function allBlogCategories( $request ) {

        $blog = BlogCategory::select( 'blog_categories.*' );

        $filterObject = self::filterBlogCategory( $request, $blog );
        $blog = $filterObject['model'];
        $filter = $filterObject['filter'];

        $blog->orderBy( 'blog_categories.created_at', 'DESC' );

        $blogCount = $blog->count();

        $limit = $request->length;
        $offset = $request->start;

        $blogs = $blog->skip( $offset )->take( $limit )->get();

        $blog = BlogCategory::select(
            DB::raw( 'COUNT(blog_categories.id) as total'
        ) );

        $filterObject = self::filterBlogCategory( $request, $blog );
        $blog = $filterObject['model'];
        $filter = $filterObject['filter'];

        $blog = $blog->first();

        $data = [
            'blog_categories' => $blogs,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $blogCount : $blog->total,
            'recordsTotal' => $filter ? BlogCategory::count() : $blogCount,
        ];

        return $data;
    }

    public static function copyBlog( $request )
    {
        $request->merge( [
            'id' => \Helper::decode( $request->id ),
        ] );
    
        DB::beginTransaction();
    
        try {

            $originalBlog = Blog::findOrFail( $request->id );
    
            $newSlug = $originalBlog->slug . '-copy';
            while ( Blog::where( 'slug', $newSlug )->exists() ) {
                $newSlug .= rand( 100, 999 );
            }
    
            $newBlog = $originalBlog->replicate();
            $newBlog->slug = $newSlug;
            $newBlog->publish_date = now()->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
            $newBlog->save();
    
            // Duplicate tags
            $originalTags = BlogTag::where( 'blog_id', $originalBlog->id )->get();
            foreach ( $originalTags as $tag ) {
                BlogTag::create( [
                    'blog_id' => $newBlog->id,
                    'tag' => $tag->tag,
                ] );
            }
    
            // Duplicate images
            $originalImages = BlogImage::where( 'blog_id', $originalBlog->id )->get();
            foreach ( $originalImages as $image ) {
                BlogImage::create( [
                    'blog_id' => $newBlog->id,
                    'path' => $image->path,
                ] );
            }
    
            DB::commit();
    
        } catch ( \Throwable $th ) {
            DB::rollback();
            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }
    
        return response()->json( [
            'message' => __( 'template.x_duplicated', [ 'title' => Str::singular( __( 'template.blogs' ) ) ] ),
        ] );
    }
    
}