<?php
namespace App\Services;

use App\Absence;
use App\AbsenceLog;
use App\AbsenceProblem;
use App\AbsenceRequest;
use App\AbsenceRequestLogs;
use App\Absence_categories;
use App\Holiday;
use App\MessageLog;
use App\ShiftPlannerStaffs;
use App\StaffSpecial;
use App\Visit;
use App\WorkTypeDays;
use App\WorkUnit;
use Illuminate\Database\QueryException;

class AbsenceService
{
    /**
     * Find the first AbsenceLog in a collection matching given criteria.
     *
     * @param \Illuminate\Support\Collection $logs
     * @param array $criteria ['type' => 'visit','queue' => 2,'statuses' => [0,1,3]]
     */
    function findAbsenceLog($logs, array $criteria = [])
    {
        // first filter by criteria
        $filtered = $logs->filter(function ($log) use ($criteria) {
            // type filter (support single or array)
            if (isset($criteria['type'])) {
                $types = (array) $criteria['type'];
                if (isset($criteria['type_not_in']) && $criteria['type_not_in'] === true) {
                    if (in_array(optional($log->category)->type, $types, true)) {
                        return false;
                    }
                } else {
                    if (! in_array(optional($log->category)->type, $types, true)) {
                        return false;
                    }
                }
            }

            // queue filter (support single or array)
            if (isset($criteria['queue'])) {
                $queues = (array) $criteria['queue'];
                if (isset($criteria['queue_not_in']) && $criteria['queue_not_in'] === true) {
                    if (in_array(optional($log->category)->queue, $queues, true)) {
                        return false;
                    }
                } else {
                    if (! in_array(optional($log->category)->queue, $queues, true)) {
                        return false;
                    }
                }
            }

            // status filter
            if (isset($criteria['status']) && ! in_array($log->status, (array) $criteria['status'], true)) {
                return false;
            }

            // absence_request_id filter
            if (isset($criteria['absence_request_id']) &&
                $log->absence_request_id !== $criteria['absence_request_id']) {
                return false;
            }

            // absence_id filter
            if (isset($criteria['absence_id']) &&
                $log->absence_id !== $criteria['absence_id']) {
                return false;
            }

            // NEW: work_type_id filter (checks nested category.workTypeDays.workType.id)
            if (isset($criteria['work_type_id'])) {
                $ids         = (array) $criteria['work_type_id'];
                $hasWorkType = optional($log->category)->workTypeDays?->contains(
                    fn($day) => in_array(optional($day->workType)->id, $ids, true)
                );
                if (! $hasWorkType) {
                    return false;
                }
            }

            return true; // passed all filters
        });

        // default order field and direction
        $orderBy  = $criteria['order_by'] ?? 'id';
        $orderDir = $criteria['order_dir'] ?? 'DESC';

        // sort the filtered collection
        $filtered = $orderDir === 'DESC'
            ? $filtered->sortByDesc($orderBy)
            : $filtered->sortBy($orderBy);

        return $filtered->values(); // reset keys
    }

    // return response()->json([
    //     'absence_request' => $absence_request,
    //     'geofence_off'    => $geofence_off,
    //     'Rlocation'       => $Rlocation,
    //     'leave'           => $leave,
    //     'permission'      => $permission,
    //     'absence_visit'   => $absence_visit,
    //     'visit'           => $visit,
    // ]);

    /**
     * Get menu data for a staff member.
     *
     * @param int $staffId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuData($staffId, $shift_group_id)
    {
        // untuk menampung data menu
        $reguler       = "";
        $holiday       = "";
        $break         = "";
        $duty          = "";
        $finish        = "";
        $excuse_id     = "";
        $logOutPending = [];

        $excuse = [];
        $visit  = [];
        $duty   = [];
        $extra  = [];

        // mematikan menu
        $menuReguler        = "OFF";
        $menuHoliday        = "OFF";
        $menuBreak          = "OFF";
        $menuExcuse         = "OFF";
        $menuVisit          = "OFF";
        $menuDuty           = "OFF";
        $menuFinish         = "OFF";
        $menuExtra          = "OFF";
        $menuLeave          = "OFF";
        $menuWaiting        = "OFF";
        $menuPermission     = "OFF";
        $geofence_off       = "OFF";
        $geofence_off_break = "OFF";

        // get ID
        $excuseC = [];
        $visitC  = [];
        $extraC  = [];

        $absence_excuse = [];
        $now            = now();

        //base filter absence request
        AbsenceRequest::$baseFilter['staff_id'] = $staffId;
        AbsenceLog::$baseFilter['staff_id']     = $staffId;

        //general request
        $absence_request              = AbsenceRequest::filter(['current' => true])->get()->groupBy('category');
        $absence_request_with_pending = AbsenceRequest::filter(['status' => ['approve', 'active', 'pending']])->get()->groupBy('category');
        $absence_log                  = AbsenceLog::filter(['where_has' => true])->get();
        $absence_log_current_off      = AbsenceLog::filter(['current' => false, 'where_has' => true])->get();

        //set geofence off if there is geolocation_off, forget, AdditionalTime with approve status
        $geofence_off = ($absence_request->has('geolocation_off') || $absence_request->has('forget') || $absence_request->has('AdditionalTime')) ? 'ON' : 'OFF';

        $fingerprint = "ON";
        $camera      = "ON";
        $gps         = "ON";

        $coordinat = WorkUnit::join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $staffId)->first();

        $lat    = $coordinat->lat;
        $lng    = $coordinat->lng;
        $radius = $coordinat->radius;

        $Rlocation = $absence_request->has('location') ? $absence_request['location']->where('work_unit_id', '!=', 'approve')->first() : null;
        if ($Rlocation) {
            $lat    = $Rlocation->lat;
            $lng    = $Rlocation->lng;
            $radius = $Rlocation->radius;
        }

        $staff_special = StaffSpecial::select('staff_specials.*')
            ->where('staff_id', $staffId)->whereDate('expired_date', '>=', date('Y-m-d'))->first();
        if ($staff_special) {
            $fingerprint = $staff_special->fingerprint;
            $camera      = $staff_special->camera;
            $gps         = $staff_special->gps;
        }

        if ($gps == "OFF") {
            $geofence_off = "ON";
        }
        // return $gps;
        $problem = AbsenceProblem::where('id', $coordinat->absence_problem_id)->first();
        $menu    = "";
        $leave   = $absence_request->has('leave') ? $absence_request['leave']->first() : null;

        $permission = $absence_request->has('permission') ? $absence_request['permission']->first() : null;

        // cek apa tanggal ini ada dinas dalam kota
        $absence_visit = $absence_request->has('visit') ? $absence_request['visit']->first() : null;

        if ($absence_visit) {
            $visit = $this->findAbsenceLog($absence_log, ['type' => 'visit', 'queue' => 2, 'status' => [1], 'absence_request_id' => $absence_visit->id])?->first();
            if ($visit) {
                $visit_id = $visit->id;
                $visitEtc = Visit::where('absence_request_id', $visit->absence_request_id)->first();
                if ($visitEtc) {
                    $menuVisit = "ON";
                } else {
                    $menuVisit = "ACTIVE";
                }
            } else {
                $visitC = Absence_categories::where('type', 'visit')->get();
            }
        }

        $duty = $absence_request->has('duty') ? $absence_request['duty']->first() : null;

        // cek ada lembur yang belum selesai start
        $absence_extra_active = $this->findAbsenceLog($absence_log_current_off, ['type' => 'extra', 'queue' => 2, 'status' => [1]])?->first();
        if ($absence_extra_active) {
            $menu = 'OFF';

            $extra = $absence_extra_active;

            $absence_extra = AbsenceRequest::where('id', $absence_extra_active->absence_request_id)
                ->first();

            if ($extra) {
                $extra_id = $extra->id;
            } else {
                $extraC = Absence_categories::where('type', 'extra')->get();
            }
            $menuExtra = "ON";
            // }
            if ($absence_extra->type == "outside") {
                $geofence_off = "ON";
            }

            return response()->json([
                'message'       => 'Success',
                'menu'          => [
                    'menuReguler'    => $menuReguler,
                    'menuHoliday'    => $menuHoliday,
                    'menuBreak'      => $menuBreak,
                    'menuExcuse'     => $menuExcuse,
                    'menuExtra'      => $menuExtra,
                    'menuDuty'       => $menuDuty,
                    'menuFinish'     => $menuFinish,
                    'geolocationOff' => $geofence_off,
                ],
                'sebelum'       => 'yaa',
                'extraC'        => $extraC,
                'extra'         => $extra,
                'request_extra' => $absence_extra,
                'date'          => date('Y-m-d h:i:s'),
                'lat'           => $lat,
                'fingerfrint'   => $fingerprint,
                'selfie'        => $camera,
                'gps'           => $gps,
                'lng'           => $lng,
                'radius'        => $radius,
            ]);
        }

        // cek ada lembur yang belum selsasi end

        $showExtra     = "No";
        $absence_extra = null;
        $absenIn       = Absence::whereDate('created_at', '=', date('Y-m-d'))->where('staff_id', $staffId)->get();
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
            $absence_extra = $absence_request->has('extra') ? $absence_request['extra']->first() : null;
        }

        // cek hari libur
        if ($leave) {

            if ($leave->status == "pending") {
                $menuWaiting = "ON";
                return response()->json([
                    'message'        => 'Success',
                    'menu'           => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak'   => $menuBreak,
                        'menuExcuse'  => $menuExcuse,
                        'menuDuty'    => $menuDuty,
                        'menuFinish'  => $menuFinish,
                        'menuLeave'   => $menuLeave,
                        'menuWaiting' => $menuWaiting,
                    ],
                    'waitingMessage' => "Menunggu Persetujuan Cuti",
                    'leave'          => $leave,
                    'date'           => date('Y-m-d h:i:s'),
                ]);
            }
            if ($leave->status == "close") {
                $menuWaiting = "ON";
                return response()->json([
                    'message'        => 'Success',
                    'menu'           => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak'   => $menuBreak,
                        'menuExcuse'  => $menuExcuse,
                        'menuDuty'    => $menuDuty,
                        'menuFinish'  => $menuFinish,
                        'menuLeave'   => $menuLeave,
                        'menuWaiting' => $menuWaiting,
                    ],
                    'waitingMessage' => "Menunggu Persetujuan Cuti",
                    'leave'          => $leave,
                    'date'           => date('Y-m-d h:i:s'),
                ]);
            } else {
                $menuLeave = "ON";
                return response()->json([
                    'message' => 'Success',
                    'menu'    => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak'   => $menuBreak,
                        'menuExcuse'  => $menuExcuse,
                        'menuDuty'    => $menuDuty,
                        'menuFinish'  => $menuFinish,
                        'menuLeave'   => $menuLeave,
                    ],
                    'leave'   => $leave,
                    'date'    => date('Y-m-d h:i:s'),
                ]);
            }
        } else if ($permission) {
            if ($permission->status == "pending") {
                $menuWaiting = "ON";
                return response()->json([
                    'message'        => 'Success',
                    'menu'           => [
                        'menuReguler'    => $menuReguler,
                        'menuHoliday'    => $menuHoliday,
                        'menuBreak'      => $menuBreak,
                        'menuExcuse'     => $menuExcuse,
                        'menuDuty'       => $menuDuty,
                        'menuFinish'     => $menuFinish,
                        'menuWaiting'    => $menuWaiting,
                        'menuPermission' => $menuPermission,
                    ],
                    'waitingMessage' => "Menunggu Persetujuan Izin",
                    'date'           => date('Y-m-d h:i:s'),
                ]);
            } else if ($permission->status == "close") {
                $menuWaiting = "ON";
                return response()->json([
                    'message'        => 'Success',
                    'menu'           => [
                        'menuReguler'    => $menuReguler,
                        'menuHoliday'    => $menuHoliday,
                        'menuBreak'      => $menuBreak,
                        'menuExcuse'     => $menuExcuse,
                        'menuDuty'       => $menuDuty,
                        'menuFinish'     => $menuFinish,
                        'menuWaiting'    => $menuWaiting,
                        'menuPermission' => $menuPermission,
                    ],
                    'waitingMessage' => "Besok Anda Sudah Bisa Mulai Bekerja",
                    'date'           => date('Y-m-d h:i:s'),
                ]);
            } else {

                $menuPermission = "ON";
                if ($permission->type == "other") {
                    $menuWaiting    = "ON";
                    $menuPermission = "OFF";
                }
                return response()->json([
                    'message'        => 'Success',
                    'menu'           => [
                        'menuReguler'    => $menuReguler,
                        'menuHoliday'    => $menuHoliday,
                        'menuBreak'      => $menuBreak,
                        'menuExcuse'     => $menuExcuse,
                        'menuDuty'       => $menuDuty,
                        'menuFinish'     => $menuFinish,
                        'menuWaiting'    => $menuWaiting,
                        'menuPermission' => $menuPermission,
                    ],
                    'permission'     => $permission,
                    'waitingMessage' => "Anda Masih Izin",
                    'date'           => date('Y-m-d h:i:s'),
                ]);
            }
        } else if ($duty) {

            if ($duty->status == "pending") {
                $menuWaiting = "ON";
                return response()->json([
                    'message'        => 'Success',
                    'fingerfrint'    => $fingerprint,
                    'selfie'         => $camera,
                    'menu'           => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak'   => $menuBreak,
                        'menuExcuse'  => $menuExcuse,
                        'menuDuty'    => $menuDuty,
                        'menuFinish'  => $menuFinish,
                        'menuWaiting' => $menuWaiting,
                    ],
                    'waitingMessage' => "Menunggu Persetujuan Dinas Luar",
                    'duty'           => $duty,
                    'date'           => date('Y-m-d h:i:s'),
                ]);
            } else if ($duty->status == "close") {
                $menuWaiting = "ON";
                return response()->json([
                    'message'        => 'Success',
                    'fingerfrint'    => $fingerprint,
                    'selfie'         => $camera,
                    'menu'           => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak'   => $menuBreak,
                        'menuExcuse'  => $menuExcuse,
                        'menuDuty'    => $menuDuty,
                        'menuFinish'  => $menuFinish,
                        'menuWaiting' => $menuWaiting,
                    ],
                    'waitingMessage' => "Besok Anda Sudah Bisa Mulai Bekerja",
                    'duty'           => $duty,
                    'date'           => date('Y-m-d h:i:s'),
                ]);
            } else {
                $AbsenceRequestLogs = AbsenceRequestLogs::where('absence_request_id', $duty->id)
                    ->where('type', 'request_log_in')
                    ->first();

                $menuDuty = 'ON';
                return response()->json([
                    'message'            => 'Success',
                    'fingerfrint'        => $fingerprint,
                    'selfie'             => $camera,
                    'menu'               => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak'   => $menuBreak,
                        'menuExcuse'  => $menuExcuse,
                        'menuDuty'    => $menuDuty,
                        'menuFinish'  => $menuFinish,
                    ],
                    'AbsenceRequestLogs' => $AbsenceRequestLogs,
                    'duty'               => $duty,
                    'coordinat'          => $coordinat,
                    'date'               => date('Y-m-d h:i:s'),
                ]);
            }
        } else if ($absence_extra) {
            $menu = 'OFF';

            if ($absence_extra) {
                $extra = $this->findAbsenceLog($absence_log_current_off, ['type' => 'extra', 'queue' => 2, 'status' => [1], 'absence_request_id' => $absence_extra->id])?->first();
            }
            if ($extra) {
                $extra_id = $extra->id;
            } else {
                $extraC = Absence_categories::where('type', 'extra')->get();
            }
            $menuExtra = "ON";
            // }
            if ($absence_extra->type == "outside") {
                $geofence_off = "ON";
            }

            return response()->json([
                'message'       => 'Success',
                'menu'          => [
                    'menuReguler'    => $menuReguler,
                    'menuHoliday'    => $menuHoliday,
                    'menuBreak'      => $menuBreak,
                    'menuExcuse'     => $menuExcuse,
                    'menuExtra'      => $menuExtra,
                    'menuDuty'       => $menuDuty,
                    'menuFinish'     => $menuFinish,
                    'geolocationOff' => $geofence_off,
                ],
                'sebelum'       => 'yaa',
                'extraC'        => $extraC,
                'extra'         => $extra,
                'request_extra' => $absence_extra,
                'date'          => date('Y-m-d h:i:s'),
                'lat'           => $lat,
                'fingerfrint'   => $fingerprint,
                'selfie'        => $camera,
                'gps'           => $gps,
                'lng'           => $lng,
                'radius'        => $radius,
            ]);
        }
        // cek jadwal kerja
        else {

            // get log in active
            $logInActive = $this->findAbsenceLog($absence_log, ['type' => 'presence', 'queue' => 1, 'status' => [0]])?->first();
            $braeakCheck = null;
            // if log in active
            if ($logInActive) {
                // get request excuse current
                $absence_excuse = $absence_request->has('excuse') ? $absence_request['excuse']->first() : null;
                if ($absence_excuse) {
                    //get log excuse end pending
                    $excuse = $this->findAbsenceLog($absence_log, ['type' => 'excuse', 'queue' => 2, 'status' => [1], 'absence_request_id' => $absence_excuse->id])?->first();
                    if ($excuse) {
                        $excuse_id = $excuse->id;
                    } else {
                        $excuseC = Absence_categories::where('type', 'excuse')->get();
                    }
                    $menuExcuse = "ON";
                }

                //get request visit current
                $absence_visit = $absence_request->has('visit') ? $absence_request['visit']->first() : null;
                if ($absence_visit) {
                    //get log visit end pending
                    $visit = $this->findAbsenceLog($absence_log, ['type' => 'visit', 'queue' => 2, 'status' => [1], 'absence_request_id' => $absence_visit->id])?->first();
                    if ($visit) {
                        $visit_id = $visit->id;
                        $visitEtc = Visit::where('absence_request_id', $visit->absence_request_id)->first();
                        if ($visitEtc) {
                            $menuVisit = "ON";
                        } else {
                            $menuVisit = "ACTIVE";
                            // $menuVisit = "ON";
                        }
                    } else {
                        $visitC    = Absence_categories::where('type', 'visit')->get();
                        $menuVisit = "ON";
                    }

                    // $menuVisit = "OFF";
                }

                // get log break pending
                $break = $this->findAbsenceLog($absence_log, ['type' => 'break', 'status' => [1], 'order_by' => 'id', 'order_dir' => 'ASC'])?->first();
                if ($break) {
                    $menuBreak = "ON";
                    //if any dinas dalam active
                    if ($menuVisit != 'OFF') {
                        $geofence_off_break = "ON";
                    }
                }

                // get log out pending
                $logOutPending = $this->findAbsenceLog($absence_log, ['type' => 'presence', 'queue' => 2, 'status' => [1], 'absence_id' => $logInActive->absence_id, 'order_by' => 'start_date', 'order_dir' => 'ASC'])?->first();
            }

            if (date('w') == '0') {
                $day = '7';
            } else {
                $day = date('w');
            }

            // get log presence pending
            $logPresencePending = $this->findAbsenceLog($absence_log, ['type' => 'presence', 'status' => [1], 'order_by' => 'start_date', 'order_dir' => 'ASC'])?->first();

            if ($logInActive) {
                // get log exclude break pending
                $logExcBreakPending = $this->findAbsenceLog($absence_log, ['type' => 'break', 'type_not_in' => true, 'status' => [1], 'absence_id' => $logInActive->absence_id, 'order_by' => 'start_date', 'order_dir' => 'ASC'])?->first();

                // get log break pending
                $logBreakPending = $this->findAbsenceLog($absence_log, ['type' => 'break', 'status' => [1], 'absence_id' => $logInActive->absence_id, 'order_by' => 'start_date', 'order_dir' => 'ASC'])?->first();

                if ($logExcBreakPending) {
                    $logPresencePending = $logExcBreakPending;
                } else if ($logBreakPending) {
                    $logPresencePending = $logBreakPending;
                } else {
                    $logPresencePending = null;
                }
            }

            $a1 = "1";

            // jika ada absen hari ini
            if ($logPresencePending) {
                //if regular get log with work_type_days start & end
                if ($logPresencePending->shift_planner_id === 0) {
                    $absen = $this->findAbsenceLog($absence_log, ['type' => 'presence', 'status' => [1], 'id' => $logPresencePending->id, 'work_type_id' => $coordinat->work_type_id])?->first();
                    $a1          = "2";
                    $menuReguler = "ON";
                    $reguler     = $absen;
                } else {
                    //if shift get log with shift_group_timesheets start & end
                    $absen = AbsenceLog::selectRaw('absence_logs.*, absence_categories.type, absence_categories.queue,shift_group_timesheets.start, shift_group_timesheets.end')->leftJoin('absences', 'absences.id', '=', 'absence_logs.absence_id')
                        ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
                        ->join('shift_group_timesheets', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
                        ->where('absence_logs.status', '=', 1)
                        ->where('absence_logs.id', $logPresencePending->id)
                        ->where('absence_categories.type', '=', 'presence')
                        ->orderBy('absence_logs.id', 'DESC')
                        ->first();

                    $a1          = "2";
                    $menuReguler = "ON";
                    $reguler     = $absen;
                }
            }
            // ketika tidak ada absen di tanggal tersebut
            else {
                // cek apa ada shift di tanggal ini
                if ($coordinat->type == "shift") {
                    $a1 = "3";
                    // buat baru start
                    // cek apa sudah ada group absen di tanggal ini
                    $c = ShiftPlannerStaffs::selectRaw('shift_planner_staffs.id as shift_planner_id, shift_planner_staffs.shift_group_id')
                        ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
                        ->leftJoin('absence_logs', 'shift_planner_staffs.id', '=', 'absence_logs.shift_planner_id')
                        ->where('shift_planner_staffs.staff_id', '=', $staffId)
                        ->whereDate('shift_planner_staffs.start', '=', date('Y-m-d'))
                        ->where('absence_logs.id', '=', null)
                        ->orderBy('shift_groups.queue', 'ASC')
                        ->get();

                    if (count($c) > 0) {
                        foreach ($c as $item) {

                            $data = [
                                'day_id'         => $day,
                                'shift_group_id' => $item->shift_group_id,
                                'staff_id'       => $staffId,
                                'created_at'     => date('Y-m-d'),
                            ];
                            $absence      = Absence::create($data);
                            $list_absence = ShiftGroups::selectRaw('duration, duration_exp, absence_categories.queue, type, time, start, absence_category_id,shift_group_timesheets.id as shift_group_timesheet_id ')
                                ->join('shift_group_timesheets', 'shift_group_timesheets.shift_group_id', '=', 'shift_groups.id')
                                ->join('absence_categories', 'shift_group_timesheets.absence_category_id', '=', 'absence_categories.id')
                                ->where('shift_groups.id', $item->shift_group_id)
                                ->where('absence_categories.type', '!=', "break")
                                ->orderBy('absence_categories.queue', 'ASC')
                                ->get();

                            // reminder absen bermesalah start
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

                            // reminder absen bermasalah end

                            // return response()->json([
                            //     'lat' =>  $list_absence,
                            //     'c' => $c
                            // ]);

                            // $expired_date = date('Y-m-d H:i:s');
                            try {
                                for ($n = 0; $n < count($list_absence); $n++) {
                                    $expired_date = date("Y-m-d H:i:s", strtotime('+ ' . (($list_absence[0]->duration + $list_absence[0]->duration_exp) * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                    $timeout      = date("Y-m-d H:i:s", strtotime('+ ' . ($list_absence[0]->duration * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                    $timein       = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . $list_absence[0]->time)));

                                    $status = 0;
                                    if ($n === (count($list_absence) - 1)) {
                                        $status = 1;
                                    } else if ($n === 2 && $list_absence[$n]->type == "break") {
                                        $status = 1;
                                    } else {
                                        $status = 0;
                                    }

                                    // if ($list_absence[$n]->start == "0000-00-00") {
                                    //     $start_date =  null;
                                    // } else {
                                    //     $start_date = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . $list_absence[$n]->start)));
                                    // }
                                    if ($list_absence[$n]->queue == "1") {
                                        $start_date = date("Y-m-d H:i:s", strtotime('- ' . ($list_absence[0]->duration_exp * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                    } else {
                                        $start_date = date("Y-m-d H:i:s", strtotime('+ ' . ($list_absence[0]->duration * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                    }
                                    $upload_image = new AbsenceLog;
                                    // sementara start
                                    $upload_image->absence_id               = $absence->id;
                                    $upload_image->shift_planner_id         = $item->shift_planner_id;
                                    $upload_image->shift_group_timesheet_id = $list_absence[$n]->shift_group_timesheet_id;
                                    $upload_image->timein                   = $timein;
                                    $upload_image->timeout                  = $timeout;

                                    $upload_image->start_date   = $start_date;
                                    $upload_image->expired_date = $expired_date;
                                    // sementara end
                                    $upload_image->created_at          = date('Y-m-d H:i:s');
                                    $upload_image->updated_at          = date('Y-m-d H:i:s');
                                    $upload_image->status              = 1;
                                    $upload_image->absence_category_id = $list_absence[$n]->absence_category_id;
                                    // $upload_image->shift_id = $request->shift_id;

                                    $upload_image->save();
                                }
                            } catch (QueryException $ex) {
                                return response()->json([
                                    'message' => 'gagal',
                                ]);
                            }
                        }
                        // test start
                        if (date('w') == '0') {
                            $day = '7';
                        } else {
                            $day = date('w');
                        }

                        // cek absen, apa ada absen hari ini
                        $absence = AbsenceLog::selectRaw('absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
                            ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                            ->where('staff_id', $staffId)
                            ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                            ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                            ->where('absence_logs.status', '=', 1)
                            ->where('absence_categories.type', '=', 'presence')
                            ->orderBy('absence_logs.start_date', 'ASC')
                            ->first();
                        $a1    = "1";
                        $absen = "";
                        // jika ada absen hari ini
                        if ($absence) {
                            if ($absence->shift_planner_id === 0) {
                                $absen = AbsenceLog::selectRaw('absence_categories.*,absence_logs.id as id, absence_id, work_type_days.start, work_type_days.end')
                                    ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
                                    ->join('work_type_days', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
                                    ->join('work_types', 'work_type_days.work_type_id', '=', 'work_types.id')
                                    ->where('work_types.id', $coordinat->work_type_id)
                                    ->where('absence_logs.id', $absence->id)
                                    ->where('absence_categories.type', '=', 'presence')
                                    ->where('absence_logs.status', '=', 1)
                                    ->first();
                                $a1          = "2";
                                $menuReguler = "ON";
                                $reguler     = $absen;
                            } else {
                                $absen = AbsenceLog::selectRaw('absence_logs.*, absence_categories.type, absence_categories.queue, shift_group_timesheets.start, shift_group_timesheets.end')->leftJoin('absences', 'absences.id', '=', 'absence_logs.absence_id')
                                    ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
                                    ->join('shift_group_timesheets', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
                                    ->where('absence_logs.status', '=', 1)
                                    ->where('absence_logs.id', $absence->id)
                                    ->where('absence_categories.type', '=', 'presence')
                                    ->orderBy('absence_logs.id', 'DESC')
                                    ->first();
                                $reguler     = $absen;
                                $a1          = "2";
                                $menuReguler = "ON";
                            }

                            return response()->json([
                                'lat'         => $lat,
                                'fingerfrint' => $fingerprint,
                                'selfie'      => $camera,
                                'gps'         => $gps,
                                'lng'         => $lng,
                                'radius'      => $radius,
                                'reguler'     => $reguler,
                                'work_type'   => $coordinat->work_type_id,
                                'menu'        => [
                                    'menuReguler'    => $menuReguler,
                                    'menuHoliday'    => $menuHoliday,
                                    'menuBreak'      => $menuBreak,
                                    'menuExcuse'     => $menuExcuse,
                                    'menuDuty'       => $menuDuty,
                                    'menuFinish'     => $menuFinish,
                                    'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                                ],
                                'break'       => $break,
                                'date'        => $coordinat->type,
                                'absence'     => $absence,
                                'tesss'       => $absen,
                                'a1'          => $a1,
                            ]);
                        }

                        // test end
                    } else {

                        if (! $logOutPending) {
                            if (date('Y-m-d H:i:s') > date('Y-m-d 21:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 23:59:59') || date('Y-m-d H:i:s') > date('Y-m-d 01:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 06:00:00')) {
                                $absence_extra = $absence_request_with_pending->has('extra') ? $absence_request_with_pending['extra']->first() : null;
                            } else {
                                $absence_extra = $absence_request->has('extra') ? $absence_request['extra']->first() : null;
                            }

                            if ($absence_extra) {
                                $menu = 'OFF';
                                if ($absence_extra) {
                                    $extra = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                                        ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                                        ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                                        ->where('staff_id', $staffId)
                                        ->where('absence_request_id', $absence_extra->id)
                                        ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                                        ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                                        ->where('absence_logs.status', '=', 1)
                                        ->where('absence_categories.type', '=', 'extra')
                                        ->where('absence_categories.queue', '=', '2')
                                        ->orderBy('absence_logs.id', 'DESC')
                                        ->first();
                                    if ($extra) {
                                        $extra_id = $extra->id;
                                    } else {
                                        $extraC = Absence_categories::where('type', 'extra')->get();
                                    }
                                    $menuExtra = "ON";
                                }
                                if ($absence_extra->type == "outside") {
                                    $geofence_off = "ON";
                                }

                                return response()->json([
                                    'message'       => 'Success',
                                    'menu'          => [
                                        'menuReguler'    => $menuReguler,
                                        'menuHoliday'    => $menuHoliday,
                                        'menuBreak'      => $menuBreak,
                                        'menuExcuse'     => $menuExcuse,
                                        'menuExtra'      => $menuExtra,
                                        'menuDuty'       => $menuDuty,
                                        'menuFinish'     => $menuFinish,
                                        'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                                    ],
                                    'extraC'        => $extraC,
                                    'extra'         => $extra,
                                    'request_extra' => $absence_extra,
                                    'date'          => date('Y-m-d h:i:s'),
                                    'lat'           => $lat,
                                    'fingerfrint'   => $fingerprint,
                                    'selfie'        => $camera,
                                    'gps'           => $gps,
                                    'selfie'        => $camera,
                                    'lng'           => $lng,
                                    'radius'        => $radius,
                                ]);
                            } else {
                                $menuWaiting = "ON";
                                return response()->json([
                                    'message'        => 'Absen Terkirim',
                                    'message'        => 'sudah pulang',
                                    'data'           => $c,
                                    'radius'         => $radius,
                                    'reguler'        => $reguler,
                                    'break'          => $break,
                                    'menu'           => [
                                        'menuBreak'      => $menuBreak,
                                        'menuExcuse'     => $menuExcuse,
                                        'menuReguler'    => $menuReguler,
                                        'menuHoliday'    => $menuHoliday,
                                        'menuDuty'       => $menuDuty,
                                        'menuFinish'     => $menuFinish,
                                        'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                                        'menuWaiting'    => $menuWaiting,

                                    ],
                                    'waitingMessage' => "Absen Sudah Selesai",
                                ]);
                            }
                        } else {
                            return response()->json([
                                'message'        => 'Absen Terkirim',
                                'message'        => 'ssssssa',
                                'excuseC'        => $excuseC,
                                'excuse'         => $excuse,
                                'request_excuse' => $absence_excuse,
                                'visitC'         => $visitC,
                                'visit'          => $visit,
                                'request_visit'  => $absence_visit,
                                'data'           => $c,

                                'lat'            => $lat,
                                'fingerfrint'    => $fingerprint,
                                'selfie'         => $camera,
                                'gps'            => $gps,
                                'lng'            => $lng,
                                'radius'         => $radius,
                                'reguler'        => $reguler,
                                'break'          => $break,
                                'absenceOut'     => $logOutPending,
                                'menu'           => [
                                    'menuBreak'      => $menuBreak,
                                    'menuExcuse'     => $menuExcuse,
                                    'menuReguler'    => $menuReguler,
                                    'menuHoliday'    => $menuHoliday,
                                    'menuVisit'      => $menuVisit,
                                    'menuDuty'       => $menuDuty,
                                    'menuFinish'     => $menuFinish,
                                    'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                                ],
                            ]);
                        }
                    }

                    // buat baru end
                }
                // jika tidak ada shift, dinas keluar kota, libur ataupun cuti, izin, atau sakit(mungkin dipisah untuk pengecekan)
                else {
                    // absence Biasa
                    $holiday = Holiday::whereDate('start', '<=', date('Y-m-d'))->whereDate('end', '>=', date('Y-m-d'))->first();
                    // cek hari libur
                    if ($holiday) {
                        $menu = 'OFF';
                        return response()->json([
                            'message' => 'Success',
                            'menu'    => [
                                'menuReguler'    => $menuReguler,
                                'menuHoliday'    => $menuHoliday,
                                'menuBreak'      => $menuBreak,
                                'menuExcuse'     => $menuExcuse,
                                'menuDuty'       => $menuDuty,
                                'menuFinish'     => $menuFinish,
                                'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                            ],
                            'date'    => date('Y-m-d h:i:s'),
                        ]);
                    } else {
                        $absen = Absence_categories::selectRaw('absence_categories.*, work_type_days.start, work_type_days.end')
                            ->join('work_type_days', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
                            ->join('work_types', 'work_type_days.work_type_id', '=', 'work_types.id')
                            ->where('work_types.id', $coordinat->work_type_id)
                            ->where('day_id', $day)
                            ->first();

                        // return response()->json([
                        //     'abs' => $absen,
                        // ]);

                        // buat baru start
                        // cek apa sudah ada group absen di tanggal ini
                        if ($absen) {
                            $c = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                                ->whereDate('absences.created_at', '=', date('Y-m-d'))
                                ->whereNotIn('absence_category_id', [9, 10])
                                ->where('staff_id', $staffId)->first();
                            if (! $c) {
                                $data = [
                                    'day_id'         => $day,
                                    'shift_group_id' => $shift_group_id,
                                    'staff_id'       => $staffId,
                                    'created_at'     => date('Y-m-d'),
                                ];
                                $absence      = Absence::create($data);
                                $list_absence = WorkTypeDays::selectRaw('duration, duration_exp, queue, type, time, start, absence_category_id,work_type_days.id as work_type_day_id ')
                                    ->join('absence_categories', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
                                    ->where('work_type_id', $coordinat->work_type_id)
                                    ->where('day_id', $day)
                                    ->where('absence_categories.type', '=', 'presence')
                                    ->orderBy('queue', 'ASC')
                                    ->get();

                                // absen bermsalah start
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

                                // absence bermsalah end
                                $expired_date = date('Y-m-d H:i:s');
                                try {
                                    for ($n = 0; $n < count($list_absence); $n++) {
                                        $expired_date = date("Y-m-d H:i:s", strtotime('+' . (($list_absence[0]->duration + $list_absence[0]->duration_exp) * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                        $timeout      = date("Y-m-d H:i:s", strtotime('+' . ($list_absence[0]->duration * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                        $timein       = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . $list_absence[0]->time)));

                                        // $status = 0;
                                        // if ($n === (count($list_absence) - 1)) {
                                        //     $status =  1;
                                        // } else if ($n === 2 && $list_absence[$n]->type == "break") {
                                        //     $status =  1;
                                        // } else {
                                        //     $status =  0;
                                        // }

                                        // if ($list_absence[$n]->start == "0000-00-00") {
                                        //     $start_date =  null;
                                        // } else {
                                        // $start_date = date("Y-m-d H:i:s", strtotime('- ' . $list_absence[$n]->duration_exp . ' minutes', strtotime(date('Y-m-d ' . $list_absence[$n]->time))));
                                        // }
                                        if ($list_absence[$n]->queue == "1") {
                                            $start_date = date("Y-m-d H:i:s", strtotime('- ' . ($list_absence[0]->duration_exp * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                        } else {
                                            $start_date = date("Y-m-d H:i:s", strtotime('+ ' . ($list_absence[0]->duration * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                        }
                                        $upload_image = new AbsenceLog;
                                        // sementara start
                                        $upload_image->absence_id       = $absence->id;
                                        $upload_image->start_date       = $start_date;
                                        $upload_image->expired_date     = $expired_date;
                                        $upload_image->work_type_day_id = $list_absence[$n]->work_type_day_id;
                                        $upload_image->timein           = $timein;
                                        $upload_image->timeout          = $timeout;
                                        // sementara end
                                        $upload_image->created_at          = date('Y-m-d H:i:s');
                                        $upload_image->updated_at          = date('Y-m-d H:i:s');
                                        $upload_image->status              = 1;
                                        $upload_image->absence_category_id = $list_absence[$n]->absence_category_id;
                                        // $upload_image->shift_id = $request->shift_id;
                                        $upload_image->save();
                                    }

                                    // cek absen, apa ada absen hari ini
                                    $absence = AbsenceLog::selectRaw('absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
                                        ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                                        ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                                        ->where('staff_id', $staffId)
                                        ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                                        ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                                        ->where('absence_logs.status', '=', 1)
                                        ->where('absence_categories.type', '=', 'presence')
                                        ->orderBy('absence_logs.start_date', 'ASC')
                                        ->first();
                                    $a1    = "1";
                                    $absen = "";

                                    // jika ada absen hari ini
                                    if ($absence) {
                                        if ($absence->shift_planner_id === 0) {
                                            $absen = AbsenceLog::selectRaw('absence_categories.*,absence_logs.id as id, absence_id, work_type_days.start, work_type_days.end')
                                                ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
                                                ->join('work_type_days', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
                                                ->join('work_types', 'work_type_days.work_type_id', '=', 'work_types.id')
                                                ->where('work_types.id', $coordinat->work_type_id)
                                                ->where('absence_logs.id', $absence->id)
                                                ->where('absence_categories.type', '=', 'presence')
                                                ->where('absence_logs.status', '=', 1)
                                                ->first();
                                            $a1          = "2";
                                            $menuReguler = "ON";
                                            $reguler     = $absen;
                                        } else {
                                            $absen = AbsenceLog::selectRaw('absence_logs.*, shift_group_timesheets.start, shift_group_timesheets.end')
                                                ->leftJoin('absences', 'absences.id', '=', 'absence_logs.absence_id')
                                                ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
                                                ->join('shift_group_timesheets', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
                                                ->where('absence_logs.status', '=', 1)
                                                ->where('absence_logs.id', $absence->id)
                                                ->where('absence_categories.type', '=', 'presence')
                                                ->orderBy('absence_logs.id', 'DESC')
                                                ->first();

                                            $a1 = "2";
                                        }
                                        return response()->json([
                                            'lat'         => $lat,
                                            'fingerfrint' => $fingerprint,
                                            'selfie'      => $camera,
                                            'gps'         => $gps,
                                            'lng'         => $lng,
                                            'radius'      => $radius,
                                            'reguler'     => $reguler,
                                            'work_type'   => $coordinat->work_type_id,
                                            'menu'        => [
                                                'menuReguler'    => $menuReguler,
                                                'menuHoliday'    => $menuHoliday,
                                                'menuBreak'      => $menuBreak,
                                                'menuExcuse'     => $menuExcuse,
                                                'menuDuty'       => $menuDuty,
                                                'menuFinish'     => $menuFinish,
                                                'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                                            ],
                                            'break'       => $break,
                                            'date'        => $coordinat->type,
                                            'absence'     => $absence,
                                            'tesss'       => $absen,
                                            'a1'          => $a1,
                                        ]);
                                    }
                                    // test end
                                } catch (QueryException $ex) {
                                    return response()->json([
                                        'message' => 'gagal',
                                    ]);
                                }
                            }

                            if (! $logOutPending) {
                                if (date('Y-m-d H:i:s') > date('Y-m-d 21:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 23:59:59') || date('Y-m-d H:i:s') > date('Y-m-d 01:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 06:00:00')) {
                                    $absence_extra = $absence_request_with_pending->has('extra') ? $absence_request_with_pending['extra']->first() : null;
                                } else {
                                    $absence_extra = $absence_request->has('extra') ? $absence_request['extra']->first() : null;
                                }

                                if ($absence_extra) {
                                    $menu = 'OFF';
                                    if ($absence_extra) {
                                        $extra = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                                            ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                                            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                                            ->where('staff_id', $staffId)
                                            ->where('absence_request_id', $absence_extra->id)
                                            ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                                            ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                                            ->where('absence_logs.status', '=', 1)
                                            ->where('absence_categories.type', '=', 'extra')
                                            ->where('absence_categories.queue', '=', '2')
                                            ->orderBy('absence_logs.id', 'DESC')
                                            ->first();
                                        if ($extra) {
                                            $extra_id = $extra->id;
                                        } else {
                                            $extraC = Absence_categories::where('type', 'extra')->get();
                                        }
                                        $menuExtra = "ON";
                                    }
                                    if ($absence_extra->type == "outside") {
                                        $geofence_off = "ON";
                                    }

                                    return response()->json([
                                        'message'       => 'Success',
                                        'menu'          => [
                                            'menuReguler'    => $menuReguler,
                                            'menuHoliday'    => $menuHoliday,
                                            'menuBreak'      => $menuBreak,
                                            'menuExcuse'     => $menuExcuse,
                                            'menuExtra'      => $menuExtra,
                                            'menuDuty'       => $menuDuty,
                                            'menuFinish'     => $menuFinish,
                                            'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                                        ],
                                        'extraC'        => $extraC,
                                        'extra'         => $extra,
                                        'request_extra' => $absence_extra,
                                        'date'          => date('Y-m-d h:i:s'),
                                        'lat'           => $lat,
                                        'fingerfrint'   => $fingerprint,
                                        'selfie'        => $camera,
                                        'gps'           => $gps,
                                        'lng'           => $lng,
                                        'radius'        => $radius,
                                    ]);
                                } else {
                                    $menuWaiting = "ON";
                                    return response()->json([
                                        'message'        => 'Absen Terkirim',
                                        'message'        => 'sudah pulang',
                                        'data'           => $c,
                                        'radius'         => $radius,
                                        'reguler'        => $reguler,
                                        'break'          => $break,
                                        'menu'           => [
                                            'menuBreak'      => $menuBreak,
                                            'menuExcuse'     => $menuExcuse,
                                            'menuReguler'    => $menuReguler,
                                            'menuHoliday'    => $menuHoliday,
                                            'menuDuty'       => $menuDuty,
                                            'menuFinish'     => $menuFinish,
                                            'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                                            'menuWaiting'    => $menuWaiting,
                                        ],
                                        'waitingMessage' => "Absen Sudah Selesai",
                                    ]);
                                }
                            } else {
                                return response()->json([
                                    'message'        => 'Absen Terkirim',
                                    'message'        => 'ssssssa',
                                    'excuseC'        => $excuseC,
                                    'excuse'         => $excuse,
                                    'request_excuse' => $absence_excuse,
                                    'visitC'         => $visitC,
                                    'visit'          => $visit,
                                    'request_visit'  => $absence_visit,
                                    'data'           => $c,

                                    'lat'            => $lat,
                                    'fingerfrint'    => $fingerprint,
                                    'selfie'         => $camera,
                                    'gps'            => $gps,
                                    'lng'            => $lng,
                                    'radius'         => $radius,
                                    'reguler'        => $reguler,
                                    'break'          => $break,
                                    'absenceOut'     => $logOutPending,
                                    'menu'           => [
                                        'menuBreak'      => $menuBreak,
                                        'menuExcuse'     => $menuExcuse,
                                        'menuReguler'    => $menuReguler,
                                        'menuHoliday'    => $menuHoliday,
                                        'menuVisit'      => $menuVisit,
                                        'menuDuty'       => $menuDuty,
                                        'menuFinish'     => $menuFinish,
                                        'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                                    ],
                                ]);
                            }
                        }
                        // buat baru end
                        $a1 = "4";
                    }
                }
            }

            // pentingg dirubah
            return response()->json([
                'message'        => 'Absen Terkirim',
                'message'        => 'ssssssa',
                // 'data' =>   $c,

                'excuseC'        => $excuseC,
                'excuse'         => $excuse,
                'request_excuse' => $absence_excuse,

                'visitC'         => $visitC,
                'visit'          => $visit,
                'request_visit'  => $absence_visit,
                // 'data' =>   $c,

                'lat'            => $lat,
                'fingerfrint'    => $fingerprint,
                'selfie'         => $camera,
                'gps'            => $gps,
                'lng'            => $lng,
                'radius'         => $radius,
                'reguler'        => $reguler,
                'break'          => $break,
                'absenceOut'     => $logOutPending,
                'menu'           => [
                    'menuBreak'      => $menuBreak,
                    'menuExcuse'     => $menuExcuse,
                    'menuReguler'    => $menuReguler,
                    'menuHoliday'    => $menuHoliday,
                    'menuVisit'      => $menuVisit,
                    'menuDuty'       => $menuDuty,
                    'menuFinish'     => $menuFinish,
                    'geolocationOff' => $geofence_off_break == 'ON' ? $geofence_off_break : $geofence_off,
                ],
            ]);
        }
    }
}
