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

        $part = Part::select( 'parts.*' );

        $partObject = self::filterPartRecord( $request, $part );
        $part = $partObject['model'];
        $filter = $partObject['filter'];

        $partCount = $part->count();

        $limit = $request->length;
        $offset = $request->start;

        $parts = $part->skip( $offset )->take( $limit )->get();

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

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'parts.name', 'LIKE', '%' . $request->custom_search . '%' );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }
}