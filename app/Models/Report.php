<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use App\Observers\ReportObserver;

class Report extends Model
{
    // #[ObservedBy([ReportObserver::class])]

    protected $fillable = [
        'outcode',
        'source',
        'status',
        'error',
    ];

    // make default scope sort by date reverse
    protected static function booted()
    {
        static::addGlobalScope('sort', function ($builder) {
            $builder->orderBy('created_at', 'desc');
        });
    }


    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
