<?php

namespace App\Http\Controllers\Admin;

use App\CtmGambarmetersms;
use DB;
use App\CtmPelanggan;
use App\Exports\TestExport;
use App\Http\Controllers\Controller;
use App\Imports\StaffImport;
use App\Staff;
use App\wa_history;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GawatDaruratController extends Controller
{
    public function index(Request $request)
    {
        ini_set("memory_limit", -1);
        set_time_limit(0);
        $month = date("m", strtotime('-1 month', strtotime(date('Y-m-01'))));
        $year =  date("Y", strtotime('-1 month', strtotime(date('Y-m-01'))));
        $data12 = [];
        $mapping = CtmGambarmetersms::selectRaw('gambarmetersms.nomorrekening,
        gambarmetersms.tanggal,
        gambarmeter.filegambar,
        gambarmeter.infowaktu,
        tblpelanggan.nomorrekening,
        tblpelanggan.namapelanggan,
        tblpelanggan.namapelanggan,
        tblpelanggan.idgol,
        tblpelanggan.idareal,
        tblpelanggan.alamat,
        map_koordinatpelanggan.lat,
        map_koordinatpelanggan.lng,
        gambarmetersms.bulanrekening,
        gambarmetersms.tahunrekening,
        tblopp.operator,
        Elt(gambarmetersms.bulanrekening, tblpemakaianair.pencatatanmeter1, tblpemakaianair.pencatatanmeter2, tblpemakaianair.pencatatanmeter3, tblpemakaianair.pencatatanmeter4, tblpemakaianair.pencatatanmeter5, tblpemakaianair.pencatatanmeter6, tblpemakaianair.pencatatanmeter7, tblpemakaianair.pencatatanmeter8, tblpemakaianair.pencatatanmeter9, tblpemakaianair.pencatatanmeter10, tblpemakaianair.pencatatanmeter11, tblpemakaianair.pencatatanmeter12) pencatatanmeter, Elt(gambarmetersms.bulanrekening, tblpemakaianair.pemakaianair1, tblpemakaianair.pemakaianair2, tblpemakaianair.pemakaianair3, tblpemakaianair.pemakaianair4, tblpemakaianair.pemakaianair5, tblpemakaianair.pemakaianair6, tblpemakaianair.pemakaianair7, tblpemakaianair.pemakaianair8, tblpemakaianair.pemakaianair9, tblpemakaianair.pemakaianair10, tblpemakaianair.pemakaianair11, tblpemakaianair.pemakaianair12) pemakaianair')
            ->join('tblpemakaianair', 'tblpemakaianair.nomorrekening', '=', 'gambarmetersms.nomorrekening')

            ->join('gambarmeter', 'gambarmeter.idgambar', '=', 'gambarmetersms.idgambar')
            ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            ->join('map_koordinatpelanggan', 'map_koordinatpelanggan.nomorrekening', '=', 'tblpelanggan.nomorrekening')
            ->join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
            ->join('tblopp', 'tblopp.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            // ->groupBy('tblpelanggan.nomorrekening')
            ->where('gambarmetersms.bulanrekening', $month)
            ->where('gambarmetersms.tahunrekening', $year)
            ->where('tblpemakaianair.tahunrekening', $year)
            ->where('tblwilayah.group_unit', '1')
            // ->FilterOperator($operator)
            // ->FilterSbg($request->nomorrekening)
            ->where('tblopp.status', '1')
            // ->groupBy('tblpelanggan.nomorrekening')
            ->orderByRaw('tblpelanggan.nomorrekening * 1')
            // ->skip(0)
            // ->take(10)
            ->get();
        // dd('selesai');
        foreach ($mapping as $data) {
            $data12[] =   [
                'Nomor Rekening' => $data->nomorrekening,
                'Nama'  => $data->namapelanggan,
                'Alamat'  => $data->alamat,
                'Golongan'  => $data->idgol,
                'Area'  => $data->idareal,
                'X'  => $data->lat,
                'Y'  => $data->lng,
                'Periode'  => $data->tanggal,
                'Kubikasi' => $data->pemakaianair,
                'Foto WM' => "https://www.ptab-vps-storage.com/pdam".$data->filegambar,
            ];
        }

        // dd($data12, $mapping);
        return Excel::download(new TestExport($data12), 'data_pelanggan.xlsx');
        // dd($mapping);
    }
    public function store(Request $request)
    {
        // Staff::where('id', $id)->first();

    }

    public function import(Request $request)
    {
        abort_unless(\Gate::allows('staff_edit'), 403);
        $import = new StaffImport;
        $test =  Excel::import($import, $request->file('file'));
        // dd($test);
        $array = $import->getArray();
        // dd($array);
        abort_unless(\Gate::allows('wablast_access'), 403);

        $staffs = $import->getArray();

        // dd($staffs);
        ini_set("memory_limit", -1);
        set_time_limit(0);
        //ini test

        // dd($staffs[2]['id']);
        for ($i = 0; $i < (count($staffs) - 1); $i++) {
            if ($staffs[$i]['work_unit_id'] != null && $staffs[$i]['id'] != null) {
                $staff = Staff::where('id', $staffs[$i]['id'])->update([
                    'work_unit_id' => $staffs[$i]['work_unit_id']
                ]);
                // dd($staff);
                // $staff->work_unit_id = $staffs[$i]['work_unit_id'];
                // $staff->nomorhp = $staffs[$i]['nomorhp'];
                // $staff->_synced = 0;
                // $staff->save();
                // dd($staff);
            }
        }
        dd($staffs);

        // return redirect()->route('admin.staffs.index');

        dd($request->file('file'));
        Staff::where('id', $id)->first();
    }
}
