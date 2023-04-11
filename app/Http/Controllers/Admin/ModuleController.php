<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Services\{
    ModuleService,
};

use Spatie\Permission\Models\{
    Permission,
};

use App\Models\{
    Module,
};

use Helper;

class ModuleController extends Controller
{
    public function index( Request $request ) {

        $this->data['header']['title'] = __( 'template.modules' );
        $this->data['content'] = 'admin.module.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.modules' ),
                'class' => 'active',
            ],
        ];

        foreach ( Route::getRoutes() as $route ) {
            
            $routeName = $route->getName();
            if ( str_contains( $route->getName(), 'admin.module_parent.' ) ) {
                $routeName = str_replace( 'admin.module_parent.', '', $routeName );
                $routeName = str_replace( '.index', '', $routeName );
                $moduleName = \Str::plural( $routeName );

                $module = Module::firstOrCreate( [
                    'name' => $moduleName,
                    'guard_name' => 'admin',
                ] );

                if ( $module ) {

                    foreach ( Helper::moduleActions() as $action ) {
                        Permission::where( 'name', $action . ' ' . $module->name )->where( 'guard_name', $module->guard_name )->firstOr( function() use ( $action, $module ) {
                            $createPermission = Permission::create( [
                                'name' => $action . ' ' . $module->name,
                                'guard_name' => $module->guard_name,
                            ] );

                            $updatePermission = Permission::find( $createPermission->id );
                            $updatePermission->module_id = $module->id;
                            $updatePermission->save();

                            return $createPermission;
                        } );
                    }
                }
            }
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();   

        return view( 'admin.main' )->with( $this->data );
    }

    public function allModules( Request $request ) {

        return ModuleService::allModules( $request );
    }
}
