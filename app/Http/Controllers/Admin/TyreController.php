<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    TyreService,
};

class TyreController extends Controller
{
    public function allTyres( Request $request ) {

        return TyreService::allTyres( $request );
    }
}
