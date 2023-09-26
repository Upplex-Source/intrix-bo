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
        $vendor1 = DB::table( 'vendors' )->insertGetId( [
            'name' => 'Expert Concept Enterprise Sdn Bhd',
            'email' => 'ece@gmail.com',
            'phone_number' => '60322233332',
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $vendor2 = DB::table( 'vendors' )->insertGetId( [
            'name' => 'AF Marketing Sdn Bhd',
            'email' => 'af_marketing@gmail.com',
            'phone_number' => '60322233332',
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $tyre1 = DB::table( 'tyres' )->insertGetId( [
            'vendor_id' => $vendor1,
            'code' => '101235',
            'name' => 'MAXXIS CST MT2 6PR 108/104Q OOL 245/75R16',
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );

        $tyre2 = DB::table( 'tyres' )->insertGetId( [
            'vendor_id' => $vendor2,
            'code' => '101260',
            'name' => 'KUNLUN KT852 295/80R22.5 18PR BUNGA HALUS',
            'status' => 10,
            'created_at' => date( 'Y-m-d H:i:s' ),
            'updated_at' => date( 'Y-m-d H:i:s' ),
        ] );
    }
}
