<?php

namespace App\Http\Controllers\Admin;

use App\Absence;
use App\Exports\AbsenceExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Absence_categories;
use App\AbsenceLog;
use App\AbsenceProblem;
use App\Dapertement;
use App\Day;
use App\Exports\AbsenceAccuracy;
use App\Exports\AbsenceReport;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\Requests;
use App\ShiftPlannerStaff;
use App\ShiftPlannerStaffs;
use App\ShiftStaff;
use App\Staff;
use App\User;
use App\WorkTypeDays;
use App\WorkTypes;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {

        // $start1 = date("Y-m-d H:i:s", strtotime('12-04-2023' . '14:00:00'));
        // $day = date("w", strtotime('12-04-2023'));
        // $staff = Staff::selectRaw('staffs.*,work_types.type as work_type, work_types.id as work_type_id ')->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
        //     ->where('staffs.id', '407')->first();
        // // dd($staff);
        // if ($staff->work_type == "shift") {
        //     $jumShift = ShiftPlannerStaffs::whereDate('shift_planner_staffs.start', '=', '2023-04-12')
        //         ->where('staff_id', $staff->id)
        //         ->get();
        //     foreach ($jumShift as $data) {
        //         $absence = ShiftPlannerStaffs::join('shift_groups', 'shift_groups.id', '=', 'shift_planner_staffs.shift_group_id')
        //             ->join('shift_group_timesheets', 'shift_groups.id', '=', 'shift_group_timesheets.shift_group_id')
        //             ->where('shift_group_timesheets.absence_category_id', '1')
        //             ->where('shift_groups.id', $data->shift_group_id)
        //             ->where('staff_id', $staff->id)
        //             ->whereDate('shift_planner_staffs.start', '=', '2023-04-12')
        //             ->orWhere('shift_group_timesheets.absence_category_id', '2')
        //             ->where('shift_groups.id', $data->shift_group_id)
        //             ->where('staff_id', $staff->id)
        //             ->whereDate('shift_planner_staffs.start', '=', '2023-04-12')
        //             ->orderBy('shift_group_timesheets.absence_category_id', 'ASC')
        //             ->get();

        //         if ($absence[0]->time > $absence[1]->time) {
        //             $masuk = date("Y-m-d H:i:s", strtotime('12-04-2023' . $absence[0]->time));
        //             $pulang = date("Y-m-d H:i:s", strtotime('+ ' . '1' . ' days', strtotime('12-04-2023' . $absence[1]->time)));
        //             //  date("Y-m-d H:i:s", strtotime());
        //         } else {
        //             $masuk = date("Y-m-d H:i:s", strtotime('12-04-2023' . $absence[0]->time));
        //             $pulang = date("Y-m-d H:i:s", strtotime('12-04-2023' . $absence[1]->time));
        //         }


        //         // dd($masuk, $start1, $pulang, $absence);
        //         if ($start1 > $masuk && $start1 < $pulang) {
        //             return json_encode(
        //                 [
        //                     'message' => 'anda tidak bisa melakukan lembur di jam kerja'
        //                 ]
        //             );
        //         }
        //     }
        // } else {
        //     $absence = WorkTypeDays::selectRaw('time')
        //         ->where('work_type_id', $staff->work_type_id)
        //         ->where('day_id', $day != "0" ? $day : "7")
        //         ->where('work_type_days.absence_category_id', '2')
        //         ->orWhere('work_type_days.absence_category_id', '1')
        //         ->where('work_type_id', $staff->work_type_id)
        //         ->where('day_id', $day != "0" ? $day : "7")
        //         ->orderBy('work_type_days.absence_category_id', 'ASC')
        //         ->get();
        //     $masuk = date("Y-m-d H:i:s", strtotime('12-04-2023' . $absence[0]->time));
        //     $pulang = date("Y-m-d H:i:s", strtotime('12-04-2023' . $absence[1]->time));
        //     // dd($masuk, $start1, $pulang);
        //     if ($start1 > $masuk && $start1 < $pulang) {
        //         return json_encode(
        //             [
        //                 'message' => 'anda tidak bisa melakukan lembur di jam kerja'
        //             ]
        //         );
        //     }
        // }

        // dd($absence);

        // $str_date = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . '02:00:00')));
        // $exp_date = date("Y-m-d H:i:s", strtotime(date('Y-m-d ' . '10:00:00')));
        // $i = 0;
        // while ($str_date < $exp_date) {

        //     $str_date = date("Y-m-d H:i:s", strtotime('+ ' . $i * 60 . ' minutes', strtotime($str_date)));
        //     $d[] = [$str_date];
        //     $i = +1;
        // }
        // dd($d);
        // $t = AbsenceProblem::get();
        // dd($t);
        // $test = AbsenceLog::select(
        //     'staffs.name',
        //     'users.device',
        //     DB::raw('AVG(accuracy) as avg_accuracy'),
        //     DB::raw('MAX(accuracy) as max_accuracy'),
        //     DB::raw('MIN(accuracy) as min_accuracy'),
        //     DB::raw('AVG(distance) as avg_distance'),
        //     DB::raw('MIN(distance) as max_distance'),
        //     DB::raw('MAX(distance) as min_distance')
        // )
        //     ->join('absences', 'absences.id', '=', 'absence_logs.absence_id')
        //     ->join('staffs', 'staffs.id', '=', 'absences.staff_id')
        //     ->join('users', 'users.staff_id', '=', 'staffs.id')
        //     ->groupBy('staffs.id')
        //     ->get();
        // // dd($test);
        // $data12 = [];
        // foreach ($test as $t) {
        //     $data12[] = [
        //         'name' => $t->name,
        //         'device' => $t->device,
        //         'avg_accuracy' => $t->avg_accuracy,
        //         'max_accuracy' => $t->max_accuracy,
        //         'min_accuracy' => $t->min_accuracy,
        //         'avg_distance' => $t->avg_distance,
        //         'max_distance' => $t->max_distance,
        //         'min_distance' => $t->min_distance,
        //     ];
        // }
        // dd($test12);

        // $accuracy = AbsenceLog::select('accuracy', 'distance', 'absence_logs.id', 'status_active', 'staff_id', 'staffs.name')
        //     ->join('absences', 'absences.id', '=', 'absence_logs.absence_id')
        //     ->join('staffs', 'staffs.id', '=', 'absences.staff_id')
        //     // ->where('staffs.id', '437')
        //     ->whereDate('absences.created_at', '2023-04-17')
        //     ->groupBy('absences.id')
        //     ->orderBy('accuracy', 'DESC')
        //     ->get();
        // $r = [];
        // foreach ($accuracy as $acc) {
        //     $r[] = [
        //         'id' => $acc->id,
        //         'accuracy' => $acc->accuracy,
        //         'distance' => $acc->distance,
        //         'status_active' => $acc->status_active,
        //         'staff_id' => $acc->staff_id,
        //         'name' => $acc->name
        //     ];
        // }

        // return Excel::download(new AbsenceAccuracy($r), 'report_accuracy.xlsx');
        // echo "<pre>";
        // print_r($r);
        // dd($r);


        // echo "<pre>";
        // print_r($test);
        // dd($test);

        abort_unless(\Gate::allows('absence_access'), 403);
        if (Auth::user()->dapertement_id != 5 && Auth::user()->dapertement_id != 0) {
            $qry = AbsenceLog::selectRaw('absence_logs.*, work_types.type as work_type,  NIK, days.name as day, staffs.name as staff, staffs.image as staff_image, absence_categories.title as absence_category')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('days', 'days.id', '=', 'absences.day_id')
                ->leftJoin('staffs', 'absences.staff_id', '=', 'staffs.id')
                ->leftJoin('work_types', 'work_types.id', '=', 'staffs.work_type_id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->FilterStaff($request->staff_id)
                ->FilterAbsenceCategory($request->absence_category_id)
                ->FilterDateWeb($request->from, $request->to)
                ->FilterDapertement(Auth::user()->dapertement_id)
                //->where('absence_logs.register', '!=', '')
                ->whereNotNull('absence_logs.register')
                ->orderBy('staffs.NIK')
                ->orderBy('register', 'DESC');
        } else {
            $qry = AbsenceLog::selectRaw('absence_logs.*, work_types.type as work_type,  NIK, days.name as day, staffs.name as staff, staffs.image as staff_image, absence_categories.title as absence_category')
                ->leftJoin('absences', 'absence_logs.absence_id', '=', 'absences.id')
                ->leftJoin('days', 'days.id', '=', 'absences.day_id')
                ->leftJoin('staffs', 'absences.staff_id', '=', 'staffs.id')
                ->leftJoin('work_types', 'work_types.id', '=', 'staffs.work_type_id')
                ->leftJoin('absence_categories', 'absence_logs.absence_category_id', '=', 'absence_categories.id')
                ->FilterStaff($request->staff_id)
                ->FilterAbsenceCategory($request->absence_category_id)
                ->FilterDateWeb($request->from, $request->to)
                ->FilterDapertement($request->dapertement)
                //->where('absence_logs.register', '!=', '')
                ->whereNotNull('absence_logs.register')
                ->orderBy('staffs.NIK')
                ->orderBy('register', 'DESC');
        }
        // ->orderBy('nik', 'ASC');
        // dd($qry->get());
        // $qry = TestModel::Filter($request)->Order('id', 'desc')->skip(0)->take(10)->get();
        // return $qry;
        if ($request->ajax()) {
            //set query
            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'absence_show';
                $editGate = 'absence_edit';
                $deleteGate = 'absence_delete';
                $crudRoutePart = 'absence';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : "";
            });

            $table->editColumn('user', function ($row) {
                return $row->user ? $row->user : "";
            });

            $table->editColumn('image', function ($row) {
                return $row->image ? $row->image : "";
            });

            $table->editColumn('user_image', function ($row) {
                return $row->image ? $row->user_image : "";
            });

            $table->editColumn('lat', function ($row) {
                return $row->lat ? $row->lat : "";
            });
            $table->editColumn('lng', function ($row) {
                return $row->lng ? $row->lng : "";
            });
            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });
            $table->editColumn('absen_category', function ($row) {
                return $row->absen_category ? $row->absen_category : "";
            });
            $table->editColumn('day', function ($row) {
                return $row->day ? $row->day : "";
            });
            $table->editColumn('late', function ($row) {
                return $row->late ? $row->late : "";
            });
            $table->editColumn('NIK', function ($row) {
                return $row->NIK ? $row->NIK : "";
            });
            $table->editColumn('late', function ($row) {
                if ($row->late > 0) {
                    return "Lambat";
                } else {
                    return "Tepat";
                }
            });
            $table->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at : "";
            });

            $table->editColumn('work_type', function ($row) {
                return $row->work_type ? $row->work_type : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        // default view
        // return view('admin.schedule.index');
        $staffs = Staff::orderBy('name', 'ASC')->get();
        $dapertements = Dapertement::get();
        $absence_categories = Absence_categories::get();

        return view('admin.absence.index', compact('staffs', 'dapertements', 'absence_categories'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('absence_create'), 403);
        $absence_categories = Absence_categories::where('day_id', null)->get();
        $day = Day::get();
        $users = User::where('staff_id', '!=', '0')->orderBy('name')->get();
        return view('admin.absence.create', compact('absence_categories', 'day', 'users'));
    }

    public function store(Request $request)
    {
        abort_unless(\Gate::allows('absence_create'), 403);
        $checkD = date("w",  strtotime($request->register));
        if ($checkD == "0") {
            $day = 7;
        } else {
            $day = $checkD;
        }
        $value = 0;
        $absence_category_id = "";
        if ($request->absence_category_id == "in" || $request->absence_category_id == "break_in" || $request->absence_category_id == "break_out" || $request->absence_category_id = "out") {
            $absence_category = Absence_categories::where('day_id', $day)->where('title', $request->absence_category_id)->first();
            $value = $absence_category->value;
            $absence_category_id = $absence_category->id;
        } else {
            $absence_category_id = $request->absence_category_id;
        }


        $data = [
            'user_id' => $request->user_id,
            'image' => '',
            'lat' => '',
            'lng' => '',
            'register' => $request->register,
            'shift_id' => '',
            'absence_category_id' => $absence_category_id,
            'value' => $value,
            'day_id' => $day,
        ];
        Absence::create($data);
        return redirect()->route('admin.absence.index');
    }

    public function show($id)
    {
        abort_unless(\Gate::allows('absence_show'), 403);
        $absence = Absence::selectRaw('absences.*, users.name as user, days.name as day, absence_categories.title as absence_category')->leftJoin('users', 'absences.user_id', '=', 'users.id')
            ->leftJoin('days', 'absences.day_id', '=', 'days.id')
            ->leftJoin('absence_categories', 'absences.absence_category_id', '=', 'absence_categories.id')
            ->where('absences.id', $id)->first();

        return view('admin.absence.show', compact('absence'));
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('absence_edit'), 403);
        $absence = Absence::selectRaw('absences.*, users.name as user, days.name as day, absence_categories.title as absence_category')->leftJoin('users', 'absences.user_id', '=', 'users.id')
            ->leftJoin('days', 'absences.day_id', '=', 'days.id')
            ->leftJoin('absence_categories', 'absences.absence_category_id', '=', 'absence_categories.id')
            ->where('absences.id', $id)->first();
        return view('admin.absence.edit', compact('absence'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('absence_edit'), 403);
        $absence = Absence::where('id', $id)->first();
        $img_path = "/images/absence";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin", \base_path());
        $basepath  = base_path().'/public';
        if ($absence->image != "") {
            unlink($basepath . $absence->image);
        }

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image = $request->file('image')->getClientOriginalName();
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            $resource_image->move($basepath . $img_path, $name_image);
            $data_image = $name_image;
        }
        $data = [
            'image' =>  $name_image
        ];
        $data = Absence::where('id', $id)->update($data);
        return redirect()->route('admin.absence.index');
    }

    // untuk nanti di API
    function countDays($year, $month, $ignore)
    {
        $count = 0;
        $counter = mktime(0, 0, 0, $month, 1, $year);
        while (date("n", $counter) == $month) {
            if (in_array(date("w", $counter), $ignore) == false) {
                $count++;
            }
            $counter = strtotime("+1 day", $counter);
        }
        return  $count;
    }
    public function test()
    {
        echo $this->countDays(2022, 12, array(0, 6)); // 23
    }

    public function absenceMenu()
    {

        $menu = "";
        $shift = ShiftStaff::join('shifts', 'shifts.id', '=', 'shift_staff.shift_id')
            ->where('date', date('Y-m-d'))
            ->where('staff_id', 2)
            ->first();
        $request = Requests::where('date', '<=', date('Y-m-d'))
            ->where('end', '>=', date('Y-m-d'))
            ->where('user_id', '=', '2')
            ->where('status', '=', 'approve')
            ->where('category', 'cuti')
            ->first();

        $holiday = Holiday::where('date', date('Y-m-d'))->first();
        if ($request) {
            dd('Anda Sedang Cuti');
        } else if ($holiday) {
            echo "hari ini libur " . $holiday->title;
        } else if ($shift) {
            $data1 = date('H:i:s', strtotime($shift->start_in));
            $data2 = date('H:i:s', strtotime($shift->end_in));

            $data3 = date('H:i:s', strtotime($shift->start_breakin));
            $data4 = date('H:i:s', strtotime($shift->end_breakin));

            $data5 = date('H:i:s', strtotime($shift->start_breakout));
            $data6 = date('H:i:s', strtotime($shift->end_breakout));

            $data7 = date('H:i:s', strtotime($shift->start_out));
            $data8 = date('H:i:s', strtotime($shift->end_out));
            // $r[] = $data1;
            if ($data1 < date('H:i:s') && $data2 > date('H:i:s')) {
                $menu = "IS";
                echo "absen data1";
            }
            if ($data3 < date('H:i:s') && $data4 > date('H:i:s')) {
                $menu = "BIS";
                echo "absen data2";
            }
            if ($data5 < date('H:i:s') && $data6 > date('H:i:s')) {
                $menu = "BOS";
                echo "absen data3";
            }
            if ($data7 < date('H:i:s') && $data8 > date('H:i:s')) {
                $menu = "IO";
                echo "absen data4";
            }
        } else {
            if (date('w') == '0') {
                $day = '7';
            } else {
                $day = date('w');
            }

            $absen = Absence_categories::selectRaw('absence_categories.*')
                ->where('day_id', 1)
                ->get();

            foreach ($absen as $data) {

                $data1 = date('H:i:s', strtotime($data->start));
                $data2 = date('H:i:s', strtotime($data->end));
                // $r[] = $data1;
                if ($data1 < date('H:i:s') && $data2 > date('H:i:s')) {
                    $menu = $data->title;
                    echo $menu;
                }
            }
        }
    }

    public function destroy($id)
    {
        abort_unless(\Gate::allows('absence_delete'), 403);
        $absence = Absence::where('id', $id)->first();
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin", \base_path());
        $basepath  = base_path().'/public';
        unlink($basepath . "images/absence/" . $absence->image);
        Absence::where('id', $id)->delete();
        return redirect()->back();
    }

    public function reportAbsenceExcelView()
    {
        return view('admin.absence.reportExcel');
    }

    public function reportAbsenceView()
    {
        return view('admin.absence.report');
    }

    public function reportAbsenceExcel(Request $request)
    {

        // try {

        // $from = date("Y-m-d", strtotime('-1 month', strtotime($request->monthyear . '-21')));
        // $to   = date("Y-m-d", strtotime($request->monthyear . '-20'));
        $from = date("Y-m-d", strtotime($request->from));
        $to   = date("Y-m-d", strtotime($request->to));
        // $report =  Absence::select(
        //     DB::raw('RIGHT(staffs.NIK , 3 ) AS No'),
        //     'staffs.name as Name',
        //     DB::raw('TIME(in.timein) AS on_duty'),
        //     DB::raw('TIME(out.timein) AS off_duty'),
        //     DB::raw('TIME(in.register) AS clock_in'),
        //     DB::raw('TIME(out.register) AS clock_out'),
        //     'days.name as timetable',
        //     DB::raw('DATE(absences.created_at) AS date'),
        //     'in.absence_category_id',
        //     'leave.absence_category_id as leave',
        //     'leave_request.type as leave_type',
        //     'leave_request.description as leave_description',
        //     'duty.absence_category_id as duty',
        //     'duty_request.type as duty_type',
        //     'duty_request.description as duty_description',
        //     'permission.absence_category_id as permission',
        //     'permission_request.type as permission_type',
        //     'permission_request.description as permission_description'
        // )
        //     ->join('staffs', 'staffs.id', '=', 'absences.staff_id')
        //     ->join('days', 'days.id', '=', 'absences.day_id')
        //     // ->join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
        //     ->leftJoin('absence_logs as in', function ($join) {
        //         $join->on('in.absence_id', '=', 'absences.id')
        //             ->where('in.absence_category_id', '=', 1)
        //             ->where('in.register', '!=', "");
        //         // ->orWhere('in.absence_category_id', '=', 4)
        //         // ->orWhere('in.absence_category_id', '=', 13);
        //     })
        //     ->leftJoin('absence_logs as out', function ($join) {
        //         $join->on('out.absence_id', '=', 'absences.id')
        //             ->where('out.absence_category_id', '=', 2)
        //             ->where('in.register', '!=', "");
        //     })

        //     ->leftJoin('absence_logs as permission', function ($join) {
        //         $join->on('permission.absence_id', '=', 'absences.id')
        //             ->leftJoin('absence_requests as permission_request', 'permission_request.id', 'permission.absence_request_id')
        //             ->where('permission.absence_category_id', '=', 13);
        //         // ->orWhere('in.absence_category_id', '=', 8);
        //     })

        //     ->leftJoin('absence_logs as duty', function ($join) {
        //         $join->on('duty.absence_id', '=', 'absences.id')
        //             ->leftJoin('absence_requests as duty_request', 'duty_request.id', 'duty.absence_request_id')
        //             ->where('duty.absence_category_id', '=', 7);
        //         // ->orWhere('in.absence_category_id', '=', 8);
        //     })

        //     ->leftJoin('absence_logs as leave', function ($join) {
        //         $join->on('leave.absence_id', '=', 'absences.id')
        //             ->leftJoin('absence_requests as leave_request', 'leave_request.id', 'leave.absence_request_id')
        //             ->where('duty.absence_category_id', '=', 8);
        //     })
        //     // ->leftJoin('absence_logs as out', function ($join) {
        //     //     $join->on('out.absence_id', '=', 'absences.id')
        //     //         ->where('out.absence_category_id', '=', 2);
        //     // })
        //     // ->where('in.id', null)
        //     // ->where('absence_logs.absence_category_id', '13')
        //     ->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to])
        //     ->orderBy('NIK', 'ASC')
        //     ->orderBy('in.created_at', 'ASC')
        //     ->get();
        // dd($report, $from, $to);
        // perulangan tanggal

        $data = [];


        $awal = $from;
        $akhir =  $to;

        // tanggalnya diubah formatnya ke Y-m-d 
        $awal = date_create_from_format('Y-m-d', $awal);
        $awal = date_format($awal, 'Y-m-d');
        $awal = strtotime($awal);

        $akhir = date_create_from_format('Y-m-d', $akhir);
        $akhir = date_format($akhir, 'Y-m-d');
        $akhir = strtotime($akhir);

        $hariefective = array();
        $harilibur = array();
        $sabtuminggu = array();
        $tglLibur = array();

        //$holidays = Holiday::select(DB::raw('DATE(holidays.start) AS start'), DB::raw('DATE(holidays.end) AS end'))->whereBetween(DB::raw('DATE(holidays.start)'), [$from, $to])
            //->orWhereBetween(DB::raw('DATE(holidays.end)'), [$from, $to])->get();
        $holidays = Holiday::select(DB::raw('DATE(holidays.start) AS start'), DB::raw('DATE(holidays.end) AS end'))->whereRaw("DATE(holidays.start) >= '".$from."' AND DATE(holidays.start) <= '".$to."'")
        ->orWhereRaw("DATE(holidays.end) >= '".$from."' AND DATE(holidays.end) <= '".$to."'")->get();
        // dd($holidays);
        foreach ($holidays as $holiday) {
            $awal_libur = date_create_from_format('Y-m-d', $holiday->start);
            $awal_libur = date_format($awal_libur, 'Y-m-d');
            $awal_libur = strtotime($awal_libur);

            $akhir_libur = date_create_from_format('Y-m-d', $holiday->end);
            $akhir_libur = date_format($akhir_libur, 'Y-m-d');
            $akhir_libur = strtotime($akhir_libur);

            for ($i = $awal_libur; $i <= $akhir_libur; $i += (60 * 60 * 24)) {
                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                    $harilibur[] = $i;
                    $tglLibur = array_merge($tglLibur, [date("Y-m-d", strtotime(date('Y-m-d', $i)))]);
                } else {
                }
            }
        }
        // dd($harilibur);
        // dd($tglLibur);


        // hari effective berdasarkan work type day start
        $work_types = WorkTypes::get();

        foreach ($work_types as $work_type) {
            $work_type_days = Day::select('days.*')->leftJoin(
                'work_type_days',
                function ($join) use ($work_type) {
                    $join->on('days.id', '=', 'work_type_days.day_id')
                        ->where('work_type_id', $work_type->id);
                }
            )
                ->where('work_type_days.day_id', '=', null)->get();
            // dd($work_type_days);
            // dd($work_type_days);
            $jadwallibur = [];
            foreach ($work_type_days as $work_type_day) {
                $jadwallibur = array_merge($jadwallibur, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
            }
            // dd($jadwallibur);
            // dd(in_array(date('w', strtotime(date('Y-m-d'))), $jadwallibur));

            // libur nasional
            $holidays = Holiday::select(DB::raw('DATE(holidays.start) AS start'), DB::raw('DATE(holidays.end) AS end'))
                //->whereBetween(DB::raw('DATE(holidays.start)'), [$from, $to])
                //->orWhereBetween(DB::raw('DATE(holidays.end)'), [$from, $to])
                ->whereRaw("DATE(holidays.start) >= '".$from."' AND DATE(holidays.start) <= '".$to."'")
                ->orWhereRaw("DATE(holidays.end) >= '".$from."' AND DATE(holidays.end) <= '".$to."'")
                ->get();
            // dd($holidays);
            foreach ($holidays as $holiday) {
                $awal_libur = date_create_from_format('Y-m-d', $holiday->start);
                $awal_libur = date_format($awal_libur, 'Y-m-d');
                $awal_libur = strtotime($awal_libur);

                $akhir_libur = date_create_from_format('Y-m-d', $holiday->end);
                $akhir_libur = date_format($akhir_libur, 'Y-m-d');
                $akhir_libur = strtotime($akhir_libur);

                $work_type_days = Day::select('days.*')->leftJoin(
                    'work_type_days',
                    function ($join) use ($work_type) {
                        $join->on('days.id', '=', 'work_type_days.day_id')
                            ->where('work_type_id', $work_type->id);
                    }
                )
                    ->where('work_type_days.day_id', '=', null)->get();
                // dd($work_type_days);
                // dd($work_type_days);
                $jadwallibur = [];
                foreach ($work_type_days as $work_type_day) {
                    $jadwallibur = array_merge($jadwallibur, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
                }
            }
            // dd($jadwallibur);


            for ($i = $awal; $i <= $akhir; $i += (60 * 60 * 24)) {

                // if (!in_array(date('w', $i), $jadwallibur) &&  !in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur)) {
                if (!in_array(date('w', $i), $jadwallibur)) {
                    $hariefective[] = ['id' => $i, 'date' => date("Y-m-d", strtotime(date('Y-m-d', $i)))];
                } else {
                    $sabtuminggu[] = $i;
                }
            }
            // dd($hariefective);
            $jumlah_efective[] = ['id' => $work_type->id, 'hari_effective' => $hariefective];
            $jadwallibur = [];
            $hariefective = [];
        }
        // dd($jumlah_efective);
        // untuk mencari work type yang mana
        $collection = collect($jumlah_efective);
        // dd($collection);

        if (Auth::user()->dapertement_id != 5 && Auth::user()->dapertement_id != 0) {
            $staffs = Staff::select('staffs.*',  DB::raw('(CASE WHEN staffs.type = "employee" THEN  SUBSTRING(staffs.NIK, 5) ELSE staffs.NIK END)  AS NIK'), 'work_types.type as work_type')
                ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
                ->join('users', 'users.staff_id', '=', 'staffs.id')
                ->groupBy('staffs.id')
                ->FilterDapertement(Auth::user()->dapertement_id)
                // ->where('staffs.id', '116')
                ->orderBy(DB::raw("FIELD(staffs.type , \"employee\", \"contract\" )"))
                ->orderBy('NIK', 'ASC')
                // ->where('staffs.id', '8')
                ->get();
        } else {
            $staffs = Staff::select('staffs.*',  DB::raw('(CASE WHEN staffs.type = "employee" THEN  SUBSTRING(staffs.NIK, 5) ELSE staffs.NIK END)  AS NIK'), 'work_types.type as work_type')
                ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
                ->join('users', 'users.staff_id', '=', 'staffs.id')
                ->groupBy('staffs.id')
                // ->where('staffs.id', '116')
                ->orderBy(DB::raw("FIELD(staffs.type , \"employee\", \"contract\" )"))
                ->orderBy('NIK', 'ASC')

                // ->where('staffs.id', '8')
                ->get();
        }
        // dd($staffs);
        $test = [];
        foreach ($staffs as $key => $value) {
            $test[] = [$value->id];
        }
        // echo "<pre>";
        // print_r($test);
        // dd($test);
        // dd($staffs);
        foreach ($staffs as $staff) {
            $absence = Absence::select(
                'absences.id',
                'staffs.id as staff_id',
                // 'extra.absence_id as extra_id',
                DB::raw('(CASE WHEN staffs.type = "employee" THEN  SUBSTRING(staffs.NIK, 5) ELSE staffs.NIK END)  AS No'),
                'staffs.name as Name',
                DB::raw('TIME(in.timein) AS on_duty'),
                DB::raw('TIME(out.timeout) AS off_duty'),
                DB::raw('TIME(in.register) AS clock_in'),
                DB::raw('TIME(out.register) AS clock_out'),
                'days.name as timetable',
                DB::raw('DATE(absences.created_at) AS date'),
                'in.absence_category_id',
                'leave.absence_category_id as leave',
                'leave_request.type as leave_type',
                'leave_request.description as leave_description',
                'duty.absence_category_id as duty',
                'duty_request.type as duty_type',
                'duty_request.description as duty_description',
                'permission.absence_category_id as permission',
                'permission_request.type as permission_type',
                'permission_request.description as permission_description'
            )
                ->join('staffs', 'staffs.id', '=', 'absences.staff_id')
                ->join('days', 'days.id', '=', 'absences.day_id')
                ->join('absence_logs as acl', function ($join) {
                    $join->on('acl.absence_id', '=', 'absences.id')
                        ->where('acl.absence_category_id', '!=', 9)
                        ->where('acl.absence_category_id', '!=', 10);
                    // ->where('in.register', '!=', "")
                    // ->whereNotNull('in.register');
                })
                ->leftJoin('absence_logs as in', function ($join) {
                    $join->on('in.absence_id', '=', 'absences.id')
                        ->where('in.absence_category_id', '=', 1);
                    // ->where('in.register', '!=', "")
                    // ->whereNotNull('in.register');
                })
                ->leftJoin('absence_logs as out', function ($join) {
                    $join->on('out.absence_id', '=', 'absences.id')
                        ->where('out.absence_category_id', '=', 2);
                    // ->where('out.register', '!=', "")
                    // ->whereNotNull('out.register');
                })

                ->leftJoin('absence_logs as permission', function ($join) {
                    $join->on('permission.absence_id', '=', 'absences.id')
                        ->leftJoin('absence_requests as permission_request', 'permission_request.id', 'permission.absence_request_id')
                        ->where('permission.absence_category_id', '=', 13);
                })

                ->leftJoin('absence_logs as duty', function ($join) {
                    $join->on('duty.absence_id', '=', 'absences.id')
                        ->leftJoin('absence_requests as duty_request', 'duty_request.id', 'duty.absence_request_id')
                        ->where('duty.absence_category_id', '=', 7);
                })

                ->leftJoin('absence_logs as leave', function ($join) {
                    $join->on('leave.absence_id', '=', 'absences.id')
                        ->leftJoin('absence_requests as leave_request', 'leave_request.id', 'leave.absence_request_id')
                        ->where('leave.absence_category_id', '=', 8);
                })
                // ->leftJoin('absence_logs as extra', function ($join) {
                //     $join->on('extra.absence_id', '=', 'absences.id')
                //         ->leftJoin('absence_requests as extra_request', 'extra_request.id', 'extra.absence_request_id')
                //         ->where('extra.absence_category_id', '=', 9);
                // })
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [$from, $to])
                ->whereRaw("DATE(absences.created_at) >= '".$from."' AND DATE(absences.created_at) <= '".$to."'")

                // ->where('extra.absence_id', null)
                ->orderBy('NIK', 'ASC')
                ->orderBy('in.created_at', 'ASC')
                ->where('staffs.id', $staff->id)
                ->where('absences.status_active', '')
                ->get();

            // dd($absence);
            if (count($absence) <= 0) {

                // if (in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur)) {
                //     $alp = "libur";
                // } else {
                $alp = "alpha";
                // }

                $datapgw[] = [
                    'Emp No' => '',
                    'AC-No' => '',
                    'No' => $staff->NIK,
                    'Name' => $staff->name,
                    'Auto-Asign' => '',
                    'Date' => '',
                    'TimeTable' => '',
                    'On_Duty' => '',
                    'Off_Duty' => '',
                    'Clock_in' => '',
                    'Clock_out' => '',
                    'keterangan' => $alp,
                    'deskripsi' => '',
                ];
                // dd($datapgw);
            }

            // dd('111');
            foreach ($absence as $value) {
                $day = "";
                if (date("w", strtotime($value->date)) == 0) {
                    $day = "Minggu";
                } else if (date("w", strtotime($value->date)) == 1) {
                    $day = "Senin";
                } else if (date("w", strtotime($value->date)) == 2) {
                    $day = "Selasa";
                } else if (date("w", strtotime($value->date)) == 3) {
                    $day = "Rabu";
                } else if (date("w", strtotime($value->date)) == 4) {
                    $day = "Kamis";
                } else if (date("w", strtotime($value->date)) == 5) {
                    $day = "Jumat";
                } else if (date("w", strtotime($value->date)) == 6) {
                    $day = "Sabtu";
                }
                if ($value->leave != null) {
                    $keterangan = "Cuti";
                    $deskripsi = $value->leave_description;
                } else if ($value->permission != null) {
                    if ($value->permission_type == "sick") {
                        $keterangan = "Sakit";
                    } else {
                        $keterangan = "Izin";
                    }
                    $deskripsi = $value->permission_description;
                } else if ($value->duty != null) {
                    $keterangan = "Duty";
                    $deskripsi = $value->duty_description;
                } else {
                    $keterangan = "Masuk";
                    $deskripsi = "";
                }
                // dd($value->absence_category_id);
                $extraS = AbsenceLog::join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                    ->where('absence_category_id', '9')->whereDate('register', '=', date("Y-m-d", strtotime($value->date)))
                    ->where('staff_id', $staff->id)
                    ->where('absences.status_active', '')
                    ->first();
                $extra = null;
                if ($extraS) {
                    $extra = AbsenceLog::select(
                        DB::raw('TIME(register) AS register')
                    )
                        ->join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                        ->where('absence_category_id', '10')
                        ->where('absences.status_active', '')
                        ->where('absence_id', $extraS->absence_id)->first();
                }
                // dd($extra);
                if ($value->absence_category_id == "1") {
                    if ($value->clock_in != null && $value->clock_in != "") {

                        if ($extra) {
                            $keterangan = "Masuk dan Lembur";
                            if ($value->clock_in  > $extra->register) {
                                $clockIn = "00:00:00";
                            } else {
                                $clockIn = $extra->register;
                            }
                        } else {
                            if ($value->clock_in  > $value->clock_out) {
                                $clockIn = "00:00:00";
                            } else {
                                $clockIn = $value->clock_out;
                            }
                        }
                        $datapgw[] = [
                            'Emp No' => '',
                            'AC-No' => '',
                            'No' => $value->No,
                            'Name' => $value->Name,
                            'Auto-Asign' => '',
                            'Date' => $value->date,
                            'TimeTable' => $day,
                            'On_Duty' => $value->on_duty,
                            'Off_Duty' => $value->off_duty,
                            'Clock_in' => $value->clock_in,
                            'Clock_out' =>  $clockIn,
                            'keterangan' => $keterangan,
                            'deskripsi' => $deskripsi,
                        ];
                    }
                } else {
                    $datapgw[] = [
                        'Emp No' => '',
                        'AC-No' => '',
                        'No' => $value->No,
                        'Name' => $value->Name,
                        'Auto-Asign' => '',
                        'Date' => $value->date,
                        'TimeTable' => $day,
                        'On_Duty' => $value->on_duty,
                        'Off_Duty' => $value->off_duty,
                        'Clock_in' => $value->clock_in,
                        'Clock_out' => $value->clock_out,
                        'keterangan' => $keterangan,
                        'deskripsi' => $deskripsi,
                    ];
                }
            }

            // dd($datapgw);
            $collectionPgw = collect($datapgw);
            // dd($collectionPgw);
            // dd('112');
            if ($staff->work_type == "shift") {
                // dd('113');
                $shift_planners = ShiftPlannerStaffs::select('shift_planner_staffs.*', DB::raw('DATE(shift_planner_staffs.start) as start'))
                    //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$from, $to])
                    ->whereRaw("DATE(shift_planner_staffs.start) >= '".$from."' AND DATE(shift_planner_staffs.start) <= '".$to."'")
                    ->where('staff_id', $staff->id)
                    ->get();
                // dd($shift_planners);
                // penting
                // if (count($shift_planners) <= 0) {
                //     $data12[] =   [
                //         'Emp No' => '',
                //         'AC-No' => '',
                //         'No' => $staff->NIK,
                //         'Name' => $staff->name,
                //         'Auto-Asign' => '',
                //         'Date' =>  '',
                //         'TimeTable' => '',
                //         'On_Duty' => '',
                //         'Off_Duty' => '',
                //         'Clock_in' => '',
                //         'Clock_out' => '',
                //         'keterangan' => 'tidak ada shift',
                //         'deskripsi' => '',
                //     ];
                // }
                // dd($shift_planners);
                foreach ($shift_planners as $shift_planner) {

                    $day = "";

                    if (date("w", strtotime($shift_planner->start)) == 0) {
                        $day = "Minggu";
                    } else if (date("w", strtotime($shift_planner->start)) == 1) {
                        $day = "Senin";
                    } else if (date("w", strtotime($shift_planner->start)) == 2) {
                        $day = "Selasa";
                    } else if (date("w", strtotime($shift_planner->start)) == 3) {
                        $day = "Rabu";
                    } else if (date("w", strtotime($shift_planner->start)) == 4) {
                        $day = "Kamis";
                    } else if (date("w", strtotime($shift_planner->start)) == 5) {
                        $day = "Jumat";
                    } else if (date("w", strtotime($shift_planner->start)) == 6) {
                        $day = "Sabtu";
                    }

                    // dd('115');
                    // dd($day);
                    // dd($value);
                    // if ($value) {
                    //     if ($value->leave != null) {
                    //         $keterangan = "Cuti";
                    //         $deskripsi = $value->leave_description;
                    //     } else if ($value->permission != null) {
                    //         if ($value->permission_type == "sick") {
                    //             $keterangan = "Sakit";
                    //         } else {
                    //             $keterangan = "Izin";
                    //         }
                    //         $deskripsi = $value->permission_description;
                    //     } else if ($value->duty != null) {
                    //         $keterangan = "Duty";
                    //         $deskripsi = $value->duty_description;
                    //     } else {
                    //         $keterangan = "Masuk";
                    //         $deskripsi = "";
                    //     }
                    // }
                    // dd('116');
                    // dd($shift_planner);
                    $extraS = AbsenceLog::join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                        ->where('absence_category_id', '9')->whereDate('register', '=', date("Y-m-d", strtotime($shift_planner->start)))->where('staff_id', $staff->id)
                        ->first();

                    // dd($shift_planner);
                    $extra = [];
                    if ($extraS) {
                        $extra = AbsenceLog::select(
                            DB::raw('TIME(register) AS register')
                        )
                            ->join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                            ->where('absence_category_id', '9')->where('absence_id', $extraS->absence_id)
                            ->orWhere('absence_category_id', '10')->where('absence_id', $extraS->absence_id)->get();
                    }

                    // dd($extra);
                    if (count($extra) > 1) {

                        if ($extra[0]->register > $extra[1]->register) {
                            $clockOut = "00:00:00";
                        } else {
                            $clockOut = $extra[1]->register;
                        }

                        $data12[] =   [
                            'Emp No' => '',
                            'AC-No' => '',
                            'No' => $staff->NIK,
                            'Name' => $staff->name,
                            'Auto-Asign' => '',
                            'Date' =>  $shift_planner->start,
                            'TimeTable' => $day,
                            'On_Duty' => '',
                            'Off_Duty' => '',
                            'Clock_in' => $extra[0]->register,
                            'Clock_out' => $clockOut,
                            'keterangan' => 'Lembur',
                            'deskripsi' => '',
                        ];
                    } else if ($collectionPgw->where('Date', $shift_planner->start)->first() != null) {
                        $data12[] =  [$collectionPgw->where('Date', $shift_planner->start)->first()];
                    } else {
                        $data12[] =   [
                            'Emp No' => '',
                            'AC-No' => '',
                            'No' => $staff->NIK,
                            'Name' => $staff->name,
                            'Auto-Asign' => '',
                            'Date' =>  $shift_planner->start,
                            'TimeTable' => $day,
                            'On_Duty' => '',
                            'Off_Duty' => '',
                            'Clock_in' => '',
                            'Clock_out' => '',
                            'keterangan' => 'Alpha',
                            'deskripsi' => '',
                        ];
                    }
                }
                // dd($data12);
            }

            // dd($data12);

            else {
                // reguler
                $item = $collection->where('id', $staff->work_type_id)->first();
                // dd($staff->work_type_id);
                // dd($staff);
                // dd($item);
                for ($b = 0; $b < count($item['hari_effective']); $b++) {
                    $day = "";
                    $day = "";
                    if (date("w", strtotime($item['hari_effective'][$b]['date'])) == 0) {
                        $day = "Minggu";
                    } else if (date("w", strtotime($item['hari_effective'][$b]['date'])) == 1) {
                        $day = "Senin";
                    } else if (date("w", strtotime($item['hari_effective'][$b]['date'])) == 2) {
                        $day = "Selasa";
                    } else if (date("w", strtotime($item['hari_effective'][$b]['date'])) == 3) {
                        $day = "Rabu";
                    } else if (date("w", strtotime($item['hari_effective'][$b]['date'])) == 4) {
                        $day = "Kamis";
                    } else if (date("w", strtotime($item['hari_effective'][$b]['date'])) == 5) {
                        $day = "Jumat";
                    } else if (date("w", strtotime($item['hari_effective'][$b]['date'])) == 6) {
                        $day = "Sabtu";
                    }
                    // if ($value->leave != null) {
                    //     $keterangan = "Cuti";
                    //     $deskripsi = $value->leave_description;
                    // } else if ($value->permission != null) {
                    //     if ($value->permission_type == "sick") {
                    //         $keterangan = "Sakit";
                    //     } else {
                    //         $keterangan = "Izin";
                    //     }
                    //     $deskripsi = $value->permission_description;
                    // } else if ($value->duty != null) {
                    //     $keterangan = "Duty";
                    //     $deskripsi = $value->duty_description;
                    // } else {
                    //     $keterangan = "Masuk";
                    //     $deskripsi = "";
                    // }
                    $extraS = AbsenceLog::join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                        ->where('absence_category_id', '9')
                        ->where('absences.status_active', '')
                        ->whereDate('register', '=', date("Y-m-d", strtotime($item['hari_effective'][$b]['date'])))->where('staff_id', $staff->id)->first();

                    $extra = [];
                    if ($extraS) {
                        $extra = AbsenceLog::select(
                            DB::raw('TIME(register) AS register')
                        )->join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                            ->where('absences.status_active', '')
                            ->where('absence_category_id', '9')->where('absence_id', $extraS->absence_id)
                            ->orWhere('absence_category_id', '10')
                            ->where('absences.status_active', '')
                            ->where('absence_id', $extraS->absence_id)->get();
                    }
                    // dd($extraS, $extra);
                    // dd($extra[0]->register);
                    // dd(count($extra) > 1);
                    // dd($collectionPgw);
                    // dd($item['hari_effective'][$b]['date']);
                    // dd($collectionPgw->where('Date', $item['hari_effective'][$b]['date'])->first());
                    // if (count($extra) > 1 && $collectionPgw->where('Date', $item['hari_effective'][$b]['date'])->first()['Clock_in'] == "") {
                    if (count($extra) > 1 && $collectionPgw->where('Date', $item['hari_effective'][$b]['date'])->first()) {
                        // dd($staff);
                        if ($extra[0]->register > $extra[1]->register) {
                            $clockOut = "00:00:00";
                        } else {
                            $clockOut = $extra[1]->register;
                        }
                        // dd($collectionPgw->where('Date', $item['hari_effective'][$b]['date'])->first());
                        $data12[] =   [
                            'Emp No' => '',
                            'AC-No' => '',
                            'No' => $staff->NIK,
                            'Name' => $staff->name,
                            'Auto-Asign' => '',
                            'Date' =>  $item['hari_effective'][$b]['date'],
                            'TimeTable' => $day,
                            'On_Duty' => '',
                            'Off_Duty' => '',
                            'Clock_in' =>  $extra[0]->register,
                            'Clock_out' => $clockOut,
                            'keterangan' => 'Lembur',
                            'deskripsi' => '',
                        ];
                        // dd('11111');
                    } else if ($collectionPgw->where('Date', $item['hari_effective'][$b]['date'])->first() == null && count($extra) > 1) {
                        // dd($staff);
                        // dd($extra);
                        // dd('22222');
                        $data12[] =   [
                            'Emp No' => '',
                            'AC-No' => '',
                            'No' => $staff->NIK,
                            'Name' => $staff->name,
                            'Auto-Asign' => '',
                            'Date' =>  $item['hari_effective'][$b]['date'],
                            'TimeTable' => $day,
                            'On_Duty' => '',
                            'Off_Duty' => '',
                            'Clock_in' => ($extra[0]->register),
                            'Clock_out' => ($extra[1]->register),
                            'keterangan' => 'Lembur',
                            'deskripsi' => '',
                        ];
                    } else if ($collectionPgw->where('Date', $item['hari_effective'][$b]['date'])->first() != null) {
                        $data12[] =  [$collectionPgw->where('Date', $item['hari_effective'][$b]['date'])->first()];
                        // dd("ssdkdk", $data12);
                        // dd($staffs);
                    } else {
                        // dd($tglLibur, $item['hari_effective'][$b]['date']);
                        if (in_array($item['hari_effective'][$b]['date'], $tglLibur)) {
                            $alp = "libur";
                        } else {
                            $alp = "alpha";
                        }

                        $data12[] =   [
                            'Emp No' => '',
                            'AC-No' => '',
                            'No' => $staff->NIK,
                            'Name' => $staff->name,
                            'Auto-Asign' => '',
                            'Date' =>  $item['hari_effective'][$b]['date'],
                            'TimeTable' => $day,
                            'On_Duty' => '',
                            'Off_Duty' => '',
                            'Clock_in' => '',
                            'Clock_out' => '',
                            'keterangan' => $alp,
                            'deskripsi' => '',
                        ];
                        // dd($staff->name);
                    }
                }
            }
            // dd($data12);
            $collectionPgw = [];
            $datapgw = [];
        }

        // dd($data12);
        // dd(count($data12), $collectionPgw, count($staffs));
        // dd($collectionPgw);
        return Excel::download(new AbsenceExport($data12), 'report_excel.xlsx');

        # code...
        // } catch (\Throwable $e) {
        //     dd($e);
        //     abort('500');
        // }
        // dd($report);
    }

    public function reportAbsence(Request $request)
    {

        try {


            if (!empty(request()->input('from')) || !empty(request()->input('to'))) {
                $awal = date("Y-m-d", strtotime($request->from));
                $akhir   = date("Y-m-d", strtotime($request->to));
            } else {

                $awal = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . '-21')));
                $akhir  = date("Y-m-d", strtotime(date('Y-m') . '-20'));
            }


            $from = $awal;
            $to = $akhir;


            // tanggalnya diubah formatnya ke Y-m-d 
            $awal = date_create_from_format('Y-m-d', $awal);
            $awal = date_format($awal, 'Y-m-d');
            $awal = strtotime($awal);

            $akhir = date_create_from_format('Y-m-d', $akhir);
            $akhir = date_format($akhir, 'Y-m-d');
            $akhir = strtotime($akhir);

            $haricuti = array();
            $sabtuminggu = array();

            for ($i = $awal; $i <= $akhir; $i += (60 * 60 * 24)) {
                if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                    $haricuti[] = $i;
                } else {
                    $sabtuminggu[] = $i;
                }
            }
            $jumlah_cuti = count($haricuti);
            $jumlah_sabtuminggu = count($sabtuminggu);
            $abtotal = $jumlah_cuti + $jumlah_sabtuminggu;


            $hariefective = array();
            $harilibur = array();
            $sabtuminggu = array();
            $tglLibur = array();

            $work_types = WorkTypes::get();

            foreach ($work_types as $work_type) {
                $work_type_days = Day::select('days.*')->leftJoin(
                    'work_type_days',
                    function ($join) use ($work_type) {
                        $join->on('days.id', '=', 'work_type_days.day_id')
                            ->where('work_type_id', $work_type->id);
                    }
                )
                    ->where('work_type_days.day_id', '=', null)->get();
                // dd($work_type_days);
                // dd($work_type_days);
                $jadwallibur = [];
                foreach ($work_type_days as $work_type_day) {
                    $jadwallibur = array_merge($jadwallibur, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
                }

                // libur nasional
                $holidays = Holiday::select(DB::raw('DATE(holidays.start) AS start'), DB::raw('DATE(holidays.end) AS end'))
                    //->whereBetween(DB::raw('DATE(holidays.start)'), [$from, $to])
                    //->orWhereBetween(DB::raw('DATE(holidays.end)'), [$from, $to])
                    ->whereRaw("DATE(holidays.start) >= '".$from."' AND DATE(holidays.start) <= '".$to."'")
                    ->orWhereRaw("DATE(holidays.end) >= '".$from."' AND DATE(holidays.end) <= '".$to."'")
                    ->get();
                // dd($holidays);
                foreach ($holidays as $holiday) {
                    $awal_libur = date_create_from_format('Y-m-d', $holiday->start);
                    $awal_libur = date_format($awal_libur, 'Y-m-d');
                    $awal_libur = strtotime($awal_libur);

                    $akhir_libur = date_create_from_format('Y-m-d', $holiday->end);
                    $akhir_libur = date_format($akhir_libur, 'Y-m-d');
                    $akhir_libur = strtotime($akhir_libur);

                    $work_type_days = Day::select('days.*')->leftJoin(
                        'work_type_days',
                        function ($join) use ($work_type) {
                            $join->on('days.id', '=', 'work_type_days.day_id')
                                ->where('work_type_id', $work_type->id);
                        }
                    )
                        ->where('work_type_days.day_id', '=', null)->get();

                    $jadwallibur = [];
                    foreach ($work_type_days as $work_type_day) {
                        $jadwallibur = array_merge($jadwallibur, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
                    }


                    for ($i = $awal_libur; $i < $akhir_libur; $i += (60 * 60 * 24)) {
                        if (!in_array(date('w', $i), $jadwallibur) &&  !in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur)) {
                            $harilibur[] = $i;
                            $tglLibur = array_merge($tglLibur, [date("Y-m-d", strtotime(date('Y-m-d', $i)))]);
                        } else {
                        }
                    }
                }

                for ($i = $awal; $i <= $akhir; $i += (60 * 60 * 24)) {

                    if (!in_array(date('w', $i), $jadwallibur) &&  !in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur)) {
                        $hariefective[] = ['id' => $i, 'date' => date("Y-m-d", strtotime(date('Y-m-d', $i)))];
                    } else {
                        $sabtuminggu[] = $i;
                    }
                }

                $jumlah_efective[] = ['id' => $work_type->id, 'hari_effective' => $hariefective];
                $jadwallibur = [];
                $hariefective = [];
                $tglLibur = [];
            }

            $collection = collect($jumlah_efective);


            // if (Auth::user()->dapertement_id != 5) {
            //     $dapertements = Dapertement::where('id', Auth::user()->dapertement_id)->first();
            //     $dapertement = $dapertements->id;
            // }

            $data12 = [];
            if (Auth::user()->dapertement_id != 5 && Auth::user()->dapertement_id != 0) {
                $stfs1 = Staff::select('staffs.*',  DB::raw('(CASE WHEN staffs.type = "employee" THEN  SUBSTRING(staffs.NIK, 5) ELSE staffs.NIK END)  AS NIK'), 'work_types.type as work_type')
                    ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
                    ->join('users', 'users.staff_id', '=', 'staffs.id')
                    ->FilterDapertement(Auth::user()->dapertement_id)
                    ->groupBy('staffs.id')
                    ->orderBy(DB::raw("FIELD(staffs.type , \"employee\", \"contract\" )"))
                    ->orderBy('NIK', 'ASC')
                    // ->where('staffs.id', '8')
                    ->get();
            } else {
                $stfs1 = Staff::select('staffs.*',  DB::raw('(CASE WHEN staffs.type = "employee" THEN  SUBSTRING(staffs.NIK, 5) ELSE staffs.NIK END)  AS NIK'), 'work_types.type as work_type')
                    ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
                    ->join('users', 'users.staff_id', '=', 'staffs.id')
                    // ->FilterDapertement($dapertement)
                    ->groupBy('staffs.id')
                    ->orderBy(DB::raw("FIELD(staffs.type , \"employee\", \"contract\" )"))
                    ->orderBy('NIK', 'ASC')
                    // ->where('staffs.id', '8')
                    ->get();
            }

            foreach ($stfs1 as $stf) {
                $report = AbsenceLog::select(
                    DB::raw('(CASE WHEN staffs.type = "employee" THEN  SUBSTRING(staffs.NIK, 5) ELSE staffs.NIK END)  AS NIK'),
                    DB::raw('count(IF(absence_category_id = 1 and register != "" ,1,NULL)) as hadir'),
                    DB::raw('count(IF(absence_category_id = 13 ,1,NULL)) as izin'),
                    DB::raw('count(IF(absence_category_id = 7 ,1,NULL)) as dinas_luar'),
                    DB::raw('count(IF(absence_category_id = 8 ,1,NULL)) as cuti'),
                    DB::raw('count(IF(absence_category_id = 5 ,1,NULL)) as dinas_dalam'),
                    DB::raw('count(IF(absence_category_id = 1 and late != ""  and late != "0" ,1,NULL)) as lambat'),
                    DB::raw('count(IF(absence_category_id = 9 ,1,NULL)) as lembur'),
                    // DB::raw('SUM(CASE WHEN absence_category_id = "1" AND late != "0" THEN 1 ELSE 0 END) as lambat'),
                    DB::raw('count(IF(absence_category_id = 11 ,1,NULL)) as permisi'),
                    // total jam
                    DB::raw('SUM(CASE WHEN absence_category_id = "1" THEN late ELSE "0" END) as jam_lambat'),
                    DB::raw('SUM(CASE WHEN absence_category_id = "2" THEN duration ELSE "0" END) as jam_hadir'),
                    DB::raw('SUM(CASE WHEN absence_category_id = "4" THEN duration ELSE "0" END) as jam_istirahat'),
                    DB::raw('SUM(CASE WHEN absence_category_id = "10" THEN duration ELSE "0" END) as jam_lembur'),
                    DB::raw('SUM(CASE WHEN absence_category_id = "6" THEN duration ELSE "0" END) as jam_dinas_dalam'),
                    DB::raw('SUM(CASE WHEN absence_category_id = "12" THEN duration ELSE "0" END) as jam_permisi'),
                    // DB::raw("SUM(DATEDIFF(hour,duration)  as jam_test"),
                    'staffs.name as staff_name',
                    'staffs.code as staff_code',
                    'jobs.name as job_name',
                    'staffs.work_type_id as work_type_id',
                    'work_types.type as work_type',
                    'staffs.id as staff_id',
                    'subdapertements.name as subdapertement_name'


                )
                    ->join('absences', 'absences.id', '=', 'absence_logs.absence_id')
                    ->join('staffs', 'staffs.id', '=', 'absences.staff_id')
                    ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
                    ->leftJoin('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                    ->leftJoin('jobs', 'jobs.id', '=', 'staffs.job_id')
                    // ->groupBy('staffs.id')
                    ->where('staffs.id', $stf->id)
                    ->where('absences.status_active', '')
                    ->FilterDateWeb($request->from, $request->to)
                    ->first();
                if ($report) {
                    $data12 = array_merge($data12, [$report]);
                } else {
                    $data12 = array_merge($data12, []);
                }
            }
            $collectionStf = collect($data12);
            // dd($collectionStf[0]->staff_id);

            // ->get();

            // $day1 = "2023-02-12 12:30:00";
            // $day1 = strtotime($day1);
            // $day2 = "2023-02-12 14:00:00";
            // $day2 = strtotime($day2);

            // $diffHours = ($day2 - $day1) / 3600;
            // dd($report, $jumlah_cuti, $jumlah_sabtuminggu, $abtotal, $diffHours);


            $no = 1;

            // foreach ($report as $value) {
            //     $day = "";
            //     if (date("w", strtotime($value->date)) == 0) {
            //         $day = "Minggu";
            //     } else if (date("w", strtotime($value->date)) == 1) {
            //         $day = "Senin";
            //     } else if (date("w", strtotime($value->date)) == 2) {
            //         $day = "Selasa";
            //     } else if (date("w", strtotime($value->date)) == 3) {
            //         $day = "Rabu";
            //     } else if (date("w", strtotime($value->date)) == 4) {
            //         $day = "Kamis";
            //     } else if (date("w", strtotime($value->date)) == 5) {
            //         $day = "Jumat";
            //     } else if (date("w", strtotime($value->date)) == 6) {
            //         $day = "Sabtu";
            //     }
            //     $data[] = [
            //         'No' => $no++,
            //         'Kode' => $value->staff_code,
            //         'Nama' => $value->staff_name,
            //         'Karyawan' => '',
            //         'Sub_Bagian' => '',
            //         'Job' => $value->job_name,
            //         'Total Hari' => $abtotal,
            //         'Total Libur' => $jumlah_sabtuminggu,
            //         'Total Efektif Kerja' => $abtotal - $jumlah_sabtuminggu,
            //         'Total Hadir' => $value->hadir,
            //         'Total Alfa' => '',
            //         'Total Izin' => $value->izin,
            //         'Total Dinas Luar' => $value->dinas_luar,
            //         'Total Cuti' => $value->cuti,
            //         'Total Jam Kerja' => $value->jam_hadir,
            //         'Total Jam Istirahat' => $value->jam_istirahat,
            //         'Total Jam Lembur' => $value->jam_lembur,
            //         'Total Jam Dinas Dalam' => $value->jam_dinas_dusun,
            //         'Total Hari Dinas Dalam' => $value->dinas_dalam,
            //         'Total Jam Terlambat' => $value->dinas_dalam,
            //         'Total Hari Terlambat' => $value->terlambat,
            //         'Total Jam Permisi' => $value->jam_permisi,
            //         'Total Hari Permisi' => $value->permisi,
            //     ];
            // }

            // dd(count($collection->where('id', 1)->first()['hari_effective']));
            if ($request->ajax()) {
                //set query
                $table = Datatables::of($collectionStf);

                $table->addColumn('placeholder', '&nbsp;');
                $table->addColumn('actions', '&nbsp;');

                $table->editColumn('actions', function ($row) {
                    $viewGate = '';
                    $editGate = '';
                    $deleteGate = '';
                    $crudRoutePart = 'absence';

                    return view('partials.datatablesActions', compact(
                        'viewGate',
                        'editGate',
                        'deleteGate',
                        'crudRoutePart',
                        'row'
                    ));
                });
                $table->editColumn('staff_code', function ($row) {
                    return $row->staff_code ? $row->staff_code : "";
                });

                $table->editColumn('staff_name', function ($row) {
                    return $row->staff_name ? $row->staff_name : "";
                });

                $table->editColumn('dapertement_name', function ($row) {
                    return $row->dapertement_name ? $row->dapertement_name : "";
                });

                $table->editColumn('job_name', function ($row) {
                    return $row->job_name ? $row->job_name : "";
                });

                $table->editColumn('abtotal', function ($row) use ($abtotal) {
                    return $abtotal ? $abtotal : "";
                });

                $table->editColumn('jumlah_libur', function ($row) use ($abtotal, $collection, $from, $to) {
                    // return $jumlah_libur ? $jumlah_libur : "";

                    if ($row->work_type == "reguler") {
                        return ($abtotal - count($collection->where('id', 1)->first()['hari_effective']));
                    } else {
                        return ($abtotal - count(ShiftPlannerStaffs::select('shift_planner_staffs.*', DB::raw('DATE(shift_planner_staffs.start) as start'))->where('staff_id',  $row->staff_id)
                            //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$from, $to])->get()));
                            ->whereRaw("DATE(shift_planner_staffs.start) >= '".$from."' AND DATE(shift_planner_staffs.start) <= '".$to."'")->get()));
                    }
                });

                $table->editColumn('efective_kerja', function ($row) use ($collection, $from, $to) {
                    if ($row->work_type == "reguler") {
                        return count($collection->where('id', 1)->first()['hari_effective']);
                    } else {
                        return   count(ShiftPlannerStaffs::select('shift_planner_staffs.*', DB::raw('DATE(shift_planner_staffs.start) as start'))->where('staff_id',  $row->staff_id)
                            //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$from, $to])->get());
                            ->whereRaw("DATE(shift_planner_staffs.start) >= '".$from."' AND DATE(shift_planner_staffs.start) <= '".$to."'")->get());
                    }
                });

                $table->editColumn('hadir', function ($row) {
                    return $row->hadir ? $row->hadir : "";
                });
                $table->editColumn('alfa', function ($row) use ($collection, $from, $to) {
                    if ($row->work_type == "reguler") {
                        return (count($collection->where('id', 1)->first()['hari_effective']) - ($row->hadir + $row->izin + $row->dinas_luar + $row->cuti));
                    } else {
                        return (count(ShiftPlannerStaffs::select('shift_planner_staffs.*', DB::raw('DATE(shift_planner_staffs.start) as start'))->where('staff_id',  $row->staff_id)
                            //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$from, $to])->get()) - ($row->hadir + $row->izin + $row->dinas_luar + $row->cuti));
                            ->whereRaw("DATE(shift_planner_staffs.start) >= '".$from."' AND DATE(shift_planner_staffs.start) <= '".$to."'")->get()) - ($row->hadir + $row->izin + $row->dinas_luar + $row->cuti));
                    }
                });
                $table->editColumn('izin', function ($row) {
                    return $row->izin ? $row->izin : "";
                });
                $table->editColumn('dinas_luar', function ($row) {
                    return $row->dinas_luar ? $row->dinas_luar : "";
                });
                $table->editColumn('cuti', function ($row) {
                    return $row->cuti ? $row->cuti : "";
                });

                $table->editColumn('lembur', function ($row) {
                    return $row->lembur ? $row->lembur : "";
                });
                $table->editColumn('jam_hadir', function ($row) {
                    return $row->jam_hadir ? round($row->jam_hadir, 2) : "0";
                });
                $table->editColumn('jam_istirahat', function ($row) {
                    return $row->jam_istirahat ? round($row->jam_istirahat, 2)  : "0";
                });
                $table->editColumn('dinas_dalam', function ($row) {
                    return $row->dinas_dalam ? $row->dinas_dalam : "";
                });
                $table->editColumn('jam_lembur', function ($row) {
                    return $row->jam_lembur ?  round($row->jam_lembur, 2) : "0";
                });
                $table->editColumn('lambat', function ($row) {
                    return $row->lambat ? $row->lambat : "";
                });
                $table->editColumn('jam_lambat', function ($row) {
                    return $row->jam_lambat ? round($row->jam_lambat, 2) : "0";
                });
                $table->editColumn('cuti', function ($row) {
                    return $row->cuti ? $row->cuti : "";
                });

                $table->editColumn('permisi', function ($row) {
                    return $row->permisi ? $row->permisi : "";
                });
                $table->editColumn('jam_permisi', function ($row) {
                    return $row->jam_permisi ? $row->jam_permisi : "";
                });

                // $table->editColumn('jam_dinas_dalam', function ($row) {
                //     return $row->jam_dinas_dalam ? $row->jam_dinas_dalam : "";
                // });
                // $table->editColumn('hari_dinas_dalam', function ($row) {
                //     return $row->dinas_dalam ? $row->dinas_dalam : "";
                // });

                $table->editColumn('jam_permisi', function ($row) {
                    return $row->jam_permisi ? round($row->jam_permisi, 2)  : "0";
                });
                $table->editColumn('work_type', function ($row) {
                    return $row->work_type ? $row->work_type  : "";
                });


                $table->editColumn('hari_permisi', function ($row) {
                    return $row->permisi ? $row->permisi : "";
                });

                $table->editColumn('NIK', function ($row) {
                    return $row->NIK ? $row->NIK : "";
                });
                $table->rawColumns(['actions', 'placeholder']);

                $table->addIndexColumn();
                return $table->make(true);
            }
            // default view
            // return view('admin.schedule.index');

            return view('admin.absence.report');

            // dd($data);
            // return Excel::download(new AbsenceReport($data), 'report.xlsx');
        } catch (\Throwable $e) {
            // abort(500);
            dd($e);
        }
    }
}
