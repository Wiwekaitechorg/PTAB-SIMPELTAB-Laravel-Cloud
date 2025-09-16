<?php
namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceLog extends Model
{
    use HasFactory;
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

    /**
     * Bulk insert absence logs for one absence & list of time slots
     */
    public static function insertForAbsence($absenceId, $staffId, $timeSlots)
    {
        $rows = [];
        foreach ($timeSlots as $slot) {
            $rows[] = [
                'absence_id'          => $absenceId,
                'absence_category_id' => $slot->absence_category_id,
                'staff_id'            => $staffId,
                'status'              => 1,
                'status_active'       => 1,
                'created_at'          => now(),
                'start_date'          => $slot->start,
                'expired_date'        => $slot->start->addMinutes($slot->duration), // adjust to your logic
                'shift_planner_id'    => 0,
            ];
        }

        static::insert($rows);
    }

    /**
     * Optionally a scope for detailed absence log for current staff
     */
    public function scopeDetailForWorkType($query, $workTypeId, $absenceId)
    {
        return $query->selectRaw('absence_categories.*,absence_logs.id as id, absence_id, work_type_days.start, work_type_days.end')
            ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
            ->join('work_type_days', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
            ->join('work_types', 'work_type_days.work_type_id', '=', 'work_types.id')
            ->where('work_types.id', $workTypeId)
            ->where('absence_logs.id', $absenceId)
            ->where('absence_categories.type', '=', 'presence')
            ->where('absence_logs.status', '=', 1);
    }

    public function scopeCurrentPresence($query, $staffId)
    {
        return $query->selectRaw(
            'absence_logs.expired_date,shift_planner_id, queue, status_active,
                 absence_categories.id as absence_category_id,
                 absences.id as absence_id,
                 absence_logs.id as id'
        )
            ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            ->where('staff_id', $staffId)
            ->where('absence_logs.start_date', '<=', now())
            ->where('absence_logs.expired_date', '>=', now())
            ->where('absence_logs.status', 1)
            ->where('absence_categories.type', 'presence')
            ->orderBy('absence_logs.start_date', 'ASC');
    }

    public function scopeExtraActive($query, $staffId, $absenceRequestId)
    {
        return $query->selectRaw(
            'absence_logs.status,
                 absence_request_id,
                 absence_id,
                 absence_categories.type as absence_category_type,
                 absence_logs.expired_date,
                 shift_planner_id,
                 queue,
                 status_active,
                 absence_categories.id as absence_category_id,
                 absence_logs.id as id'
        )
            ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            ->where('staff_id', $staffId)
            ->where('absence_request_id', $absenceRequestId)
            ->where('absence_logs.start_date', '<=', now())
            ->where('absence_logs.expired_date', '>=', now())
            ->where('absence_logs.status', 1)
            ->where('absence_categories.type', 'extra')
            ->where('absence_categories.queue', '2')
            ->orderBy('absence_logs.id', 'DESC');
    }

    /**
     * Scope for “active” presence logs for today (start <= now <= expired)
     */
    public function scopeActivePresenceForStaff(Builder $query, int $staffId): Builder
    {
        return $query->selectRaw('
                absence_logs.expired_date,
                shift_planner_id,
                queue,
                status_active,
                absence_categories.id as absence_category_id,
                absences.id as absence_id,
                absence_logs.id as id
            ')
            ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            ->where('staff_id', $staffId)
            ->where('absence_logs.start_date', '<=', now())
            ->where('absence_logs.expired_date', '>=', now())
            ->where('absence_logs.status', 1)
            ->where('absence_categories.type', 'presence')
            ->orderBy('absence_logs.start_date', 'ASC');
    }

    /**
     * Scope for “work-type day” absence details (non shift-planner)
     */
    public function scopeWithWorkTypeDetails(Builder $query, int $workTypeId, int $absenceId): Builder
    {
        return $query->selectRaw('
                absence_categories.*,
                absence_logs.id as id,
                absence_id,
                work_type_days.start,
                work_type_days.end
            ')
            ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
            ->join('work_type_days', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
            ->join('work_types', 'work_type_days.work_type_id', '=', 'work_types.id')
            ->where('work_types.id', $workTypeId)
            ->where('absence_logs.id', $absenceId)
            ->where('absence_categories.type', 'presence')
            ->where('absence_logs.status', 1);
    }

    /**
     * Scope for “shift-group” absence details (shift planner)
     */
    public function scopeWithShiftGroupDetails(Builder $query, int $absenceId): Builder
    {
        return $query->selectRaw('
                absence_logs.*,
                absence_categories.type,
                absence_categories.queue,
                shift_group_timesheets.start,
                shift_group_timesheets.end
            ')
            ->leftJoin('absences', 'absences.id', '=', 'absence_logs.absence_id')
            ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
            ->join('shift_group_timesheets', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
            ->where('absence_logs.status', 1)
            ->where('absence_logs.id', $absenceId)
            ->where('absence_categories.type', 'presence')
            ->orderBy('absence_logs.id', 'DESC');
    }

    public function scopeWithRegularWorkType($query, $workTypeId, $absenceId)
    {
        return $query->selectRaw('absence_categories.*, absence_logs.id as id, absence_id, work_type_days.start, work_type_days.end')
            ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
            ->join('work_type_days', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
            ->join('work_types', 'work_type_days.work_type_id', '=', 'work_types.id')
            ->where('work_types.id', $workTypeId)
            ->where('absence_logs.id', $absenceId)
            ->where('absence_categories.type', 'presence')
            ->where('absence_logs.status', 1);
    }

    // scope for logs with shift_planner_id != 0
    public function scopeWithShiftGroup($query, $absenceId)
    {
        return $query->selectRaw('absence_logs.*, absence_categories.type, absence_categories.queue,shift_group_timesheets.start, shift_group_timesheets.end')
            ->leftJoin('absences', 'absences.id', '=', 'absence_logs.absence_id')
            ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
            ->join('shift_group_timesheets', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
            ->where('absence_logs.status', 1)
            ->where('absence_logs.id', $absenceId)
            ->where('absence_categories.type', 'presence')
            ->orderBy('absence_logs.id', 'DESC');
    }

    public function scopePresenceToday($q, $staffId)
    {
        return $q->selectRaw('absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
            ->withAbsenceAndCategory()
            ->forStaff($staffId)
            ->activeBetween()
            ->status(1)
            ->categoryType('presence')
            ->orderBy('absence_logs.start_date', 'ASC');
    }

    public function scopeCheckInToday($q, $staffId)
    {
        // absence_logs.status 0 and category id 1
        return $q->selectRaw('absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
            ->withAbsenceAndCategory()
            ->forStaff($staffId)
            ->activeBetween()
            ->status(0)
            ->categoryId(1)
            ->orderByDesc('absence_logs.start_date');
    }

    public function scopeFollowUpLog($q, $staffId, $absenceId)
    {
        return $q->selectRaw('absence_logs.expired_date,absence_logs.start_date, absence_logs.status as absence_log_status, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
            ->withAbsenceAndCategory()
            ->forStaff($staffId)
            ->where('absence_logs.absence_id', $absenceId)
            ->status(1)
            ->orderBy('absence_logs.start_date', 'ASC');
    }

    public function scopeActiveBetween($q, $now = null)
    {
        $now = $now ?? now();
        return $q->where('absence_logs.start_date', '<=', $now)
            ->where('absence_logs.expired_date', '>=', $now);
    }

    public function scopeStatus($q, $status)
    {
        return $q->where('absence_logs.status', $status);
    }

    public function scopeCategoryType($q, $type)
    {
        return $q->where('absence_categories.type', $type);
    }

    public function scopeCategoryId($q, $id)
    {
        return $q->where('absence_logs.absence_category_id', $id);
    }

    // ready-made for your break check
    public function scopeCurrentBreak($q, $staffId)
    {
        return $q->selectRaw('absence_id, absence_logs.status, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id')
            ->withAbsenceAndCategory()
            ->forStaff($staffId)
            ->activeBetween()
            ->status(0)
            ->categoryId(1)
            ->orderByDesc('absence_logs.id');
    }

    // ready-made for excuse/visit logs linked to request
    public function scopeLinkedToRequest($q, $staffId, $requestId, $type)
    {
        return $q->selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
            ->withAbsenceAndCategory()
            ->forStaff($staffId)
            ->where('absence_request_id', $requestId)
            ->activeBetween()
            ->status(1)
            ->categoryType($type)
            ->where('absence_categories.queue', 2)
            ->orderByDesc('absence_logs.id');
    }

    public function scopeCurrentBreakActive($q, $staffId)
    {
        return $q->selectRaw('absence_logs.status, absence_categories.type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.absence_id as absence_id, absence_logs.id as id')
            ->withAbsenceAndCategory()
            ->forStaff($staffId)
            ->activeBetween()
            ->categoryType('break')
            ->status(1)
            ->orderBy('absence_logs.id', 'ASC');
    }

    public function scopeWithAbsenceAndCategory($query)
    {
        return $query
            ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id');
    }

    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeForRequest($query, $requestId)
    {
        return $query->where('absence_request_id', $requestId);
    }

    public function scopeActive($query)
    {
        return $query->where('absence_logs.status', 1);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('absence_categories.type', $type);
    }

    public function scopeExtraForRequest($query, $staffId, $requestId)
    {
        return $query->selectRaw('
                absence_logs.status,
                absence_request_id,
                absence_id,
                absence_categories.type as absence_category_type,
                absence_logs.expired_date,
                shift_planner_id,
                queue,
                status_active,
                absence_categories.id as absence_category_id,
                absence_logs.id as id
            ')
            ->withAbsenceAndCategory()
            ->forStaff($staffId)
            ->forRequest($requestId)
            ->active()
            ->ofType('extra')
            ->queue(2)
            ->orderByDesc('absence_logs.id');
    }

    public function scopeByStaff($q, $staffId)
    {
        return $q->where('staff_id', $staffId);
    }

    public function scopeByStatus($q, $status = 1)
    {
        return $q->where('absence_logs.status', $status);
    }

    public function scopeByAbsenceCategory($q, $categoryId)
    {
        return $q->where('absence_logs.absence_category_id', $categoryId);
    }

    public function scopeType($q, $type)
    {
        return $q->where('absence_categories.type', $type);
    }

    public function scopeQueue($q, $queue)
    {
        return $q->where('absence_categories.queue', $queue);
    }

    /**
     * Scope for a specific absence request id.
     */
    public function scopeByRequest(Builder $query, $requestId)
    {
        return $query->where('absence_request_id', $requestId);
    }

    /**
     * Scope for logs that are active between now and expiration.
     */
    public function scopeActiveInPeriod(Builder $query, $now = null)
    {
        $now = $now ?? Carbon::now()->format('Y-m-d H:i:s');
        return $query->where('absence_logs.start_date', '<=', $now)
            ->where('absence_logs.expired_date', '>=', $now);
    }

    /**
     * Scope for a specific log status.
     */
    public function scopeWithStatus(Builder $query, $status)
    {
        return $query->where('absence_logs.status', $status);
    }

    /**
     * Scope for a given absence category type (requires join).
     */
    public function scopeWithCategoryType(Builder $query, string $type)
    {
        return $query->where('absence_categories.type', $type);
    }

    /**
     * Scope for a given category queue (requires join).
     */
    public function scopeWithCategoryQueue(Builder $query, $queue)
    {
        return $query->where('absence_categories.queue', $queue);
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
        return $query->whereRaw("DATE(absences.created_at) >= '" . $from . "' AND DATE(absences.created_at) <= '" . $to . "'");
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
        if (! empty(request()->input('from')) && ! empty(request()->input('to'))) {
            $from = request()->input('from');
            $to   = request()->input('to');
            //return $query->whereBetween('lock_action.created_at', [$from, $to]);
            // return $query->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(absences.created_at) >= '" . $from . "' AND DATE(absences.created_at) <= '" . $to . "'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {
            if (date('d') > 20) {
                $from = date("Y-m-d", strtotime(date('Y-m') . "-21"));
                $to   = date("Y-m-d", strtotime('+1 month', strtotime(date('Y-m') . "-20")));
            } else {
                $from = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . "-21")));
                $to   = date("Y-m-d", strtotime('0 month', strtotime(date('Y-m') . "-20")));
            }

            // return $query->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(absences.created_at) >= '" . $from . "' AND DATE(absences.created_at) <= '" . $to . "'");
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
