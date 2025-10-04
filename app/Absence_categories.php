<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AbsenceCategoryFilter;

class Absence_categories extends Model
{
    use HasFactory;
    use AbsenceCategoryFilter;
    
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
