<?php

namespace App\Http\Controllers\Api\V1\Absence;

use App\Absence;
use App\AbsenceLog;
use App\AbsenceRequest;
use App\AbsenceRequestLogs;
use App\Day;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\MessageLog;
use App\Requests;
use App\Requests_file;
use App\ShiftChange;
use App\ShiftGroupTimesheets;
use App\ShiftPlannerStaffs;
use App\Staff;
use App\StaffApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OneSignal;
use App\Traits\WablasTrait;
use App\User;
use App\wa_history;
use App\WorkTypeDays;
use App\WorkUnit;

class RequestApiController extends Controller
{
    use WablasTrait;
    // tidak dipakai lagi
    public function index(Request $request)
    {
        $workPermit = Absence::where('user_id', $request->id)->where('register', $request->date)->get();
        $absenOut = Absence::where('user_id', $request->id)->where('absen_category_id', $request->absen_category_id)->get();
        $wP = '0';
        $aO = '0';
        if (count($workPermit) > 0) {
            $wP = '0';
        } else {
            $wP = '1';
        }
        if (count($absenOut) > 0) {
            $aO = '1';
        } else {
            $wP = '0';
        }
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'absenOut' => $aO,
            'workPermit' => $wP,
        ]);
    }

    public function store(Request $request)
    {

        // return response()->json([
        //     'message' => 'Pengajuan Terkirimn 1',
        // ]);
        // $last_code = $this->get_last_code('lock_action');
        // $code = acc_code_generate($last_code, 8, 3);
        $dataForm = json_decode($request->form);
        // $dataForm = $request;
        // pengecekan utama start
        if ($dataForm->category == "extra") {
            $start1 = date("Y-m-d H:i:s", strtotime($dataForm->start . $dataForm->time));
            $day = date("w", strtotime($dataForm->start));
            $staff = Staff::selectRaw('staffs.*,work_types.type as work_type, work_types.id as work_type_id ')->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
                ->where('staffs.id', $dataForm->staff_id)->first();
            // dd($staff);
            if ($staff->work_type == "shift") {
                $jumShift = ShiftPlannerStaffs::whereDate('shift_planner_staffs.start', '=', $dataForm->start)
                    ->where('staff_id', $staff->id)
                    ->get();
                foreach ($jumShift as $data) {
                    $absence = ShiftPlannerStaffs::join('shift_groups', 'shift_groups.id', '=', 'shift_planner_staffs.shift_group_id')
                        ->join('shift_group_timesheets', 'shift_groups.id', '=', 'shift_group_timesheets.shift_group_id')
                        ->where('shift_group_timesheets.absence_category_id', '1')
                        ->where('shift_groups.id', $data->shift_group_id)
                        ->where('staff_id', $staff->id)
                        ->whereDate('shift_planner_staffs.start', '=', $dataForm->start)
                        ->orWhere('shift_group_timesheets.absence_category_id', '2')
                        ->where('shift_groups.id', $data->shift_group_id)
                        ->where('staff_id', $staff->id)
                        ->whereDate('shift_planner_staffs.start', '=', $dataForm->start)
                        ->orderBy('shift_group_timesheets.absence_category_id', 'ASC')
                        ->get();

                    if ($absence[0]->time > $absence[1]->time) {
                        $masuk = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[0]->time));
                        $pulang = date("Y-m-d H:i:s", strtotime('+ ' . '1' . ' days', strtotime($dataForm->start . $absence[1]->time)));
                        //  date("Y-m-d H:i:s", strtotime());
                    } else {
                        $masuk = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[0]->time));
                        $pulang = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[1]->time));
                    }


                    // dd($masuk, $start1, $pulang, $absence);
                    if ($start1 > $masuk && $start1 < $pulang) {
                        return response()->json(
                            [

                                'message' => 'anda tidak bisa melakukan lembur di jam kerja'
                            ]
                        );
                    }
                }
            } else {
                $absence = WorkTypeDays::selectRaw('time')
                    ->where('work_type_id', $staff->work_type_id)
                    ->where('day_id', $day != "0" ? $day : "7")
                    ->where('work_type_days.absence_category_id', '2')
                    ->orWhere('work_type_days.absence_category_id', '1')
                    ->where('work_type_id', $staff->work_type_id)
                    ->where('day_id', $day != "0" ? $day : "7")
                    ->orderBy('work_type_days.absence_category_id', 'ASC')
                    ->get();
                $holiday = Holiday::whereDate('start', '=', date("Y-m-d", strtotime($dataForm->start)))->first();
                if (!$holiday) {
                    if (count($absence) > 0) {
                        $masuk = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[0]->time));
                        $pulang = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[1]->time));
                        // dd($masuk, $start1, $pulang);
                        if ($start1 > $masuk && $start1 < $pulang) {
                            return response()->json(
                                [
                                    'message' => 'anda tidak bisa melakukan lembur di jam kerja'
                                ]
                            );
                        }
                    }
                }
                // else {
                //     return response()->json(
                //         [
                //             'message' => $staff->work_type_id
                //         ]
                //     );
                // }
            }

            // dd($absence);
        } else {
        }
        // pengecekan utama end

        $start = "";
        $end = "";
        $error = "";
        $cek = null;
        if ($dataForm->start == "") {
            $start = date('Y-m-d H:i:s');
            $startS = date('Y-m-d H:i:s');
        } else if ($dataForm->start != "" && $dataForm->time == "") {
            $start = date("Y-m-d H:i:s", strtotime($dataForm->start));
            $startS = date("Y-m-d H:i:s", strtotime($dataForm->start));
        } else {
            $start = date("Y-m-d H:i:s", strtotime($dataForm->start . $dataForm->time));
            $startS = date("Y-m-d H:i:s", strtotime($dataForm->start . $dataForm->time));
        }

        if ($dataForm->end == "") {
            $end = date("Y-m-d H:i:s", strtotime($dataForm->start . '23:59:59'));
            $endS = date("Y-m-d H:i:s", strtotime($dataForm->start . '23:59:59'));
        } else {
            $end = date("Y-m-d H:i:s", strtotime($dataForm->end . '23:59:59'));
            $endS = date("Y-m-d H:i:s", strtotime($dataForm->end . '23:59:59'));
        }
        if ($start < date('Y-m-d') || $start > $end) {
            $cek = "pass";
            $error = "Tanggal kurang dari hari ini";
        }
        // pengecekan lebur dan dinas hanya di jam kerja start
        else if ($dataForm->category == "leave" || $dataForm->category == "permission") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $absen_check = Absence::where('staff_id', $dataForm->staff_id)
                ->leftJoin('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [$start, $end])
                ->whereRaw("DATE(absences.created_at) >= '".$start."' AND DATE(absences.created_at) <= '".$end."'")
                //->where('register', '!=', null)
                ->whereNotNull('register')
                ->first();
            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
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
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            // dd($cek);


            if ($cek) {
                $error = "Anda Masih Memiliki Cuti/Dinas/Izin yang masih aktif di tanggal ini";
            } else if ($absen_check) {
                $error = "Pengajuan tidak bisa dilakukan jika anda masuk dihari tersebut";
            }
        } else if ($dataForm->category == "duty" || $dataForm->category == "visit" || $dataForm->category == "leave" || $dataForm->category == "permission") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));

            if ($dataForm->category == "visit") {
                $startS = date("Y-m-d H:i:s", strtotime($dataForm->start . $dataForm->time));
                // pengecekan lanjutan start
                $request_date = $start;
                $day_id = date('w', strtotime($request_date)) == "0" ? '7' : date('w', strtotime($request_date));
                // dd($day_id);
                $message_err = 'anda hanya bisa mengajukan di jam kerja';
                $staff = Staff::where('id', $dataForm->staff_id)->first();
                $absen_now = Absence::join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
                    ->where('staff_id', $dataForm->staff_id)
                    ->where('absence_category_id', 2)
                    ->where('absence_logs.status', 1)
                    ->whereDate('absences.created_at', date('Y-m-d'))
                    ->first();



                if (date('Y-m-d') == $request_date) {
                    $schedule = null;
                    if (!$absen_now) {
                        $error = 'anda hanya bisa mengajukan di jam kerja';
                    } else {

                        if ($staff->work_type_id === 1) {
                            $schedule = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 1)->first();
                            // $schedule_end = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 2)->first();
                        } else {
                            $shift_staff = ShiftPlannerStaffs::whereDate('start', '=', $request_date)
                                ->where('staff_id', $staff->id)->first();
                            // dd($shift_staff);
                            if ($shift_staff) {
                                $schedule = ShiftGroupTimesheets::where('id', $shift_staff->shift_group_id)
                                    ->where('start', $request_date)
                                    ->where('absence_category_id', 1)->first();
                                // $schedule_end = ShiftGroupTimesheets::where('id', $id)->where('absence_category_id', 2)->first();
                            } else {
                                $error = 'anda hanya bisa mengajukan di jam kerja';
                            }
                        }
                        // dd($absen_now);
                        if ($schedule) {
                            $time_end = date("Y-m-d H:i:s", strtotime('+' . $schedule->duration . ' hours', strtotime(date('Y-m-d ' . $schedule->time))));
                            $time_start = date("Y-m-d H:i:s", strtotime('+' . 0 . ' hours', strtotime(date('Y-m-d ' . $schedule->time))));
                            // dd($schedule);
                            if ($time_start <  $startS &&  $startS < $time_end) {
                                // return json_encode('pengajuan berhasil');
                            } else {
                                $error = 'anda hanya bisa mengajukan di jam kerja';
                            }
                        } else {
                            $error = 'anda hanya bisa mengajukan di jam kerja';
                        }
                    }
                } else {

                    if ($staff->work_type_id === 1) {
                        $schedule = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 1)->first();
                        // $schedule_end = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 2)->first();
                    } else {
                        $shift_staff = ShiftPlannerStaffs::where('staff_id', $staff->id)->first();
                        // dd($shift_staff);
                        if ($shift_staff) {
                            $schedule = ShiftGroupTimesheets::where('id', $shift_staff->id)
                                ->where('start', $request_date)
                                ->where('absence_category_id', 1)
                                ->first();
                            // $schedule_end = ShiftGroupTimesheets::where('id', $id)->where('absence_category_id', 2)->first();
                        } else {
                            $error = 'anda hanya bisa mengajukan di jam kerja';
                        }
                    }
                    if ($schedule) {
                        $time_end = date("Y-m-d H:i:s", strtotime('+' . $schedule->duration . ' hours', strtotime(date($request_date . ' ' . $schedule->time))));
                        $time_start = date("Y-m-d H:i:s", strtotime('+' . 0 . ' hours', strtotime(date($request_date . ' ' . $schedule->time))));
                        // dd($schedule);
                        if ($time_start <  $startS &&  $startS < $time_end) {
                            // return json_encode('pengajuan berhasil');
                        } else {
                            $error = 'anda hanya bisa mengajukan di jam kerja';
                        }
                    } else {
                        $error = 'anda hanya bisa mengajukan di jam kerja';
                    }
                }

                // cek sudah absen
                // $cekAbsenceInAlready = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                //     ->whereDate('absences.created_at', '=', date('Y-m-d'))
                //     ->where('absence_category_id', 1)
                //     ->where('status', 0)
                //     ->where('staff_id', $staff->id)->first();
                // if ($cekAbsenceInAlready) {
                //     $cekAbsenceOutAlready = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                //         ->whereDate('absences.created_at', '=', date('Y-m-d'))
                //         ->where('absence_category_id', 2)
                //         ->where('status', 1)
                //         ->where('staff_id', $staff->id)->first();
                //     if ($cekAbsenceOutAlready) {
                //         $error = "";
                //     }
                // }


                // cek sudah absen
                $cekAbsenceInAlready = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                    ->whereDate('absences.created_at', '=', date('Y-m-d'))
                    ->where('absence_category_id', 1)
                    ->where('status', 0)
                    ->where('staff_id', $staff->id)->first();
                if ($cekAbsenceInAlready) {
                    $cekAbsenceOutAlready = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                        ->whereDate('absences.created_at', '=', date('Y-m-d'))
                        ->where('absence_category_id', 2)
                        ->where('status', 1)
                        ->where('staff_id', $staff->id)->first();
                    if ($cekAbsenceOutAlready) {
                        if ($cekAbsenceOutAlready->timeout < date('Y-m-d')) {
                            $error = "";
                        }
                    }
                }

                // pengecekan lanjutan end
            }



            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) use ($start, $end) {
                    $query->where('category', 'visit')
                        ->orWhere('category', 'visit')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission')
                        ->orWhere('category', 'extra')
                        ->orWhere('category', 'excuse');

                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            // dd($cek);


            if ($cek) {
                $error = "Anda Masih Memiliki Cuti/Dinas/Izin yang masih aktif di tanggal ini";
            }
            //  else {
            //     $error = "kosong";
            // }
            // return response()->json([
            //     'message' => $error,
            //     'data' => $error,
            // ]);
        } else if ($dataForm->category == "excuse") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $startS = date("Y-m-d H:i:s", strtotime($dataForm->start . $dataForm->time));

            // pengecekan lanjutan start
            $request_date = $start;
            $day_id = date('w', strtotime($request_date)) == "0" ? '7' : date('w', strtotime($request_date));
            // dd($day_id);
            $message_err = 'anda hanya bisa mengajukan di jam kerja';
            $staff = Staff::where('id', $dataForm->staff_id)->first();
            $absen_now = Absence::join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
                ->where('staff_id', $dataForm->staff_id)
                ->where('absence_category_id', 2)
                ->where('absence_logs.status', 1)
                ->whereDate('absences.created_at', date('Y-m-d'))
                ->first();



            if (date('Y-m-d') == $request_date) {
                $schedule = null;
                if (!$absen_now) {
                    $error = 'anda hanya bisa mengajukan di jam kerja';
                } else {

                    if ($staff->work_type_id === 1) {
                        $schedule = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 1)->first();
                        // $schedule_end = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 2)->first();
                    } else {
                        $shift_staff = ShiftPlannerStaffs::whereDate('start', '=', $request_date)
                            ->where('staff_id', $staff->id)->first();
                        // dd($shift_staff);
                        // return response()->json([
                        //     'message' => $shift_staff->shift_group_id,
                        //     'data' => '',
                        // ]);
                        if ($shift_staff) {
                            // return response()->json([
                            //     'message' => $shift_staff->shift_group_id,
                            //     'data' => '',
                            // ]);
                            $schedule = ShiftGroupTimesheets::where('id', $shift_staff->shift_group_id)
                                ->where('absence_category_id', 1)
                                ->first();

                            // return response()->json([
                            //     'message' => 'sjshsbsbs',
                            //     'data' => '',
                            // ]);
                            // $schedule_end = ShiftGroupTimesheets::where('id', $id)->where('absence_category_id', 2)->first();
                        } else {
                            $error = 'anda hanya bisa mengajukan di jam kerja';
                        }
                    }
                    // dd($absen_now);
                    if ($schedule) {
                        $time_end = date("Y-m-d H:i:s", strtotime('+' . $schedule->duration . ' hours', strtotime(date('Y-m-d ' . $schedule->time))));
                        $time_start = date("Y-m-d H:i:s", strtotime('+' . 0 . ' hours', strtotime(date('Y-m-d ' . $schedule->time))));
                        // dd($schedule);
                        if ($time_start <  $startS &&  $startS < $time_end) {
                            // return json_encode('pengajuan berhasil');
                        } else {
                            $error = 'anda hanya bisa mengajukan di jam kerja';
                        }
                    } else {
                        $error = 'anda hanya bisa mengajukan di jam kerja';
                    }
                }
            } else {

                if ($staff->work_type_id === 1) {
                    $schedule = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 1)->first();
                    // $schedule_end = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 2)->first();
                } else {
                    $shift_staff = ShiftPlannerStaffs::where('staff_id', $staff->id)->first();
                    // dd($shift_staff);
                    if ($shift_staff) {
                        $schedule = ShiftGroupTimesheets::whereDate('start', $request_date)
                            ->where('id', $shift_staff->id)
                            ->whereDate('start', '=', $request_date)
                            ->where('absence_category_id', 1)->first();
                        // $schedule_end = ShiftGroupTimesheets::where('id', $id)->where('absence_category_id', 2)->first();
                    } else {
                        $error = 'anda hanya bisa mengajukan di jam kerja';
                    }
                }
                if ($schedule) {
                    $time_end = date("Y-m-d H:i:s", strtotime('+' . $schedule->duration . ' hours', strtotime(date($request_date . ' ' . $schedule->time))));
                    $time_start = date("Y-m-d H:i:s", strtotime('+' . 0 . ' hours', strtotime(date($request_date . ' ' . $schedule->time))));
                    // dd($schedule);
                    if ($time_start <  $startS &&  $startS < $time_end) {
                        // return json_encode('pengajuan berhasil');
                    } else {
                        $error = 'anda hanya bisa mengajukan di jam kerja';
                    }
                } else {
                    $error = 'anda hanya bisa mengajukan di jam kerja';
                }
            }

            // cek sudah absen
            $cekAbsenceInAlready = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->whereDate('absences.created_at', '=', date('Y-m-d'))
                ->where('absence_category_id', 1)
                ->where('status', 0)
                ->where('staff_id', $staff->id)->first();
            if ($cekAbsenceInAlready) {
                $cekAbsenceOutAlready = Absence::join('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                    ->whereDate('absences.created_at', '=', date('Y-m-d'))
                    ->where('absence_category_id', 2)
                    ->where('status', 1)
                    ->where('staff_id', $staff->id)->first();
                if ($cekAbsenceOutAlready) {
                    if ($cekAbsenceOutAlready->timeout < date('Y-m-d')) {
                        $error = "";
                    }
                }
            }


            // pengecekan lanjutan end


            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) {
                    $query->where('category', 'excuse')
                        ->orWhere('category', 'duty')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission');
                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();


            if ($cek) {
                $error = "Anda Masih Memiliki Permisi di tanggal ini";
            }
        } else if ($dataForm->category == "extra") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) {
                    $query->where('category', 'extra')
                        ->orWhere('category', 'duty')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission');
                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(bsence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            if ($cek) {
                $error = "Anda Masih Memiliki Lembur di tanggal ini";
            }
        } else if ($dataForm->category == "geolocation_off") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) {
                    $query->where('category', 'geolocation_off')
                        ->orWhere('category', 'duty')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission')
                        ->orWhere('category', 'excuse');
                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            if ($cek) {
                $error = "Anda Masih Memiliki Permohonan Absen Luar di tanggal ini";
            }
        } else if ($dataForm->category == "forget") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $startS = date("Y-m-d H:i:s");

            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) {
                    $query->orWhere('category', 'duty')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission')
                        ->orWhere('category', 'forget')
                        ->orWhere('category', 'additionalTime');
                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            if ($cek) {
                $error = "Anda Sudah Memiliki Pengajuan di tanggal ini";
            }
            // pengecekan lanjutan start
            $request_date = $start;
            $day_id = date('w', strtotime($request_date)) == "0" ? '7' : date('w', strtotime($request_date));
            // dd($day_id);
            $message_err = 'anda hanya bisa mengajukan di jam kerja';
            $staff = Staff::where('id', $dataForm->staff_id)->first();
            $absen_now = Absence::join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
                ->where('staff_id', $dataForm->staff_id)
                ->where('absence_category_id', 2)
                ->where('absence_logs.status', 1)
                ->whereDate('absences.created_at', date('Y-m-d'))
                ->first();



            if (date('Y-m-d') == $request_date) {
                $schedule = null;
                if (!$absen_now) {
                    $error = 'anda hanya bisa mengajukan di jam kerja';
                } else {

                    if ($staff->work_type_id === 1) {
                        $schedule = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 1)->first();
                        // $schedule_end = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 2)->first();
                    } else {
                        $shift_staff = ShiftPlannerStaffs::where('staff_id', $staff->id)->first();
                        // dd($shift_staff);
                        if ($shift_staff) {
                            $schedule = ShiftGroupTimesheets::where('id', $shift_staff->id)
                                ->where('start', $request_date)
                                ->where('absence_category_id', 1)->first();
                            // $schedule_end = ShiftGroupTimesheets::where('id', $id)->where('absence_category_id', 2)->first();
                        } else {
                            $error = 'anda hanya bisa mengajukan di hari anda bekerja';
                        }
                    }
                    // dd($absen_now);
                    if ($schedule) {
                        $time_end = date("Y-m-d H:i:s", strtotime('+' . $schedule->duration . ' hours', strtotime(date('Y-m-d ' . $schedule->time))));
                        $time_start = date("Y-m-d H:i:s", strtotime('+' . 0 . ' hours', strtotime(date('Y-m-d ' . $schedule->time))));
                        // dd($schedule);
                        if ($time_start <  $startS &&  $startS < $time_end) {
                            // return json_encode('pengajuan berhasil');
                            $error = 'anda hanya bisa mengajukan ketika lewat jam kerja';
                        } else {
                        }
                    } else {
                        $error = 'anda hanya bisa mengajukan di hari anda bekerja';
                    }
                }
            } else {

                if ($staff->work_type_id === 1) {
                    $schedule = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 1)->first();
                    // $schedule_end = WorkTypeDays::where('day_id', $day_id)->where('absence_category_id', 2)->first();
                } else {
                    $shift_staff = ShiftPlannerStaffs::where('staff_id', $staff->id)->first();
                    // dd($shift_staff);
                    if ($shift_staff) {
                        $schedule = ShiftGroupTimesheets::where('id', $shift_staff->id)
                            ->where('start', $request_date)
                            ->where('absence_category_id', 1)->first();
                        // $schedule_end = ShiftGroupTimesheets::where('id', $id)->where('absence_category_id', 2)->first();
                    } else {
                        $error = 'anda hanya bisa mengajukan di hari anda bekerja';
                    }
                }
                if ($schedule) {
                    $time_end = date("Y-m-d H:i:s", strtotime('+' . $schedule->duration . ' hours', strtotime(date($request_date . ' ' . $schedule->time))));
                    $time_start = date("Y-m-d H:i:s", strtotime('+' . 0 . ' hours', strtotime(date($request_date . ' ' . $schedule->time))));
                    // dd($schedule);
                    if ($time_start <  $startS &&  $startS < $time_end) {
                        $error = 'anda hanya bisa mengajukan ketika lewat jam kerja';
                    } else {
                        // $error = 'anda hanya bisa mengajukan di jam kerja';
                    }
                } else {
                    $error = 'anda hanya bisa mengajukan di hari anda bekerja';
                }
            }

            // pengecekan lanjutan end
        } else if ($dataForm->category == "AdditionalTime") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $startS = date("Y-m-d H:i:s");

            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) {
                    $query->orWhere('category', 'duty')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission')
                        ->orWhere('category', 'AdditionalTime')
                        ->orWhere('category', 'forget');
                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            if ($cek) {
                $error = "Anda Sudah Memiliki Pengajuan di tanggal ini";
            }
            // pengecekan lanjutan start
            $request_date = $start;
            $day_id = date('w', strtotime($request_date)) == "0" ? '7' : date('w', strtotime($request_date));
            // dd($day_id);
            $message_err = 'anda hanya bisa mengajukan di jam kerja';
            $staff = Staff::where('id', $dataForm->staff_id)->first();
            $absen_now = Absence::join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
                ->where('staff_id', $dataForm->staff_id)
                ->where('absence_category_id', 2)
                ->where('absence_logs.status', 1)
                ->whereDate('absences.created_at', date('Y-m-d'))
                ->first();


            if (!$absen_now) {
                $error = 'anda hanya bisa mengajukan ketika sudah absen masuk dan belum melakukan absen pulang';
            }
            // pengecekan lanjutan end
        } else if ($dataForm->category == "location") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) {
                    $query->where('category', 'geolocation_off')
                        ->orWhere('category', 'duty')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission')
                        ->orWhere('category', 'excuse');
                    // ->orWhere('category', 'location');
                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            if ($cek) {
                $error = "Anda Masih Memiliki Permohonan Absen Luar di tanggal ini";
            }
        } else {
        }

        if ($dataForm->type == "sick") {
            $endS = "";
        } else if ($dataForm->type == "sick_approve") {
            $endS = "";
        } else {
            $endS =  $endS;
        }

        if ($error == "") {
            $requests = new AbsenceRequest();
            $requests->staff_id = $dataForm->staff_id;
            $requests->description = $dataForm->description;
            $requests->start = $startS;
            $requests->end = $endS;
            $requests->type = $dataForm->type;
            $requests->time = $dataForm->time;
            $requests->status = $dataForm->status;
            $requests->category = $dataForm->category;

            if ($dataForm->category == "location") {
                $requests->work_unit_id = $dataForm->work_unit_id;
            }

            $requests->save();
            $requests_id = $requests->id;


            if ($request->file('imageP')) {
                $image = $request->file('imageP');
                $resourceImage = $image;
                $nameImage = 'imageP' . date('Y-m-d h:i:s') . '.' . $image->extension();
                $file_extImage = $image->extension();
                $folder_upload = 'images/RequestFile';
                $resourceImage->move($folder_upload, $nameImage);

                // dd($request->file('old_image')->move($folder_upload, $img_name));

                // if ($actionWm->old_image != '') {
                //     foreach (json_decode($actionWm->old_image) as $n) {
                //         if (file_exists($n)) {

                //             unlink($basepath . $n);
                //         }
                //     }
                // }
                $data = [
                    'image' => $nameImage,
                    'absence_request_id' => $requests_id,
                    'type' => 'approve'
                ];
                $data = AbsenceRequestLogs::create($data);
            }

            if ($request->file('imagePng')) {
                $image = $request->file('imagePng');
                $resourceImage = $image;
                $nameImage = 'imagePng' . date('Y-m-d h:i:s') . '.' . $image->extension();
                $file_extImage = $image->extension();
                $folder_upload = 'images/RequestFile';
                $resourceImage->move($folder_upload, $nameImage);


                $data = [
                    'image' =>  $nameImage,
                    'absence_request_id' => $requests_id,
                    'type' => 'request'
                ];
                $data = AbsenceRequestLogs::create($data);
            }


            // untuk Notif start
            $admin = Staff::selectRaw('users.*')->where('staffs.id',  $dataForm->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
            $id_onesignal = $admin->_id_onesignal;
            // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
            //wa notif                
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user

            if ($dataForm->category == "visit") {
                $categoryName = "Dinas Dalam";
            } else if ($dataForm->category == "duty") {
                $categoryName = "Dinas Luar";
            } else if ($dataForm->category == "permission") {
                $categoryName = "Izin";
            } else if ($dataForm->category == "excuse") {
                $categoryName = "Permisi";
            } else if ($dataForm->category == "geolocation_off") {
                $categoryName = "Absen Diluar";
            } else if ($dataForm->category == "extra") {
                $categoryName = "Lembur";
            } else if ($dataForm->category == "leave") {
                $categoryName = "Cuti";
            } else if ($dataForm->category == "location") {
                $categoryName = "Pindah Lokasi";
            } else if ($dataForm->category == "forget") {
                $categoryName = "Lupa Absen";
            } else if ($dataForm->category == "AdditionalTime") {
                $categoryName = "Penambahan Waktu Kerja";
            } else {
                $categoryName = "";
            }

            $phone_no = $admin->phone;
            $message = "Pengajuan " . $categoryName . " oleh " . $admin->name;
            $wa_data = [
                'phone' => $this->gantiFormat($phone_no),
                'customer_id' => null,
                'message' => $message,
                'template_id' => '',
                'status' => 'gagal',
                'ref_id' => $wa_code,
                'created_at' => date('Y-m-d h:i:sa'),
                'updated_at' => date('Y-m-d h:i:sa')
            ];
            $wa_data_group[] = $wa_data;
            DB::table('wa_histories')->insert($wa_data);
            $wa_sent = WablasTrait::sendText($wa_data_group);
            $array_merg = [];
            if (!empty(json_decode($wa_sent)->data->messages)) {
                $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
            }
            foreach ($array_merg as $key => $value) {
                if (!empty($value->ref_id)) {
                    wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                }
            }

            // //onesignal notif                                
            if (!empty($id_onesignal)) {
                OneSignal::sendNotificationToUser(
                    $message,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }
            // // untuk notif end

            //send notif to admin
            $bagian = Staff::selectRaw('users.*')->where('staffs.id',  $dataForm->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
            $admin_arr = User::where('dapertement_id', $bagian->dapertement_id)
                ->where('subdapertement_id', 0)
                ->where('staff_id', 0)->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
                //wa notif                
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff->phone;
                } else {
                    $phone_no = $admin->phone;
                }
                $wa_data = [
                    'phone' => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message' => $message,
                    'template_id' => '',
                    'status' => 'gagal',
                    'ref_id' => $wa_code,
                    'created_at' => date('Y-m-d h:i:sa'),
                    'updated_at' => date('Y-m-d h:i:sa')
                ];
                $wa_data_group[] = $wa_data;
                DB::table('wa_histories')->insert($wa_data);
                $wa_sent = WablasTrait::sendText($wa_data_group);
                $array_merg = [];
                if (!empty(json_decode($wa_sent)->data->messages)) {
                    $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
                }
                foreach ($array_merg as $key => $value) {
                    if (!empty($value->ref_id)) {
                        wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                    }
                }
                //onesignal notif                                
                if (!empty($id_onesignal)) {
                    OneSignal::sendNotificationToUser(
                        $message,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );
                }
            }

            return response()->json([
                'message' => 'Pengajuan Terkirim',
                'data' => $requests,
            ]);
        } else {
            return response()->json([
                'message' => $error,
                'data' => '',
            ]);
        }
    }

    public function update(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');
        // $code = acc_code_generate($last_code, 8, 3);
        $dataForm = json_decode($request->form);

        if ($request->file('imageP')) {
            $image = $request->file('imageP');
            $resourceImage = $image;
            $nameImage = 'imageP' . date('Y-m-d h:i:s') . '.' . $image->extension();
            $file_extImage = $image->extension();
            $folder_upload = 'images/RequestFile';
            $resourceImage->move($folder_upload, $nameImage);

            // dd($request->file('old_image')->move($folder_upload, $img_name));

            // if ($actionWm->old_image != '') {
            //     foreach (json_decode($actionWm->old_image) as $n) {
            //         if (file_exists($n)) {

            //             unlink($basepath . $n);
            //         }
            //     }
            // }
            $data = [
                'image' => $nameImage,
                'absence_request_id' => $dataForm->id,
                'type' => 'approve'
            ];
            $data = AbsenceRequestLogs::create($data);
        }

        if ($request->file('imagePng')) {
            $image = $request->file('imagePng');
            $resourceImage = $image;
            $nameImage = 'imagePng' . date('Y-m-d h:i:s') . '.' . $image->extension();
            $file_extImage = $image->extension();
            $folder_upload = 'images/RequestFile';
            $resourceImage->move($folder_upload, $nameImage);


            $data = [
                'image' =>  $nameImage,
                'absence_request_id' => $dataForm->id,
                'type' => 'request'
            ];
            $data = AbsenceRequestLogs::create($data);
        }

        return response()->json([
            'message' => 'Pengajuan Terkirim',
        ]);
    }

    public function history(Request $request)
    {
        $requests = AbsenceRequest::where('staff_id', $request->staff_id)
            ->FilterDate($request->from, $request->to)
            ->orderBy('created_at', 'DESC')
            ->paginate(3, ['*'], 'page', $request->page);
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $requests,
        ]);
    }

    public function imageDelete($id)
    {
        $requests = AbsenceRequestLogs::where('id', $id)->delete();
        return response()->json([
            'message' => 'Bukti Dihapus',
            'id' => $id,
            'data' => $requests,
        ]);
    }

    public function getPermissionCat(Request $request)
    {
        $cat = [
            ['id' => 'sick', 'name' => 'sakit', 'checked' => false],
            ['id' => 'other', 'name' => 'Lain-Lain', 'checked' => false],
        ];
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $cat,
        ]);
    }

    public function listFile(Request $request)
    {
        $file = AbsenceRequestLogs::selectRaw('image, id')->where('absence_request_id', $request->id)->get();
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $file,
            '$s' => $request->id
        ]);
    }

    // mungkin tidak dipakai
    public function absenceList(Request $request)
    {
        $duty = Requests::where('category', 'duty')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->get();
        $extra = Requests::where('category', 'extra')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->get();
        $permit = Requests::where('category', 'permit')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->get();

        return response()->json([
            'message' => 'Succes',
            'duty' => $duty,
            'extra' => $extra,
            'permit' => $permit,
        ]);
    }

    // untuk admin start

    public function menuAdmin(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        $checker = [];
        $users = user::with(['roles'])
            ->where('id', $request->id)
            ->first();
        foreach ($users->roles as $data) {
            foreach ($data->permissions as $data2) {
                $checker[] = $data2->title;
            }
        }
        if (in_array('absence_all_access', $checker)) {
            $absence_request_count =  AbsenceRequest::selectRaw('COUNT(CASE WHEN category = "visit" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS visit_count')
                ->selectRaw('COUNT(CASE WHEN category = "duty" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS duty_count')
                ->selectRaw('COUNT(CASE WHEN category = "excuse" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS excuse_count')
                ->selectRaw('COUNT(CASE WHEN category = "extra" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS extra_count')
                ->selectRaw('COUNT(CASE WHEN category = "leave" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS leave_count')
                ->selectRaw('COUNT(CASE WHEN category = "geolocation_off" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS geolocation_off_count')
                ->selectRaw('COUNT(CASE WHEN category = "permission" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS permission_count')
                ->selectRaw('COUNT(CASE WHEN category = "location" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS location_count')
                ->selectRaw('COUNT(CASE WHEN category = "forget" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS forget_count')
                ->selectRaw('COUNT(CASE WHEN category = "AdditionalTime" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS AdditionalTime_count')
                ->first();

            $shift_change = ShiftChange::selectRaw('count(shift_changes.id) as total')
                ->join('shift_planner_staffs as s1', 's1.id', '=', 'shift_changes.shift_id')
                ->join('staffs as st1', 'st1.id', '=', 'shift_changes.staff_id')
                ->join('shift_groups as sh1', 'sh1.id', '=', 's1.shift_group_id')
                ->join('shift_planner_staffs as s2', 's2.id', '=', 'shift_changes.shift_change_id')
                ->join('staffs as st2', 'st2.id', '=', 'shift_changes.staff_change_id')
                ->join('shift_groups as sh2', 'sh2.id', '=', 's2.shift_group_id')
                ->whereDate('shift_changes.created_at', '>=', date('Y-m-d'))
                ->whereDate('s1.start', '>=', date('Y-m-d'))
                ->whereDate('s2.start', '>=', date('Y-m-d'))
                ->where('shift_changes.status', 'pending')
                ->orderBy('shift_changes.created_at', 'ASC')
                ->first();
        } else {
            $absence_request_count =  AbsenceRequest::selectRaw('COUNT(CASE WHEN category = "visit" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS visit_count')
                ->selectRaw('COUNT(CASE WHEN category = "duty" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS duty_count')
                ->selectRaw('COUNT(CASE WHEN category = "excuse" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS excuse_count')
                ->selectRaw('COUNT(CASE WHEN category = "extra" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS extra_count')
                ->selectRaw('COUNT(CASE WHEN category = "leave" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS leave_count')
                ->selectRaw('COUNT(CASE WHEN category = "geolocation_off" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS geolocation_off_count')
                ->selectRaw('COUNT(CASE WHEN category = "permission" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS permission_count')
                ->selectRaw('COUNT(CASE WHEN category = "location" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS location_count')
                ->selectRaw('COUNT(CASE WHEN category = "forget" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS forget_count')
                ->selectRaw('COUNT(CASE WHEN category = "AdditionalTime" and status = "pending" and absence_requests.created_at >= "' . date('Y-m-d') . '" THEN 1 END) AS AdditionalTime_count')
                ->join('staffs', 'staffs.id', '=', 'absence_requests.staff_id')
                ->where('dapertement_id', $user->dapertement_id)
                ->first();

            $shift_change = ShiftChange::selectRaw('count(shift_changes.id) as total')
                ->join('shift_planner_staffs as s1', 's1.id', '=', 'shift_changes.shift_id')
                ->join('staffs as st1', 'st1.id', '=', 'shift_changes.staff_id')
                ->join('shift_groups as sh1', 'sh1.id', '=', 's1.shift_group_id')
                ->join('shift_planner_staffs as s2', 's2.id', '=', 'shift_changes.shift_change_id')
                ->join('staffs as st2', 'st2.id', '=', 'shift_changes.staff_change_id')
                ->join('shift_groups as sh2', 'sh2.id', '=', 's2.shift_group_id')
                ->FilterDapertement($user->dapertement_id)
                // ->whereDate('shift_changes.created_at', '>=', date('Y-m-d'))
                ->whereDate('s1.start', '>=', date('Y-m-d'))
                ->whereDate('s2.start', '>=', date('Y-m-d'))
                ->where('shift_changes.status', 'pending')
                ->orderBy('shift_changes.created_at', 'ASC')
                ->first();
        }



        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $absence_request_count->setAttribute('change_shift', $shift_change->total)
            // 'change_shift' => 
        ]);
    }

    public function requestApprove(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        // return response()->json([
        //     'message' => 'Pengajuan Terkirim',
        //     'data' =>  $user,
        // ]);
        // AbsenceRequest::selectRaw('COUNT(CASE WHEN category = "visit" THEN 1 END) AS visit_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "duty" THEN 1 END) AS duty_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "excuse" THEN 1 END) AS excuse_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "extra" THEN 1 END) AS extra_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "leave" THEN 1 END) AS leave_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "geolocaation_off" THEN 1 END) AS geolocaation_off_count')
        //     ->selectRaw('COUNT(CASE WHEN category = "permission" THEN 1 END) AS permission_count');
        $checker = [];
        $users = user::with(['roles'])
            ->where('id', $request->id)
            ->first();
        foreach ($users->roles as $data) {
            foreach ($data->permissions as $data2) {
                $checker[] = $data2->title;
            }
        }
        if (in_array('absence_all_access', $checker)) {
            $requests = AbsenceRequest::select('absence_requests.*', 'staffs.name as staff_name')->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
                // ->FilterDapertement($user->dapertement_id)
                ->FilterDate($request->from, $request->to)
                ->where('category', $request->category)
                ->orderBy('created_at', 'DESC')
                ->paginate(3, ['*'], 'page', $request->page);
            return response()->json([
                'message' => 'Pengajuan Terkirim',
                'data' => $requests,
            ]);
        } else {
            $requests = AbsenceRequest::select('absence_requests.*', 'staffs.name as staff_name')->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
                ->FilterDapertement($user->dapertement_id)
                ->FilterDate($request->from, $request->to)
                ->where('category', $request->category)
                ->orderBy('created_at', 'DESC')
                ->paginate(3, ['*'], 'page', $request->page);
            return response()->json([
                'message' => 'Pengajuan Terkirim',
                'data' => $requests,
            ]);
        }
    }

    // untuk admin end

    public function approve(Request $request)
    {


        $requests = AbsenceRequest::selectRaw('absence_requests.*,work_types.id as work_type_id, work_types.type as type, staffs.id as staff_id')
            ->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
            ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
            ->where('absence_requests.id', $request->id)->first();

        $d = AbsenceRequest::where('id', $request->id)->first();
        //     ->update(
        //         [
        //             'status' => 'approve'
        //         ]
        //     );
        if (date('Y-m-d') <= date('Y-m-d', strtotime($d->start))) {
            // jika lupa absen start
            if ($requests->category == "forget" || $requests->category == "AdditionalTime") {



                $cek_absen = Absence::with(['absence_logs', 'staffs'])
                    ->whereDate('created_at', date('Y-m-d'))
                    ->where('staff_id', $d->staff_id)
                    ->orderBy('id', 'DESC')
                    ->first();
                // dd($cek_absen);
                if ($cek_absen) {
                    // untuk reguler
                    if ($cek_absen->staffs->work_type_id != 2) {
                        $get_absence =  Absence::with(['absence_logs', 'absence_logs.workTypeDays', 'staffs'])
                            ->where('id', $cek_absen->id)
                            ->first();
                        $time_cek  = $get_absence->absence_logs->where('absence_category_id', 2)->where('status', '1')->first();
                        $time  = $get_absence->absence_logs->where('absence_category_id', 1)->where('status', '0')->first();
                        // dd($get_absence, $time_cek, $time);
                        if ($time_cek && $time) {
                            $penambahan_durasi = date("Y-m-d H:i:s", strtotime('+ ' . ($time->workTypeDays->duration * 60) . ' minutes', strtotime(date('Y-m-d ' . $time->workTypeDays->time))));
                            // dd($penambahan_durasi);
                            if ($penambahan_durasi < date('Y-m-d H:i:s')) {
                                AbsenceLog::where('id',  $time_cek->id)
                                    ->update([
                                        // 'expired_date' => date('Y-m-d 23:59:59')
                                        'expired_date' => date("Y-m-d 23:59:59", strtotime(date('Y-m-d', strtotime($time_cek->expired_date)))),
                                        'absence_request_id' => $request->id
                                    ]);
                            } else if ($requests->category == "AdditionalTime") {
                                AbsenceLog::where('id',  $time_cek->id)
                                    ->update([
                                        // 'expired_date' => date('Y-m-d 23:59:59')
                                        'expired_date' => date("Y-m-d 23:59:59", strtotime(date('Y-m-d', strtotime($time_cek->expired_date)))),
                                        'absence_request_id' => $request->id
                                    ]);
                            }
                            // dd('cek');
                        } else {
                        }
                        // dd($time_cek);
                    } else {
                        // dd($shiftGroupTimeSheets->duration);
                        $get_absence =  Absence::with(['absence_logs', 'absence_logs.shiftGroupTimeSheets', 'staffs'])
                            ->where('id',  $cek_absen->id)
                            ->first();
                        $time_cek  = $get_absence->absence_logs->where('absence_category_id', 2)->where('status', '1')->first();
                        $time  = $get_absence->absence_logs->where('absence_category_id', 1)->where('status', '0')->first();
                        if ($time_cek && $time) {
                            $penambahan_durasi = date("Y-m-d H:i:s", strtotime('+ ' . ($time->shiftGroupTimeSheets->duration * 60) . ' minutes', strtotime(date('Y-m-d ' . $time->shiftGroupTimeSheets->time))));

                            if ($penambahan_durasi > date('Y-m-d H:i:s')) {
                                AbsenceLog::where('id',  $time_cek->id)
                                    ->update([
                                        // 'expired_date' => date('Y-m-d 23:59:59') 
                                        'expired_date' => date("Y-m-d 23:59:59", strtotime(date('Y-m-d', strtotime($time_cek->expired_date)))),
                                        'absence_request_id' => $request->id
                                    ]);
                            } else if ($requests->category == "AdditionalTime") {
                                AbsenceLog::where('id',  $time_cek->id)
                                    ->update([
                                        // 'expired_date' => date('Y-m-d 23:59:59')
                                        'expired_date' => date("Y-m-d 23:59:59", strtotime(date('Y-m-d', strtotime($time_cek->expired_date)))),
                                        'absence_request_id' => $request->id
                                    ]);
                            }
                        } else {
                        }
                    }
                }
            }


            // jika lupa absen end


            // jika dinas luar/izin/lembur start
            if ($requests->category == "permission" || $requests->category == "duty" || $requests->category == "leave") {

                $d = AbsenceRequest::where('id', $request->id)
                    ->update(['status' => 'approve']);
                $d = AbsenceRequest::where('id', $request->id)->first();

                // buat absence log start

                $absenceRequest =  AbsenceRequest::select(
                    'absence_requests.*',
                    DB::raw('DATE(start) as start'),
                    DB::raw('DATE(end) as end'),
                )
                    ->where('id', $request->id)->first();
                // dd($absenceRequest);
                if ($requests->category == "permission") {
                    $message = "Izin anda tanggal " . $d->start . " disetujui";
                } else if ($requests->category == "duty") {
                    $message = "Dinas anda tanggal " . $d->start . " sampai dengan " . $d->end . " disetujui";
                } else if ($requests->category == "leave") {
                    $message = "Cuti anda tanggal " . $d->start . " sampai dengan " . $d->end . " disetujui";
                } else {
                    $message = "";
                }
                // if (date('Y-m-d') >= $absenceRequest->start) {
                $begin = strtotime($absenceRequest->start);
                $end   = strtotime($absenceRequest->end);
                // return response()->json([
                //     'message' => 'Pengajuan Terkirim',
                //     'begin' => $begin,
                //     'end' => $end
                // ]);
                // dd();

                if ($requests->category != "duty") {
                    if ($requests->type != "shift") {

                        // libur nasional
                        // $holidays = Holiday::select(DB::raw('DATE(holidays.start) AS start'), DB::raw('DATE(holidays.end) AS end'))
                        //     //  ->whereBetween(DB::raw('DATE(holidays.start)'), [$from, $to])
                        //     //  ->orWhereBetween(DB::raw('DATE(holidays.end)'), [$from, $to])
                        //     ->get();
                        // // dd($holidays);
                        // foreach ($holidays as $holiday) {
                        //     $awal_libur = date_create_from_format('Y-m-d', $holiday->start);
                        //     $awal_libur = date_format($awal_libur, 'Y-m-d');
                        //     $awal_libur = strtotime($awal_libur);

                        //     $akhir_libur = date_create_from_format('Y-m-d', $holiday->end);
                        //     $akhir_libur = date_format($akhir_libur, 'Y-m-d');
                        //     $akhir_libur = strtotime($akhir_libur);

                        //     $work_type_days = Day::select('days.*')->leftJoin(
                        //         'work_type_days',
                        //         function ($join) use ($requests) {
                        //             $join->on('days.id', '=', 'work_type_days.day_id')
                        //                 ->where('work_type_id', $requests->work_type_id);
                        //         }
                        //     )
                        //         ->where('work_type_days.day_id', '=', null)->get();
                        // }
                        $category_absence = "";
                        if ($requests->category == "permission") {
                            $category_absence = 13;
                        } else if ($requests->category == "leave") {
                            $category_absence = 8;
                        } else {
                            // $category_absence = 7;
                        }

                        $work_type_days = Day::select('days.*')->leftJoin(
                            'work_type_days',
                            function ($join) use ($requests) {
                                $join->on('days.id', '=', 'work_type_days.day_id')
                                    ->where('work_type_id', $requests->work_type_id);
                            }
                        )
                            ->where('work_type_days.day_id', '=', null)->get();
                        // dd($work_type_days);
                        // dd($work_type_days);
                        $jadwallibur = [];
                        foreach ($work_type_days as $work_type_day) {
                            $jadwallibur = array_merge($jadwallibur, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
                        }
                        $ab_id = [];
                        for ($i = $begin; $i <= $end; $i = $i + 86400) {
                            $holiday = Holiday::whereDate(DB::raw('DATE(start)'), '<=', date('Y-m-d', $i))->whereDate(DB::raw('DATE(end)'), '>=', date('Y-m-d', $i))->first();

                            // if (!$holiday) {
                            // if ((!in_array(date('w', $i), $jadwallibur))) {
                            $check_empty = Absence::where('staff_id', $absenceRequest->staff_id)->whereDate('created_at', '=', date('Y-m-d', $i))->first();
                            if (!$check_empty) {
                                // dd('test');
                                // $ab_id[] = [
                                //     'day_id' => date("w", strtotime(date('Y-m-d', $i))),
                                //     'staff_id' => $absenceRequest->staff_id,
                                //     'created_at' => date('Y-m-d H:i:s', $i),
                                //     'updated_at' => date('Y-m-d H:i:s')
                                // ];
                                $ab_id = Absence::create([
                                    'day_id' => date("w", strtotime(date('Y-m-d', $i))),
                                    'staff_id' => $absenceRequest->staff_id,
                                    'created_at' => date('Y-m-d H:i:s', $i),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                                AbsenceLog::create([
                                    'absence_category_id' => $category_absence,
                                    'lat' => '',
                                    'lng' => '',
                                    'absence_request_id' => $absenceRequest->id,
                                    'register' => date('Y-m-d', $i),
                                    'absence_id' => $ab_id->id,
                                    'duration' => '',
                                    'status' => ''
                                ]);
                            }
                            // }
                            // dd($holiday);

                            // }
                            // if ($holiday) {
                            //     $hd[] = ['test1' => $holiday->start, 'test1' => $holiday->end];
                            // }
                            // $id[] = [date('Y-m-d', $i)];
                        }
                        // return response()->json([
                        //     'message' => 'Pengajuan Terkirim',
                        //     'begin' => $ab_id,
                        //     'holiday' => $hd,
                        //     'i' => $id
                        // ]);
                    } else {
                        $shift_planners = ShiftPlannerStaffs::select('shift_planner_staffs.*', DB::raw('DATE(shift_planner_staffs.start) as start'))->where('staff_id',  $requests->staff_id)
                            // ->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$from, $to])
                            ->get();

                        $category_absence = "";
                        if ($requests->category == "permission") {
                            $category_absence = 13;
                        } else if ($requests->category == "leave") {
                            $category_absence = 8;
                        } else {
                            // $category_absence = 7;
                        }

                        $jadwalmasuk = [];
                        foreach ($shift_planners as $shift_planner) {
                            $jadwalmasuk = array_merge($jadwalmasuk, [$shift_planner->start]);
                        }

                        // for ($i = $begin; $i <= $end; $i = $i + 86400) {
                        $holiday = Holiday::whereDate('start', '<=', date('Y-m-d', $i))->whereDate('end', '>=', date('Y-m-d', $i))->first();
                        // if (!$holiday) {
                        if ((in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $jadwalmasuk))) {
                            // dd('test');
                            $check_empty = Absence::where('staff_id', $absenceRequest->staff_id)->whereDate('created_at', '=', date('Y-m-d', $i))->first();
                            if (!$check_empty) {
                                $ab_id = Absence::create([
                                    'day_id' => date("w", strtotime(date('Y-m-d', $i))),
                                    'staff_id' => $absenceRequest->staff_id,
                                    'created_at' => date('Y-m-d H:i:s', $i),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                                AbsenceLog::create([
                                    'absence_category_id' => $category_absence,
                                    'lat' => '',
                                    'lng' => '',
                                    'absence_request_id' => $absenceRequest->id,
                                    'register' => date('Y-m-d', $i),
                                    'absence_id' => $ab_id->id,
                                    'duration' => '',
                                    'status' => ''
                                ]);
                            }

                            // }
                        }
                        // dd($holiday);
                        // }
                        // }
                    }
                    // return $jadwallibur;

                    // dd('hhh');
                    // buat absence log end
                    $message = "Izin anda tanggal " . $d->start . " sampai dengan " . $d->end . " diterima";
                    MessageLog::create([
                        'staff_id' => $d->staff_id,
                        'memo' => $message,
                        'type' => 'message',
                        'status' => 'pending',
                    ]);

                    // untuk Notif start
                    $admin = Staff::selectRaw('users.*')->where('staffs.id', $d->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
                    $id_onesignal = $admin->_id_onesignal;
                    // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
                    //wa notif                
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($d->staff_id > 0) {
                        $staff = Staff::where('id', $d->staff_id)->first();
                        $phone_no = $staff->phone;
                    } else {
                        $phone_no = $admin->phone;
                    }
                    $wa_data = [
                        'phone' => $this->gantiFormat($phone_no),
                        'customer_id' => null,
                        'message' => $message,
                        'template_id' => '',
                        'status' => 'gagal',
                        'ref_id' => $wa_code,
                        'created_at' => date('Y-m-d h:i:sa'),
                        'updated_at' => date('Y-m-d h:i:sa')
                    ];
                    $wa_data_group[] = $wa_data;
                    DB::table('wa_histories')->insert($wa_data);
                    $wa_sent = WablasTrait::sendText($wa_data_group);
                    $array_merg = [];
                    if (!empty(json_decode($wa_sent)->data->messages)) {
                        $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
                    }
                    foreach ($array_merg as $key => $value) {
                        if (!empty($value->ref_id)) {
                            wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                        }
                    }

                    //onesignal notif                                
                    if (!empty($id_onesignal)) {
                        OneSignal::sendNotificationToUser(
                            $message,
                            $id_onesignal,
                            $url = null,
                            $data = null,
                            $buttons = null,
                            $schedule = null
                        );
                    }
                }
                // untuk notif end
            } else {
                $d = AbsenceRequest::where('id', $request->id)
                    ->update(['status' => 'approve']);

                $d = AbsenceRequest::where('id', $request->id)->first();
                // dd($d);
                $message = "Permisi anda tanggal " . $d->start . " disetujui";
                if ($d->category == "visit") {
                    $message = "Dinas anda tanggal " . $d->start . " disetujui";
                } else if ($d->category == "excuse") {
                    $message = "Permisi anda tanggal " . $d->start . " disetujui";
                } else if ($d->category == "extra") {
                    $message = "Lembur anda tanggal " . $d->start . " disetujui";
                } else if ($d->category == "forget") {
                    $message = "Lupa Absen anda tanggal " . $d->start . " disetujui";
                } else if ($d->category == "location") {
                    $message = "Pindah Lokasi anda tanggal " . $d->start . " disetujui";
                } else if ($d->category == "AdditionalTime") {
                    $message = "Penambahan Jam Kerja anda tanggal " . $d->start . " disetujui";
                } else {
                    $message = "geolocation off disetujui";
                }
                MessageLog::create([
                    'staff_id' => $d->staff_id,
                    'memo' => $message,
                    'type' => 'message',
                    'status' => 'pending',
                ]);
                // dd($message);

                // untuk Notif start
                $admin = Staff::selectRaw('users.*')->where('staffs.id', $d->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
                $id_onesignal = $admin->_id_onesignal;
                // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
                //wa notif                
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($d->staff_id > 0) {
                    $staff = Staff::where('id', $d->staff_id)->first();
                    $phone_no = $staff->phone;
                } else {
                    $phone_no = $admin->phone;
                }
                $wa_data = [
                    'phone' => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message' => $message,
                    'template_id' => '',
                    'status' => 'gagal',
                    'ref_id' => $wa_code,
                    'created_at' => date('Y-m-d h:i:sa'),
                    'updated_at' => date('Y-m-d h:i:sa')
                ];
                $wa_data_group[] = $wa_data;
                DB::table('wa_histories')->insert($wa_data);
                $wa_sent = WablasTrait::sendText($wa_data_group);
                $array_merg = [];
                if (!empty(json_decode($wa_sent)->data->messages)) {
                    $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
                }
                foreach ($array_merg as $key => $value) {
                    if (!empty($value->ref_id)) {
                        wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
                    }
                }

                //onesignal notif                                
                if (!empty($id_onesignal)) {
                    OneSignal::sendNotificationToUser(
                        $message,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );
                }
                // untuk notif end
            }
        }


        // jika dinas luar/izin/lembur end




        return response()->json([
            'message' => 'Berhasil Disetujui',
            // 'id' => $id,
            'data' => $requests,
        ]);
    }

    public function reject(Request $request)
    {
        $d = AbsenceRequest::where('id', $request->id)
            ->update(['status' => 'reject']);
        $d = AbsenceRequest::where('id', $request->id)->first();
        if ($request->id) {
            $absenceLog = AbsenceLog::where('absence_request_id', $request->id)->get();
            foreach ($absenceLog as $da) {
                $deleteAbsence = Absence::where('id', $da->absence_id)->first();
                if ($deleteAbsence) {
                    Absence::where('id', $da->absence_id)->delete();
                }
            }


            AbsenceLog::where('absence_request_id', $request->id)->delete();
        }
        if ($d->category == "permission") {
            $message = "Izin anda tanggal " . $d->start . " ditolak";
        } else if ($d->category == "duty") {
            $message = "Dinas anda tanggal " . $d->start . " sampai dengan " . $d->end . " ditolak";
        } else if ($d->category == "leave") {
            $message = "Cuti anda tanggal " . $d->start . " sampai dengan " . $d->end . " ditolak";
        } else {
            $message = "";
        }
        MessageLog::create([
            'staff_id' => $d->staff_id,
            'memo' => $message,
            'type' => 'message',
            'status' => 'pending',
        ]);

        // untuk Notif start
        $admin = Staff::selectRaw('users.*')->where('staffs.id', $d->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
        $id_onesignal = $admin->_id_onesignal;
        // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
        //wa notif                
        $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $wa_data_group = [];
        //get phone user
        if ($d->staff_id > 0) {
            $staff = Staff::where('id', $d->staff_id)->first();
            $phone_no = $staff->phone;
        } else {
            $phone_no = $admin->phone;
        }
        $wa_data = [
            'phone' => $this->gantiFormat($phone_no),
            'customer_id' => null,
            'message' => $message,
            'template_id' => '',
            'status' => 'gagal',
            'ref_id' => $wa_code,
            'created_at' => date('Y-m-d h:i:sa'),
            'updated_at' => date('Y-m-d h:i:sa')
        ];
        $wa_data_group[] = $wa_data;
        DB::table('wa_histories')->insert($wa_data);
        $wa_sent = WablasTrait::sendText($wa_data_group);
        $array_merg = [];
        if (!empty(json_decode($wa_sent)->data->messages)) {
            $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
        }
        foreach ($array_merg as $key => $value) {
            if (!empty($value->ref_id)) {
                wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
            }
        }

        //onesignal notif                                
        if (!empty($id_onesignal)) {
            OneSignal::sendNotificationToUser(
                $message,
                $id_onesignal,
                $url = null,
                $data = null,
                $buttons = null,
                $schedule = null
            );
        }
        // untuk notif end

        return response()->json([
            'message' => 'Berhasil Ditolak',
            // 'id' => $id,
            'data' => $d,
        ]);
    }

    public function show(Request $request)
    {

        $requests = AbsenceRequest::selectRaw('absence_requests.*,work_types.id as work_type_id, work_types.type as type, staffs.id as staff_id')
            ->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
            ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
            ->where('absence_requests.id', $request->id)->first();

        $request_file = AbsenceRequestLogs::where('absence_request_id', $request->id)->get();

        return response()->json([
            'message' => 'Bukti Dihapus',
            'data' => $requests,
            'data2' => $request_file,
        ]);
    }

    public function getLocation()
    {
        $data = [];
        $datas = WorkUnit::get();
        foreach ($datas as $key => $value) {
            # code...
            $data[] = ['id' => $value->id, 'name' => $value->name, 'checked' => false];
        }
        // $data = [
        //     ['id' => 'sick', 'name' => 'sakit', 'checked' => false],
        //     ['id' => 'other', 'name' => 'Lain-Lain', 'checked' => false],
        // ];
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $data,
        ]);
    }

    public function closeLocation(Request $request)
    {
        $d = AbsenceRequest::where('id', $request->id)
            ->update(['status' => 'close']);
        // $data = [
        //     ['id' => 'sick', 'name' => 'sakit', 'checked' => false],
        //     ['id' => 'other', 'name' => 'Lain-Lain', 'checked' => false],
        // ];
        return response()->json([
            'message' => 'Pindah lokasi ditutup',
        ]);
    }
}
