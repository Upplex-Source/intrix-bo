<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TyreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $supplier1 = DB::table( 'suppliers' )->insertGetId( [
            'name' => 'Expert Concept Enterprise Sdn Bhd',
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $supplier2 = DB::table( 'suppliers' )->insertGetId( [
            'name' => 'AF Marketing Sdn Bhd',
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $tyre1 = DB::table( 'tyres' )->insertGetId( [
            'supplier_id' => $supplier1,
            'code' => '101235',
            'name' => 'MAXXIS CST MT2 6PR 108/104Q OOL 245/75R16',
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $tyre2 = DB::table( 'tyres' )->insertGetId( [
            'supplier_id' => $supplier2,
            'code' => '101260',
            'name' => 'KUNLUN KT852 295/80R22.5 18PR BUNGA HALUS',
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );
    }
}
