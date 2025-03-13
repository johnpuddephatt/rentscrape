<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcode extends Model
{
    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'subcode';
    protected $orderBy = 'subcode';
}
