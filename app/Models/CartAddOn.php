<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class CartAddOn extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cart_id',
        'add_on_id',
        'user_id',
        'total_price',
        'discount',
        'status',
        'quantity',
        'payment_plan',
    ];

    public function cart() {
        return $this->belongsTo( Cart::class, 'cart_id' );
    }

    public function addOn() {
        return $this->belongsTo( ProductAddOn::class, 'add_on_id' );
    }

    public function productVariant() {
        return $this->belongsTo( ProductVariant::class, 'product_variant_id' );
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

    public function getFroyosMetasAttribute()
    {
        $froyoIds = json_decode($this->attributes['froyos'], true);
    
        if (empty($froyoIds)) {
            return collect(); // Return an empty collection if no froyo IDs
        }

        $froyo = Froyo::whereIn('id', $froyoIds)
        ->select('id', 'title', 'price', 'image') // Select only specific fields
        ->get();

        $froyo->append('image_path');
    
        return $froyo;
    }

    public function getSyrupsMetasAttribute()
    {
        $syrupIds = json_decode($this->attributes['syrups'], true);
    
        if (empty($syrupIds)) {
            return collect(); // Return an empty collection if no syrup IDs
        }
    
        $syrup = Syrup::whereIn('id', $syrupIds)
        ->select('id', 'title', 'price', 'image') // Select only specific fields
        ->get();

        $syrup->append('image_path');
    
        return $syrup;
    }

    public function getToppingsMetasAttribute()
    {
        $toppingIds = json_decode($this->attributes['toppings'], true);
    
        if (empty($toppingIds)) {
            return collect(); // Return an empty collection if no topping IDs
        }
    
        $topping = Topping::whereIn('id', $toppingIds)
        ->select('id', 'title', 'price', 'image') // Select only specific fields
        ->get();

        $topping->append('image_path');
    
        return $topping;
    }

    public function getProductsMetasAttribute()
    {
        return Product::whereIn('id', $this->attributes['products'])
        ->select('id', 'title', 'price')
        ->get();
    }

    public function cartAddOns() {
        return $this->hasMany( CartAddOn::class, 'cart_id' );
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'cart_id',
        'add_on_id',
        'user_id',
        'total_price',
        'discount',
        'status',
        'quantity',
        'payment_plan',
    ];

    protected static $logName = 'cart_add_ons';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} cart add on";
    }
}
