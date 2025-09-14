<?php

namespace App\Http\Controllers\Api\V1\Absence;

use App\Absence;
use App\AbsenceLog;
use App\Day;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\MessageLog;
use App\Requests;
use App\ShiftPlannerStaffs;
use App\Staff;
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
        if ($request->version != "2023-04-19") {
            $messageM = "Update ke V 23.04.19";
        }




        return response()->json([
            'message' => 'Success',
            // 'messageLog' => $messageLogs,
            'messageCount' => $messageCount,
            'staff' => $staff,
            'messageM' => $messageM,
            'month1' => "50",
            'month2' => "70",
            'month3' => "90",
            'versionNow' => "yes",
            'version' => 'Versi Baru 23.04.19',
            'monthName1' => 'Januari',
            'monthName2' => 'Februari',
            'monthName3' => 'Maret'

        ]);
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

        $staff = Staff::selectRaw('staffs.*, work_types.type, users.email, work_units.lng, work_units.lat, work_units.radius ')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->join('users', 'users.staff_id', '=', 'staffs.id')
            ->join('work_units', 'work_units.id', '=', 'staffs.work_unit_id')
            ->where('staffs.id', $request->staff_id)->first();


        if (date('d') > 20) {
            $awal1 = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . "-21")));
            $akhir1 = date("Y-m-d", strtotime('0 month', strtotime(date('Y-m') . "-20")));

            $awal2 = date("Y-m-d", strtotime('-2 month', strtotime(date('Y-m') . "-21")));
            $akhir2 = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . "-20")));

            $awal3 = date("Y-m-d", strtotime('-3 month', strtotime(date('Y-m') . "-21")));
            $akhir3 = date("Y-m-d", strtotime('-2 month', strtotime(date('Y-m') . "-20")));
        } else {
            $awal1 = date("Y-m-d", strtotime('-2 month', strtotime(date('Y-m') . "-21")));
            $akhir1 = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . "-20")));

            $awal2 = date("Y-m-d", strtotime('-3 month', strtotime(date('Y-m') . "-21")));
            $akhir2 = date("Y-m-d", strtotime('-2 month', strtotime(date('Y-m') . "-20")));

            $awal3 = date("Y-m-d", strtotime('-4 month', strtotime(date('Y-m') . "-21")));
            $akhir3 = date("Y-m-d", strtotime('-3 month', strtotime(date('Y-m') . "-20")));
        }


        // bulan 1 start
        $from1 = $awal1;
        $to1 = $akhir1;


        // tanggalnya diubah formatnya ke Y-m-d 
        $from1 = date_create_from_format('Y-m-d', $from1);
        $from1 = date_format($from1, 'Y-m-d');
        $from1 = strtotime($from1);

        $to1 = date_create_from_format('Y-m-d',  $to1);
        $to1 = date_format($to1, 'Y-m-d');
        $to1 = strtotime($to1);

        $haricuti = array();
        $sabtuminggu1 = array();

        for ($i = $from1; $i <=  $to1; $i += (60 * 60 * 24)) {
            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                $haricuti[] = $i;
            } else {
                $sabtuminggu1[] = $i;
            }
        }
        $jumlah_cuti = count($haricuti);
        $jumlah_sabtuminggu = count($sabtuminggu1);
        $abtotal = $jumlah_cuti + $jumlah_sabtuminggu;


        $hariefective1 = array();
        $harilibur = array();
        $sabtuminggu1 = array();
        $tglLibur1 = array();

        $work_types = WorkTypes::get();

        foreach ($work_types as $work_type) {
            $work_type_days1 = Day::select('days.*')->leftJoin(
                'work_type_days',
                function ($join) use ($work_type) {
                    $join->on('days.id', '=', 'work_type_days.day_id')
                        ->where('work_type_id', $work_type->id);
                }
            )
                ->where('work_type_days.day_id', '=', null)->get();
            // dd($work_type_days);
            // dd($work_type_days);
            $jadwallibur1 = [];
            foreach ($work_type_days1 as $work_type_day) {
                $jadwallibur1 = array_merge($jadwallibur1, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
            }

            // libur nasional
            $holidays1 = Holiday::select(DB::raw('DATE(holidays.start) AS start'), DB::raw('DATE(holidays.end) AS end'))
                //->whereBetween(DB::raw('DATE(holidays.start)'), [$awal1, $akhir1])
                //->orWhereBetween(DB::raw('DATE(holidays.end)'), [$awal1, $akhir1])
                ->whereRaw("DATE(holidays.start) >= '".$awal1."' AND DATE(holidays.start) <= '".$akhir1."'")
                ->orWhereRaw("DATE(holidays.end) >= '".$awal1."' AND DATE(holidays.end) <= '".$akhir1."'")
                ->get();
            // return $holidays1;
            foreach ($holidays1 as $holiday) {
                $awal_libur1 = date_create_from_format('Y-m-d', $holiday->start);
                $awal_libur1 = date_format($awal_libur1, 'Y-m-d');
                $awal_libur1 = strtotime($awal_libur1);

                $akhir_libur1 = date_create_from_format('Y-m-d', $holiday->end);
                $akhir_libur1 = date_format($akhir_libur1, 'Y-m-d');
                $akhir_libur1 = strtotime($akhir_libur1);

                $work_type_days1 = Day::select('days.*')->leftJoin(
                    'work_type_days',
                    function ($join) use ($work_type) {
                        $join->on('days.id', '=', 'work_type_days.day_id')
                            ->where('work_type_id', $work_type->id);
                    }
                )
                    ->where('work_type_days.day_id', '=', null)->get();

                $jadwallibur1 = [];
                foreach ($work_type_days1 as $work_type_day) {
                    $jadwallibur1 = array_merge($jadwallibur1, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
                }


                for ($i = $awal_libur1; $i < $akhir_libur1; $i += (60 * 60 * 24)) {
                    if (!in_array(date('w', $i), $jadwallibur1) &&  !in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur1)) {
                        $harilibur[] = $i;
                        $tglLibur1 = array_merge($tglLibur1, [date("Y-m-d", strtotime(date('Y-m-d', $i)))]);
                    } else {
                    }
                }
            }

            for ($i = $from1; $i <= $to1; $i += (60 * 60 * 24)) {

                if (!in_array(date('w', $i), $jadwallibur1) &&  !in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur1)) {
                    $hariefective1[] = ['id' => $i, 'date' => date("Y-m-d", strtotime(date('Y-m-d', $i)))];
                } else {
                    $sabtuminggu1[] = $i;
                }
            }

            $jumlah_efective1[] = ['id' => $work_type->id, 'hari_effective' => $hariefective1];
            $jadwallibur1 = [];
            $hariefective1 = [];
            $tglLibur1 = [];
        }

        $collection1 = collect($jumlah_efective1);


        $report1 = AbsenceLog::select(
            DB::raw('RIGHT(staffs.NIK , 3 ) AS NIK'),
            DB::raw('count(IF(absence_category_id = 1 and register != "" ,1,NULL)) as hadir'),
            DB::raw('count(IF(absence_category_id = 13 ,1,NULL)) as izin'),
            DB::raw('count(IF(absence_category_id = 7 ,1,NULL)) as dinas_luar'),
            DB::raw('count(IF(absence_category_id = 8 ,1,NULL)) as cuti'),
            DB::raw('count(IF(absence_category_id = 5 ,1,NULL)) as dinas_dalam'),
            DB::raw('count(IF(absence_category_id = 1 and late != ""  and late != "0" ,1,NULL)) as lambat'),
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
            ->groupBy('staffs.id')
            ->where('staff_id', $staff->id)
            ->FilterDate($awal1, $akhir1)
            ->first();
        if ($report1 && $report1->work_type == "reguler") {
            // return $report1;
            // return ($report1->hadir + $report1->dinas_luar);
            $month1 =  round((($report1->hadir + $report1->dinas_luar) / count($collection1->where('id', 1)->first()['hari_effective'])), 2) * 100;
        } else if ($report1 && $report1->work_type == "shift") {
            $month1 =  round((count(ShiftPlannerStaffs::select('shift_planner_staffs.*', DB::raw('DATE(shift_planner_staffs.start) as start'))->where('staff_id',  $report1->staff_id)
                //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$awal1, $akhir1])->get()) - ($report1->hadir + $report1->izin + $report1->dinas_luar + $report1->cuti)), 2) * 100;
                ->whereRaw("DATE(shift_planner_staffs.start) >= '".$awal1."' AND DATE(shift_planner_staffs.start) <= '".$akhir1."'")->get()) - ($report1->hadir + $report1->izin + $report1->dinas_luar + $report1->cuti)), 2) * 100;
        } else {
            $month1 =  0;
        }
        // bulan 1 end




        // bulan 2 start
        $from2 = $awal2;
        $to2 = $akhir2;


        // tanggalnya diubah formatnya ke Y-m-d 
        $from2 = date_create_from_format('Y-m-d', $from2);
        $from2 = date_format($from2, 'Y-m-d');
        $from2 = strtotime($from2);

        $to2 = date_create_from_format('Y-m-d',  $to2);
        $to2 = date_format($to2, 'Y-m-d');
        $to2 = strtotime($to2);

        $haricuti = array();
        $sabtuminggu2 = array();

        for ($i = $from2; $i <=  $to2; $i += (60 * 60 * 24)) {
            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                $haricuti[] = $i;
            } else {
                $sabtuminggu2[] = $i;
            }
        }
        $jumlah_cuti = count($haricuti);
        $jumlah_sabtuminggu = count($sabtuminggu2);
        $abtotal = $jumlah_cuti + $jumlah_sabtuminggu;


        $hariefective2 = array();
        $harilibur = array();
        $sabtuminggu2 = array();
        $tglLibur2 = array();

        $work_types = WorkTypes::get();

        foreach ($work_types as $work_type) {
            $work_type_days2 = Day::select('days.*')->leftJoin(
                'work_type_days',
                function ($join) use ($work_type) {
                    $join->on('days.id', '=', 'work_type_days.day_id')
                        ->where('work_type_id', $work_type->id);
                }
            )
                ->where('work_type_days.day_id', '=', null)->get();
            // dd($work_type_days);
            // dd($work_type_days);
            $jadwallibur2 = [];
            foreach ($work_type_days2 as $work_type_day) {
                $jadwallibur2 = array_merge($jadwallibur2, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
            }

            // libur nasional
            $holidays2 = Holiday::select(DB::raw('DATE(holidays.start) AS start'), DB::raw('DATE(holidays.end) AS end'))
                //->whereBetween(DB::raw('DATE(holidays.start)'), [$awal2, $akhir2])
                //->orWhereBetween(DB::raw('DATE(holidays.end)'), [$awal2, $akhir2])
                ->whereRaw("DATE(holidays.start) >= '".$awal2."' AND DATE(holidays.start) <= '".$akhir2."'")
                ->orWhereRaw("DATE(holidays.end) >= '".$awal2."' AND DATE(holidays.end) <= '".$akhir2."'")
                ->get();
            // return $holidays2;
            foreach ($holidays2 as $holiday) {
                $awal_libur2 = date_create_from_format('Y-m-d', $holiday->start);
                $awal_libur2 = date_format($awal_libur2, 'Y-m-d');
                $awal_libur2 = strtotime($awal_libur2);

                $akhir_libur2 = date_create_from_format('Y-m-d', $holiday->end);
                $akhir_libur2 = date_format($akhir_libur2, 'Y-m-d');
                $akhir_libur2 = strtotime($akhir_libur2);

                $work_type_days2 = Day::select('days.*')->leftJoin(
                    'work_type_days',
                    function ($join) use ($work_type) {
                        $join->on('days.id', '=', 'work_type_days.day_id')
                            ->where('work_type_id', $work_type->id);
                    }
                )
                    ->where('work_type_days.day_id', '=', null)->get();

                $jadwallibur2 = [];
                foreach ($work_type_days2 as $work_type_day) {
                    $jadwallibur2 = array_merge($jadwallibur2, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
                }


                for ($i = $awal_libur2; $i < $akhir_libur2; $i += (60 * 60 * 24)) {
                    if (!in_array(date('w', $i), $jadwallibur2) &&  !in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur2)) {
                        $harilibur[] = $i;
                        $tglLibur2 = array_merge($tglLibur2, [date("Y-m-d", strtotime(date('Y-m-d', $i)))]);
                    } else {
                    }
                }
            }

            for ($i = $from2; $i <= $to2; $i += (60 * 60 * 24)) {

                if (!in_array(date('w', $i), $jadwallibur2) &&  !in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur2)) {
                    $hariefective2[] = ['id' => $i, 'date' => date("Y-m-d", strtotime(date('Y-m-d', $i)))];
                } else {
                    $sabtuminggu2[] = $i;
                }
            }

            $jumlah_efective2[] = ['id' => $work_type->id, 'hari_effective' => $hariefective2];
            $jadwallibur2 = [];
            $hariefective2 = [];
            $tglLibur2 = [];
        }

        $collection2 = collect($jumlah_efective2);


        $report2 = AbsenceLog::select(
            DB::raw('RIGHT(staffs.NIK , 3 ) AS NIK'),
            DB::raw('count(IF(absence_category_id = 1 and register != "" ,1,NULL)) as hadir'),
            DB::raw('count(IF(absence_category_id = 13 ,1,NULL)) as izin'),
            DB::raw('count(IF(absence_category_id = 7 ,1,NULL)) as dinas_luar'),
            DB::raw('count(IF(absence_category_id = 8 ,1,NULL)) as cuti'),
            DB::raw('count(IF(absence_category_id = 5 ,1,NULL)) as dinas_dalam'),
            DB::raw('count(IF(absence_category_id = 1 and late != ""  and late != "0" ,1,NULL)) as lambat'),
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
            ->groupBy('staffs.id')
            ->where('staff_id', $staff->id)
            ->FilterDate($awal2, $akhir2)
            ->first();
        if ($report2 && $report2->work_type == "reguler") {
            // return [$report2, $report1];
            // return ($report2->hadir + $report2->dinas_luar);
            $month2 =  round((($report2->hadir + $report2->dinas_luar) / count($collection2->where('id', 1)->first()['hari_effective'])), 2) * 100;
        } else if ($report2 && $report2->work_type == "shift") {
            $month2 =  round((count(ShiftPlannerStaffs::select('shift_planner_staffs.*', DB::raw('DATE(shift_planner_staffs.start) as start'))->where('staff_id',  $report1->staff_id)
                //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$awal2, $akhir2])->get()) - ($report2->hadir + $report2->izin + $report2->dinas_luar + $report2->cuti)), 2) * 100;
                ->whereRaw("DATE(shift_planner_staffs.start) >= '".$awal2."' AND DATE(shift_planner_staffs.start) <= '".$akhir2."'")->get()) - ($report2->hadir + $report2->izin + $report2->dinas_luar + $report2->cuti)), 2) * 100;
        } else {
            $month2 = 0;
        }
        // bulan 2 end


        // bulan 3 start
        $from3 = $awal3;
        $to3 = $akhir3;


        // tanggalnya diubah formatnya ke Y-m-d 
        $from3 = date_create_from_format('Y-m-d', $from3);
        $from3 = date_format($from3, 'Y-m-d');
        $from3 = strtotime($from3);

        $to3 = date_create_from_format('Y-m-d',  $to3);
        $to3 = date_format($to3, 'Y-m-d');
        $to3 = strtotime($to3);

        $haricuti = array();
        $sabtuminggu3 = array();

        for ($i = $from3; $i <=  $to3; $i += (60 * 60 * 24)) {
            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                $haricuti[] = $i;
            } else {
                $sabtuminggu3[] = $i;
            }
        }
        $jumlah_cuti = count($haricuti);
        $jumlah_sabtuminggu = count($sabtuminggu3);
        $abtotal = $jumlah_cuti + $jumlah_sabtuminggu;


        $hariefective3 = array();
        $harilibur = array();
        $sabtuminggu3 = array();
        $tglLibur3 = array();

        $work_types = WorkTypes::get();

        foreach ($work_types as $work_type) {
            $work_type_days3 = Day::select('days.*')->leftJoin(
                'work_type_days',
                function ($join) use ($work_type) {
                    $join->on('days.id', '=', 'work_type_days.day_id')
                        ->where('work_type_id', $work_type->id);
                }
            )
                ->where('work_type_days.day_id', '=', null)->get();
            // dd($work_type_days);
            // dd($work_type_days);
            $jadwallibur3 = [];
            foreach ($work_type_days3 as $work_type_day) {
                $jadwallibur3 = array_merge($jadwallibur3, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
            }

            // libur nasional
            $holidays3 = Holiday::select(DB::raw('DATE(holidays.start) AS start'), DB::raw('DATE(holidays.end) AS end'))
                //->whereBetween(DB::raw('DATE(holidays.start)'), [$awal3, $akhir3])
                //->orWhereBetween(DB::raw('DATE(holidays.end)'), [$awal3, $akhir3])
                ->whereRaw("DATE(holidays.start) >= '".$awal3."' AND DATE(holidays.start) <= '".$akhir3."'")
                ->orWhereRaw("DATE(holidays.end) >= '".$awal3."' AND DATE(holidays.end) <= '".$akhir3."'")
                ->get();
            // return $holidays3;
            foreach ($holidays3 as $holiday) {
                $awal_libur3 = date_create_from_format('Y-m-d', $holiday->start);
                $awal_libur3 = date_format($awal_libur3, 'Y-m-d');
                $awal_libur3 = strtotime($awal_libur3);

                $akhir_libur3 = date_create_from_format('Y-m-d', $holiday->end);
                $akhir_libur3 = date_format($akhir_libur3, 'Y-m-d');
                $akhir_libur3 = strtotime($akhir_libur3);

                $work_type_days3 = Day::select('days.*')->leftJoin(
                    'work_type_days',
                    function ($join) use ($work_type) {
                        $join->on('days.id', '=', 'work_type_days.day_id')
                            ->where('work_type_id', $work_type->id);
                    }
                )
                    ->where('work_type_days.day_id', '=', null)->get();

                $jadwallibur3 = [];
                foreach ($work_type_days3 as $work_type_day) {
                    $jadwallibur3 = array_merge($jadwallibur3, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
                }


                for ($i = $awal_libur3; $i < $akhir_libur3; $i += (60 * 60 * 24)) {
                    if (!in_array(date('w', $i), $jadwallibur3) &&  !in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur3)) {
                        $harilibur[] = $i;
                        $tglLibur3 = array_merge($tglLibur3, [date("Y-m-d", strtotime(date('Y-m-d', $i)))]);
                    } else {
                    }
                }
            }

            for ($i = $from3; $i <= $to3; $i += (60 * 60 * 24)) {

                if (!in_array(date('w', $i), $jadwallibur3) &&  !in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $tglLibur3)) {
                    $hariefective3[] = ['id' => $i, 'date' => date("Y-m-d", strtotime(date('Y-m-d', $i)))];
                } else {
                    $sabtuminggu3[] = $i;
                }
            }

            $jumlah_efective3[] = ['id' => $work_type->id, 'hari_effective' => $hariefective3];
            $jadwallibur3 = [];
            $hariefective3 = [];
            $tglLibur3 = [];
        }

        $collection3 = collect($jumlah_efective3);


        $report3 = AbsenceLog::select(
            DB::raw('RIGHT(staffs.NIK , 3 ) AS NIK'),
            DB::raw('count(IF(absence_category_id = 1 and register != "" ,1,NULL)) as hadir'),
            DB::raw('count(IF(absence_category_id = 13 ,1,NULL)) as izin'),
            DB::raw('count(IF(absence_category_id = 7 ,1,NULL)) as dinas_luar'),
            DB::raw('count(IF(absence_category_id = 8 ,1,NULL)) as cuti'),
            DB::raw('count(IF(absence_category_id = 5 ,1,NULL)) as dinas_dalam'),
            DB::raw('count(IF(absence_category_id = 1 and late != ""  and late != "0" ,1,NULL)) as lambat'),
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
            ->groupBy('staffs.id')
            ->where('staff_id', $staff->id)
            ->FilterDate($awal3, $akhir3)
            ->first();
        if ($report3 && $report3->work_type == "reguler") {
            // return [$report3, $report1, $report2];
            // return ($report3->hadir + $report3->dinas_luar);
            $month3 =  round((($report3->hadir + $report3->dinas_luar) / count($collection3->where('id', 1)->first()['hari_effective'])), 2) * 100;
        } else if ($report3 && $report3->work_type == "shift") {
            $month3 =  round((count(ShiftPlannerStaffs::select('shift_planner_staffs.*', DB::raw('DATE(shift_planner_staffs.start) as start'))->where('staff_id',  $report1->staff_id)
                //->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$awal3, $akhir3])->get()) - ($report2->hadir + $report2->izin + $report2->dinas_luar + $report2->cuti)), 2) * 100;
                ->whereRaw("DATE(shift_planner_staffs.start) >= '".$awal3."' AND DATE(shift_planner_staffs.start) <= '".$akhir3."'")->get()) - ($report2->hadir + $report2->izin + $report2->dinas_luar + $report2->cuti)), 2) * 100;
        } else {
            $month3 = 0;
        }

        // return [$report3, $report2, $report1];
        // bulan 3 end
        $year = date('Y');
        $colorBox1 = "#044cd0";
        $colorBox2 = "#09aeae";
        $colorBox3 = "#e6bc15";
        $colorBox4 = "#d72503";

        return response()->json([
            'message' => 'Success',
            // 'month1' => round($month1),
            // 'month2' => round($month2),
            // 'month3' => round($month3),
            'month1' => 90,
            'month2' => 80,
            'month3' => 70,
            'nMonth1' => 90,
            'nMonth2' => 80,
            'nMonth3' => 70,
            'monthName1' => 'Januari',
            'monthName2' => 'Februari',
            'monthName3' => 'Maret',
            'colorBox4' => $colorBox1,
            'colorBox3' => $colorBox2,
            'colorBox2' => $colorBox3,
            'colorBox1' => $colorBox4,
            'colorChart1' => $colorBox1,
            'colorChart2' => $colorBox2,
            'colorChart3' => $colorBox3,
            'year' => $year,
            'start1' => $awal1,
            'end1' => $akhir1,
            'start2' => $awal2,
            'end2' => $akhir2,
            'start3' => $awal3,
            'end3' => $akhir3,

        ]);
    }
}
