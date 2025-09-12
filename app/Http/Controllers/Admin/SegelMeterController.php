<?php

namespace App\Http\Controllers\Admin;

use App\AreaStaff;
use App\CtmPbk;
use App\CtmPelanggan;
use App\CtmPembayaran;
use App\CtmWilayah;
use App\Customer;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Lock;
use App\Staff;
use App\Traits\TraitModel;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OneSignal;
use Yajra\DataTables\Facades\DataTables;

class SegelMeterController extends Controller
{

    use TraitModel;

    public function deligate()
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));

        if ($date_now > $date_comp) {
            $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.idareal,tblwilayah.group_unit,tblpembayaran.bulanrekening, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->having('jumlahtunggakan', 2)
                ->groupBy('tblpembayaran.nomorrekening');
        } else {
            $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.idareal,tblwilayah.group_unit,tblpembayaran.bulanrekening, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->having('jumlahtunggakan', 2)
                ->groupBy('tblpembayaran.nomorrekening');
        }
        $staff_arr = array();
        foreach ($qry as $index => $qry_row) {
            //get sub departement
            if ($qry_row->group_unit == 2) {
                $subdapertement_id = 13;
            } else if ($qry_row->group_unit == 3) {
                $subdapertement_id = 20;
            } else if ($qry_row->group_unit == 4) {
                $subdapertement_id = 18;
            } else if ($qry_row->group_unit == 5) {
                $subdapertement_id = 16;
            } else {
                $subdapertement_id = 10;
            }
            //get staff
            $staff_id = 0;
            $area_staff = AreaStaff::where('area_id', $qry_row->idareal)->first();
            if ($area_staff != null) {
                $staff_id = $area_staff->staff_id;
            }
            //get scb
            $arr['subdapertement_id'] = $subdapertement_id;
            $arr['month'] = date("m");
            $arr['year'] = date("Y");
            $arr['idareal'] = $qry_row->idareal;
            $last_scb = $this->get_last_code('scb-lock', $arr);
            $scb = acc_code_generate($last_scb, 24, 16, 'Y');
            if (Lock::where('customer_id', $qry_row->nomorrekening)->first() === null || Lock::where('customer_id', $qry_row->nomorrekening)->where('status', '!=', 'close')->first() === null) {
                // echo $scb."-".$qry_row->nomorrekening."</br>";
                $lock = Lock::create(['code' => $scb, 'customer_id' => $qry_row->nomorrekening, 'subdapertement_id' => $subdapertement_id, 'description' => '']);
                if ($staff_id > 0) {
                    $lock->staff()->attach($staff_id);
                    array_push($staff_arr, $staff_id);
                }
            }
        }
        //loop for notif
        $staff_arr = array_unique($staff_arr);
        // return $staff_arr;
        //*
        foreach ($staff_arr as $key => $staff_id) {
            //staff
            // echo $staff_id;
            $staff_row = User::where('staff_id', $staff_id)->first();
            if ($staff_row != null && !(empty($staff_row))) {
                // print_r($staff_row);
                //send notif to staff
                $admin_arr = User::where('staff_id', $staff_id)->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Staff: Perintah Penyegelan Baru Diteruskan : ';
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
                //send notif to admin
                $admin_arr = User::where('dapertement_id', 0)->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Admin: Perintah Penyegelan Baru Diteruskan : ';
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
                //send notif to departement terkait
                $admin_arr = User::where('dapertement_id', $staff_row->dapertement_id)
                    ->where('subdapertement_id', 0)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Bagian: Perintah Penyegelan Baru Diteruskan : ';
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
                //send notif to sub departement terkait
                $admin_arr = User::where('subdapertement_id', $staff_row->subdapertement_id)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Sub Bagian: Perintah Penyegelan Baru Diteruskan : ';
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
            }
        } //*/
        return back()->withErrors(['Teruskan Serentak Telah Selesai Diproses.']);
    }

    public function deligateBAK(Request $request)
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));
        //56530,5632,10011

        // $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.namapelanggan,tblpembayaran.tahunrekening,tblpembayaran.bulanrekening')
        //     ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
        //     ->where('tblpelanggan.nomorrekening', 56530)
        //     ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")') , '<' , date('2021-9-01'))
        //     ->get();

        if ($date_now > $date_comp) {
            $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.namapelanggan,tblwilayah.group_unit,tblpembayaran.bulanrekening, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->having('jumlahtunggakan', 2)
                ->groupBy('tblpembayaran.nomorrekening')
                ->skip(0)->take(10)->get();
        } else {
            $qry = CtmPelanggan::selectRaw('tblpelanggan.nomorrekening,tblpelanggan.namapelanggan,tblwilayah.group_unit,tblpembayaran.bulanrekening, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->having('jumlahtunggakan', 2)
                ->groupBy('tblpembayaran.nomorrekening')
                ->skip(0)->take(10)->get();
        }
        $data_insert = array();
        for ($i = 0; $i < count($qry); $i++) {
            if ($qry[$i]->group_unit == 2) {
                $subdapertement_id = 13;
            } else if ($qry[$i]->group_unit == 3) {
                $subdapertement_id = 20;
            } else if ($qry[$i]->group_unit == 4) {
                $subdapertement_id = 18;
            } else if ($qry[$i]->group_unit == 5) {
                $subdapertement_id = 16;
            } else {
                $subdapertement_id = 10;
            }
            $arr['subdapertement_id'] = $subdapertement_id;
            $arr['month'] = date("m");
            $arr['year'] = date("Y");
            $last_scb = $this->get_last_code('scb-lock', $arr);
            $scb = acc_code_generate($last_scb, 16, 12, 'Y');
            $data_insert[$i] = ['code' => $scb, 'customer_id' => $qry[$i]->nomorrekening, 'subdapertement_id' => $subdapertement_id, 'description' => ''];
        }
        return $this->deligateStore($data_insert);
        // $lock = Lock::insert($data_insert);
        // return count($qry);
        // foreach ($qry as $key => $qry_row) {
        //     echo $qry_row->nomorrekening."</br>";
        // }
        // foreach ($qry as $index => $qry_row) {
        //     if($qry_row->group_unit==2){
        //         $subdapertement_id=13;
        //     }else if($qry_row->group_unit==3){
        //         $subdapertement_id=20;
        //     }else if($qry_row->group_unit==4){
        //         $subdapertement_id=18;
        //     }else if($qry_row->group_unit==5){
        //         $subdapertement_id=16;
        //     }else{
        //         $subdapertement_id=10;
        //     }
        //     // $arr['subdapertement_id'] = $subdapertement_id;
        //     // $arr['month'] = date("m");
        //     // $arr['year'] = date("Y");
        //     // $last_scb = $this->get_last_code('scb-lock', $arr);
        //     // $scb = acc_code_generate($last_scb, 16, 12, 'Y');
        //     if (Lock::where('customer_id', $qry_row->nomorrekening)->first() === null) {
        //     // echo $scb."-".$qry_row->nomorrekening."</br>";
        //     $lock = Lock::create(['code'=>$index,'customer_id'=>$qry_row->nomorrekening,'subdapertement_id'=>$subdapertement_id,'description'=>'']);
        //     }
        // }
    }

    public function index(Request $request)
    {
        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));
        // dd(Auth::user());

        // new start
        $staff = Staff::where('id', $request->staff)->first();
        if ($staff) {
            $staffPbk = CtmPbk::where('Name', $staff->pbk)->get();
        } else {
            $staffPbk = [];
        }
        if ($request->staff && count($staffPbk) > 0) {

            if (Auth::user()->dapertement_id != 0 && Auth::user()->subdapertement_id != 0 && Auth::user()->staff_id != 0) {
                $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name ')
                    ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
                    ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                    ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
                    ->groupBy('staffs.id');
                $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
            } else if (Auth::user()->dapertement_id != 0 || Auth::user()->subdapertement_id != 0 || Auth::user()->staff_id != 0) {

                if (Auth::user()->staff_id === 0) {
                    $group_unit = Dapertement::select('dapertements.group_unit')
                        ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
                    $data = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
                    // dd($data[0]->code);
                } else {
                    $data = AreaStaff::join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'area_id')->selectRaw('area_id as code,NamaWilayah')->where('group_unit', $group_unit)->get();
                }

                $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name ')
                    ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
                    ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                    ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
                    ->groupBy('staffs.id');

                $qrystf->where(function ($query) use ($data) {
                    //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                    for ($i = 0; $i < count($data); $i++) {
                        if ($i == 0) {
                            $query->where('area_id', $data[$i]->code);
                        } else {
                            $query->orWhere('area_id', $data[$i]->code);
                        }
                    }
                });

                $qrystf->where(function ($query) {
                    $query->where('subdapertement_id', 10)->orWhere('dapertements.group_unit', '>', 1);
                });

                $areas = $data;
                $qrystf = $qrystf;
            } else {
                $data = AreaStaff::join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'area_id')->selectRaw('area_id as code,NamaWilayah')->where('staff_id', Auth::user()->staff_id)->get();
                $areas = $data;
                $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name ')
                    ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
                    ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                    ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
                    ->groupBy('staffs.id');

                $qrystf->where(function ($query) use ($data) {
                    //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                    for ($i = 0; $i < count($data); $i++) {
                        if ($i == 0) {
                            $query->where('area_id', $data[$i]->code);
                        } else {
                            $query->orWhere('area_id', $data[$i]->code);
                        }
                    }
                });

                $qrystf->where(function ($query) {
                    $query->where('subdapertement_id', 10)->orWhere('dapertements.group_unit', '>', 1);
                });

                $qrystf = $qrystf;
            }
            // $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name ')
            //     ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
            //     ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
            //     ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
            //     ->groupBy('staffs.id');
            // $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
            if ($date_now < $date_comp) {
                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->join('tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                    ->where('tblopp.operator', $staff->pbk)
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->having('jumlahtunggakan', '>', 1)
                    ->FilterStatusNew($request->status)
                    ->groupBy('tblpembayaran.nomorrekening');
            } else {
                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->join('tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                    ->where('tblopp.operator', $staff->pbk)
                    ->where('tblpelanggan.status', 1)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                    ->having('jumlahtunggakan', '>', 1)
                    ->FilterStatusNewNew($request->status)
                    ->groupBy('tblpembayaran.nomorrekening');
            }
        }

        // new end

        else {

            if (Auth::user()->dapertement_id != 0 || Auth::user()->subdapertement_id != 0 || Auth::user()->staff_id != 0) {

                if (Auth::user()->staff_id === 0) {
                    $group_unit = Dapertement::select('dapertements.group_unit')
                        ->where('dapertements.id', Auth::user()->dapertement_id)->first()->group_unit;
                    $data = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
                    // dd($data[0]->code);
                } else {
                    $data = AreaStaff::join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'area_id')->selectRaw('area_id as code,NamaWilayah')->where('staff_id', Auth::user()->staff_id)->get();
                }
                $areas = $data;
                // dd($data);
                $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name ')
                    ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
                    ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                    ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
                    ->groupBy('staffs.id');

                $qrystf->where(function ($query) use ($data) {
                    //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                    for ($i = 0; $i < count($data); $i++) {
                        if ($i == 0) {
                            $query->where('area_id', $data[$i]->code);
                        } else {
                            $query->orWhere('area_id', $data[$i]->code);
                        }
                    }
                });

                $qrystf->where(function ($query) {
                    $query->where('subdapertement_id', 10)->orWhere('dapertements.group_unit', '>', 1);
                });

                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening');

                // isi pertama
                if ($date_now > $date_comp) {
                    if ($request->staff != '') {
                        // $data = AreaStaff::select('area_id')->where('staff_id', $request->staff)->get();
                        if (count($data) > 0) {
                            $qry->where('tblpelanggan.status', 1)
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                ->having('jumlahtunggakan', '>', 1)
                                ->FilterStatusNew(request()->input('status'));

                            $qry->where(function ($query) use ($data) {
                                //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($i == 0) {
                                        $query->where('tblpelanggan.idareal', $data[$i]->code);
                                    } else {
                                        $query->orWhere('tblpelanggan.idareal', $data[$i]->code);
                                    }
                                }
                            });
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry->FilterWilayah(request()->input('area'))->groupBy('tblpembayaran.nomorrekening');
                    } else {
                        if (count($data) > 0) {

                            $qry->where('tblpelanggan.status', 1)
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                ->having('jumlahtunggakan', '>', 1)
                                ->FilterStatusNew(request()->input('status'));

                            $qry->where(function ($query) use ($data) {
                                //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($i == 0) {
                                        $query->where('tblpelanggan.idareal', $data[$i]->code);
                                    } else {
                                        $query->orWhere('tblpelanggan.idareal', $data[$i]->code);
                                    }
                                }
                            });
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry->FilterWilayah(request()->input('area'))->groupBy('tblpembayaran.nomorrekening');
                    }
                } else {

                    if ($request->staff != '') {
                        $data = AreaStaff::select('area_id')->where('staff_id', $request->staff)->get();
                        if (count($data) > 0) {
                            $qry->where('tblpelanggan.status', 1)
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                ->having('jumlahtunggakan', '>', 1)
                                ->FilterStatusNew(request()->input('status'));

                            $qry->where(function ($query) use ($data) {
                                //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($i == 0) {
                                        $query->where('tblpelanggan.idareal', $data[$i]->area_id);
                                    } else {
                                        $query->orWhere('tblpelanggan.idareal', $data[$i]->area_id);
                                    }
                                }
                            });
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry->FilterWilayah(request()->input('area'))->groupBy('tblpembayaran.nomorrekening');
                    } else {
                        if (count($data) > 0) {

                            $qry->where('tblpelanggan.status', 1)
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                ->having('jumlahtunggakan', '>', 1)
                                ->FilterStatusNew(request()->input('status'));

                            $qry->where(function ($query) use ($data) {
                                //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($i == 0) {
                                        $query->where('tblpelanggan.idareal', $data[$i]->code);
                                    } else {
                                        $query->orWhere('tblpelanggan.idareal', $data[$i]->code);
                                    }
                                }
                            });
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry->FilterWilayah(request()->input('area'))->groupBy('tblpembayaran.nomorrekening');
                    }
                }
            }
            // digunakan untuk admin
            else if (Auth::user()->name == 'ADMIN') {

                $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
                $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name ')
                    ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
                    ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                    ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
                    ->where('subdapertement_id', 10)
                    ->orWhere('dapertements.group_unit', '>', 1)
                    ->groupBy('staffs.id');
                // dd($qrystf->get());
                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening');
                if ($date_now > $date_comp) {
                    if ($request->staff != '') {
                        $data = AreaStaff::select('area_id')->where('staff_id', $request->staff)->get();
                        if (count($data) > 0) {

                            $qry->where('tblpelanggan.idareal', $data[$i]->area_id)
                                ->where('tblpelanggan.status', 1)
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                ->having('jumlahtunggakan', '>', 1)
                                ->FilterStatusNew(request()->input('status'));

                            $qry->where(function ($query) use ($data) {
                                //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($i == 0) {
                                        $query->where('tblpelanggan.idareal', $data[$i]->area_id);
                                    } else {
                                        $query->orWhere('tblpelanggan.idareal', $data[$i]->area_id);
                                    }
                                }
                            });
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry->FilterWilayah(request()->input('area'))->groupBy('tblpembayaran.nomorrekening');
                    } else {
                        $qry->having('jumlahtunggakan', '>', 1)
                            ->where('tblpelanggan.status', 1)
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                            ->FilterWilayah(request()->input('area'))
                            ->groupBy('tblpembayaran.nomorrekening')
                            ->FilterStatusNew(request()->input('status'));
                    }
                } else {

                    if ($request->staff != '') {
                        $data = AreaStaff::select('area_id')->where('staff_id', $request->staff)->get();
                        if (count($data) > 0) {

                            $qry->where('tblpelanggan.status', 1)
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                ->having('jumlahtunggakan', '>', 1)
                                ->FilterStatusNew(request()->input('status'));

                            $qry->where(function ($query) use ($data) {
                                //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($i == 0) {
                                        $query->where('tblpelanggan.idareal', $data[$i]->area_id);
                                    } else {
                                        $query->orWhere('tblpelanggan.idareal', $data[$i]->area_id);
                                    }
                                }
                            });
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry->FilterWilayah(request()->input('area'))->groupBy('tblpembayaran.nomorrekening');
                    } else {
                        $qry->having('jumlahtunggakan', '>', 1)
                            ->where('tblpelanggan.status', 1)
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                            ->FilterWilayah(request()->input('area'))
                            ->groupBy('tblpembayaran.nomorrekening')
                            ->FilterStatusNew(request()->input('status'));
                    }
                }
            } else {

                $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
                $qrystf = Staff::selectRaw('staffs.*, subdapertements.name as subdapertements_name, area_staff.area_id, dapertements.name as dapertements_name ')
                    ->join('dapertements', 'staffs.dapertement_id', '=', 'dapertements.id')
                    ->join('subdapertements', 'subdapertements.id', '=', 'staffs.subdapertement_id')
                    ->join('area_staff', 'staffs.id', '=', 'area_staff.staff_id')
                    ->where('subdapertement_id', 10)
                    ->orWhere('dapertements.group_unit', '>', 1)
                    ->groupBy('staffs.id');
                // dd($qrystf->get());
                $qry = Customer::selectRaw('tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak')
                    ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening');
                if ($date_now > $date_comp) {
                    if ($request->staff != '') {
                        $data = AreaStaff::select('area_id')->where('staff_id', $request->staff)->get();
                        if (count($data) > 0) {

                            $qry->where('tblpelanggan.status', 1)
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                ->having('jumlahtunggakan', '>', 1)
                                ->FilterStatusNew(request()->input('status'));

                            $qry->where(function ($query) use ($data) {
                                //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($i == 0) {
                                        $query->where('tblpelanggan.idareal', $data[$i]->area_id);
                                    } else {
                                        $query->orWhere('tblpelanggan.idareal', $data[$i]->area_id);
                                    }
                                }
                            });
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry->FilterWilayah(request()->input('area'))->groupBy('tblpembayaran.nomorrekening');
                    } else {
                        $qry->having('jumlahtunggakan', '>', 1)
                            ->where('tblpelanggan.status', 1)
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                            ->FilterWilayah(request()->input('area'))
                            ->groupBy('tblpembayaran.nomorrekening')
                            ->FilterStatusNew(request()->input('status'));
                    }
                } else {

                    if ($request->staff != '') {
                        $data = AreaStaff::select('area_id')->where('staff_id', $request->staff)->get();
                        if (count($data) > 0) {

                            $qry->where('tblpelanggan.status', 1)
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                                ->having('jumlahtunggakan', '>', 1)
                                ->FilterStatusNew(request()->input('status'));

                            $qry->where(function ($query) use ($data) {
                                //$query->where('tblpelanggan.idareal', $data[0]->area_id);
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($i == 0) {
                                        $query->where('tblpelanggan.idareal', $data[$i]->area_id);
                                    } else {
                                        $query->orWhere('tblpelanggan.idareal', $data[$i]->area_id);
                                    }
                                }
                            });
                        } else {
                            $qry->where('tblpelanggan.nomorrekening', null);
                        }

                        $qry->FilterWilayah(request()->input('area'))->groupBy('tblpembayaran.nomorrekening');
                    } else {
                        $qry->having('jumlahtunggakan', '>', 1)
                            ->where('tblpelanggan.status', 1)
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                            ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                            ->FilterWilayah(request()->input('area'))
                            ->groupBy('tblpembayaran.nomorrekening')
                            ->FilterStatusNew(request()->input('status'));
                    }
                }
            }
        }
        // dd($data);

        // dd($qry->get());

        
        // return $qry->limit(10)->get();       
        // return $qry->toSql();
        // return dd($qry);

        if ($request->ajax()) {
            //set query
            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'lock_show';
                $editGate = '';
                $deleteGate = '';
                $crudRoutePart = 'segelmeter';
                // $lockGate = $row->statusnunggak;
                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    // 'lockGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('nomorrekening', function ($row) {
                return $row->nomorrekening ? $row->nomorrekening : "";
            });

            $table->editColumn('namapelanggan', function ($row) {
                return $row->namapelanggan ? $row->namapelanggan : "";
            });

            $table->editColumn('alamat', function ($row) {
                return $row->alamat ? $row->alamat : "";
            });
            $table->editColumn('idareal', function ($row) {
                return $row->idareal ? $row->idareal : "";
            });

            $table->editColumn('jumlahtunggakan', function ($row) {
                return $row->jumlahtunggakan ? $row->jumlahtunggakan : 0;
            });

            $table->editColumn('statusnunggak', function ($row) {
                if ($row->jumlahtunggakan == 0) {
                    return '<span class="badge bg-success">Lunas</span>';
                } else if ($row->jumlahtunggakan == 1) {
                    return '<span class="badge bg-warning">Awas</span>';
                } else {
                    return '<span class="badge bg-danger">Tunggak</span>';
                }
            });

            $table->rawColumns(['actions', 'placeholder', 'statusnunggak']);

            $table->addIndexColumn();
            return $table->make(true);
        }

        $staff = $qrystf->get();
        // dd($staff);
        return view('admin.segelmeter.index', compact('staff', 'areas'));
    }

    public function show($id)
    {
        $customer = Customer::where('nomorrekening', $id)
            ->first();
        $customer->year = date('Y');
        // dd($ctm);

        // ctm pay
        $date_now = date("Y-m-d");
        $date_comp = date("Y-m") . "-20";
        $month_next = date('n', strtotime('+1 month'));
        $month_now = ($month_next - 1);
        $tunggakan = 0;
        $tagihan = 0;
        $denda = 0;
        $total = 0;
        $ctm_lock = 0;
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));
        if ($date_now > $date_comp) {
            $ctm_lock_old = 0;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        } else {
            $ctm_lock_old = 1;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '>=', $last_4_month)
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }

        foreach ($ctm as $key => $item) {
            $m3 = $item->bulanini - $item->bulanlalu;
            $sisa = $item->wajibdibayar - $item->sudahdibayar;
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

            $dataPembayaran[$key] = [
                // 'no' => $key +1,
                'norekening' => $item->nomorrekening,
                'periode' => $periode,
                'tanggal' => $item->tglbayarterakhir,
                'm3' => $m3,
                'wajibdibayar' => $item->wajibdibayar,
                'sudahbayar' => $item->sudahdibayar,
                'denda' => $item->denda,
                'sisa' => $sisa,
            ];
        }

        if ($tunggakan > 0 && $tunggakan < 2) {
            $denda = 10000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 1 && $tunggakan < 4) {
            $denda = 50000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 3) {
            $denda = 'SSB (Sanksi Denda Setara Sambungan Baru)';
            $total = $tagihan;
        }

        $recap = [
            'tagihan' => $tagihan,
            'denda' => $denda,
            'total' => $total,
            'tunggakan' => $tunggakan,
        ];

        return view('admin.segelmeter.show', compact('customer', 'dataPembayaran', 'recap'));
    }

    public function sppPrint($id)
    {

        $customer = Customer::where('nomorrekening', $id)
            ->first();
        $customer->year = date('Y');
        // dd($ctm);

        // ctm pay
        $date_now = date("Y-m-d");
        $date_comp = date("Y-m") . "-20";
        $month_next = date('n', strtotime('+1 month'));
        $month_now = ($month_next - 1);
        $tunggakan = 0;
        $tagihan = 0;
        $denda = 0;
        $total = 0;
        $ctm_lock = 0;
        if ($date_now > $date_comp) {
            $ctm_lock_old = 0;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.statuslunas', '=', 0)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        } else {
            $ctm_lock_old = 1;
            $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                ->where('tblpembayaran.nomorrekening', $id)
                ->where('tblpembayaran.statuslunas', '=', 0)
                ->where('tblpelanggan.status', 1)
                ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                ->get();
        }

        foreach ($ctm as $key => $item) {
            $m3 = $item->bulanini - $item->bulanlalu;
            $sisa = $item->wajibdibayar - $item->sudahdibayar;
            $tagihan = $tagihan + $sisa;

            if ($month_now == $item->bulanrekening && $ctm_lock_old == 1) {
                $ctm_lock = 1;
            }

            if ($sisa > 0 && $ctm_lock == 0) {
                $tunggakan = $tunggakan + 1;
            }

            $dataPembayaran[$key] = [
                // 'no' => $key +1,
                'norekening' => $item->nomorrekening,
                'periode' => $item->tahunrekening . '-' . $item->bulanrekening,
                'tanggal' => $item->tglbayarterakhir,
                'm3' => $m3,
                'wajibdibayar' => $item->wajibdibayar,
                'sudahbayar' => $item->sudahdibayar,
                'denda' => $item->denda,
                'sisa' => $sisa,
            ];
        }

        if ($tunggakan > 0 && $tunggakan < 2) {
            $denda = 10000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 1 && $tunggakan < 4) {
            $denda = 50000;
            $total = $tagihan + $denda;
            $denda = $denda;
        }
        if ($tunggakan > 3) {
            $denda = 'SSB (Sanksi Denda Setara Sambungan Baru)';
            $total = $tagihan;
        }

        $recap = [
            'tagihan' => $tagihan,
            'denda' => $denda,
            'total' => $total,
            'tunggakan' => $tunggakan,
        ];

        return view('admin.segelmeter.spp', compact('customer', 'dataPembayaran', 'recap'));
    }
}
