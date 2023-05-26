<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class Vehicle extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'employee_id',
        'photo',
        'name',
        'license_plate',
        'in_service',
        'type',
        'status',
    ];

    public function employee() {
        return $this->belongsTo( Employee::class, 'employee_id' );
    }

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
        'employee_id',
        'photo',
        'name',
        'license_plate',
        'in_service',
        'type',
        'status',
    ];

    protected static $logName = 'vehicles';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} vehicle";
    }
}
