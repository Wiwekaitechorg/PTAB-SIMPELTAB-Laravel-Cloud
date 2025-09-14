<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftGroupTimesheets extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'shift_group_id',
        'absence_category_id',
        'time',
        'start',
        'end',
        'duration',
        'created_at',
        'updated_at',
        'duration_exp',
    ];
}
