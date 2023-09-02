<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    PartService,
};

class PartController extends Controller
{
    public function allParts( Request $request ) {

        return PartService::allParts( $request );
    }
}
