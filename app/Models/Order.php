<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'owner_id',
        'farm_id',
        'buyer_id',
        'reference',
        'order_date',
        'grade',
        'weight',
        'rate',
        'subtotal',
        'total',
        'internal_status',
        'status',
    ];

    public function owner() {
        return $this->belongsTo( User::class, 'owner_id' );
    }

    public function farm() {
        return $this->belongsTo( Farm::class, 'farm_id' );
    }

    public function buyer() {
        return $this->belongsTo( Buyer::class, 'buyer_id' );
    }

    public function orderItems() {
        return $this->hasMany( OrderItem::class, 'order_id' )->orderBy( 'grade' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'owner_id',
        'farm_id',
        'buyer_id',
        'reference',
        'order_date',
        'grade',
        'weight',
        'rate',
        'subtotal',
        'total',
        'internal_status',
        'status',
    ];

    protected static $logName = 'orders';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} order";
    }
}
