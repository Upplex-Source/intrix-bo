<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class GuideBranch extends Model
{

    protected $table = 'countries_branches';

    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'country_id',
        'state_id',
        'file',
        'sequence',
        'status',
        'title',
        'description',
        'address',
        'phone_number',
        'file_type',
        'latitude',
        'longitude',
    ];

    public function country()
    {
        return $this->belongsTo(GuideCountry::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(GuideState::class, 'state_id');
    }

    public function getFilePathAttribute() {
        return $this->attributes['file'] ? asset( 'storage/' . $this->attributes['file'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public function getGuideTypeAttribute()
    {
        return $this->attributes['type'] ?? null;
    }
    
    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'country_id',
        'state_id',
        'file',
        'sequence',
        'status',
        'title',
        'description',
        'address',
        'phone_number',
        'file_type',
        'latitude',
        'longitude',
    ];

    protected static $logName = 'guides';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} guide";
    }
}
