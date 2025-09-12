<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class staffActionwms extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'staff_id',
        'action_wm_id',
    ];
}
