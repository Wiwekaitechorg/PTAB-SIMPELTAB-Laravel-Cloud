<?php
namespace App\Services;

use App\Absence;
use App\AbsenceCategory;
use App\AbsenceLog;
use App\AbsenceProblem;
use App\AbsenceRequest;
use App\StaffSpecial;
use App\Visit;
use App\WorkUnit;
use Illuminate\Support\Facades\DB;

class AbsenceMenuService
{
    /**
     * Get menu data for a staff member.
     *
     * @param int $staffId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuData($staffId)
    {
        $menu = [
            'menuReguler'    => 'OFF',
            'menuHoliday'    => 'OFF',
            'menuBreak'      => 'OFF',
            'menuExcuse'     => 'OFF',
            'menuVisit'      => 'OFF',
            'menuDuty'       => 'OFF',
            'menuFinish'     => 'OFF',
            'menuExtra'      => 'OFF',
            'menuLeave'      => 'OFF',
            'menuWaiting'    => 'OFF',
            'menuPermission' => 'OFF',
            'geolocationOff' => 'OFF',
        ];

        $fingerprint = $camera = $gps = 'ON';
        $lat         = $lng         = $radius         = null;
        $now         = date('Y-m-d H:i:s');

        //get work unit coordinate
        $coordinat = WorkUnit::withStaffAndType($staffId)->first();
        if ($coordinat) {
            $lat    = $coordinat->lat;
            $lng    = $coordinat->lng;
            $radius = $coordinat->radius;
        }

        //check geofence off
        $geofence_off = AbsenceRequest::activeInPeriod($now)
            ->geofenceOff()
            ->byStaff($staffId)
            ->exists() ? 'ON' : 'OFF';

        //check staff special
        $staff_special = StaffSpecial::byStaff($staffId)->valid()->first();
        if ($staff_special) {
            $fingerprint = $staff_special->fingerprint;
            $camera      = $staff_special->camera;
            $gps         = $staff_special->gps;
        }
        if ($gps == 'OFF') {
            $geofence_off = 'ON';
        }
        $menu['geolocationOff'] = $geofence_off;

        //check Leave
        $leave = AbsenceRequest::activeInPeriod($now)
            ->byCategory('leave')
            ->byStaff($staffId)
            ->approvedOrActive()
            ->first();
        if ($leave) {
            $menuResponse   = $menu;
            $waitingMessage = null;

            if (in_array($leave->status, ['pending', 'close'])) {
                $menuResponse['menuWaiting'] = 'ON';
                $waitingMessage              = 'Menunggu Persetujuan Cuti';
            } else {
                $menuResponse['menuLeave'] = 'ON';
            }

            return response()->json([
                'message'        => 'Success',
                'menu'           => $menuResponse,
                'leave'          => $leave,
                'waitingMessage' => $waitingMessage,
                'date'           => now()->format('Y-m-d H:i:s'),
            ]);
        }

        //check Permission
        $permission = AbsenceRequest::activeInPeriod($now)
            ->byCategory('permission')
            ->byStaff($staffId)
            ->approvedOrActive()
            ->first();
        if ($permission) {
            $menuResponse   = $menu;
            $waitingMessage = null;

            if (in_array($permission->status, ['pending', 'close'])) {
                $menuResponse['menuWaiting'] = 'ON';
                $waitingMessage              = $permission->status === 'pending'
                    ? 'Menunggu Persetujuan Izin'
                    : 'Besok Anda Sudah Bisa Mulai Bekerja';
            } else {
                $menuResponse['menuPermission'] = 'ON';

                if ($permission->type === 'other') {
                    $menuResponse['menuWaiting']    = 'ON';
                    $menuResponse['menuPermission'] = 'OFF';
                }

                $waitingMessage = 'Anda Masih Izin';
            }

            return response()->json([
                'message'        => 'Success',
                'menu'           => $menuResponse,
                'permission'     => $permission,
                'waitingMessage' => $waitingMessage,
                'date'           => now()->format('Y-m-d H:i:s'),
            ]);
        }

        //check Duty
        $duty = AbsenceRequest::activeInPeriod($now)
            ->byCategory('duty')
            ->byStaff($staffId)
            ->approvedOrActive()
            ->first();
        if ($duty) {
            $menuResponse   = $menu; // clone your initialized menu array
            $waitingMessage = null;

            if (in_array($duty->status, ['pending', 'close'])) {
                $menuResponse['menuWaiting'] = 'ON';
                $waitingMessage              = $duty->status === 'pending'
                    ? 'Waiting for Duty Approval'
                    : 'Tomorrow you can start working again';

                return response()->json([
                    'message'        => 'Success',
                    'fingerfrint'    => $fingerprint,
                    'selfie'         => $camera,
                    'menu'           => $menuResponse,
                    'waitingMessage' => $waitingMessage,
                    'duty'           => $duty,
                    'date'           => now()->format('Y-m-d H:i:s'),
                ]);
            }

            // active duty
            $AbsenceRequestLogs = AbsenceRequestLogs::where('absence_request_id', $duty->id)
                ->where('type', 'request_log_in')
                ->first();

            $menuResponse['menuDuty'] = 'ON';

            return response()->json([
                'message'            => 'Success',
                'fingerfrint'        => $fingerprint,
                'selfie'             => $camera,
                'menu'               => $menuResponse,
                'AbsenceRequestLogs' => $AbsenceRequestLogs,
                'duty'               => $duty,
                'coordinat'          => $coordinat,
                'date'               => now()->format('Y-m-d H:i:s'),
            ]);
        }

        //check Visit
        $absence_visit = AbsenceRequest::activeInPeriod($now)
            ->byCategory('visit')
            ->byStaff($request->staff_id)
            ->approvedOrActive()
            ->orderBy(DB::raw('FIELD(status, "active", "approve")'))
            ->first();
        if ($absence_visit) {
            $visit = AbsenceLog::selectRaw('absence_logs.status,
                                    absence_request_id,
                                    absence_id,
                                    absence_categories.type as absence_category_type,
                                    absence_logs.expired_date,
                                    shift_planner_id,
                                    queue,
                                    status_active,
                                    absence_categories.id as absence_category_id,
                                    absence_logs.id as id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->byStaff($request->staff_id)
                ->byRequest($absence_visit->id)
                ->activeInPeriod($now)
                ->withStatus(1)
                ->withCategoryType('visit')
                ->withCategoryQueue(2)
                ->orderBy('absence_logs.id', 'DESC')
                ->first();

            if ($visit) {
                $visitEtc          = Visit::where('absence_request_id', $visit->absence_request_id)->first();
                $menu['menuVisit'] = $visitEtc ? "ON" : "ACTIVE";
            } else {
                $visitC = AbsenceCategory::byType('visit')->get();
            }
        }

        // find active extra absence (off, forget, AdditionalTime)
        // if found, show the active extra absence data
        // if not found, check if can create new extra absence and show the create new extra absence data
        // if cannot create new extra absence, just show the regular menu
        $absence_extra_active = Absence::withAbsenceLogs()
            ->selectExtraFields()
            ->activeLogs()
            ->byStaff($request->staff_id)
            ->byAbsenceCategoryId(10)
            ->first();
        if ($absence_extra_active) {
            $extra = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_logs.id', $absence_extra_active->id)
                ->byStatus(1)   // scope
                ->type('extra') // scope
                ->queue('2')    // scope
                ->orderByDesc('absence_logs.id')
                ->first();
            $absence_extra = AbsenceRequest::find($absence_extra_active->absence_request_id);

            if ($extra) {
                $extra_id = $extra->id;
            } else {
                $extraC = Absence_categories::where('type', 'extra')->get();
            }
            $menu['menuExtra'] = "ON";

            if ($absence_extra && $absence_extra->type == "outside") {
                $geofence_off = "ON";
            }
            $menu['geolocationOff'] = $geofence_off;

            return response()->json([
                'message'       => 'Success',
                'menu'          => $menu,
                'extraC'        => $extraC ?? null,
                'extra'         => $extra,
                'request_extra' => $absence_extra,
                'date'          => $now,
                'lat'           => $lat,
                'fingerfrint'   => $fingerprint,
                'selfie'        => $camera,
                'gps'           => $gps,
                'lng'           => $lng,
                'radius'        => $radius,
            ]);
        }

        // check if can create new extra absence
        // if there is no absence today, or if there is an absence today but both check-in and check-out have been done
        // then allow to create new extra absence
        $showExtra     = "No";
        $absence_extra = null;
        $absenIn       = Absence::todayByStaff($request->staff_id)->get();
        foreach ($absenIn as $data) {
            $c_in  = $data->absence_logs->where('absence_category_id', 1)->where('status', 0)->first();
            $c_out = $data->absence_logs->where('absence_category_id', 2)->where('status', 0)->first();
            if ($c_in && $c_out) {
                $showExtra = "Yes";
            }
        }
        if ($absenIn->isEmpty()) {
            $showExtra = "Yes";
        }
        if ($showExtra === "Yes") {
            $absence_extra = AbsenceRequest::activeInPeriod(now())
                ->byCategory('extra')
                ->byStaff($request->staff_id)
                ->approvedOrActive()
                ->orderActiveApproveFirst()
                ->first();
        }
        if ($absence_extra) {
            $menuResponse = $menu;

            // now just call the scope
            $extra = AbsenceLog::extraForRequest($request->staff_id, $absence_extra->id)->first();

            $extraC = $extra ? null : AbsenceCategory::ofType('extra')->get();

            $menuResponse['menuExtra'] = 'ON';
            if ($absence_extra->type === 'outside') {
                $menuResponse['geolocationOff'] = 'ON';
            }

            return response()->json([
                'message'       => 'Success',
                'menu'          => $menuResponse,
                'sebelum'       => 'yaa',
                'extraC'        => $extraC,
                'extra'         => $extra,
                'request_extra' => $absence_extra,
                'date'          => now()->format('Y-m-d H:i:s'),
                'lat'           => $lat,
                'fingerfrint'   => $fingerprint,
                'selfie'        => $camera,
                'gps'           => $gps,
                'lng'           => $lng,
                'radius'        => $radius,
            ]);
        }

        // check if there is an active break
        $absenceBreak = AbsenceLog::currentBreak($request->staff_id)->first();
        if ($absenceBreak) {
            // check excuse request
            $absence_excuse = AbsenceRequest::activeNow()
                ->where('category', 'excuse')
                ->forStaff($request->staff_id)
                ->first();

            if ($absence_excuse) {
                $excuse             = AbsenceLog::linkedToRequest($request->staff_id, $absence_excuse->id, 'excuse')->first();
                $excuse_id          = $excuse?->id;
                $excuseC            = $excuse ? null : AbsenceCategory::ofType('excuse')->get();
                $menu['menuExcuse'] = "ON";
            }

            // check visit request
            $absence_visit = AbsenceRequest::activeNow()
                ->where('category', 'visit')
                ->forStaff($request->staff_id)
                ->first();

            if ($absence_visit) {
                $visit = AbsenceLog::linkedToRequest($request->staff_id, $absence_visit->id, 'visit')->first();
                if ($visit) {
                    $visit_id          = $visit->id;
                    $visitEtc          = Visit::where('absence_request_id', $visit->absence_request_id)->first();
                    $menu['menuVisit'] = $visitEtc ? "ON" : "ACTIVE";
                } else {
                    $visitC            = AbsenceCategory::ofType('visit')->get();
                    $menu['menuVisit'] = "ON";
                }
            }

            // break active
            $break = AbsenceLog::currentBreakActive($request->staff_id)->first();
            if ($break) {
                $menu['menuBreak'] = "ON";
                if ($menu['menuVisit'] != 'OFF') {
                    $geofence_off_break = "ON";
                }
            }

            // absence out (you can also make a scope for this)
            $absenceOut = AbsenceLog::selectRaw('absence_logs.expired_date, absence_logs.start_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id')
                ->withAbsenceAndCategory()
                ->forStaff($request->staff_id)
                ->where('absence_logs.expired_date', '>=', now())
                ->status(1)
                ->where('absence_logs.absence_id', $absenceBreak->absence_id)
                ->where('absence_categories.id', 2)
                ->categoryType('presence')
                ->orderBy('absence_logs.start_date', 'ASC')
                ->first();
        }

        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        // first presence log for today
        $absence = AbsenceLog::presenceToday($request->staff_id)->first();
        // did staff already check in?
        $checkIn = AbsenceLog::checkInToday($request->staff_id)->first();
        if ($checkIn) {
            // is there a follow-up log (not in category 3,4)?
            $followUp = AbsenceLog::followUpLog($request->staff_id, $checkIn->absence_id)
                ->whereNotIn('absence_category_id', [3, 4])
                ->first();

            // or in category 3,4?
            $followUpSpecial = AbsenceLog::followUpLog($request->staff_id, $checkIn->absence_id)
                ->whereIn('absence_category_id', [3, 4])
                ->first();

            if (! $followUp && ! $followUpSpecial) {
                // absence out expired today for taking expired date
                $absenceOut = AbsenceLog::selectRaw('absence_logs.expired_date, absence_logs.start_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id')
                    ->withAbsenceAndCategory()
                    ->forStaff($request->staff_id)
                    ->where('absence_logs.expired_date', '>=', now())
                    ->status(1)
                    ->where('absence_logs.absence_id', $checkIn->absence_id)
                    ->where('absence_categories.id', 2)
                    ->categoryType('presence')
                    ->orderBy('absence_logs.start_date', 'ASC')
                    ->first();
            }

            // decide which absence to show
            if ($followUp) {
                if ($followUp->start_date <= now() && $followUp->expired_date >= now()) {
                    $absence = $followUp;
                } elseif ($followUp->absence_category_id === 2 && $followUp->expired_date >= now()) {
                    $absence = null;
                } else {
                    if ($followUpSpecial) {
                        if ($followUpSpecial->start_date <= now() && $followUpSpecial->expired_date >= now()) {
                            $absence = $followUpSpecial;
                        } else {
                            $absence = null;
                        }
                    }
                }
            }
        }

        $a1 = "1";

        if ($absence) {
            if ($absence->shift_planner_id === 0) {
                $absen = AbsenceLog::withRegularWorkType($coordinat->work_type_id, $absence->id)->first();
            } else {
                $absen = AbsenceLog::withShiftGroup($absence->id)->first();
            }

            $a1                  = "2";
            $menu['menuReguler'] = "ON";
            $reguler             = $absen;
        } else {
            if ($coordinat->type == "shift") {
                $a1 = "3";
                $c  = ShiftPlannerStaffs::select(
                    'shift_planner_staffs.id as shift_planner_id',
                    'shift_planner_staffs.shift_group_id'
                )
                    ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
                    ->todayForStaff($request->staff_id)
                    ->withoutAbsenceLog()
                    ->orderBy('shift_groups.queue')
                    ->get();
                // if there is a shift today
                if (count($c) > 0) {
                    foreach ($c as $item) {

                        // create absence record
                        $absence = Absence::create([
                            'day_id'         => $day,
                            'shift_group_id' => $item->shift_group_id,
                            'staff_id'       => $request->staff_id,
                            'created_at'     => now()->toDateString(),
                        ]);

                        // fetch absence time slots for that shift group
                        $listAbsence = ShiftGroup::absenceTimeSlots($item->shift_group_id)->get();

                        $problem = AbsenceProblem::where('id', $coordinat->absence_problem_id)->first();
                        // --- Reminder: problematic attendance ---
                        if ($problem && $listAbsence->isNotEmpty()) {
                            $baseTime = Carbon::parse(now()->toDateString() . ' ' . $listAbsence[0]->time);
                            $endTime  = $baseTime->copy()->addMinutes(($listAbsence[0]->duration - $problem->duration) * 60);

                            while ($baseTime->lt($endTime)) {
                                $baseTime->addMinutes($problem->duration * 60);

                                MessageLog::create([
                                    'staff_id'   => $request->staff_id,
                                    'memo'       => 'Anda Dalam Pengawasan, Buka Untuk Absen Lokasi',
                                    'type'       => 'check',
                                    'status'     => 'pending',
                                    'created_at' => $baseTime,
                                ]);
                            }
                        }

                        // --- Create absence logs ---
                        try {
                            foreach ($listAbsence as $index => $slot) {
                                // base times
                                $timeIn  = Carbon::parse(now()->toDateString() . ' ' . $listAbsence[0]->time);
                                $timeout = $timeIn->copy()->addMinutes($listAbsence[0]->duration * 60);
                                $expired = $timeIn->copy()->addMinutes(($listAbsence[0]->duration + $listAbsence[0]->duration_exp) * 60);

                                // start date depends on queue
                                $startDate = $slot->queue == '1'
                                    ? $timeIn->copy()->subMinutes($listAbsence[0]->duration_exp * 60)
                                    : $timeIn->copy()->addMinutes($listAbsence[0]->duration * 60);

                                // decide status
                                $status = 0;
                                if ($index === $listAbsence->count() - 1) {
                                    $status = 1;
                                } elseif ($index === 2 && $slot->type === 'break') {
                                    $status = 1;
                                }

                                // create absence log
                                AbsenceLog::create([
                                    'absence_id'               => $absence->id,
                                    'shift_planner_id'         => $item->shift_planner_id,
                                    'shift_group_timesheet_id' => $slot->shift_group_timesheet_id,
                                    'timein'                   => $timeIn,
                                    'timeout'                  => $timeout,
                                    'start_date'               => $startDate,
                                    'expired_date'             => $expired,
                                    'status'                   => $status,
                                    'absence_category_id'      => $slot->absence_category_id,
                                    'created_at'               => now(),
                                    'updated_at'               => now(),
                                ]);
                            }
                        } catch (QueryException $ex) {
                            return response()->json(['message' => 'gagal']);
                        }
                    }
                    // test start
                    if (date('w') == '0') {
                        $day = '7';
                    } else {
                        $day = date('w');
                    }
                    // Get today’s active absence
                    $absence = AbsenceLog::activePresenceForStaff($request->staff_id)->first();

                    if ($absence) {
                        if ((int) $absence->shift_planner_id === 0) {
                            $absen = AbsenceLog::withWorkTypeDetails($coordinat->work_type_id, $absence->id)->first();
                        } else {
                            $absen = AbsenceLog::withShiftGroupDetails($absence->id)->first();
                        }

                        $reguler             = $absen;
                        $a1                  = "2";
                        $menu['menuReguler'] = "ON";

                        return response()->json([
                            'lat'         => $lat,
                            'fingerfrint' => $fingerprint,
                            'selfie'      => $camera,
                            'gps'         => $gps,
                            'lng'         => $lng,
                            'radius'      => $radius,
                            'reguler'     => $reguler,
                            'work_type'   => $coordinat->work_type_id,
                            'menu'        => $menu,
                            'break'       => $break,
                            'date'        => $coordinat->type,
                            'absence'     => $absence,
                            'tesss'       => $absen,
                            'a1'          => $a1,
                        ]);
                    }

                } else {
                    // no shift today
                    $holiday = Holiday::today()->first();

                    if ($holiday) {
                        // holiday response
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
                            'date'    => now()->format('Y-m-d h:i:s'),
                        ]);
                    }

                    // Normal presence
                    $absenCategory = AbsenceCategory::withWorkTypeDay($coordinat->work_type_id, $day)->first();

                    if ($absenCategory) {
                        // create Absence if not exist today
                        $hasAbsence = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                            ->whereDate('absences.created_at', today())
                            ->whereNotIn('absence_category_id', [9, 10])
                            ->where('staff_id', $request->staff_id)
                            ->first();

                        if (! $hasAbsence) {
                            $absence = Absence::create([
                                'day_id'         => $day,
                                'shift_group_id' => $request->shift_group_id,
                                'staff_id'       => $request->staff_id,
                                'created_at'     => today(),
                            ]);

                            // fetch time slots
                            $timeSlots = WorkTypeDay::presenceFor($coordinat->work_type_id, $day)->get();

                            // insert absence logs for each slot
                            AbsenceLog::insertForAbsence($absence->id, $request->staff_id, $timeSlots);
                        }

                        // afterwards get current presence log
                        $currentAbsence = AbsenceLog::currentPresence($request->staff_id)->first();

                        if ($currentAbsence) {
                            // optional: detailed info
                            $detail = AbsenceLog::detailForWorkType($coordinat->work_type_id, $currentAbsence->id)->first();

                            return response()->json([
                                'reguler' => $detail,
                                'absence' => $currentAbsence,
                                // plus other fields…
                            ]);
                        }
                    }

                }

            }
        }
    }
}
