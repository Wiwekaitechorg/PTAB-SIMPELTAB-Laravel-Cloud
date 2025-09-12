<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmWilayah extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'tblwilayah';

    protected $casts = [
    'id' => 'string',
    ];

    public $timestamps = false;    
}
