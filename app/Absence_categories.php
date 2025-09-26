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

    public function workTypeDays()
    {
        return $this->hasMany(WorkTypeDays::class, 'absence_category_id');
    }

    public function shiftGroupTimesheets()
    {
        return $this->hasMany(ShiftGroupTimesheets::class, 'absence_category_id');
    }
}
