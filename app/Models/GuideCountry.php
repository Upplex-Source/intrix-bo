<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class GuideCountry extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'guide_countries';

    protected $fillable = [
        'name',
        'image',
        'currency_symbol',
        'iso_alpha2_code',
        'iso_alpha3_code',
        'calling_code',
        'status',
    ];

    public function guides()
    {
        return $this->hasMany(Guide::class, 'country_id');
    }

    public function branches()
    {
        return $this->hasMany(GuideBranch::class, 'country_id');
    }

    public function states()
    {
        return $this->hasMany(GuideState::class, 'country_id');
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getProductBrochuresCountAttribute() {
        return $this->guides()->where( 'file_type', 1 )->count();
    }

    public function getInstallationGuidesCountAttribute() {
        return $this->guides()->where( 'file_type', 2 )->count();
    }

    public function getVideosCountAttribute() {
        return $this->guides()->where( 'file_type', 3 )->count();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getGuideTypeAttribute()
    {
        return $this->attributes['type'] ?? null;
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'name',
        'image',
        'currency_symbol',
        'iso_alpha2_code',
        'iso_alpha3_code',
        'calling_code',
        'status',
    ];

    protected static $logName = 'guide_coutries';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} guide country";
    }
}
