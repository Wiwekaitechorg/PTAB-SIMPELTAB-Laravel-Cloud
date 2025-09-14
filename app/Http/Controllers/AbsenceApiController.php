<?php

namespace App\Http\Controllers\Api\V1\Absence;

use App\Absence;
use App\Absence_categories;
use App\AbsenceLog;
use App\AbsenceProblem;
use App\AbsenceRequest;
use App\AbsenceRequestLogs;
use App\Day;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\MessageLog;
use App\Requests;
use App\Shift;
use App\ShiftGroups;
use App\ShiftPlannerStaff;
use App\ShiftPlannerStaffs;
use App\ShiftStaff;
use App\StaffSpecial;
use App\User;
use App\WorkTypeDays;
use App\WorkUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Image;

class AbsenceApiController extends Controller
{

    // ketika sudah dibuatkan absence oleh sistem
    public function index(Request $request)
    {
        // untuk menampung data menu
        $reguler = "";
        $holiday = "";
        $break = "";
        $duty = "";
        $finish = "";
        $excuse_id = "";
        $absenceOut = [];

        $excuse = [];
        $visit = [];
        $duty = [];
        $extra = [];

        // mematikan menu
        $menuReguler = "OFF";
        $menuHoliday = "OFF";
        $menuBreak = "OFF";
        $menuExcuse = "OFF";
        $menuVisit = "OFF";
        $menuDuty = "OFF";
        $menuFinish = "OFF";
        $menuExtra = "OFF";
        $menuLeave = "OFF";
        $menuWaiting = "OFF";
        $menuPermission = "OFF";
        $geofence_off = "OFF";

        // get ID
        $excuseC = [];
        $visitC = [];
        $extraC = [];


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

        // return response()->json([
        //     'message' => 'Success',
        //     'menu' => [
        //         'menuReguler' =>  $geofence_off,
        //     ],
        //     'waitingMessage' => "Menunggu Persetujuan Cuti",
        //     'date' => date('Y-m-d h:i:s')
        // ]);
        $fingerprint = "ON";
        $camera = "ON";
        $gps = "ON";

        $coordinat = WorkUnit::join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $request->staff_id)->first();
        $staff_special = StaffSpecial::select('staff_specials.*')
            ->where('staff_id', $request->staff_id)->whereDate('expired_date', '>=', date('Y-m-d'))->first();
        if ($staff_special) {
            $fingerprint = $staff_special->fingerprint;
            $camera = $staff_special->camera;
            $gps = $staff_special->gps;
        }

        if ($gps == "OFF") {
            $geofence_off = "ON";
        }
        // return $gps;
        $problem = AbsenceProblem::where('id', $coordinat->absence_problem_id)->first();
        $menu = "";
        $leave = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
            ->where('end', '>=', date('Y-m-d H:i:s'))
            ->where('category', 'leave')
            ->where('staff_id', $request->staff_id)
            ->where(function ($query) {
                $query->where('status', 'approve')
                    ->orWhere('status', 'active');
                // ->orWhere('status', 'pending');
                // ->orWhere('status', 'close');
            })
            ->first();



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
            } else {
                $visitC = Absence_categories::where('type', 'visit')->get();
            }
            $menuVisit = "ON";
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

        // return response()->json([
        //     'message' => 'Success',
        //     'leave' => $absence_extra,
        //     'permission' => $extra,
        //     'duty' => $duty,
        //     'date' => date('Y-m-d h:i:s')
        // ]);

        // cek hari libur
        if ($leave) {


            if ($leave->status == "pending") {
                $menuWaiting = "ON";
                return response()->json([
                    'message' => 'Success',
                    'menu' => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak' => $menuBreak,
                        'menuExcuse' => $menuExcuse,
                        'menuDuty' => $menuDuty,
                        'menuFinish' =>  $menuFinish,
                        'menuLeave' => $menuLeave,
                        'menuWaiting' => $menuWaiting,
                    ],
                    'waitingMessage' => "Menunggu Persetujuan Cuti",
                    'leave' => $leave,
                    'date' => date('Y-m-d h:i:s')
                ]);
            }
            if ($leave->status == "close") {
                $menuWaiting = "ON";
                return response()->json([
                    'message' => 'Success',
                    'menu' => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak' => $menuBreak,
                        'menuExcuse' => $menuExcuse,
                        'menuDuty' => $menuDuty,
                        'menuFinish' =>  $menuFinish,
                        'menuLeave' => $menuLeave,
                        'menuWaiting' => $menuWaiting
                    ],
                    'waitingMessage' => "Menunggu Persetujuan Cuti",
                    'leave' => $leave,
                    'date' => date('Y-m-d h:i:s')
                ]);
            } else {
                $menuLeave = "ON";
                return response()->json([
                    'message' => 'Success',
                    'menu' => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak' => $menuBreak,
                        'menuExcuse' => $menuExcuse,
                        'menuDuty' => $menuDuty,
                        'menuFinish' =>  $menuFinish,
                        'menuLeave' => $menuLeave
                    ],
                    'leave' => $leave,
                    'date' => date('Y-m-d h:i:s')
                ]);
            }
        } else if ($permission) {
            if ($permission->status == "pending") {
                $menuWaiting = "ON";
                return response()->json([
                    'message' => 'Success',
                    'menu' => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak' => $menuBreak,
                        'menuExcuse' => $menuExcuse,
                        'menuDuty' => $menuDuty,
                        'menuFinish' =>  $menuFinish,
                        'menuWaiting' => $menuWaiting,
                        'menuPermission' =>  $menuPermission
                    ],
                    'waitingMessage' => "Menunggu Persetujuan Izin",
                    'date' => date('Y-m-d h:i:s')
                ]);
            } else if ($permission->status == "close") {
                $menuWaiting = "ON";
                return response()->json([
                    'message' => 'Success',
                    'menu' => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak' => $menuBreak,
                        'menuExcuse' => $menuExcuse,
                        'menuDuty' => $menuDuty,
                        'menuFinish' =>  $menuFinish,
                        'menuWaiting' => $menuWaiting,
                        'menuPermission' =>  $menuPermission
                    ],
                    'waitingMessage' => "Besok Anda Sudah Bisa Mulai Bekerja",
                    'date' => date('Y-m-d h:i:s')
                ]);
            } else {

                $menuPermission = "ON";
                if ($permission->type == "other") {
                    $menuWaiting = "ON";
                    $menuPermission = "OFF";
                }
                return response()->json([
                    'message' => 'Success',
                    'menu' => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak' => $menuBreak,
                        'menuExcuse' => $menuExcuse,
                        'menuDuty' => $menuDuty,
                        'menuFinish' =>  $menuFinish,
                        'menuWaiting' => $menuWaiting,
                        'menuPermission' =>  $menuPermission
                    ],
                    'permission' => $permission,
                    'waitingMessage' => "Anda Masih Izin",
                    'date' => date('Y-m-d h:i:s')
                ]);
            }
        } else if ($duty) {

            if ($duty->status == "pending") {
                $menuWaiting = "ON";
                return response()->json([
                    'message' => 'Success',
                    'menu' => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak' => $menuBreak,
                        'menuExcuse' => $menuExcuse,
                        'menuDuty' => $menuDuty,
                        'menuFinish' =>  $menuFinish,
                        'menuWaiting' => $menuWaiting
                    ],
                    'waitingMessage' => "Menunggu Persetujuan Dinas Luar",
                    'duty' => $duty,
                    'date' => date('Y-m-d h:i:s')
                ]);
            } else if ($duty->status == "close") {
                $menuWaiting = "ON";
                return response()->json([
                    'message' => 'Success',
                    'menu' => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak' => $menuBreak,
                        'menuExcuse' => $menuExcuse,
                        'menuDuty' => $menuDuty,
                        'menuFinish' =>  $menuFinish,
                        'menuWaiting' => $menuWaiting
                    ],
                    'waitingMessage' => "Besok Anda Sudah Bisa Mulai Bekerja",
                    'duty' => $duty,
                    'date' => date('Y-m-d h:i:s')
                ]);
            } else {
                $AbsenceRequestLogs = AbsenceRequestLogs::where('absence_request_id', $duty->id)
                    ->first();

                $menuDuty = 'ON';
                return response()->json([
                    'message' => 'Success',
                    'menu' => [
                        'menuReguler' => $menuReguler,
                        'menuHoliday' => $menuHoliday,
                        'menuBreak' => $menuBreak,
                        'menuExcuse' => $menuExcuse,
                        'menuDuty' => $menuDuty,
                        'menuFinish' =>  $menuFinish
                    ],
                    'AbsenceRequestLogs' => $AbsenceRequestLogs,
                    'duty' => $duty,
                    'coordinat' => $coordinat,
                    'date' => date('Y-m-d h:i:s')
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
                    ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                    ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
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
                'message' => 'Success',
                'menu' => [
                    'menuReguler' => $menuReguler,
                    'menuHoliday' => $menuHoliday,
                    'menuBreak' => $menuBreak,
                    'menuExcuse' => $menuExcuse,
                    'menuExtra' => $menuExtra,
                    'menuDuty' => $menuDuty,
                    'menuFinish' =>  $menuFinish,
                    'geolocationOff' => $geofence_off
                ],
                'sebelum' => 'yaa',
                'extraC' => $extraC,
                'extra' => $extra,
                'request_extra' =>  $absence_extra,
                'date' => date('Y-m-d h:i:s'),
                'lat' => $coordinat->lat,
                'fingerfrint' => $fingerprint,
                'selfie' => $camera,
                'gps' => $gps,
                'lng' => $coordinat->lng,
                'radius' => $coordinat->radius,
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
                    } else {
                        $visitC = Absence_categories::where('type', 'visit')->get();
                    }
                    $menuVisit = "ON";
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
                    $a1 = "2";
                    $menuReguler = "ON";
                    $reguler =  $absen;
                } else {
                    $absen = AbsenceLog::selectRaw('absence_logs.*, absence_categories.type, absence_categories.queue,shift_group_timesheets.start, shift_group_timesheets.end')->leftJoin('absences', 'absences.id', '=', 'absence_logs.absence_id')
                        ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
                        ->join('shift_group_timesheets', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
                        ->where('absence_logs.status', '=', 1)
                        ->where('absence_logs.id', $absence->id)
                        ->where('absence_categories.type', '=', 'presence')
                        ->orderBy('absence_logs.id', 'DESC')
                        ->first();

                    $a1 = "2";
                    $menuReguler = "ON";
                    $reguler =  $absen;
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
                                'day_id' => $day,
                                'shift_group_id' => $item->shift_group_id,
                                'staff_id' => $request->staff_id,
                                'created_at' => date('Y-m-d')
                            ];
                            $absence = Absence::create($data);
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
                                    $i = +$problem->duration;
                                    $str_date = date("Y-m-d H:i:s", strtotime('+ ' . $i * 60 . ' minutes', strtotime($str_date)));
                                    // $d[] = [$str_date];
                                    $message = "Anda Dalam Pengawasan, Buka Untuk Absen Lokasi";
                                    MessageLog::create([
                                        'staff_id' => $request->staff_id,
                                        'memo' => $message,
                                        'type' => 'check',
                                        'status' => 'pending',
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
                                    $timeout = date("Y-m-d H:i:s", strtotime('+ ' . ($list_absence[0]->duration * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                    $timein = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . $list_absence[0]->time)));

                                    $status = 0;
                                    if ($n === (count($list_absence) - 1)) {
                                        $status =  1;
                                    } else if ($n === 2 && $list_absence[$n]->type == "break") {
                                        $status =  1;
                                    } else {
                                        $status =  0;
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
                                    $upload_image->absence_id = $absence->id;
                                    $upload_image->shift_planner_id = $item->shift_planner_id;
                                    $upload_image->shift_group_timesheet_id = $list_absence[$n]->shift_group_timesheet_id;
                                    $upload_image->timein = $timein;
                                    $upload_image->timeout = $timeout;

                                    $upload_image->start_date = $start_date;
                                    $upload_image->expired_date = $expired_date;
                                    // sementara end
                                    $upload_image->created_at = date('Y-m-d H:i:s');
                                    $upload_image->updated_at = date('Y-m-d H:i:s');
                                    $upload_image->status = 1;
                                    $upload_image->absence_category_id =  $list_absence[$n]->absence_category_id;
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
                        $a1 = "1";
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
                                $a1 = "2";
                                $menuReguler = "ON";
                                $reguler =  $absen;
                            } else {
                                $absen = AbsenceLog::selectRaw('absence_logs.*, absence_categories.type, absence_categories.queue, shift_group_timesheets.start, shift_group_timesheets.end')->leftJoin('absences', 'absences.id', '=', 'absence_logs.absence_id')
                                    ->join('absence_categories', 'absence_categories.id', '=', 'absence_logs.absence_category_id')
                                    ->join('shift_group_timesheets', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
                                    ->where('absence_logs.status', '=', 1)
                                    ->where('absence_logs.id', $absence->id)
                                    ->where('absence_categories.type', '=', 'presence')
                                    ->orderBy('absence_logs.id', 'DESC')
                                    ->first();
                                $reguler = $absen;
                                $a1 = "2";
                                $menuReguler = "ON";
                            }

                            return response()->json([
                                'lat' => $coordinat->lat,
                                'fingerfrint' => $fingerprint,
                                'selfie' => $camera,
                                'gps' => $gps,
                                'lng' => $coordinat->lng,
                                'radius' => $coordinat->radius,
                                'reguler' => $reguler,
                                'work_type' => $coordinat->work_type_id,
                                'menu' => [
                                    'menuReguler' => $menuReguler,
                                    'menuHoliday' => $menuHoliday,
                                    'menuBreak' => $menuBreak,
                                    'menuExcuse' => $menuExcuse,
                                    'menuDuty' => $menuDuty,
                                    'menuFinish' =>  $menuFinish,
                                    'geolocationOff' => $geofence_off
                                ],
                                'break' => $break,
                                'date' => $coordinat->type,
                                'absence' => $absence,
                                'tesss' => $absen,
                                'a1' => $a1,
                            ]);
                        }

                        // test end
                    } else {


                        if (!$absenceOut) {
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
                                    'message' => 'Success',
                                    'menu' => [
                                        'menuReguler' => $menuReguler,
                                        'menuHoliday' => $menuHoliday,
                                        'menuBreak' => $menuBreak,
                                        'menuExcuse' => $menuExcuse,
                                        'menuExtra' => $menuExtra,
                                        'menuDuty' => $menuDuty,
                                        'menuFinish' =>  $menuFinish,
                                        'geolocationOff' => $geofence_off
                                    ],
                                    'extraC' => $extraC,
                                    'extra' => $extra,
                                    'request_extra' =>  $absence_extra,
                                    'date' => date('Y-m-d h:i:s'),
                                    'lat' => $coordinat->lat,
                                    'fingerfrint' => $fingerprint,
                                    'selfie' => $camera,
                                    'gps' => $gps,
                                    'selfie' => $camera,
                                    'lng' => $coordinat->lng,
                                    'radius' => $coordinat->radius,
                                ]);
                            } else {
                                $menuWaiting = "ON";
                                return response()->json([
                                    'message' => 'Absen Terkirim',
                                    'message' => 'sudah pulang',
                                    'data' =>   $c,
                                    'radius' => $coordinat->radius,
                                    'reguler' => $reguler,
                                    'break' => $break,
                                    'menu' => [
                                        'menuBreak' => $menuBreak,
                                        'menuExcuse' => $menuExcuse,
                                        'menuReguler' => $menuReguler,
                                        'menuHoliday' => $menuHoliday,
                                        'menuDuty' => $menuDuty,
                                        'menuFinish' => $menuFinish,
                                        'geolocationOff' => $geofence_off,
                                        'menuWaiting' => $menuWaiting

                                    ],
                                    'waitingMessage' => "Absen Sudah Selesai",
                                ]);
                            }
                        } else {
                            return response()->json([
                                'message' => 'Absen Terkirim',
                                'message' => 'ssssssa',
                                'excuseC' => $excuseC,
                                'excuse' => $excuse,
                                'request_excuse' =>  $absence_excuse,
                                'visitC' => $visitC,
                                'visit' => $visit,
                                'request_visit' =>  $absence_visit,
                                'data' =>   $c,

                                'lat' => $coordinat->lat,
                                'fingerfrint' => $fingerprint,
                                'selfie' => $camera,
                                'gps' => $gps,
                                'lng' => $coordinat->lng,
                                'radius' => $coordinat->radius,
                                'reguler' => $reguler,
                                'break' => $break,
                                'absenceOut' => $absenceOut,
                                'menu' => [
                                    'menuBreak' => $menuBreak,
                                    'menuExcuse' => $menuExcuse,
                                    'menuReguler' => $menuReguler,
                                    'menuHoliday' => $menuHoliday,
                                    'menuVisit' => $menuVisit,
                                    'menuDuty' => $menuDuty,
                                    'menuFinish' => $menuFinish,
                                    'geolocationOff' => $geofence_off
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
                            'menu' => [
                                'menuReguler' => $menuReguler,
                                'menuHoliday' => $menuHoliday,
                                'menuBreak' => $menuBreak,
                                'menuExcuse' => $menuExcuse,
                                'menuDuty' => $menuDuty,
                                'menuFinish' =>  $menuFinish,
                                'geolocationOff' => $geofence_off
                            ],
                            'date' => date('Y-m-d h:i:s')
                        ]);
                    } else {
                        $absen = Absence_categories::selectRaw('absence_categories.*, work_type_days.start, work_type_days.end')
                            ->join('work_type_days', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
                            ->join('work_types', 'work_type_days.work_type_id', '=', 'work_types.id')
                            ->where('work_types.id', $coordinat->work_type_id)
                            ->where('day_id', $day)
                            ->first();

                        // buat baru start
                        // cek apa sudah ada group absen di tanggal ini
                        if ($absen) {
                            $c = Absence::whereDate('created_at', '=', date('Y-m-d'))
                                ->where('staff_id', $request->staff_id)->first();
                            if (!$c) {
                                $data = [
                                    'day_id' => $day,
                                    'shift_group_id' => $request->shift_group_id,
                                    'staff_id' => $request->staff_id,
                                    'created_at' => date('Y-m-d')
                                ];
                                $absence = Absence::create($data);
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
                                        $i = +$problem->duration;
                                        $str_date = date("Y-m-d H:i:s", strtotime('+ ' . $i * 60 . ' minutes', strtotime($str_date)));
                                        // $d[] = [$str_date];
                                        $message = "Anda Dalam Pengawasan, Buka Untuk Absen Lokasi";
                                        MessageLog::create([
                                            'staff_id' => $request->staff_id,
                                            'memo' => $message,
                                            'type' => 'check',
                                            'status' => 'pending',
                                            'created_at' => $str_date,
                                        ]);
                                    }
                                }

                                // absence bermsalah end
                                $expired_date = date('Y-m-d H:i:s');
                                try {
                                    for ($n = 0; $n < count($list_absence); $n++) {
                                        $expired_date = date("Y-m-d H:i:s", strtotime('+' . (($list_absence[0]->duration + $list_absence[0]->duration_exp) * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                        $timeout = date("Y-m-d H:i:s", strtotime('+' . ($list_absence[0]->duration * 60) . ' minutes', strtotime(date('Y-m-d ' . $list_absence[0]->time))));
                                        $timein = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . $list_absence[0]->time)));

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
                                        $upload_image->absence_id = $absence->id;
                                        $upload_image->start_date = $start_date;
                                        $upload_image->expired_date = $expired_date;
                                        $upload_image->work_type_day_id = $list_absence[$n]->work_type_day_id;
                                        $upload_image->timein = $timein;
                                        $upload_image->timeout = $timeout;
                                        // sementara end
                                        $upload_image->created_at = date('Y-m-d H:i:s');
                                        $upload_image->updated_at = date('Y-m-d H:i:s');
                                        $upload_image->status = 1;
                                        $upload_image->absence_category_id =  $list_absence[$n]->absence_category_id;
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
                                    $a1 = "1";
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
                                            $a1 = "2";
                                            $menuReguler = "ON";
                                            $reguler =  $absen;
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
                                            'lat' => $coordinat->lat,
                                            'fingerfrint' => $fingerprint,
                                            'selfie' => $camera,
                                            'gps' => $gps,
                                            'lng' => $coordinat->lng,
                                            'radius' => $coordinat->radius,
                                            'reguler' => $reguler,
                                            'work_type' => $coordinat->work_type_id,
                                            'menu' => [
                                                'menuReguler' => $menuReguler,
                                                'menuHoliday' => $menuHoliday,
                                                'menuBreak' => $menuBreak,
                                                'menuExcuse' => $menuExcuse,
                                                'menuDuty' => $menuDuty,
                                                'menuFinish' =>  $menuFinish,
                                                'geolocationOff' => $geofence_off,
                                            ],
                                            'break' => $break,
                                            'date' => $coordinat->type,
                                            'absence' => $absence,
                                            'tesss' => $absen,
                                            'a1' => $a1,
                                        ]);
                                    }
                                    // test end
                                } catch (QueryException $ex) {
                                    return response()->json([
                                        'message' => 'gagal',
                                    ]);
                                }
                            }


                            if (!$absenceOut) {
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
                                        'message' => 'Success',
                                        'menu' => [
                                            'menuReguler' => $menuReguler,
                                            'menuHoliday' => $menuHoliday,
                                            'menuBreak' => $menuBreak,
                                            'menuExcuse' => $menuExcuse,
                                            'menuExtra' => $menuExtra,
                                            'menuDuty' => $menuDuty,
                                            'menuFinish' =>  $menuFinish,
                                            'geolocationOff' => $geofence_off
                                        ],
                                        'extraC' => $extraC,
                                        'extra' => $extra,
                                        'request_extra' =>  $absence_extra,
                                        'date' => date('Y-m-d h:i:s'),
                                        'lat' => $coordinat->lat,
                                        'fingerfrint' => $fingerprint,
                                        'selfie' => $camera,
                                        'gps' => $gps,
                                        'lng' => $coordinat->lng,
                                        'radius' => $coordinat->radius,
                                    ]);
                                } else {
                                    $menuWaiting = "ON";
                                    return response()->json([
                                        'message' => 'Absen Terkirim',
                                        'message' => 'sudah pulang',
                                        'data' =>   $c,
                                        'radius' => $coordinat->radius,
                                        'reguler' => $reguler,
                                        'break' => $break,
                                        'menu' => [
                                            'menuBreak' => $menuBreak,
                                            'menuExcuse' => $menuExcuse,
                                            'menuReguler' => $menuReguler,
                                            'menuHoliday' => $menuHoliday,
                                            'menuDuty' => $menuDuty,
                                            'menuFinish' => $menuFinish,
                                            'geolocationOff' => $geofence_off,
                                            'menuWaiting' => $menuWaiting
                                        ],
                                        'waitingMessage' => "Absen Sudah Selesai",
                                    ]);
                                }
                            } else {
                                return response()->json([
                                    'message' => 'Absen Terkirim',
                                    'message' => 'ssssssa',
                                    'excuseC' => $excuseC,
                                    'excuse' => $excuse,
                                    'request_excuse' =>  $absence_excuse,
                                    'visitC' => $visitC,
                                    'visit' => $visit,
                                    'request_visit' =>  $absence_visit,
                                    'data' =>   $c,

                                    'lat' => $coordinat->lat,
                                    'fingerfrint' => $fingerprint,
                                    'selfie' => $camera,
                                    'gps' => $gps,
                                    'lng' => $coordinat->lng,
                                    'radius' => $coordinat->radius,
                                    'reguler' => $reguler,
                                    'break' => $break,
                                    'absenceOut' => $absenceOut,
                                    'menu' => [
                                        'menuBreak' => $menuBreak,
                                        'menuExcuse' => $menuExcuse,
                                        'menuReguler' => $menuReguler,
                                        'menuHoliday' => $menuHoliday,
                                        'menuVisit' => $menuVisit,
                                        'menuDuty' => $menuDuty,
                                        'menuFinish' => $menuFinish,
                                        'geolocationOff' => $geofence_off
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
                'message' => 'Absen Terkirim',
                'message' => 'ssssssa',
                // 'data' =>   $c,

                'excuseC' => $excuseC,
                'excuse' => $excuse,
                'request_excuse' =>  $absence_excuse,

                'visitC' => $visitC,
                'visit' => $visit,
                'request_visit' =>  $absence_visit,
                // 'data' =>   $c,

                'lat' => $coordinat->lat,
                'fingerfrint' => $fingerprint,
                'selfie' => $camera,
                'gps' => $gps,
                'lng' => $coordinat->lng,
                'radius' => $coordinat->radius,
                'reguler' => $reguler,
                'break' => $break,
                'absenceOut' => $absenceOut,
                'menu' => [
                    'menuBreak' => $menuBreak,
                    'menuExcuse' => $menuExcuse,
                    'menuReguler' => $menuReguler,
                    'menuHoliday' => $menuHoliday,
                    'menuVisit' => $menuVisit,
                    'menuDuty' => $menuDuty,
                    'menuFinish' => $menuFinish,
                    'geolocationOff' => $geofence_off
                ],
            ]);
        }
    }

    public function store(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image = "";

        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        // jika ada figerprint bermasalah start

        if ($request->fingerprintError == "yes") {
            Absence::where('id', $request->absence_id)->update([
                'status_active' =>  '1'
            ]);
        }

        // jika ada figerprint bermasalah end

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image = $request->staff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path().'/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . '/' . $name_image);

            // tambah watermark end


            // $resource_image->move($basepath . $img_path, $name_image);
            $data_image = $name_image;
        }


        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }

        $absenceBefore = AbsenceLog::select('register')
            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            ->where('absence_id', $request->absence_id)
            ->where('queue', '1')
            ->where('type', 'presence')
            ->first();

        // mencari durasi
        $duration = 0;
        if ($request->queue == "2") {
            $absenceBefore2 = AbsenceLog::selectRaw('absence_logs.id, register, absence_logs.expired_date, absence_logs.absence_id')
                ->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_id', $request->absence_id)
                ->where('type', $request->type)
                ->where('queue', '1')
                ->first();
            // return response()->json([
            //     'message' => $absenceBefore2,
            // ]);
            $day3 = $absenceBefore2->register;
            $day3 = strtotime($day3);
            $day4 = date('Y-m-d H:i:s');
            $day4 = strtotime($day4);

            $duration = ($day4 - $day3) / 3600;
        }

        if ($absenceBefore != null) {
            $day1 = $absenceBefore->register;
        } else {
            $day1 = $absenceBefore->register;
        }

        if ($request->type == "presence" && $request->queue == "1") {
            $outDuration = 0;
        } else {
            $day1 = strtotime($day1);
            $day2 = date('Y-m-d H:i:s');
            $day2 = strtotime($day2);

            $outDuration = ($day2 - $day1) / 3600;
        }

        // variable early dan late
        $late = 0;
        $early = 0;
        try {
            $upload_image = AbsenceLog::where('id', $request->id)->first();
            if ($request->type == "presence") {
                if (date('Y-m-d H:i:s') > $upload_image->timein) {
                    $dayL1 = $upload_image->timein;
                    $dayL1 = strtotime($dayL1);
                    $dayL2 = date('Y-m-d H:i:s');
                    $dayL2 = strtotime($dayL2);

                    $late = ($dayL2 - $dayL1) / 3600;
                } else {
                    $dayE1 = $upload_image->timein;
                    $dayE1 = strtotime($dayE1);
                    $dayE2 = date('Y-m-d H:i:s');
                    $dayE2 = strtotime($dayE2);

                    $early = ($dayE1 - $dayE2) / 3600;
                }
            }


            $upload_image->late = $late;
            $upload_image->early = $early;


            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->staff_id;
            $upload_image->updated_by_staff_id = $request->staff_id;
            $upload_image->register = date('Y-m-d H:i:s');
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            $upload_image->duration = $duration;

            // sementara end
            $upload_image->register = date('Y-m-d H:i:s');
            $upload_image->updated_at = date('Y-m-d H:i:s');
            $upload_image->lat = $request->lat;
            $upload_image->lng = $request->lng;
            $upload_image->status =  0;
            $upload_image->accuracy = $request->accuracy;
            $upload_image->distance = $request->distance;
            // $upload_image->shift_id = $request->shift_id;

            $upload_image->save();

            if ($request->queue == "1") {
                $end = AbsenceLog::selectRaw('absence_logs.id, absence_logs.expired_date, absence_logs.absence_id')
                    ->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                    ->where('absence_id', $request->absence_id)
                    ->where('type', $request->type)
                    ->where('absence_logs.status', '1')
                    ->where('queue', '2')
                    ->first();
                AbsenceLog::where('id', $end->id)->update(['register' => date('Y-m-d H:i:s')]);
            }

            // start update request
            if ($upload_image->absence_request_id != "" && $upload_image->absence_request_id != null) {
                AbsenceRequest::where('id', $upload_image->absence_request_id)->update(['status' => 'close']);
            }

            // end update request
            if ($request->type != "extra") {
                $out = AbsenceLog::selectRaw('absence_logs.id, absence_logs.expired_date, absence_logs.absence_id')->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                    ->where('absence_id', $request->absence_id)
                    ->where('type', 'presence')
                    ->orderBy('queue', 'DESC')
                    ->first();
                AbsenceLog::where('id', $out->id)->update([
                    'register' => date('Y-m-d H:i:s'),
                    'duration' => $outDuration
                ]);
            }

            if ($request->queue == "1" && $request->type == "presence") {
                $check = AbsenceLog::where('absence_id', $out->absence_id)
                    ->where('absence_category_id', 3)
                    ->first();
                if (!$check) {
                    // buat absen istirahat
                    AbsenceLog::create([

                        'absence_id' => $out->absence_id,
                        'absence_category_id' => 3,
                        'status' => '1',
                        'expired_date' => $out->expired_date,
                        'start_date' => date('Y-m-d H:i:10'),

                    ]);
                    AbsenceLog::create([

                        'absence_id' => $out->absence_id,
                        'absence_category_id' => 4,
                        'status' => '1',
                        'expired_date' => $out->expired_date,
                        'start_date' => date('Y-m-d H:i:11'),

                    ]);
                    // buat absen istirahat end
                }
            }


            return response()->json([
                'message' => 'Absen Terkirim',
                'data' => $upload_image,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // create absen baru
    public function storeLocation(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image = "";
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }
        // cari durasi kerja

        $workDuration = Absence::join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
            ->join('work_type_days', 'work_type_days.id', '=', 'absence_logs.work_type_day_id')
            ->select('work_type_days.duration')->where('absence_id', $request->absence_id)->where('work_type_days.absence_category_id', '1')
            ->first();
        if (!$workDuration) {
            $workDuration = Absence::join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
                ->join('shift_group_timesheets', 'shift_group_timesheets.id', '=', 'absence_logs.shift_group_timesheet_id')
                ->select('shift_group_timesheets.duration')->where('absence_id', $request->absence_id)->where('shift_group_timesheets.absence_category_id', '1')
                ->first()->duration;
        } else {
            $workDuration = $workDuration->duration;
        }
        // set durasi start
        $absenceBefore = AbsenceLog::select('register')
            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            ->where('absence_id', $request->absence_id)
            ->where('queue', '1')
            ->where('type', 'presence')
            ->first();

        // mencari durasi
        // $duration = 0;
        // if ($request->queue == "2") {
        //     $absenceBefore2 = AbsenceLog::selectRaw('absence_logs.id, register, absence_logs.expired_date, absence_logs.absence_id')
        //         ->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
        //         ->where('absence_id', $request->absence_id)
        //         ->where('type', $request->type)
        //         ->where('absence_request_id', $request->absence_request_id)
        //         // ->where('absence_logs.status', '1')
        //         ->where('queue', '1')
        //         ->first();
        //     // AbsenceLog::where('id', $end->id)->update(['register' => date('Y-m-d H:i:s')]);
        //     $day3 = $absenceBefore2->register;
        //     $day3 = strtotime($day3);
        //     $day4 = date('Y-m-d H:i:s');
        //     $day4 = strtotime($day4);

        //     $duration = ($day4 - $day3) / 3600;
        // }


        $day1 = $absenceBefore->register;
        $day1 = strtotime($day1);
        $day2 = date('Y-m-d H:i:s');
        $day2 = strtotime($day2);

        $outDuration = ($day2 - $day1) / 3600;

        if ($request->absence_category_id == "11") {
            if ($outDuration < ($workDuration / 2)) {
                Absence::where('id', $request->absence_id)->update([
                    'status_active' => '3'
                ]);
            }
        }

        // set durasi end

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image = $request->satff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path().'/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . '/' . $name_image);

            // tambah watermark end
            // $resource_image->move($basepath . $img_path, $name_image);
            $data_image = $name_image;
        }


        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }

        try {
            $upload_image = new AbsenceLog;
            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->satff_id;
            $upload_image->updated_by_staff_id = $request->satff_id;
            $upload_image->register = date('Y-m-d H:i:s');
            $upload_image->absence_id = $request->absence_id;
            $upload_image->absence_request_id = $request->absence_request_id;
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            // $upload_image->duration = $duration;
            // sementara end
            $upload_image->register = date('Y-m-d H:i:s');
            $upload_image->created_at = date('Y-m-d H:i:s');
            $upload_image->updated_at = date('Y-m-d H:i:s');
            $upload_image->absence_category_id = $request->absence_category_id;
            $upload_image->lat = $request->lat;
            $upload_image->lng = $request->lng;
            $upload_image->status =  0;
            $upload_image->expired_date = $request->expired_date;
            $upload_image->start_date = date('Y-m-d H:i:10');
            $upload_image->accuracy = $request->accuracy;
            $upload_image->distance = $request->distance;
            // $upload_image->shift_id = $request->shift_id;

            $upload_image->save();

            $out = AbsenceLog::selectRaw('absence_logs.id, absence_logs.expired_date, absence_logs.absence_id')->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_id', $request->absence_id)
                ->where('type', 'presence')
                ->orderBy('queue', 'DESC')
                ->first();

            AbsenceLog::where('id', $out->id)->update(['register' => date('Y-m-d H:i:s'), 'duration' => $outDuration]);

            $breakin = AbsenceLog::selectRaw('absence_logs.id, absence_logs.expired_date, absence_logs.absence_id')->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_id', $request->absence_id)
                ->where('type', 'break')
                ->where('queue', '1')
                ->first();

            AbsenceLog::where('id', $breakin->id)->update(['register' => date('Y-m-d H:i:s'), 'status' => '0']);

            $breakout = AbsenceLog::selectRaw('absence_logs.id, absence_logs.expired_date, absence_logs.absence_id')->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_id', $request->absence_id)
                ->where('type', 'break')
                ->where('queue', '2')
                ->first();

            AbsenceLog::where('id', $breakout->id)->update(['register' => date('Y-m-d H:i:s'), 'status' => '0']);

            // buat absen endnya
            $absenceR = AbsenceRequest::where('id', $request->absence_request_id)->first();

            // cek absen sudah ada atau tidak ada
            $check = AbsenceLog::where('absence_id', $request->absence_id)
                ->where('absence_category_id', $request->absence_category_id_end)->first();
            if (!$check) {
                if ($request->absence_category_id == "11" && $absenceR->type == "out") {
                    AbsenceLog::create([
                        'absence_id' => $request->absence_id,
                        'absence_category_id' => $request->absence_category_id_end,
                        'status' => '0',
                        'register' => date('Y-m-d H:i:s'),
                        'absence_request_id' => $request->absence_request_id,
                        'expired_date' => $request->expired_date,
                        'start_date' => date('Y-m-d H:i:10'),

                    ]);
                    AbsenceLog::where('id', $out->id)->update(['register' => date('Y-m-d H:i:s'), 'duration' => $outDuration, 'status' => '0']);
                } else {
                    AbsenceLog::create([
                        'absence_id' => $request->absence_id,
                        'absence_category_id' => $request->absence_category_id_end,
                        'status' => '1',
                        'absence_request_id' => $request->absence_request_id,
                        'expired_date' => $request->expired_date,
                        'start_date' => date('Y-m-d H:i:10'),

                    ]);
                }


                // cek absen sudah ada atau tidak ada

                AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'active']);
                if ($request->absence_category_id == "11" && $absenceR->type == "out") {
                    AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'close']);
                }
                return response()->json([
                    'message' => 'Absen Terkirim',
                    'data' => $upload_image,
                ]);
            } else {
                return response()->json([
                    'message' => 'Tadi Sudah Absen',
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // absen lokasi end
    public function storeLocationEnd(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image = "";
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        $absence_check = AbsenceLog::where('id', $request->id)->where('absence_request_id', $request->absence_request_id)->first();
        if ($absence_check->absence_category_id == "12") {

            Absence::where('id', $absence_check->absence_id)->update([
                'status_active' => ""
            ]);
        }

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image = $request->staff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;
            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path().'/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . '/' . $name_image);

            // tambah watermark end
            // $resource_image->move($basepath . $img_path, $name_image);
            $data_image = $name_image;
        }


        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }

        $absenceBefore = AbsenceLog::select('register')
            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            ->where('absence_id', $request->absence_id)
            ->where('queue', '1')
            ->where('type', 'presence')
            ->first();

        // mencari durasi
        $duration = 0;
        if ($request->queue == "2") {
            $absenceBefore2 = AbsenceLog::selectRaw('absence_logs.id, register, absence_logs.expired_date, absence_logs.absence_id')
                ->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_id', $request->absence_id)
                ->where('type', $request->type)
                ->where('absence_request_id', $request->absence_request_id)
                // ->where('absence_logs.status', '1')
                ->where('queue', '1')
                ->first();
            // AbsenceLog::where('id', $end->id)->update(['register' => date('Y-m-d H:i:s')]);
            $day3 = $absenceBefore2->register;
            $day3 = strtotime($day3);
            $day4 = date('Y-m-d H:i:s');
            $day4 = strtotime($day4);

            $duration = ($day4 - $day3) / 3600;
        }


        $day1 = $absenceBefore->register;
        $day1 = strtotime($day1);
        $day2 = date('Y-m-d H:i:s');
        $day2 = strtotime($day2);

        $outDuration = ($day2 - $day1) / 3600;

        try {
            $upload_image = AbsenceLog::where('id', $request->id)->where('absence_request_id', $request->absence_request_id)->first();
            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->staff_id;
            $upload_image->updated_by_staff_id = $request->staff_id;
            $upload_image->register = date('Y-m-d H:i:s');
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            $upload_image->duration = $duration;
            // sementara end
            $upload_image->register = date('Y-m-d H:i:s');
            $upload_image->updated_at = date('Y-m-d H:i:s');
            $upload_image->lat = $request->lat;
            $upload_image->lng = $request->lng;
            $upload_image->status =  0;
            $upload_image->accuracy = $request->accuracy;
            $upload_image->distance = $request->distance;
            // $upload_image->shift_id = $request->shift_id;

            $upload_image->save();

            if ($request->queue == "1") {
                $end = AbsenceLog::selectRaw('absence_logs.id, absence_logs.expired_date, absence_logs.absence_id')
                    ->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                    ->where('absence_id', $request->absence_id)
                    ->where('type', $request->type)
                    ->where('absence_logs.status', '1')
                    ->where('queue', '2')
                    ->first();
                AbsenceLog::where('id', $end->id)->update(['register' => date('Y-m-d H:i:s')]);
            }

            // start update request
            if ($upload_image->absence_request_id != "" && $upload_image->absence_request_id != null) {
                AbsenceRequest::where('id', $upload_image->absence_request_id)->update(['status' => 'close']);
            }

            // end update request
            if ($request->type != "extra") {
                $out = AbsenceLog::selectRaw('absence_logs.id, absence_logs.expired_date, absence_logs.absence_id')->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                    ->where('absence_id', $request->absence_id)
                    ->where('type', 'presence')
                    ->orderBy('queue', 'DESC')
                    ->first();
                AbsenceLog::where('id', $out->id)->update([
                    'register' => date('Y-m-d H:i:s'),
                    'duration' => $outDuration
                ]);
            }

            // if ($request->queue == "1" && $request->type == "presence") {

            //     // buat absen istirahat
            //     AbsenceLog::create([

            //         'absence_id' => $out->absence_id,
            //         'absence_category_id' => 3,
            //         'status' => '1',
            //         'expired_date' => $out->expired_date,
            //         'start_date' => date('Y-m-d H:i:10'),

            //     ]);
            //     AbsenceLog::create([

            //         'absence_id' => $out->absence_id,
            //         'absence_category_id' => 4,
            //         'status' => '1',
            //         'expired_date' => $out->expired_date,
            //         'start_date' => date('Y-m-d H:i:11'),

            //     ]);
            //     // buat absen istirahat end
            // }


            return response()->json([
                'message' => 'Absen Terkirim',
                'data' => $upload_image,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // create absen baru
    public function storeLocationDuty(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/RequestFile";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image = "";
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        $AbsenceRequestLogs = AbsenceRequestLogs::where('absence_request_id', $request->absence_request_id)
            ->first();

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image = $request->satff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;
            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path().'/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . '/' . $name_image);

            // tambah watermark end
            // $resource_image->move($basepath . $img_path, $name_image);
            $data_image = $name_image;
        }


        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }
        if ($AbsenceRequestLogs) {
            $type = "request_log_out";
        } else {
            $type = "request_log_in";
        }

        try {

            $upload_image = new AbsenceRequestLogs;
            $upload_image->image = $data_image;
            // sementara start
            // $upload_image->created_by_staff_id = $request->staff_id;
            // $upload_image->updated_by_staff_id = $request->staff_id;
            $upload_image->register = date('Y-m-d H:i:s');
            $upload_image->absence_request_id = $request->absence_request_id;
            $upload_image->type = $type;
            $upload_image->memo = $request->memo;
            $upload_image->created_at = date('Y-m-d H:i:s');
            $upload_image->updated_at = date('Y-m-d H:i:s');
            $upload_image->lat = $request->lat;
            $upload_image->lng = $request->lng;
            $upload_image->accuracy = $request->accuracy;
            $upload_image->distance = $request->distance;
            // $upload_image->shift_id = $request->shift_id;

            $upload_image->save();

            if ($AbsenceRequestLogs) {
                $absenceRequest = AbsenceRequest::select('category', DB::raw('DATE(start) as start'), DB::raw('DATE(end) as end'))
                    ->where('id', $request->absence_request_id)->first();
                AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'close', 'attendance' => date("Y-m-d H:i:s", strtotime('- ' . 1 . ' days', strtotime(date('Y-m-d ' . '23:59:59'))))]);
                $check = AbsenceLog::join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                    ->where('absence_category_id', $absenceRequest->category)
                    ->where('absences.staff_id', $request->staff_id)
                    ->where('absences.absence_request_id', $request->absence_request_id)
                    ->whereDate('absences.created_at', $absenceRequest->start)
                    ->first();
                if (!$check) {
                    Absence::whereDate('created_at', '>', date('Y-m-d'))->delete();
                    AbsenceLog::whereDate('register', '>', date('Y-m-d'))->delete();
                    $begin = strtotime($absenceRequest->start);
                    $end   = strtotime(date('Y-m-d'));

                    for ($i = $begin; $i <= $end; $i = $i + 86400) {
                        $holiday = Holiday::whereDate('start', '<=', date('Y-m-d', $i))->whereDate('end', '>=', date('Y-m-d', $i))->first();
                        if (!$holiday) {
                            if (date("w", strtotime(date('Y-m-d', $i))) != 0 && date("w", strtotime(date('Y-m-d', $i))) != 6) {

                                Absence::create([
                                    'day_id' => date("w", strtotime(date('Y-m-d', $i))),
                                    'staff_id' => $request->staff_id,
                                    'created_at' => date('Y-m-d H:i:s', $i),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                                AbsenceLog::create([
                                    'absence_category_id' => $absenceRequest->category == "duty" ? 7 : 8,
                                    'lat' => '',
                                    'lng' => '',
                                    'register' => date('Y-m-d', $i),
                                    'absence_id' => '',
                                    'duration' => '',
                                    'status' => ''
                                ]);
                            }
                        }
                    }
                } else {
                    return response()->json([
                        'message' => 'Tadi Sudah Absen',
                    ]);
                }
            } else {
                AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'active']);
                // $absenceRequest = AbsenceRequest::select('category', DB::raw('DATE(start) as start'), DB::raw('DATE(end) as end'))
                //     ->where('id', $request->absence_request_id)->first();
            }


            return response()->json([
                'message' => 'Absen Terkirim',
                'data' => $upload_image,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // create absen baru
    public function storeLocationExtra(Request $request)
    {
        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image = "";
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        $cekStatus = AbsenceRequest::where('id', $request->absence_request_id)->first();

        if ($cekStatus->status && $cekStatus->status == "pending") {
            $data = [
                'day_id' => $day,
                'staff_id' => $request->staff_id,
                'created_at' => date('Y-m-d'),
                'status_active' => '2'
            ];
        } else {
            $data = [
                'day_id' => $day,
                'staff_id' => $request->staff_id,
                'created_at' => date('Y-m-d')
            ];
        }
        $check = AbsenceLog::join('absences', 'absences.id', '=', 'absence_logs.absence_id')
            ->where('absence_category_id', $request->absence_category_id)
            ->where('absences.staff_id', $request->staff_id)
            ->where('absence_logs.absence_request_id', $request->absence_request_id)
            ->whereDate('absences.created_at', date('Y-m-d'))
            ->first();

        if (!$check) {
            $absence = Absence::create($data);

            if ($request->file('image')) {
                $resource_image = $request->file('image');
                $name_image = $request->staff_id;
                $file_ext_image = $request->file('image')->extension();
                // $id_name_image = str_replace(' ', '-', $id_image);
                // $nameIMG = 
                $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

                // tambah watermark start
                $image = $request->file('image');

                $imgFile = Image::make($image->getRealPath());

                $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

                $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                    $font->file(base_path().'/public' . '/font/Titania-Regular.ttf');
                    $font->size(14);
                    $font->color('#000000');
                    $font->valign('top');
                })->save($basepath . '/' . $name_image);

                // tambah watermark end

                // $resource_image->move($basepath . $img_path, $name_image);
                $data_image = $name_image;
            }


            if ($responseImage != '') {
                return response()->json([
                    'message' => $responseImage,
                ]);
            }
        }

        try {
            if (!$check) {
                $upload_image = new AbsenceLog;
                $upload_image->image = $data_image;
                // sementara start
                $upload_image->created_by_staff_id = $request->staff_id;
                $upload_image->updated_by_staff_id = $request->staff_id;
                $upload_image->register = date('Y-m-d H:i:s');
                $upload_image->absence_id = $absence->id;
                $upload_image->absence_request_id = $request->absence_request_id;
                // $upload_image->late = $late;
                // $upload_image->early = $early;
                // $upload_image->duration = $duration;
                // sementara end
                $upload_image->register = date('Y-m-d H:i:s');
                $upload_image->created_at = date('Y-m-d H:i:s');
                $upload_image->updated_at = date('Y-m-d H:i:s');
                $upload_image->absence_category_id = $request->absence_category_id;
                $upload_image->lat = $request->lat;
                $upload_image->lng = $request->lng;
                $upload_image->status =  0;
                $upload_image->expired_date = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime(date('Y-m-d H:i:s'))));
                $upload_image->start_date = date('Y-m-d H:i:10');
                $upload_image->accuracy = $request->accuracy;
                $upload_image->distance = $request->distance;
                // $upload_image->shift_id = $request->shift_id;

                $upload_image->save();




                // buat absen endnya
                AbsenceLog::create([
                    'absence_id' => $absence->id,
                    'absence_category_id' => $request->absence_category_id_end,
                    'status' => '1',
                    'absence_request_id' => $request->absence_request_id,
                    'expired_date' =>  date("Y-m-d H:i:s", strtotime('+12 hours', strtotime(date('Y-m-d H:i:s')))),
                    'start_date' => date('Y-m-d H:i:10'),

                ]);
                AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'active']);


                return response()->json([
                    'message' => 'Absen Terkirim',
                    'data' => $upload_image,
                ]);
            } else {
                return response()->json([
                    'message' => 'Tadi Sudah Absen',
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    public function leaveEnd(Request $request)
    {
        try {
            $absenceRequest =  AbsenceRequest::where('id', $request->id)->first();

            if (date('Y-m-d') > $absenceRequest->start) {
                AbsenceRequest::where('id', $request->id)->update(['status' => 'close', 'attendance' => date("Y-m-d H:i:s", strtotime('- ' . 1 . ' days', strtotime(date('Y-m-d ' . '23:59:59'))))]);
                $absenceLog = AbsenceLog::where('absence_request_id', $absenceRequest->id)->get();
                foreach ($absenceLog as $d) {
                    $deleteAbsence = Absence::where('id', $absence_id)->first();
                    if ($deleteAbsence) {
                        Absence::where('id', $d->id)->delete();
                    }
                }

                AbsenceLog::where('absence_request_id', $absenceRequest->id)->delete();

                $begin = strtotime($absenceRequest->start);
                $end   = strtotime(date('Y-m-d'));

                for ($i = $begin; $i < $end; $i = $i + 86400) {
                    $holiday = Holiday::whereDate('start', '<=', date('Y-m-d', $i))->whereDate('end', '>=', date('Y-m-d', $i))->first();
                    if (!$holiday) {
                        if (date("w", strtotime(date('Y-m-d', $i))) != 0 && date("w", strtotime(date('Y-m-d', $i))) != 6) {

                            $ab_id = Absence::create([
                                'day_id' => date("w", strtotime(date('Y-m-d', $i))),
                                'staff_id' => $absenceRequest->staff_id,
                                'created_at' => date('Y-m-d H:i:s', $i),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                            AbsenceLog::create([
                                'absence_category_id' => $absenceRequest->category == "leave" ? 8 : 13,
                                'lat' => '',
                                'lng' => '',
                                'absence_request_id' => $absenceRequest->id,
                                'register' => date('Y-m-d', $i),
                                'absence_id' => $ab_id->id,
                                'duration' => '',
                                'status' => ''
                            ]);
                        }
                    }
                }
                return response()->json([
                    'message' => 'Absen Terkirim',
                    // 'data' => $upload_image,
                ]);
            } else {
                return response()->json([
                    'message' => 'tidak bisa diakhiri hari ini karena tanggal mulai sama dengan tanggal sekarang',
                    // 'data' => $upload_image,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    public function checkAbsenceLocation(Request $request)
    {
        $absence = Absence::where('user_id', $request->user_id)->where('requests_id', $request->requests_id)->whereDate('register', '=', date('Y-m-d'))->first();

        if ($absence != null) {
            $cek = "1";
        } else {
            $cek = "0";
        }
        return response()->json([
            'message' => 'success',
            'data' => $cek,
        ]);
    }

    public function history(Request $request)
    {
        $data = [];
        $absence = Absence::join('days', 'days.id', '=', 'absences.day_id')
            ->selectRaw('absences.id,DATE(created_at) as created_at, days.name as day_name')
            ->where('staff_id', $request->staff_id)
            ->FilterDate($request->from, $request->to)
            ->groupByRaw('DATE(created_at)')
            ->orderBy('created_at', 'DESC')
            ->get();

        foreach ($absence as $d) {
            // $absence_log = AbsenceLog::join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            //     ->select('absence_logs.*', 'absence_categories.title as category_title', 'IF("register != "", absence_logs.id, "")')
            //     ->where('absence_logs.register', '!=', '')
            //     ->where('absence_id', '=', $d->id)->get();
            $absence_log = AbsenceLog::join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->select(
                    'absence_logs.*',
                    'absence_categories.title as category_title',
                    DB::raw("(CASE WHEN status = 0 THEN register ELSE '2020:01:01 00:00:00' END) as register")
                )
                //->where('absence_logs.register', '!=', '')
                ->whereNotNull('absence_logs.register')
                ->where('absence_id', '=', $d->id)->get();
            if (count($absence_log) > 0) {
                if ($absence_log[0]->absence_category_id != 9 && $absence_log[0]->absence_category_id != 10) {
                    $data[] = ['date' => $d->created_at, 'day_name' => $d->day_name, 'list' => $absence_log];
                }
            }
        }
        return response()->json([
            'message' => 'success',
            'data' => $data,
            'tesss' => $absence,
        ]);
    }

    public function schedule(Request $request)
    {
        $type = "";
        $schedule = [];
        $coordinat = WorkUnit::join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $request->staff_id)->first();
        $type = $coordinat->type;
        if ($coordinat->type == "shift") {

            $list_absence = ShiftPlannerStaffs::select(DB::raw('DATE(shift_planner_staffs.start) AS date'), 'shift_planner_staffs.id as shift_planner_id', 'shift_planner_staffs.shift_group_id')
                ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
                ->leftJoin('absence_logs', 'shift_planner_staffs.id', '=', 'absence_logs.shift_planner_id')
                ->where('shift_planner_staffs.staff_id', '=', $request->staff_id)
                ->groupBy('shift_planner_staffs.id')
                // ->whereDate('shift_planner_staffs.start', '>=', date('Y-m-d'))
                ->orderBy('shift_groups.queue', 'ASC')
                ->get();

            foreach ($list_absence as $data) {
                $schedule[] = [
                    'id' => $data->shift_planner_id,
                    'date' => $data->date,
                    'list' => ShiftGroups::selectRaw('duration, duration_exp, type, time, start, absence_category_id,shift_group_timesheets.id as shift_group_timesheet_id ')
                        ->join('shift_group_timesheets', 'shift_group_timesheets.shift_group_id', '=', 'shift_groups.id')
                        ->join('absence_categories', 'shift_group_timesheets.absence_category_id', '=', 'absence_categories.id')
                        ->where('shift_groups.id', $data->shift_group_id)
                        ->orderBy('absence_categories.id', 'ASC')
                        ->get()

                ];
            }
            $schedule = $list_absence;
        } else {
            $day = Day::get();
            // $list_absence = WorkTypeDays::selectRaw('duration, duration_exp, type, time, start, absence_category_id,work_type_days.id as work_type_day_id ')
            //     ->join('absence_categories', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
            //     ->where('work_type_id', $coordinat->work_type_id)
            //     ->where('day_id', $day)
            //     ->orderBy('day_id', 'ASC')
            //     ->get();
            foreach ($day as $data) {
                $schedule[] = [
                    'day' => $data->name,
                    'list' => WorkTypeDays::selectRaw('duration, duration_exp, type, time, start, absence_category_id,work_type_days.id as work_type_day_id ')
                        ->join('absence_categories', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
                        ->where('work_type_id', $coordinat->work_type_id)
                        ->where('day_id', $data->id)
                        ->orderBy('day_id', 'ASC')
                        ->get()

                ];
            }
        }
        return response()->json([
            'message' => 'success',
            'type' => $type,
            'data' => $schedule,
        ]);
    }

    public function holiday(Request $request)
    {
        $holiday = Holiday::paginate(3, ['*'], 'page', $request->page);
        return response()->json([
            'message' => 'success',
            'data' => $holiday,
        ]);
    }

    // untuk lembur
    public function storeExtra(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image = "";
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image = $request->staff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path().'/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . '/' . $name_image);

            // tambah watermark end

            // $resource_image->move($basepath . $img_path, $name_image);
            $data_image = $name_image;
        }


        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }

        $absenceBefore = AbsenceLog::select('register')
            ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            ->where('absence_id', $request->absence_id)
            ->where('queue', '1')
            ->where('type', 'extra')
            ->first();

        // mencari durasi
        $duration = 0;


        if ($absenceBefore != null) {
            $day1 = $absenceBefore->register;
        } else {
            $day1 = $absenceBefore->register;
        }


        $day1 = strtotime($day1);
        $day2 = date('Y-m-d H:i:s');
        $day2 = strtotime($day2);

        $duration = ($day2 - $day1) / 3600;

        if ($duration > 8) {
            $duration = 8;
        }

        // variable early dan late
        $late = 0;
        $early = 0;
        try {
            $upload_image = AbsenceLog::where('id', $request->id)->first();



            $upload_image->late = $late;
            $upload_image->early = $early;


            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->staff_id;
            $upload_image->updated_by_staff_id = $request->staff_id;
            $upload_image->register = date('Y-m-d H:i:s');
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            $upload_image->duration = $duration;
            // sementara end
            $upload_image->register = date('Y-m-d H:i:s');
            $upload_image->updated_at = date('Y-m-d H:i:s');
            $upload_image->lat = $request->lat;
            $upload_image->lng = $request->lng;
            $upload_image->status =  0;
            $upload_image->accuracy = $request->accuracy;
            $upload_image->distance = $request->distance;
            // $upload_image->shift_id = $request->shift_id;

            $upload_image->save();

            // start update request
            if ($upload_image->absence_request_id != "" && $upload_image->absence_request_id != null) {
                AbsenceRequest::where('id', $upload_image->absence_request_id)->update(['status' => 'close']);
            }

            return response()->json([
                'message' => 'Absen Terkirim',
                'data' => $upload_image,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // extra
    public function historyExtra(Request $request)
    {
        $data = [];
        $absence = Absence::join('days', 'days.id', '=', 'absences.day_id')
            ->join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
            ->selectRaw('absences.id,DATE(absences.created_at) as created_at, days.name as day_name')
            ->where('staff_id', $request->staff_id)
            ->where('absence_logs.absence_category_id', '9')
            ->FilterDate($request->from, $request->to)
            ->groupBy('absences.id')->get();

        foreach ($absence as $d) {
            $absence_log = AbsenceLog::join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')->selectRaw('absence_logs.*, absence_categories.title as category_title')->where('absence_id', '=', $d->id)->get();
            if (count($absence_log) > 0) {
                // if ($absence_log[0]->absence_category_id == "9") {
                $data[] = ['date' => $d->created_at, 'day_name' => $d->day_name, 'list' => $absence_log];
                // }
            }
        }
        return response()->json([
            'message' => 'success',
            'data' => $data,
            'tesss' => $absence,
        ]);
    }

    public function sickAdd(Request $request)
    {
        $dataForm = json_decode($request->form);
        try {
            $absenceRequest =  AbsenceRequest::where('id', $dataForm->id)->first();
            // return response()->json([
            //     'message' => $dataForm->end,
            //     // 'data' => $upload_image,
            // ]);
            if ($dataForm->end > $absenceRequest->end) {
                AbsenceRequest::where('id', $dataForm->id)->update(['status' => 'close', 'end' => $dataForm->end, 'attendance' => date("Y-m-d H:i:s", strtotime('- ' . 1 . ' days', strtotime(date('Y-m-d ' . '23:59:59'))))]);
                $absenceLog = AbsenceLog::where('absence_request_id', $absenceRequest->id)->get();
                foreach ($absenceLog as $d) {
                    $deleteAbsence = Absence::where('id', $d->absence_id)->first();
                    if ($deleteAbsence) {
                        Absence::where('id', $d->id)->delete();
                    }
                }

                AbsenceLog::where('absence_request_id', $absenceRequest->id)->delete();

                $begin = strtotime($absenceRequest->start);
                $end   = strtotime($dataForm->end);

                for ($i = $begin; $i < $end; $i = $i + 86400) {
                    $holiday = Holiday::whereDate('start', '<=', date('Y-m-d', $i))->whereDate('end', '>=', date('Y-m-d', $i))->first();
                    if (!$holiday) {
                        if (date("w", strtotime(date('Y-m-d', $i))) != 0 && date("w", strtotime(date('Y-m-d', $i))) != 6) {

                            $ab_id = Absence::create([
                                'day_id' => date("w", strtotime(date('Y-m-d', $i))),
                                'staff_id' => $absenceRequest->staff_id,
                                'created_at' => date('Y-m-d H:i:s', $i),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                            AbsenceLog::create([
                                'absence_category_id' => $absenceRequest->category == "leave" ? 8 : 13,
                                'lat' => '',
                                'lng' => '',
                                'absence_request_id' => $absenceRequest->id,
                                'register' => date('Y-m-d', $i),
                                'absence_id' => $ab_id->id,
                                'duration' => '',
                                'status' => ''
                            ]);
                        }
                    }
                }
                return response()->json([
                    'message' => 'Absen Terkirim',
                    // 'data' => $upload_image,
                ]);
            } else {
                return response()->json([
                    'message' => 'tidak bisa kurang dari tanggal pengajuan sebelumnya',
                    // 'data' => $upload_image,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }
}
