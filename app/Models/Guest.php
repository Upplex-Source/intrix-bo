<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Guest extends Model
{
    use HasFactory, LogsActivity, HasApiTokens;

    protected $hidden = ['password'];

    protected $fillable = [
        'fullname',
        'email',
        'session_key',
        'password',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'calling_code',
        'status',
        'phone_number',  
        'ip_address',
        'user_agent',
        'last_visit',
        'country',
        'company_name',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'fullname',
        'email',
        'session_key',
        'password',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'calling_code',
        'status',
        'phone_number',  
        'ip_address',
        'user_agent',
        'last_visit',
        'country',
        'company_name',
    ];

    protected static $logName = 'guests';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} guest";
    }
}
