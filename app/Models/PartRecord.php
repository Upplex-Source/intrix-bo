<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class PartRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'supplier_id',
        'vehicle_id',
        'part_id',
        'reference',
        'unit_price',
        'part_date',
    ];

    public function supplier() {
        return $this->belongsTo( Supplier::class, 'supplier_id' );
    }

    public function vehicle() {
        return $this->belongsTo( Vehicle::class, 'vehicle_id' );
    }

    public function part() {
        return $this->belongsTo( Part::class, 'part_id' );
    }

    public function getLocalPartDateAttribute() {
        return Carbon::createFromFormat( 'Y-m-d H:i:s', $this->attributes['part_date'] )->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'supplier_id',
        'vehicle_id',
        'part_id',
        'reference',
        'unit_price',
        'part_date',
    ];

    protected static $logName = 'part_records';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} part record";
    }
}
