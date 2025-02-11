<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Blog extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'main_title',
        'subtitle',
        'text',
        'image',
        'type',
        'meta_title',
        'meta_desc',
        'publish_date',
        'status',
    ];

    protected $appends = [
        'encrypted_id',
        'display_type',
        'display_publish_date',
    ];

    public function images() {
        return $this->hasMany( BlogImage::class, 'blog_id' );
    }

    public function tag() {
        return $this->hasMany( BlogTag::class, 'blog_id' );
    }

    public function getDisplayPublishDateAttribute() {
        return $this->attributes[ 'publish_date' ] ? Carbon::parse( $this->attributes[ 'publish_date' ] )->format( 'Y-m-d' ) : null;
    }

    public function getDisplayTypeAttribute() {
        $types = \Helper::types();
        return $this->attributes[ 'type' ] ? $types[ $this->attributes[ 'type' ] ] : null;
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'main_title',
        'subtitle',
        'text',
        'type',
        'image',
        'meta_title',
        'meta_desc',
        'publish_date',
        'status',
    ];

    protected static $logName = 'blogs';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} ";
    }
}
