<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class UserBundle extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'user_id',
        'product_bundle_id',
        'total_cups',
        'cups_left',
        'last_used',
        'status',
    ];

    protected $hidden = [
        'secret_code'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function productBundle()
    {
        return $this->belongsTo(ProductBundle::class, 'product_bundle_id');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' );
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getUsedAtDateOnlyAttribute()
    {
        return $this->attributes['used_at'] ? $this->attributes['used_at']->format('Y-m-d') : null;
    }

    public function getRedeemFromLabelAttribute()
    {
        $rewardTypes = [
            '1' => __('user.checkin_rewards'),
            '2' => __('user.points_exchange'),
        ];

        return $rewardTypes[$this->attributes['redeem_from']] ?? null;
    }

    public function getBundleStatusLabelAttribute()
    {

        $statuses = [
            10 => __('bundle.active'),
            20 => __('bundle.used'),
            21 => __('bundle.expired'),
        ];

        return $statuses[$this->attributes['status']] ?? null;
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'user_id',
        'product_bundle_id',
        'total_cups',
        'cups_left',
        'last_used',
        'status',
    ];

    protected static $logName = 'user_bundles';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} user bundles";
    }
}
