<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComplainStatus extends Model
{
    protected $table = 'complain_status';
    protected $fillable = [
        'code',
        'name',
        'description',
    ];
}
