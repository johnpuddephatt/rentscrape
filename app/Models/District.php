<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'district';
    protected $keyType = 'string';
    protected $orderBy = 'district';
}
