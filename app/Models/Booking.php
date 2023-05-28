<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'reference',
        'customer_name',
        'invoice_number',
        'invoice_date',
        'delivery_order_number',
        'delivery_order_date',
        'pickup_address',
        'dropoff_address',
        'pickup_date',
        'dropoff_date',
        'company_id',
        'customer_type',
        'customer_quantity',
        'customer_unit_of_measurement',
        'customer_rate',
        'customer_total_amount',
        'customer_remarks',
        'driver_id',
        'driver_quantity',
        'driver_unit_of_measurement',
        'driver_rate',
        'driver_total_amount',
        'driver_percentage',
        'driver_final_amount',
        'internal_status',
        'status',
    ];

    public function vehicle() {
        return $this->belongsTo( Vehicle::class, 'vehicle_id' );
    }

    public function company() {
        return $this->belongsTo( Company::class, 'company_id' );
    }

    public function driver() {
        return $this->belongsTo( Employee::class, 'driver_id' );
    }

    public function getDisplayPickupAddressAttribute() {
        return json_decode( $this->attributes['pickup_address'] );
    }

    public function getDisplayDropOffAddressAttribute() {
        return json_decode( $this->attributes['dropoff_address'] );
    }

    public function getPickupDateAttribute() {
        return Carbon::createFromFormat( 'Y-m-d H:i:s', $this->attributes['pickup_date'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    public function getDropoffDateAttribute() {
        return Carbon::createFromFormat( 'Y-m-d H:i:s', $this->attributes['dropoff_date'] )->setTimezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'vehicle_id',
        'reference',
        'customer_name',
        'invoice_number',
        'invoice_date',
        'delivery_order_number',
        'delivery_order_date',
        'pickup_address',
        'dropoff_address',
        'pickup_date',
        'dropoff_date',
        'company_id',
        'customer_type',
        'customer_quantity',
        'customer_unit_of_measurement',
        'customer_rate',
        'customer_total_amount',
        'customer_remarks',
        'driver_id',
        'driver_quantity',
        'driver_unit_of_measurement',
        'driver_rate',
        'driver_total_amount',
        'driver_percentage',
        'driver_final_amount',
        'internal_status',
        'status',
    ];

    protected static $logName = 'bookings';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} booking";
    }
}
