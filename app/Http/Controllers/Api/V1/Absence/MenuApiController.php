<?php

namespace App\Http\Controllers\Api\V1\Absence;

use App\Absence;
use App\AbsenceLog;
use App\Day;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\MessageLog;
use App\Requests;
use App\ShiftPlannerStaff;
use App\ShiftPlannerStaffs;
use App\Staff;
use App\WorkTypeDays;
use App\WorkTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuApiController extends Controller
{

    public function index(Request $request)
    {
        // $holiday = Holiday::selectRaw('count(id) as id')->where('date', date('Y-m-d'))->first()->id;
        // $workDay = $this->countDays(date('Y'), date('n'), array(0, 6)) - $holiday;
        // $menu = Absence::selectRaw('(SUM(value)/2)/"' . $workDay . '" as persentage, SUM(value) as total')
        //     ->groupBy(DB::raw('MONTH(register)'))
        //     ->whereBetween('register', [date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01')))), date("Y-n-d", strtotime('+1 month', strtotime(date('Y-m-01'))))])
        //     ->where('user_id', $request->user_id)
        //     ->get();
        // $duty = Requests::where('category', 'duty')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->first();
        // $extra = Requests::where('category', 'extra')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->first();
        // $permit = Requests::where('category', 'permit')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->first();
        $staff = Staff::selectRaw('staffs.*, work_types.type, users.email, work_units.lng, work_units.lat, work_units.radius ')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->join('users', 'users.staff_id', '=', 'staffs.id')
            ->join('work_units', 'work_units.id', '=', 'staffs.work_unit_id')
            ->where('staffs.id', $request->staff_id)->first();
        // return ($staff);
        // $messageLogs = MessageLog::where('staff_id', $request->staff_id)->get();
        $messageLogs = MessageLog::where('staff_id', $request->staff_id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'DESC')->get();
        if (count($messageLogs) <= 0) {
            $messageCount = "";
            $messageM = "Tidak Ada Pesan Baru";
        } else {
            $messageCount = count($messageLogs);
            if ($messageCount > 10) {
                $messageCount = "10+";
            } else {
                $messageCount = "" . count($messageLogs);
            }
            $messageM = $messageLogs[0]->memo;
        }
        $versionNow = "yes";
        // if ($request->version != "2023-09-19") {
        //     $messageM = "Tolong update ke Versi terbaru di App Store atau Playstore";
        //     $versionNow = "not";
        // }



        if ($request->version) {
            return response()->json([
                'message' => 'Success',
                // 'messageLog' => $messageLogs,
                'messageCount' => $messageCount,
                'staff' => $staff,
                'messageM' => $messageM,
                'month1' => "50",
                'month2' => "70",
                'month3' => "90",
                'versionNow' => $versionNow,
                'version' => 'Versi Baru 23.09.19',
                'monthName1' => 'Januari',
                'monthName2' => 'Februari',
                'monthName3' => 'Maret'

            ]);
        } else {
            return response()->json([
                'message' => 'failed',
                // 'messageLog' => $messageLogs,

            ]);
        }
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
        echo $this->countDays(date('Y'), date('n'), array(0, 6)); // 23
    }

    public function graphic(Request $request)
    {

        $libur_dibulan2 = 0;
        if (date('d') > 20) {
            $awal1 = strtotime('-1 month', strtotime(date('Y-m') . "-21"));
            $akhir1 = strtotime('0 month', strtotime(date('Y-m') . "-20"));
            $namaB1 = date("F", strtotime('0 month', strtotime(date('Y-m') . "-21")));

            $awal2 = strtotime('-2 month', strtotime(date('Y-m') . "-21"));
            $akhir2 = strtotime('-1 month', strtotime(date('Y-m') . "-20"));

            $namaB2 = date("F", strtotime('-1 month', strtotime(date('Y-m') . "-21")));

            $awal3 = strtotime('-3 month', strtotime(date('Y-m') . "-21"));
            $akhir3 = strtotime('-2 month', strtotime(date('Y-m') . "-20"));

            $namaB3 = date("F", strtotime('-2 month', strtotime(date('Y-m') . "-21")));
        } else {
            // $awal1 = strtotime('-2 month', strtotime(date('Y-m') . "-21"));
            // $akhir1 = strtotime('-1 month', strtotime(date('Y-m') . "-20"));
            // $namaB1 = date("F", strtotime('-2 month', strtotime(date('Y-m') . "-21")));

            // $awal2 = strtotime('-3 month', strtotime(date('Y-m') . "-21"));
            // $akhir2 = strtotime('-2 month', strtotime(date('Y-m') . "-20"));
            // $namaB2 = date("F", strtotime('-3 month', strtotime(date('Y-m') . "-21")));

            // $awal3 = strtotime('-4 month', strtotime(date('Y-m') . "-21"));
            // $akhir3 = strtotime('-3 month', strtotime(date('Y-m') . "-20"));
            // $namaB3 = date("F", strtotime('-4 month', strtotime(date('Y-m') . "-21")));

            $awal1 = strtotime('-2 month', strtotime(date('Y-m') . "-21"));
            $akhir1 = strtotime('-1 month', strtotime(date('Y-m') . "-20"));
            $namaB1 = date("F", strtotime('-1 month', strtotime(date('Y-m') . "-21")));

            $awal2 = strtotime('-3 month', strtotime(date('Y-m') . "-21"));
            $akhir2 = strtotime('-2 month', strtotime(date('Y-m') . "-20"));
            $namaB2 = date("F", strtotime('-2 month', strtotime(date('Y-m') . "-21")));

            $awal3 = strtotime('-4 month', strtotime(date('Y-m') . "-21"));
            $akhir3 = strtotime('-3 month', strtotime(date('Y-m') . "-20"));
            $namaB3 = date("F", strtotime('-3 month', strtotime(date('Y-m') . "-21")));
        }

        $staff = Staff::selectRaw('work_types.type as work_type')->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $request->staff_id)->first();

        if ($staff->work_type == "reguler") {
            $hari_effective = [];
            $sabtuminggu = [];

            $work_type_day = [];
            $work_type = WorkTypes::where('type', 'reguler')->get();


            foreach ($work_type as $key => $value) {
                $work_type_day[$value->id] = [
                    WorkTypeDays::where('work_type_id', $value->id)->get()->keyBy('day_id')->toArray()
                ];
            }


            // mulai mencari persentase bulan 2
            $jumlah_hadir = 0;
            $absence = Absence::selectRaw('count(absence_logs.id) as jmlh_masuk, staffs.work_type_id')
                ->rightJoin('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->leftJoin('staffs', 'staffs.id', '=', 'absences.staff_id')
                ->where('absence_category_id', '1')
                ->where('absence_logs.status', '0')
                ->where('absences.status_active', '')
                ->where('absences.staff_id', $request->staff_id)
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [date('Y-m-d', $awal2), date('Y-m-d', $akhir2)])
                ->whereRaw("DATE(absences.created_at) >= '".date('Y-m-d', $awal2)."' AND DATE(absences.created_at) <= '".date('Y-m-d', $akhir2)."'")
                ->first();
            if ($absence) {
                $jumlah_hadir = $absence->jmlh_masuk;
            } else {
                $jumlah_hadir = 0;
            }
            for ($i = $awal2; $i <= $akhir2; $i += (60 * 60 * 24)) {
                if (!empty($work_type_day[$absence->work_type_id][0][date('w', $i)])) {
                    $hari_effective[] = $i;
                }
                if (date('w', $i) === 0 && $work_type_day[1][0]['7']) {
                    $hari_effective[] = $i;
                } else {
                    $sabtuminggu[] = $i;
                }
            }


            // libur nasional
            $holidays = Holiday::selectRaw('count(holidays.id) as holiday_total')
                //->whereBetween(DB::raw('DATE(holidays.start)'), [date('Y-m-d', $awal2), date('Y-m-d', $akhir2)])
                ->whereRaw("DATE(holidays.start) >= '".date('Y-m-d', $awal2)."' AND DATE(holidays.start) <= '".date('Y-m-d', $akhir2)."'")
                ->where('status', null)
                ->first();

            $jumlah_effective = count($hari_effective);

            $hari_setelah_libur = $jumlah_effective - $holidays->holiday_total;

            if ($hari_setelah_libur > 0) {
                $persentase2 =  $jumlah_hadir / $hari_setelah_libur;
            } else {
                $persentase2 = 0;
            }
            $libur_dibulan2 = $jumlah_effective;

            // mulai mencari persentase bulan 3

            $hari_effective = [];
            $sabtuminggu = [];
            $jumlah_hadir = 0;
            $absence = Absence::selectRaw('count(absence_logs.id) as jmlh_masuk, staffs.work_type_id')
                ->rightJoin('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->leftJoin('staffs', 'staffs.id', '=', 'absences.staff_id')
                ->where('absence_category_id', '1')
                ->where('absence_logs.status', '0')
                ->where('absences.status_active', '')
                ->where('absences.staff_id', $request->staff_id)
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [date('Y-m-d', $awal3), date('Y-m-d', $akhir3)])
                ->whereRaw("DATE(absences.created_at) >= '".date('Y-m-d', $awal3)."' AND DATE(absences.created_at) <= '".date('Y-m-d', $akhir3)."'")
                ->first();
            if ($absence) {
                $jumlah_hadir = $absence->jmlh_masuk;
            } else {
                $jumlah_hadir = 0;
            }
            for ($i = $awal3; $i <= $akhir3; $i += (60 * 60 * 24)) {
                if (!empty($work_type_day[$absence->work_type_id][0][date('w', $i)])) {
                    $hari_effective[] = $i;
                }
                if (date('w', $i) === 0 && $work_type_day[1][0]['7']) {
                    $hari_effective[] = $i;
                } else {
                    $sabtuminggu[] = $i;
                }
            }



            // libur nasional
            $holidays = Holiday::selectRaw('count(holidays.id) as holiday_total')
                //->whereBetween(DB::raw('DATE(holidays.start)'), [date('Y-m-d', $awal3), date('Y-m-d', $akhir3)])
                ->whereRaw("DATE(holidays.start) >= '".date('Y-m-d', $awal3)."' AND DATE(holidays.start) <= '".date('Y-m-d', $akhir3)."'")
                ->where('status', null)
                ->first();

            $jumlah_effective = count($hari_effective);

            $hari_setelah_libur = $jumlah_effective - $holidays->holiday_total;

            if ($hari_setelah_libur > 0) {
                $persentase3 =  $jumlah_hadir / $hari_setelah_libur;
            } else {
                $persentase3 = 0;
            }

            // mulai mencari persentase bulan 1

            $hari_effective = [];
            $sabtuminggu = [];

            $jumlah_hadir = 0;
            $absence = Absence::selectRaw('count(absence_logs.id) as jmlh_masuk, staffs.work_type_id')
                ->rightJoin('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->leftJoin('staffs', 'staffs.id', '=', 'absences.staff_id')
                ->where('absence_category_id', '1')
                ->where('absence_logs.status', '0')
                ->where('absences.staff_id', $request->staff_id)
                ->where('absences.status_active', '')
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [date('Y-m-d', $awal1), date('Y-m-d', $akhir1)])
                ->whereRaw("DATE(absences.created_at) >= '".date('Y-m-d', $awal1)."' AND DATE(absences.created_at) <= '".date('Y-m-d', $akhir1)."'")
                ->first();
            if ($absence) {
                $jumlah_hadir = $absence->jmlh_masuk;
            } else {
                $jumlah_hadir = 0;
            }
            for ($i = $awal1; $i <= $akhir1; $i += (60 * 60 * 24)) {
                if (!empty($work_type_day[$absence->work_type_id][0][date('w', $i)])) {
                    $hari_effective[] = $i;
                }
                if (date('w', $i) === 0 && $work_type_day[1][0]['7']) {
                    $hari_effective[] = $i;
                } else {
                    $sabtuminggu[] = $i;
                }
            }


            // libur nasional
            $holidays = Holiday::selectRaw('count(holidays.id) as holiday_total')
                //->whereBetween(DB::raw('DATE(holidays.start)'), [date('Y-m-d', $awal1), date('Y-m-d', $akhir1)])
                ->whereRaw("DATE(holidays.start) >= '".date('Y-m-d', $awal1)."' AND DATE(holidays.start) <= '".date('Y-m-d', $akhir1)."'")
                ->where('status', null)
                ->first();

            $jumlah_effective = count($hari_effective);

            $hari_setelah_libur = $jumlah_effective - $holidays->holiday_total;

            if ($hari_setelah_libur > 0) {
                $persentase =  $jumlah_hadir / $hari_setelah_libur;
            } else {
                $persentase = 0;
            }
        } else {
            // bulan 1
            $absence = Absence::selectRaw('count(absence_logs.id) as jmlh_masuk, staffs.work_type_id')
                ->rightJoin('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->leftJoin('staffs', 'staffs.id', '=', 'absences.staff_id')
                ->where('absence_category_id', '1')
                ->where('absence_logs.status', '0')
                ->where('absences.staff_id', $request->staff_id)
                ->where('absences.status_active', '')
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [date('Y-m-d', $awal1), date('Y-m-d', $akhir1)])
                ->whereRaw("DATE(absences.created_at) >= '".date('Y-m-d', $awal1)."' AND DATE(absences.created_at) <= '".date('Y-m-d', $akhir1)."'")
                ->first();
            $jumlah_hadir =  $absence->jmlh_masuk;
            $work = ShiftPlannerStaffs::selectRaw('count(shift_planner_staffs.id) as total')
                //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [date('Y-m-d', $awal1), date('Y-m-d', $akhir1)])
                ->whereRaw("DATE(shift_planner_staffs.start) >= '".date('Y-m-d', $awal1)."' AND DATE(shift_planner_staffs.start) <= '".date('Y-m-d', $akhir1)."'")
                ->where('staff_id', $request->staff_id)->first();
            if ($work->total > 0) {
                $persentase =  $jumlah_hadir / $work->total;
            } else {
                $persentase = 0;
            }

            $absence = Absence::selectRaw('count(absence_logs.id) as jmlh_masuk, staffs.work_type_id')
                ->rightJoin('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->leftJoin('staffs', 'staffs.id', '=', 'absences.staff_id')
                ->where('absence_category_id', '1')
                ->where('absence_logs.status', '0')
                ->where('absences.staff_id', $request->staff_id)
                ->where('absences.status_active', '')
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [date('Y-m-d', $awal2), date('Y-m-d', $akhir2)])
                ->whereRaw("DATE(absences.created_at) >= '".date('Y-m-d', $awal2)."' AND DATE(absences.created_at) <= '".date('Y-m-d', $akhir2)."'")
                ->first();
            $jumlah_hadir =  $absence->jmlh_masuk;
            $work = ShiftPlannerStaffs::selectRaw('count(shift_planner_staffs.id) as total')
                //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [date('Y-m-d', $awal2), date('Y-m-d', $akhir2)])
                ->whereRaw("DATE(shift_planner_staffs.start) >= '".date('Y-m-d', $awal2)."' AND DATE(shift_planner_staffs.start) <= '".date('Y-m-d', $akhir2)."'")
                ->where('staff_id', $request->staff_id)->first();
            if ($work->total > 0) {
                $persentase2 =  $jumlah_hadir / $work->total;
            } else {
                $persentase2 = 0;
            }

            $absence = Absence::selectRaw('count(absence_logs.id) as jmlh_masuk, staffs.work_type_id')
                ->rightJoin('absence_logs', 'absences.id', '=', 'absence_logs.absence_id')
                ->leftJoin('staffs', 'staffs.id', '=', 'absences.staff_id')
                ->where('absence_category_id', '1')
                ->where('absence_logs.status', '0')
                ->where('absences.staff_id', $request->staff_id)
                ->where('absences.status_active', '')
                //->whereBetween(DB::raw('DATE(absences.created_at)'), [date('Y-m-d', $awal3), date('Y-m-d', $akhir3)])
                ->whereRaw("DATE(absences.created_at) >= '".date('Y-m-d', $awal3)."' AND DATE(absences.created_at) <= '".date('Y-m-d', $akhir3)."'")
                ->first();
            $jumlah_hadir =  $absence->jmlh_masuk;
            $work = ShiftPlannerStaffs::selectRaw('count(shift_planner_staffs.id) as total')
                //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [date('Y-m-d', $awal3), date('Y-m-d', $akhir3)])
                ->whereRaw("DATE(shift_planner_staffs.start) >= '".date('Y-m-d', $awal3)."' AND DATE(shift_planner_staffs.start) <= '".date('Y-m-d', $akhir3)."'")
                ->where('staff_id', $request->staff_id)->first();
            if ($work->total > 0) {
                $persentase3 =  $jumlah_hadir / $work->total;
            } else {
                $persentase3 = 0;
            }
        }



        // return [$report3, $report2, $report1];
        // bulan 3 end
        $year = date('Y');
        $colorBox1 = "#044cd0";
        $colorBox2 = "#09aeae";
        $colorBox3 = "#e6bc15";
        $colorBox4 = "#d72503";


        if (($persentase * 100) > 95) {
            $color1 = $colorBox1;
        } else if (($persentase * 100) > 80) {
            $color1 = $colorBox2;
        } else if (($persentase * 100) > 50) {
            $color1 = $colorBox3;
        } else {
            $color1 = $colorBox4;
        }

        if (($persentase2 * 100) > 95) {
            $color2 = $colorBox1;
        } else if (($persentase2 * 100) > 80) {
            $color2 = $colorBox2;
        } else if (($persentase2 * 100) > 50) {
            $color2 = $colorBox3;
        } else {
            $color2 = $colorBox4;
        }

        if (($persentase3 * 100) > 95) {
            $color3 = $colorBox1;
        } else if (($persentase3 * 100) > 80) {
            $color3 = $colorBox2;
        } else if (($persentase3 * 100) > 50) {
            $color3 = $colorBox3;
        } else {
            $color3 = $colorBox4;
        }


        return response()->json([
            'message' => 'Success',
            'month1' => number_format(($persentase * 100), 2),
            'month2' => number_format(($persentase2 * 100), 2),
            'month3' => number_format(($persentase3 * 100), 2),
            'nMonth1' => number_format(($persentase * 100), 2),
            'nMonth2' => number_format(($persentase2 * 100), 2),
            'nMonth3' => number_format(($persentase3 * 100), 2),
            'monthName1' => $namaB1,
            'monthName2' => $namaB2,
            'monthName3' => $namaB3,
            'colorBox4' => $colorBox1,
            'colorBox3' => $colorBox2,
            'colorBox2' => $colorBox3,
            'colorBox1' => $colorBox4,
            'colorChart1' => $color1,
            'colorChart2' => $color2,
            'colorChart3' => $color3,
            'year' => $year,
            'start1' => date('Y-m-d', $awal1),
            'end1' =>  date('Y-m-d', $akhir1),
            'start2' =>  date('Y-m-d', $awal2),
            'end2' =>  date('Y-m-d', $akhir2),
            'start3' =>  date('Y-m-d', $awal3),
            'end3' =>  date('Y-m-d', $akhir3),
            'hariLibur2' => $libur_dibulan2
            // 'tess' => $jumlah_effective,
            // 'hdhdh' =>  $absence

        ]);
    }
}
