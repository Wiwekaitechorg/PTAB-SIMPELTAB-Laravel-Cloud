<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkUnit extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'code',
        'name',
        'serial_number',
        'lat',
        'lng',
        'radius',
    ];
}
