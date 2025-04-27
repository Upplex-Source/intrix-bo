<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Product extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'code',
        'title',
        'description',
        'price',
        'image',
        'discount_price',
        'status',
        'product_type',
    ];

    public function freeGifts() {
        return $this->belongsToMany( ProductFreeGift::class, 'products_product_free_gifts', 'product_id', 'free_gift_id' );
    }
    
    public function addOns() {
        return $this->belongsToMany( ProductAddOn::class, 'products_product_add_ons', 'product_id', 'add_on_id' );
    }
    
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }
    
    public function activeProductVariants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id')->where('status',10);
    }
    
    public function productGalleries()
    {
        return $this->hasMany(ProductGallery::class, 'product_id');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'code',
        'title',
        'description',
        'price',
        'image',
        'discount_price',
        'status',
        'product_type',
    ];

    protected static $logName = 'categories';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} category";
    }
}
