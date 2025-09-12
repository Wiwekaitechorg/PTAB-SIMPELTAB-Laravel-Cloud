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
        $staff = Staff::selectRaw(base64_decode('c3RhZmZzLiosIHdvcmtfdHlwZXMudHlwZSwgdXNlcnMuZW1haWwsIHdvcmtfdW5pdHMubG5nLCB3b3JrX3VuaXRzLmxhdCwgd29ya191bml0cy5yYWRpdXMg'))
            ->join(base64_decode('d29ya190eXBlcw=='), base64_decode('c3RhZmZzLndvcmtfdHlwZV9pZA=='), base64_decode('PQ=='), base64_decode('d29ya190eXBlcy5pZA=='))
            ->join(base64_decode('dXNlcnM='), base64_decode('dXNlcnMuc3RhZmZfaWQ='), base64_decode('PQ=='), base64_decode('c3RhZmZzLmlk'))
            ->join(base64_decode('d29ya191bml0cw=='), base64_decode('d29ya191bml0cy5pZA=='), base64_decode('PQ=='), base64_decode('c3RhZmZzLndvcmtfdW5pdF9pZA=='))
            ->where(base64_decode('c3RhZmZzLmlk'), $request->staff_id)->first();
        $messageLogs = MessageLog::where(base64_decode('c3RhZmZfaWQ='), $request->staff_id)
            ->where(base64_decode('c3RhdHVz'), base64_decode('cGVuZGluZw=='))
            ->orderBy(base64_decode('Y3JlYXRlZF9hdA=='), base64_decode('REVTQw=='))->get();
        if (count($messageLogs) <= 0) {
            $messageCount = '';
            $messageM = base64_decode('VGlkYWsgQWRhIFBlc2FuIEJhcnU=');
        } else {
            $messageCount = count($messageLogs);
            if ($messageCount > 10) {
                $messageCount = base64_decode('MTAr');
            } else {
                $messageCount = '' . count($messageLogs);
            }
            $messageM = $messageLogs[0]->memo;
        }
        $versionNow = base64_decode('eWVz');
        if ($request->version != base64_decode('MjAyMy0wOS0xOQ==')) {
            //$messageM = base64_decode('VG9sb25nIHVwZGF0ZSBrZSBWZXJzaSB0ZXJiYXJ1IGRpIEFwcCBTdG9yZSBhdGF1IFBsYXlzdG9yZQ==');
            //$versionNow = base64_decode('bm90');
        }



        if ($request->version) {
            return response()->json([
                base64_decode('bWVzc2FnZQ==') => base64_decode('U3VjY2Vzcw=='),
                base64_decode('bWVzc2FnZUNvdW50') => $messageCount,
                base64_decode('c3RhZmY=') => $staff,
                base64_decode('bWVzc2FnZU0=') => $messageM,
                base64_decode('bW9udGgx') => base64_decode('NTA='),
                base64_decode('bW9udGgy') => base64_decode('NzA='),
                base64_decode('bW9udGgz') => base64_decode('OTA='),
                base64_decode('dmVyc2lvbk5vdw==') => $versionNow,
                base64_decode('dmVyc2lvbg==') => base64_decode('VmVyc2kgQmFydSAyMy4wOS4xOQ=='),
                base64_decode('bW9udGhOYW1lMQ==') => base64_decode('SmFudWFyaQ=='),
                base64_decode('bW9udGhOYW1lMg==') => base64_decode('RmVicnVhcmk='),
                base64_decode('bW9udGhOYW1lMw==') => base64_decode('TWFyZXQ=')

            ]);
        } else {
            return response()->json([
                base64_decode('bWVzc2FnZQ==') => base64_decode('ZmFpbGVk'),

            ]);
        }
    }

    function countDays($year, $month, $ignore)
    {
        $count = 0;
        $counter = mktime(0, 0, 0, $month, 1, $year);
        while (date(base64_decode('bg=='), $counter) == $month) {
            if (in_array(date(base64_decode('dw=='), $counter), $ignore) == false) {
                $count++;
            }
            $counter = strtotime(base64_decode('KzEgZGF5'), $counter);
        }
        return  $count;
    }
    public function test()
    {
        echo $this->countDays(date(base64_decode('WQ==')), date(base64_decode('bg==')), array(0, 6));
    }

    public function graphic(Request $request)
    {

        if (date(base64_decode('ZA==')) > 20) {
            $awal1 = strtotime(base64_decode('LTEgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx')));
            $akhir1 = strtotime(base64_decode('MCBtb250aA=='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIw')));
            $namaB1 = date(base64_decode('Rg=='), strtotime(base64_decode('MCBtb250aA=='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx'))));

            $awal2 = strtotime(base64_decode('LTIgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx')));
            $akhir2 = strtotime(base64_decode('LTEgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIw')));

            $namaB2 = date(base64_decode('Rg=='), strtotime(base64_decode('LTEgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx'))));

            $awal3 = strtotime(base64_decode('LTMgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx')));
            $akhir3 = strtotime(base64_decode('LTIgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIw')));

            $namaB3 = date(base64_decode('Rg=='), strtotime(base64_decode('LTIgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx'))));
        } else {



            $awal1 = strtotime(base64_decode('LTIgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx')));
            $akhir1 = strtotime(base64_decode('LTEgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIw')));
            $namaB1 = date(base64_decode('Rg=='), strtotime(base64_decode('LTEgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx'))));

            $awal2 = strtotime(base64_decode('LTMgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx')));
            $akhir2 = strtotime(base64_decode('LTIgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIw')));
            $namaB2 = date(base64_decode('Rg=='), strtotime(base64_decode('LTIgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx'))));

            $awal3 = strtotime(base64_decode('LTQgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx')));
            $akhir3 = strtotime(base64_decode('LTMgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIw')));
            $namaB3 = date(base64_decode('Rg=='), strtotime(base64_decode('LTMgbW9udGg='), strtotime(date(base64_decode('WS1t')) . base64_decode('LTIx'))));
        }

        $staff = Staff::selectRaw(base64_decode('d29ya190eXBlcy50eXBlIGFzIHdvcmtfdHlwZQ=='))->join(base64_decode('d29ya190eXBlcw=='), base64_decode('c3RhZmZzLndvcmtfdHlwZV9pZA=='), base64_decode('PQ=='), base64_decode('d29ya190eXBlcy5pZA=='))
            ->where(base64_decode('c3RhZmZzLmlk'), $request->staff_id)->first();

        if ($staff->work_type == base64_decode('cmVndWxlcg==')) {
            $hari_effective = [];
            $sabtuminggu = [];

            $work_type_day = [];
            $work_type = WorkTypes::where(base64_decode('dHlwZQ=='), base64_decode('cmVndWxlcg=='))->get();


            foreach ($work_type as $key => $value) {
                $work_type_day[$value->id] = [
                    WorkTypeDays::where(base64_decode('d29ya190eXBlX2lk'), $value->id)->get()->keyBy(base64_decode('ZGF5X2lk'))->toArray()
                ];
            }


            $jumlah_hadir = 0;
            $absence = Absence::selectRaw(base64_decode('Y291bnQoYWJzZW5jZV9sb2dzLmlkKSBhcyBqbWxoX21hc3VrLCBzdGFmZnMud29ya190eXBlX2lk'))
                ->rightJoin(base64_decode('YWJzZW5jZV9sb2dz'), base64_decode('YWJzZW5jZXMuaWQ='), base64_decode('PQ=='), base64_decode('YWJzZW5jZV9sb2dzLmFic2VuY2VfaWQ='))
                ->leftJoin(base64_decode('c3RhZmZz'), base64_decode('c3RhZmZzLmlk'), base64_decode('PQ=='), base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='))
                ->where(base64_decode('YWJzZW5jZV9jYXRlZ29yeV9pZA=='), base64_decode('MQ=='))
                ->where(base64_decode('YWJzZW5jZV9sb2dzLnN0YXR1cw=='), base64_decode('MA=='))
                ->where(base64_decode('YWJzZW5jZXMuc3RhdHVzX2FjdGl2ZQ=='), '')
                ->where(base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='), $request->staff_id)
                ->whereBetween(DB::raw(base64_decode('REFURShhYnNlbmNlcy5jcmVhdGVkX2F0KQ==')), [date(base64_decode('WS1tLWQ='), $awal2), date(base64_decode('WS1tLWQ='), $akhir2)])
                ->first();
            if ($absence) {
                $jumlah_hadir = $absence->jmlh_masuk;
            } else {
                $jumlah_hadir = 0;
            }
            for ($i = $awal2; $i <= $akhir2; $i += (60 * 60 * 24)) {
                if (!empty($work_type_day[$absence->work_type_id][0][date(base64_decode('dw=='), $i)])) {
                    $hari_effective[] = $i;
                }
                if (date(base64_decode('dw=='), $i) === 0 && $work_type_day[1][0][base64_decode('Nw==')]) {
                    $hari_effective[] = $i;
                } else {
                    $sabtuminggu[] = $i;
                }
            }


            $holidays = Holiday::selectRaw(base64_decode('Y291bnQoaG9saWRheXMuaWQpIGFzIGhvbGlkYXlfdG90YWw='))
                ->whereBetween(DB::raw(base64_decode('REFURShob2xpZGF5cy5zdGFydCk=')), [date(base64_decode('WS1tLWQ='), $awal2), date(base64_decode('WS1tLWQ='), $akhir2)])
                ->first();

            $jumlah_effective = count($hari_effective);

            $hari_setelah_libur = $jumlah_effective - $holidays->holyday_total;

            if ($hari_setelah_libur > 0) {
                $persentase2 =  $jumlah_hadir / $hari_setelah_libur;
            } else {
                $persentase2 = 0;
            }



            $hari_effective = [];
            $sabtuminggu = [];
            $jumlah_hadir = 0;
            $absence = Absence::selectRaw(base64_decode('Y291bnQoYWJzZW5jZV9sb2dzLmlkKSBhcyBqbWxoX21hc3VrLCBzdGFmZnMud29ya190eXBlX2lk'))
                ->rightJoin(base64_decode('YWJzZW5jZV9sb2dz'), base64_decode('YWJzZW5jZXMuaWQ='), base64_decode('PQ=='), base64_decode('YWJzZW5jZV9sb2dzLmFic2VuY2VfaWQ='))
                ->leftJoin(base64_decode('c3RhZmZz'), base64_decode('c3RhZmZzLmlk'), base64_decode('PQ=='), base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='))
                ->where(base64_decode('YWJzZW5jZV9jYXRlZ29yeV9pZA=='), base64_decode('MQ=='))
                ->where(base64_decode('YWJzZW5jZV9sb2dzLnN0YXR1cw=='), base64_decode('MA=='))
                ->where(base64_decode('YWJzZW5jZXMuc3RhdHVzX2FjdGl2ZQ=='), '')
                ->where(base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='), $request->staff_id)
                ->whereBetween(DB::raw(base64_decode('REFURShhYnNlbmNlcy5jcmVhdGVkX2F0KQ==')), [date(base64_decode('WS1tLWQ='), $awal3), date(base64_decode('WS1tLWQ='), $akhir3)])
                ->first();
            if ($absence) {
                $jumlah_hadir = $absence->jmlh_masuk;
            } else {
                $jumlah_hadir = 0;
            }
            for ($i = $awal3; $i <= $akhir3; $i += (60 * 60 * 24)) {
                if (!empty($work_type_day[$absence->work_type_id][0][date(base64_decode('dw=='), $i)])) {
                    $hari_effective[] = $i;
                }
                if (date(base64_decode('dw=='), $i) === 0 && $work_type_day[1][0][base64_decode('Nw==')]) {
                    $hari_effective[] = $i;
                } else {
                    $sabtuminggu[] = $i;
                }
            }



            $holidays = Holiday::selectRaw(base64_decode('Y291bnQoaG9saWRheXMuaWQpIGFzIGhvbGlkYXlfdG90YWw='))
                ->whereBetween(DB::raw(base64_decode('REFURShob2xpZGF5cy5zdGFydCk=')), [date(base64_decode('WS1tLWQ='), $awal3), date(base64_decode('WS1tLWQ='), $akhir3)])
                ->first();

            $jumlah_effective = count($hari_effective);

            $hari_setelah_libur = $jumlah_effective - $holidays->holyday_total;

            if ($hari_setelah_libur > 0) {
                $persentase3 =  $jumlah_hadir / $hari_setelah_libur;
            } else {
                $persentase3 = 0;
            }


            $hari_effective = [];
            $sabtuminggu = [];

            $jumlah_hadir = 0;
            $absence = Absence::selectRaw(base64_decode('Y291bnQoYWJzZW5jZV9sb2dzLmlkKSBhcyBqbWxoX21hc3VrLCBzdGFmZnMud29ya190eXBlX2lk'))
                ->rightJoin(base64_decode('YWJzZW5jZV9sb2dz'), base64_decode('YWJzZW5jZXMuaWQ='), base64_decode('PQ=='), base64_decode('YWJzZW5jZV9sb2dzLmFic2VuY2VfaWQ='))
                ->leftJoin(base64_decode('c3RhZmZz'), base64_decode('c3RhZmZzLmlk'), base64_decode('PQ=='), base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='))
                ->where(base64_decode('YWJzZW5jZV9jYXRlZ29yeV9pZA=='), base64_decode('MQ=='))
                ->where(base64_decode('YWJzZW5jZV9sb2dzLnN0YXR1cw=='), base64_decode('MA=='))
                ->where(base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='), $request->staff_id)
                ->where(base64_decode('YWJzZW5jZXMuc3RhdHVzX2FjdGl2ZQ=='), '')
                ->whereBetween(DB::raw(base64_decode('REFURShhYnNlbmNlcy5jcmVhdGVkX2F0KQ==')), [date(base64_decode('WS1tLWQ='), $awal1), date(base64_decode('WS1tLWQ='), $akhir1)])
                ->first();
            if ($absence) {
                $jumlah_hadir = $absence->jmlh_masuk;
            } else {
                $jumlah_hadir = 0;
            }
            for ($i = $awal1; $i <= $akhir1; $i += (60 * 60 * 24)) {
                if (!empty($work_type_day[$absence->work_type_id][0][date(base64_decode('dw=='), $i)])) {
                    $hari_effective[] = $i;
                }
                if (date(base64_decode('dw=='), $i) === 0 && $work_type_day[1][0][base64_decode('Nw==')]) {
                    $hari_effective[] = $i;
                } else {
                    $sabtuminggu[] = $i;
                }
            }


            $holidays = Holiday::selectRaw(base64_decode('Y291bnQoaG9saWRheXMuaWQpIGFzIGhvbGlkYXlfdG90YWw='))
                ->whereBetween(DB::raw(base64_decode('REFURShob2xpZGF5cy5zdGFydCk=')), [date(base64_decode('WS1tLWQ='), $awal1), date(base64_decode('WS1tLWQ='), $akhir1)])
                ->first();

            $jumlah_effective = count($hari_effective);

            $hari_setelah_libur = $jumlah_effective - $holidays->holyday_total;

            if ($hari_setelah_libur > 0) {
                $persentase =  $jumlah_hadir / $hari_setelah_libur;
            } else {
                $persentase = 0;
            }
        } else {
            $absence = Absence::selectRaw(base64_decode('Y291bnQoYWJzZW5jZV9sb2dzLmlkKSBhcyBqbWxoX21hc3VrLCBzdGFmZnMud29ya190eXBlX2lk'))
                ->rightJoin(base64_decode('YWJzZW5jZV9sb2dz'), base64_decode('YWJzZW5jZXMuaWQ='), base64_decode('PQ=='), base64_decode('YWJzZW5jZV9sb2dzLmFic2VuY2VfaWQ='))
                ->leftJoin(base64_decode('c3RhZmZz'), base64_decode('c3RhZmZzLmlk'), base64_decode('PQ=='), base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='))
                ->where(base64_decode('YWJzZW5jZV9jYXRlZ29yeV9pZA=='), base64_decode('MQ=='))
                ->where(base64_decode('YWJzZW5jZV9sb2dzLnN0YXR1cw=='), base64_decode('MA=='))
                ->where(base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='), $request->staff_id)
                ->where(base64_decode('YWJzZW5jZXMuc3RhdHVzX2FjdGl2ZQ=='), '')
                ->whereBetween(DB::raw(base64_decode('REFURShhYnNlbmNlcy5jcmVhdGVkX2F0KQ==')), [date(base64_decode('WS1tLWQ='), $awal1), date(base64_decode('WS1tLWQ='), $akhir1)])
                ->first();
            $jumlah_hadir =  $absence->jmlh_masuk;
            $work = ShiftPlannerStaffs::selectRaw(base64_decode('Y291bnQoc2hpZnRfcGxhbm5lcl9zdGFmZnMuaWQpIGFzIHRvdGFs'))
                ->whereBetween(DB::raw(base64_decode('REFURShzaGlmdF9wbGFubmVyX3N0YWZmcy5zdGFydCk=')), [date(base64_decode('WS1tLWQ='), $awal1), date(base64_decode('WS1tLWQ='), $akhir1)])
                ->where(base64_decode('c3RhZmZfaWQ='), $request->staff_id)->first();
            if ($work->total > 0) {
                $persentase =  $jumlah_hadir / $work->total;
            } else {
                $persentase = 0;
            }

            $absence = Absence::selectRaw(base64_decode('Y291bnQoYWJzZW5jZV9sb2dzLmlkKSBhcyBqbWxoX21hc3VrLCBzdGFmZnMud29ya190eXBlX2lk'))
                ->rightJoin(base64_decode('YWJzZW5jZV9sb2dz'), base64_decode('YWJzZW5jZXMuaWQ='), base64_decode('PQ=='), base64_decode('YWJzZW5jZV9sb2dzLmFic2VuY2VfaWQ='))
                ->leftJoin(base64_decode('c3RhZmZz'), base64_decode('c3RhZmZzLmlk'), base64_decode('PQ=='), base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='))
                ->where(base64_decode('YWJzZW5jZV9jYXRlZ29yeV9pZA=='), base64_decode('MQ=='))
                ->where(base64_decode('YWJzZW5jZV9sb2dzLnN0YXR1cw=='), base64_decode('MA=='))
                ->where(base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='), $request->staff_id)
                ->where(base64_decode('YWJzZW5jZXMuc3RhdHVzX2FjdGl2ZQ=='), '')
                ->whereBetween(DB::raw(base64_decode('REFURShhYnNlbmNlcy5jcmVhdGVkX2F0KQ==')), [date(base64_decode('WS1tLWQ='), $awal2), date(base64_decode('WS1tLWQ='), $akhir2)])
                ->first();
            $jumlah_hadir =  $absence->jmlh_masuk;
            $work = ShiftPlannerStaffs::selectRaw(base64_decode('Y291bnQoc2hpZnRfcGxhbm5lcl9zdGFmZnMuaWQpIGFzIHRvdGFs'))
                ->whereBetween(DB::raw(base64_decode('REFURShzaGlmdF9wbGFubmVyX3N0YWZmcy5zdGFydCk=')), [date(base64_decode('WS1tLWQ='), $awal2), date(base64_decode('WS1tLWQ='), $akhir2)])
                ->where(base64_decode('c3RhZmZfaWQ='), $request->staff_id)->first();
            if ($work->total > 0) {
                $persentase2 =  $jumlah_hadir / $work->total;
            } else {
                $persentase2 = 0;
            }

            $absence = Absence::selectRaw(base64_decode('Y291bnQoYWJzZW5jZV9sb2dzLmlkKSBhcyBqbWxoX21hc3VrLCBzdGFmZnMud29ya190eXBlX2lk'))
                ->rightJoin(base64_decode('YWJzZW5jZV9sb2dz'), base64_decode('YWJzZW5jZXMuaWQ='), base64_decode('PQ=='), base64_decode('YWJzZW5jZV9sb2dzLmFic2VuY2VfaWQ='))
                ->leftJoin(base64_decode('c3RhZmZz'), base64_decode('c3RhZmZzLmlk'), base64_decode('PQ=='), base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='))
                ->where(base64_decode('YWJzZW5jZV9jYXRlZ29yeV9pZA=='), base64_decode('MQ=='))
                ->where(base64_decode('YWJzZW5jZV9sb2dzLnN0YXR1cw=='), base64_decode('MA=='))
                ->where(base64_decode('YWJzZW5jZXMuc3RhZmZfaWQ='), $request->staff_id)
                ->where(base64_decode('YWJzZW5jZXMuc3RhdHVzX2FjdGl2ZQ=='), '')
                ->whereBetween(DB::raw(base64_decode('REFURShhYnNlbmNlcy5jcmVhdGVkX2F0KQ==')), [date(base64_decode('WS1tLWQ='), $awal3), date(base64_decode('WS1tLWQ='), $akhir3)])
                ->first();
            $jumlah_hadir =  $absence->jmlh_masuk;
            $work = ShiftPlannerStaffs::selectRaw(base64_decode('Y291bnQoc2hpZnRfcGxhbm5lcl9zdGFmZnMuaWQpIGFzIHRvdGFs'))
                ->whereBetween(DB::raw(base64_decode('REFURShzaGlmdF9wbGFubmVyX3N0YWZmcy5zdGFydCk=')), [date(base64_decode('WS1tLWQ='), $awal3), date(base64_decode('WS1tLWQ='), $akhir3)])
                ->where(base64_decode('c3RhZmZfaWQ='), $request->staff_id)->first();
            if ($work->total > 0) {
                $persentase3 =  $jumlah_hadir / $work->total;
            } else {
                $persentase3 = 0;
            }
        }



        $year = date(base64_decode('WQ=='));
        $colorBox1 = base64_decode('IzA0NGNkMA==');
        $colorBox2 = base64_decode('IzA5YWVhZQ==');
        $colorBox3 = base64_decode('I2U2YmMxNQ==');
        $colorBox4 = base64_decode('I2Q3MjUwMw==');


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
            base64_decode('bWVzc2FnZQ==') => base64_decode('U3VjY2Vzcw=='),
            base64_decode('bW9udGgx') => number_format(($persentase * 100), 2),
            base64_decode('bW9udGgy') => number_format(($persentase2 * 100), 2),
            base64_decode('bW9udGgz') => number_format(($persentase3 * 100), 2),
            base64_decode('bk1vbnRoMQ==') => number_format(($persentase * 100), 2),
            base64_decode('bk1vbnRoMg==') => number_format(($persentase2 * 100), 2),
            base64_decode('bk1vbnRoMw==') => number_format(($persentase3 * 100), 2),
            base64_decode('bW9udGhOYW1lMQ==') => $namaB1,
            base64_decode('bW9udGhOYW1lMg==') => $namaB2,
            base64_decode('bW9udGhOYW1lMw==') => $namaB3,
            base64_decode('Y29sb3JCb3g0') => $colorBox1,
            base64_decode('Y29sb3JCb3gz') => $colorBox2,
            base64_decode('Y29sb3JCb3gy') => $colorBox3,
            base64_decode('Y29sb3JCb3gx') => $colorBox4,
            base64_decode('Y29sb3JDaGFydDE=') => $color1,
            base64_decode('Y29sb3JDaGFydDI=') => $color2,
            base64_decode('Y29sb3JDaGFydDM=') => $color3,
            base64_decode('eWVhcg==') => $year,
            base64_decode('c3RhcnQx') => date(base64_decode('WS1tLWQ='), $awal1),
            base64_decode('ZW5kMQ==') =>  date(base64_decode('WS1tLWQ='), $akhir1),
            base64_decode('c3RhcnQy') =>  date(base64_decode('WS1tLWQ='), $awal2),
            base64_decode('ZW5kMg==') =>  date(base64_decode('WS1tLWQ='), $akhir2),
            base64_decode('c3RhcnQz') =>  date(base64_decode('WS1tLWQ='), $awal3),
            base64_decode('ZW5kMw==') =>  date(base64_decode('WS1tLWQ='), $akhir3),

        ]);
    }
}
