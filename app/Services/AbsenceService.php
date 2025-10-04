<?php
namespace App\Services;

use App\Absence;
use App\MessageLog;
use App\Repositories\AbsenceCategoryRepository;
use App\Repositories\AbsenceLogRepository;
use App\Repositories\AbsenceRepository;
use App\Repositories\AbsenceRequestRepository;
use App\Repositories\ShiftGroupTimesheetRepository;
use App\Repositories\ShiftPlannerStaffRepository;
use App\Repositories\WorkTypeDayRepository;
use App\StaffSpecial;
use App\WorkUnit;
use Illuminate\Support\Facades\Log;

class AbsenceService
{
    // --- original properties retained (including original defaults / misspellings) ---
    protected $staff          = null;
    protected $menu           = [];
    protected $data           = [];
    protected $presenceStatus = "ON";
    protected $staffID;
    protected $absenceRequestRepo;
    protected $absenceLogRepo;

    // repository dependencies (injected)
    public function __construct(
        private AbsenceLogRepository $absenceLog,
        private AbsenceRequestRepository $absenceRequest,
        private ShiftPlannerStaffRepository $shiftPlannerStaff,
        private ShiftGroupTimesheetRepository $shiftGroupTimesheet,
        private AbsenceCategoryRepository $absenceCategory,
        private AbsenceRepository $absenceRepo,
        private WorkTypeDayRepository $workTypeDayRepo
    ) {}

    /**
     * Main method (refactored) — preserves behavior while extracting repeated logic.
     *
     * @param int $staffId
     * @param int $shift_group_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSession($staffId)
    {
        // local repositories used in original code instantiate AbsenceLogRepository & AbsenceRequestRepository with staff id
        $this->absenceLogRepo     = new AbsenceLogRepository($staffId);
        $this->absenceRequestRepo = new AbsenceRequestRepository($staffId);
        //Set Staff Attribute (reguler/shift) & Condition (geofenceoff ON/OFF, etc)
        if (! $this->setStaffAttribute()) {
            return response()->json($this->responseGetSession());
        }
        //Check If Leave => Output
        if ($this->isLeave()) {
            return response()->json($this->responseGetSession());
        }
        //Else Check If Permission => Output
        else if ($this->isPermission()) {
            return response()->json($this->responseGetSession());
        }
        //Else Check If Duty => Output
        else if ($this->isDuty()) {
            return response()->json($this->responseGetSession());
        }
        //Else Check If Dispense => Output
        //Else Check If Extra Old End Open => Output
        else if ($this->isExtraOld()) {
            return response()->json($this->responseGetSession());
        }
        //Else Check If Holiday
        else if ($this->isHoliday()) {
            //Check If Extra on Holiday => Output
            if ($this->isExtraHoliday()) {
                return response()->json($this->responseGetSession());
            } else {
                return response()->json($this->responseGetSession());
            }
        }
        //Else
        else {
            //Check If Presence Empty => Create Absence & Log
            $this->isPresenceEmpty();
            //Check If Presence Open => Output
            if ($this->isPresenceOpen()) {
                return response()->json($this->responseGetSession());
            }
            //Check If Presence Active
            else if ($this->isPresenceActive()) {
                //Check If Break => Output
                if ($this->isBreak()) {
                    return response()->json($this->responseGetSession());
                }
                //Else Check If Visit => Output
                else if ($this->isVisit()) {
                    return response()->json($this->responseGetSession());
                }
                //Else Check If Excuse => Output
                else if ($this->isExcuse()) {
                    return response()->json($this->responseGetSession());
                }
                //Else Output
                else {
                    return response()->json($this->responseGetSession());
                }
            }
            //Check If Presence Closed => Output
            else if ($this->isPresenceClosed()) {
                //Check If Extra on Presence Closed => Output
                if ($this->isExtraPresenceClosed()) {
                    return response()->json($this->responseGetSession());
                } else {
                    return response()->json($this->responseGetSession());
                }
            }
        }
    }

    // -------------------------
    // Helper methods extracted
    // -------------------------

    private function getDayNumber()
    {
        return date('w') == '0' ? '7' : date('w');
    }

    private function isNightWindow()
    {
        $now = date('Y-m-d H:i:s');
        return (($now > date('Y-m-d 21:00:00') && $now < date('Y-m-d 23:59:59')) ||
            ($now > date('Y-m-d 01:00:00') && $now < date('Y-m-d 06:00:00')));
    }

    /**
     * Centralized response builder (keeps original shape)
     */
    private function responseGetSession()
    {
        return [
            'staff'          => $this->staff,
            'menu'           => $this->menu,
            'data'           => $this->data,
            'presenceStatus' => $this->presenceStatus,
        ];
    }

    /**
     * Set Staff Attribute (reguler/shift) & Condition (geofenceoff ON/OFF, etc)
     */
    private function setStaffAttribute()
    {
        // staff attributes by joining work_units, staffs, work_types
        $this->staff = WorkUnit::join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $this->staffId)->first();
        // if no staff found
        if (! $this->staff) {
            return false;
        }

        // set base geofence flag (original logic)
        $this->staff->geolocationOff = ($this->absenceRequestRepo->findByCategory('geolocation_off') || $this->absenceRequestRepo->findByCategory('forget') || $this->absenceRequestRepo->findByCategory('AdditionalTime')) ? 'ON' : 'OFF';

        // location override request (original method: findByCategoryNotIn)
        $location = $this->absenceRequestRepo->findByCategory('location') ? $this->absenceRequestRepo->findByCategoryNotIn('location', [['work_unit_id' => 'approve']]) : null;
        if ($location) {
            $this->staff->lat    = $location->lat;
            $this->staff->lng    = $location->lng;
            $this->staff->radius = $location->radius;
        }

        // staff special overrides for fingerprint/selfie/gps
        $staffSpecial = StaffSpecial::select('staff_specials.*')
            ->where('staff_id', $staffId)->whereDate('expired_date', '>=', date('Y-m-d'))->first();
        if ($staffSpecial) {
            $this->staff->fingerfrint = $staffSpecial->fingerprint;
            $this->staff->selfie      = $staffSpecial->camera;
            $this->staff->gps         = $staffSpecial->gps;
        }

        // if gps disabled -> geolocationOff ON
        if ($this->staff->gps == "OFF") {
            $this->staff->geolocationOff = "ON";
        }

        return true;
    }

    /**
     * Session Leave
     */
    private function isLeave()
    {
        // Check if Request Active (category 'leave' status is 'approve' or 'active', if type 'sick' filter by date start, if 'other' filter by date currently)
        $leave         = $this->absenceRequestRepo->findByCategory('leave');
        // if success => push menu 'leave', data.leave = Request Active, presenceStatus = OFF, return true
        if ($leave) {
            $this->menu[]        = 'leave';
            $this->data['leave'] = $leave;
            $this->presenceStatus = "OFF";
            return true;
        }
        // else return false
        return false;
    }

    /**
     * Session Permission
     */
    private function isPermission()
    {
        // Check if Request Active (category 'permission' status is 'approve' or 'active', if type 'sick' filter by date start, if 'other' filter by date currently)
        // if success => push menu 'permission', data.permission = Request Active, presenceStatus = OFF, return true
        // else return false
    }

    /**
     * Session Duty
     */
    private function isDuty()
    {
        // Check if Request Active (category 'duty' status is 'approve' or 'active' filter by date currently)
        // if success => push menu 'duty', data.duty = Request Active, data.dutyLog = Log Open Related order by id asc, presenceStatus = OFF, return true
        // else return false
    }

    /**
     * Session Extra Old
     */
    private function isExtraOld()
    {
        // Check if Log Active Expired
        // if success => push menu 'extra', data.extra = Request Related, data.extraLog = Log Active Expired, staff.geofenceOff = Request Related type == outside ? ON:OFF, return true
        // else return false
    }

    /**
     * Session Holiday
     */
    private function isHoliday()
    {
        // Check if Holiday Active (by staff work type)
        // if success => presenceStatus = OFF, return true
        // else return false
    }

    /**
     * Session Extra on Holiday
     */
    private function isExtraHoliday()
    {
        // Check if Request Active & Check if Holiday Active
        // if success => push menu 'extra', data.extra = Request Active, data.extraLog = Log Open Related  order by id asc, staff.geofenceOff = Request Active type == outside ? ON:OFF, return true
        // else return false
    }

    /**
     * Session Presence Empty
     */
    private function isPresenceEmpty()
    {
        // Check if Log Empty & presenceStatus is ON
        // if success => Create Absence & Log (by staff work type)
        // return true
    }

    /**
     * Session Presence Open
     */
    private function isPresenceOpen()
    {
        // Check if Log Open
        // if success => push menu 'presence', data.presenceLog = Log Open order by id asc, return true
        // else return false
    }

    /**
     * Session Presence Active
     */
    private function isPresenceActive()
    {
        // Check if Log Active
        // if success => return true
        // else return false
    }

    /**
     * Check Break (isBreak)
     */
    private function isBreak()
    {
        // Check If Log Open & Presence Active
        // if success => push menu 'break', data.breakLog = Log Open order by id asc, return true
        // else return false
    }

    /**
     * Check Visit (isVisit)
     */
    private function isVisit()
    {
        // Check if Request Active & Presence Active
        // if success => push menu 'visit', data.visit = Request Active, data.visitLog = Log Open Related order by id asc, data.inVisit = Visit (table), return true
        // else return false
    }

    /**
     * Check Excuse (isExcuse)
     */
    private function isExcuse()
    {
        // Check if Request Active & Presence Active
        // if success => push menu 'excuse', data.excuse = Request Active, data.excuseLog = Log Open Related order by id asc, return true
        // else return false
    }

    /**
     * Check Presence Closed (isPresenceClosed)
     */
    private function isPresenceClosed()
    {
        // Check if Log Closed
        // if success => return true
        // else return false
    }

    /**
     * Check Extra on Presence Closed (isExtraPresenceClosed())
     */
    private function isExtraPresenceClosed()
    {
        // Check if Request Active & Check if Presence Closed
        // if success => push menu 'extra', data.extra = Request Active, data.extraLog = Log Open Related order by id asc, staff.geofenceOff = Request Active type == outside ? ON:OFF, return true
        // else return false
    }

    /**
     * Create message logs for problem duration (original logic) — made robust
     */
    private function createProblemMessageLogs($staffId, $firstScheduleItem, $problem)
    {
        if (! $firstScheduleItem) {
            return;
        }

        $str_date = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . $firstScheduleItem->time)));
        $exp_date = date("Y-m-d H:i:s", strtotime('+ ' . (($firstScheduleItem->duration - $problem->duration) * 60) . ' minutes', strtotime(date('Y-m-d ' . $firstScheduleItem->time))));

        $i = 0;
        // fix original loop so it iterates in steps of $problem->duration
        while (strtotime($str_date) < strtotime($exp_date)) {
            $i += $problem->duration;
            $str_date = date("Y-m-d H:i:s", strtotime('+ ' . $i * 60 . ' minutes', strtotime($str_date)));
            $message  = "Anda Dalam Pengawasan, Buka Untuk Absen Lokasi";
            MessageLog::create([
                'staff_id'   => $staffId,
                'memo'       => $message,
                'type'       => 'check',
                'status'     => 'pending',
                'created_at' => $str_date,
            ]);
        }
    }

    /**
     * Save shift-type absence logs (keeps same save shape and status logic as original)
     */
    private function saveShiftAbsenceLogs($absenceModel, $list_absence, $plannerItem)
    {
        // use values from the first element as the base
        if (count($list_absence) === 0) {
            return;
        }

        $baseDuration    = $list_absence[0]->duration;
        $baseDurationExp = $list_absence[0]->duration_exp;
        $baseTime        = strtotime(date('Y-m-d ' . $list_absence[0]->time));

        $timein       = date("Y-m-d H:i:s", $baseTime);
        $timeout      = date("Y-m-d H:i:s", strtotime("+{$baseDuration} minutes", $baseTime));
        $expired_date = date("Y-m-d H:i:s", strtotime('+ ' . ($baseDuration + $baseDurationExp) . ' minutes', $baseTime));

        foreach ($list_absence as $index => $absenceItem) {
            $status = 0;
            if ($index === count($list_absence) - 1) {
                $status = 1;
            } elseif ($index === 2 && $absenceItem->type === "break") {
                $status = 1;
            }

            // start_date depends on queue (original logic)
            if ($absenceItem->queue == 1) {
                $start_date = date("Y-m-d H:i:s", strtotime("-{$baseDurationExp} minutes", $baseTime));
            } else {
                $start_date = date("Y-m-d H:i:s", strtotime("+{$baseDuration} minutes", $baseTime));
            }

            $this->absenceLogRepo->save([
                'absence_id'               => $absenceModel->id,
                'shift_planner_id'         => $plannerItem->shift_planner_id,
                'shift_group_timesheet_id' => $absenceItem->shift_group_timesheet_id,
                'timein'                   => $timein,
                'timeout'                  => $timeout,
                'start_date'               => $start_date,
                'expired_date'             => $expired_date,
                'status'                   => $status,
                'absence_category_id'      => $absenceItem->absence_category_id,
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);
        }
    }

    /**
     * Save regular-type absence logs (work_type_day items)
     */
    private function saveRegularAbsenceLogs($absenceModel, $list_absence)
    {
        if (count($list_absence) === 0) {
            return;
        }

        $baseTime    = strtotime(date('Y-m-d ' . $list_absence[0]->time));
        $duration    = $list_absence[0]->duration;
        $durationExp = $list_absence[0]->duration_exp;

        foreach ($list_absence as $item) {
            $timein       = date("Y-m-d H:i:s", $baseTime);
            $timeout      = date("Y-m-d H:i:s", strtotime("+{$duration} minutes", $baseTime));
            $expired_date = date("Y-m-d H:i:s", strtotime("+" . ($duration + $durationExp) . " minutes", $baseTime));

            $start_date = $item->queue == 1
                ? date("Y-m-d H:i:s", strtotime("-{$durationExp} minutes", $baseTime))
                : date("Y-m-d H:i:s", strtotime("+{$duration} minutes", $baseTime));

            $this->absenceLogRepo->save([
                'absence_id'          => $absenceModel->id,
                'start_date'          => $start_date,
                'expired_date'        => $expired_date,
                'work_type_day_id'    => $item->work_type_day_id,
                'timein'              => $timein,
                'timeout'             => $timeout,
                'status'              => 1,
                'absence_category_id' => $item->absence_category_id,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }
}
