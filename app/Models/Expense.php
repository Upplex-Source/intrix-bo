<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Helper;

use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'fuel_expense_id',
        'toll_expense_id',
        'amount',
        'type',
        'transaction_time',
    ];
}
