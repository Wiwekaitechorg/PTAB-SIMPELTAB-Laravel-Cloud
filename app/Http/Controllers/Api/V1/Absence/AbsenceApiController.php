<?php
namespace App\Http\Controllers\Api\V1\Absence;

use App\Absence;
use App\AbsenceAdj;
use App\AbsenceLog;
use App\AbsenceProblem;
use App\AbsenceRequest;
use App\AbsenceRequestLogs;
use App\Absence_categories;
use App\Dapertement;
use App\Day;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\MessageLog;
use App\ShiftGroups;
use App\ShiftPlannerStaffs;
use App\Staff;
use App\StaffSpecial;
use App\User;
use App\Visit;
use App\WorkTypeDays;
use App\WorkUnit;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Image;
use App\Services\AbsenceService;

class AbsenceApiController extends Controller
{

    protected $absenceService;

    public function __construct(AbsenceService $absenceService)
    {
        $this->absenceService = $absenceService;
    }

    public function index_service(Request $request)
    {
        $data = $this->absenceService->getMenuData($request->staff_id, $request->shift_group_id);
        return response()->json($data);

        // // return as JSON or pass to view
        // return response()->json([
        //     'message' => 'Success',
        //     'menu' => $data['menu'],
        //     'location' => $data['location'],
        //     'problem' => $data['problem'],
        //     'date' => now(),
        // ]);
    }

    public function user($id)
    {
        $requests = User::where('id', $id)->first();
        //return response()->json($data)->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        return response()->json([
            'message' => 'Data User',
            'data'    => $requests,
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    public function tetTmpOff(Request $request)
    {
        $start = date('Y-m-d H:i:s');
        $end   = date('Y-m-d H:i:s');
        $cek   = AbsenceRequest::where('staff_id', $request->staff_id)
            ->where(function ($query) use ($start, $end) {
                $query->where('category', 'visit')
                    ->orWhere('category', 'visit')
                    ->orWhere('category', 'leave')
                    ->orWhere('category', 'permission')
                    ->orWhere('category', 'extra')
                    ->orWhere('category', 'geolocation_off')
                    ->orWhere('category', 'excuse');

                // ->orWhere('status', 'close');
            })
            ->where(function ($query) use ($start, $end) {
                //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                $query->whereRaw("DATE(absence_requests.start) >= '" . $start . "' AND DATE(absence_requests.start) <= '" . $end . "'")
                    ->where(function ($query) use ($start, $end) {
                        $query->where('status', '=', 'active')
                            ->orWhere('status', '=', 'pending')
                            ->orWhere('status', '=', 'approve');
                        // ->orWhere('status', 'close');
                    })
                //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                    ->orWhereRaw("DATE(absence_requests.end) >= '" . $start . "' AND DATE(absence_requests.end) <= '" . $end . "'")
                    ->where(function ($query) use ($start, $end) {
                        $query->where('status', '=', 'active')
                            ->orWhere('status', '=', 'pending')
                            ->orWhere('status', '=', 'approve');
                        // ->orWhere('status', 'close');
                    });
                // ->orWhere('status', 'close');
            })

            ->first();
        // ->toSql();
        // dd($cek);

        $cek_sick = AbsenceRequest::where('staff_id', $request->staff_id)
            ->where(function ($query) use ($start, $end) {
                $query->where('category', 'permission')
                    ->orWhere('category', 'leave');
            })
            ->where(function ($query) {
                $query->where('status', 'approve')
                    ->orWhere('status', 'active');
            })
            ->where('start', '<=', $start)
            ->where('type', 'sick')
            ->first();

        // $permission = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
        //     ->where('category', 'permission')
        //     ->where('staff_id', $request->staff_id)
        //     ->where(function ($query) {
        //         $query->where('status', 'approve')
        //             ->orWhere('status', 'active');
        //     })
        //     ->first();

        // $leave = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
        //     ->where('end', '>=', date('Y-m-d H:i:s'))
        //     ->where('category', 'leave')
        //     ->where('staff_id', $request->staff_id)
        //     ->where(function ($query) {
        //         $query->where('status', 'approve')
        //             ->orWhere('status', 'active');
        //     })
        //     ->orWhere('start', '<=', date('Y-m-d H:i:s'))
        //     ->where('category', 'leave')
        //     ->where('type', 'sick')
        //     ->where('staff_id', $request->staff_id)
        //     ->where(function ($query) {
        //         $query->where('status', 'approve')
        //             ->orWhere('status', 'active');
        //     })
        //     //->first();
        //     ->toSql();

        // $leave = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
        //     ->where('category', 'leave')
        //     ->where('staff_id', $request->staff_id)
        //     ->where(function ($query) {
        //         $query->where('status', 'approve')
        //             ->orWhere('status', 'active');
        //     })
        //     ->first();

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

        // $permission = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
        //     ->where('end', '>=', date('Y-m-d H:i:s'))
        //     ->where('category', 'permission')
        //     ->where('staff_id', $request->staff_id)
        //     ->where(function ($query) {
        //         $query->where('status', 'approve')
        //             ->orWhere('status', 'active');
        //         // ->orWhere('status', 'pending');
        //         // ->orWhere('status', 'close');
        //     })
        //     ->orWhere('start', '<=', date('Y-m-d H:i:s'))
        //     ->where('category', 'permission')
        //     ->where('type', 'sick')
        //     ->where('staff_id', $request->staff_id)
        //     ->where(function ($query) {
        //         $query->where('status', 'approve')
        //             ->orWhere('status', 'active');
        //         // ->orWhere('status', 'pending');
        //         // ->orWhere('status', 'close');
        //     })
        //     //->first();
        //     ->toSql();

        return $cek_sick;
    }

    public function tetTmp(Request $request)
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
        $menuReguler    = "OFF";
        $menuHoliday    = "OFF";
        $menuBreak      = "OFF";
        $menuExcuse     = "OFF";
        $menuVisit      = "OFF";
        $menuDuty       = "OFF";
        $menuFinish     = "OFF";
        $menuExtra      = "OFF";
        $menuLeave      = "OFF";
        $menuWaiting    = "OFF";
        $menuPermission = "OFF";
        $geofence_off   = "OFF";

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
        //return $coordinat;

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
                    //return $request->staff_id;

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
                                    'geolocationOff' => $geofence_off,
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
                                        'geolocationOff' => $geofence_off,
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
                                        'geolocationOff' => $geofence_off,
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
                                    'geolocationOff' => $geofence_off,
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
                                'geolocationOff' => $geofence_off,
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
                                                'geolocationOff' => $geofence_off,
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
                                            'geolocationOff' => $geofence_off,
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
                                            'geolocationOff' => $geofence_off,
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
                                        'geolocationOff' => $geofence_off,
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
                    'geolocationOff' => $geofence_off,
                ],
            ]);
        }
    }

    public function report(Request $request)
    {
        $date_start   = $request->from;
        $date_end     = $request->to;
        $alpha        = 0;
        $absen_bolong = 0;

        $awal_cuti  = strtotime($date_start);
        $akhir_cuti = strtotime($date_end);

        // satu detik
        // 0,01666668

        $report = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 ,1,NULL)) jumlah_masuk')
            ->selectRaw('count(IF(absence_category_id = 3 AND image !="" AND status = 0 ,1,NULL)) jumlah_k1')
            ->selectRaw('count(IF(absence_category_id = 4 AND image !="" AND status = 0 ,1,NULL)) jumlah_k2')
            ->selectRaw('count(IF(absence_category_id = 5 AND status = 0 ,1,NULL)) jumlah_dinasDalam')
            ->selectRaw('count(IF(absence_category_id = 7 AND status = 0 ,1,NULL)) jumlah_dinasLuar')
            ->selectRaw('count(IF(absence_category_id = 8 AND status = 0 ,1,NULL)) jumlah_cuti')
            ->selectRaw('count(IF(absence_category_id = 10 AND duration < 4 AND status = 0 ,1,NULL)) jumlah_lembur')
            ->selectRaw('count(IF(absence_category_id = 10 AND duration >= 4 AND status = 0 ,1,NULL)) jumlah_lemburlebih')
            ->selectRaw('count(IF(absence_category_id = 11 AND status = 0 ,1,NULL)) jumlah_permisi')
        // ->selectRaw('count(IF(absence_category_id = 13 AND status = 0 ,1,NULL)) jumlah_izin')
            ->selectRaw('count(IF(absence_category_id = 14 AND status = 0 ,1,NULL)) jumlah_dispen')
            ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 AND late > 0.016667 ,1,NULL)) jumlah_lambat')
            ->where('staff_id', $request->staff_id)
        //->whereBetween('absences.created_at', [$date_start, $date_end])
            ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            ->first();

        $sakit = AbsenceRequest::selectRaw('count(id) as jumlah_sakit')
            ->where('category', 'permission')
            ->where('type', 'sick')
            ->where('staff_id', $request->staff_id)
            ->whereNotIn('status', ['reject', 'pending'])
        //->whereBetween('start', [$date_start, $date_end])
            ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
            ->first();
        $izin = AbsenceRequest::selectRaw('count(id) as jumlah_izin')
            ->where('category', 'permission')
            ->where('type', 'other')
            ->where('staff_id', $request->staff_id)
            ->whereNotIn('status', ['reject', 'pending'])
        //->whereBetween('start', [$date_start, $date_end])
            ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
            ->first();
        //get dinas luar
        $dinasLuarRows = AbsenceRequest::selectRaw('start,end')
            ->where('category', 'duty')
            ->where('staff_id', $request->staff_id)
            ->whereNotIn('status', ['reject', 'pending'])
        //->whereBetween('start', [$date_start, $date_end])
            ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
            ->get();
        $jumlah_dinasLuar = 0;
        foreach ($dinasLuarRows as $dinasLuarRow) {
            $diff = date_diff(date_create($dinasLuarRow->start), date_create($dinasLuarRow->end));
            $diff = $diff->format("%R%a");
            $diff = (int) $diff + 1;
            $jumlah_dinasLuar += $diff;
        }

        $report_masuk = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            ->selectRaw('absence_logs.*')
            ->where('absence_category_id', 1)
            ->where('status', 0)
            ->where('staff_id', $request->staff_id)
        //->whereBetween('absences.created_at', [$date_start, $date_end])
            ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            ->get();

        foreach ($report_masuk as $data) {
            if ($request->type != "shift") {
                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($data->timein))));
                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                if ($data->register >= $cekDateNew) {
                    $absen_bolong += 1;
                }
            } else {
                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($data->timein))));
                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                if ($data->register >= $cekDateNew) {
                    $absen_bolong += 1;
                }
            }
        }

        $kegiatan = '';

        if ($request->type != "shift") {

            // tanggalnya diubah formatnya ke Y-m-d

            $hariKerja   = [];
            $sabtuminggu = [];

            for ($i = $awal_cuti; $i <= $akhir_cuti; $i += (60 * 60 * 24)) {
                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                    $hariKerja[] = $i;
                } else {
                    $sabtuminggu[] = $i;
                }
            }
            $jumlah_kerja = count($hariKerja);

            // mencari jumlah hari end

            //$holiday =  Holiday::selectRaw('count(id) jumlah_libur')->whereBetween('start',  [$date_start, $date_end])->where('status', null)->first();
            $holiday = Holiday::selectRaw('count(id) jumlah_libur')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->first();

            //holiday in weekend
            $holiday_weekend = 0;
            //$holiday_rows = Holiday::selectRaw('start')->whereBetween('start', [$date_start, $date_end])->where('status', null)->get();
            $holiday_rows = Holiday::selectRaw('start')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->get();
            foreach ($holiday_rows as $holiday_row) {
                $dayOfWeek = date('w', strtotime($holiday_row->start));
                if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                    $holiday_weekend++;
                }
            }

            $jumlah_libur = $holiday->jumlah_libur - $holiday_weekend;
            $jumlah_kerja = $jumlah_kerja - $jumlah_libur;
            $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit;
            $kegiatan     = "Kegiatan";
        } else {
            //$jadwal = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $request->staff_id)->whereBetween('start',  [$date_start, $date_end])->first();
            $jadwal       = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $request->staff_id)->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->first();
            $jumlah_kerja = $jadwal->jumlah_kerja;
            $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit;
            $jumlah_libur = "";
            $kegiatan     = "Kontrol";
        }

        $data = [
            ["title" => "Jumlah masuk", "data" => $report->jumlah_masuk],
            ["title" => "Jumlah " . $kegiatan . " 1", "data" => $report->jumlah_k1],
            ["title" => "Jumlah " . $kegiatan . " 2", "data" => $report->jumlah_k2],
            ["title" => "Jumlah dinas dalam", "data" => $report->jumlah_dinasDalam],
            ["title" => "Jumlah dinas luar", "data" => $jumlah_dinasLuar],
            ["title" => "Jumlah cuti", "data" => $report->jumlah_cuti],
            ["title" => "Jumlah lembur < 4", "data" => $report->jumlah_lembur],
            ["title" => "Jumlah lembur >= 4", "data" => $report->jumlah_lemburlebih],
            ["title" => "Jumlah permisi", "data" => $report->jumlah_permisi],
            ["title" => "Jumlah izin", "data" => $izin->jumlah_izin],
            ["title" => "Jumlah sakit", "data" => $sakit->jumlah_sakit],
            ["title" => "Jumlah dispen", "data" => $report->jumlah_dispen],
            ["title" => 'Tanpa Keterangan', "data" => $alpha > 0 ? $alpha : 0],
            ["title" => 'Jumlah Kerja', "data" => $jumlah_kerja],
            ["title" => 'Jumlah Libur', "data" => $jumlah_libur],
            ["title" => 'Absen Bolong', "data" => $absen_bolong],
            ["title" => 'Absen Lambat', "data" => $report->jumlah_lambat],

        ];

        return response()->json($data);
    }

    public function index(Request $request)
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
        $menuReguler    = "OFF";
        $menuHoliday    = "OFF";
        $menuBreak      = "OFF";
        $menuExcuse     = "OFF";
        $menuVisit      = "OFF";
        $menuDuty       = "OFF";
        $menuFinish     = "OFF";
        $menuExtra      = "OFF";
        $menuLeave      = "OFF";
        $menuWaiting    = "OFF";
        $menuPermission = "OFF";
        $geofence_off   = "OFF";
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
                    if($menuVisit !='OFF'){
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
                                    'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
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
                                        'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
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
                                        'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
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
                                    'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
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
                                'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
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
                                                'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
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
                                            'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
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
                                            'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
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
                                        'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
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
                    'geolocationOff' => $geofence_off_break =='ON' ?$geofence_off_break:$geofence_off,
                ],
            ]);
        }
    }

    // ketika sudah dibuatkan absence oleh sistem
    public function index_BAK(Request $request)
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
        $menuReguler    = "OFF";
        $menuHoliday    = "OFF";
        $menuBreak      = "OFF";
        $menuExcuse     = "OFF";
        $menuVisit      = "OFF";
        $menuDuty       = "OFF";
        $menuFinish     = "OFF";
        $menuExtra      = "OFF";
        $menuLeave      = "OFF";
        $menuWaiting    = "OFF";
        $menuPermission = "OFF";
        $geofence_off   = "OFF";

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
                                    'geolocationOff' => $geofence_off,
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
                                        'geolocationOff' => $geofence_off,
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
                                        'geolocationOff' => $geofence_off,
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
                                    'geolocationOff' => $geofence_off,
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
                                'geolocationOff' => $geofence_off,
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
                                                'geolocationOff' => $geofence_off,
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
                                            'geolocationOff' => $geofence_off,
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
                                            'geolocationOff' => $geofence_off,
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
                                        'geolocationOff' => $geofence_off,
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
                    'geolocationOff' => $geofence_off,
                ],
            ]);
        }
    }

    public function store(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');
        $staff = Staff::where('id', $request->staff_id)->first();
        // $code = acc_code_generate($last_code, 8, 3);
        // $img_path = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image    = "";

        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        // jika ada figerprint bermasalah start

        if ($request->fingerprintError == "yes") {
            Absence::where('id', $request->absence_id)->update([
                'status_active' => '1',
            ]);
        }

        // jika ada figerprint bermasalah end

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image     = $request->staff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);
            $imgFile->orientate();
            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path() . '/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . $name_image);

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
        $late  = 0;
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

            $upload_image->late  = $late;
            $upload_image->early = $early;

            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->staff_id;
            $upload_image->updated_by_staff_id = $request->staff_id;
            $upload_image->register            = date('Y-m-d H:i:s');
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            $upload_image->duration = $duration;

            // sementara end
            $upload_image->register   = date('Y-m-d H:i:s');
            $upload_image->updated_at = date('Y-m-d H:i:s');
            $upload_image->lat        = $request->lat;
            $upload_image->lng        = $request->lng;
            $upload_image->status     = 0;
            $upload_image->accuracy   = $request->accuracy;
            $upload_image->distance   = $request->distance;
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
                    'duration' => $outDuration,
                ]);
            }

            if ($request->queue == "1" && $request->type == "presence") {
                $check = AbsenceLog::where('absence_id', $out->absence_id)
                    ->where('absence_category_id', 3)
                    ->first();
                if (! $check) {
                    // buat absen istirahat

                    if ($staff->work_type_id === 2) {
                        AbsenceLog::create([

                            'absence_id'          => $out->absence_id,
                            'absence_category_id' => 3,
                            'status'              => '1',
                            'expired_date'        => $out->expired_date,
                            'start_date'          => date('Y-m-d H:i:10'),

                        ]);
                        AbsenceLog::create([

                            'absence_id'          => $out->absence_id,
                            'absence_category_id' => 4,
                            'status'              => '1',
                            'expired_date'        => $out->expired_date,
                            'start_date'          => date('Y-m-d H:i:11'),

                        ]);
                    }
                    // buat absen istirahat end
                }
            }

            return response()->json([
                'message' => 'Absen Terkirim',
                'data'    => $upload_image,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // new absen store start

    public function storeNew(Request $request)
    {

        $staff = Staff::where('id', $request->staff_id)->first();
        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image    = "";

        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        // jika ada figerprint bermasalah start

        if ($request->fingerprintError == "yes") {
            Absence::where('id', $request->absence_id)->update([
                'status_active' => '1',
            ]);
        }

        // jika ada figerprint bermasalah end

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image     = $request->staff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath())->orientate();

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path() . '/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . $name_image);

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
        $late  = 0;
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

            $absence = Absence::where('id', $request->absence_id)
                ->first();

            $change_register = "false";
            if ($request->type == "presence") {
                $cek_toleransi_untuk2shift = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                    ->where('absence_logs.absence_category_id', '2')
                    ->where('absence_logs.status', '0')
                // ->whereDate('absence_logs.created_at', '=', date('Y-m-d', strtotime($upload_image->created_at)))
                    ->where('absence_logs.timeout', '>=', (date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime(date('Y-m-d H:i:s'))))))
                    ->where('staff_id', $absence->staff_id)
                    ->first();
                if ($cek_toleransi_untuk2shift) {
                    // if (date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime(date('Y-m-d H:i:s')))) < $upload_image->timein) {
                    $late            = 0;
                    $change_register = "true";
                    // }
                }
            }

            //check if this time range is dispense_special
            $dispense_special = AbsenceRequest::where('start', '<=', date('Y-m-d H:i:s'))
                ->where('end', '>=', date('Y-m-d H:i:s'))
                ->where('category', 'dispense_special')
                ->where('status', 'approve')
                ->where('staff_id', $absence->staff_id)
                ->first();
            if ($dispense_special && $request->type == "presence" && $request->queue == "1") {
                $late = 0;
            }

            $upload_image->late  = $late;
            $upload_image->early = $early;

            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->staff_id;
            $upload_image->updated_by_staff_id = $request->staff_id;
            $upload_image->register            = date('Y-m-d H:i:s');
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            $upload_image->duration = $duration;

            // sementara end
            $upload_image->register   = $change_register == "true" ? $upload_image->timein : date('Y-m-d H:i:s');
            $upload_image->updated_at = date('Y-m-d H:i:s');
            $upload_image->lat        = $request->lat;
            $upload_image->lng        = $request->lng;
            $upload_image->status     = 0;
            $upload_image->accuracy   = '0';
            $upload_image->distance   = $request->distance;
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
                    'duration' => $outDuration,
                ]);
            }

            if ($request->queue == "1" && $request->type == "presence") {
                $check = AbsenceLog::where('absence_id', $out->absence_id)
                    ->where('absence_category_id', 3)
                    ->first();
                if (! $check) {
                    // buat absen istirahat

                    if ($staff->work_type_id === 1) {

                        if ($day != "5") {
                            AbsenceLog::create([

                                'absence_id'          => $out->absence_id,
                                'absence_category_id' => 3,
                                'status'              => '1',
                                'expired_date'        => date('Y-m-d 11:30:59'),
                                'start_date'          => date('Y-m-d 11:00:00'),

                            ]);
                            AbsenceLog::create([

                                'absence_id'          => $out->absence_id,
                                'absence_category_id' => 4,
                                'status'              => '1',
                                'expired_date'        => date('Y-m-d 13:30:59'),
                                'start_date'          => date('Y-m-d 13:00:00'),

                            ]);
                        } else {
                            AbsenceLog::create([

                                'absence_id'          => $out->absence_id,
                                'absence_category_id' => 3,
                                'status'              => '1',
                                'expired_date'        => date('Y-m-d 09:30:59'),
                                'start_date'          => date('Y-m-d 09:00:00'),

                            ]);
                            AbsenceLog::create([

                                'absence_id'          => $out->absence_id,
                                'absence_category_id' => 4,
                                'status'              => '1',
                                'expired_date'        => date('Y-m-d 11:30:59'),
                                'start_date'          => date('Y-m-d 11:00:00'),

                            ]);
                        }
                    } else {

                        $dayL2 = $upload_image->timeout;
                        $dayL2 = strtotime($dayL2);
                        $dayL1 = $upload_image->timein;
                        $dayL1 = strtotime($dayL1);

                        $start_kegiatan1 = date("Y-m-d H:i:s", strtotime('+ ' . 120 . ' minutes', $dayL1));
                        $end_kegiatan1   = date("Y-m-d H:i:59", strtotime('+ ' . 150 . ' minutes', $dayL1));

                        $start_kegiatan2 = date("Y-m-d H:i:s", strtotime('- ' . 120 . ' minutes', $dayL2));
                        $end_kegiatan2   = date("Y-m-d H:i:59", strtotime('- ' . 90 . ' minutes', $dayL2));

                        AbsenceLog::create([

                            'absence_id'          => $out->absence_id,
                            'absence_category_id' => 3,
                            'status'              => '1',
                            'expired_date'        => $end_kegiatan1,
                            'start_date'          => $start_kegiatan1,

                        ]);
                        AbsenceLog::create([

                            'absence_id'          => $out->absence_id,
                            'absence_category_id' => 4,
                            'status'              => '1',
                            'expired_date'        => $end_kegiatan2,
                            'start_date'          => $start_kegiatan2,

                        ]);
                        // buat absen istirahat end
                    }
                }
            }

            return response()->json([
                'message' => 'Absen Terkirim',
                'data'    => $upload_image,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // new absen store end

    // create absen baru
    public function storeLocation(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image    = "";
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
        if (! $workDuration) {
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
                    'status_active' => '3',
                ]);
            }
        }

        // set durasi end

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image     = $request->satff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $datlng = $request->lng ? $request->lng : "";
            $datlat = $request->lat ? $request->lat : "";

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $datlat . ' lng : ' . $datlng, 10, 10, function ($font) {
                $font->file(base_path() . '/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . $name_image);

            // tambah watermark end
            // $resource_image->move($basepath . $img_path, $name_image);
            $data_image = $name_image;
        }

        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }

        // try {
        // cek absen sudah ada atau tidak ada
        // $check = AbsenceLog::where('absence_id', $request->absence_id)
        //     ->where('absence_request_id', $request->absence_request_id)
        //     ->where('absence_category_id', $request->absence_category_id_end)->first();
        $check = AbsenceLog::where('absence_id', $request->absence_id)
            ->where('absence_request_id', $request->absence_request_id)
        // ->where('absence_category_id', $request->absence_category_id_end)
            ->first();
        if (! $check) {
            $upload_image        = new AbsenceLog;
            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->satff_id;
            $upload_image->updated_by_staff_id = $request->satff_id;
            $upload_image->register            = date('Y-m-d H:i:s');
            $upload_image->absence_id          = $request->absence_id;
            $upload_image->absence_request_id  = $request->absence_request_id;
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            // $upload_image->duration = $duration;
            // sementara end
            $upload_image->register            = date('Y-m-d H:i:s');
            $upload_image->created_at          = date('Y-m-d H:i:s');
            $upload_image->updated_at          = date('Y-m-d H:i:s');
            $upload_image->absence_category_id = $request->absence_category_id;
            $upload_image->lat                 = $request->lat ? $request->lat : "";
            $upload_image->lng                 = $request->lat ? $request->lat : "";
            $upload_image->status              = 0;
            $upload_image->expired_date        = $request->expired_date;
            $upload_image->start_date          = date('Y-m-d H:i:10');
            $upload_image->accuracy            = $request->accuracy ? $request->accuracy : "";
            $upload_image->distance            = $request->distance ? $request->distance : "";
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
                ->where('status', 1)
                ->where('queue', '1')
                ->first();
            if ($breakin) {
                AbsenceLog::where('id', $breakin->id)->update(['register' => date('Y-m-d H:i:s'), 'status' => '0']);
            }
            $breakout = AbsenceLog::selectRaw('absence_logs.id, absence_logs.expired_date, absence_logs.absence_id')->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_id', $request->absence_id)
                ->where('type', 'break')
                ->where('status', 1)
                ->where('queue', '2')
                ->first();

            if ($breakout) {
                AbsenceLog::where('id', $breakout->id)->update(['register' => date('Y-m-d H:i:s'), 'status' => '0']);
            }
            // buat absen endnya
            $absenceR = AbsenceRequest::where('id', $request->absence_request_id)->first();

            if ($request->absence_category_id == "11" && $absenceR->type == "out") {
                AbsenceLog::create([
                    'absence_id'          => $request->absence_id,
                    'absence_category_id' => $request->absence_category_id_end,
                    'status'              => '0',
                    'register'            => date('Y-m-d H:i:s'),
                    'absence_request_id'  => $request->absence_request_id,
                    'expired_date'        => $request->expired_date,
                    'start_date'          => date('Y-m-d H:i:10'),

                ]);
                AbsenceLog::where('id', $out->id)->update(['register' => date('Y-m-d H:i:s'), 'duration' => $outDuration, 'status' => '0']);
            } else {
                AbsenceLog::create([
                    'absence_id'          => $request->absence_id,
                    'absence_category_id' => $request->absence_category_id_end,
                    'status'              => '1',
                    'absence_request_id'  => $request->absence_request_id,
                    'expired_date'        => $request->expired_date,
                    'start_date'          => date('Y-m-d H:i:10'),

                ]);
            }

            // cek absen sudah ada atau tidak ada

            AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'active']);
            if ($request->absence_category_id == "11" && $absenceR->type == "out") {
                AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'close']);
            }
            return response()->json([
                'message' => 'Absen Terkirim',
                'data'    => $upload_image,
            ]);
        } else {
            return response()->json([
                'message' => 'Tadi Sudah Absen',
            ]);
        }
        // } catch (QueryException $ex) {
        //     return response()->json([
        //         'message' => 'gagal',
        //     ]);
        // }
    }

    // create absen baru
    public function storeLocationNew(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image    = "";
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
        if (! $workDuration) {
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
                    'status_active' => '3',
                ]);
            }
        }

        // set durasi end

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image     = $request->satff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $datlng = $request->lng ? $request->lng : "";
            $datlat = $request->lat ? $request->lat : "";

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $datlat . ' lng : ' . $datlng, 10, 10, function ($font) {
                $font->file(base_path() . '/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . $name_image);

            // tambah watermark end
            // $resource_image->move($basepath . $img_path, $name_image);
            $data_image = $name_image;
        }

        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }

        // try {
        // cek absen sudah ada atau tidak ada
        // $check = AbsenceLog::where('absence_id', $request->absence_id)
        //     ->where('absence_request_id', $request->absence_request_id)
        //     ->where('absence_category_id', $request->absence_category_id_end)->first();
        $check = AbsenceLog::where('absence_id', $request->absence_id)
            ->where('absence_request_id', $request->absence_request_id)
        // ->where('absence_category_id', $request->absence_category_id_end)
            ->first();
        if (! $check) {
            $upload_image        = new AbsenceLog;
            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->satff_id;
            $upload_image->updated_by_staff_id = $request->satff_id;
            $upload_image->register            = date('Y-m-d H:i:s');
            $upload_image->absence_id          = $request->absence_id;
            $upload_image->absence_request_id  = $request->absence_request_id;
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            // $upload_image->duration = $duration;
            // sementara end
            $upload_image->register            = date('Y-m-d H:i:s');
            $upload_image->created_at          = date('Y-m-d H:i:s');
            $upload_image->updated_at          = date('Y-m-d H:i:s');
            $upload_image->absence_category_id = $request->absence_category_id;
            $upload_image->lat                 = $request->lat ? $request->lat : "";
            $upload_image->lng                 = $request->lat ? $request->lat : "";
            $upload_image->status              = 0;
            $upload_image->expired_date        = $request->expired_date;
            $upload_image->start_date          = date('Y-m-d H:i:10');
            $upload_image->accuracy            = $request->accuracy ? $request->accuracy : "";
            $upload_image->distance            = $request->distance ? $request->distance : "";
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
                ->where('status', 1)
                ->where('queue', '1')
                ->first();
            if ($breakin && ($request->absence_category_id != "5" && $request->absence_category_id != "6")) {
                AbsenceLog::where('id', $breakin->id)->update(['register' => date('Y-m-d H:i:s'), 'status' => '0']);
            }
            $breakout = AbsenceLog::selectRaw('absence_logs.id, absence_logs.expired_date, absence_logs.absence_id')->join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_id', $request->absence_id)
                ->where('type', 'break')
                ->where('status', 1)
                ->where('queue', '2')
                ->first();

            if ($breakout && ($request->absence_category_id != "5" && $request->absence_category_id != "6")) {
                AbsenceLog::where('id', $breakout->id)->update(['register' => date('Y-m-d H:i:s'), 'status' => '0']);
            }
            // buat absen endnya
            $absenceR = AbsenceRequest::where('id', $request->absence_request_id)->first();

            if ($request->absence_category_id == "11" && $absenceR->type == "out") {
                AbsenceLog::create([
                    'absence_id'          => $request->absence_id,
                    'absence_category_id' => $request->absence_category_id_end,
                    'status'              => '0',
                    'register'            => date('Y-m-d H:i:s'),
                    'absence_request_id'  => $request->absence_request_id,
                    'expired_date'        => $request->expired_date,
                    'start_date'          => date('Y-m-d H:i:10'),

                ]);
                AbsenceLog::where('id', $out->id)->update(['register' => date('Y-m-d H:i:s'), 'duration' => $outDuration, 'status' => '0']);
            } else {
                AbsenceLog::create([
                    'absence_id'          => $request->absence_id,
                    'absence_category_id' => $request->absence_category_id_end,
                    'status'              => '1',
                    'absence_request_id'  => $request->absence_request_id,
                    'expired_date'        => $request->expired_date,
                    'start_date'          => date('Y-m-d H:i:10'),

                ]);
            }

            // cek absen sudah ada atau tidak ada

            AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'active']);
            if ($request->absence_category_id == "11" && $absenceR->type == "out") {
                AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'close']);
            }
            return response()->json([
                'message' => 'Absen Terkirim',
                'data'    => $upload_image,
            ]);
        } else {
            return response()->json([
                'message' => 'Tadi Sudah Absen',
            ]);
        }
        // } catch (QueryException $ex) {
        //     return response()->json([
        //         'message' => 'gagal',
        //     ]);
        // }
    }

    // absen lokasi end
    public function storeLocationEnd(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image    = "";
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        $absence_check = AbsenceLog::where('id', $request->id)->where('absence_request_id', $request->absence_request_id)->first();
        if ($absence_check->absence_category_id == "12") {

            Absence::where('id', $absence_check->absence_id)->update([
                'status_active' => "",
            ]);
        }

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image     = $request->staff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;
            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path() . '/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . $name_image);

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
            $upload_image        = AbsenceLog::where('id', $request->id)->where('absence_request_id', $request->absence_request_id)->first();
            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->staff_id;
            $upload_image->updated_by_staff_id = $request->staff_id;
            $upload_image->register            = date('Y-m-d H:i:s');
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            $upload_image->duration = $duration;
            // sementara end
            $upload_image->register   = date('Y-m-d H:i:s');
            $upload_image->updated_at = date('Y-m-d H:i:s');
            $upload_image->lat        = $request->lat;
            $upload_image->lng        = $request->lng;
            $upload_image->status     = 0;
            $upload_image->accuracy   = $request->accuracy;
            $upload_image->distance   = $request->distance;
            // $upload_image->shift_id = $request->shift_id;

            $upload_image->save();

            // untuk menutup absen error akibat double data input start
            AbsenceLog::where('absence_id', $request->absence_id)->where('absence_request_id', $request->absence_request_id)->update(['status' => 0]);
            // untuk menutup absen error akibat double data input end

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
                    'duration' => $outDuration,
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
                'data'    => $upload_image,
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
        $basepath = base_path() . '/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image    = "";
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        $AbsenceRequestLogs = AbsenceRequestLogs::where('absence_request_id', $request->absence_request_id)
            ->where('type', 'request_log_in')
            ->first();

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image     = $request->satff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;
            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path() . '/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . $name_image);

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

            $upload_image        = new AbsenceRequestLogs;
            $upload_image->image = $data_image;
            // sementara start
            // $upload_image->created_by_staff_id = $request->staff_id;
            // $upload_image->updated_by_staff_id = $request->staff_id;
            $upload_image->register           = date('Y-m-d H:i:s');
            $upload_image->absence_request_id = $request->absence_request_id;
            $upload_image->type               = $type;
            $upload_image->memo               = $request->memo;
            $upload_image->created_at         = date('Y-m-d H:i:s');
            $upload_image->updated_at         = date('Y-m-d H:i:s');
            $upload_image->lat                = $request->lat;
            $upload_image->lng                = $request->lng;
            // $upload_image->accuracy = $request->accuracy;
            // $upload_image->distance = $request->distance;
            // $upload_image->shift_id = $request->shift_id;

            $upload_image->save();

            if ($AbsenceRequestLogs) {
                $absenceRequest = AbsenceRequest::select('category', DB::raw('DATE(start) as start'), DB::raw('DATE(end) as end'))
                    ->where('id', $request->absence_request_id)->first();
                AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'close', 'attendance' => date("Y-m-d H:i:s", strtotime('- ' . 1 . ' days', strtotime(date('Y-m-d ' . '23:59:59'))))]);
                $check = AbsenceLog::join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                    ->where('absence_category_id', $absenceRequest->category)
                    ->where('absences.staff_id', $request->staff_id)
                    ->where('absence_logs.absence_request_id', $request->absence_request_id)
                    ->whereDate('absences.created_at', $absenceRequest->start)
                    ->first();
                if (! $check) {
                    $begin = strtotime($absenceRequest->start);
                    // $end   = strtotime(date('Y-m-d'));
                    $end = strtotime($absenceRequest->end);

                    // Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                    //     ->where('absences.staff_id', $request->staff_id)
                    //     ->whereDate('absences.created_at', '>=', $absenceRequest->start)
                    //     // ->where('absence_logs.absence_request_id', $request->absence_request_id)
                    //     // ->where('absence_category_id', $absenceRequest->category)
                    //     ->delete();
                    // AbsenceLog::join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                    //     ->where('absences.staff_id', $request->staff_id)
                    //     ->whereDate('register', '>=', $absenceRequest->start)
                    //     // ->where('absence_logs.absence_request_id', $request->absence_request_id)
                    //     ->where('absence_category_id', $absenceRequest->category)
                    //     ->delete();
                    $list_abs = Absence::select('absences.*')->join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                        ->where('absences.staff_id', $request->staff_id)
                        ->whereDate('absences.created_at', '>=', $absenceRequest->start)
                    // ->where('absence_logs.absence_request_id', $request->absence_request_id)
                    // ->where('absence_category_id', $absenceRequest->category == "duty" ? 7 : 8)
                        ->get();
                    foreach ($list_abs as $data) {

                        AbsenceLog::where('absence_id', $data->id)->delete();
                        // dd('error');
                        $data->delete();
                        # code...
                    }
                    // AbsenceLog::join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                    //     ->where('absence_logs.id', '94811')->delete('absence_logs');

                    // $q = 'DELETE absence_logs, absences FROM absences LEFT JOIN absence_logs ON `absences`.id = `absence_logs`.absence_id where absence_logs.id = "94811"';
                    // $status = DB::delete($q);

                    for ($i = $begin; $i <= $end; $i = $i + 86400) {
                        // $holiday = Holiday::whereDate('start', '=', date('Y-m-d', $i))->first();
                        // if (!$holiday) {
                        if (date("w", strtotime(date('Y-m-d', $i))) != 0) {
                            $day = date("w", strtotime(date('Y-m-d', $i)));
                        } else {
                            $day = 7;
                        }

                        $ab1 = Absence::create([
                            'day_id'     => $day,
                            'staff_id'   => $request->staff_id,
                            'created_at' => date('Y-m-d H:i:s', $i),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        AbsenceLog::create([
                            'absence_category_id' => $absenceRequest->category == "duty" ? 7 : 8,
                            'absence_request_id'  => $request->absence_request_id,
                            'lat'                 => '',
                            'lng'                 => '',
                            'register'            => date('Y-m-d', $i),
                            'absence_id'          => $ab1->id,
                            'duration'            => '',
                            'status'              => '',
                        ]);
                    }
                    //     }
                    // }
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
                'data'    => $upload_image,
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
        $basepath = base_path() . '/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image    = "";
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        $cekStatus = AbsenceRequest::where('id', $request->absence_request_id)->first();

        if ($cekStatus->status && $cekStatus->status == "pending") {
            $data = [
                'day_id'        => $day,
                'staff_id'      => $request->staff_id,
                'created_at'    => date('Y-m-d'),
                'status_active' => '2',
            ];
        } else {
            $data = [
                'day_id'     => $day,
                'staff_id'   => $request->staff_id,
                'created_at' => date('Y-m-d'),
            ];
        }
        $check = AbsenceLog::join('absences', 'absences.id', '=', 'absence_logs.absence_id')
            ->where('absence_category_id', $request->absence_category_id)
            ->where('absences.staff_id', $request->staff_id)
            ->where('absence_logs.absence_request_id', $request->absence_request_id)
            ->whereDate('absences.created_at', date('Y-m-d'))
            ->first();

        if (! $check) {
            $absence = Absence::create($data);

            if ($request->file('image')) {
                $resource_image = $request->file('image');
                $name_image     = $request->staff_id;
                $file_ext_image = $request->file('image')->extension();
                // $id_name_image = str_replace(' ', '-', $id_image);
                // $nameIMG =
                $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

                // tambah watermark start
                $image = $request->file('image');

                $imgFile = Image::make($image->getRealPath());

                $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

                $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                    $font->file(base_path() . '/public' . '/font/Titania-Regular.ttf');
                    $font->size(14);
                    $font->color('#000000');
                    $font->valign('top');
                })->save($basepath . $name_image);

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
            if (! $check) {
                $upload_image        = new AbsenceLog;
                $upload_image->image = $data_image;
                // sementara start
                $upload_image->created_by_staff_id = $request->staff_id;
                $upload_image->updated_by_staff_id = $request->staff_id;
                $upload_image->register            = date('Y-m-d H:i:s');
                $upload_image->absence_id          = $absence->id;
                $upload_image->absence_request_id  = $request->absence_request_id;
                // $upload_image->late = $late;
                // $upload_image->early = $early;
                // $upload_image->duration = $duration;
                // sementara end
                $upload_image->register            = date('Y-m-d H:i:s');
                $upload_image->created_at          = date('Y-m-d H:i:s');
                $upload_image->updated_at          = date('Y-m-d H:i:s');
                $upload_image->absence_category_id = $request->absence_category_id;
                $upload_image->lat                 = $request->lat ? $request->lat : '';
                $upload_image->lng                 = $request->lng ? $request->lng : '';
                $upload_image->status              = 0;
                $upload_image->expired_date        = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime(date('Y-m-d H:i:s'))));
                $upload_image->start_date          = date('Y-m-d H:i:10');
                $upload_image->accuracy            = $request->accuracy;
                $upload_image->distance            = $request->distance;
                // $upload_image->shift_id = $request->shift_id;

                $upload_image->save();

                // buat absen endnya
                AbsenceLog::create([
                    'absence_id'          => $absence->id,
                    'absence_category_id' => $request->absence_category_id_end,
                    'status'              => '1',
                    'absence_request_id'  => $request->absence_request_id,
                    'expired_date'        => date("Y-m-d H:i:s", strtotime('+12 hours', strtotime(date('Y-m-d H:i:s')))),
                    'start_date'          => date('Y-m-d H:i:10'),

                ]);
                AbsenceRequest::where('id', $request->absence_request_id)->update(['status' => 'active']);

                return response()->json([
                    'message' => 'Absen Terkirim',
                    'data'    => $upload_image,
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
            $absenceRequest = AbsenceRequest::where('id', $request->id)->first();

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
                    if (! $holiday) {
                        if (date("w", strtotime(date('Y-m-d', $i))) != 0 && date("w", strtotime(date('Y-m-d', $i))) != 6) {

                            $ab_id = Absence::create([
                                'day_id'     => date("w", strtotime(date('Y-m-d', $i))),
                                'staff_id'   => $absenceRequest->staff_id,
                                'created_at' => date('Y-m-d H:i:s', $i),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                            AbsenceLog::create([
                                'absence_category_id' => $absenceRequest->category == "leave" ? 8 : 13,
                                'lat'                 => '',
                                'lng'                 => '',
                                'absence_request_id'  => $absenceRequest->id,
                                'register'            => date('Y-m-d', $i),
                                'absence_id'          => $ab_id->id,
                                'duration'            => '',
                                'status'              => '',
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
            'data'    => $cek,
        ]);
    }    

    public function reportAbsenceExcelBak(Request $request)
    {
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $lemburLebih  = "";
        $lemburJumlah = "";
        // dd($request->all());

        $dapertement_id = '';
        if ($request->kode_bagian) {
            $dapertement_row = Dapertement::select('id')->where('code', $request->kode_bagian)->first();
            $dapertement_id  = $dapertement_row->id;
        }

        //get staffs
        $staffs = Staff::FilterWorkUnit($request->work_unit_id)
            ->FilterNik($request->NIK)
            ->FilterId($request->staff_id)
            ->FilterJob1($request->job_id)
            ->FilterSubdapertement($request->subdapertement_id, $request->job_id)
            ->FilterDapertement($dapertement_id)
            ->orderBy('NIK', 'ASC')
            ->where('_status', 'active')
            ->get();

        $list_absen_excel = [];
        $date_from        = $request->from;
        $date_to          = $request->to;

        //loop staffs
        foreach ($staffs as $stf) {
            // echo $stf->NIK;
            $staff = Staff::select(
                'staffs.*',
                DB::raw(
                    '(CASE WHEN staffs.type = "employee" THEN  SUBSTRING(staffs.NIK, 5) ELSE staffs.NIK END)  AS NIK'
                )

            )->where('id', $stf->id)->first();

            //get Absence Logs by staff
            //if not shift
            if ($staff->work_type_id != 2) {
                // untuk reguler
                $absence = Absence::with(['absence_logs', 'absence_logs.workTypeDays', 'staffs'])
                    ->where('staff_id', $staff->id)
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [$date_from, $date_to])
                    ->whereRaw("DATE(absences.created_at) >= '" . $date_from . "' AND DATE(absences.created_at) <= '" . $date_to . "'")
                    ->get();
            } else {
                // untuk shift
                $absence = Absence::with(['absence_logs', 'absence_logs.shiftGroupTimeSheets', 'staffs'])
                    ->where('staff_id', $staff->id)
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [$date_from, $date_to])
                    ->whereRaw("DATE(absences.created_at) >= '" . $date_from . "' AND DATE(absences.created_at) <= '" . $date_to . "'")
                    ->get();
            }
            $list_absen = [];

            //set list for a holiday (if not shift)
            if ($staff->work_type_id != 2) {
                $holidays = Holiday::get();
                foreach ($holidays as $data) {
                    $list_absen[] = [
                        // 'Emp No'     => $staff->NIK,
                        // 'AC-No'      => '',
                        'NIK'        => $stf->NIK,
                        'Name'       => $staff->name,
                        // 'Auto-Asign' => '',
                        'Date'       => date('Y-m-d', strtotime($data->start)),
                        // 'TimeTable'  => '',
                        // 'On_Duty'    => '',
                        // 'Off_Duty'   => '',
                        'Clock_in'   => 'null',
                        'Clock_out'  => 'null',
                        // 'Normal'     => '',
                        // 'Real time'  => '',
                        // 'Late'       => '',
                        // 'Early'      => '',
                        // 'Absent'     => '',
                        // 'OT Time'    => '',
                        // 'Work Time'  => '',
                        // 'Exception'  => '',
                        // 'Must C/In'  => '',
                        // 'Must C/Out' => '',
                        // 'Department' => '',
                        // 'NDays'      => '',
                        // 'WeekEnd'    => '',
                        // 'Holiday'    => '',
                        // 'ATT_Time'   => '',
                        // 'NDays_OT'   => '',
                        // 'WeekEnd_OT' => '',
                        // 'Holiday_OT' => '',
                        'Lembur'     => '',
                        'Lembur_4'   => '',
                        'Flag'       => '',
                        'Status'     => 'libur',
                        'Keterangan' => $data->description,
                        'Kegiatan1'  => 'null',
                        'Kegiatan2'  => 'null',
                    ];
                }
            }

            //get sabtu minggu
            $get_jadwal_libur = Day::select('days.*')->leftJoin(
                'work_type_days',
                function ($join) use ($staff) {
                    $join->on('days.id', '=', 'work_type_days.day_id')
                        ->where('work_type_id', $staff->work_type_id);
                }
            )->where('work_type_days.day_id', '=', null)->get();
            $jadwal_libur = [];
            foreach ($get_jadwal_libur as $data) {
                $jadwal_libur[] = $data->id;
            }
            // in_array('1', $jadwal_libur);
            // dd($jadwal_libur);

            //set list as absence
            foreach ($absence as $data) {
                $shift     = "";
                $deskripsi = "";
                $idLembur  = "";

                $get_duty_in   = $data->absence_logs->where('absence_category_id', 1)->first();
                $get_duty_out  = $data->absence_logs->where('absence_category_id', 2)->first();
                $get_clock_in  = $data->absence_logs->where('absence_category_id', 1)->first();
                $get_clock_out = $data->absence_logs->where('absence_category_id', 2)->first();

                $get_lembur_in  = $data->absence_logs->where('absence_category_id', 9)->first();
                $get_lembur_out = $data->absence_logs->where('absence_category_id', 10)->first();

                $get_dinasLuar = $data->absence_logs->where('absence_category_id', 7)->first();
                $get_cuti      = $data->absence_logs->where('absence_category_id', 8)->first();
                $get_izin      = $data->absence_logs->where('absence_category_id', 13)->first();

                $get_permisi_in = $data->absence_logs->where('absence_category_id', 11)->first();
                $get_dispen     = $data->absence_logs->where('absence_category_id', 14)->first();

                $get_datang    = $data->absence_logs->where('absence_category_id', 1)->where('status', 0)->first();
                $get_pulang    = $data->absence_logs->where('absence_category_id', 2)->where('status', 0)->first();
                $get_kegiatan1 = $data->absence_logs->where('absence_category_id', 3)->where('status', 0)->first();
                $get_kegiatan2 = $data->absence_logs->where('absence_category_id', 4)->where('status', 0)->first();
                //additional
                //get req izin
                $get_req_izin = AbsenceRequest::whereRaw("DATE(start) >= '" . $data->created_at . "' AND DATE(end) <= '" . $data->created_at . "'")
                    ->where('category', 'permission')
                    ->where('staff_id', $stf->id)
                    ->where('status', 'approve')
                    ->first();

                //set ref time duty in/out
                //set log time clock in/out register
                //if not shift
                if ($staff->work_type_id != 2) {

                    if ($get_duty_in) {
                        if ($get_duty_in->workTypeDays) {
                            $duty_in = $get_duty_in->workTypeDays->time;
                        } else {
                            $duty_in = '';
                        }
                    } else {
                        $duty_in = '';
                    }

                    if ($get_duty_out) {
                        if ($get_duty_out->workTypeDays) {
                            $duty_out = $get_duty_out->workTypeDays->time;
                        } else {
                            $duty_out = '';
                        }
                    } else {
                        $duty_out = '';
                    }

                    $clock_in  = $get_clock_in ? $get_clock_in->register : '';
                    $clock_out = $get_clock_out ? $get_clock_out->register : '';
                    $kegiatan1 = $get_kegiatan1 ? $get_kegiatan1->register : '';
                    $kegiatan2 = $get_kegiatan2 ? $get_kegiatan2->register : '';
                } else {
                    if ($get_duty_in) {
                        if ($get_duty_in->shiftGroupTimeSheets) {
                            $duty_in = $get_duty_in->shiftGroupTimeSheets->time;
                        } else {
                            $duty_in = '';
                        }
                    } else {
                        $duty_in = '';
                    }

                    if ($get_duty_out) {
                        if ($get_duty_out->shiftGroupTimeSheets) {
                            $duty_out = $get_duty_out->shiftGroupTimeSheets->time;
                        } else {
                            $duty_out = '';
                        }
                    } else {
                        $duty_out = '';
                    }

                    $clock_in  = $get_clock_in ? $get_clock_in->register : '';
                    $clock_out = $get_clock_out ? $get_clock_out->register : '';
                    $shift     = $get_clock_out ? $get_clock_out->shift_planner_id : '';
                    $kegiatan1 = $get_kegiatan1 ? $get_kegiatan1->register : '';
                    $kegiatan2 = $get_kegiatan2 ? $get_kegiatan2->register : '';
                }

                //if clock/check in but late more than 210 minutes => alfa
                $status     = '';
                $flag       = '';
                $keterangan = '';
                if ($clock_in != '') {
                    $keterangan   = "Masuk";
                    $lemburLebih  = "";
                    $lemburJumlah = "";
                    $flag         = '';
                    $status       = 'masuknormal';
                    if ($staff->work_type_id != 2) {
                        $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($get_clock_in->timein))));
                        // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                        if ($clock_in >= $cekDateNew) {
                            $clock_in   = "";
                            $keterangan = "Lambat 3.5 Jam";
                            $status     = 'alfa';
                        }
                    } else {
                        $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($get_clock_in->timein))));
                        // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                        if ($clock_in >= $cekDateNew) {
                            $clock_in   = "";
                            $keterangan = "Lambat 2 Jam";
                            $status     = 'alfa';
                        }
                    }
                    //get late
                    $flag = ($get_clock_in->late > 0.016667 && $status == 'masuknormal') ? 'lambat' : '';

                    // $clock_in =  "";
                } else if ($get_dinasLuar) {
                    $flag         = '';
                    $status       = 'dinas';
                    $clock_in     = "";
                    $clock_out    = "";
                    $kegiatan1    = "";
                    $kegiatan2    = "";
                    $keterangan   = "Dinas Luar";
                    $lemburLebih  = "";
                    $lemburJumlah = "";
                    $idLembur     = "";
                } else if ($get_cuti) {
                    $flag         = '';
                    $status       = 'cuti';
                    $clock_in     = "";
                    $clock_out    = "";
                    $kegiatan1    = "";
                    $kegiatan2    = "";
                    $keterangan   = "Cuti";
                    $lemburLebih  = "";
                    $lemburJumlah = "";
                    $idLembur     = "";
                } else if ($get_dispen) {
                    $flag         = '';
                    $status       = 'dispen';
                    $clock_in     = "";
                    $clock_out    = "";
                    $kegiatan1    = "";
                    $kegiatan2    = "";
                    $keterangan   = "Dispensasi";
                    $lemburLebih  = "";
                    $lemburJumlah = "";
                    $idLembur     = "";
                } else if ($get_izin || $get_req_izin) {
                    $flag      = '';
                    $status    = 'izin';
                    $clock_in  = "";
                    $clock_out = "";
                    $kegiatan1 = '';
                    $kegiatan2 = '';
                    if ($get_izin) {
                        if ($get_izin->absenceRequests->type == "sick") {
                            $keterangan = "Sakit Tidak Izin Dokter";
                            $status     = 'sakittidakizindokter';
                        } else if ($get_izin->absenceRequests->type == "sick_proof") {
                            $keterangan = "Sakit Izin Dokter";
                            $status     = 'sakitizindokter';
                        } else {
                            $keterangan = "Izin";
                        }
                    } else {
                        if ($get_req_izin->type == "sick") {
                            $keterangan = "Sakit Tidak Izin Dokter";
                            $status     = 'sakittidakizindokter';
                        } else if ($get_req_izin->type == "sick_proof") {
                            $keterangan = "Sakit Izin Dokter";
                            $status     = 'sakitizindokter';
                        } else {
                            $keterangan = "Izin";
                        }
                    }
                    $lemburLebih  = "";
                    $lemburJumlah = "";
                    $idLembur     = "";
                } else {
                    $flag         = '';
                    $status       = 'alfa';
                    $keterangan   = "Alfa";
                    $lemburLebih  = "";
                    $lemburJumlah = "";
                    $idLembur     = "";
                }
                // if lembur (lemburLebih = lembur > 4jam) (lembur jumlah = total jam lembur)
                $clock_lembur_in  = 'null';
                $clock_lembur_out = 'null';
                if ($get_lembur_in) {
                    $flag .= $flag != '' ? ',lembur' : 'lembur';
                    $clock_lembur_in  = $get_lembur_in->register;
                    $clock_lembur_out = $get_lembur_out->register;
                    $idLembur         = "lembur";
                    $lemburJumlah     = round($get_lembur_out->duration);
                    $lemburLebih      = "";
                    if ($get_lembur_out->duration > 4) {
                        $lemburLebih = "Y";
                    } else if ($get_lembur_out->duration <= 4) {
                        $lemburLebih = "N";
                    }
                }
                // if permisi
                if ($get_permisi_in) {
                    //check reguler or shift
                    if ($staff->work_type_id != 2) {
                        //if reguler get hari
                        $day_id = date('w', strtotime($get_permisi_in->register)) == "0" ? '7' : date('w', strtotime($get_permisi_in->register));
                        //get clock
                        $date_now_12      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 12:00:00";
                        $date_now_11      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 11:00:00";
                        $register_to_time = strtotime(date('Y-m-d H:i:s', strtotime($get_permisi_in->register)));
                        $now_12_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_12)));
                        $now_11_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_11)));
                        //echo $day_id." - ". $register_to_time." - ". $now_12_to_time." - ". $now_11_to_time;
                        //if reguler mon - thu, check if jam mulai < 12
                        if ($day_id >= 1 && $day_id <= 4 && ($register_to_time < $now_12_to_time)) {
                            $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                        }
                        //if reguler fri, check if jam mulai < 11
                        else if (($day_id == 5 || $day_id == 6) && ($register_to_time < $now_11_to_time)) {
                            $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                        } else {
                            $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                        }
                    } else {
                        $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                    }
                }

                //get bolong masuk
                if (! $get_datang && $get_pulang) {
                    $flag .= $flag != '' ? ',bolongdatang' : 'bolongdatang';
                }
                if ($get_datang && ! $get_pulang) {
                    $flag .= $flag != '' ? ',bolongpulang' : 'bolongpulang';
                }
                //get bolong kegiatan
                if (! $get_kegiatan1) {
                    $flag .= $flag != '' ? ',bolongkegiatan1' : 'bolongkegiatan1';
                }
                if (! $get_kegiatan2) {
                    $flag .= $flag != '' ? ',bolongkegiatan2' : 'bolongkegiatan2';
                }

                if ($get_clock_out && $get_clock_out->status == '1' && $status != "alfa") {
                    $deskripsi = "Cek Mungkin Lupa Absen";
                    // dd($get_clock_out);
                }
                if (date('H:i:s', strtotime($clock_in)) > date('H:i:s', strtotime($clock_out))) {

                    if ($clock_out) {
                        $deskripsi = "Cek Mungkin Terhitung 2 Hari (" . $clock_in . ' - ' . $clock_out . ')';
                    } else {
                        $deskripsi = "Cek Mungkin Lupa Absen";
                    }
                    // dd('tesss');
                }

                //set list
                $list_absen[] = [
                    // 'Emp No'     => $stf->NIK,
                    // 'AC-No'      => '',
                    'NIK'        => $stf->NIK,
                    'Name'       => $staff->name,
                    // 'Auto-Asign' => $idLembur,
                    'Date'       => date('Y-m-d', strtotime($data->created_at)),
                    // 'TimeTable'  => '',
                    // 'On_Duty'    => $duty_in ? date('H:i', strtotime($duty_in)) : '',
                    // 'Off_Duty'   => $duty_out ? date('H:i', strtotime($duty_out)) : '',
                    'Clock_in'   => $clock_in ? date('Y-m-d H:i:s', strtotime($clock_in)) : 'null',
                    'Clock_out'  => $clock_out ? date('Y-m-d H:i:s', strtotime($clock_out)) : 'null',
                    // 'Normal'     => '',
                    // 'Real time'  => '',
                    // 'Late'       => $shift,
                    // 'Early'      => '',
                    // 'Absent'     => '',
                    // 'OT Time'    => '',
                    // 'Work Time'  => '',
                    // 'Exception'  => '',
                    // 'Must C/In'  => '',
                    // 'Must C/Out' => '',
                    // 'Department' => '',
                    // 'NDays'      => '',
                    // 'WeekEnd'    => '',
                    // 'Holiday'    => '',
                    // 'ATT_Time'   => '',
                    // 'NDays_OT'   => '',
                    // 'WeekEnd_OT' => '',
                    // 'Holiday_OT' => '',
                    'Lembur_in'  => $clock_lembur_in,
                    'Lembur_out' => $clock_lembur_out,
                    'Lembur'     => $lemburJumlah,
                    'Lembur_4'   => $lemburLebih,
                    'Flag'       => $flag,
                    'Status'     => $status,
                    'Keterangan' => $keterangan,
                    'Kegiatan1'  => $kegiatan1 ? date('Y-m-d H:i:s', strtotime($kegiatan1)) : 'null',
                    'Kegiatan2'  => $kegiatan2 ? date('Y-m-d H:i:s', strtotime($kegiatan2)) : 'null',
                ];
            }

            $list_absen = collect($list_absen);

            if ($staff->work_type_id === 2) {
                $list_absen_excel2 = [];
                // untuk shift start
                // $shifts = ShiftPlannerStaffs::where('staff_id', $staff->id)
                //     ->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$date_from, $date_to])
                //     ->get();
                $shifts = ShiftPlannerStaffs::selectRaw('shift_planner_staffs.*')->where('staff_id', $staff->id)
                //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$date_from, $date_to])
                    ->whereRaw("DATE(shift_planner_staffs.start) >= '" . $date_from . "' AND DATE(shift_planner_staffs.start) <= '" . $date_to . "'")
                    ->get();
                // dd($shifts);
                // foreach ($list_absen as $data) {
                //     $list_absen_excel[] = $data;
                // }
                // $o = 0;
                foreach ($shifts as $data) {
                    // $o++;
                    $ck1 = $list_absen->where('Date', date('Y-m-d', strtotime($data->start)))->sortBy('Clock_in')->first();
                    if ($ck1) {
                        $list_absen_excel2[] = $ck1;
                        // dd('ssssss', $ck1);
                    }
                    $cek = $list_absen->where('Late', $data->id)->first();
                    // $cek = true;
                    if (! $cek) {
                        // dd($data->id);
                        $cek_keterangan = $list_absen->where('Date', date('Y-m-d', strtotime($data->start)))->first();
                        // $cek_keterangan = $list_absen->where('Date', date('Y-m-d', strtotime($data->start)))->first();
                        if ($cek_keterangan) {
                            // $list_absen_excel2[] = [
                            //     'Emp No' => $stf->NIK,
                            //     'AC-No' => '',
                            //     'No' => $stf->NIK,
                            //     'Name' => $staff->name,
                            //     'Auto-Asign' => '',
                            //     'Date' => date('d/m/Y', strtotime($data->start)),
                            //     'TimeTable' => '',
                            //     'On_Duty' => '',
                            //     'Off_Duty' => '',
                            //     'Clock_in' => $cek_keterangan['Clock_in'],
                            //     'Clock_out' => $cek_keterangan['Clock_out'],
                            //     'keterangan' => $lemburJumlah,
                            //     'deskripsi' => $lemburLebih,
                            //     'shift' => $data->id,
                            // ];
                        } else {
                            $list_absen_excel2[] = [
                                // 'Emp No'     => $stf->NIK,
                                // 'AC-No'      => '',
                                'NIK'        => $stf->NIK,
                                'Name'       => $staff->name,
                                // 'Auto-Asign' => '',
                                'Date'       => date('Y-m-d', strtotime($data->start)),
                                // 'TimeTable'  => '',
                                // 'On_Duty'    => '',
                                // 'Off_Duty'   => '',
                                'Clock_in'   => 'null',
                                'Clock_out'  => 'null',
                                // 'keterangan' => $lemburJumlah,
                                // 'deskripsi'  => $lemburLebih,
                                // 'shift'      => $data->id,
                                'Lembur'     => '',
                                'Lembur_4'   => '',
                                'Flag'       => '',
                                'Status'     => 'alfa',
                                'Keterangan' => 'Alfa',
                                'Kegiatan1'  => 'null',
                                'Kegiatan2'  => 'null',
                            ];
                        }
                    }
                }

                //set rows planner shift
                $list_absen_shift = collect($list_absen_excel2);
                $dataLemburShift  = $list_absen->where('Lembur', '>', 0);
                foreach ($dataLemburShift as $data) {
                    $cek_if_lembur_masuk = $list_absen_shift->where('Date', date('Y-m-d', strtotime($data['Date'])))->first();
                    if ($cek_if_lembur_masuk) {

                    } else {
                        $data['Clock_in']    = $data['Lembur_in'];
                        $data['Clock_out']   = $data['Lembur_out'];
                        $data['Status']      = 'libur';
                        $data['Keterangan']  = 'Libur';
                        $list_absen_excel2[] = $data;
                    }
                }

                $c = collect($list_absen_excel2);
                foreach ($c->sortBy('Date') as $data) {
                    $list_absen_excel[] = $data;
                }
                // dd($list_absen_excel);
                // $shifts = Absence::leftJoin('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
                //     ->where('staff_id', $staff->id)
                //     ->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$date_from, $date_to])
                //     ->get();
                // dd('aaa', $list_absen_excel);
                // dd($shifts);
                // dd($shifts);
                // dd($o, $list_absen_excel);
                // untuk shift end
            } else {

                $dateRange = CarbonPeriod::create($date_from, $date_to);
                $dates     = $dateRange->toArray();

                $i = 0;

                //loop semua rentang hari, kalau diatas cuman loop logs saja
                foreach ($dates as $dt) {
                    $day_id = date('w', strtotime($dt->format('Y-m-d'))) == "0" ? '7' : date('w', strtotime($dt->format('Y-m-d')));

                    $list = $list_absen->where('Date', $dt->format('Y-m-d'))->sortBy('Clock_in')->first();

                    //check hari ada di list absen atas kalau tidak cek hari libur non sabtu minggu
                    if ($list != null) {
                        $cek_masuk = $list_absen->where('Date', $dt->format('Y-m-d'))->where('status', 'masuknormal')->first();

                        $cek_lembur = $list_absen->where('Date', $dt->format('Y-m-d'))->where('flag', 'lembur')->first();
                        if ($cek_masuk && $cek_lembur) {
                            $deskripsi = "";
                            if ($cek_lembur['deskripsi'] != "") {
                                $deskripsi = $cek_lembur['deskripsi'];
                            } else {
                                $deskripsi = $cek_masuk['deskripsi'];
                            }
                            $list_absen_excel[] = [
                                // 'Emp No'     => $cek_masuk['No'],
                                // 'AC-No'      => '',
                                'NIK'        => $stf->NIK,
                                'Name'       => $cek_masuk['Name'],
                                // 'Auto-Asign' => '',
                                'Date'       => $cek_masuk['Date'],
                                // 'TimeTable'  => '',
                                // 'On_Duty'    => $cek_masuk['On_Duty'],
                                // 'Off_Duty'   => $cek_masuk['Off_Duty'],
                                'Clock_in'   => $cek_masuk['Clock_in'],
                                'Clock_out'  => $cek_masuk['Clock_out'],
                                // 'Clock_in' => $cek_masuk['Clock_in'] && $cek_masuk['Clock_in'] = "" ? $cek_masuk['Clock_in'] : $deskripsi,
                                // 'Clock_out' => $cek_masuk['Clock_out'] && $cek_masuk['Clock_out'] = "" ? $cek_masuk['Clock_out'] : $deskripsi,
                                // 'keterangan' => 'Masuk dan Lembur',
                                // 'Normal'     => '',
                                // 'Real time'  => '',
                                // 'Late'       => '',
                                // 'Early'      => '',
                                // 'Absent'     => '',
                                // 'OT Time'    => '',
                                // 'Work Time'  => '',
                                // 'Exception'  => '',
                                // 'Must C/In'  => '',
                                // 'Must C/Out' => '',
                                // 'Department' => '',
                                // 'NDays'      => '',
                                // 'WeekEnd'    => '',
                                // 'Holiday'    => '',
                                // 'ATT_Time'   => '',
                                // 'NDays_OT'   => '',
                                // 'WeekEnd_OT' => '',
                                // 'Holiday_OT' => '',
                                'Lembur'     => $lemburJumlah,
                                'Lembur_4'   => $lemburLebih,
                                'Flag'       => $cek_masuk['Flag'],
                                'Status'     => $cek_masuk['Status'],
                                'Keterangan' => $cek_masuk['Keterangan'],
                                'Kegiatan1'  => $cek_masuk['Kegiatan1'],
                                'Kegiatan2'  => $cek_masuk['Kegiatan2'],
                            ];
                        } else if ($cek_lembur) {
                            // untuk absen lembur
                            $list_absen_excel[] = $cek_lembur;
                        } else if ($cek_lembur) {
                            // untuk absen masuk
                            $list_absen_excel[] = $cek_masuk;
                        } else {
                            $list_absen_excel[] = $list;
                        }
                    } else if (in_array($day_id, $jadwal_libur)) {
                        // libur
                        $list_absen_excel[] = [
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '',
                            // 'Off_Duty'   => '',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => $lemburJumlah,
                            'Lembur_4'   => $lemburLebih,
                            'Flag'       => $lemburJumlah > 0 ? 'lembur' : '',
                            'Status'     => 'libur',
                            'Keterangan' => $day_id === '7' ? 'Libur Minggu' : 'Libur Sabtu',
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ];
                    } else {
                        // tanpa keterangan
                        $list_absen_excel[] = [
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '07:30',
                            // 'Off_Duty'   => '15:30',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => '',
                            'Lembur_4'   => '',
                            'Flag'       => '',
                            'Status'     => 'alfa',
                            'Keterangan' => 'Alfa',
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ];
                    }
                }
            }
        }

        return response()->json([
            'message' => 'success',
            'data'    => $list_absen_excel,
        ]);
    }

    public function reportAbsenceExcel(Request $request)
    {
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $lemburLebih  = "";
        $lemburJumlah = "";
        // dd($request->all());

        $dapertement_id = '';
        if ($request->kode_bagian) {
            $dapertement_row = Dapertement::select('id')->where('code', $request->kode_bagian)->first();
            $dapertement_id  = $dapertement_row->id;
        }

        //get staffs
        $staffs = Staff::FilterWorkUnit($request->work_unit_id)
            ->FilterNik($request->NIK)
            ->FilterId($request->staff_id)
            ->FilterJob1($request->job_id)
            ->FilterSubdapertement($request->subdapertement_id, $request->job_id)
            ->FilterDapertement($dapertement_id)
            ->orderBy('NIK', 'ASC')
            ->where('_status', 'active')
            ->get();

        $list_absen_excel = [];
        $date_from        = $request->from;
        $date_to          = $request->to;

        //loop staffs
        foreach ($staffs as $stf) {
            // echo $stf->NIK;
            $staff = Staff::select(
                'staffs.*',
                DB::raw(
                    '(CASE WHEN staffs.type = "employee" THEN  SUBSTRING(staffs.NIK, 5) ELSE staffs.NIK END)  AS NIK'
                )

            )->where('id', $stf->id)->first();

            //get sabtu minggu
            $get_jadwal_libur = Day::select('days.*')->leftJoin(
                'work_type_days',
                function ($join) use ($staff) {
                    $join->on('days.id', '=', 'work_type_days.day_id')
                        ->where('work_type_id', $staff->work_type_id);
                }
            )->where('work_type_days.day_id', '=', null)->get();
            $jadwal_libur = [];
            foreach ($get_jadwal_libur as $data) {
                $jadwal_libur[] = $data->id;
            }

            if ($staff->work_type_id === 2) {
                $list_absen_excel2 = [];

                $dateRange = CarbonPeriod::create($date_from, $date_to);
                $dates     = $dateRange->toArray();
                $i         = 0;
                //loop semua rentang hari, kalau diatas cuman loop logs saja
                foreach ($dates as $dt) {
                    //if holiday
                    $holidays = Holiday::whereDate('start', '=', $dt->format('Y-m-d'))->first();
                    $day_id   = date('w', strtotime($dt->format('Y-m-d'))) == "0" ? '7' : date('w', strtotime($dt->format('Y-m-d')));
                    //shift data
                    $data = ShiftPlannerStaffs::selectRaw('shift_planner_staffs.*')->where('staff_id', $staff->id)
                        ->whereRaw("DATE(shift_planner_staffs.start) >= '" . $dt->format('Y-m-d') . "' AND DATE(shift_planner_staffs.start) <= '" . $dt->format('Y-m-d') . "'")
                        ->first();
                    //absence data
                    $absence = Absence::with(['absence_logs', 'absence_logs.shiftGroupTimeSheets', 'staffs'])
                        ->where('staff_id', $staff->id)
                        ->whereRaw("DATE(absences.created_at) >= '" . $dt->format('Y-m-d') . "' AND DATE(absences.created_at) <= '" . $dt->format('Y-m-d') . "'")
                        ->first();
                    if (! $data && ! $absence && ! $holidays) {
                        // libur shift
                        $list_absen = [[
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '',
                            // 'Off_Duty'   => '',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => $lemburJumlah,
                            'Lembur_4'   => $lemburLebih,
                            'Flag'       => $lemburJumlah > 0 ? 'lembur' : '',
                            'Status'     => 'libur',
                            'Keterangan' => 'Libur',
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ]];
                    } else if ($holidays && ! $absence) {
                        $list_absen = [[
                            // 'Emp No'     => $staff->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '',
                            // 'Off_Duty'   => '',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => '',
                            'Lembur_4'   => '',
                            'Flag'       => '',
                            'Status'     => 'libur',
                            'Keterangan' => $holidays->description,
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ]];
                    } else {

                        $shift     = "";
                        $deskripsi = "";
                        $idLembur  = "";

                        $get_duty_in   = $absence ? $absence->absence_logs->where('absence_category_id', 1)->first() : null;
                        $get_duty_out  = $absence ? $absence->absence_logs->where('absence_category_id', 2)->first() : null;
                        $get_clock_in  = $absence ? $absence->absence_logs->where('absence_category_id', 1)->first() : null;
                        $get_clock_out = $absence ? $absence->absence_logs->where('absence_category_id', 2)->first() : null;

                        $get_lembur_in  = $absence ? $absence->absence_logs->where('absence_category_id', 9)->first() : null;
                        $get_lembur_out = $absence ? $absence->absence_logs->where('absence_category_id', 10)->first() : null;
                        $get_dinasLuar  = $absence ? $absence->absence_logs->where('absence_category_id', 7)->first() : null;
                        $get_cuti       = $absence ? $absence->absence_logs->where('absence_category_id', 8)->first() : null;
                        $get_izin       = $absence ? $absence->absence_logs->where('absence_category_id', 13)->first() : null;
                        $get_permisi_in = $absence ? $absence->absence_logs->where('absence_category_id', 11)->first() : null;
                        $get_dispen     = $absence ? $absence->absence_logs->where('absence_category_id', 14)->first() : null;
                        $get_datang     = $absence ? $absence->absence_logs->where('absence_category_id', 1)->where('status', 0)->first() : null;
                        $get_pulang     = $absence ? $absence->absence_logs->where('absence_category_id', 2)->where('status', 0)->first() : null;
                        $get_kegiatan1  = $absence ? $absence->absence_logs->where('absence_category_id', 3)->where('status', 0)->where('image', '!=', '')->first() : null;
                        $get_kegiatan2  = $absence ? $absence->absence_logs->where('absence_category_id', 4)->where('status', 0)->where('image', '!=', '')->first() : null;
                        //additional

                        //get req izin
                        $get_req_izin = AbsenceRequest::whereRaw("DATE(start) = '" . $dt->format('Y-m-d') . "'")
                            ->where('category', 'permission')
                            ->where('staff_id', $stf->id)
                            ->where('status', 'approve')
                            ->first();

                        if ($get_duty_in) {
                            if ($get_duty_in->shiftGroupTimeSheets) {
                                $duty_in = $get_duty_in->shiftGroupTimeSheets->time;
                            } else {
                                $duty_in = '';
                            }
                        } else {
                            $duty_in = '';
                        }

                        if ($get_duty_out) {
                            if ($get_duty_out->shiftGroupTimeSheets) {
                                $duty_out = $get_duty_out->shiftGroupTimeSheets->time;
                            } else {
                                $duty_out = '';
                            }
                        } else {
                            $duty_out = '';
                        }

                        $clock_in  = $get_clock_in ? $get_clock_in->register : '';
                        $clock_out = $get_clock_out ? $get_clock_out->register : '';
                        $shift     = $get_clock_out ? $get_clock_out->shift_planner_id : '';
                        $kegiatan1 = $get_kegiatan1 ? $get_kegiatan1->register : '';
                        $kegiatan2 = $get_kegiatan2 ? $get_kegiatan2->register : '';

                        //if clock/check in but late more than 210 minutes => alfa
                        $status     = '';
                        $flag       = '';
                        $keterangan = '';
                        if ($clock_in != '') {
                            $keterangan   = "Masuk";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $flag         = '';
                            $status       = 'masuknormal';
                            if ($staff->work_type_id != 2) {
                                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($get_clock_in->timein))));
                                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                                if ($clock_in >= $cekDateNew) {
                                    $clock_in   = "";
                                    $keterangan = "Lambat 3.5 Jam";
                                    $status     = 'alfa';
                                }
                            } else {
                                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($get_clock_in->timein))));
                                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                                if ($clock_in >= $cekDateNew) {
                                    $clock_in   = "";
                                    $keterangan = "Lambat 2 Jam";
                                    $status     = 'alfa';
                                }
                            }
                            //get late
                            $flag = ($get_clock_in->late > 0.016667 && $status == 'masuknormal') ? 'lambat' : '';

                            // $clock_in =  "";
                        } else if ($get_dinasLuar) {
                            $flag         = '';
                            $status       = 'dinas';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Dinas Luar";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_cuti) {
                            $flag         = '';
                            $status       = 'cuti';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Cuti";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_dispen) {
                            $flag         = '';
                            $status       = 'dispen';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Dispensasi";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_izin || $get_req_izin) {
                            $flag      = '';
                            $status    = 'izin';
                            $clock_in  = "";
                            $clock_out = "";
                            $kegiatan1 = '';
                            $kegiatan2 = '';
                            if ($get_izin) {
                                if ($get_izin->absenceRequests->type == "sick") {
                                    $keterangan = "Sakit Tidak Izin Dokter";
                                    $status     = 'sakittidakizindokter';
                                } else if ($get_izin->absenceRequests->type == "sick_proof") {
                                    $keterangan = "Sakit Izin Dokter";
                                    $status     = 'sakitizindokter';
                                } else {
                                    $keterangan = "Izin";
                                }
                            } else {
                                if ($get_req_izin->type == "sick") {
                                    $keterangan = "Sakit Tidak Izin Dokter";
                                    $status     = 'sakittidakizindokter';
                                } else if ($get_req_izin->type == "sick_proof") {
                                    $keterangan = "Sakit Izin Dokter";
                                    $status     = 'sakitizindokter';
                                } else {
                                    $keterangan = "Izin";
                                }
                            }
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else {
                            $flag         = '';
                            $status       = 'alfa';
                            $keterangan   = "Alfa";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        }
                        // if lembur (lemburLebih = lembur > 4jam) (lembur jumlah = total jam lembur)
                        $clock_lembur_in  = 'null';
                        $clock_lembur_out = 'null';
                        if ($get_lembur_in) {
                            $flag .= $flag != '' ? ',lembur' : 'lembur';
                            $clock_lembur_in  = $get_lembur_in->register;
                            $clock_lembur_out = $get_lembur_out->register;
                            $idLembur         = "lembur";
                            $lemburJumlah     = round($get_lembur_out->duration);
                            $lemburLebih      = "";
                            if ($get_lembur_out->duration > 4) {
                                $lemburLebih = "Y";
                            } else if ($get_lembur_out->duration <= 4) {
                                $lemburLebih = "N";
                            }
                            if ($status == 'alfa') {
                                $status     = 'libur';
                                $keterangan = "Libur";
                            }
                        }
                        // if permisi
                        if ($get_permisi_in) {
                            //check reguler or shift
                            if ($staff->work_type_id != 2) {
                                //if reguler get hari
                                $day_id = date('w', strtotime($get_permisi_in->register)) == "0" ? '7' : date('w', strtotime($get_permisi_in->register));
                                //get clock
                                $date_now_12      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 12:00:00";
                                $date_now_11      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 11:00:00";
                                $register_to_time = strtotime(date('Y-m-d H:i:s', strtotime($get_permisi_in->register)));
                                $now_12_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_12)));
                                $now_11_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_11)));
                                //echo $day_id." - ". $register_to_time." - ". $now_12_to_time." - ". $now_11_to_time;
                                //if reguler mon - thu, check if jam mulai < 12
                                if ($day_id >= 1 && $day_id <= 4 && ($register_to_time < $now_12_to_time)) {
                                    $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                                }
                                //if reguler fri, check if jam mulai < 11
                                else if (($day_id == 5 || $day_id == 6) && ($register_to_time < $now_11_to_time)) {
                                    $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                                } else {
                                    $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                                }
                            } else {
                                $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                            }
                        }

                        //get bolong masuk
                        if (! $get_datang && $get_pulang) {
                            $flag .= $flag != '' ? ',bolongdatang' : 'bolongdatang';
                        }
                        if ($get_datang && ! $get_pulang) {
                            $flag .= $flag != '' ? ',bolongpulang' : 'bolongpulang';
                        }
                        //get bolong kegiatan
                        if (! $get_kegiatan1 && ($get_pulang || $get_datang)) {
                            $flag .= $flag != '' ? ',bolongkegiatan1' : 'bolongkegiatan1';
                        }
                        if (! $get_kegiatan2 && ($get_pulang || $get_datang)) {
                            $flag .= $flag != '' ? ',bolongkegiatan2' : 'bolongkegiatan2';
                        }

                        if ($get_clock_out && $get_clock_out->status == '1' && $status != "alfa") {
                            $deskripsi = "Cek Mungkin Lupa Absen";
                            // dd($get_clock_out);
                        }
                        if (date('H:i:s', strtotime($clock_in)) > date('H:i:s', strtotime($clock_out))) {

                            if ($clock_out) {
                                $deskripsi = "Cek Mungkin Terhitung 2 Hari (" . $clock_in . ' - ' . $clock_out . ')';
                            } else {
                                $deskripsi = "Cek Mungkin Lupa Absen";
                            }
                            // dd('tesss');
                        }

                        //set list
                        $list_absen = [[
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => $idLembur,
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => $duty_in ? date('H:i', strtotime($duty_in)) : '',
                            // 'Off_Duty'   => $duty_out ? date('H:i', strtotime($duty_out)) : '',
                            'Clock_in'   => $clock_in ? date('Y-m-d H:i:s', strtotime($clock_in)) : 'null',
                            'Clock_out'  => $clock_out ? date('Y-m-d H:i:s', strtotime($clock_out)) : 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => $shift,
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur_in'  => $clock_lembur_in,
                            'Lembur_out' => $clock_lembur_out,
                            'Lembur'     => $lemburJumlah,
                            'Lembur_4'   => $lemburLebih,
                            'Flag'       => $flag,
                            'Status'     => $status,
                            'Keterangan' => $keterangan,
                            'Kegiatan1'  => $kegiatan1 ? date('Y-m-d H:i:s', strtotime($kegiatan1)) : 'null',
                            'Kegiatan2'  => $kegiatan2 ? date('Y-m-d H:i:s', strtotime($kegiatan2)) : 'null',
                        ]];
                    }

                    $list_absen = collect($list_absen);

                    $ck1 = $list_absen->where('Date', $dt->format('Y-m-d'))->sortBy('Clock_in')->first();
                    if ($ck1) {
                        $list_absen_excel2[] = $ck1;
                        // dd('ssssss', $ck1);
                    }
                    $cek = $list_absen->where('Late', 9)->first();
                    // $cek = true;
                    if (! $cek) {
                        $cek_keterangan = $list_absen->where('Date', $dt->format('Y-m-d'))->first();
                        // $cek_keterangan = $list_absen->where('Date', $dt->format('Y-m-d'))->first();
                        if ($cek_keterangan) {

                        } else {
                            $list_absen_excel2[] = [
                                // 'Emp No'     => $stf->NIK,
                                // 'AC-No'      => '',
                                'NIK'        => $stf->NIK,
                                'Name'       => $staff->name,
                                // 'Auto-Asign' => '',
                                'Date'       => $dt->format('Y-m-d'),
                                // 'TimeTable'  => '',
                                // 'On_Duty'    => '',
                                // 'Off_Duty'   => '',
                                'Clock_in'   => 'null',
                                'Clock_out'  => 'null',
                                // 'keterangan' => $lemburJumlah,
                                // 'deskripsi'  => $lemburLebih,
                                // 'shift'      => ,
                                'Lembur'     => '',
                                'Lembur_4'   => '',
                                'Flag'       => '',
                                'Status'     => 'alfa',
                                'Keterangan' => 'Alfa',
                                'Kegiatan1'  => 'null',
                                'Kegiatan2'  => 'null',
                            ];
                        }
                    }
                }

                //set rows planner shift
                $list_absen_shift = collect($list_absen_excel2);
                $dataLemburShift  = $list_absen->where('Lembur', '>', 0);
                foreach ($dataLemburShift as $data) {
                    $cek_if_lembur_masuk = $list_absen_shift->where('Date', date('Y-m-d', strtotime($data['Date'])))->first();
                    if ($cek_if_lembur_masuk) {

                    } else {
                        $data['Clock_in']    = $data['Lembur_in'];
                        $data['Clock_out']   = $data['Lembur_out'];
                        $data['Status']      = 'libur';
                        $data['Keterangan']  = 'Libur';
                        $list_absen_excel2[] = $data;
                    }
                }

                $c = collect($list_absen_excel2);
                foreach ($c->sortBy('Date') as $data) {
                    $list_absen_excel[] = $data;
                }
            } else {
                //REGULAR
                $dateRange = CarbonPeriod::create($date_from, $date_to);
                $dates     = $dateRange->toArray();
                $i         = 0;
                //loop semua rentang hari, kalau diatas cuman loop logs saja
                foreach ($dates as $dt) {
                    //if holiday
                    $holidays = Holiday::whereDate('start', '=', $dt->format('Y-m-d'))->first();
                    $day_id   = date('w', strtotime($dt->format('Y-m-d'))) == "0" ? '7' : date('w', strtotime($dt->format('Y-m-d')));
                    $absence  = Absence::with(['absence_logs', 'absence_logs.workTypeDays', 'staffs'])
                        ->where('staff_id', $staff->id)
                        ->whereDate('absences.created_at', '=', $dt->format('Y-m-d'))
                        ->first();
                    if (in_array($day_id, $jadwal_libur) && !$absence) {
                        // libur
                        $list_absen = [[
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '',
                            // 'Off_Duty'   => '',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => $lemburJumlah,
                            'Lembur_4'   => $lemburLebih,
                            'Flag'       => $lemburJumlah > 0 ? 'lembur' : '',
                            'Status'     => 'libur',
                            'Keterangan' => $day_id === '7' ? 'Libur Minggu' : 'Libur Sabtu',
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ]];
                    } else if ($holidays && !$absence) {
                        $list_absen = [[
                            // 'Emp No'     => $staff->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => date('Y-m-d', strtotime($holidays->start)),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '',
                            // 'Off_Duty'   => '',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => '',
                            'Lembur_4'   => '',
                            'Flag'       => '',
                            'Status'     => 'libur',
                            'Keterangan' => $holidays->description,
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ]];
                    } else {
                        // untuk reguler

                        $shift     = "";
                        $deskripsi = "";
                        $idLembur  = "";

                        $get_duty_in   = $absence ? $absence->absence_logs->where('absence_category_id', 1)->first() : null;
                        $get_duty_out  = $absence ? $absence->absence_logs->where('absence_category_id', 2)->first() : null;
                        $get_clock_in  = $absence ? $absence->absence_logs->where('absence_category_id', 1)->first() : null;
                        $get_clock_out = $absence ? $absence->absence_logs->where('absence_category_id', 2)->first() : null;

                        $get_lembur_in  = $absence ? $absence->absence_logs->where('absence_category_id', 9)->first() : null;
                        $get_lembur_out = $absence ? $absence->absence_logs->where('absence_category_id', 10)->first() : null;
                        $get_dinasLuar  = $absence ? $absence->absence_logs->where('absence_category_id', 7)->first() : null;
                        $get_cuti       = $absence ? $absence->absence_logs->where('absence_category_id', 8)->first() : null;
                        $get_izin       = $absence ? $absence->absence_logs->where('absence_category_id', 13)->first() : null;
                        $get_permisi_in = $absence ? $absence->absence_logs->where('absence_category_id', 11)->first() : null;
                        $get_dispen     = $absence ? $absence->absence_logs->where('absence_category_id', 14)->first() : null;
                        $get_datang     = $absence ? $absence->absence_logs->where('absence_category_id', 1)->where('status', 0)->first() : null;
                        $get_pulang     = $absence ? $absence->absence_logs->where('absence_category_id', 2)->where('status', 0)->first() : null;
                        $get_kegiatan1  = $absence ? $absence->absence_logs->where('absence_category_id', 3)->where('status', 0)->first() : null;
                        $get_kegiatan2  = $absence ? $absence->absence_logs->where('absence_category_id', 4)->where('status', 0)->first() : null;
                        //additional

                        //get req izin
                        $get_req_izin = AbsenceRequest::whereDate('start', '=', $dt->format('Y-m-d'))
                            ->where('category', 'permission')
                            ->where('staff_id', $stf->id)
                            ->where('status', 'approve')
                            ->first();

                        //set ref time duty in/out
                        //set log time clock in/out register
                        //if not shift
                        if ($staff->work_type_id != 2) {

                            if ($get_duty_in) {
                                if ($get_duty_in->workTypeDays) {
                                    $duty_in = $get_duty_in->workTypeDays->time;
                                } else {
                                    $duty_in = '';
                                }
                            } else {
                                $duty_in = '';
                            }

                            if ($get_duty_out) {
                                if ($get_duty_out->workTypeDays) {
                                    $duty_out = $get_duty_out->workTypeDays->time;
                                } else {
                                    $duty_out = '';
                                }
                            } else {
                                $duty_out = '';
                            }

                            $clock_in  = $get_clock_in ? $get_clock_in->register : '';
                            $clock_out = $get_clock_out ? $get_clock_out->register : '';
                            $kegiatan1 = $get_kegiatan1 ? $get_kegiatan1->register : '';
                            $kegiatan2 = $get_kegiatan2 ? $get_kegiatan2->register : '';
                        }

                        //if clock/check in but late more than 210 minutes => alfa
                        $status     = '';
                        $flag       = '';
                        $keterangan = '';
                        if ($clock_in != '') {
                            $keterangan   = "Masuk";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $flag         = '';
                            $status       = 'masuknormal';
                            if ($staff->work_type_id != 2) {
                                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($get_clock_in->timein))));
                                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                                if ($clock_in >= $cekDateNew) {
                                    $clock_in   = "";
                                    $keterangan = "Lambat 3.5 Jam";
                                    $status     = 'alfa';
                                }
                            } else {
                                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($get_clock_in->timein))));
                                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                                if ($clock_in >= $cekDateNew) {
                                    $clock_in   = "";
                                    $keterangan = "Lambat 2 Jam";
                                    $status     = 'alfa';
                                }
                            }
                            //get late
                            $flag = ($get_clock_in->late > 0.016667 && $status == 'masuknormal') ? 'lambat' : '';

                            // $clock_in =  "";
                        } else if ($get_dinasLuar) {
                            $flag         = '';
                            $status       = 'dinas';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Dinas Luar";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_cuti) {
                            $flag         = '';
                            $status       = 'cuti';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Cuti";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_dispen) {
                            $flag         = '';
                            $status       = 'dispen';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Dispensasi";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_izin || $get_req_izin) {
                            $flag      = '';
                            $status    = 'izin';
                            $clock_in  = "";
                            $clock_out = "";
                            $kegiatan1 = '';
                            $kegiatan2 = '';
                            if ($get_izin) {
                                if ($get_izin->absenceRequests->type == "sick") {
                                    $keterangan = "Sakit Tidak Izin Dokter";
                                    $status     = 'sakittidakizindokter';
                                } else if ($get_izin->absenceRequests->type == "sick_proof") {
                                    $keterangan = "Sakit Izin Dokter";
                                    $status     = 'sakitizindokter';
                                } else {
                                    $keterangan = "Izin";
                                }
                            } else {
                                if ($get_req_izin->type == "sick") {
                                    $keterangan = "Sakit Tidak Izin Dokter";
                                    $status     = 'sakittidakizindokter';
                                } else if ($get_req_izin->type == "sick_proof") {
                                    $keterangan = "Sakit Izin Dokter";
                                    $status     = 'sakitizindokter';
                                } else {
                                    $keterangan = "Izin";
                                }
                            }
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else {
                            $flag         = '';
                            $status       = 'alfa';
                            $keterangan   = "Alfa";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        }
                        // if lembur (lemburLebih = lembur > 4jam) (lembur jumlah = total jam lembur)
                        $clock_lembur_in  = 'null';
                        $clock_lembur_out = 'null';
                        if ($get_lembur_in) {
                            $flag .= $flag != '' ? ',lembur' : 'lembur';
                            $clock_lembur_in  = $get_lembur_in->register;
                            $clock_lembur_out = $get_lembur_out->register;
                            $idLembur         = "lembur";
                            $lemburJumlah     = round($get_lembur_out->duration);
                            $lemburLebih      = "";
                            if ($get_lembur_out->duration > 4) {
                                $lemburLebih = "Y";
                            } else if ($get_lembur_out->duration <= 4) {
                                $lemburLebih = "N";
                            }
                            if($status=='alfa'){
                                $keterangan   = 'Libur';
                                $status       = 'libur';
                            }
                        }
                        // if permisi
                        if ($get_permisi_in) {
                            //check reguler or shift
                            if ($staff->work_type_id != 2) {
                                //if reguler get hari
                                $day_id = date('w', strtotime($get_permisi_in->register)) == "0" ? '7' : date('w', strtotime($get_permisi_in->register));
                                //get clock
                                $date_now_12      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 12:00:00";
                                $date_now_11      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 11:00:00";
                                $register_to_time = strtotime(date('Y-m-d H:i:s', strtotime($get_permisi_in->register)));
                                $now_12_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_12)));
                                $now_11_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_11)));
                                //echo $day_id." - ". $register_to_time." - ". $now_12_to_time." - ". $now_11_to_time;
                                //if reguler mon - thu, check if jam mulai < 12
                                if ($day_id >= 1 && $day_id <= 4 && ($register_to_time < $now_12_to_time)) {
                                    $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                                }
                                //if reguler fri, check if jam mulai < 11
                                else if (($day_id == 5 || $day_id == 6) && ($register_to_time < $now_11_to_time)) {
                                    $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                                } else {
                                    $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                                }
                            } else {
                                $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                            }
                        }

                        //get bolong masuk
                        if (! $get_datang && $get_pulang) {
                            $flag .= $flag != '' ? ',bolongdatang' : 'bolongdatang';
                        }
                        if ($get_datang && ! $get_pulang) {
                            $flag .= $flag != '' ? ',bolongpulang' : 'bolongpulang';
                        }
                        //get bolong kegiatan
                        if (! $get_kegiatan1 && ($get_pulang || $get_datang)) {
                            $flag .= $flag != '' ? ',bolongkegiatan1' : 'bolongkegiatan1';
                        }
                        if (! $get_kegiatan2 && ($get_pulang || $get_datang)) {
                            $flag .= $flag != '' ? ',bolongkegiatan2' : 'bolongkegiatan2';
                        }

                        if ($get_clock_out && $get_clock_out->status == '1' && $status != "alfa") {
                            $deskripsi = "Cek Mungkin Lupa Absen";
                            // dd($get_clock_out);
                        }

                        if ($get_clock_out && $get_clock_out->status == '1' && $status != "alfa") {
                            $deskripsi = "Cek Mungkin Lupa Absen";
                            // dd($get_clock_out);
                        }
                        if (date('H:i:s', strtotime($clock_in)) > date('H:i:s', strtotime($clock_out))) {

                            if ($clock_out) {
                                $deskripsi = "Cek Mungkin Terhitung 2 Hari (" . $clock_in . ' - ' . $clock_out . ')';
                            } else {
                                $deskripsi = "Cek Mungkin Lupa Absen";
                            }
                            // dd('tesss');
                        }

                        //set list
                        $list_absen = [[
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => $idLembur,
                            'Date'       => date('Y-m-d', strtotime($dt)),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => $duty_in ? date('H:i', strtotime($duty_in)) : '',
                            // 'Off_Duty'   => $duty_out ? date('H:i', strtotime($duty_out)) : '',
                            'Clock_in'   => $clock_in ? date('Y-m-d H:i:s', strtotime($clock_in)) : 'null',
                            'Clock_out'  => $clock_out ? date('Y-m-d H:i:s', strtotime($clock_out)) : 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => $shift,
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur_in'  => $clock_lembur_in,
                            'Lembur_out' => $clock_lembur_out,
                            'Lembur'     => $lemburJumlah,
                            'Lembur_4'   => $lemburLebih,
                            'Flag'       => $flag,
                            'Status'     => $status,
                            'Keterangan' => $keterangan,
                            'Kegiatan1'  => $kegiatan1 ? date('Y-m-d H:i:s', strtotime($kegiatan1)) : 'null',
                            'Kegiatan2'  => $kegiatan2 ? date('Y-m-d H:i:s', strtotime($kegiatan2)) : 'null',
                        ]];
                    }

                    $list_absen = collect($list_absen);

                    $day_id = date('w', strtotime($dt->format('Y-m-d'))) == "0" ? '7' : date('w', strtotime($dt->format('Y-m-d')));

                    $list = $list_absen->where('Date', date('Y-m-d', strtotime($dt)))->sortBy('Clock_in')->first();

                    if ($list != null) {
                        $cek_masuk = $list_absen->where('Date', $dt->format('Y-m-d'))->where('status', 'masuknormal')->first();

                        $cek_lembur = $list_absen->where('Date', $dt->format('Y-m-d'))->where('flag', 'lembur')->first();
                        if ($cek_masuk && $cek_lembur) {
                            $deskripsi = "";
                            if ($cek_lembur['deskripsi'] != "") {
                                $deskripsi = $cek_lembur['deskripsi'];
                            } else {
                                $deskripsi = $cek_masuk['deskripsi'];
                            }
                            $list_absen_excel[] = [
                                // 'Emp No'     => $cek_masuk['No'],
                                // 'AC-No'      => '',
                                'NIK'        => $stf->NIK,
                                'Name'       => $cek_masuk['Name'],
                                // 'Auto-Asign' => '',
                                'Date'       => $cek_masuk['Date'],
                                // 'TimeTable'  => '',
                                // 'On_Duty'    => $cek_masuk['On_Duty'],
                                // 'Off_Duty'   => $cek_masuk['Off_Duty'],
                                'Clock_in'   => $cek_masuk['Clock_in'],
                                'Clock_out'  => $cek_masuk['Clock_out'],
                                // 'Clock_in' => $cek_masuk['Clock_in'] && $cek_masuk['Clock_in'] = "" ? $cek_masuk['Clock_in'] : $deskripsi,
                                // 'Clock_out' => $cek_masuk['Clock_out'] && $cek_masuk['Clock_out'] = "" ? $cek_masuk['Clock_out'] : $deskripsi,
                                // 'keterangan' => 'Masuk dan Lembur',
                                // 'Normal'     => '',
                                // 'Real time'  => '',
                                // 'Late'       => '',
                                // 'Early'      => '',
                                // 'Absent'     => '',
                                // 'OT Time'    => '',
                                // 'Work Time'  => '',
                                // 'Exception'  => '',
                                // 'Must C/In'  => '',
                                // 'Must C/Out' => '',
                                // 'Department' => '',
                                // 'NDays'      => '',
                                // 'WeekEnd'    => '',
                                // 'Holiday'    => '',
                                // 'ATT_Time'   => '',
                                // 'NDays_OT'   => '',
                                // 'WeekEnd_OT' => '',
                                // 'Holiday_OT' => '',
                                'Lembur'     => $lemburJumlah,
                                'Lembur_4'   => $lemburLebih,
                                'Flag'       => $cek_masuk['Flag'],
                                'Status'     => $cek_masuk['Status'],
                                'Keterangan' => $cek_masuk['Keterangan'],
                                'Kegiatan1'  => $cek_masuk['Kegiatan1'],
                                'Kegiatan2'  => $cek_masuk['Kegiatan2'],
                            ];
                        } else if ($cek_lembur) {
                            // untuk absen lembur
                            $list_absen_excel[] = $cek_lembur;
                        } else if ($cek_lembur) {
                            // untuk absen masuk
                            $list_absen_excel[] = $cek_masuk;
                        } else {
                            $list_absen_excel[] = $list;
                        }
                    } else {
                        // tanpa keterangan
                        $list_absen_excel[] = [
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '07:30',
                            // 'Off_Duty'   => '15:30',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => '',
                            'Lembur_4'   => '',
                            'Flag'       => '',
                            'Status'     => 'alfa',
                            'Keterangan' => 'Alfa',
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ];
                    }
                }
            }
        }

        return response()->json([
            'message' => 'success',
            'data'    => $list_absen_excel,
        ]);
    }

    public function reportAbsenceExcelTest(Request $request)
    {
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $lemburLebih  = "";
        $lemburJumlah = "";
        // dd($request->all());

        $dapertement_id = '';
        if ($request->kode_bagian) {
            $dapertement_row = Dapertement::select('id')->where('code', $request->kode_bagian)->first();
            $dapertement_id  = $dapertement_row->id;
        }

        //get staffs
        $staffs = Staff::FilterWorkUnit($request->work_unit_id)
            ->FilterNik($request->NIK)
            ->FilterId($request->staff_id)
            ->FilterJob1($request->job_id)
            ->FilterSubdapertement($request->subdapertement_id, $request->job_id)
            ->FilterDapertement($dapertement_id)
            ->orderBy('NIK', 'ASC')
            ->where('_status', 'active')
            ->get();

        $list_absen_excel = [];
        $date_from        = $request->from;
        $date_to          = $request->to;

        //loop staffs
        foreach ($staffs as $stf) {
            // echo $stf->NIK;
            $staff = Staff::select(
                'staffs.*',
                DB::raw(
                    '(CASE WHEN staffs.type = "employee" THEN  SUBSTRING(staffs.NIK, 5) ELSE staffs.NIK END)  AS NIK'
                )

            )->where('id', $stf->id)->first();

            //get sabtu minggu
            $get_jadwal_libur = Day::select('days.*')->leftJoin(
                'work_type_days',
                function ($join) use ($staff) {
                    $join->on('days.id', '=', 'work_type_days.day_id')
                        ->where('work_type_id', $staff->work_type_id);
                }
            )->where('work_type_days.day_id', '=', null)->get();
            $jadwal_libur = [];
            foreach ($get_jadwal_libur as $data) {
                $jadwal_libur[] = $data->id;
            }

            if ($staff->work_type_id === 2) {
                $list_absen_excel2 = [];

                $dateRange = CarbonPeriod::create($date_from, $date_to);
                $dates     = $dateRange->toArray();
                $i         = 0;
                //loop semua rentang hari, kalau diatas cuman loop logs saja
                foreach ($dates as $dt) {
                    //if holiday
                    $holidays = Holiday::whereDate('start', '=', $dt->format('Y-m-d'))->first();
                    $day_id   = date('w', strtotime($dt->format('Y-m-d'))) == "0" ? '7' : date('w', strtotime($dt->format('Y-m-d')));
                    //shift data
                    $data = ShiftPlannerStaffs::selectRaw('shift_planner_staffs.*')->where('staff_id', $staff->id)
                        ->whereRaw("DATE(shift_planner_staffs.start) >= '" . $dt->format('Y-m-d') . "' AND DATE(shift_planner_staffs.start) <= '" . $dt->format('Y-m-d') . "'")
                        ->first();
                    //absence data
                    $absence = Absence::with(['absence_logs', 'absence_logs.shiftGroupTimeSheets', 'staffs'])
                        ->where('staff_id', $staff->id)
                        ->whereRaw("DATE(absences.created_at) >= '" . $dt->format('Y-m-d') . "' AND DATE(absences.created_at) <= '" . $dt->format('Y-m-d') . "'")
                        ->first();
                    if (! $data && ! $absence && ! $holidays) {
                        // libur shift
                        $list_absen = [[
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '',
                            // 'Off_Duty'   => '',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => $lemburJumlah,
                            'Lembur_4'   => $lemburLebih,
                            'Flag'       => $lemburJumlah > 0 ? 'lembur' : '',
                            'Status'     => 'libur',
                            'Keterangan' => 'Libur',
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ]];
                    } else if ($holidays && ! $absence) {
                        $list_absen = [[
                            // 'Emp No'     => $staff->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '',
                            // 'Off_Duty'   => '',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => '',
                            'Lembur_4'   => '',
                            'Flag'       => '',
                            'Status'     => 'libur',
                            'Keterangan' => $holidays->description,
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ]];
                    } else {

                        $shift     = "";
                        $deskripsi = "";
                        $idLembur  = "";

                        $get_duty_in   = $absence ? $absence->absence_logs->where('absence_category_id', 1)->first() : null;
                        $get_duty_out  = $absence ? $absence->absence_logs->where('absence_category_id', 2)->first() : null;
                        $get_clock_in  = $absence ? $absence->absence_logs->where('absence_category_id', 1)->first() : null;
                        $get_clock_out = $absence ? $absence->absence_logs->where('absence_category_id', 2)->first() : null;

                        $get_lembur_in  = $absence ? $absence->absence_logs->where('absence_category_id', 9)->first() : null;
                        $get_lembur_out = $absence ? $absence->absence_logs->where('absence_category_id', 10)->first() : null;
                        $get_dinasLuar  = $absence ? $absence->absence_logs->where('absence_category_id', 7)->first() : null;
                        $get_cuti       = $absence ? $absence->absence_logs->where('absence_category_id', 8)->first() : null;
                        $get_izin       = $absence ? $absence->absence_logs->where('absence_category_id', 13)->first() : null;
                        $get_permisi_in = $absence ? $absence->absence_logs->where('absence_category_id', 11)->first() : null;
                        $get_dispen     = $absence ? $absence->absence_logs->where('absence_category_id', 14)->first() : null;
                        $get_datang     = $absence ? $absence->absence_logs->where('absence_category_id', 1)->where('status', 0)->first() : null;
                        $get_pulang     = $absence ? $absence->absence_logs->where('absence_category_id', 2)->where('status', 0)->first() : null;
                        $get_kegiatan1  = $absence ? $absence->absence_logs->where('absence_category_id', 3)->where('status', 0)->first() : null;
                        $get_kegiatan2  = $absence ? $absence->absence_logs->where('absence_category_id', 4)->where('status', 0)->first() : null;
                        //additional

                        //get req izin
                        $get_req_izin = AbsenceRequest::whereRaw("DATE(start) = '" . $dt->format('Y-m-d') . "'")
                            ->where('category', 'permission')
                            ->where('staff_id', $stf->id)
                            ->where('status', 'approve')
                            ->first();

                        if ($get_duty_in) {
                            if ($get_duty_in->shiftGroupTimeSheets) {
                                $duty_in = $get_duty_in->shiftGroupTimeSheets->time;
                            } else {
                                $duty_in = '';
                            }
                        } else {
                            $duty_in = '';
                        }

                        if ($get_duty_out) {
                            if ($get_duty_out->shiftGroupTimeSheets) {
                                $duty_out = $get_duty_out->shiftGroupTimeSheets->time;
                            } else {
                                $duty_out = '';
                            }
                        } else {
                            $duty_out = '';
                        }

                        $clock_in  = $get_clock_in ? $get_clock_in->register : '';
                        $clock_out = $get_clock_out ? $get_clock_out->register : '';
                        $shift     = $get_clock_out ? $get_clock_out->shift_planner_id : '';
                        $kegiatan1 = $get_kegiatan1 ? $get_kegiatan1->register : '';
                        $kegiatan2 = $get_kegiatan2 ? $get_kegiatan2->register : '';

                        //if clock/check in but late more than 210 minutes => alfa
                        $status     = '';
                        $flag       = '';
                        $keterangan = '';
                        if ($clock_in != '') {
                            $keterangan   = "Masuk";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $flag         = '';
                            $status       = 'masuknormal';
                            if ($staff->work_type_id != 2) {
                                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($get_clock_in->timein))));
                                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                                if ($clock_in >= $cekDateNew) {
                                    $clock_in   = "";
                                    $keterangan = "Lambat 3.5 Jam";
                                    $status     = 'alfa';
                                }
                            } else {
                                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($get_clock_in->timein))));
                                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                                if ($clock_in >= $cekDateNew) {
                                    $clock_in   = "";
                                    $keterangan = "Lambat 2 Jam";
                                    $status     = 'alfa';
                                }
                            }
                            //get late
                            $flag = ($get_clock_in->late > 0.016667 && $status == 'masuknormal') ? 'lambat' : '';

                            // $clock_in =  "";
                        } else if ($get_dinasLuar) {
                            $flag         = '';
                            $status       = 'dinas';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Dinas Luar";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_cuti) {
                            $flag         = '';
                            $status       = 'cuti';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Cuti";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_dispen) {
                            $flag         = '';
                            $status       = 'dispen';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Dispensasi";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_izin || $get_req_izin) {
                            $flag      = '';
                            $status    = 'izin';
                            $clock_in  = "";
                            $clock_out = "";
                            $kegiatan1 = '';
                            $kegiatan2 = '';
                            if ($get_izin) {
                                if ($get_izin->absenceRequests->type == "sick") {
                                    $keterangan = "Sakit Tidak Izin Dokter";
                                    $status     = 'sakittidakizindokter';
                                } else if ($get_izin->absenceRequests->type == "sick_proof") {
                                    $keterangan = "Sakit Izin Dokter";
                                    $status     = 'sakitizindokter';
                                } else {
                                    $keterangan = "Izin";
                                }
                            } else {
                                if ($get_req_izin->type == "sick") {
                                    $keterangan = "Sakit Tidak Izin Dokter";
                                    $status     = 'sakittidakizindokter';
                                } else if ($get_req_izin->type == "sick_proof") {
                                    $keterangan = "Sakit Izin Dokter";
                                    $status     = 'sakitizindokter';
                                } else {
                                    $keterangan = "Izin";
                                }
                            }
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else {
                            $flag         = '';
                            $status       = 'alfa';
                            $keterangan   = "Alfa";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        }
                        // if lembur (lemburLebih = lembur > 4jam) (lembur jumlah = total jam lembur)
                        $clock_lembur_in  = 'null';
                        $clock_lembur_out = 'null';
                        if ($get_lembur_in) {
                            $flag .= $flag != '' ? ',lembur' : 'lembur';
                            $clock_lembur_in  = $get_lembur_in->register;
                            $clock_lembur_out = $get_lembur_out->register;
                            $idLembur         = "lembur";
                            $lemburJumlah     = round($get_lembur_out->duration);
                            $lemburLebih      = "";
                            if ($get_lembur_out->duration > 4) {
                                $lemburLebih = "Y";
                            } else if ($get_lembur_out->duration <= 4) {
                                $lemburLebih = "N";
                            }
                            if ($status == 'alfa') {
                                $status     = 'libur';
                                $keterangan = "Libur";
                            }
                        }
                        // if permisi
                        if ($get_permisi_in) {
                            //check reguler or shift
                            if ($staff->work_type_id != 2) {
                                //if reguler get hari
                                $day_id = date('w', strtotime($get_permisi_in->register)) == "0" ? '7' : date('w', strtotime($get_permisi_in->register));
                                //get clock
                                $date_now_12      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 12:00:00";
                                $date_now_11      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 11:00:00";
                                $register_to_time = strtotime(date('Y-m-d H:i:s', strtotime($get_permisi_in->register)));
                                $now_12_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_12)));
                                $now_11_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_11)));
                                //echo $day_id." - ". $register_to_time." - ". $now_12_to_time." - ". $now_11_to_time;
                                //if reguler mon - thu, check if jam mulai < 12
                                if ($day_id >= 1 && $day_id <= 4 && ($register_to_time < $now_12_to_time)) {
                                    $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                                }
                                //if reguler fri, check if jam mulai < 11
                                else if (($day_id == 5 || $day_id == 6) && ($register_to_time < $now_11_to_time)) {
                                    $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                                } else {
                                    $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                                }
                            } else {
                                $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                            }
                        }

                        //get bolong masuk
                        if (! $get_datang && $get_pulang) {
                            $flag .= $flag != '' ? ',bolongdatang' : 'bolongdatang';
                        }
                        if ($get_datang && ! $get_pulang) {
                            $flag .= $flag != '' ? ',bolongpulang' : 'bolongpulang';
                        }
                        //get bolong kegiatan
                        if (! $get_kegiatan1 && ($get_pulang || $get_datang)) {
                            $flag .= $flag != '' ? ',bolongkegiatan1' : 'bolongkegiatan1';
                        }
                        if (! $get_kegiatan2 && ($get_pulang || $get_datang)) {
                            $flag .= $flag != '' ? ',bolongkegiatan2' : 'bolongkegiatan2';
                        }

                        if ($get_clock_out && $get_clock_out->status == '1' && $status != "alfa") {
                            $deskripsi = "Cek Mungkin Lupa Absen";
                            // dd($get_clock_out);
                        }
                        if (date('H:i:s', strtotime($clock_in)) > date('H:i:s', strtotime($clock_out))) {

                            if ($clock_out) {
                                $deskripsi = "Cek Mungkin Terhitung 2 Hari (" . $clock_in . ' - ' . $clock_out . ')';
                            } else {
                                $deskripsi = "Cek Mungkin Lupa Absen";
                            }
                            // dd('tesss');
                        }

                        //set list
                        $list_absen = [[
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => $idLembur,
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => $duty_in ? date('H:i', strtotime($duty_in)) : '',
                            // 'Off_Duty'   => $duty_out ? date('H:i', strtotime($duty_out)) : '',
                            'Clock_in'   => $clock_in ? date('Y-m-d H:i:s', strtotime($clock_in)) : 'null',
                            'Clock_out'  => $clock_out ? date('Y-m-d H:i:s', strtotime($clock_out)) : 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => $shift,
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur_in'  => $clock_lembur_in,
                            'Lembur_out' => $clock_lembur_out,
                            'Lembur'     => $lemburJumlah,
                            'Lembur_4'   => $lemburLebih,
                            'Flag'       => $flag,
                            'Status'     => $status,
                            'Keterangan' => $keterangan,
                            'Kegiatan1'  => $kegiatan1 ? date('Y-m-d H:i:s', strtotime($kegiatan1)) : 'null',
                            'Kegiatan2'  => $kegiatan2 ? date('Y-m-d H:i:s', strtotime($kegiatan2)) : 'null',
                        ]];
                    }

                    $list_absen = collect($list_absen);

                    $ck1 = $list_absen->where('Date', $dt->format('Y-m-d'))->sortBy('Clock_in')->first();
                    if ($ck1) {
                        $list_absen_excel2[] = $ck1;
                        // dd('ssssss', $ck1);
                    }
                    $cek = $list_absen->where('Late', 9)->first();
                    // $cek = true;
                    if (! $cek) {
                        $cek_keterangan = $list_absen->where('Date', $dt->format('Y-m-d'))->first();
                        // $cek_keterangan = $list_absen->where('Date', $dt->format('Y-m-d'))->first();
                        if ($cek_keterangan) {

                        } else {
                            $list_absen_excel2[] = [
                                // 'Emp No'     => $stf->NIK,
                                // 'AC-No'      => '',
                                'NIK'        => $stf->NIK,
                                'Name'       => $staff->name,
                                // 'Auto-Asign' => '',
                                'Date'       => $dt->format('Y-m-d'),
                                // 'TimeTable'  => '',
                                // 'On_Duty'    => '',
                                // 'Off_Duty'   => '',
                                'Clock_in'   => 'null',
                                'Clock_out'  => 'null',
                                // 'keterangan' => $lemburJumlah,
                                // 'deskripsi'  => $lemburLebih,
                                // 'shift'      => ,
                                'Lembur'     => '',
                                'Lembur_4'   => '',
                                'Flag'       => '',
                                'Status'     => 'alfa',
                                'Keterangan' => 'Alfa',
                                'Kegiatan1'  => 'null',
                                'Kegiatan2'  => 'null',
                            ];
                        }
                    }
                }

                //set rows planner shift
                $list_absen_shift = collect($list_absen_excel2);
                $dataLemburShift  = $list_absen->where('Lembur', '>', 0);
                foreach ($dataLemburShift as $data) {
                    $cek_if_lembur_masuk = $list_absen_shift->where('Date', date('Y-m-d', strtotime($data['Date'])))->first();
                    if ($cek_if_lembur_masuk) {

                    } else {
                        $data['Clock_in']    = $data['Lembur_in'];
                        $data['Clock_out']   = $data['Lembur_out'];
                        $data['Status']      = 'libur';
                        $data['Keterangan']  = 'Libur';
                        $list_absen_excel2[] = $data;
                    }
                }

                $c = collect($list_absen_excel2);
                foreach ($c->sortBy('Date') as $data) {
                    $list_absen_excel[] = $data;
                }
            } else {
                //REGULAR
                $dateRange = CarbonPeriod::create($date_from, $date_to);
                $dates     = $dateRange->toArray();
                $i         = 0;
                //loop semua rentang hari, kalau diatas cuman loop logs saja
                foreach ($dates as $dt) {
                    //if holiday
                    $holidays = Holiday::whereDate('start', '=', $dt->format('Y-m-d'))->first();
                    $day_id   = date('w', strtotime($dt->format('Y-m-d'))) == "0" ? '7' : date('w', strtotime($dt->format('Y-m-d')));
                    $absence  = Absence::with(['absence_logs', 'absence_logs.workTypeDays', 'staffs'])
                        ->where('staff_id', $staff->id)
                        ->whereDate('absences.created_at', '=', $dt->format('Y-m-d'))
                        ->first();
                    if (in_array($day_id, $jadwal_libur) && !$absence) {
                        // libur
                        $list_absen = [[
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '',
                            // 'Off_Duty'   => '',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => $lemburJumlah,
                            'Lembur_4'   => $lemburLebih,
                            'Flag'       => $lemburJumlah > 0 ? 'lembur' : '',
                            'Status'     => 'libur',
                            'Keterangan' => $day_id === '7' ? 'Libur Minggu' : 'Libur Sabtu',
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ]];
                    } else if ($holidays && !$absence) {
                        $list_absen = [[
                            // 'Emp No'     => $staff->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => date('Y-m-d', strtotime($holidays->start)),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '',
                            // 'Off_Duty'   => '',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => '',
                            'Lembur_4'   => '',
                            'Flag'       => '',
                            'Status'     => 'libur',
                            'Keterangan' => $holidays->description,
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ]];
                    } else {
                        // untuk reguler

                        $shift     = "";
                        $deskripsi = "";
                        $idLembur  = "";

                        $get_duty_in   = $absence ? $absence->absence_logs->where('absence_category_id', 1)->first() : null;
                        $get_duty_out  = $absence ? $absence->absence_logs->where('absence_category_id', 2)->first() : null;
                        $get_clock_in  = $absence ? $absence->absence_logs->where('absence_category_id', 1)->first() : null;
                        $get_clock_out = $absence ? $absence->absence_logs->where('absence_category_id', 2)->first() : null;

                        $get_lembur_in  = $absence ? $absence->absence_logs->where('absence_category_id', 9)->first() : null;
                        $get_lembur_out = $absence ? $absence->absence_logs->where('absence_category_id', 10)->first() : null;
                        $get_dinasLuar  = $absence ? $absence->absence_logs->where('absence_category_id', 7)->first() : null;
                        $get_cuti       = $absence ? $absence->absence_logs->where('absence_category_id', 8)->first() : null;
                        $get_izin       = $absence ? $absence->absence_logs->where('absence_category_id', 13)->first() : null;
                        $get_permisi_in = $absence ? $absence->absence_logs->where('absence_category_id', 11)->first() : null;
                        $get_dispen     = $absence ? $absence->absence_logs->where('absence_category_id', 14)->first() : null;
                        $get_datang     = $absence ? $absence->absence_logs->where('absence_category_id', 1)->where('status', 0)->first() : null;
                        $get_pulang     = $absence ? $absence->absence_logs->where('absence_category_id', 2)->where('status', 0)->first() : null;
                        $get_kegiatan1  = $absence ? $absence->absence_logs->where('absence_category_id', 3)->where('status', 0)->first() : null;
                        $get_kegiatan2  = $absence ? $absence->absence_logs->where('absence_category_id', 4)->where('status', 0)->first() : null;
                        //additional

                        //get req izin
                        $get_req_izin = AbsenceRequest::whereDate('start', '=', $dt->format('Y-m-d'))
                            ->where('category', 'permission')
                            ->where('staff_id', $stf->id)
                            ->where('status', 'approve')
                            ->first();

                        //set ref time duty in/out
                        //set log time clock in/out register
                        //if not shift
                        if ($staff->work_type_id != 2) {

                            if ($get_duty_in) {
                                if ($get_duty_in->workTypeDays) {
                                    $duty_in = $get_duty_in->workTypeDays->time;
                                } else {
                                    $duty_in = '';
                                }
                            } else {
                                $duty_in = '';
                            }

                            if ($get_duty_out) {
                                if ($get_duty_out->workTypeDays) {
                                    $duty_out = $get_duty_out->workTypeDays->time;
                                } else {
                                    $duty_out = '';
                                }
                            } else {
                                $duty_out = '';
                            }

                            $clock_in  = $get_clock_in ? $get_clock_in->register : '';
                            $clock_out = $get_clock_out ? $get_clock_out->register : '';
                            $kegiatan1 = $get_kegiatan1 ? $get_kegiatan1->register : '';
                            $kegiatan2 = $get_kegiatan2 ? $get_kegiatan2->register : '';
                        }

                        //if clock/check in but late more than 210 minutes => alfa
                        $status     = '';
                        $flag       = '';
                        $keterangan = '';
                        if ($clock_in != '') {
                            $keterangan   = "Masuk";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $flag         = '';
                            $status       = 'masuknormal';
                            if ($staff->work_type_id != 2) {
                                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($get_clock_in->timein))));
                                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                                if ($clock_in >= $cekDateNew) {
                                    $clock_in   = "";
                                    $keterangan = "Lambat 3.5 Jam";
                                    $status     = 'alfa';
                                }
                            } else {
                                $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($get_clock_in->timein))));
                                // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
                                if ($clock_in >= $cekDateNew) {
                                    $clock_in   = "";
                                    $keterangan = "Lambat 2 Jam";
                                    $status     = 'alfa';
                                }
                            }
                            //get late
                            $flag = ($get_clock_in->late > 0.016667 && $status == 'masuknormal') ? 'lambat' : '';

                            // $clock_in =  "";
                        } else if ($get_dinasLuar) {
                            $flag         = '';
                            $status       = 'dinas';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Dinas Luar";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_cuti) {
                            $flag         = '';
                            $status       = 'cuti';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Cuti";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_dispen) {
                            $flag         = '';
                            $status       = 'dispen';
                            $clock_in     = "";
                            $clock_out    = "";
                            $kegiatan1    = "";
                            $kegiatan2    = "";
                            $keterangan   = "Dispensasi";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else if ($get_izin || $get_req_izin) {
                            $flag      = '';
                            $status    = 'izin';
                            $clock_in  = "";
                            $clock_out = "";
                            $kegiatan1 = '';
                            $kegiatan2 = '';
                            if ($get_izin) {
                                if ($get_izin->absenceRequests->type == "sick") {
                                    $keterangan = "Sakit Tidak Izin Dokter";
                                    $status     = 'sakittidakizindokter';
                                } else if ($get_izin->absenceRequests->type == "sick_proof") {
                                    $keterangan = "Sakit Izin Dokter";
                                    $status     = 'sakitizindokter';
                                } else {
                                    $keterangan = "Izin";
                                }
                            } else {
                                if ($get_req_izin->type == "sick") {
                                    $keterangan = "Sakit Tidak Izin Dokter";
                                    $status     = 'sakittidakizindokter';
                                } else if ($get_req_izin->type == "sick_proof") {
                                    $keterangan = "Sakit Izin Dokter";
                                    $status     = 'sakitizindokter';
                                } else {
                                    $keterangan = "Izin";
                                }
                            }
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        } else {
                            $flag         = '';
                            $status       = 'alfa';
                            $keterangan   = "Alfa";
                            $lemburLebih  = "";
                            $lemburJumlah = "";
                            $idLembur     = "";
                        }
                        // if lembur (lemburLebih = lembur > 4jam) (lembur jumlah = total jam lembur)
                        $clock_lembur_in  = 'null';
                        $clock_lembur_out = 'null';
                        if ($get_lembur_in) {
                            $flag .= $flag != '' ? ',lembur' : 'lembur';
                            $clock_lembur_in  = $get_lembur_in->register;
                            $clock_lembur_out = $get_lembur_out->register;
                            $idLembur         = "lembur";
                            $lemburJumlah     = round($get_lembur_out->duration);
                            $lemburLebih      = "";
                            if ($get_lembur_out->duration > 4) {
                                $lemburLebih = "Y";
                            } else if ($get_lembur_out->duration <= 4) {
                                $lemburLebih = "N";
                            }
                            if($status=='alfa'){
                                $keterangan   = 'Libur';
                                $status       = 'libur';
                            }
                        }
                        // if permisi
                        if ($get_permisi_in) {
                            //check reguler or shift
                            if ($staff->work_type_id != 2) {
                                //if reguler get hari
                                $day_id = date('w', strtotime($get_permisi_in->register)) == "0" ? '7' : date('w', strtotime($get_permisi_in->register));
                                //get clock
                                $date_now_12      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 12:00:00";
                                $date_now_11      = date('Y-m-d', strtotime($get_permisi_in->register)) . " 11:00:00";
                                $register_to_time = strtotime(date('Y-m-d H:i:s', strtotime($get_permisi_in->register)));
                                $now_12_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_12)));
                                $now_11_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_11)));
                                //echo $day_id." - ". $register_to_time." - ". $now_12_to_time." - ". $now_11_to_time;
                                //if reguler mon - thu, check if jam mulai < 12
                                if ($day_id >= 1 && $day_id <= 4 && ($register_to_time < $now_12_to_time)) {
                                    $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                                }
                                //if reguler fri, check if jam mulai < 11
                                else if (($day_id == 5 || $day_id == 6) && ($register_to_time < $now_11_to_time)) {
                                    $flag .= $flag != '' ? ',permisipotonggaji' : 'permisipotonggaji';
                                } else {
                                    $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                                }
                            } else {
                                $flag .= $flag != '' ? ',permisitidakpotonggaji' : 'permisitidakpotonggaji';
                            }
                        }

                        //get bolong masuk
                        if (! $get_datang && $get_pulang) {
                            $flag .= $flag != '' ? ',bolongdatang' : 'bolongdatang';
                        }
                        if ($get_datang && ! $get_pulang) {
                            $flag .= $flag != '' ? ',bolongpulang' : 'bolongpulang';
                        }
                        //get bolong kegiatan
                        if (! $get_kegiatan1 && ($get_pulang || $get_datang)) {
                            $flag .= $flag != '' ? ',bolongkegiatan1' : 'bolongkegiatan1';
                        }
                        if (! $get_kegiatan2 && ($get_pulang || $get_datang)) {
                            $flag .= $flag != '' ? ',bolongkegiatan2' : 'bolongkegiatan2';
                        }

                        if ($get_clock_out && $get_clock_out->status == '1' && $status != "alfa") {
                            $deskripsi = "Cek Mungkin Lupa Absen";
                            // dd($get_clock_out);
                        }

                        if ($get_clock_out && $get_clock_out->status == '1' && $status != "alfa") {
                            $deskripsi = "Cek Mungkin Lupa Absen";
                            // dd($get_clock_out);
                        }
                        if (date('H:i:s', strtotime($clock_in)) > date('H:i:s', strtotime($clock_out))) {

                            if ($clock_out) {
                                $deskripsi = "Cek Mungkin Terhitung 2 Hari (" . $clock_in . ' - ' . $clock_out . ')';
                            } else {
                                $deskripsi = "Cek Mungkin Lupa Absen";
                            }
                            // dd('tesss');
                        }

                        //set list
                        $list_absen = [[
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => $idLembur,
                            'Date'       => date('Y-m-d', strtotime($dt)),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => $duty_in ? date('H:i', strtotime($duty_in)) : '',
                            // 'Off_Duty'   => $duty_out ? date('H:i', strtotime($duty_out)) : '',
                            'Clock_in'   => $clock_in ? date('Y-m-d H:i:s', strtotime($clock_in)) : 'null',
                            'Clock_out'  => $clock_out ? date('Y-m-d H:i:s', strtotime($clock_out)) : 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => $shift,
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur_in'  => $clock_lembur_in,
                            'Lembur_out' => $clock_lembur_out,
                            'Lembur'     => $lemburJumlah,
                            'Lembur_4'   => $lemburLebih,
                            'Flag'       => $flag,
                            'Status'     => $status,
                            'Keterangan' => $keterangan,
                            'Kegiatan1'  => $kegiatan1 ? date('Y-m-d H:i:s', strtotime($kegiatan1)) : 'null',
                            'Kegiatan2'  => $kegiatan2 ? date('Y-m-d H:i:s', strtotime($kegiatan2)) : 'null',
                        ]];
                    }

                    $list_absen = collect($list_absen);

                    $day_id = date('w', strtotime($dt->format('Y-m-d'))) == "0" ? '7' : date('w', strtotime($dt->format('Y-m-d')));

                    $list = $list_absen->where('Date', date('Y-m-d', strtotime($dt)))->sortBy('Clock_in')->first();

                    if ($list != null) {
                        $cek_masuk = $list_absen->where('Date', $dt->format('Y-m-d'))->where('status', 'masuknormal')->first();

                        $cek_lembur = $list_absen->where('Date', $dt->format('Y-m-d'))->where('flag', 'lembur')->first();
                        if ($cek_masuk && $cek_lembur) {
                            $deskripsi = "";
                            if ($cek_lembur['deskripsi'] != "") {
                                $deskripsi = $cek_lembur['deskripsi'];
                            } else {
                                $deskripsi = $cek_masuk['deskripsi'];
                            }
                            $list_absen_excel[] = [
                                // 'Emp No'     => $cek_masuk['No'],
                                // 'AC-No'      => '',
                                'NIK'        => $stf->NIK,
                                'Name'       => $cek_masuk['Name'],
                                // 'Auto-Asign' => '',
                                'Date'       => $cek_masuk['Date'],
                                // 'TimeTable'  => '',
                                // 'On_Duty'    => $cek_masuk['On_Duty'],
                                // 'Off_Duty'   => $cek_masuk['Off_Duty'],
                                'Clock_in'   => $cek_masuk['Clock_in'],
                                'Clock_out'  => $cek_masuk['Clock_out'],
                                // 'Clock_in' => $cek_masuk['Clock_in'] && $cek_masuk['Clock_in'] = "" ? $cek_masuk['Clock_in'] : $deskripsi,
                                // 'Clock_out' => $cek_masuk['Clock_out'] && $cek_masuk['Clock_out'] = "" ? $cek_masuk['Clock_out'] : $deskripsi,
                                // 'keterangan' => 'Masuk dan Lembur',
                                // 'Normal'     => '',
                                // 'Real time'  => '',
                                // 'Late'       => '',
                                // 'Early'      => '',
                                // 'Absent'     => '',
                                // 'OT Time'    => '',
                                // 'Work Time'  => '',
                                // 'Exception'  => '',
                                // 'Must C/In'  => '',
                                // 'Must C/Out' => '',
                                // 'Department' => '',
                                // 'NDays'      => '',
                                // 'WeekEnd'    => '',
                                // 'Holiday'    => '',
                                // 'ATT_Time'   => '',
                                // 'NDays_OT'   => '',
                                // 'WeekEnd_OT' => '',
                                // 'Holiday_OT' => '',
                                'Lembur'     => $lemburJumlah,
                                'Lembur_4'   => $lemburLebih,
                                'Flag'       => $cek_masuk['Flag'],
                                'Status'     => $cek_masuk['Status'],
                                'Keterangan' => $cek_masuk['Keterangan'],
                                'Kegiatan1'  => $cek_masuk['Kegiatan1'],
                                'Kegiatan2'  => $cek_masuk['Kegiatan2'],
                            ];
                        } else if ($cek_lembur) {
                            // untuk absen lembur
                            $list_absen_excel[] = $cek_lembur;
                        } else if ($cek_lembur) {
                            // untuk absen masuk
                            $list_absen_excel[] = $cek_masuk;
                        } else {
                            $list_absen_excel[] = $list;
                        }
                    } else {
                        // tanpa keterangan
                        $list_absen_excel[] = [
                            // 'Emp No'     => $stf->NIK,
                            // 'AC-No'      => '',
                            'NIK'        => $stf->NIK,
                            'Name'       => $staff->name,
                            // 'Auto-Asign' => '',
                            'Date'       => $dt->format('Y-m-d'),
                            // 'TimeTable'  => '',
                            // 'On_Duty'    => '07:30',
                            // 'Off_Duty'   => '15:30',
                            'Clock_in'   => 'null',
                            'Clock_out'  => 'null',
                            // 'Normal'     => '',
                            // 'Real time'  => '',
                            // 'Late'       => '',
                            // 'Early'      => '',
                            // 'Absent'     => '',
                            // 'OT Time'    => '',
                            // 'Work Time'  => '',
                            // 'Exception'  => '',
                            // 'Must C/In'  => '',
                            // 'Must C/Out' => '',
                            // 'Department' => '',
                            // 'NDays'      => '',
                            // 'WeekEnd'    => '',
                            // 'Holiday'    => '',
                            // 'ATT_Time'   => '',
                            // 'NDays_OT'   => '',
                            // 'WeekEnd_OT' => '',
                            // 'Holiday_OT' => '',
                            'Lembur'     => '',
                            'Lembur_4'   => '',
                            'Flag'       => '',
                            'Status'     => 'alfa',
                            'Keterangan' => 'Alfa',
                            'Kegiatan1'  => 'null',
                            'Kegiatan2'  => 'null',
                        ];
                    }
                }
            }
        }

        return response()->json([
            'message' => 'success',
            'data'    => $list_absen_excel,
        ]);
    }

    public function reportAllExcelBak(Request $request)
    {
        ini_set("memory_limit", -1);
        set_time_limit(0);

        $date_start   = $request->from;
        $date_end     = $request->to;
        $alpha        = 0;
        $absen_bolong = 0;

        $awal_cuti  = strtotime($date_start);
        $akhir_cuti = strtotime($date_end);

        $dapertement_id = '';
        if ($request->kode_bagian) {
            $dapertement_row = Dapertement::select('id')->where('code', $request->kode_bagian)->first();
            $dapertement_id  = $dapertement_row->id;
        }

        $staffs = Staff::FilterWorkUnit($request->work_unit_id)
            ->FilterNik($request->NIK)
            ->FilterDapertement($dapertement_id)
            ->FilterId($request->staff_id)
            ->FilterJob($request->job_id)
            ->with('dapertement', 'subdapertement')
            ->orderBy('NIK', 'ASC')
            ->where('_status', 'active')
            ->get();
        $data = [];

        foreach ($staffs as $staff) {

            $report = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 ,1,NULL)) jumlah_masuk')
                ->selectRaw('count(IF(absence_category_id = 2 AND status = 0 ,1,NULL)) jumlah_pulang')
                ->selectRaw('count(IF(absence_category_id = 3 AND status = 0 ,1,NULL)) jumlah_k1')
                ->selectRaw('count(IF(absence_category_id = 4 AND status = 0 ,1,NULL)) jumlah_k2')
                ->selectRaw('count(IF(absence_category_id = 5 AND status = 0 ,1,NULL)) jumlah_dinasDalam')
                ->selectRaw('count(IF(absence_category_id = 7 AND status = 0 ,1,NULL)) jumlah_dinasLuar')
                ->selectRaw('count(IF(absence_category_id = 8 AND status = 0 ,1,NULL)) jumlah_cuti')
                ->selectRaw('SUM(IF(absence_category_id = 10 AND duration > 0 AND status = 0 ,duration,0)) jumlah_lembur')
                ->selectRaw('count(IF(absence_category_id = 10 AND duration >= 4 AND status = 0 ,1,NULL)) jumlah_lemburlebih')
                ->selectRaw('count(IF(absence_category_id = 11 AND status = 0 ,1,NULL)) jumlah_permisi')
            // ->selectRaw('count(IF(absence_category_id = 13 AND status = 0 ,1,NULL)) jumlah_izin')
                ->selectRaw('count(IF(absence_category_id = 14 AND status = 0 ,1,NULL)) jumlah_dispen')
                ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 AND late > 0.016667 ,1,NULL)) jumlah_lambat')
                ->where('staff_id', $staff->id)
            //->whereBetween('absences.created_at', [$date_start, $date_end])
                ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
                ->first();

            $sakit = AbsenceRequest::selectRaw('count(id) as jumlah_sakit')
                ->where('category', 'permission')
                ->where('type', 'sick')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            $izin = AbsenceRequest::selectRaw('count(id) as jumlah_izin')
                ->where('category', 'permission')
                ->where('type', 'other')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            //get dinas luar
            $dinasLuarRows = AbsenceRequest::selectRaw('start,end')
                ->where('category', 'duty')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->get();
            $jumlah_dinasLuar = 0;
            foreach ($dinasLuarRows as $dinasLuarRow) {
                $diff = date_diff(date_create($dinasLuarRow->start), date_create($dinasLuarRow->end));
                $diff = $diff->format("%R%a");
                $diff = (int) $diff + 1;
                $jumlah_dinasLuar += $diff;
            }
            //permisi
            $permisi = AbsenceRequest::selectRaw('*')
                ->where('category', 'excuse')
            //->where('type', 'out')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();

            // if permisi
            $permisi_potong_gaji       = 0;
            $permisi_tidak_potong_gaji = 0;
            if ($permisi) {
                //check reguler or shift
                if ($staff->work_type_id != 2) {
                    //if reguler get hari
                    $day_id = date('w', strtotime($permisi->start)) == "0" ? '7' : date('w', strtotime($permisi->start));
                    //get clock
                    $date_now_12      = date('Y-m-d', strtotime($permisi->start)) . " 12:00:00";
                    $date_now_11      = date('Y-m-d', strtotime($permisi->start)) . " 11:00:00";
                    $register_to_time = strtotime(date('Y-m-d H:i:s', strtotime($permisi->start)));
                    $now_12_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_12)));
                    $now_11_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_11)));
                    //echo $day_id." - ". $register_to_time." - ". $now_12_to_time." - ". $now_11_to_time;
                    //if reguler mon - thu, check if jam mulai < 12
                    if ($day_id >= 1 && $day_id <= 4 && ($register_to_time < $now_12_to_time)) {
                        $permisi_potong_gaji++;
                    }
                    //if reguler fri, check if jam mulai < 11
                    else if (($day_id == 5 || $day_id == 6) && ($register_to_time < $now_11_to_time)) {
                        $permisi_potong_gaji++;
                    } else {
                        $permisi_tidak_potong_gaji++;
                    }
                } else {
                    $permisi_tidak_potong_gaji++;
                }
            }

            $absen_bolong_datang = 0;
            $absen_bolong_pulang = 0;
            //get bolong masuk
            if ($report->jumlah_masuk < $report->jumlah_pulang) {
                $absen_bolong_datang++;
            }
            if ($report->jumlah_masuk > $report->jumlah_pulang) {
                $absen_bolong_pulang++;
            }
            // $report_masuk        = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            //     ->selectRaw('absence_logs.*')
            //     ->where('absence_category_id', 1)
            //     ->where('status', 0)
            //     ->where('staff_id', $staff->id)
            // //->whereBetween('absences.created_at', [$date_start, $date_end])
            //     ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            //     ->get();

            // foreach ($report_masuk as $item) {
            //     //get absen pulang
            //     $log_out = AbsenceLog::selectRaw('id')
            //         ->where('absence_id', $item->absence_id)
            //         ->where('absence_category_id', 2)
            //         ->first();
            //     if (! $log_out) {
            //         $absen_bolong_pulang += 1;
            //     }
            //     // if ($staff->work_type_id === 1) {
            //     //     $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($item->timein))));
            //     //     // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
            //     //     if ($item->register >= $cekDateNew) {
            //     //         $absen_bolong += 1;
            //     //     }
            //     // } else {
            //     //     $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($item->timein))));
            //     //     // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
            //     //     if ($item->register >= $cekDateNew) {
            //     //         $absen_bolong += 1;
            //     //     }
            //     // }
            // }

            // $report_pulang = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            //     ->selectRaw('absence_logs.*')
            //     ->where('absence_category_id', 2)
            //     ->where('status', 0)
            //     ->where('staff_id', $staff->id)
            // //->whereBetween('absences.created_at', [$date_start, $date_end])
            //     ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            //     ->get();

            // foreach ($report_pulang as $item) {
            //     //get absen masuk
            //     $log_in = AbsenceLog::selectRaw('id')
            //         ->where('absence_id', $item->absence_id)
            //         ->where('absence_category_id', 1)
            //         ->first();
            //     if (! $log_in) {
            //         $absen_bolong_masuk += 1;
            //     }
            // }

            $kegiatan = '';

            if ($staff->work_type_id === 1) {

                // tanggalnya diubah formatnya ke Y-m-d

                $hariKerja   = [];
                $sabtuminggu = [];

                for ($i = $awal_cuti; $i <= $akhir_cuti; $i += (60 * 60 * 24)) {
                    if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                        $hariKerja[] = $i;
                    } else {
                        $sabtuminggu[] = $i;
                    }
                }
                $jumlah_kerja = count($hariKerja);

                // mencari jumlah hari end

                //$holiday = Holiday::selectRaw('count(id) jumlah_libur')->whereBetween('start', [$date_start, $date_end])->where('status', null)->first();
                $holiday = Holiday::selectRaw('count(id) jumlah_libur')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->first();

                //holiday in weekend
                $holiday_weekend = 0;
                //$holiday_rows = Holiday::selectRaw('start')->whereBetween('start', [$date_start, $date_end])->where('status', null)->get();
                $holiday_rows = Holiday::selectRaw('start')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->get();
                foreach ($holiday_rows as $holiday_row) {
                    $dayOfWeek = date('w', strtotime($holiday_row->start));
                    if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                        $holiday_weekend++;
                    }
                }

                $jumlah_libur = $holiday->jumlah_libur - $holiday_weekend;
                $jumlah_kerja = $jumlah_kerja - $jumlah_libur;
                $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit;
                $kegiatan     = "Kegiatan";
                if ($alpha <= 0) {
                    $alpha = 0;
                }
            } else {
                //$jadwal = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $staff->id)->whereBetween('start', [$date_start, $date_end])->first();
                $jadwal       = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $staff->id)->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->first();
                $jumlah_kerja = $jadwal->jumlah_kerja;
                $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit;
                $jumlah_libur = 0;
                $kegiatan     = "Kontrol";
                if ($alpha <= 0) {
                    $alpha = 0;
                }
            }

            $jumlah_cuti = $report->jumlah_cuti;
            if ($report->jumlah_cuti > $jumlah_kerja) {
                $jumlah_cuti = $jumlah_kerja;
            }

            $absenceadjs = AbsenceAdj::whereDate('session_start', '=', $request->from)->whereDate('session_end', '=', $request->to)->where('staff_id', '=', $staff->id)->first();

            $data[] = [
                "NIK"                       => $staff->NIK,
                "Nama"                      => $staff->id . " " . $staff->name,
                "Bagian_Unit"               => $staff->dapertement->name,
                "Tipe_Kerja"                => $staff->work_type_id === 1 ? "Reguler" : "Shift",
                "Jumlah_Masuk"              => ($report->jumlah_masuk + $report->jumlah_dispen) + ($absenceadjs ? $absenceadjs->Jumlah_Masuk : 0),
                "Jumlah_" . $kegiatan . "1" => $report->jumlah_k1 + ($absenceadjs ? $absenceadjs->Jumlah_Kegiatan1 : 0),
                "Jumlah_" . $kegiatan . "2" => $report->jumlah_k2 + ($absenceadjs ? $absenceadjs->Jumlah_Kegiatan2 : 0),
                "Jumlah_Dinas_Dalam"        => $report->jumlah_dinasDalam + ($absenceadjs ? $absenceadjs->Jumlah_Dinas_Dalam : 0),
                "Jumlah_Dinas_Luar"         => $jumlah_dinasLuar + ($absenceadjs ? $absenceadjs->Jumlah_Dinas_Luar : 0),
                "Jumlah_Cuti"               => $jumlah_cuti + ($absenceadjs ? $absenceadjs->Jumlah_Cuti : 0),
                "Jumlah_Lembur"             => $report->jumlah_lembur + ($absenceadjs ? $absenceadjs->Jumlah_Lembur : 0),
                "Jumlah_Lembur4"            => $report->jumlah_lemburlebih + ($absenceadjs ? $absenceadjs->Jumlah_Lembur4 : 0),
                "Jumlah_Permisi"            => $report->jumlah_permisi + ($absenceadjs ? $absenceadjs->Jumlah_Permisi : 0),
                "Jumlah_Izin"               => $izin->jumlah_izin + ($absenceadjs ? $absenceadjs->Jumlah_Izin : 0),
                "Jumlah_Sakit"              => $sakit->jumlah_sakit + ($absenceadjs ? $absenceadjs->Jumlah_Sakit : 0),
                "Jumlah_Dispen"             => $report->jumlah_dispen + ($absenceadjs ? $absenceadjs->Jumlah_Dispen : 0),
                "Jumlah_Alfa"               => ($alpha > 0 ? $alpha : 0) + ($absenceadjs ? $absenceadjs->Jumlah_Alfa : 0),
                "Jumlah_Tidak_Masuk"        => ($jumlah_cuti + $izin->jumlah_izin + $sakit->jumlah_sakit + $alpha) + ($absenceadjs ? $absenceadjs->Jumlah_Tidak_Masuk : 0),
                "Jumlah_Hari_Kerja"         => $jumlah_kerja + ($absenceadjs ? $absenceadjs->Jumlah_Hari_Kerja : 0),
                "Jumlah_Hari_Libur"         => $jumlah_libur + ($absenceadjs ? $absenceadjs->Jumlah_Hari_Libur : 0),
                "Absen_Bolong_Datang"       => $absen_bolong_datang + ($absenceadjs ? $absenceadjs->Absen_Bolong_Datang : 0),
                "Absen_Bolong_Pulang"       => $absen_bolong_pulang + ($absenceadjs ? $absenceadjs->Absen_Bolong_Pulang : 0), //
                "Absen_Lambat"              => $report->jumlah_lambat + ($absenceadjs ? $absenceadjs->Absen_Lambat : 0),
                "Permisi_Potong_Gaji"       => $permisi_potong_gaji + ($absenceadjs ? $absenceadjs->Permisi_Potong_Gaji : 0),
                "Permisi_Tidak_Potong_Gaji" => $permisi_tidak_potong_gaji + ($absenceadjs ? $absenceadjs->Permisi_Tidak_Potong_Gaji : 0),
            ];
        }

        return response()->json([
            'message' => 'success',
            'data'    => $data,
        ]);
    }

    public function reportAllExcel(Request $request)
    {
        ini_set("memory_limit", -1);
        set_time_limit(0);

        $date_start   = $request->from;
        $date_end     = $request->to;
        $alpha        = 0;
        $absen_bolong = 0;

        $awal_cuti  = strtotime($date_start);
        $akhir_cuti = strtotime($date_end);

        $dapertement_id = '';
        if ($request->kode_bagian) {
            $dapertement_row = Dapertement::select('id')->where('code', $request->kode_bagian)->first();
            $dapertement_id  = $dapertement_row->id;
        }

        $staffs = Staff::FilterWorkUnit($request->work_unit_id)
            ->FilterNik($request->NIK)
            ->FilterDapertement($dapertement_id)
            ->FilterId($request->staff_id)
            ->FilterJob($request->job_id)
            ->with('dapertement', 'subdapertement')
            ->orderBy('NIK', 'ASC')
            ->where('_status', 'active')
            ->get();
        $data = [];

        foreach ($staffs as $staff) {

            $report = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 ,1,NULL)) jumlah_masuk')
                ->selectRaw('count(IF(absence_category_id = 2 AND status = 0 ,1,NULL)) jumlah_pulang')
                ->selectRaw('count(IF(absence_category_id = 3 AND image !="" AND status = 0 ,1,NULL)) jumlah_k1')
                ->selectRaw('count(IF(absence_category_id = 4 AND image !="" AND status = 0 ,1,NULL)) jumlah_k2')
                ->selectRaw('count(IF(absence_category_id = 5 AND status = 0 ,1,NULL)) jumlah_dinasDalam')
                ->selectRaw('count(IF(absence_category_id = 7 AND status = 0 ,1,NULL)) jumlah_dinasLuar')
                ->selectRaw('count(IF(absence_category_id = 8 AND status = 0 ,1,NULL)) jumlah_cuti')
                ->selectRaw('SUM(IF(absence_category_id = 10 AND duration > 0 AND status = 0 ,duration,0)) jumlah_lembur')
                ->selectRaw('count(IF(absence_category_id = 10 AND duration >= 4 AND status = 0 ,1,NULL)) jumlah_lemburlebih')
                ->selectRaw('count(IF(absence_category_id = 11 AND status = 0 ,1,NULL)) jumlah_permisi')
            // ->selectRaw('count(IF(absence_category_id = 13 AND status = 0 ,1,NULL)) jumlah_izin')
                ->selectRaw('count(IF(absence_category_id = 14 AND status = 0 ,1,NULL)) jumlah_dispen')
                ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 AND late > 0.016667 ,1,NULL)) jumlah_lambat')
                ->where('staff_id', $staff->id)
            //->whereBetween('absences.created_at', [$date_start, $date_end])
                ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
                ->first();

            $sakit = AbsenceRequest::selectRaw('count(id) as jumlah_sakit')
                ->where('category', 'permission')
                ->where('type', 'sick')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            $sakitizindokter = AbsenceRequest::selectRaw('count(id) as jumlah_sakit')
                ->where('category', 'permission')
                ->where('type', 'sick_proof')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            $izin = AbsenceRequest::selectRaw('count(id) as jumlah_izin')
                ->where('category', 'permission')
                ->where('type', 'other')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            //get dinas luar
            $dinasLuarRows = AbsenceRequest::selectRaw('start,end')
                ->where('category', 'duty')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->get();
            $jumlah_dinasLuar = 0;
            foreach ($dinasLuarRows as $dinasLuarRow) {
                $diff = date_diff(date_create($dinasLuarRow->start), date_create($dinasLuarRow->end));
                $diff = $diff->format("%R%a");
                $diff = (int) $diff + 1;
                $jumlah_dinasLuar += $diff;
            }
            //permisi
            $permisi = AbsenceRequest::selectRaw('*')
                ->where('category', 'excuse')
            //->where('type', 'out')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();

            // if permisi
            $permisi_potong_gaji       = 0;
            $permisi_tidak_potong_gaji = 0;
            if ($permisi) {
                //check reguler or shift
                if ($staff->work_type_id != 2) {
                    //if reguler get hari
                    $day_id = date('w', strtotime($permisi->start)) == "0" ? '7' : date('w', strtotime($permisi->start));
                    //get clock
                    $date_now_12      = date('Y-m-d', strtotime($permisi->start)) . " 12:00:00";
                    $date_now_11      = date('Y-m-d', strtotime($permisi->start)) . " 11:00:00";
                    $register_to_time = strtotime(date('Y-m-d H:i:s', strtotime($permisi->start)));
                    $now_12_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_12)));
                    $now_11_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_11)));
                    //echo $day_id." - ". $register_to_time." - ". $now_12_to_time." - ". $now_11_to_time;
                    //if reguler mon - thu, check if jam mulai < 12
                    if ($day_id >= 1 && $day_id <= 4 && ($register_to_time < $now_12_to_time)) {
                        $permisi_potong_gaji++;
                    }
                    //if reguler fri, check if jam mulai < 11
                    else if (($day_id == 5 || $day_id == 6) && ($register_to_time < $now_11_to_time)) {
                        $permisi_potong_gaji++;
                    } else {
                        $permisi_tidak_potong_gaji++;
                    }
                } else {
                    $permisi_tidak_potong_gaji++;
                }
            }

            $absen_bolong_datang    = 0;
            $absen_bolong_pulang    = 0;
            $absen_bolong_kegiatan1 = 0;
            $absen_bolong_kegiatan2 = 0;
            //get bolong masuk
            if ($report->jumlah_masuk < $report->jumlah_pulang) {
                $absen_bolong_datang = $report->jumlah_pulang - $report->jumlah_masuk;
            }
            if ($report->jumlah_masuk > $report->jumlah_pulang) {
                $absen_bolong_pulang = $report->jumlah_masuk - $report->jumlah_pulang;
            }
            //get bolong kegiatan
            if ($report->jumlah_k1 < ($report->jumlah_masuk + $absen_bolong_datang)) {
                $absen_bolong_kegiatan1 = ($report->jumlah_masuk + $absen_bolong_datang) - $report->jumlah_k1;
            }
            if ($report->jumlah_k2 < ($report->jumlah_masuk + $absen_bolong_datang)) {
                $absen_bolong_kegiatan2 = ($report->jumlah_masuk + $absen_bolong_datang) - $report->jumlah_k2;
            }
            // $report_masuk        = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            //     ->selectRaw('absence_logs.*')
            //     ->where('absence_category_id', 1)
            //     ->where('status', 0)
            //     ->where('staff_id', $staff->id)
            // //->whereBetween('absences.created_at', [$date_start, $date_end])
            //     ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            //     ->get();

            // foreach ($report_masuk as $item) {
            //     //get absen pulang
            //     $log_out = AbsenceLog::selectRaw('id')
            //         ->where('absence_id', $item->absence_id)
            //         ->where('absence_category_id', 2)
            //         ->first();
            //     if (! $log_out) {
            //         $absen_bolong_pulang += 1;
            //     }
            //     // if ($staff->work_type_id === 1) {
            //     //     $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($item->timein))));
            //     //     // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
            //     //     if ($item->register >= $cekDateNew) {
            //     //         $absen_bolong += 1;
            //     //     }
            //     // } else {
            //     //     $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($item->timein))));
            //     //     // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
            //     //     if ($item->register >= $cekDateNew) {
            //     //         $absen_bolong += 1;
            //     //     }
            //     // }
            // }

            // $report_pulang = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            //     ->selectRaw('absence_logs.*')
            //     ->where('absence_category_id', 2)
            //     ->where('status', 0)
            //     ->where('staff_id', $staff->id)
            // //->whereBetween('absences.created_at', [$date_start, $date_end])
            //     ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            //     ->get();

            // foreach ($report_pulang as $item) {
            //     //get absen masuk
            //     $log_in = AbsenceLog::selectRaw('id')
            //         ->where('absence_id', $item->absence_id)
            //         ->where('absence_category_id', 1)
            //         ->first();
            //     if (! $log_in) {
            //         $absen_bolong_masuk += 1;
            //     }
            // }

            $kegiatan = '';

            if ($staff->work_type_id === 1) {

                // tanggalnya diubah formatnya ke Y-m-d

                $hariKerja   = [];
                $sabtuminggu = [];

                for ($i = $awal_cuti; $i <= $akhir_cuti; $i += (60 * 60 * 24)) {
                    if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                        $hariKerja[] = $i;
                    } else {
                        $sabtuminggu[] = $i;
                    }
                }
                $jumlah_kerja = count($hariKerja);

                // mencari jumlah hari end

                //$holiday = Holiday::selectRaw('count(id) jumlah_libur')->whereBetween('start', [$date_start, $date_end])->where('status', null)->first();
                $holiday = Holiday::selectRaw('count(id) jumlah_libur')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->first();

                //holiday in weekend
                $holiday_weekend = 0;
                //$holiday_rows = Holiday::selectRaw('start')->whereBetween('start', [$date_start, $date_end])->where('status', null)->get();
                $holiday_rows = Holiday::selectRaw('start')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->get();
                foreach ($holiday_rows as $holiday_row) {
                    $dayOfWeek = date('w', strtotime($holiday_row->start));
                    if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                        $holiday_weekend++;
                    }
                }

                $jumlah_libur = $holiday->jumlah_libur - $holiday_weekend;
                $jumlah_kerja = $jumlah_kerja - $jumlah_libur;
                $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit - $sakitizindokter->jumlah_sakit;
                $kegiatan     = "Kegiatan";
                if ($alpha <= 0) {
                    $alpha = 0;
                }
            } else {
                //$jadwal = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $staff->id)->whereBetween('start', [$date_start, $date_end])->first();
                $jadwal       = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $staff->id)->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->first();
                $jumlah_kerja = $jadwal->jumlah_kerja;
                $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit - $sakitizindokter->jumlah_sakit;
                $jumlah_libur = 0;
                $kegiatan     = "Kontrol";
                if ($alpha <= 0) {
                    $alpha = 0;
                }
            }

            $jumlah_cuti = $report->jumlah_cuti;
            if ($report->jumlah_cuti > $jumlah_kerja) {
                $jumlah_cuti = $jumlah_kerja;
            }

            $absenceadjs = AbsenceAdj::whereDate('session_start', '=', $request->from)->whereDate('session_end', '=', $request->to)->where('staff_id', '=', $staff->id)->first();

            $data[] = [
                "NIK"                             => $staff->NIK,
                "Nama"                            => $staff->id . " " . $staff->name,
                "Bagian_Unit"                     => $staff->dapertement->name,
                "Tipe_Kerja"                      => $staff->work_type_id === 1 ? "Reguler" : "Shift",
                "Jumlah_Masuk"                    => ($report->jumlah_masuk + $report->jumlah_dispen + $jumlah_dinasLuar) + ($absenceadjs ? $absenceadjs->Jumlah_Masuk : 0),
                "Jumlah_" . $kegiatan . "1"       => $report->jumlah_k1 + ($absenceadjs ? $absenceadjs->Jumlah_Kegiatan1 : 0),
                "Jumlah_" . $kegiatan . "2"       => $report->jumlah_k2 + ($absenceadjs ? $absenceadjs->Jumlah_Kegiatan2 : 0),
                "Jumlah_Dinas_Dalam"              => $report->jumlah_dinasDalam + ($absenceadjs ? $absenceadjs->Jumlah_Dinas_Dalam : 0),
                "Jumlah_Dinas_Luar"               => $jumlah_dinasLuar + ($absenceadjs ? $absenceadjs->Jumlah_Dinas_Luar : 0),
                "Jumlah_Cuti"                     => $jumlah_cuti + ($absenceadjs ? $absenceadjs->Jumlah_Cuti : 0),
                "Jumlah_Lembur"                   => $report->jumlah_lembur + ($absenceadjs ? $absenceadjs->Jumlah_Lembur : 0),
                "Jumlah_Lembur4"                  => $report->jumlah_lemburlebih + ($absenceadjs ? $absenceadjs->Jumlah_Lembur4 : 0),
                "Jumlah_Permisi"                  => $report->jumlah_permisi + ($absenceadjs ? $absenceadjs->Jumlah_Permisi : 0),
                "Jumlah_Izin"                     => $izin->jumlah_izin + ($absenceadjs ? $absenceadjs->Jumlah_Izin : 0),
                "Jumlah_Sakit_Tidak_Izin_Dokter"  => $sakit->jumlah_sakit + ($absenceadjs ? $absenceadjs->Jumlah_Sakit : 0),
                "Jumlah_Sakit_Izin_Dokter"        => $sakitizindokter->jumlah_sakit + ($absenceadjs ? $absenceadjs->Jumlah_Sakit_Izin_Dokter : 0),
                "Jumlah_Dispen"                   => $report->jumlah_dispen + ($absenceadjs ? $absenceadjs->Jumlah_Dispen : 0),
                "Jumlah_Alfa"                     => ($alpha > 0 ? $alpha : 0) + ($absenceadjs ? $absenceadjs->Jumlah_Alfa : 0),
                "Jumlah_Tidak_Masuk"              => ($jumlah_cuti + $izin->jumlah_izin + $sakit->jumlah_sakit + $sakitizindokter->jumlah_sakit + $alpha) + ($absenceadjs ? $absenceadjs->Jumlah_Tidak_Masuk : 0),
                "Jumlah_Hari_Kerja"               => $jumlah_kerja + ($absenceadjs ? $absenceadjs->Jumlah_Hari_Kerja : 0),
                "Jumlah_Hari_Libur"               => $jumlah_libur + ($absenceadjs ? $absenceadjs->Jumlah_Hari_Libur : 0),
                "Absen_Bolong_Datang"             => $absen_bolong_datang + ($absenceadjs ? $absenceadjs->Absen_Bolong_Datang : 0),
                "Absen_Bolong_Pulang"             => $absen_bolong_pulang + ($absenceadjs ? $absenceadjs->Absen_Bolong_Pulang : 0), //
                "Absen_Lambat"                    => $report->jumlah_lambat + ($absenceadjs ? $absenceadjs->Absen_Lambat : 0),
                "Permisi_Potong_Gaji"             => $permisi_potong_gaji + ($absenceadjs ? $absenceadjs->Permisi_Potong_Gaji : 0),
                "Permisi_Tidak_Potong_Gaji"       => $permisi_tidak_potong_gaji + ($absenceadjs ? $absenceadjs->Permisi_Tidak_Potong_Gaji : 0),
                "Absen_Bolong_" . $kegiatan . "1" => $absen_bolong_kegiatan1 + ($absenceadjs ? $absenceadjs->Absen_Bolong_Kegiatan1 : 0),
                "Absen_Bolong_" . $kegiatan . "2" => $absen_bolong_kegiatan2 + ($absenceadjs ? $absenceadjs->Absen_Bolong_Kegiatan2 : 0),
            ];
        }

        return response()->json([
            'message' => 'success',
            'data'    => $data,
        ]);
    }

    public function reportAllExcelTest(Request $request)
    {
        ini_set("memory_limit", -1);
        set_time_limit(0);

        $date_start   = $request->from;
        $date_end     = $request->to;
        $alpha        = 0;
        $absen_bolong = 0;

        $awal_cuti  = strtotime($date_start);
        $akhir_cuti = strtotime($date_end);

        $dapertement_id = '';
        if ($request->kode_bagian) {
            $dapertement_row = Dapertement::select('id')->where('code', $request->kode_bagian)->first();
            $dapertement_id  = $dapertement_row->id;
        }

        $staffs = Staff::FilterWorkUnit($request->work_unit_id)
            ->FilterNik($request->NIK)
            ->FilterDapertement($dapertement_id)
            ->FilterId($request->staff_id)
            ->FilterJob($request->job_id)
            ->with('dapertement', 'subdapertement')
            ->orderBy('NIK', 'ASC')
            ->where('_status', 'active')
            ->get();
        $data = [];

        foreach ($staffs as $staff) {

            $report = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 ,1,NULL)) jumlah_masuk')
                ->selectRaw('count(IF(absence_category_id = 2 AND status = 0 ,1,NULL)) jumlah_pulang')
                ->selectRaw('count(IF(absence_category_id = 3 AND status = 0 ,1,NULL)) jumlah_k1')
                ->selectRaw('count(IF(absence_category_id = 4 AND status = 0 ,1,NULL)) jumlah_k2')
                ->selectRaw('count(IF(absence_category_id = 5 AND status = 0 ,1,NULL)) jumlah_dinasDalam')
                ->selectRaw('count(IF(absence_category_id = 7 AND status = 0 ,1,NULL)) jumlah_dinasLuar')
                ->selectRaw('count(IF(absence_category_id = 8 AND status = 0 ,1,NULL)) jumlah_cuti')
                ->selectRaw('SUM(IF(absence_category_id = 10 AND duration > 0 AND status = 0 ,duration,0)) jumlah_lembur')
                ->selectRaw('count(IF(absence_category_id = 10 AND duration >= 4 AND status = 0 ,1,NULL)) jumlah_lemburlebih')
                ->selectRaw('count(IF(absence_category_id = 11 AND status = 0 ,1,NULL)) jumlah_permisi')
            // ->selectRaw('count(IF(absence_category_id = 13 AND status = 0 ,1,NULL)) jumlah_izin')
                ->selectRaw('count(IF(absence_category_id = 14 AND status = 0 ,1,NULL)) jumlah_dispen')
                ->selectRaw('count(IF(absence_category_id = 1 AND status = 0 AND late > 0.016667 ,1,NULL)) jumlah_lambat')
                ->where('staff_id', $staff->id)
            //->whereBetween('absences.created_at', [$date_start, $date_end])
                ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
                ->first();

            $sakit = AbsenceRequest::selectRaw('count(id) as jumlah_sakit')
                ->where('category', 'permission')
                ->where('type', 'sick')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            $sakitizindokter = AbsenceRequest::selectRaw('count(id) as jumlah_sakit')
                ->where('category', 'permission')
                ->where('type', 'sick_proof')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            $izin = AbsenceRequest::selectRaw('count(id) as jumlah_izin')
                ->where('category', 'permission')
                ->where('type', 'other')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();
            //get dinas luar
            $dinasLuarRows = AbsenceRequest::selectRaw('start,end')
                ->where('category', 'duty')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->get();
            $jumlah_dinasLuar = 0;
            foreach ($dinasLuarRows as $dinasLuarRow) {
                $diff = date_diff(date_create($dinasLuarRow->start), date_create($dinasLuarRow->end));
                $diff = $diff->format("%R%a");
                $diff = (int) $diff + 1;
                $jumlah_dinasLuar += $diff;
            }
            //permisi
            $permisi = AbsenceRequest::selectRaw('*')
                ->where('category', 'excuse')
            //->where('type', 'out')
                ->where('staff_id', $staff->id)
                ->whereNotIn('status', ['reject', 'pending'])
            //->whereBetween('start', [$date_start, $date_end])
                ->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")
                ->first();

            // if permisi
            $permisi_potong_gaji       = 0;
            $permisi_tidak_potong_gaji = 0;
            if ($permisi) {
                //check reguler or shift
                if ($staff->work_type_id != 2) {
                    //if reguler get hari
                    $day_id = date('w', strtotime($permisi->start)) == "0" ? '7' : date('w', strtotime($permisi->start));
                    //get clock
                    $date_now_12      = date('Y-m-d', strtotime($permisi->start)) . " 12:00:00";
                    $date_now_11      = date('Y-m-d', strtotime($permisi->start)) . " 11:00:00";
                    $register_to_time = strtotime(date('Y-m-d H:i:s', strtotime($permisi->start)));
                    $now_12_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_12)));
                    $now_11_to_time   = strtotime(date('Y-m-d H:i:s', strtotime($date_now_11)));
                    //echo $day_id." - ". $register_to_time." - ". $now_12_to_time." - ". $now_11_to_time;
                    //if reguler mon - thu, check if jam mulai < 12
                    if ($day_id >= 1 && $day_id <= 4 && ($register_to_time < $now_12_to_time)) {
                        $permisi_potong_gaji++;
                    }
                    //if reguler fri, check if jam mulai < 11
                    else if (($day_id == 5 || $day_id == 6) && ($register_to_time < $now_11_to_time)) {
                        $permisi_potong_gaji++;
                    } else {
                        $permisi_tidak_potong_gaji++;
                    }
                } else {
                    $permisi_tidak_potong_gaji++;
                }
            }

            $absen_bolong_datang    = 0;
            $absen_bolong_pulang    = 0;
            $absen_bolong_kegiatan1 = 0;
            $absen_bolong_kegiatan2 = 0;
            //get bolong masuk
            if ($report->jumlah_masuk < $report->jumlah_pulang) {
                $absen_bolong_datang = $report->jumlah_pulang - $report->jumlah_masuk;
            }
            if ($report->jumlah_masuk > $report->jumlah_pulang) {
                $absen_bolong_pulang = $report->jumlah_masuk - $report->jumlah_pulang;
            }
            //get bolong kegiatan
            if ($report->jumlah_k1 < ($report->jumlah_masuk + $absen_bolong_datang)) {
                $absen_bolong_kegiatan1 = ($report->jumlah_masuk + $absen_bolong_datang) - $report->jumlah_k1;
            }
            if ($report->jumlah_k2 < ($report->jumlah_masuk + $absen_bolong_datang)) {
                $absen_bolong_kegiatan2 = ($report->jumlah_masuk + $absen_bolong_datang) - $report->jumlah_k2;
            }
            // $report_masuk        = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            //     ->selectRaw('absence_logs.*')
            //     ->where('absence_category_id', 1)
            //     ->where('status', 0)
            //     ->where('staff_id', $staff->id)
            // //->whereBetween('absences.created_at', [$date_start, $date_end])
            //     ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            //     ->get();

            // foreach ($report_masuk as $item) {
            //     //get absen pulang
            //     $log_out = AbsenceLog::selectRaw('id')
            //         ->where('absence_id', $item->absence_id)
            //         ->where('absence_category_id', 2)
            //         ->first();
            //     if (! $log_out) {
            //         $absen_bolong_pulang += 1;
            //     }
            //     // if ($staff->work_type_id === 1) {
            //     //     $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 210 . 'minutes', strtotime(date($item->timein))));
            //     //     // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
            //     //     if ($item->register >= $cekDateNew) {
            //     //         $absen_bolong += 1;
            //     //     }
            //     // } else {
            //     //     $cekDateNew = date("Y-m-d H:i:s", strtotime('+ ' . 120 . 'minutes', strtotime(date($item->timein))));
            //     //     // dd($cekDateNew > $clock_in, $cekDateNew . ' lebih besar dari ' . $clock_in);
            //     //     if ($item->register >= $cekDateNew) {
            //     //         $absen_bolong += 1;
            //     //     }
            //     // }
            // }

            // $report_pulang = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
            //     ->selectRaw('absence_logs.*')
            //     ->where('absence_category_id', 2)
            //     ->where('status', 0)
            //     ->where('staff_id', $staff->id)
            // //->whereBetween('absences.created_at', [$date_start, $date_end])
            //     ->whereRaw("DATE(absences.created_at) >= '" . $date_start . "' AND DATE(absences.created_at) <= '" . $date_end . "'")
            //     ->get();

            // foreach ($report_pulang as $item) {
            //     //get absen masuk
            //     $log_in = AbsenceLog::selectRaw('id')
            //         ->where('absence_id', $item->absence_id)
            //         ->where('absence_category_id', 1)
            //         ->first();
            //     if (! $log_in) {
            //         $absen_bolong_masuk += 1;
            //     }
            // }

            $kegiatan = '';

            if ($staff->work_type_id === 1) {

                // tanggalnya diubah formatnya ke Y-m-d

                $hariKerja   = [];
                $sabtuminggu = [];

                for ($i = $awal_cuti; $i <= $akhir_cuti; $i += (60 * 60 * 24)) {
                    if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                        $hariKerja[] = $i;
                    } else {
                        $sabtuminggu[] = $i;
                    }
                }
                $jumlah_kerja = count($hariKerja);

                // mencari jumlah hari end

                //$holiday = Holiday::selectRaw('count(id) jumlah_libur')->whereBetween('start', [$date_start, $date_end])->where('status', null)->first();
                $holiday = Holiday::selectRaw('count(id) jumlah_libur')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->first();

                //holiday in weekend
                $holiday_weekend = 0;
                //$holiday_rows = Holiday::selectRaw('start')->whereBetween('start', [$date_start, $date_end])->where('status', null)->get();
                $holiday_rows = Holiday::selectRaw('start')->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->where('status', null)->get();
                foreach ($holiday_rows as $holiday_row) {
                    $dayOfWeek = date('w', strtotime($holiday_row->start));
                    if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                        $holiday_weekend++;
                    }
                }

                $jumlah_libur = $holiday->jumlah_libur - $holiday_weekend;
                $jumlah_kerja = $jumlah_kerja - $jumlah_libur;
                $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit - $sakitizindokter->jumlah_sakit;
                $kegiatan     = "Kegiatan";
                if ($alpha <= 0) {
                    $alpha = 0;
                }
            } else {
                //$jadwal = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $staff->id)->whereBetween('start', [$date_start, $date_end])->first();
                $jadwal       = ShiftPlannerStaffs::selectRaw('count(id) jumlah_kerja')->where('staff_id', $staff->id)->whereRaw("DATE(start) >= '" . $date_start . "' AND DATE(start) <= '" . $date_end . "'")->first();
                $jumlah_kerja = $jadwal->jumlah_kerja;
                $alpha        = $jumlah_kerja - $report->jumlah_masuk - $jumlah_dinasLuar - $report->jumlah_cuti - $izin->jumlah_izin - $report->jumlah_dispen - $sakit->jumlah_sakit - $sakitizindokter->jumlah_sakit;
                $jumlah_libur = 0;
                $kegiatan     = "Kontrol";
                if ($alpha <= 0) {
                    $alpha = 0;
                }
            }

            $jumlah_cuti = $report->jumlah_cuti;
            if ($report->jumlah_cuti > $jumlah_kerja) {
                $jumlah_cuti = $jumlah_kerja;
            }

            $absenceadjs = AbsenceAdj::whereDate('session_start', '=', $request->from)->whereDate('session_end', '=', $request->to)->where('staff_id', '=', $staff->id)->first();

            $data[] = [
                "NIK"                             => $staff->NIK,
                "Nama"                            => $staff->id . " " . $staff->name,
                "Bagian_Unit"                     => $staff->dapertement->name,
                "Tipe_Kerja"                      => $staff->work_type_id === 1 ? "Reguler" : "Shift",
                "Jumlah_Masuk"                    => ($report->jumlah_masuk + $report->jumlah_dispen + $jumlah_dinasLuar) + ($absenceadjs ? $absenceadjs->Jumlah_Masuk : 0),
                "Jumlah_" . $kegiatan . "1"       => $report->jumlah_k1 + ($absenceadjs ? $absenceadjs->Jumlah_Kegiatan1 : 0),
                "Jumlah_" . $kegiatan . "2"       => $report->jumlah_k2 + ($absenceadjs ? $absenceadjs->Jumlah_Kegiatan2 : 0),
                "Jumlah_Dinas_Dalam"              => $report->jumlah_dinasDalam + ($absenceadjs ? $absenceadjs->Jumlah_Dinas_Dalam : 0),
                "Jumlah_Dinas_Luar"               => $jumlah_dinasLuar + ($absenceadjs ? $absenceadjs->Jumlah_Dinas_Luar : 0),
                "Jumlah_Cuti"                     => $jumlah_cuti + ($absenceadjs ? $absenceadjs->Jumlah_Cuti : 0),
                "Jumlah_Lembur"                   => $report->jumlah_lembur + ($absenceadjs ? $absenceadjs->Jumlah_Lembur : 0),
                "Jumlah_Lembur4"                  => $report->jumlah_lemburlebih + ($absenceadjs ? $absenceadjs->Jumlah_Lembur4 : 0),
                "Jumlah_Permisi"                  => $report->jumlah_permisi + ($absenceadjs ? $absenceadjs->Jumlah_Permisi : 0),
                "Jumlah_Izin"                     => $izin->jumlah_izin + ($absenceadjs ? $absenceadjs->Jumlah_Izin : 0),
                "Jumlah_Sakit_Tidak_Izin_Dokter"  => $sakit->jumlah_sakit + ($absenceadjs ? $absenceadjs->Jumlah_Sakit : 0),
                "Jumlah_Sakit_Izin_Dokter"        => $sakitizindokter->jumlah_sakit + ($absenceadjs ? $absenceadjs->Jumlah_Sakit_Izin_Dokter : 0),
                "Jumlah_Dispen"                   => $report->jumlah_dispen + ($absenceadjs ? $absenceadjs->Jumlah_Dispen : 0),
                "Jumlah_Alfa"                     => ($alpha > 0 ? $alpha : 0) + ($absenceadjs ? $absenceadjs->Jumlah_Alfa : 0),
                "Jumlah_Tidak_Masuk"              => ($jumlah_cuti + $izin->jumlah_izin + $sakit->jumlah_sakit + $sakitizindokter->jumlah_sakit + $alpha) + ($absenceadjs ? $absenceadjs->Jumlah_Tidak_Masuk : 0),
                "Jumlah_Hari_Kerja"               => $jumlah_kerja + ($absenceadjs ? $absenceadjs->Jumlah_Hari_Kerja : 0),
                "Jumlah_Hari_Libur"               => $jumlah_libur + ($absenceadjs ? $absenceadjs->Jumlah_Hari_Libur : 0),
                "Absen_Bolong_Datang"             => $absen_bolong_datang + ($absenceadjs ? $absenceadjs->Absen_Bolong_Datang : 0),
                "Absen_Bolong_Pulang"             => $absen_bolong_pulang + ($absenceadjs ? $absenceadjs->Absen_Bolong_Pulang : 0), //
                "Absen_Lambat"                    => $report->jumlah_lambat + ($absenceadjs ? $absenceadjs->Absen_Lambat : 0),
                "Permisi_Potong_Gaji"             => $permisi_potong_gaji + ($absenceadjs ? $absenceadjs->Permisi_Potong_Gaji : 0),
                "Permisi_Tidak_Potong_Gaji"       => $permisi_tidak_potong_gaji + ($absenceadjs ? $absenceadjs->Permisi_Tidak_Potong_Gaji : 0),
                "Absen_Bolong_" . $kegiatan . "1" => $absen_bolong_kegiatan1 + ($absenceadjs ? $absenceadjs->Absen_Bolong_Kegiatan1 : 0),
                "Absen_Bolong_" . $kegiatan . "2" => $absen_bolong_kegiatan2 + ($absenceadjs ? $absenceadjs->Absen_Bolong_Kegiatan2 : 0),
            ];
        }

        return response()->json([
            'message' => 'success',
            'data'    => $data,
        ]);
    }

    public function history(Request $request)
    {
        $data = [];
        // $absence = Absence::join('days', 'days.id', '=', 'absences.day_id')
        //     ->selectRaw('absences.id,DATE(created_at) as created_at, days.name as day_name')
        //     // ->where('staff_id', $request->staff_id)
        //     ->where('staff_id', $request->staff_id)
        //     ->FilterDate($request->from, $request->to)
        //     ->groupByRaw('DATE(created_at)')
        //     ->orderBy('created_at', 'DESC')
        //     ->get();

        $absence = Absence::join('days', 'days.id', '=', 'absences.day_id')
            ->join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
            ->selectRaw('absences.id,DATE(absences.created_at) as created_at, days.name as day_name')
            ->where('absence_logs.absence_category_id', '2')
            ->where('staff_id', $request->staff_id)
            ->FilterDate($request->from, $request->to)
        // ->groupByRaw('DATE(created_at)')
            ->orderBy('absences.created_at', 'DESC')
            ->get();

        //$outtestt = '';
        foreach ($absence as $d) {
            // $absence_log = AbsenceLog::join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            //     ->select('absence_logs.*', 'absence_categories.title as category_title', 'IF("register != "", absence_logs.id, "")')
            //     ->where('absence_logs.register', '!=', '')
            //     ->where('absence_id', '=', $d->id)->get();
            // $absence_log_str = AbsenceLog::join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
            //     ->select(
            //         'absence_logs.*',
            //         'absence_categories.title as category_title',
            //         DB::raw("count(IF(absence_category_id = 1 AND status = 0 AND late > 0.016667 ,1,NULL)) late"),
            //         DB::raw("(CASE WHEN status = 0 THEN register ELSE '2020:01:01 00:00:00' END) as register")
            //     )
            //     //->where('absence_logs.register', '!=', '')
            //     ->whereNotNull('absence_logs.register')
            //     ->where('absence_id', '=', $d->id)
            //     ->groupBy('absence_logs.id')
            //     ->toSql();
            //     $outtestt .=$absence_log_str;
            $absence_log = AbsenceLog::join('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->select(
                    'absence_logs.*',
                    'absence_categories.title as category_title',
                    DB::raw("count(IF(absence_category_id = 1 AND status = 0 AND late > 0.016667 ,1,NULL)) late"),
                    DB::raw("(CASE WHEN status = 0 THEN register ELSE '2020:01:01 00:00:00' END) as register")
                )
            //->where('absence_logs.register', '!=', '')
                ->whereNotNull('absence_logs.register')
                ->where('absence_id', '=', $d->id)
                ->groupBy('absence_logs.id')
                ->get();
            if (count($absence_log) > 0) {
                if ($absence_log[0]->absence_category_id != 9 && $absence_log[0]->absence_category_id != 10) {
                    $data[] = ['date' => $d->created_at, 'day_name' => $d->day_name, 'list' => $absence_log];
                }
            }
        }

        // $tesss = Absence::join('days', 'days.id', '=', 'absences.day_id')
        //     ->join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
        //     ->selectRaw('absences.id,DATE(absences.created_at) as created_at, days.name as day_name')
        //     ->where('absence_logs.absence_category_id', '2')
        //     ->where('staff_id', $request->staff_id)
        //     ->FilterDate($request->from, $request->to)
        //     // ->groupByRaw('DATE(created_at)')
        //     ->orderBy('absences.created_at', 'DESC')
        //     ->toSql();
        return response()->json([
            'message' => 'success',
            'data'    => $data,
            'tesss'   => $absence, //$absence
        ]);
    }

    public function schedule(Request $request)
    {
        $type      = "";
        $schedule  = [];
        $coordinat = WorkUnit::join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $request->staff_id)->first();
        $type = $coordinat->type;
        if ($coordinat->type == "shift") {

            $list_absence = ShiftPlannerStaffs::select(DB::raw('DATE(shift_planner_staffs.start) AS date'), 'shift_planner_staffs.id as shift_planner_id', 'shift_planner_staffs.shift_group_id')
                ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
                ->leftJoin('absence_logs', 'shift_planner_staffs.id', '=', 'absence_logs.shift_planner_id')
            // ->where('absence_category_id', '!=', '3')
            // ->where('absence_category_id', '!=', '4')
                ->where('shift_planner_staffs.staff_id', '=', $request->staff_id)
                ->groupBy('shift_planner_staffs.id')
                ->whereDate('shift_planner_staffs.start', '>=', date('Y-m-d'))
                ->orderBy('shift_planner_staffs.start', 'ASC')
                ->get();

            foreach ($list_absence as $data) {
                $schedule[] = [
                    'id'   => $data->shift_planner_id,
                    'date' => $data->date,
                    'list' => ShiftGroups::selectRaw('duration, duration_exp, type, time, start, absence_category_id,shift_group_timesheets.id as shift_group_timesheet_id ')
                        ->join('shift_group_timesheets', 'shift_group_timesheets.shift_group_id', '=', 'shift_groups.id')
                        ->join('absence_categories', 'shift_group_timesheets.absence_category_id', '=', 'absence_categories.id')
                        ->where('shift_groups.id', $data->shift_group_id)
                        ->orderBy('absence_categories.id', 'ASC')
                        ->get(),

                ];
            }
            $schedule = $schedule;
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
                    'day'  => $data->name,
                    'list' => WorkTypeDays::selectRaw('duration, duration_exp, type, time, start, absence_category_id,work_type_days.id as work_type_day_id ')
                        ->join('absence_categories', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
                        ->where('work_type_id', $coordinat->work_type_id)
                        ->where('day_id', $data->id)
                        ->orderBy('day_id', 'ASC')
                        ->get(),

                ];
            }
        }
        return response()->json([
            'message' => 'success',
            'type'    => $type,
            // 'type' => 'benar',
            'data'    => $schedule,
        ]);
    }

    public function holiday(Request $request)
    {
        $holiday = Holiday::whereDate('start', '>=', date('Y-m-d'))->paginate(3, ['*'], 'page', $request->page);
        return response()->json([
            'message' => 'success',
            'data'    => $holiday,
        ]);
    }

    // untuk lembur
    public function storeExtra(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';
        $data_image    = "";
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image     = $request->staff_id;
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            // tambah watermark start
            $image = $request->file('image');

            $imgFile = Image::make($image->getRealPath());

            $imgFile->insert($basepath . "/images/Logo.png", 'bottom-right', 10, 10);

            $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : ' . $request->lat . ' lng : ' . $request->lng, 10, 10, function ($font) {
                $font->file(base_path() . '/public' . '/font/Titania-Regular.ttf');
                $font->size(14);
                $font->color('#000000');
                $font->valign('top');
            })->save($basepath . $name_image);

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
        $late  = 0;
        $early = 0;
        try {
            $upload_image = AbsenceLog::where('id', $request->id)->first();

            $upload_image->late  = $late;
            $upload_image->early = $early;

            $upload_image->image = $data_image;
            // sementara start
            $upload_image->created_by_staff_id = $request->staff_id;
            $upload_image->updated_by_staff_id = $request->staff_id;
            $upload_image->register            = date('Y-m-d H:i:s');
            // $upload_image->late = $late;
            // $upload_image->early = $early;
            $upload_image->duration = $duration;
            // sementara end
            $upload_image->register   = date('Y-m-d H:i:s');
            $upload_image->updated_at = date('Y-m-d H:i:s');
            $upload_image->lat        = $request->lat;
            $upload_image->lng        = $request->lng;
            $upload_image->status     = 0;
            $upload_image->accuracy   = $request->accuracy;
            $upload_image->distance   = $request->distance;
            // $upload_image->shift_id = $request->shift_id;

            $upload_image->save();

            // start update request
            if ($upload_image->absence_request_id != "" && $upload_image->absence_request_id != null) {
                AbsenceRequest::where('id', $upload_image->absence_request_id)->update(['status' => 'close']);
            }

            return response()->json([
                'message' => 'Absen Terkirim',
                'data'    => $upload_image,
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
        $data    = [];
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
            'data'    => $data,
            'tesss'   => $absence,
        ]);
    }

    public function sickAdd(Request $request)
    {
        $dataForm = json_decode($request->form);
        try {
            $absenceRequest = AbsenceRequest::where('id', $dataForm->id)->first();
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
                    if (! $holiday) {
                        if (date("w", strtotime(date('Y-m-d', $i))) != 0 && date("w", strtotime(date('Y-m-d', $i))) != 6) {

                            $ab_id = Absence::create([
                                'day_id'     => date("w", strtotime(date('Y-m-d', $i))),
                                'staff_id'   => $absenceRequest->staff_id,
                                'created_at' => date('Y-m-d H:i:s', $i),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                            AbsenceLog::create([
                                'absence_category_id' => $absenceRequest->category == "leave" ? 8 : 13,
                                'lat'                 => '',
                                'lng'                 => '',
                                'absence_request_id'  => $absenceRequest->id,
                                'register'            => date('Y-m-d', $i),
                                'absence_id'          => $ab_id->id,
                                'duration'            => '',
                                'status'              => '',
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

    // ambil jadwal
    public function nextAbsence(Request $request)
    {
        // cek jadwal absen selanjutnya start
        $list_absence = [];

        $absence = Absence::select('absences.*')->join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')->whereDate('absences.created_at', date('Y-m-d'))
            ->where('absence_logs.absence_category_id', 2)
            ->where('absence_logs.status', 1)
            ->where('staff_id', $request->staff_id)
            ->orderBy('absence_logs.start_date', 'ASC')
            ->first();

        $staff = Staff::where('id', $request->staff_id)->first();

        if ($absence) {
            $absenceList = AbsenceLog::selectRaw('absence_logs.expired_date, absence_logs.start_date,shift_planner_id, queue, status_active, absence_categories.id as absence_category_id, absences.id as absence_id, absence_logs.id as id')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->where('absence_id', $absence->id)
            // ->where('absence_logs.start_date', '<=', date('Y-m-d H:i:s'))
                ->where('absence_logs.expired_date', '>=', date('Y-m-d H:i:s'))
                ->where('absence_logs.status', '=', 1)
                ->whereIn('absence_category_id', [1, 2, 3, 4])
                ->orderBy('absence_logs.start_date', 'ASC')
                ->get();

            foreach ($absenceList as $data) {

                switch ($data->absence_category_id) {
                    case 1:
                        $title = "Masuk";
                        break;
                    case 2:
                        $title = "Pulang";
                        break;
                    case 3:
                        $title = $staff ? "Kegiatan 1" : "Kontrol 1";
                        break;
                    case 4:
                        $title = $staff ? "Kegiatan 2" : "Kontrol 2";
                        break;
                    default:
                        $title = "";
                        break;
                }

                $list_absence[] = ["title" => $title, "start" => substr($data->start_date, 0, -3), "end" => substr($data->expired_date, 0, -3)];
            }
        }

        // cek jadwal absence selanjutnya end

        return response()->json(
            $list_absence
        );
    }
}
