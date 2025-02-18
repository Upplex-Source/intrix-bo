<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    FileService,  
};

class FileController extends Controller
{
    public function upload( Request $request ) {

        return FileService::upload( $request );
    }

    public function ckeUpload( Request $request ) {

        return FileService::ckeUpload( $request );
    }

    public function blogUpload( Request $request ) {
        return FileService::blogUpload( $request );
    }

}
