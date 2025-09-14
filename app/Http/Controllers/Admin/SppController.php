<?php

namespace App\Http\Controllers\Admin;

use App\CtmPembayaran;
use App\CtmWilayah;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Lock;
use App\Traits\TraitModel;
use DateTime;
use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class SppController extends Controller
{
    use TraitModel;

    public function index()
    {
        $lock_groups = array();
        //loop tblwilayah
        $wilayah = CtmWilayah::select('id as code')->orderBy('tblwilayah.group_unit', 'ASC')->orderBy('tblwilayah.id', 'ASC')->get();
        foreach ($wilayah as $i => $wilayah_row) {
            $lock_group_arr = array();
            $lock_group = Lock::selectRaw('locks.id as id')
                ->join('simpletabctm.tblpelanggan as tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'locks.customer_id')
                ->join('simpletabctm.tblopp as tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                ->where('locks.status', 'pending')
                ->where('tblpelanggan.idareal', $wilayah_row->code)
                ->orderBy('tblopp.operator', 'asc')
                ->orderBy('tblpelanggan.idurut', 'asc')
                ->get();
            foreach ($lock_group as $lock_group_row) {
                array_push($lock_group_arr, $lock_group_row->id);
            }
            $lock_groups[$i]['data'] = $lock_group_arr;
            $lock_groups[$i]['title'] = $wilayah_row->code;
        }
        // return $lock_groups;
        return view('admin.spp.index', compact('lock_groups'));

    }

    public function sppPrintAll(Request $request)
    {
        $lock_list = array();
        $return_test = '';
        foreach ($request->locks as $index => $lock_id) {

            $lock = Lock::with('staff')->findOrFail($lock_id);
            $id = $lock->customer_id;
            $code = $lock->code;
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
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                    ->get();
            } else {
                $ctm_lock_old = 1;
                $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                    ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpembayaran.nomorrekening', $id)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->where('tblpembayaran.statuslunas', '=', 0)
                    ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                    ->get();
            }

            $dataPembayaran = array();
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

            $lock_list[$index]['customer'] = $customer;
            $lock_list[$index]['dataPembayaran'] = $dataPembayaran;
            $lock_list[$index]['recap'] = $recap;
            $lock_list[$index]['lock'] = $lock;
        }
        $lock_list_json = json_encode($lock_list);
        // return $lock_list;
        $templateProcessor = new TemplateProcessor('template-word/word.docx');

        $code = '';
        $petugas = '';
        $namapelanggan = '';
        $alamat = '';
        $noSBG = '';
        $area = '';
        $total = '';
        $tanggal = '';
        // foreach ($lock_list as $lock_list_row) {
        //     $code = $lock_list_row['lock']->code ;
        //     $namapelanggan = $lock_list_row['customer']->namapelanggan;
        //     $alamat = $lock_list_row['customer']->alamat ;
        //     $noSBG = $lock_list_row['customer']->nomorrekening;
        //     $area = $lock_list_row['customer']->idareal;
        //     $total = is_int($lock_list_row['recap']['total']) ? Rupiah($lock_list_row['recap']['total']) : $lock_list_row['recap']['total'] ;
        //     $tanggal = date('d') .' '. Bulan(date('m')) .' '. date('Y');
        //     foreach ($lock_list_row['lock']->staff as $index => $staff_row){
        //         $petugas = $staff_row->name;
        //     }
        //     foreach ($lock_list_row['dataPembayaran'] as $key=>$item ){
        //        $periode = ','.Bulan( (new DateTime($item['periode']))->format('m')).'-'. (new DateTime($item['periode']))->format('Y') .' = '. Rupiah($item['wajibdibayar'] - $item['sudahbayar']);
        //     }
        // }

        $replacements = array(

        );
        // $test='';
        // foreach ($lock_list as $lock_list_row) {
        //     foreach ($lock_list_row['dataPembayaran'] as $key=>$item ){
        //         $i=0;
        //         for($i=0; $i < count($lock_list); $i++){
        //        $test = $i.Bulan( (new DateTime($item['periode']))->format('m')).'-'. (new DateTime($item['periode']))->format('Y') .' = '. Rupiah($item['wajibdibayar'] - $item['sudahbayar']);
        //         }
        //     }
        // }

        foreach ($lock_list as $lock_list_row) {
            foreach ($lock_list_row['lock']->staff as $index => $staff_row) {
                $petugas = $staff_row->name;
            }
            $periode = '';
            foreach ($lock_list_row['dataPembayaran'] as $key => $item) {
                $periode .= ',' . Bulan((new DateTime($item['periode']))->format('m')) . '-' . (new DateTime($item['periode']))->format('Y') . ' = ' . Rupiah($item['wajibdibayar'] - $item['sudahbayar']);
            }
            array_push($replacements, array
                ('code' => $lock_list_row['lock']->code,
                    'petugas' => $petugas,
                    'namapelanggan' => $lock_list_row['customer']->namapelanggan,
                    'alamat' => $lock_list_row['customer']->alamat,
                    'noSBG' => $lock_list_row['customer']->nomorrekening,
                    'area' => $lock_list_row['customer']->idareal,
                    'total' => is_int($lock_list_row['recap']['total']) ? Rupiah($lock_list_row['recap']['total']) : $lock_list_row['recap']['total'],
                    'tanggal' => date('d') . ' ' . Bulan(date('m')) . ' ' . date('Y'),
                    'periode' => $periode,
                )
            );
        }

        $templateProcessor->cloneBlock('block_name', 0, true, false, $replacements);
        $fileName = date('d') . ' ' . Bulan(date('m')) . ' ' . date('Y');
        $templateProcessor->saveAs($fileName . '.docx');
        return response()->download($fileName . '.docx');

        return $test;
        // return view('admin.spp.spp', compact('lock_list'));
    }

}
