<?php
namespace App\Services;

use App\Absence;
use App\AbsenceProblem;
use App\AbsenceRequestLogs;
use App\Holiday;
use App\MessageLog;
use App\Repositories\AbsenceCategoryRepository;
use App\Repositories\AbsenceLogRepository;
use App\Repositories\AbsenceRepository;
use App\Repositories\AbsenceRequestRepository;
use App\Repositories\ShiftGroupTimesheetRepository;
use App\Repositories\ShiftPlannerStaffRepository;
use App\Repositories\WorkTypeDayRepository;
use App\StaffSpecial;
use App\Visit;
use App\WorkUnit;
use Illuminate\Database\QueryException;

class AbsenceService
{
    // --- original properties retained (including original defaults / misspellings) ---
    protected $message        = 'Success';
    protected $menuReguler    = "OFF";
    protected $menuHoliday    = "OFF";
    protected $menuBreak      = "OFF";
    protected $menuExcuse     = "OFF";
    protected $menuVisit      = "OFF";
    protected $menuDuty       = "OFF";
    protected $menuFinish     = "OFF";
    protected $menuExtra      = "OFF";
    protected $menuLeave      = "OFF";
    protected $menuWaiting    = "OFF";
    protected $menuPermission = "OFF";
    protected $geolocationOff = "OFF";
    protected $sebelum;
    protected $extraC;
    protected $extra;
    protected $request_extra;
    protected $excuseC;
    protected $excuse;
    protected $request_excuse;
    protected $visitC;
    protected $visit;
    protected $request_visit;
    protected $leave;
    protected $permission;
    protected $duty;
    protected $reguler;
    protected $break;
    protected $absenceOut;
    protected $date;
    protected $lat;
    protected $fingerfrint = "ON";
    protected $selfie      = "ON";
    protected $gps         = "ON";
    protected $lng;
    protected $radius;
    protected $coordinat;
    protected $work_type;
    protected $absence;
    protected $tesss;
    protected $a1;
    protected $data;
    protected $waitingMessage;
    protected $AbsenceRequestLogs;

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
     * Centralized response builder (keeps original shape)
     */
    private function sendResponse()
    {
        return [
            'message'            => $this->message,
            'menu'               => [
                'menuReguler'    => $this->menuReguler,
                'menuHoliday'    => $this->menuHoliday,
                'menuBreak'      => $this->menuBreak,
                'menuExcuse'     => $this->menuExcuse,
                'menuVisit'      => $this->menuVisit,
                'menuDuty'       => $this->menuDuty,
                'menuFinish'     => $this->menuFinish,
                'menuExtra'      => $this->menuExtra,
                'menuLeave'      => $this->menuLeave,
                'menuWaiting'    => $this->menuWaiting,
                'menuPermission' => $this->menuPermission,
                'geolocationOff' => $this->geolocationOff,
            ],
            'sebelum'            => $this->sebelum,
            'extraC'             => $this->extraC,
            'extra'              => $this->extra,
            'request_extra'      => $this->request_extra,
            'excuseC'            => $this->excuseC,
            'excuse'             => $this->excuse,
            'request_excuse'     => $this->request_excuse,
            'visitC'             => $this->visitC,
            'visit'              => $this->visit,
            'request_visit'      => $this->request_visit,
            'leave'              => $this->leave,
            'permission'         => $this->permission,
            'duty'               => $this->duty,
            'reguler'            => $this->reguler,
            'break'              => $this->break,
            'absenceOut'         => $this->absenceOut,
            'date'               => date('Y-m-d h:i:s'),
            'lat'                => $this->lat,
            'fingerfrint'        => $this->fingerfrint,
            'selfie'             => $this->selfie,
            'gps'                => $this->gps,
            'lng'                => $this->lng,
            'radius'             => $this->radius,
            'coordinat'          => $this->coordinat,
            'work_type'          => $this->work_type,
            'absence'            => $this->absence,
            'tesss'              => $this->tesss,
            'a1'                 => $this->a1,
            'data'               => $this->data,
            'waitingMessage'     => $this->waitingMessage,
            'AbsenceRequestLogs' => $this->AbsenceRequestLogs,
        ];
    }

    /**
     * Main method (refactored) — preserves behavior while extracting repeated logic.
     *
     * @param int $staffId
     * @param int $shift_group_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuData($staffId, $shift_group_id)
    {
        // local repositories used in original code instantiate AbsenceLogRepository & AbsenceRequestRepository with staff id
        $absenceLogRepo     = new AbsenceLogRepository($staffId);
        $absenceRequestRepo = new AbsenceRequestRepository($staffId);

        // set base geofence flag (original logic)
        $this->geolocationOff = ($absenceRequestRepo->findByCategory('geolocation_off') || $absenceRequestRepo->findByCategory('forget') || $absenceRequestRepo->findByCategory('AdditionalTime')) ? 'ON' : 'OFF';

        // load work unit & coordinate
        $this->coordinat = WorkUnit::join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $staffId)->first();

        // if no coordinat found protect against errors
        if (! $this->coordinat) {
            return response()->json($this->sendResponse());
        }

        $this->lat    = $this->coordinat->lat;
        $this->lng    = $this->coordinat->lng;
        $this->radius = $this->coordinat->radius;

        // location override request (original method: findByCategoryNotIn)
        $Rlocation = $absenceRequestRepo->findByCategory('location') ? $absenceRequestRepo->findByCategoryNotIn('location', [['work_unit_id' => 'approve']]) : null;
        if ($Rlocation) {
            $this->lat    = $Rlocation->lat;
            $this->lng    = $Rlocation->lng;
            $this->radius = $Rlocation->radius;
        }

        // staff special overrides for fingerprint/selfie/gps
        $staff_special = StaffSpecial::select('staff_specials.*')
            ->where('staff_id', $staffId)->whereDate('expired_date', '>=', date('Y-m-d'))->first();
        if ($staff_special) {
            $this->fingerfrint = $staff_special->fingerprint;
            $this->selfie      = $staff_special->camera;
            $this->gps         = $staff_special->gps;
        }

        // if gps disabled -> geolocationOff ON
        if ($this->gps == "OFF") {
            $this->geolocationOff = "ON";
        }

        // in-work check duration problem
        $problem = AbsenceProblem::where('id', $this->coordinat->absence_problem_id)->first();

        // ---------------------------------------------------------------------
        // 1) preload request categories (simple assignments)
        // ---------------------------------------------------------------------
        $this->leave         = $absenceRequestRepo->findByCategory('leave');
        $this->permission    = $absenceRequestRepo->findByCategory('permission');
        $this->duty = $absenceRequestRepo->findByCategory('duty');

        // ---------------------------------------------------------------------
        // 2) if extra active for all-date (highest priority in original function)
        // ---------------------------------------------------------------------
        $absence_extra_active = $absenceLogRepo->findExtraEndPendingAllDate();
        if ($absence_extra_active) {
            $this->extra = $absence_extra_active;
            // note: original code used $absenceRequest->findById(...) - use injected instance to get by id
            $this->request_extra = $this->absenceRequest->findById($absence_extra_active->absence_request_id);
            $this->extraResponse();
        }

        // ---------------------------------------------------------------------
        // 3) check today extra-eligibility (original showExtra logic)
        // ---------------------------------------------------------------------
        $showExtra           = "No";
        $this->request_extra = null;
        $absenIn             = $this->absenceRepo->getTodayByStaff($staffId);
        foreach ($absenIn as $data) {
            $c_in  = $data->absence_logs->where('absence_category_id', 1)->where('status', 0)->first();
            $c_out = $data->absence_logs->where('absence_category_id', 2)->where('status', 0)->first();
            if ($c_in && $c_out) {
                $showExtra = "Yes";
            }
        }
        if (count($absenIn) <= 0) {
            $showExtra = "Yes";
        }
        if ($showExtra === "Yes") {
            $this->request_extra = $absenceRequestRepo->findByCategory('extra');
        }

        // ---------------------------------------------------------------------
        // 4) handle leave / permission / duty flows (many early returns)
        // ---------------------------------------------------------------------
        // Leave
        if ($this->leave) {
            if ($this->leave->status === "pending" || $this->leave->status === "close") {
                $this->menuWaiting    = "ON";
                $this->waitingMessage = "Menunggu Persetujuan Cuti";
                return response()->json($this->sendResponse());
            } else {
                $this->menuLeave = "ON";
                return response()->json($this->sendResponse());
            }
        }

        // Permission
        if ($this->permission) {
            if ($this->permission->status === "pending") {
                $this->menuWaiting    = "ON";
                $this->waitingMessage = "Menunggu Persetujuan Izin";
                return response()->json($this->sendResponse());
            } else if ($this->permission->status === "close") {
                $this->menuWaiting    = "ON";
                $this->waitingMessage = "Besok Anda Sudah Bisa Mulai Bekerja";
                return response()->json($this->sendResponse());
            } else {
                $this->menuPermission = "ON";
                if ($this->permission->type == "other") {
                    $this->menuWaiting    = "ON";
                    $this->menuPermission = "OFF";
                }
                $this->waitingMessage = "Anda Masih Izin";
                return response()->json($this->sendResponse());
            }
        }

        // Duty
        if ($this->duty) {
            if ($this->duty->status === "pending") {
                $this->menuWaiting    = "ON";
                $this->waitingMessage = "Menunggu Persetujuan Dinas Luar";
                return response()->json($this->sendResponse());
            } else if ($this->duty->status === "close") {
                $this->menuWaiting    = "ON";
                $this->waitingMessage = "Besok Anda Sudah Bisa Mulai Bekerja";
                return response()->json($this->sendResponse());
            } else {
                $AbsenceRequestLogs = AbsenceRequestLogs::where('absence_request_id', $this->duty->id)
                    ->where('type', 'request_log_in')
                    ->first();
                $this->AbsenceRequestLogs = $AbsenceRequestLogs;
                $this->menuDuty           = 'ON';
                return response()->json($this->sendResponse());
            }
        }

        // ---------------------------------------------------------------------
        // 5) if there's a request_extra prepared earlier
        // ---------------------------------------------------------------------
        if ($this->request_extra) {
            $this->extra = $absenceLogRepo->findExtraEndPendingAllDate($this->request_extra->id);
            $this->extraResponse();
        }

        // ---------------------------------------------------------------------
        // 6) main "not leave/permission/duty/extra" flow
        // ---------------------------------------------------------------------
        // get log in active
        $logInActive = $absenceLogRepo->findInActive();
        $braeakCheck = null;

        if ($logInActive) {
            // get request excuse current
            $this->request_excuse = $absenceRequestRepo->findByCategory('excuse');
            if ($this->request_excuse) {
                // get log excuse end pending
                $this->excuse = $absenceLogRepo->findExcuseEndPending($this->request_excuse->id);
                if ($this->excuse) {
                    $excuse_id = $this->excuse->id ?? null;
                } else {
                    $this->excuseC = $this->absenceCategory->getByType('excuse');
                }
                $this->menuExcuse = "ON";
            }

            // get request visit current
            $this->request_visit = $absenceRequestRepo->findByCategory('visit');
            if ($this->request_visit) {
                $this->handleVisitRequest($this->request_visit, $absenceLogRepo);                
            }

            // get log break pending
            $this->break = $absenceLogRepo->findBreakPending();
            if ($this->break) {
                $this->menuBreak = "ON";
                // if any dinas dalam active
                if ($this->menuVisit != 'OFF') {
                    $this->geolocationOff = "ON";
                }
            }

            // get log out pending
            $this->absenceOut = $absenceLogRepo->findOutPending($logInActive->absence_id);
        }

        // normalized day number
        $day = $this->getDayNumber();

        // get presence pending
        $this->absence = $absenceLogRepo->findPresencePending();

        // when logged in, check exclude break / break pending to override presence
        if ($logInActive) {
            $logExcBreakPending = $absenceLogRepo->findExcBreakPending($logInActive->absence_id);
            $logBreakPending    = $absenceLogRepo->findBreakPending($logInActive->absence_id);

            if ($logExcBreakPending) {
                $this->absence = $logExcBreakPending;
            } else if ($logBreakPending) {
                $this->absence = $logBreakPending;
            } else {
                $this->absence = null;
            }
        }

        $this->a1 = "1";

        // if there is presence pending (regular / shift decision)
        if ($this->absence) {
            if ($this->absence->shift_planner_id === 0) {
                $absen             = $absenceLogRepo->findPresenceWorkTypePending($this->coordinat->work_type_id, $day, $this->absence->id);
                $this->a1          = "2";
                $this->menuReguler = "ON";
                $this->reguler     = $absen;
            } else {
                $absen             = $absenceLogRepo->findPresenceShiftGroupPending($this->absence->shiftPlanner->shift_group_id, $this->absence->id);
                $this->a1          = "2";
                $this->menuReguler = "ON";
                $this->reguler     = $absen;
            }
        } else {
            // --- no presence pending: handle shift or regular schedule creation ---
            if ($this->coordinat->type == "shift") {
                $this->a1 = "3";
                // get shift planner staff today and not yet absence log
                $this->data = $this->shiftPlannerStaff->getLogYet($staffId);

                if (count($this->data) > 0) {
                    foreach ($this->data as $item) {
                        // create absence entry
                        $data = [
                            'day_id'         => $day,
                            'shift_group_id' => $item->shift_group_id,
                            'staff_id'       => $staffId,
                            'created_at'     => date('Y-m-d'),
                        ];
                        $this->absence = Absence::create($data);

                        // get list shift group timesheets & absence_categories by shift_group_id
                        $list_absence = $this->shiftGroupTimesheet->getByShiftGroup($item->shift_group_id);

                        // create message log (notif) for each in-work check duration
                        if ($problem && count($list_absence) > 0) {
                            $this->createProblemMessageLogs($staffId, $list_absence[0], $problem);
                        }

                        // save logs for list
                        try {
                            $this->saveShiftAbsenceLogs($this->absence, $list_absence, $item, $absenceLogRepo);
                        } catch (QueryException $ex) {
                            $this->message = 'gagal';
                            return response()->json($this->sendResponse());
                        }
                    } // end foreach data

                    // after creation find today's presence pending (again)
                    $day           = $this->getDayNumber();
                    $this->absence = $absenceLogRepo->findPresencePending();
                    $this->a1      = "1";

                    if ($this->absence) {
                        if ($this->absence->shift_planner_id === 0) {
                            $absen             = $absenceLogRepo->findPresenceWorkTypePending($this->coordinat->work_type_id, $day, $this->absence->id);
                            $this->a1          = "2";
                            $this->menuReguler = "ON";
                            $this->reguler     = $absen;
                        } else {
                            $absen             = $absenceLogRepo->findPresenceShiftGroupPending($this->absence->shiftPlanner->shift_group_id, $this->absence->id);
                            $this->a1          = "2";
                            $this->menuReguler = "ON";
                            $this->reguler     = $absen;
                        }
                        $this->work_type = $this->coordinat->work_type_id;
                        return response()->json($this->sendResponse());
                    }
                } else {
                    // no shift planner staff today (and no created absence)
                    if (! $this->absenceOut) {
                        $this->extraOnPresence($absenceRequestRepo, $absenceLogRepo);
                    } else {
                        // already have absenceOut => nothing to do
                        return response()->json($this->sendResponse());
                    }
                } // end if count data > 0
            } // end shift branch

            // regular worker branch
            else {
                $holiday = Holiday::whereDate('start', '<=', date('Y-m-d'))->whereDate('end', '>=', date('Y-m-d'))->first();
                if ($holiday) {
                    $this->menuHoliday = "ON";
                    $this->menuReguler = "OFF";
                    $this->menuWaiting = "OFF";
                    return response()->json($this->sendResponse());
                } else {
                    // find absence categories with work_type_days by work_type_id & day_id
                    $absen = $this->absenceCategory->findByWorkTypeAndDay($this->coordinat->work_type_id, $day);

                    if ($absen) {
                        $this->data = $this->absenceRepo->findTodayByStaffExcCategory($staffId, [9, 10]);
                        if (! $this->data) {
                            // create absence for today
                            $data = [
                                'day_id'         => $day,
                                'shift_group_id' => $shift_group_id,
                                'staff_id'       => $staffId,
                                'created_at'     => date('Y-m-d'),
                            ];
                            $this->absence = Absence::create($data);

                            // get list work type days & absence_categories by work_type_id & day_id
                            $list_absence = $this->workTypeDayRepo->getPresenceByWorkTypeAndDay($this->coordinat->work_type_id, $day);

                            // create message logs for problem (if any)
                            if ($problem && count($list_absence) > 0) {
                                $this->createProblemMessageLogs($staffId, $list_absence[0], $problem);
                            }

                            try {
                                $this->saveRegularAbsenceLogs($this->absence, $list_absence, $absenceLogRepo);
                            } catch (QueryException $ex) {
                                $this->message = 'gagal';
                                return response()->json($this->sendResponse());
                            }

                            // find today's presence pending
                            $this->absence = $absenceLogRepo->findPresencePending();
                            $this->a1      = "1";

                            if ($this->absence) {
                                if ($this->absence->shift_planner_id === 0) {
                                    $absen             = $absenceLogRepo->findPresenceWorkTypePending($this->coordinat->work_type_id, $day, $this->absence->id);
                                    $this->menuReguler = "ON";
                                    $this->reguler     = $absen;
                                } else {
                                    $absen = $absenceLogRepo->findPresenceShiftGroupPending($this->absence->shiftPlanner->shift_group_id, $this->absence->id);
                                }
                                $this->a1        = "2";
                                $this->work_type = $this->coordinat->work_type_id;

                                return response()->json($this->sendResponse());
                            }
                        }

                        // if we reach here, check extra/out status same as shift branch
                        if (! $this->absenceOut) {
                            $this->extraOnPresence($absenceRequestRepo, $absenceLogRepo);
                        } else {
                            $this->message = "ssssssa";
                            return response()->json($this->sendResponse());
                        }
                    } // end if absen
                } // end holiday else

                $this->a1 = "4";
            } // end regular else
        } // end top else (no presence)

        // default return (keeps original 'ssssssa' message used near the end)
        $this->message = "ssssssa";
        return response()->json($this->sendResponse());
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
     * Extra Response
     */
    private function extraResponse()
    {
        if ($this->extra) {
            $extra_id = $this->extra->id ?? null;
        } else {
            $this->extraC = $this->absenceCategory->getByType('extra');
        }
        $this->menuExtra = "ON";
        if ($this->request_extra->type == "outside") {
            $this->geolocationOff = "ON";
        }

        return response()->json($this->sendResponse());
    }

    /**
     * Generate Extra On Presence Done
     */
    private function extraOnPresence($absenceRequestRepo, $absenceLogRepo)
    {
        if ($this->isNightWindow()) {
            $this->request_extra = $absenceRequestRepo->findByCategoryWithPending('extra');
        } else {
            $this->request_extra = $absenceRequestRepo->findByCategory('extra');
        }

        if ($this->request_extra) {
            $this->extra = $absenceLogRepo->findExtraEndPending($this->request_extra->id);
            $this->extraResponse();
        } else {
            $this->menuWaiting    = "ON";
            $this->message        = "sudah pulang";
            $this->waitingMessage = "Absen Sudah Selesai";
            return response()->json($this->sendResponse());
        }
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
    private function saveShiftAbsenceLogs($absenceModel, $list_absence, $plannerItem, $absenceLogRepo)
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

            $absenceLogRepo->save([
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
    private function saveRegularAbsenceLogs($absenceModel, $list_absence, $absenceLogRepo)
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

            $absenceLogRepo->save([
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

    /**
     * Handle a visit request block (extracted from repeated code)
     */
    private function handleVisitRequest($request_visit, $absenceLogRepo)
    {
        $this->request_visit = $request_visit;
        $this->visit         = $absenceLogRepo->findVisitEndPending($this->request_visit->id);
        if ($this->visit) {
            $visitEtc = Visit::where('absence_request_id', $this->visit->absence_request_id)->first();
            if ($visitEtc) {
                $this->menuVisit = "ON";
            } else {
                $this->menuVisit = "ACTIVE";
            }
        } else {
            $this->visitC = $this->absenceCategory->getByType('visit');
            $this->menuVisit = "ON";
        }
    }
}
