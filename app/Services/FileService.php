<?php

namespace App\Services;

use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    FileManager,
};

use Helper;

use Carbon\Carbon;

class FileService
{
    public static function upload( $request ) {

        $createFile = FileManager::create( [
            'name' => $request->file( 'file' )->getClientOriginalName(),
            'file' => $request->file( 'file' )->store( 'file-managers', [ 'disk' => 'public' ] ),
            'type' => $request->file( 'file' )->getClientOriginalExtension() == 'pdf' ? 1 : 2,
        ] );

        return response()->json( [
            'status' => 200,
            'data' => $createFile,
        ] );
    }
}