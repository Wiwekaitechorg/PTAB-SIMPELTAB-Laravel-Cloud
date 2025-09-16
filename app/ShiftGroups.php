<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftGroups extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        // 'code',
        'title',
        'dapertement_id',
        'job_id',
        'subdapertement_id',
        'shift_parent_id',
        'created_at',
        'updated_at',
        'work_type_id',
        'work_unit_id',
        'queue',
        'type_s'
    ];

    /**
     * Scope: all absence time slots for a given shift group (non-break).
     */
    public function scopeAbsenceTimeSlots(Builder $query, int $shiftGroupId): Builder
    {
        return $query->select(
                'duration',
                'duration_exp',
                'absence_categories.queue',
                'type',
                'time',
                'start',
                'absence_category_id',
                'shift_group_timesheets.id as shift_group_timesheet_id'
            )
            ->join('shift_group_timesheets', 'shift_group_timesheets.shift_group_id', '=', 'shift_groups.id')
            ->join('absence_categories', 'shift_group_timesheets.absence_category_id', '=', 'absence_categories.id')
            ->where('shift_groups.id', $shiftGroupId)
            ->where('absence_categories.type', '!=', 'break')
            ->orderBy('absence_categories.queue', 'ASC');
    }


    public function scopeFilterSubDapertement($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('subdapertement_id', $ststussm);
        }
        return $query;
    }

    public function scopeFilterDapertement($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('dapertement_id', $ststussm);
        }
        return $query;
    }

    public function scopeFilterWorkUnit($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('work_unit_id', $ststussm);
        }
        return $query;
    }

    public function scopeFilterJob($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('job_id', $ststussm);
        }
        return $query;
    }
}
