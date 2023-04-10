<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Superadmin
        $superAdminRole = DB::table( 'roles' )->insertGetId( [
            'name' => 'super_admin',
            'guard_name' => 'admin',
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $superAdmin = DB::table( 'administrators' )->insertGetId( [
            'name' => 'altasming',
            'email' => 'altas.x.junming@gmail.com',
            'password' => Hash::make( 'altasming1234' ),
            'fullname' => 'Altas Xiao',
            'role' => $superAdminRole,
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        DB::table( 'model_has_roles' )->insert( [
            'role_id' => $superAdminRole,
            'model_type' => 'App\Models\Administrator',
            'model_id' => $superAdmin,
        ] );
    }
}
