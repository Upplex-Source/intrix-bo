<?php

namespace App\Services;

use App\Models\{
    Company,
};

class CompanyService
{
    public static function get() {

        $companies = Company::where( 'status', 10 )->get()->toArray();

        return $companies;
    }
}