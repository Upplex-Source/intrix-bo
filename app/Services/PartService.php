<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Part,
};

use Helper;

use Carbon\Carbon;

class PartService
{
    public static function allParts( $request ) {

        $part = Part::with( [
            'vendor',
        ] )->select( 'parts.*' );

        $part->leftJoin( 'vendors', 'vendors.id', '=', 'parts.vendor_id' );

        $partObject = self::filterPartRecord( $request, $part );
        $part = $partObject['model'];
        $filter = $partObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 1:
                    $part->orderBy( 'parts.created_at', $dir );
                    break;
                case 4:
                    $part->orderBy( 'parts.status', $dir );
                    break;
            }
        }

        $partCount = $part->count();

        $limit = $request->length;
        $offset = $request->start;

        $parts = $part->skip( $offset )->take( $limit )->get();

        if ( $parts ) {
            $parts->append( [
                'encrypted_id',
            ] );
        }

        $totalRecord = Part::count();

        $data = [
            'parts' => $parts,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $partCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filterPartRecord( $request, $model ) {

        $filter = false;

        if ( !empty( $request->vendor ) ) {
            $model->where( 'vendors.name', 'LIKE', '%' . $request->vendor . '%' );
            $filter = true;
        }

        if ( !empty( $request->name ) ) {
            $model->where( 'parts.name', 'LIKE', '%' . $request->name . '%' );
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'parts.status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'parts.name', 'LIKE', '%' . $request->custom_search . '%' );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function onePart( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $part = Part::with( [
            'vendor',
        ] )->find( $request->id );

        if( $part ) {
            $part->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( $part );
    }

    public static function createPart( $request ) {

        $validator = Validator::make( $request->all(), [
            'vendor' => [ 'required', 'exists:vendors,id' ],
            'name' => [ 'required' ],
        ] );

        $attributeName = [
            'vendor' => __( 'part.vendor' ),
            'name' => __( 'part.name' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            Part::create( [
                'vendor_id' => $request->vendor,
                'name' => $request->name,
            ] );

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.parts' ) ) ] ),
        ] );
    }

    public static function updatePart( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $validator = Validator::make( $request->all(), [
            'vendor' => [ 'required', 'exists:vendors,id' ],
            'name' => [ 'required' ],
        ] );

        $attributeName = [
            'vendor' => __( 'part.vendor' ),
            'name' => __( 'part.name' ),
        ];

        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }

        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {

            $updatePart = Part::find( $request->id );
            $updatePart->vendor_id = $request->vendor;
            $updatePart->name = $request->name;
            $updatePart->save();

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.parts' ) ) ] ),
        ] );
    }

    public static function updatePartStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $updatePart = Part::find( $request->id );
        $updatePart->status = $request->status;
        $updatePart->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.vendors' ) ) ] ),
        ] );
    }
}