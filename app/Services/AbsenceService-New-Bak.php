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

    public function __construct(private AbsenceLogRepository $absenceLog, private AbsenceRequestRepository $absenceRequest, private ShiftPlannerStaffRepository $shiftPlannerStaff, private ShiftGroupTimesheetRepository $shiftGroupTimesheet, private AbsenceCategoryRepository $absenceCategory, private AbsenceRepository $absenceRepo, private WorkTypeDayRepository $workTypeDayRepo)
    {}

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
     * Get menu data for a staff member.
     *
     * @param int $staffId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuData($staffId, $shift_group_id)
    {
        //base filter absence log
        $absenceLogRepo     = new AbsenceLogRepository($staffId);
        $absenceRequestRepo = new AbsenceRequestRepository($staffId);

        //set geofence off if there is geolocation_off, forget, AdditionalTime with approve status
        $this->geolocationOff = ($absenceRequestRepo->findByCategory('geolocation_off') || $absenceRequestRepo->findByCategory('forget') || $absenceRequestRepo->findByCategory('AdditionalTime')) ? 'ON' : 'OFF';

        $this->coordinat = WorkUnit::join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $staffId)->first();
        $this->lat    = $this->coordinat->lat;
        $this->lng    = $this->coordinat->lng;
        $this->radius = $this->coordinat->radius;

        $Rlocation = $absenceRequestRepo->findByCategory('location') ? $absenceRequestRepo->findByCategoryNotIn('location', [['work_unit_id' => 'approve']]) : null;
        if ($Rlocation) {
            $this->lat    = $Rlocation->lat;
            $this->lng    = $Rlocation->lng;
            $this->radius = $Rlocation->radius;
        }

        $staff_special = StaffSpecial::select('staff_specials.*')
            ->where('staff_id', $staffId)->whereDate('expired_date', '>=', date('Y-m-d'))->first();
        if ($staff_special) {
            $this->fingerfrint = $staff_special->fingerprint;
            $this->selfie      = $staff_special->camera;
            $this->gps         = $staff_special->gps;
        }

        if ($this->gps == "OFF") {
            $this->geolocationOff = "ON";
        }
        // find in-work check duration by staff
        $problem = AbsenceProblem::where('id', $this->coordinat->absence_problem_id)->first();
        $menu    = "";

        // check if there is any leave request for today
        $this->leave = $absenceRequestRepo->findByCategory('leave') ? $absenceRequestRepo->findByCategory('leave') : null;

        // check if there is any permission request for today
        $this->permission = $absenceRequestRepo->findByCategory('permission') ? $absenceRequestRepo->findByCategory('permission') : null;

        // get request visit current
        $this->request_visit = $absenceRequestRepo->findByCategory('visit') ? $absenceRequestRepo->findByCategory('visit') : null;
        if ($this->request_visit) {
            $this->visit = $absenceLogRepo->findVisitEndPending($this->request_visit->id);
            if ($this->visit) {
                $visit_id = $this->visit->id;
                $visitEtc = Visit::where('absence_request_id', $this->visit->absence_request_id)->first();
                if ($visitEtc) {
                    $this->menuVisit = "ON";
                } else {
                    $this->menuVisit = "ACTIVE";
                }
            } else {
                $this->visitC = $this->absenceCategory->getByType('visit');
            }
        }

        // check if there is any duty request for today
        $this->duty = $absenceRequestRepo->findByCategory('duty') ? $absenceRequestRepo->findByCategory('duty') : null;

        // check if there is any extra request with log end pending for all date
        $absence_extra_active = $absenceLogRepo->findExtraEndPendingAllDate();
        if ($absence_extra_active) {
            $menu = 'OFF';

            $this->extra = $absence_extra_active;

            $this->request_extra = $absenceRequest->findById($absence_extra_active->absence_request_id);

            if ($this->extra) {
                $extra_id = $this->extra->id;
            } else {
                $this->extraC = $this->absenceCategory->getByType('extra');
            }
            $this->menuExtra = "ON";
            // }
            if ($this->request_extra->type == "outside") {
                $this->geolocationOff = "ON";
            }

            $this->sebelum = "yaa";
            return response()->json($this->sendResponse());
        }

        // check if there is any extra request for today
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
        if ($showExtra == "Yes") {
            $this->request_extra = $absenceRequestRepo->findByCategory('extra') ? $absenceRequestRepo->findByCategory('extra') : null;
        }

        // cek hari libur
        if ($this->leave) {

            if ($this->leave->status == "pending") {
                $this->menuWaiting    = "ON";
                $this->waitingMessage = "Menunggu Persetujuan Cuti";
                return response()->json($this->sendResponse());
            }
            if ($this->leave->status == "close") {
                $this->menuWaiting    = "ON";
                $this->waitingMessage = "Menunggu Persetujuan Cuti";
                return response()->json($this->sendResponse());
            } else {
                $this->menuLeave = "ON";
                return response()->json($this->sendResponse());
            }
        } else if ($this->permission) {
            if ($this->permission->status == "pending") {
                $this->menuWaiting    = "ON";
                $this->waitingMessage = "Menunggu Persetujuan Izin";
                return response()->json($this->sendResponse());
            } else if ($this->permission->status == "close") {
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
        } else if ($this->duty) {

            if ($this->duty->status == "pending") {
                $this->menuWaiting    = "ON";
                $this->waitingMessage = "Menunggu Persetujuan Dinas Luar";
                return response()->json($this->sendResponse());
            } else if ($this->duty->status == "close") {
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
        } else if ($this->request_extra) {
            $menu = 'OFF';

            if ($this->request_extra) {
                $this->extra = $absenceLogRepo->findExtraEndPendingAllDate($this->request_extra->id);
            }
            if ($this->extra) {
                $extra_id = $this->extra->id;
            } else {
                $this->extraC = $this->absenceCategory->getByType('extra');
            }

            $this->menuExtra = "ON";
            // }
            if ($this->request_extra->type == "outside") {
                $this->geolocationOff = "ON";
            }

            return response()->json($this->sendResponse());
        }
        // if not leave, permission, duty, extra
        else {

            // get log in active
            $logInActive = $absenceLogRepo->findInActive();

            $braeakCheck = null;
            // if log in active
            if ($logInActive) {
                // get request excuse current
                $this->request_excuse = $absenceRequestRepo->findByCategory('excuse') ? $absenceRequestRepo->findByCategory('excuse') : null;
                if ($this->request_excuse) {
                    //get log excuse end pending
                    $this->excuse = $absenceLogRepo->findExcuseEndPending($this->request_excuse->id);

                    if ($this->excuse) {
                        $excuse_id = $this->excuse->id;
                    } else {
                        $this->excuseC = $this->absenceCategory->getByType('excuse');
                    }
                    $this->menuExcuse = "ON";
                }

                //get request visit current
                $this->request_visit = $absenceRequestRepo->findByCategory('visit') ? $absenceRequestRepo->findByCategory('visit') : null;
                if ($this->request_visit) {
                    //get log visit end pending
                    $this->visit = $absenceLogRepo->findVisitEndPending($this->request_visit->id);

                    if ($this->visit) {
                        $visit_id = $this->visit->id;
                        $visitEtc = Visit::where('absence_request_id', $this->visit->absence_request_id)->first();
                        if ($visitEtc) {
                            $this->menuVisit = "ON";
                        } else {
                            $this->menuVisit = "ACTIVE";
                            // $this->menuVisit = "ON";
                        }
                    } else {
                        $this->visitC    = $this->absenceCategory->getByType('visit');
                        $this->menuVisit = "ON";
                    }
                }

                // get log break pending
                $this->break = $absenceLogRepo->findBreakPending();

                if ($this->break) {
                    $this->menuBreak = "ON";
                    //if any dinas dalam active
                    if ($this->menuVisit != 'OFF') {
                        $this->geolocationOff = "ON";
                    }
                }

                // get log out pending
                $this->absenceOut = $absenceLogRepo->findOutPending($logInActive->absence_id);
            }

            if (date('w') == '0') {
                $day = '7';
            } else {
                $day = date('w');
            }

            // get log presence pending
            $this->absence = $absenceLogRepo->findPresencePending();

            if ($logInActive) {
                // get log exclude break pending
                $logExcBreakPending = $absenceLogRepo->findExcBreakPending($logInActive->absence_id);

                // get log break pending
                $logBreakPending = $absenceLogRepo->findBreakPending($logInActive->absence_id);

                if ($logExcBreakPending) {
                    $this->absence = $logExcBreakPending;
                } else if ($logBreakPending) {
                    $this->absence = $logBreakPending;
                } else {
                    $this->absence = null;
                }
            }

            $this->a1 = "1";

            // if there is log presence pending
            if ($this->absence) {
                //if regular get log with work_type_days start & end
                if ($this->absence->shift_planner_id === 0) {
                    $absen             = $absenceLogRepo->findPresenceWorkTypePending($this->coordinat->work_type_id, $day, $this->absence->id);
                    $this->a1          = "2";
                    $this->menuReguler = "ON";
                    $this->reguler     = $absen;
                } else {
                    //if shift get log with shift_group_timesheets start & end
                    $absen = $absenceLogRepo->findPresenceShiftGroupPending($this->absence->shiftPlanner->shift_group_id, $this->absence->id);

                    $this->a1          = "2";
                    $this->menuReguler = "ON";
                    $this->reguler     = $absen;
                }
            }
            // if no log presence pending
            else {
                // if shift
                if ($this->coordinat->type == "shift") {
                    $this->a1 = "3";
                    // get shift planner staff today and not yet absence log
                    $this->data = $this->shiftPlannerStaff->getLogYet($staffId);
                    //if there is shift planner staff today and not yet absence log
                    if (count($this->data) > 0) {
                        foreach ($this->data as $item) {

                            $data = [
                                'day_id'         => $day,
                                'shift_group_id' => $item->shift_group_id,
                                'staff_id'       => $staffId,
                                'created_at'     => date('Y-m-d'),
                            ];
                            $this->absence = Absence::create($data);
                            //get list shift group timesheets & absence_categories by shift_group_id
                            $list_absence = $this->shiftGroupTimesheet->getByShiftGroup($item->shift_group_id);

                            // create message log (notif) for each in-work check duration
                            if ($problem) {
                                $str_date = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . $list_absence[0]->time)));
                                $exp_date = date("Y-m-d H:i:s", strtotime('+ ' . (($list_absence[0]->duration - $problem->duration) * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));

                                $i = 0;
                                // looping insert message log while in-work check duration
                                while ($str_date < $exp_date) {
                                    $i        = +$problem->duration;
                                    $str_date = date("Y-m-d H:i:s", strtotime('+ ' . $i * 60 . ' minutes', strtotime($str_date)));
                                    // $d[] = [$str_date];
                                    $message = "Anda Dalam Pengawasan, Buka Untuk Absen Lokasi";
                                    MessageLog::create([
                                        'staff_id'   => $staffId,
                                        'memo'       => $message,
                                        'type'       => 'check',
                                        'status'     => 'pending',
                                        'created_at' => $str_date,
                                    ]);
                                }
                            }

                            // try save absence log for each list
                            try {
                                // Base data always taken from the first element
                                $baseDuration    = $list_absence[0]->duration;
                                $baseDurationExp = $list_absence[0]->duration_exp;
                                $baseTime        = strtotime(date('Y-m-d ' . $list_absence[0]->time));

                                $timein       = date("Y-m-d H:i:s", $baseTime);
                                $timeout      = date("Y-m-d H:i:s", strtotime("+{$baseDuration} minutes", $baseTime));
                                $expired_date = date(
                                    "Y-m-d H:i:s",
                                    strtotime('+' . ($baseDuration + $baseDurationExp) . ' minutes', $baseTime)
                                );

                                foreach ($list_absence as $index => $absenceItem) {

                                    // Status logic stays the same
                                    $status = 0;
                                    if ($index === count($list_absence) - 1) {
                                        $status = 1;
                                    } elseif ($index === 2 && $absenceItem->type === "break") {
                                        $status = 1;
                                    }

                                    // Start date logic (based on queue)
                                    if ($absenceItem->queue == 1) {
                                        $start_date = date("Y-m-d H:i:s", strtotime("-{$baseDurationExp} minutes", $baseTime));
                                    } else {
                                        $start_date = date("Y-m-d H:i:s", strtotime("+{$baseDuration} minutes", $baseTime));
                                    }

                                    $absenceLogRepo->save([
                                        'absence_id'               => $this->absence->id,
                                        'shift_planner_id'         => $item->shift_planner_id,
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
                            } catch (QueryException $ex) {
                                $this->message = 'gagal';
                                return response()->json($this->sendResponse());
                            }

                        }
                        // test start
                        if (date('w') == '0') {
                            $day = '7';
                        } else {
                            $day = date('w');
                        }

                        // find today's presence pending
                        $this->absence = $absenceLogRepo->findPresencePending();
                        $this->a1      = "1";
                        $absen         = "";
                        // if there is log presence pending
                        if ($this->absence) {
                            if ($this->absence->shift_planner_id === 0) {
                                $absen             = $absenceLogRepo->findPresenceWorkTypePending($this->coordinat->work_type_id, $day, $this->absence->id);
                                $this->a1          = "2";
                                $this->menuReguler = "ON";
                                $this->reguler     = $absen;
                            } else {
                                //if shift get log with shift_group_timesheets start & end
                                $absen             = $absenceLogRepo->findPresenceShiftGroupPending($this->absence->shiftPlanner->shift_group_id, $this->absence->id);
                                $this->reguler     = $absen;
                                $this->a1          = "2";
                                $this->menuReguler = "ON";
                            }

                            $this->work_type = $this->coordinat->work_type_id;
                            return response()->json($this->sendResponse());
                        }
                    } else {
                        // if there is no shift planner staff today and not yet absence log
                        if (! $this->absenceOut) {
                            // get request extra current
                            if (date('Y-m-d H:i:s') > date('Y-m-d 21:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 23:59:59') || date('Y-m-d H:i:s') > date('Y-m-d 01:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 06:00:00')) {
                                $this->request_extra = $absenceRequestRepo->findByCategoryWithPending('extra') ? $absenceRequestRepo->findByCategoryWithPending('extra') : null;
                            } else {
                                $this->request_extra = $absenceRequestRepo->findByCategory('extra') ? $absenceRequestRepo->findByCategory('extra') : null;
                            }

                            // if there is any extra request for today
                            if ($this->request_extra) {
                                $menu = 'OFF';
                                if ($this->request_extra) {
                                    $this->extra = $absenceLogRepo->findExtraEndPending($this->request_extra->id);
                                    if ($this->extra) {
                                        $extra_id = $this->extra->id;
                                    } else {
                                        $this->extraC = $this->absenceCategory->getByType('extra');
                                    }
                                    $this->menuExtra = "ON";
                                }
                                if ($this->request_extra->type == "outside") {
                                    $this->geolocationOff = "ON";
                                }

                                return response()->json($this->sendResponse());
                            } else {
                                $this->menuWaiting    = "ON";
                                $this->message        = "sudah pulang";
                                $this->waitingMessage = "Absen Sudah Selesai";
                                return response()->json($this->sendResponse());
                            }
                        } else {
                            // if there is shift planner staff today and already absence log
                            return response()->json($this->sendResponse());
                        }
                    }
                }
                // if regular
                else {
                    // get absence today and not yet absence log out
                    $holiday = Holiday::whereDate('start', '<=', date('Y-m-d'))->whereDate('end', '>=', date('Y-m-d'))->first();
                    // if holiday
                    if ($holiday) {
                        $menu = 'OFF';
                        return response()->json($this->sendResponse());
                    } else {
                        //find absence categories with work_type_days by work_type_id & day_id
                        $absen = $this->absenceCategory->findByWorkTypeAndDay($this->coordinat->work_type_id, $day);

                        // if there is absence categories with work_type_days by work_type_id & day_id
                        if ($absen) {
                            $this->data = $this->absenceRepo->findTodayByStaffExcCategory($staffId, [9, 10]);
                            if (! $this->data) {
                                $data = [
                                    'day_id'         => $day,
                                    'shift_group_id' => $shift_group_id,
                                    'staff_id'       => $staffId,
                                    'created_at'     => date('Y-m-d'),
                                ];
                                $this->absence = Absence::create($data);
                                //get list work type days & absence_categories by work_type_id & day_id
                                $list_absence = $this->workTypeDayRepo->getPresenceByWorkTypeAndDay($this->coordinat->work_type_id, $day);

                                // create message log (notif) for each in-work check duration
                                if ($problem) {
                                    $str_date = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . $list_absence[0]->time)));
                                    $exp_date = date("Y-m-d H:i:s", strtotime('+ ' . (($list_absence[0]->duration - $problem->duration) * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));

                                    $i = 0;
                                    while ($str_date < $exp_date) {
                                        $i        = +$problem->duration;
                                        $str_date = date("Y-m-d H:i:s", strtotime('+ ' . $i * 60 . ' minutes', strtotime($str_date)));
                                        // $d[] = [$str_date];
                                        $message = "Anda Dalam Pengawasan, Buka Untuk Absen Lokasi";
                                        MessageLog::create([
                                            'staff_id'   => $staffId,
                                            'memo'       => $message,
                                            'type'       => 'check',
                                            'status'     => 'pending',
                                            'created_at' => $str_date,
                                        ]);
                                    }
                                }
                                $expired_date = date('Y-m-d H:i:s');
                                try {
                                    foreach ($list_absence as $n => $item) {
                                        $baseTime    = strtotime(date('Y-m-d ' . $list_absence[0]->time));
                                        $duration    = $list_absence[0]->duration;
                                        $durationExp = $list_absence[0]->duration_exp;

                                        $timein       = date("Y-m-d H:i:s", $baseTime);
                                        $timeout      = date("Y-m-d H:i:s", strtotime("+{$duration} minutes", $baseTime));
                                        $expired_date = date("Y-m-d H:i:s", strtotime("+" . ($duration + $durationExp) . " minutes", $baseTime));

                                        $start_date = $item->queue == 1
                                            ? date("Y-m-d H:i:s", strtotime("-{$durationExp} minutes", $baseTime))
                                            : date("Y-m-d H:i:s", strtotime("+{$duration} minutes", $baseTime));

                                        $absenceLogRepo->save([
                                            'absence_id'          => $this->absence->id,
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

                                    // find today's presence pending
                                    $this->absence = $absenceLogRepo->findPresencePending();
                                    $this->a1      = "1";
                                    $absen         = "";

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
                                } catch (QueryException $ex) {
                                    $this->message = 'gagal';
                                    return response()->json($this->sendResponse());
                                }

                            }

                            if (! $this->absenceOut) {
                                if (date('Y-m-d H:i:s') > date('Y-m-d 21:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 23:59:59') || date('Y-m-d H:i:s') > date('Y-m-d 01:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 06:00:00')) {
                                    $this->request_extra = $absenceRequestRepo->findByCategoryWithPending('extra') ? $absenceRequestRepo->findByCategoryWithPending('extra') : null;
                                } else {
                                    $this->request_extra = $absenceRequestRepo->findByCategory('extra') ? $absenceRequestRepo->findByCategory('extra') : null;
                                }

                                if ($this->request_extra) {
                                    $menu = 'OFF';
                                    if ($this->request_extra) {
                                        $this->extra = $absenceLogRepo->findExtraEndPending($this->request_extra->id);
                                        if ($this->extra) {
                                            $extra_id = $this->extra->id;
                                        } else {
                                            $this->extraC = $this->absenceCategory->getByType('extra');
                                        }
                                        $this->menuExtra = "ON";
                                    }
                                    if ($this->request_extra->type == "outside") {
                                        $this->geolocationOff = "ON";
                                    }

                                    return response()->json($this->sendResponse());
                                } else {
                                    $this->menuWaiting    = "ON";
                                    $this->message        = "sudah pulang";
                                    $this->waitingMessage = "Absen Sudah Selesai";
                                    return response()->json($this->sendResponse());
                                }
                            } else {
                                $this->message = "ssssssa";
                                return response()->json($this->sendResponse());
                            }
                        }
                        $this->a1 = "4";
                    }
                }
            }

            // return response
            $this->message = "ssssssa";
            return response()->json($this->sendResponse());
        }
    }
}
