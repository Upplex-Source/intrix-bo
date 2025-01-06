<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;
use Carbon\Carbon;

class VendingMachine extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'outlet_id',
        'title',
        'description',
        'code',
        'image',
        'latitude',
        'longtide',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'opening_hour',
        'closing_hour',
        'navigation_links',
        'status',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function stocks()
    {
        return $this->hasMany(VendingMachineStock::class, 'vending_machine_id');
    }

    public function getThumbnailPathAttribute() {
        return $this->attributes['thumbnail'] ? asset( 'storage/'.$this->attributes['thumbnail'] ) : asset( 'admin/images/placeholder.png' );
    }

    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/'.$this->attributes['image'] ) : asset( 'admin/images/placeholder.png' );
    }
    
    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }
    public function getOperationalHourAttribute()
    {
        // Extract and format opening and closing hours
        $openingHour = $this->attributes['opening_hour'] 
            ? Carbon::parse($this->attributes['opening_hour'])->format('g:ia') 
            : null;
    
        $closingHour = $this->attributes['closing_hour'] 
            ? Carbon::parse($this->attributes['closing_hour'])->format('g:ia') 
            : null;
    
        // Get current time as a Carbon instance
        $currentTime = now();
    
        // Parse opening and closing times into Carbon instances for comparison
        $openingTime = $this->attributes['opening_hour'] 
            ? Carbon::parse($this->attributes['opening_hour']) 
            : null;
    
        $closingTime = $this->attributes['closing_hour'] 
            ? Carbon::parse($this->attributes['closing_hour']) 
            : null;
    
        // Determine if the current time is within the operational range
        $isInOperation = $openingTime && $closingTime &&
            $currentTime->between($openingTime, $closingTime);
    
        // Generate operational hours string
        $operationString = $openingHour && $closingHour
            ? "{$openingHour} - {$closingHour}"
            : "Hours not set";
    
        // Append status
        $statusString = $isInOperation ? 'In Operation' : 'Closed';
    
        return "{$operationString}, {$statusString}";
    }
    
    public function getFormattedOpeningHourAttribute(){
        $openingHour = $this->attributes['opening_hour'] 
            ? Carbon::parse($this->attributes['opening_hour'])->format('g:ia') 
            : null;

        return $openingHour;
    }

    public function getFormattedClosingHourAttribute(){
        $openingHour = $this->attributes['closing_hour'] 
            ? Carbon::parse($this->attributes['closing_hour'])->format('g:ia') 
            : null;

        return $openingHour;
    }

    public $translatable = [ 'name', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'outlet_id',
        'title',
        'description',
        'code',
        'image',
        'latitude',
        'longtide',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'opening_hour',
        'closing_hour',
        'navigation_links',
        'status',
    ];

    protected static $logName = 'vending_machines';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} vending machine";
    }
}
