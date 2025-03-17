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
        'product_id',
        'order_transaction_id',
        'product_variant_id',
        'user_id',
        'outlet_id',
        'total_price',
        'discount',
        'reference',
        'payment_method',
        'status',
        'voucher_id',
        'taxes',
        'payment_attempt',
        'payment_url',
        'user_bundle_id',
        'tax',
        'subtotal',
        'additional_charges',
        'guest_id',
        'payment_plan',
        'remarks',
        'country',
        'company_name',
        'fullname',
        'email',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'calling_code',
        'phone_number',  
        'add_on_id',
        'free_gift_id',
    ];

    protected $hidden = [
        'taxes'
    ];

    public function getOrderStatusLabelAttribute()
    {
        $discountTypes = [
            '1' => __( 'datatables.order_placed' ),
            '2' => __( 'datatables.order_pending_payment' ),
            '3' => __( 'datatables.order_paid' ),
            '10' => __( 'datatables.order_completed' ),
            '20' => __( 'datatables.order_canceled' ),
        ];

        return $discountTypes[$this->attributes['status']] ?? null;
    }

    public function guest() {
        return $this->belongsTo( Guest::class, 'guest_id' );
    }

    public function productVariant() {
        return $this->belongsTo( ProductVariant::class, 'product_variant_id' );
    }

    public function voucher() {
        return $this->belongsTo( Voucher::class, 'voucher_id' );
    }

    public function product() {
        return $this->belongsTo( Product::class, 'product_id' );
    }

    public function userBundle() {
        return $this->belongsTo( UserBundle::class, 'user_bundle_id' );
    }

    public function productBundle() {
        return $this->belongsTo( ProductBundle::class, 'product_bundle_id' );
    }

    public function user() {
        return $this->belongsTo( User::class, 'user_id' );
    }

    public function outlet() {
        return $this->belongsTo( Outlet::class, 'outlet_id' );
    }

    public function vendingMachine() {
        return $this->belongsTo( VendingMachine::class, 'vending_machine_id' );
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
        'product_id',
        'order_transaction_id',
        'product_variant_id',
        'user_id',
        'outlet_id',
        'total_price',
        'discount',
        'reference',
        'payment_method',
        'status',
        'voucher_id',
        'taxes',
        'payment_attempt',
        'payment_url',
        'user_bundle_id',
        'tax',
        'subtotal',
        'additional_charges',
        'guest_id',
        'payment_plan',
        'remarks',
        'country',
        'company_name',
        'fullname',
        'email',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'calling_code',
        'phone_number',  
        'add_on_id',
        'free_gift_id',
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
