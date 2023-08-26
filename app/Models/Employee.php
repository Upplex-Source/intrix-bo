<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo',
        'name',
        'email',
        'phone_number',
        'identification_number',
        'license_number',
        'remarks',
        'license_expiry_date',
        'status',
        'designation',
        'employment_type',
    ];

    public function getPathAttribute() {
        return $this->attributes['photo'] ? asset( 'storage/' . $this->attributes['photo'] ) : null;
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'photo',
        'name',
        'email',
        'phone_number',
        'identification_number',
        'license_number',
        'remarks',
        'license_expiry_date',
        'status',
        'designation',
        'employment_type',
    ];

    protected static $logName = 'employees';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} employee";
    }
}
