<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence_categories extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'time',
        'start',
        'end',
        'value',

    ];
}
