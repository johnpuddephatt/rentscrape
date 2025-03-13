<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postcode extends Model
{
    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'postcode';
    protected $orderBy = 'postcode';
}
