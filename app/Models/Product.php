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
        'parent_id',
        'brand_id',
        'supplier_id',
        'unit_id',
        'title',
        'description',
        'product_code',
        'barcode_symbology',
        'workmanship',
        'location',
        'address_1',
        'address_2',
        'city',
        'type',
        'state',
        'postcode',
        'purchase_unit',
        'sale_unit',
        'price',
        'promotional_price',
        'promotion_start',
        'promotion_end',
        'promotion_on',
        'cost',
        'alert_quantity',
        'quantity',
        'thumbnail',
        'imei',
        'serial_number',
        'stock_worth',
        'tax_method',
        'featured',
        'status',
    ];

    public static function getPredefinedBarcodeSymbologies()
    {
        return [
            'EAN-13',
            'UPC-A',
            'Code 128',
            'Code 39',
            'QR Code',
            'Data Matrix',
            'PDF417'
        ];
    }

    public static function getPredefinedTaxMethods()
    {
        return [
            1 => 'Exclusive',
            2 => 'Inclusive',
            3 => 'Flat',
        ];
    }

    public static function getPredefinedProductTypes()
    {
        return [
            1 => 'Standard',
            2 => 'Limited',
            2 => 'Subscription',
        ];
    }

public static function getPredefinedUnits()
    {
        return [
            'purchase_unit' => [
                'Box',
                'Kilogram (kg)',
                'Liter (L)',
                'Piece (pc)',
                'Meter (m)',
                'Bundle',
            ],
            'sale_unit' => [
                'Piece (pc)',
                'Kilogram (kg)',
                'Liter (L)',
                'Box',
                'Set',
                'Meter (m)',
            ]
        ];
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'products_bundles')
        ->withPivot('quantity', 'price');
    }
    
    public function galleries() {
        return $this->hasMany( ProductGallery::class, 'product_id' );
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'products_categories')
                    ->withPivot('is_child', 'status')
                    ->withTimestamps();
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouses_products')
                    ->withPivot('quantity', 'price', 'status')
                    ->withTimestamps();
    }

    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    // Child relationship (one product can have many children)
    public function children()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }
    
    // Recursive function to get descendants
    public function descendants()
    {
        // First get the immediate children
        $children = $this->children()->get();
        
        // Then loop through each child and get their children (grandchildren)
        $descendants = $children->map(function ($child) {
            return $child->descendants()->add($child);
        });

        // Return all descendants
        return $descendants->flatten();
    } 

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function productInventories()
    {
        return $this->hasMany(ProductInventory::class);
    }

    public function getStockWorthAttribute() {
        return ( $this->attributes['price'] && $this->attributes['quantity'] ) ? $this->attributes['price'] . ' / ' . $this->attributes['quantity'] : '-';
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' );
    }

    public function getThumbnailPathAttribute() {
        return $this->attributes['thumbnail'] ? asset( 'storage/'.$this->attributes['thumbnail'] ) : asset( 'admin/images/placeholder.png' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'parent_id',
        'brand_id',
        'supplier_id',
        'unit_id',
        'title',
        'description',
        'product_code',
        'barcode_symbology',
        'workmanship',
        'location',
        'address_1',
        'address_2',
        'city',
        'type',
        'state',
        'postcode',
        'purchase_unit',
        'sale_unit',
        'price',
        'promotional_price',
        'promotion_start',
        'promotion_end',
        'promotion_on',
        'cost',
        'alert_quantity',
        'quantity',
        'thumbnail',
        'imei',
        'serial_number',
        'stock_worth',
        'tax_method',
        'featured',
        'status',
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
