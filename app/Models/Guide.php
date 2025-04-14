<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Guide extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'country_id',
        'file',
        'sequence',
        'status',
        'title',
        'description',
        'file_type',
    ];

    public function country()
    {
        return $this->belongsTo(GuideCountry::class, 'country_id');
    }

    public function getFileTypeLabelAttribute()
    {
        $fileType = [
            '1' => __('guide.product_brochures'),
            '2' => __('guide.installation_guides'),
            '3' => __('guide.videos'),
        ];

        return $fileType[$this->attributes['file_type']] ?? null;
    }

    public function getFilePathAttribute() {
        return $this->attributes['file'] ? asset( 'storage/' . $this->attributes['file'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getThumbnailPathAttribute() {
        $file = $this->attributes['file'] ?? null;
    
        if ( ! $file ) {
            return asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
        }
    
        $extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
    
        switch ( $extension ) {
            case 'pdf':
                return asset( 'admin/images/file_pdf.png' );
            case 'mp4':
            case 'mov':
            case 'avi':
            case 'webm':
                return asset( 'admin/images/file_video.png' );
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
                return asset( 'storage/' . $file );
            default:
                return asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
        }
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
        'title',
        'description',
        'file',
        'sequence',
        'status',
        'title',
        'description',
        'file_type',
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
