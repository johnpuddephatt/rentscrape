<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outcode extends Model
{
    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'outcode';
    protected $orderBy = 'outcode';
}
