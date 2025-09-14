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

    public function scopeFilterJob($query, $job)
    {
        if ($job != '' && $job != '0' && $job != null) {
            $query->where('shift_groups.job_id', '=', $job);
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
}
