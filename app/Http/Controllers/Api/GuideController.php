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
    GuideService,
    UserService,
};

use App\Models\{
    User,
};

use Helper;

class GuideController extends Controller {

    /**
     * 1. Get Countries
     * 
     * @group Where-To-Find-Us API
     * 
     * @bodyParam country string The resources' country to be filtered ( id or name ). Example: Malaysia
     * 
     */ 
    public function getCountries( Request $request ) {
        
        return GuideService::getCountries( $request );
    }

    /**
     * 2. Get States
     * 
     * @group Where-To-Find-Us API
     * 
     * @bodyParam country string The resources' country to be filtered ( id or name ). Example: Malaysia
     * 
     */ 
    public function getStates( Request $request ) {
        
        return GuideService::getStates( $request );
    }

    /**
     * 3. Get Branches
     * 
     * @group Where-To-Find-Us API
     * 
     * @bodyParam state string The state of the Branches to be filtered ( id or name ). Example: Kuala Lumpur
     * @bodyParam title string The title of the Branches to be filtered. Example: B
     * 
     */ 
    public function getBranches( Request $request ) {
        
        return GuideService::getBranches( $request );
    }

    /**
     * 4. Get Guide and resources
     * 
     * <strong>file_type</strong></br>
     * 1: product brochures<br>
     * 2: installation guide<br>
     * 3: videos<br>
     * 
     * @group Where-To-Find-Us API
     * 
     * @bodyParam country string The resources' country to be filtered ( id or name ). Example: Malaysia
     * @bodyParam file_type interger The resources' file_type to be filtered . Example: 1
     * 
     * 
     */ 
    public function getGuideAndResources( Request $request ) {
        
        return GuideService::getGuideAndResources( $request );
    }

    /**
     * 5. Get Product Brochures ( sorted )
     * 
     * @group Where-To-Find-Us API
     * 
     * @bodyParam country string The resources' country to be filtered ( id or name ). Example: Malaysia
     * 
     * 
     */ 
    public function getProductBrochures( Request $request ) {
        
        return GuideService::getProductBrochures( $request );
    }

    /**
     * 6. Get Installation Guide ( sorted )
     * 
     * @group Where-To-Find-Us API
     * 
     * 
     * @bodyParam country string The resources' country to be filtered ( id or name ). Example: Malaysia
     * 
     */ 
    public function getInstallationGuides( Request $request ) {
        
        return GuideService::getInstallationGuides( $request );
    }

    /**
     * 7. Get Videos ( sorted )
     * 
     * @group Where-To-Find-Us API
     * 
     * 
     * @bodyParam country string The resources' country to be filtered ( id or name ). Example: Malaysia
     * 
     */ 
    public function getVideos( Request $request ) {
        
        return GuideService::getVideos( $request );
    }
}