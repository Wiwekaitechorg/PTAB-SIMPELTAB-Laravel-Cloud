<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\AbsenceLogFilter;

class AbsenceLog extends Model
{
    use HasFactory;
    use AbsenceLogFilter;
    protected $fillable = [
        'id',
        'staff_id',
        'absence_id',
        'image',
        'lat',
        'lng',
        'register',
        'shift_planner_id',
        'requests_id',
        'memo',
        'early',
        'duration',
        'created_by_staff_id',
        'updated_by_staff_id',
        'absence_category_id',
        'expired_date',
        'start_date',
        'status',
        'late',
        'day_id',
        'absence_request_id',
        'work_type_day_id',
        'shift_group_timesheet_id',
        'timein',
        'timeout',
        'accuracy',
        'distance',
    ];

    public static array $baseFilter = [
        'current'   => true,
        'start_col' => 'start_date',
        'end_col'   => 'expired_date',
        'status'       => ['1', '0'],
        // 'order_by_raw' => [
        //     'FIELD(status,"active","approve")',
        // ],
        'order_by'     => 'id',
        'order_dir'    => 'DESC',
        'with'         => ['absence', 'shiftPlanner', 'category', 'category.workTypeDays'],
    ];
    
    public function absence()
    {
        return $this->belongsTo(Absence::class, 'absence_id');
    }

    public function category()
    {
        return $this->belongsTo(Absence_categories::class, 'absence_category_id');
    }

    public function shiftPlanner()
    {
        return $this->belongsTo(ShiftPlannerStaffs::class, 'shift_planner_id');
    }

    public function workTypeDays()
    {
        return $this->belongsTo(WorkTypeDays::class, 'work_type_day_id', 'id');
    }

    public function shiftGroupTimeSheets()
    {
        return $this->belongsTo(ShiftGroupTimesheets::class, 'shift_group_timesheet_id', 'id');
    }

    public function absenceRequests()
    {
        return $this->belongsTo(AbsenceRequest::class, 'absence_request_id', 'id');
    }


    // public function scopeFilterDate($query, $monthyear)
    // {
    //     if (!empty(request()->input('monthyear'))) {
    //         $monthyear = request()->input('monthyear');
    //         $from = date("Y-m-d", strtotime('-1 month', strtotime($monthyear . '-21')));
    //         $to = $monthyear . '-20';
    //         //return $query->whereBetween('lock_action.created_at', [$from, $to]);
    //         return $query->whereBetween(DB::raw('DATE(absence_logs.created_at)'), [$from, $to]);
    //         // return $query->where('froms_id', $from);
    //         // dd(request()->input('from'));

    //     } else {
    //         if (date('d') > 20) {
    //             $from = date("Y-m-d", strtotime(date('Y-m') . "-21"));
    //             $to = date("Y-m-d", strtotime('+1 month', strtotime(date('Y-m') . "-20")));
    //         } else {
    //             $from = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . "-21")));
    //             $to = date("Y-m-d", strtotime('0 month', strtotime(date('Y-m') . "-20")));
    //         }

    //         return $query->whereBetween(DB::raw('DATE(absence_logs.created_at)'), [$from, $to]);
    //     }
    // }
    public function scopeFilterDate($query, $from, $to)
    {
        // if (!empty(request()->input('from')) || !empty(request()->input('to'))) {
        // $from = request()->input('from');
        // $to = request()->input('to');
        // $from = date("Y-m-d", strtotime('-1 month', strtotime($from . '-21')));
        // $to = $from . '-20';

        //return $query->whereBetween('lock_action.created_at', [$from, $to]);
        //return $query->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to]);
        return $query->whereRaw("DATE(absences.created_at) >= '".$from."' AND DATE(absences.created_at) <= '".$to."'");
        // return $query->where('froms_id', $from);
        // dd(request()->input('from'));

        // } else {
        //     if (date('d') > 20) {
        //         $from = date("Y-m-d", strtotime(date('Y-m') . "-21"));
        //         $to = date("Y-m-d", strtotime('+1 month', strtotime(date('Y-m') . "-20")));
        //     } else {
        //         $from = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . "-21")));
        //         $to = date("Y-m-d", strtotime('0 month', strtotime(date('Y-m') . "-20")));
        //     }

        // return $query->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to]);
        // }
    }


    public function scopeFilterDateWeb($query, $from, $to)
    {
        if (!empty(request()->input('from')) && !empty(request()->input('to'))) {
            $from = request()->input('from');
            $to = request()->input('to');
            //return $query->whereBetween('lock_action.created_at', [$from, $to]);
            // return $query->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to]);
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

            // return $query->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(absences.created_at) >= '".$from."' AND DATE(absences.created_at) <= '".$to."'");
        }
    }


    public function scopeFilterStaff($query, $staff)
    {
        if ($staff != '') {
            $query->where('absences.staff_id', $staff);
        }
        return $query;
    }

    public function scopeFilterJob($query, $job)
    {
        if ($job != '') {
            $query->where('staffs.job_id', $job);
        }
        return $query;
    }

    public function scopeFilterAbsenceCategory($query, $absence_category)
    {
        if ($absence_category != '') {
            $query->where('absence_logs.absence_category_id', $absence_category);
        }
        return $query;
    }


    public function scopeFilterWorkUnit($query, $work_unit_id)
    {
        if ($work_unit_id != '') {
            $query->where('staffs.work_unit_id', $work_unit_id);
        }
        return $query;
    }


    public function scopeFilterdapertement($query, $dapertement)
    {
        if ($dapertement != '') {
            $query->where('staffs.dapertement_id', $dapertement);
        }
        return $query;
    }
}
