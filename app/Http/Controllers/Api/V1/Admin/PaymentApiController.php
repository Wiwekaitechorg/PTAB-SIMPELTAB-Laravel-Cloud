<?php

namespace App\Http\Controllers\api\v1\admin;

use App\CtmPembayaran;
use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use DB;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    use TraitModel;

    public function index()
    {
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update($id)
    {
        //
    }

    public function updatePay(Request $request)
    {
        $rules = array(
            'form' => 'required',
        );

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();
            return response()->json([
                'status' => false,
                'cek' => json_decode($request->form),
                'message' => $errors,
                'data' => $request->all(),
            ]);
        }

        $data = json_decode($request->form);
        $arrQry = [];

        try {

            foreach ($data as $value) {

                $result = DB::connection('mysql2')->table('tblpembayaran')
                    ->where('tahunrekening', '=', $value->tahunrekening)
                    ->where('bulanrekening', '=', $value->bulanrekening)
                    ->where('nomorrekening', '=', $value->nomorrekening)
                    ->update(['statuslunas' => $value->statuslunas]);
                if ($result != 1) {
                    $arrQry[] = ['nomorrekening' => $value->nomorrekening, 'bulanrekening' => $value->bulanrekening, 'tahunrekening' => $value->tahunrekening];
                }
                // $arrQry = [];
            }
            return response()->json([
                'status' => true,
                'message' => 'Data Pembayaran Update Success',
                'data' => $result,
                //'failed' => $arrQry
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'status' => false,
                'cek' => $request->form,
                'message' => 'Data Pembayaran Update Gagal.',
                'data' => $e,
            ]);
        }

        // $tblpelanggan_arr = $this->getCtmJenispelanggan($var['nomorrekening']);
        // //hitung rp-tagihan
        // $tblpelanggan_arr['pemakaianair'] = $var['pemakaianair'];
        // $pdam_tagihan_arr = $this->getCtmTagihan($tblpelanggan_arr);

        // $tblpembayaran = CtmPembayaran::where('tahunrekening', '=', $var['tahunbayar'])
        //     ->where('bulanrekening', '=', $var['bulanbayar'])
        //     ->where('nomorrekening', '=', $var['nomorrekening'])
        //     ->first();

        // $arrCol = array("nomorrekening", "bulanrekening", "tahunrekening", "idgol", "idareal", "tarif01", "tarif02", "tarif03", "tarif04", "tarif05", "tarif06", "danameter", "adm", "beban", "denda", "batas1", "batas2", "batas3", "batas4", "batas5", "pajak", "pemakaianair", "pemakaianair01", "pemakaianair02", "pemakaianair03", "pemakaianair04", "pemakaianair05", "pemakaianair06", "bulanini", "bulanlalu", "wajibdibayar", "idbiro", "tglbayarterakhir", "operator", "operator1", "_synced");
        // $arrVal = array($var['nomorrekening'], $var['bulanbayar'], $var['tahunbayar'], $tblpelanggan_arr['idgol'], $tblpelanggan_arr['idareal'], $tblpelanggan_arr['tarif01'], $tblpelanggan_arr['tarif02'], $tblpelanggan_arr['tarif03'], $tblpelanggan_arr['tarif04'], $tblpelanggan_arr['tarif05'], $tblpelanggan_arr['tarif06'], $tblpelanggan_arr['danameter'], $tblpelanggan_arr['adm'], $tblpelanggan_arr['beban'], "'0'", $tblpelanggan_arr['batas1'], $tblpelanggan_arr['batas2'], $tblpelanggan_arr['batas3'], $tblpelanggan_arr['batas4'], $tblpelanggan_arr['batas5'], $pdam_tagihan_arr['pajak'], $pdam_tagihan_arr['pemakaianair'], $pdam_tagihan_arr['pemakaianair01'], $pdam_tagihan_arr['pemakaianair02'], $pdam_tagihan_arr['pemakaianair03'], $pdam_tagihan_arr['pemakaianair04'], $pdam_tagihan_arr['pemakaianair05'], $pdam_tagihan_arr['pemakaianair06'], $var['pencatatanmeter'], $var['meterawal'], $pdam_tagihan_arr['rp_tagihan'], $tblpelanggan_arr['idbiro'], $var['datecatatf3'], $var['operator'], $var['operator'], "0");

        // $arrQry = array();
        // foreach ($arrCol as $key => $value) {
        //     $arrQry[$value] = $arrVal[$key];
        // }

        // if ($tblpembayaran === null) {
        //     $result = DB::connection('mysql2')->table('tblpembayaran')->insert($arrQry);
        // } else {
        //     $result = DB::connection('mysql2')->table('tblpembayaran')
        //         ->where('tahunrekening', '=', $var['tahunbayar'])
        //         ->where('bulanrekening', '=', $var['bulanbayar'])
        //         ->where('nomorrekening', '=', $var['nomorrekening'])
        //         ->update($arrQry);
        // }
    }

    public function destroy(CtmPembayaran $staff)
    {
    }
}
