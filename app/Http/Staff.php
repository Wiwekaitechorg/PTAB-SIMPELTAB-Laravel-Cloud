<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Staff extends Model
{
    protected $table = 'staffs';
    protected $fillable = [
        'code',
        'name',
        'phone',
        'dapertement_id',
        'subdapertement_id',
        'work_unit_id',
        'pbk',
        'work_type_id',
        'job_id',
        'NIK',
        'fingerprint',
        'image',
        'type',
        '_status'
    ];

    public function dapertement()
    {
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id');
    }

    public function subdapertement()
    {
        return $this->belongsTo(Subdapertement::class, 'subdapertement_id', 'id');
    }

    public function action()
    {
        return $this->belongsToMany(Action::class, 'action_staff', 'action_id', 'staff_id')
            ->withPivot([
                'status'
            ]);
    }
    public function area()
    {
        return $this->belongsToMany(CtmWilayah::class, 'simpletab.area_staff', 'staff_id', 'area_id');
    }
    public function scopeFilterDapertement($query, $dapertement)
    {
        if ($dapertement != '') {
            $query->where('dapertement_id', $dapertement);
        }
        return $query;
    }
    public function scopeFilterJob($query, $job)
    {
        if ($job != '') {
            $query->where('job_id', $job);
        }
        return $query;
    }
    public function scopeFilterWorkUnit($query, $work_unit)
    {
        if ($work_unit != '') {
            $query->where('work_unit_id', $work_unit);
        }
        return $query;
    }

    public function scopeFilterName($query, $name)
    {
        if ($name != '') {
            $query->where('staffs.name', 'like', '%' . $name . '%');
        }
        return $query;
    }

    public function scopeFilterId($query, $id)
    {
        if ($id != '') {
            $query->where('staffs.id', $id);
        }
        return $query;
    }

    public function scopeFilterSubdapertement($query, $subdapertement, $job)
    {
        if ($job == '' || $job == '0' || $job == null) {
            if ($subdapertement != '' && $subdapertement != '') {
                $query->where('subdapertement_id', $subdapertement);
            }
            return $query;
        }
    }

    public function scopeFilterDateWeb($query, $from, $to)
    {
        if (!empty(request()->input('from')) && !empty(request()->input('to'))) {
            $from = request()->input('from');
            $to = request()->input('to');
            //return $query->whereBetween('lock_action.created_at', [$from, $to]);
            //return $query->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(absences.created_at) >= '".$from."' AND DATE(absences.created_at) <= '".$to."'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {
            if (date('d') > 20) {
                $from = date("Y-m-d", strtotime(date('Y-m') . "-21"));
                $to = date("Y-m-d", strtotime('+1 month', strtotime(date('Y-m') . "-20")));
            } else {
                $from = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . "-21")));
                $to = date("Y-m-d", strtotime('0 month', strtotime(date('Y-m') . "-20")));
            }

            //return $query->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(absences.created_at) >= '".$from."' AND DATE(absences.created_at) <= '".$to."'");
        }
    }
}
