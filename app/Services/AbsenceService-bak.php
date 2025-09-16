<?php
namespace App\Services;

use App\AbsenceRequest;
use App\StaffSpecial;
use App\WorkUnit;

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
        // untuk menampung data menu
        $reguler    = "";
        $holiday    = "";
        $break      = "";
        $duty       = "";
        $finish     = "";
        $excuse_id  = "";
        $absenceOut = [];

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

        // mematikan batas radius di absence

        $geolocation = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            ->where('end', '>=', date('Y-m-d H:i:s'))
            ->where('category', 'geolocation_off')
            ->where('status', 'approve')
            ->where('staff_id', $request->staff_id)
            ->first();
        if ($geolocation) {
            $geofence_off = "ON";
        }

        $forget = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            ->where('end', '>=', date('Y-m-d H:i:s'))
            ->where('category', 'forget')
            ->where('status', 'approve')
            ->where('staff_id', $request->staff_id)
            ->first();
        if ($forget) {
            $geofence_off = "ON";
        }

        $additionalTime = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            ->where('end', '>=', date('Y-m-d H:i:s'))
            ->where('category', 'AdditionalTime')
            ->where('status', 'approve')
            ->where('type', 'out')
            ->where('staff_id', $request->staff_id)
            ->first();
        if ($additionalTime) {
            $geofence_off = "ON";
        }

        // return response()->json([
        //     'message' => 'Success',
        //     'menu' => [
        //         'menuReguler' =>  $geofence_off,
        //     ],
        //     'waitingMessage' => "Menunggu Persetujuan Cuti",
        //     'date' => date('Y-m-d h:i:s')
        // ]);
        $fingerprint = "ON";
        $camera      = "ON";
        $gps         = "ON";

        $coordinat = WorkUnit::join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $request->staff_id)->first();

        $lat    = $coordinat->lat;
        $lng    = $coordinat->lng;
        $radius = $coordinat->radius;

        $Rlocation = AbsenceRequest::join('work_units', 'work_units.id', '=', 'absence_requests.work_unit_id')
            ->where('start', '<=', date('Y-m-d H:i:s'))
            ->where('end', '>=', date('Y-m-d H:i:s'))
            ->where('category', 'location')
            ->where('status', 'approve')
            ->where('absence_requests.work_unit_id', '!=', 'approve')
            ->where('staff_id', $request->staff_id)
            ->orderBy('absence_requests.id', 'DESC')
            ->first();

        if ($Rlocation) {
            // if ($forget) {
            //     $geofence_off = "MOVE";
            // }

            $lat    = $Rlocation->lat;
            $lng    = $Rlocation->lng;
            $radius = $Rlocation->radius;
        }

        $staff_special = StaffSpecial::select('staff_specials.*')
            ->where('staff_id', $request->staff_id)->whereDate('expired_date', '>=', date('Y-m-d'))->first();
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
        $leave   = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            ->where('end', '>=', date('Y-m-d H:i:s'))
            ->where('category', 'leave')
            ->where('staff_id', $request->staff_id)
            ->where(function ($query) {
                $query->where('status', 'approve')
                    ->orWhere('status', 'active');
            })
            ->orWhere('start', '<=', date('Y-m-d H:i:s'))
            ->where('category', 'leave')
            ->where('type', 'sick')
            ->where('staff_id', $request->staff_id)
            ->where(function ($query) {
                $query->where('status', 'approve')
                    ->orWhere('status', 'active');
            })
            ->first();

        // $leave   = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
        //     ->where('end', '>=', date('Y-m-d H:i:s'))
        //     ->where('category', 'leave')
        //     ->where('staff_id', $request->staff_id)
        //     ->where(function ($query) {
        //         $query->where('status', 'approve')
        //             ->orWhere('status', 'active');
        //         // ->orWhere('status', 'pending');
        //         // ->orWhere('status', 'close');
        //     })
        //     ->first();

        $permission = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            ->where('end', '>=', date('Y-m-d H:i:s'))
            ->where('category', 'permission')
            ->where('staff_id', $request->staff_id)
            ->where(function ($query) {
                $query->where('status', 'approve')
                    ->orWhere('status', 'active');
                // ->orWhere('status', 'pending');
                // ->orWhere('status', 'close');
            })
            ->orWhere('start', '<=', date('Y-m-d H:i:s'))
            ->where('category', 'permission')
            ->where('type', 'sick')
            ->where('staff_id', $request->staff_id)
            ->where(function ($query) {
                $query->where('status', 'approve')
                    ->orWhere('status', 'active');
                // ->orWhere('status', 'pending');
                // ->orWhere('status', 'close');
            })
            ->first();

        // cek apa tanggal ini ada dinas dalam kota
        $absence_visit = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            ->where('end', '>=', date('Y-m-d H:i:s'))
            ->where('category', 'visit')
            ->where('staff_id', $request->staff_id)
            ->where(function ($query) {
                $query->where('status', 'approve')
                    ->orWhere('status', 'active');
            })
            ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
            ->first();
        if ($absence_visit) {
            $visit = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('staff_id', $request->staff_id)
                ->where('absence_request_id', $absence_visit->id)
                ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                ->where('absence_logs.status', '=', 1)
                ->where('absence_categories.type', '=', 'visit')
                ->where('absence_categories.queue', '=', '2')
                ->orderBy('absence_logs.id', 'DESC')
                ->first();
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
                $visitC = Absence_categories::where('type', 'visit')->get();
            }
            // $menuVisit = "ON";
            // $menuVisit = "OFF";
        }

        $duty = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            ->where('end', '>=', date('Y-m-d H:i:s'))
            ->where('category', 'duty')
            ->where('staff_id', $request->staff_id)
            ->where(function ($query) {
                $query->where('status', 'approve')
                    ->orWhere('status', 'active');
                // ->orWhere('status', 'pending');
                // ->orWhere('status', 'close');
            })
            ->first();

        // $extra = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
        //     ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
        //     ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
        //     ->where('staff_id', $request->staff_id)
        //     // ->where('absence_request_id', $absence_extra->id)
        //     ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
        //     ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
        //     ->where('absence_logs.status', '=', 1)
        //     ->where('absence_categories.type', '=', 'extra')
        //     ->where('absence_categories.queue', '=', '2')
        //     ->orderBy('absence_logs.id', 'DESC')
        //     ->first();

        // cek ada lembur yang belum selesai start

        $absence_extra_active = Absence::selectRaw('absence_logs.id as id, absence_logs.absence_request_id')
            ->join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
            ->where('absence_logs.status', 1)->where('staff_id', $request->staff_id)
            ->where('absence_logs.absence_category_id', 10)->first();

        if ($absence_extra_active) {
            $menu = 'OFF';

            $extra = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_logs.id', $absence_extra_active->id)
            // ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
            // ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                ->where('absence_logs.status', '=', 1)
                ->where('absence_categories.type', '=', 'extra')
                ->where('absence_categories.queue', '=', '2')
                ->orderBy('absence_logs.id', 'DESC')
                ->first();

            $absence_extra = AbsenceRequest::where('id', $absence_extra_active->absence_request_id)
                ->first();

            // return response()->json([
            //     'message' => 'Success',
            //     'extra' => $extra,
            //     'absence extra active' =>  $absence_extra_active,
            //     'absence extra' => $absence_extra

            // ]);
            // $absence_extra = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            //     ->where('end', '>=', date('Y-m-d H:i:s'))
            //     ->where('category', 'extra')
            //     ->where('staff_id', $request->staff_id)
            //     ->where(function ($query) {
            //         $query->where('status', 'approve')
            //             ->orWhere('status', 'active');
            //     })

            //     ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
            //     ->first();
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
        $absenIn       = Absence::whereDate('created_at', '=', date('Y-m-d'))->where('staff_id', $request->staff_id)->get();
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
            $absence_extra = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
                ->where('end', '>=', date('Y-m-d H:i:s'))
                ->where('category', 'extra')
                ->where('staff_id', $request->staff_id)
                ->where(function ($query) {
                    $query->where('status', 'approve')
                        ->orWhere('status', 'active');
                })

                ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
                ->first();
        }

        // return response()->json([
        //     'message' => 'Success',
        //     'leave' => $absence_extra,
        //     'permission' => $absenIn,
        //     'duty' => $showExtra,
        //     'date' => date('Y-m-d h:i:s')
        // ]);

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
                $extra = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                    ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                    ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                    ->where('staff_id', $request->staff_id)
                    ->where('absence_request_id', $absence_extra->id)
                // ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                // ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                    ->where('absence_logs.status', '=', 1)
                    ->where('absence_categories.type', '=', 'extra')
                    ->where('absence_categories.queue', '=', '2')
                    ->orderBy('absence_logs.id', 'DESC')
                    ->first();
            }

            // $absence_extra = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            //     ->where('end', '>=', date('Y-m-d H:i:s'))
            //     ->where('category', 'extra')
            //     ->where('staff_id', $request->staff_id)
            //     ->where(function ($query) {
            //         $query->where('status', 'approve')
            //             ->orWhere('status', 'active');
            //     })

            //     ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
            //     ->first();
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

            // cek absen masuk saat ini
            $absenceBreak = AbsenceLog::selectRaw('absence_id, absence_logs.status, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('staff_id', $request->staff_id)
                ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                ->where('absence_logs.status', '=', 0)
                ->where('absence_logs.absence_category_id', '=', 1)
                ->orderBy('absence_logs.id', 'DESC')
                ->first();

            $braeakCheck = null;
            // cek apa sudah melakukan absen masuk
            if ($absenceBreak) {
                // cek apa ada permisi di tanggal ini
                $absence_excuse = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
                    ->where('end', '>=', date('Y-m-d H:i:s'))
                    ->where('category', 'excuse')
                    ->where('staff_id', $request->staff_id)
                    ->where(function ($query) {
                        $query->where('status', 'approve')
                            ->orWhere('status', 'active');
                    })
                    ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
                    ->first();
                if ($absence_excuse) {
                    $excuse = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                        ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                        ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                        ->where('staff_id', $request->staff_id)
                        ->where('absence_request_id', $absence_excuse->id)
                        ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                        ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                        ->where('absence_logs.status', '=', 1)
                        ->where('absence_categories.type', '=', 'excuse')
                        ->where('absence_categories.queue', '=', '2')
                        ->orderBy('absence_logs.id', 'DESC')
                        ->first();
                    if ($excuse) {
                        $excuse_id = $excuse->id;
                    } else {
                        $excuseC = Absence_categories::where('type', 'excuse')->get();
                    }
                    $menuExcuse = "ON";
                }

                // cek apa tanggal ini ada dinas dalam kota
                $absence_visit = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
                    ->where('end', '>=', date('Y-m-d H:i:s'))
                    ->where('category', 'visit')
                    ->where('staff_id', $request->staff_id)
                    ->where(function ($query) {
                        $query->where('status', 'approve')
                            ->orWhere('status', 'active');
                    })
                    ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
                    ->first();
                if ($absence_visit) {
                    $visit = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                        ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                        ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                        ->where('staff_id', $request->staff_id)
                        ->where('absence_request_id', $absence_visit->id)
                        ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                        ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                        ->where('absence_logs.status', '=', 1)
                        ->where('absence_categories.type', '=', 'visit')
                        ->where('absence_categories.queue', '=', '2')
                        ->orderBy('absence_logs.id', 'DESC')
                        ->first();
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

                // cek apa ada data absen istirahat dengan expired_date waktu saat ini
                $break = AbsenceLog::selectRaw('absence_logs.status, absence_categories.type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.absence_id as absence_id, absence_logs.id as id')
                    ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                    ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                    ->where('staff_id', $request->staff_id)
                    ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                    ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                    ->where('absence_categories.type', '=', 'break')
                    ->where('absence_logs.status', '=', '1')
                    ->orderBy('absence_logs.id', 'ASC')
                    ->first();
                if ($break) {
                    $menuBreak = "ON";
                    //if any dinas dalam active
                    if ($menuVisit != 'OFF') {
                        $geofence_off_break = "ON";
                    }
                }
                // return response()->json([
                //     'message' =>  $break,
                //     // 'sss' => $excuse
                // ]);
                // cari absen out expired hari ini untuk mengambil expired date
                $absenceOut = AbsenceLog::selectRaw('absence_logs.expired_date, absence_logs.start_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id')
                    ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                    ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                    ->where('staff_id', $request->staff_id)
                // ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                    ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                    ->where('absence_logs.status', '=', 1)
                    ->where('absence_logs.absence_id', $absenceBreak->absence_id)
                    ->where('absence_categories.id', '=', '2')
                    ->where('absence_categories.type', '=', 'presence')
                // ->orderBy(DB::raw("FIELD(absence_logs.absence_category_id , \"3\", \"4\", \"2\" )"))
                    ->orderBy('absence_logs.start_date', 'ASC')
                    ->first();
                // jika belum ada absen istirahat
            }
            // cek end
            // return response()->json([
            //     'message' =>      $absenceOut,
            //     // 'sss' => $excuse
            // ]);

            if (date('w') == '0') {
                $day = '7';
            } else {
                $day = date('w');
            }

            // cek absen, apa ada absen hari ini
            $absence = AbsenceLog::selectRaw('absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('staff_id', $request->staff_id)
                ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                ->where('absence_logs.status', '=', 1)
                ->where('absence_categories.type', '=', 'presence')
                ->orderBy('absence_logs.start_date', 'ASC')
                ->first();

            // return ($absence);
            // cek absen, hari ini sudah absen masuk
            $pengecekanApaAdaAbsenMasuk = AbsenceLog::selectRaw('absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('staff_id', $request->staff_id)
                ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                ->where('absence_logs.status', '=', 0)
                ->where('absence_logs.absence_category_id', '1')
                ->orderBy('absence_logs.start_date', 'DESC')
                ->first();
            // return ($pengecekanApaAdaAbsenMasuk);

            // return $pengecekanApaAdaAbsenMasuk;
            if ($pengecekanApaAdaAbsenMasuk) {
                // pengecekan ada absen pulang atau tidak
                $pengecekanApaAdaAbsenLanjutan = AbsenceLog::selectRaw('absence_logs.expired_date,absence_logs.start_date, absence_logs.status as absence_log_status, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
                    ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                    ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                    ->where('staff_id', $request->staff_id)
                // ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                // ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                    ->where('absence_logs.status', '=', 1)
                    ->where('absence_logs.absence_id', $pengecekanApaAdaAbsenMasuk->absence_id)
                    ->whereNotIn('absence_category_id', [3, 4])
                    ->orderBy('absence_logs.start_date', 'ASC')

                    ->first();

                $pengecekanApaAdaAbsenLanjutanIs = AbsenceLog::selectRaw('absence_logs.expired_date,absence_logs.start_date, absence_logs.status as absence_log_status, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
                    ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                    ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                    ->where('staff_id', $request->staff_id)
                    ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                    ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                    ->where('absence_logs.status', '=', 1)
                    ->where('absence_logs.absence_id', $pengecekanApaAdaAbsenMasuk->absence_id)
                    ->whereIn('absence_category_id', [3, 4])
                    ->orderBy('absence_logs.start_date', 'ASC')

                    ->first();

                if (! $pengecekanApaAdaAbsenLanjutan) {
                    if (! $pengecekanApaAdaAbsenLanjutanIs) {
                        // cari absen out expired hari ini untuk mengambil expired date
                        $absenceOut = AbsenceLog::selectRaw('absence_logs.expired_date, absence_logs.start_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id')
                            ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                            ->where('staff_id', $request->staff_id)
                        // ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                            ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                            ->where('absence_logs.status', '=', 1)
                            ->where('absence_logs.absence_id', $pengecekanApaAdaAbsenMasuk->absence_id)
                            ->where('absence_categories.id', '=', '2')
                            ->where('absence_categories.type', '=', 'presence')
                            ->orderBy('absence_logs.start_date', 'ASC')
                            ->first();
                    }
                }

                // return $pengecekanApaAdaAbsenLanjutan;
                if ($pengecekanApaAdaAbsenLanjutan) {
                    if ($pengecekanApaAdaAbsenLanjutan->start_date <= date('Y-m-d H:i:s') && $pengecekanApaAdaAbsenLanjutan->expired_date >= date('Y-m-d H:i:s')) {
                        $absence = $pengecekanApaAdaAbsenLanjutan;
                    } else if ($pengecekanApaAdaAbsenLanjutan->absence_category_id === 2 && $pengecekanApaAdaAbsenLanjutan->expired_date >= date('Y-m-d H:i:s')) {
                        $absence = null;
                    } else {
                        if ($pengecekanApaAdaAbsenLanjutanIs) {
                            if ($pengecekanApaAdaAbsenLanjutanIs->start_date <= date('Y-m-d H:i:s') && $pengecekanApaAdaAbsenLanjutanIs->expired_date >= date('Y-m-d H:i:s')) {
                                $absence = $pengecekanApaAdaAbsenLanjutanIs;
                            } else {
                                $absence = null;
                            }
                        }
                    }
                }
                // return $absence;
            }

            $a1 = "1";

            // jika ada absen hari ini
            if ($absence) {
                if ($absence->shift_planner_id === 0) {
                    $absen = AbsenceLog::selectRaw('absence_categories.*, absence_logs.id as id, absence_id, work_type_days.start, work_type_days.end')
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
                    $absen = AbsenceLog::selectRaw('absence_logs.*, absence_categories.type, absence_categories.queue,shift_group_timesheets.start, shift_group_timesheets.end')->leftJoin('absences', 'absences.id', '=', 'absence_logs.absence_id')
                        ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
                        ->join('shift_group_timesheets', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
                        ->where('absence_logs.status', '=', 1)
                        ->where('absence_logs.id', $absence->id)
                        ->where('absence_categories.type', '=', 'presence')
                        ->orderBy('absence_logs.id', 'DESC')
                        ->first();

                    $a1          = "2";
                    $menuReguler = "ON";
                    $reguler     = $absen;
                }
                // $open = "Close";
                // if ($absen) {
                //     if ($absen->start_date <= date('Y-m-d H:i:s')) {
                //         $open = "Open";
                //     } else {
                //         $open = "Close";
                //     }
                // }
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
                        ->where('shift_planner_staffs.staff_id', '=', $request->staff_id)
                        ->whereDate('shift_planner_staffs.start', '=', date('Y-m-d'))
                        ->where('absence_logs.id', '=', null)
                        ->orderBy('shift_groups.queue', 'ASC')
                        ->get();

                    if (count($c) > 0) {
                        foreach ($c as $item) {

                            $data = [
                                'day_id'         => $day,
                                'shift_group_id' => $item->shift_group_id,
                                'staff_id'       => $request->staff_id,
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
                                        'staff_id'   => $request->staff_id,
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
                            ->where('staff_id', $request->staff_id)
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

                        if (! $absenceOut) {
                            if (date('Y-m-d H:i:s') > date('Y-m-d 21:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 23:59:59') || date('Y-m-d H:i:s') > date('Y-m-d 01:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 06:00:00')) {
                                $absence_extra = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
                                    ->where('end', '>=', date('Y-m-d H:i:s'))
                                    ->where('category', 'extra')
                                    ->where('staff_id', $request->staff_id)
                                    ->where(function ($query) {
                                        $query->where('status', 'approve')
                                            ->orWhere('status', 'active')
                                            ->orWhere('status', 'pending');
                                    })

                                    ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
                                    ->first();
                            } else {
                                $absence_extra = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
                                    ->where('end', '>=', date('Y-m-d H:i:s'))
                                    ->where('category', 'extra')
                                    ->where('staff_id', $request->staff_id)
                                    ->where(function ($query) {
                                        $query->where('status', 'approve')
                                            ->orWhere('status', 'active');
                                    })

                                    ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
                                    ->first();
                            }

                            if ($absence_extra) {
                                $menu = 'OFF';
                                if ($absence_extra) {
                                    $extra = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                                        ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                                        ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                                        ->where('staff_id', $request->staff_id)
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
                                'absenceOut'     => $absenceOut,
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
                                ->where('staff_id', $request->staff_id)->first();
                            if (! $c) {
                                $data = [
                                    'day_id'         => $day,
                                    'shift_group_id' => $request->shift_group_id,
                                    'staff_id'       => $request->staff_id,
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
                                            'staff_id'   => $request->staff_id,
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
                                        ->where('staff_id', $request->staff_id)
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

                            if (! $absenceOut) {
                                if (date('Y-m-d H:i:s') > date('Y-m-d 21:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 23:59:59') || date('Y-m-d H:i:s') > date('Y-m-d 01:00:00') && date('Y-m-d H:i:s') < date('Y-m-d 06:00:00')) {
                                    $absence_extra = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
                                        ->where('end', '>=', date('Y-m-d H:i:s'))
                                        ->where('category', 'extra')
                                        ->where('staff_id', $request->staff_id)
                                        ->where(function ($query) {
                                            $query->where('status', 'approve')
                                                ->orWhere('status', 'active')
                                                ->orWhere('status', 'pending');
                                        })

                                        ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
                                        ->first();
                                } else {
                                    $absence_extra = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
                                        ->where('end', '>=', date('Y-m-d H:i:s'))
                                        ->where('category', 'extra')
                                        ->where('staff_id', $request->staff_id)
                                        ->where(function ($query) {
                                            $query->where('status', 'approve')
                                                ->orWhere('status', 'active');
                                        })

                                        ->orderBy(DB::raw("FIELD(status , \"active\", \"approve\" )"))
                                        ->first();
                                }

                                if ($absence_extra) {
                                    $menu = 'OFF';
                                    if ($absence_extra) {
                                        $extra = AbsenceLog::selectRaw('absence_logs.status, absence_request_id , absence_id, absence_categories.type as absence_category_type, absence_logs.expired_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absence_logs.id as id')
                                            ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                                            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                                            ->where('staff_id', $request->staff_id)
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
                                    'absenceOut'     => $absenceOut,
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
                'absenceOut'     => $absenceOut,
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