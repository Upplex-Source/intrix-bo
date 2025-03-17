<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class ProductFreeGift extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'product_free_gifts';

    protected $fillable = [
        'product_id',
        'title',
        'description',
        'code',
        'color',
        'image',
        'status',
        'brochure',
        'sku',
        'specification',
        'features',
        'whats_included',
        'price',
        'discount_price',
    ];

    public function freeGiftProducts() {
        return $this->belongsToMany( Product::class, 'products_product_free_gifts','free_gift_id', 'product_id' );
    }

    public function product() {
        return $this->belongsTo( Product::class, 'product_id' );
    }

    public function getPathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : null;
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'product_id',
        'title',
        'description',
        'code',
        'color',
        'image',
        'status',
        'brochure',
        'sku',
        'specification',
        'features',
        'whats_included',
        'price',
        'discount_price',
    ];

    protected static $logName = 'product_free_gifts';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} product free gift";
    }
}
