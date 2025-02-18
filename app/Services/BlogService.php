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

    public static function oneBlog( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $blog = Blog::with( [
            'images',
            'tag',
            'author',
        ] )->find( $request->id );

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
                'author_id' => $request->author ?? auth()->user()->id,
                'main_title' => $request->main_title,
                'subtitle' => $request->subtitle,
                'type' => $request->type,
                'slug' => $request->slug,
                'text' => $request->text,
                'image' => $request->image,
                'meta_title' => $request->meta_title,
                'meta_desc' => $request->meta_desc,
                'publish_date' => $request->publish_date,
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
            $updateBlog->author_id = $request->author ?? auth()->user()->id;
            $updateBlog->main_title = $request->main_title;
            $updateBlog->subtitle = $request->subtitle;
            $updateBlog->slug = $request->slug;
            $updateBlog->type = $request->type;
            $updateBlog->text = $request->text;
            $updateBlog->image = $request->image;
            $updateBlog->meta_title = $request->meta_title;
            $updateBlog->meta_desc = $request->meta_desc;
            $updateBlog->publish_date = $request->publish_date;
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

        $now = Carbon::now();

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
            return $blog;
        });

        $blog = Blog::select(
            DB::raw( 'COUNT(blogs.id) as total'
        ) )->whereDate( 'publish_date', '<=', $now )
        ->where( 'status', 10 );

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

    public static function _oneBlog( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $blog = Blog::with( [
            'images',
            'tag',
            'author',
        ] )->find( $request->id );

        if( $blog->author ) {
            $blog->author->append( ['profile_pic_path'] );
        }

        return response()->json( $blog );
    }

    public static function oneBlogBySlug( $request ) {

        $blog = Blog::with( [
            'images',
            'tag',
        ] )->where( 'slug', $request->slug )->first();

        return response()->json( $blog );
    }
}