<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSpecial extends Model
{
    use HasFactory;
    protected $fillable = [
        'staff_id',
        'fingerprint',
        'camera',
        'gps',
        'expired_date'
    ];
}
