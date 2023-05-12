<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

class VehicleInspection extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'created_by',
        'updated_by',
        'vehicle_id',
        'meter_reading_outgoing',
        'meter_reading_incoming',
        'fuel_level_outgoing',
        'fuel_level_incoming',
        'petrol_card',
        'petrol_card_remark',
        'light_indicator',
        'light_indicator_remark',
        'inverter_cigrette',
        'inverter_cigrette_remark',
        'car_mat_seat_cover',
        'car_mat_seat_cover_remark',
        'interior_damage',
        'interior_damage_remark',
        'interior_light',
        'interior_light_remark',
        'outgoing_time',
        'incoming_time',
    ];

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'created_by',
        'updated_by',
        'vehicle_id',
        'meter_reading_outgoing',
        'meter_reading_incoming',
        'fuel_level_outgoing',
        'fuel_level_incoming',
        'petrol_card',
        'petrol_card_remark',
        'light_indicator',
        'light_indicator_remark',
        'inverter_cigrette',
        'inverter_cigrette_remark',
        'car_mat_seat_cover',
        'car_mat_seat_cover_remark',
        'interior_damage',
        'interior_damage_remark',
        'interior_light',
        'interior_light_remark',
        'outgoing_time',
        'incoming_time',
    ];

    protected static $logName = 'vehicle_inspections';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} vehicle inspection";
    }
}
