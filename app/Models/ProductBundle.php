<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class ProductBundle extends Model
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
        'validity_days',
    ];

    public function productBundleMetas()
    {
        return $this->hasMany(ProductBundleMeta::class);
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' );
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getExpiredDateAttribute() {
        if (!isset($this->attributes['created_at'])) {
            return null; // Return null or handle this scenario as needed
        }
    
        $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['created_at'])
            ->setTimezone('Asia/Kuala_Lumpur');
    
        $validityDays = (int) ($this->attributes['validity_days'] ?? 0);
    
        return $createdAt->addDays($validityDays)->format('Y-m-d');
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
        'validity_days',
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
