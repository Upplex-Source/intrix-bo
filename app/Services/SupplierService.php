<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    Supplier,
};

use Helper;

use Carbon\Carbon;

class SupplierService
{
    public static function allSuppliers( $request ) {

        $supplier = Supplier::select( 'suppliers.*' );

        $supplierObject = self::filterSupplierRecord( $request, $supplier );
        $supplier = $supplierObject['model'];
        $filter = $supplierObject['filter'];

        $supplierCount = $supplier->count();

        $limit = $request->length;
        $offset = $request->start;

        $suppliers = $supplier->skip( $offset )->take( $limit )->get();

        $totalRecord = Supplier::count();

        $data = [
            'suppliers' => $suppliers,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $supplierCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
    }

    private static function filterSupplierRecord( $request, $model ) {

        $filter = false;

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'suppliers.name', 'LIKE', '%' . request( 'custom_search' ) . '%' );
        }

        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }
}