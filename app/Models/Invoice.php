<?php

namespace App\Models;

use Helper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'company_id',
        'customer_id',
        'do_number',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function customer() {
        return $this->belongsTo( Customer::class, 'customer_id' );
    }

    public function company() {
        return $this->belongsTo( Company::class, 'company_id' );
    }

}
