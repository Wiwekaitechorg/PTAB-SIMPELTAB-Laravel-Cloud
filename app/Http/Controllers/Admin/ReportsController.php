<?php
namespace App\Http\Controllers\Admin;

use App\actionWms;
use App\Complain;
use App\CtmPelanggan;
use App\CtmWilayah;
use App\Dapertement;
use App\Director;
use App\Http\Controllers\Controller;
use App\proposalWms;
use App\Subdapertement;
use App\Ticket;
use App\Traits\TraitModel;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    use TraitModel;

    public function reportComplain()
    {

        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $departementlist = Dapertement::all();
        $areas           = CtmWilayah::select('id as code', 'NamaWilayah')->get();
        $month           = $monthList[date('n')];
        // return view ('admin.reports.reportSubDistribusi');
        return view('admin.reports.complain', compact('departementlist', 'month', 'areas'));
    }

    public function reportComplainProses(Request $request)
    {
        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $month     = $monthList[date('n')];
        $complains = Complain::whereRaw("DATE(created_at) >= '" . $request->from . "' AND DATE(created_at) <= '" . $request->to . "'")->FilterArea($request->areas)->FilterStatus($request->status)->with(['ticket', 'areas', 'category', 'dapertement'])->get();
        $director  = Director::selectRaw('directors.name, directors.director_name,dapertements.name as dapertement_name')->join('dapertements', 'dapertements.director_id', '=', 'directors.id')
            ->where('dapertements.id', 1)->first();
        $menyetujui          = $director ? $director->name : "";
        $director_name       = $director ? $director->director_name : "";
        $mengetahui          = $director ? $director->dapertement_name : "";
        $subdapertement_name = Subdapertement::where('id', 8)->first();
        $dibuat              = $subdapertement_name ? $subdapertement_name->name : "";
        // dd($request->all());
        $month = $monthList[date('n')];

        return view('admin.reports.reportComplain', compact('director_name', 'complains', 'request', 'month', 'menyetujui', 'mengetahui', 'dibuat'));
    }

    public function reportSubHumas()
    {
        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $departementlist = Dapertement::all();
        $month           = $monthList[date('n')];
        return view('admin.reports.subHumas', compact('departementlist', 'month'));
        // return view ('admin.reports.reportSubHumas', compact('tickets'));
    }

    public function reportSubHumasProses(Request $request)
    {
        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        if ($request->subdapertement_id) {
            //$tickets = Ticket::selectRaw('tickets.*')->join('actions', 'tickets.id', '=', 'actions.ticket_id')->where('actions.subdapertement_id', $request->subdapertement_id)->whereBetween(DB::raw('DATE(tickets.created_at)'), [$request->from, $request->to])
            $tickets = Ticket::selectRaw('tickets.*')->join('actions', 'tickets.id', '=', 'actions.ticket_id')->where('actions.subdapertement_id', $request->subdapertement_id)->whereRaw("DATE(tickets.created_at) >= '" . $request->from . "' AND DATE(tickets.created_at) <= '" . $request->to . "'")
                ->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])
                ->get();
        } else if ($request->dapertement_id === 22 || $request->dapertement_id === 20 || $request->dapertement_id === 21 || $request->dapertement_id === 23) {
            //$tickets = Ticket::where('dapertement_id', $dapertement_id)->whereBetween(DB::raw('DATE(created_at)'), [$request->from, $request->to])->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();

            // $tickets = Ticket::where('dapertement_id', $dapertement_id)->whereRaw("DATE(created_at) >= '".$request->from."' AND DATE(created_at) <= '".$request->to."'")->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();

            $tickets = Ticket::selectRaw('tickets.*')->join('actions', 'tickets.id', '=', 'actions.ticket_id')->whereRaw("DATE(tickets.created_at) >= '" . $request->from . "' AND DATE(tickets.created_at) <= '" . $request->to . "'")
                ->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])
                ->get();
        } else {
            //$tickets = Ticket::whereBetween(DB::raw('DATE(created_at)'), [$request->from, $request->to])->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();

            // $tickets = Ticket::whereRaw("DATE(created_at) >= '".$request->from."' AND DATE(created_at) <= '".$request->to."'")->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();

            $tickets = Ticket::selectRaw('tickets.*')->join('actions', 'tickets.id', '=', 'actions.ticket_id')->whereRaw("DATE(tickets.created_at) >= '" . $request->from . "' AND DATE(tickets.created_at) <= '" . $request->to . "'")
                ->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])
                ->get();
        }
        // dd($tickets[0]->category->categorytype);
        $director = Director::selectRaw('directors.name,directors.director_name,dapertements.name as dapertement_name')->join('dapertements', 'dapertements.director_id', '=', 'directors.id')
            ->where('dapertements.id', $request->dapertement_id)->first();
        $menyetujui          = $director ? $director->name : "";
        $director_name       = $director ? $director->director_name : "";
        $mengetahui          = $director ? $director->dapertement_name : "";
        $subdapertement_name = Subdapertement::where('id', $request->subdapertement_id)->first();
        $dibuat              = $subdapertement_name ? $subdapertement_name->name : "";
        // dd($request->all());
        $month = $monthList[date('n')];
        //return $tickets;
        return view('admin.reports.reportSubHumas', compact('director_name', 'tickets', 'request', 'month', 'menyetujui', 'mengetahui', 'dibuat'));
    }

    public function reportSubDistribusi()
    {

        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $departementlist = Dapertement::all();
        $month           = $monthList[date('n')];
        // return view ('admin.reports.reportSubDistribusi');
        return view('admin.reports.subDistribusi', compact('departementlist', 'month'));
    }

    public function reportSubDistribusiProses(Request $request)
    {
        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $month = $monthList[date('n')];
        if ($request->subdapertement_id) {
            //$tickets = Ticket::selectRaw('tickets.*')->join('actions', 'tickets.id', '=', 'actions.ticket_id')->where('actions.subdapertement_id', $request->subdapertement_id)->whereBetween(DB::raw('DATE(tickets.created_at)'), [$request->from, $request->to])
            $tickets = Ticket::selectRaw('tickets.*')->join('actions', 'tickets.id', '=', 'actions.ticket_id')->where('actions.subdapertement_id', $request->subdapertement_id)->whereRaw("DATE(tickets.created_at) >= '" . $request->from . "' AND DATE(tickets.created_at) <= '" . $request->to . "'")
                ->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])
                ->get();
        } else {
            //$tickets = Ticket::whereBetween(DB::raw('DATE(created_at)'), [$request->from, $request->to])->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();
            $tickets = Ticket::whereRaw("DATE(created_at) >= '" . $request->from . "' AND DATE(created_at) <= '" . $request->to . "'")->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();
        }
        $director = Director::selectRaw('directors.name, directors.director_name,dapertements.name as dapertement_name')->join('dapertements', 'dapertements.director_id', '=', 'directors.id')
            ->where('dapertements.id', $request->dapertement_id)->first();
        $menyetujui          = $director ? $director->name : "";
        $director_name       = $director ? $director->director_name : "";
        $mengetahui          = $director ? $director->dapertement_name : "";
        $subdapertement_name = Subdapertement::where('id', $request->subdapertement_id)->first();
        $dibuat              = $subdapertement_name ? $subdapertement_name->name : "";
        // dd($request->all());
        $month = $monthList[date('n')];

        return view('admin.reports.reportSubDistribusi', compact('director_name', 'tickets', 'request', 'month', 'menyetujui', 'mengetahui', 'dibuat'));
    }

    public function reportLockAction()
    {

        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));

        $date_now   = date("Y-m-d");
        $date_comp  = date("Y-m") . "-20";
        $month_now  = date('n');
        $year_now   = date('Y');
        $month_next = date('n', strtotime('+1 month')) - 1;
        $per_page   = 100000;

        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        if ($date_now > $date_comp) {
            $customer = CtmPelanggan::selectRaw('tblpelanggan.namapelanggan, tblpelanggan.nomorrekening, tblwilayah.id as wilayah_id, tblpelanggan.alamat,tblpelanggan.idgol , tblpelanggan.nomorrekening ,(((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->groupBy('tblpembayaran.nomorrekening')
                ->having('jumlahtunggakan', '>', 1)
                ->orderBy('tblpelanggan.idareal', 'ASC')
                ->orderBy('tblpelanggan.nomorrekening', 'ASC')
                ->get();
        } else {
            $customer = CtmPelanggan::selectRaw('tblpelanggan.namapelanggan, tblpelanggan.nomorrekening, tblwilayah.id as wilayah_id, tblpelanggan.alamat,tblpelanggan.idgol , tblpelanggan.nomorrekening ,(((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->groupBy('tblpembayaran.nomorrekening')
                ->having('jumlahtunggakan', '>', 1)
                ->orderBy('tblpelanggan.idareal', 'ASC')
                ->orderBy('tblpelanggan.nomorrekening', 'ASC')
                ->get();
        }
        $take = $customer->count();
        if ($take % $per_page === 0) {
            $jum = floor($take / $per_page);
        } else {
            $jum = floor($take / $per_page) + 1;
        }

        // $departementlist = Dapertement::all();
        $month = $monthList[date('n')];
        return view('admin.reports.lockAction', compact('month', 'jum', 'take'));
        // return view ('admin.reports.reportSubHumas', compact('tickets'));
    }

    public function reportLockActionProses(Request $request)
    {
        // dd($request->jum);

        $per_page = 100000;

        // dd($jarak->d);
        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $monthRomawi = [
            0  => '',
            1  => 'I',
            2  => 'II',
            3  => 'III',
            4  => 'IV',
            5  => 'V',
            6  => 'VI',
            7  => 'VII',
            8  => 'VIII',
            9  => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        $month        = $monthList[date('n') - 1];
        $monthR       = $monthRomawi[date('n') - 1];
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));

        $date_now   = date("Y-m-d");
        $date_comp  = date("Y-m") . "-20";
        $month_now  = date('n');
        $year_now   = date('Y');
        $month_next = date('n', strtotime('+1 month')) - 1;

        $d1 = [];

        $subQuery = DB::table('simpletab.lock_action')

            ->select('lock_action.customer_id', 'lock_action.id', 'lock_action.type', 'created_at as action_date')
            ->whereRaw('lock_action.id in (select max(lock_action.id) from simpletab.lock_action group by (lock_action.customer_id))');

        // dd($subQuery);
        if ($date_now > $date_comp) {
            $customer = CtmPelanggan::selectRaw('tblpelanggan.namapelanggan,lock_action.id,lock_action.action_date, lock_action.type as lockActionType, tblpelanggan.nomorrekening, tblwilayah.id as wilayah_id, tblpelanggan.alamat,tblpelanggan.idgol , tblpelanggan.nomorrekening, staffs.name as staff_name ,(((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->leftJoin(DB::raw('(' . $subQuery->toSql() . ') lock_action'), 'tblpelanggan.nomorrekening', '=', 'lock_action.customer_id')
                ->leftJoin('simpletab.staffs', 'staffs.id', '=', 'lock_action.id')
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->groupBy('tblpembayaran.nomorrekening')
                ->having('jumlahtunggakan', '>', 1)
                ->orderBy('tblpelanggan.idareal', 'ASC')
                ->orderBy('tblpelanggan.nomorrekening', 'ASC')
                ->skip($request->jum * $per_page)
                ->take($per_page)
                ->get();
        } else {
            $customer = CtmPelanggan::selectRaw('tblpelanggan.namapelanggan,lock_action.id,lock_action.action_date, lock_action.type as lockActionType, tblpelanggan.nomorrekening, tblwilayah.id as wilayah_id, tblpelanggan.alamat,tblpelanggan.idgol , tblpelanggan.nomorrekening, staffs.name as staff_name ,(((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->leftJoin(DB::raw('(' . $subQuery->toSql() . ') lock_action'), 'tblpelanggan.nomorrekening', '=', 'lock_action.customer_id')
                ->leftJoin('simpletab.staffs', 'staffs.id', '=', 'lock_action.id')
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->groupBy('tblpembayaran.nomorrekening')
                ->having('jumlahtunggakan', '>', 1)
                ->orderBy('tblpelanggan.idareal', 'ASC')
                ->orderBy('tblpelanggan.nomorrekening', 'ASC')
                ->skip($request->jum * $per_page)
                ->take($per_page)
                ->get();
        }

        // dd($customer);
        if ($month_now > $month_next) {
            $month_next = $month_next + 12;
        }

        $month_now_new          = date('n');
        $tagihan                = 0;
        $status_paid_this_month = 0;
        foreach ($customer as $key => $item) {
            //get this month paid
            if ($item->bulanrekening == $month_now_new && $item->tahunrekening == $year_now) {
                if ($item->statuslunas == 2) {
                    $status_paid_this_month = 1;
                }
            }
            $m3      = $item->bulanini - $item->bulanlalu;
            $sisa    = $item->wajibdibayar - $item->sudahdibayar;
            $tagihan = $tagihan + $sisa;

            if ($month_now == $item->bulanrekening && $ctm_lock_old == 1) {
                $ctm_lock = 1;
            }

            if ($sisa > 0 && $ctm_lock == 0) {
                $tunggakan = $tunggakan + 1;
            }

            //if not paid
            if ($sisa > 0) {
                $item->tglbayarterakhir = "";
            }
            //set to prev
            $periode = date('Y-m', strtotime(date($item->tahunrekening . '-' . $item->bulanrekening . '-01') . " -1 month"));

            //get jarak
            $date2 = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d'))));
            if ($item->action_date != null) {
                $tgl2  = new DateTime($item->action_date);
                $tgl1  = new DateTime($date2);
                $jarak = $tgl2->diff($tgl1);
                $jarak = $jarak->days;
                // dd($jarak->days);
            } else {
                $jarak = "";
            }

            $d1[] = ['namapelanggan' => $item->namapelanggan, 'lockActionType' => $item->lockActionType, 'nomorrekening' => $item->nomorrekening, 'wilayah_id' => $item->wilayah_id, 'alamat' => $item->alamat, 'idgol' => $item->idgol, 'staff_name' => $item->staff_name, 'status_paid_this_month' => $status_paid_this_month, 'tglbayarterakhir' => $item->tglbayarterakhir, 'jarak' => $jarak, 'action_date' => $item->action_date];
        }
        $jum  = $request->jum;
        $take = $request->take - 1;
        return view('admin.reports.reportLockAction', compact('customer', 'request', 'month', 'monthR', 'd1', 'take', 'jum'));
    }

    public function reportPWM()
    {
        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $departementlist = Dapertement::all();
        $areas           = CtmWilayah::select('id as code', 'NamaWilayah');

        if (empty(Auth::user()->dapertement_id) || (Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 8 || Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 24)) {
            //if (Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 8 || Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 24) {
            $areas = $areas->get();
        } else {
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
            $areas->where('tblwilayah.group_unit', $group_unit);
            $areas = $areas->get();
        }

        $month = $monthList[date('n')];
        // return view ('admin.reports.reportSubDistribusi');
        return view('admin.reports.PWM', compact('departementlist', 'month', 'areas'));
    }

    public function reportPWMProcess(Request $request)
    {
        $unitName  = "";
        $time      = strtotime($request->monthyear);
        $monthR    = date("n", $time);
        $yearR     = date("Y", $time);
        $monthYear = $request->monthyear;
        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $monthRomawi = [
            0  => '',
            1  => 'I',
            2  => 'II',
            3  => 'III',
            4  => 'IV',
            5  => 'V',
            6  => 'VI',
            7  => 'VII',
            8  => 'VIII',
            9  => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        $month  = $monthList[$monthR];
        $monthR = $monthRomawi[$monthR];

        $proposalWm = proposalWms::selectRaw('tblopp.operator as operator,
        proposal_wms.code,
        proposal_wms.queue,
        proposal_wms.close_queue,
        proposal_wms.customer_id,
        proposal_wms.status,
        proposal_wms.status_wm,
        proposal_wms.priority,
        proposal_wms.created_at as diterima,
        proposal_wms.updated_at,
        tblpelanggan.namapelanggan,
        tblpelanggan.nomorrekening,
        tblpelanggan.alamat,
        tblpelanggan.telp,
        tblpelanggan.idareal,
        tblpelanggan.idgol
        ')
            ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
            ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
            ->join('simpletabctm.tblopp as tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
        // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('-1 month', strtotime($request->monthyear))), date('Y-m-20', strtotime('0 month', strtotime($request->monthyear)))])
            ->where('proposal_wms.created_at', 'like', date('Y-m', strtotime('0 month', strtotime($request->monthyear))) . '%')
        // ->where('proposal_wms.created_at', 'like', date('Y-m-1', strtotime('-1 month', strtotime($request->monthyear))) . '%')
            ->where('tblopp.status', '=', '1')
            ->FilterAreas($request->areas);
        // ->where('proposal_wms.status', 'close');

        if (empty(Auth::user()->dapertement_id) || (Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 8 || Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 24)) {
            //if (Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 8 || Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 24) {
            $dapertement     = "";
            $sub_dapertement = "";
            $proposalWm      = $proposalWm->get();
        } else {
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
            $dapertement = Dapertement::select('dapertements.name')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->name;
            if (Auth::user()->subdapertement_id != 0 && Auth::user()->subdapertement_id != null) {
                $sub_dapertement = Subdapertement::where('id', Auth::user()->subdapertement_id)->first()->name;
            } else {
                $sub_dapertement = "";
            }

            // $data = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
            // $areas = $data;
            // $proposalWm = proposalWms::selectRaw('tblpelanggan.idareal, proposal_wms.code, proposal_wms.customer_id, proposal_wms.status_wm, proposal_wms.priority, proposal_wms.year, proposal_wms.month, proposal_wms.id, proposal_wms.created_at, proposal_wms.updated_at, proposal_wms.status')
            //     ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening');
            if ($group_unit == "1") {
                $unitName = "Kota";
            } else if ($group_unit == "2") {
                $unitName = "Unit Kerambitan";
            } else if ($group_unit == "3") {
                $unitName = "Unit Selemadeg";
            } else if ($group_unit == "4") {
                $unitName = "Unit Penebel";
            } else if ($group_unit == "5") {
                $unitName = "Unit Baturiti";
            }

            $proposalWm->where('tblwilayah.group_unit', $group_unit);
            // $proposalWm = $proposalWm->get();
            $proposalWm = $proposalWm->orderBy('proposal_wms.code', 'ASC')->orderBy('close_queue', 'ASC')->get();

            // dd($proposalWm);
            // dd($proposalWm->get());
            // else {
            //     for ($i = 0; $i < count($data); $i++) {
            //         if ($i < 1) {

            //             $proposalWm->where('tblpelanggan.idareal', $data[$i]->code);

            //             // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
            //         } else {
            //             $proposalWm->orWhere('tblpelanggan.idareal', $data[$i]->code);
            //         }
            //     }
        }

        $director = Director::selectRaw('directors.name,directors.director_name,dapertements.name as dapertement_name')->join('dapertements', 'dapertements.director_id', '=', 'directors.id')
            ->where('dapertements.id', Auth::user()->dapertement_id)->first();
        $menyetujui    = $director ? $director->name : "";
        $director_name = $director ? $director->director_name : "";

        $d1 = $proposalWm;
        //return $proposalWm;
        return view('admin.reports.reportPWM', compact('director_name', 'menyetujui', 'sub_dapertement', 'dapertement', 'proposalWm', 'd1', 'monthR', 'request', 'month', 'monthYear', 'unitName'));
    }

    public function reportProposalWm()
    {

        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $departementlist = Dapertement::all();
        $areas           = CtmWilayah::select('id as code', 'NamaWilayah');
        //return Auth::user()->dapertement_id;
        if (empty(Auth::user()->dapertement_id) || (Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 8 || Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 24)) {
            $areas = $areas->get();
        } else {
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
            $areas->where('tblwilayah.group_unit', $group_unit);
            $areas = $areas->get();
        }

        $month = $monthList[date('n')];
        // return view ('admin.reports.reportSubDistribusi');
        return view('admin.reports.proposalWm', compact('departementlist', 'month', 'areas'));
    }

    public function reportProposalWmProses(Request $request)
    {
        $unitName  = "";
        $time      = strtotime($request->monthyear);
        $monthR    = date("n", $time);
        $yearR     = date("Y", $time);
        $monthYear = $request->monthyear;
        $monthList = [
            0  => '',
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $monthRomawi = [
            0  => '',
            1  => 'I',
            2  => 'II',
            3  => 'III',
            4  => 'IV',
            5  => 'V',
            6  => 'VI',
            7  => 'VII',
            8  => 'VIII',
            9  => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        $month  = $monthList[$monthR];
        $monthR = $monthRomawi[$monthR];

        $proposalWm = actionWms::selectRaw('action_wms.id, action_wms.code,
        action_wms.proposal_wm_id,
        action_wms.memo,
        action_wms.old_image,
        action_wms.new_image,
        action_wms.image_done,
        action_wms.noWM1,
        action_wms.updated_at as date,
        action_wms.brandWM1,
        action_wms.standWM1,
        action_wms.noWM2,
        action_wms.brandWM2,
        action_wms.standWM2,
        action_wms.subdapertement_id,
        proposal_wms.code,
        proposal_wms.queue,
        proposal_wms.close_queue,
        proposal_wms.customer_id,
        proposal_wms.status,
        proposal_wms.status_wm,
        proposal_wms.priority,
        proposal_wms.created_at as diterima,
        proposal_wms.updated_at,
        tblpelanggan.namapelanggan,
        tblpelanggan.nomorrekening,
        tblpelanggan.alamat,
        tblpelanggan.telp,
        tblpelanggan.idareal,
        tblpelanggan.idgol,
        subdapertements.name,
        action_wm_staff.created_at as dikeluarkan
        ')
            ->rightJoin('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')

            ->leftJoin('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
            ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
            ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
            ->leftJoin('action_wm_staff', 'action_wm_staff.action_wm_id', '=', 'action_wms.id')
        // ->where('proposal_wms.created_at', 'like', $request->monthyear . '%')
        //  ->whereBetween('proposal_wms.created_at', [date('Y-m-01', strtotime('0 month', strtotime($request->monthyear))), date('Y-m-20', strtotime('0 month', strtotime($request->monthyear)))])
        // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime($request->monthyear))), date('Y-m-20', strtotime('1 month', strtotime($request->monthyear)))])
        // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime($request->monthyear))), date('Y-m-20', strtotime('1 month', strtotime($request->monthyear)))])
        // ->whereMonth('proposal_wms.created_at', '=', date('m', strtotime('0 month', strtotime($request->monthyear))))
        // ->whereYear('proposal_wms.created_at', '=', date('Y', strtotime('0 month', strtotime($request->monthyear))))

        // ->where('action_wm_staff.created_at', 'like', date('Y-m', strtotime('0 month', strtotime($request->monthyear))) . '%')
            ->where('action_wms.created_at', 'like', date('Y-m', strtotime('0 month', strtotime($request->monthyear))) . '%')

            ->where('proposal_wms.status', 'close')
            ->FilterAreas($request->areas);

        // ->where('proposal_wms.status', 'close');

        if (empty(Auth::user()->dapertement_id) || (Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 8 || Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 24)) {
            $dapertement     = "";
            $sub_dapertement = "";
            $proposalWm      = $proposalWm->orderBy('proposal_wms.code', 'ASC')->orderBy('close_queue', 'ASC')->get();
        } else {
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
            $dapertement = Dapertement::select('dapertements.name')
                ->where('dapertements.id', Auth::user()->dapertement_id)->first()->name;
            if (Auth::user()->subdapertement_id != 0 && Auth::user()->subdapertement_id != null) {
                $sub_dapertement = Subdapertement::where('id', Auth::user()->subdapertement_id)->first()->name;
            } else {
                $sub_dapertement = "";
            }

            // $data = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
            // $areas = $data;
            // $proposalWm = proposalWms::selectRaw('tblpelanggan.idareal, proposal_wms.code, proposal_wms.customer_id, proposal_wms.status_wm, proposal_wms.priority, proposal_wms.year, proposal_wms.month, proposal_wms.id, proposal_wms.created_at, proposal_wms.updated_at, proposal_wms.status')
            //     ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening');
            if ($group_unit == "1") {
                $unitName = "Kota";
            } else if ($group_unit == "2") {
                $unitName = "Unit Kerambitan";
            } else if ($group_unit == "3") {
                $unitName = "Unit Selemadeg";
            } else if ($group_unit == "4") {
                $unitName = "Unit Penebel";
            } else if ($group_unit == "5") {
                $unitName = "Unit Baturiti";
            }

            $proposalWm->where('tblwilayah.group_unit', $group_unit);
            $proposalWm = $proposalWm->orderBy('proposal_wms.code', 'ASC')->orderBy('close_queue', 'ASC')->get();
            // dd($proposalWm);
            // dd($proposalWm->get());
            // else {
            //     for ($i = 0; $i < count($data); $i++) {
            //         if ($i < 1) {

            //             $proposalWm->where('tblpelanggan.idareal', $data[$i]->code);

            //             // $data2 = $data2 . ' where idareal = ' . $data[$i]->area_id;
            //         } else {
            //             $proposalWm->orWhere('tblpelanggan.idareal', $data[$i]->code);
            //         }
            //     }
        }

        $director = Director::selectRaw('directors.name,directors.director_name,dapertements.name as dapertement_name')->join('dapertements', 'dapertements.director_id', '=', 'directors.id')
            ->where('dapertements.id', Auth::user()->dapertement_id)->first();
        $menyetujui    = $director ? $director->name : "";
        $director_name = $director ? $director->director_name : "";

        $d1 = $proposalWm;
        return view('admin.reports.reportProposalWm', compact('director_name', 'menyetujui', 'sub_dapertement', 'dapertement', 'proposalWm', 'd1', 'monthR', 'request', 'month', 'monthYear', 'unitName'));
    }
}
