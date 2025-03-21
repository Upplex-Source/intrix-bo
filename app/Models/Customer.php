<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'pic_name',
        'phone_number',
        'phone_number_2',
        'email',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'remarks',
        'status',
    ];

    public function getDisplayAddressAttribute() {

        $displayAddress = [
            'a1' => $this->attributes['address_1'],
            'a2' => $this->attributes['address_2'],
            'c' => $this->attributes['city'],
            'p' => $this->attributes['postcode'],
            's' => $this->attributes['state'],
        ];

        return $displayAddress;
        // return json_decode( $this->attributes['address'] );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name',
        'pic_name',
        'phone_number',
        'phone_number_2',
        'email',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'remarks',
        'status',
    ];

    protected static $logName = 'customers';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} customer";
    }
}
