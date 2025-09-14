<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkTypeDays extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_id',
        'time',
        'duration',
        'absence_category_id',
        'duration_exp',
        'work_type_id',
        'work_type_id',
    ];
}
