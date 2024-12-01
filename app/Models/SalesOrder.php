<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class SalesOrder extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'quotation_id',
        'salesman_id',
        'customer_id',
        'warehouse_id',
        'supplier_id',
        'order_tax',
        'order_discount',
        'shipping_cost',
        'attachment',
        'remarks',
        'reference',
        'tax_type',
        'status',
        'amount',
        'original_amount',
        'paid_amount',
        'final_amount',
        'tax_method_id',
    ];
    
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function salesOrderMetas()
    {
        return $this->hasMany(SalesOrderMeta::class, 'sales_order_id');
    }

    public function salesman()
    {
        return $this->belongsTo(Administrator::class, 'salesman_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function taxMethod()
    {
        return $this->belongsTo(TaxMethod::class, 'tax_method_id');
    }

    public function getAttachmentPathAttribute() {
        return $this->attributes['attachment'] ? asset( 'storage/' . $this->attributes['attachment'] ) : asset( 'admin/images/placeholder.png' );
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'salesman_id',
        'customer_id',
        'warehouse_id',
        'supplier_id',
        'order_tax',
        'order_discount',
        'shipping_cost',
        'attachment',
        'remarks',
        'reference',
        'tax_type',
        'status',
        'amount',
        'original_amount',
        'paid_amount',
        'final_amount',
        'tax_method_id',
    ];

    protected static $logName = 'salesorders';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} salesorder";
    }
}
