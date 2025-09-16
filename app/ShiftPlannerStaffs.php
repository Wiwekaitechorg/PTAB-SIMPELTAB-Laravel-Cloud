<?php
namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftPlannerStaffs extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'shift_group_id',
        'staff_id',
        'date',
        'start',
        'end',
        'created_at',
        'updated_at',
    ];

    public function scopeTodayForStaff($query, $staffId)
    {
        return $query->where('shift_planner_staffs.staff_id', $staffId)
            ->whereDate('shift_planner_staffs.start', now());
    }

    public function scopeWithoutAbsenceLog($query)
    {
        return $query->leftJoin('absence_logs', 'shift_planner_staffs.id', '=', 'absence_logs.shift_planner_id')
            ->whereNull('absence_logs.id');
    }

    public function absenceLogs()
    {
        return $this->hasMany(AbsenceLog::class, 'shift_planner_id', 'id');
    }

    public function scopeFilterJob($query, $job)
    {
        if ($job != '' && $job != '0' && $job != null) {
            $query->where('shift_groups.job_id', '=', $job);
        }
        return $query;
    }
    public function scopeFilterJob1($query, $job)
    {
        if ($job != '' && $job != '0' && $job != null) {
            $query->where('shift_groups.job_id', '=', $job);
        } else {
            return $query->whereNull('staffs.job_id');
        }
        return $query;
    }

    public function scopeFilterWorkUnit($query, $WorkUnit)
    {
        if ($WorkUnit != '' && $WorkUnit != '0') {
            $query->where('shift_groups.work_unit_id', '=', $WorkUnit);
        }
        return $query;
    }
    public function scopeFilterSubdapertement($query, $subdapertement, $job)
    {
        if ($job == '' || $job == '0' || $job == null) {
            if ($subdapertement != '' && $subdapertement != '') {
                $query->where('shift_groups.subdapertement_id', $subdapertement);
            }
            return $query;
        }
    }
    public function scopeFilterDapertement($query, $dapertement)
    {
        if ($dapertement != '') {
            $query->where('staffs.dapertement_id', $dapertement);
        }
        return $query;
    }
}
