<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    public $fillable = [
        'report_id',
        'listing_id',

        'rental_price',
        'raw_price',
        'postcode',
        'subcode',
        'outcode',
        'district',
        'address',
        'description',
        'latitude',
        'longitude',
        'property_type',
        'property_status',
        'bedrooms',
        'bathrooms',
        'student_friendly',
        'families_allowed',
        'pets_allowed',
        'smokers_allowed',
        'dss_covers_rent',
        'landlord'
    ];

    public $casts = [
        'student_friendly' => 'boolean',
        'families_allowed' => 'boolean',
        'pets_allowed' => 'boolean',
        'smokers_allowed' => 'boolean',
        'dss_covers_rent' => 'boolean'
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
