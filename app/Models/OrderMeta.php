<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class OrderMeta extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_bundle_id',
        'froyo_id',
        'syrup_id',
        'topping_id',
        'froyo_quantity',
        'syrup_quantity',
        'topping_quantity',
        'status',
        'froyos',
        'syrups',
        'toppings',
    ];

    public function order() {
        return $this->belongsTo( Order::class, 'order_id' );
    }

    public function product() {
        return $this->belongsTo( Product::class, 'product_id' );
    }

    public function productBundle() {
        return $this->belongsTo( ProductBundle::class, 'product_bundle_id' );
    }

    public function froyo()
    {
        return $this->belongsTo(Froyo::class, 'froyo_id');
    }

    public function syrup()
    {
        return $this->belongsTo(Syrup::class, 'syrup_id');
    }

    public function topping()
    {
        return $this->belongsTo(Topping::class, 'topping_id');
    }

    public function orderMetas() {
        return $this->hasMany( OrderMeta::class, 'order_id' );
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'order_id',
        'product_id',
        'product_bundle_id',
        'froyo_id',
        'syrup_id',
        'topping_id',
        'froyo_quantity',
        'syrup_quantity',
        'topping_quantity',
        'status',
        'froyos',
        'syrups',
        'toppings',
    ];

    protected static $logName = 'order_metas';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} order meta";
    }
}
