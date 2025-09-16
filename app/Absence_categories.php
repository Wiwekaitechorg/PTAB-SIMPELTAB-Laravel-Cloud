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

    public function scopeWithWorkTypeDay($query, $workTypeId, $day)
    {
        return $query->selectRaw('absence_categories.*, work_type_days.start, work_type_days.end')
            ->join('work_type_days', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
            ->join('work_types', 'work_type_days.work_type_id', '=', 'work_types.id')
            ->where('work_types.id', $workTypeId)
            ->where('day_id', $day);
    }

    public function scopeExtraType($query)
    {
        return $query->where('type', 'extra');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Scope by type string.
     */
    public function scopeByType(Builder $query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by queue number/string.
     */
    public function scopeByQueue(Builder $query, $queue)
    {
        return $query->where('queue', $queue);
    }
}
