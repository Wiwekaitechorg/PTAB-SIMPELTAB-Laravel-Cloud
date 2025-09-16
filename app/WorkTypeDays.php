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

    public function scopePresenceFor($query, $workTypeId, $day)
    {
        return $query->selectRaw(
                'duration,duration_exp,queue,type,time,start,
                 absence_category_id,work_type_days.id as work_type_day_id'
            )
            ->join('absence_categories', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
            ->where('work_type_id', $workTypeId)
            ->where('day_id', $day)
            ->where('absence_categories.type', 'presence')
            ->orderBy('queue','ASC');
    }
}
