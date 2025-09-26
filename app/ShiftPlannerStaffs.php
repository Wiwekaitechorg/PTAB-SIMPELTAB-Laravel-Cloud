<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ShiftPlannerStaffsFilter;

class ShiftPlannerStaffs extends Model
{
    use HasFactory;
    use ShiftPlannerStaffsFilter;
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

    public static array $baseFilter = [
        // 'current'   => true,
        // 'start_col' => 'date',
        // 'end_col'   => 'date',
        // 'status'       => ['1', '0'],
        // 'order_by_raw' => [
        //     'FIELD(status,"active","approve")',
        // ],
        'order_by'     => 'id',
        'order_dir'    => 'DESC',
        'with'         => ['shiftGroup'],
    ];

    public function shiftGroup()
    {
        return $this->belongsTo(ShiftGroup::class, 'shift_group_id');
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
