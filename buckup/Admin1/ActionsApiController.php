<?php

namespace App\Http\Controllers\api\v1\admin1;

use App\ActionApi;
use App\ActionStaff;
use App\Audited;
use App\CtmGambarmetersms;
use App\CtmMapKunjungan;
use App\CtmPbk;
use App\CtmPelanggan;
use App\CtmPembayaran;
use App\CtmStatussmPelanggan;
use App\CtmWilayah;
use App\Customer;
use App\CustomerApi;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Lock;
use App\LockAction;
use App\StaffApi;
use App\Subdapertement;
use App\Ticket;
use App\TicketApi;
use App\Traits\TraitModel;
use App\Traits\WablasTrait;
use App\User;
use App\wa_history;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use OneSignal;

class ActionsApiController extends Controller
{
    use TraitModel;
    use WablasTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    public function getCtmHasilbaca(Request $request)
    {
        $mapping = CtmGambarmetersms::selectRaw('gambarmetersms.nomorrekening, DATE(gambarmetersms.tanggal) as date, COUNT(DISTINCT gambarmetersms.nomorrekening) total')
            ->join('tblpemakaianair', 'tblpemakaianair.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            ->join('gambarmeter', 'gambarmeter.idgambar', '=', 'gambarmetersms.idgambar')
            ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            ->join('tblopp', 'tblopp.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            ->FilterMonth($request->month)
            ->FilterYear($request->year)
            ->FilterOperator($request->operator)
            ->where('tblopp.status', '1')
            ->groupBy('date')
            ->get();
        try {
            if (!empty($mapping)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $mapping,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getCtmStatussm(Request $request)
    {
        $month = $request->month;
        $year = $request->year;
        $map_kunjungan_num = CtmMapKunjungan::selectRaw('idkunjungan')
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->count();
        // $map_kunjungan_num = count($map_kunjungan);
        $status = CtmPelanggan::selectRaw('CASE
        WHEN tblstatussmpelanggan.NamaStatus != "-" AND tblstatussmpelanggan.NamaStatus != "" AND tblstatussmpelanggan.NamaStatus IS NOT NULL THEN tblstatussmpelanggan.NamaStatus ELSE "Terbaca" END AS namastatus, COUNT(tblpelanggan.nomorrekening) jumlahstatus,tblstatussmpelanggan.statusid')
            ->leftJoinSub(
                CtmStatussmPelanggan::selectRaw('tblstatussmpelanggan.statussm,tblstatussmpelanggan.nomorrekening,tblstatuswm.NamaStatus AS NamaStatus,tblstatuswm.id AS statusid')
                    ->join('tblstatuswm', 'tblstatussmpelanggan.statussm', '=', 'tblstatuswm.id')
                    ->FilterMonth($request->month)
                    ->FilterYear($request->year),
                'tblstatussmpelanggan',
                function ($join) {
                    $join->on('tblstatussmpelanggan.nomorrekening', '=', 'tblpelanggan.nomorrekening');
                }
            )
            ->where('tblpelanggan.status', 1)
            ->groupBy('tblstatussmpelanggan.statusid')
            ->get();
        $status_obj = array();

        $namastatus_first = 'Terbaca';
        $jumlahstatus_first = $map_kunjungan_num; //0
        $statusid_first = '-';
        $key_index = 0;
        foreach ($status as $key => $value) {
            if ($value->namastatus != 'Terbaca') {
                $jumlahstatus_first -= $value->jumlahstatus; //+
            }
        }
        $status_obj[$key_index]['namastatus'] = $namastatus_first;
        $status_obj[$key_index]['jumlahstatus'] = $jumlahstatus_first;
        $status_obj[$key_index]['statusid'] = $statusid_first;
        foreach ($status as $key => $value) {
            if ($value->namastatus != 'Terbaca') {
                $key_index++;
                $status_obj[$key_index]['namastatus'] = $value->namastatus;
                $status_obj[$key_index]['jumlahstatus'] = $value->jumlahstatus;
                $status_obj[$key_index]['statusid'] = $value->statusid;
            }
        }
        try {
            if (!empty($status)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $status_obj,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getCtmkubikasi(Request $request)
    {
        $mapping = CtmGambarmetersms::selectRaw('tbljenispelanggan.jenispelanggan,tbljenispelanggan.id as jenispelanggan_code, count(gambarmetersms.nomorrekening) lembar, sum(Elt(gambarmetersms.bulanrekening, tblpemakaianair.pemakaianair1, tblpemakaianair.pemakaianair2, tblpemakaianair.pemakaianair3, tblpemakaianair.pemakaianair4, tblpemakaianair.pemakaianair5, tblpemakaianair.pemakaianair6, tblpemakaianair.pemakaianair7, tblpemakaianair.pemakaianair8, tblpemakaianair.pemakaianair9, tblpemakaianair.pemakaianair10, tblpemakaianair.pemakaianair11, tblpemakaianair.pemakaianair12)) kubikasi, sum(Elt(gambarmetersms.bulanrekening, tblpemakaianair.pemakaianair1, tblpemakaianair.pemakaianair2, tblpemakaianair.pemakaianair3, tblpemakaianair.pemakaianair4, tblpemakaianair.pemakaianair5, tblpemakaianair.pemakaianair6, tblpemakaianair.pemakaianair7, tblpemakaianair.pemakaianair8, tblpemakaianair.pemakaianair9, tblpemakaianair.pemakaianair10, tblpemakaianair.pemakaianair11, tblpemakaianair.pemakaianair12))/count(gambarmetersms.nomorrekening) avg')
            ->join('tblpemakaianair', 'tblpemakaianair.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            ->join('tbljenispelanggan', 'tblpelanggan.idgol', '=', 'tbljenispelanggan.id')
            ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
            ->FilterMonth($request->month)
            ->FilterYear($request->year)
            ->FilterAreal($request->areal)
            ->groupBy('tbljenispelanggan.id')
            ->get();
        try {
            if (!empty($mapping)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $mapping,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getCtmarealgroup(Request $request)
    {
        $wilayah = CtmWilayah::selectRaw('CASE
        WHEN tblwilayah.group_unit = 1 THEN "DAERAH KOTA"
        WHEN tblwilayah.group_unit = 2 THEN "UNIT KERAMBITAN"
        WHEN tblwilayah.group_unit = 3 THEN "UNIT SELEMADEG"
        WHEN tblwilayah.group_unit = 4 THEN "UNIT PENEBEL"
        ELSE "UNIT BATURITI"
    END AS namawilayah,tblwilayah.group_unit')
            ->groupBy('group_unit')
            ->get();
        try {
            if (!empty($wilayah)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $wilayah,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getCtmoperator(Request $request)
    {
        $operator = CtmPbk::where('Status', '1')
            ->get();
        try {
            if (!empty($operator)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $operator,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getCtmmapping(Request $request)
    {
        $operator = '';
        if (isset($request->operator)) {
            $operator = $request->operator;
        }
        $mapping = CtmGambarmetersms::selectRaw('gambarmetersms.nomorrekening, gambarmetersms.tanggal, gambarmeter.filegambar,gambarmeter.infowaktu, tblpelanggan.nomorrekening,tblpelanggan.namapelanggan,tblpelanggan.namapelanggan,tblpelanggan.idgol,tblpelanggan.idareal, gambarmetersms.nomorrekening,gambarmetersms.bulanrekening,gambarmetersms.tahunrekening,tblopp.operator, Elt(gambarmetersms.bulanrekening, tblpemakaianair.pencatatanmeter1, tblpemakaianair.pencatatanmeter2, tblpemakaianair.pencatatanmeter3, tblpemakaianair.pencatatanmeter4, tblpemakaianair.pencatatanmeter5, tblpemakaianair.pencatatanmeter6, tblpemakaianair.pencatatanmeter7, tblpemakaianair.pencatatanmeter8, tblpemakaianair.pencatatanmeter9, tblpemakaianair.pencatatanmeter10, tblpemakaianair.pencatatanmeter11, tblpemakaianair.pencatatanmeter12) pencatatanmeter, Elt(gambarmetersms.bulanrekening, tblpemakaianair.pemakaianair1, tblpemakaianair.pemakaianair2, tblpemakaianair.pemakaianair3, tblpemakaianair.pemakaianair4, tblpemakaianair.pemakaianair5, tblpemakaianair.pemakaianair6, tblpemakaianair.pemakaianair7, tblpemakaianair.pemakaianair8, tblpemakaianair.pemakaianair9, tblpemakaianair.pemakaianair10, tblpemakaianair.pemakaianair11, tblpemakaianair.pemakaianair12) pemakaianair')
            ->join('tblpemakaianair', 'tblpemakaianair.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            ->join('gambarmeter', 'gambarmeter.idgambar', '=', 'gambarmetersms.idgambar')
            ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            ->join('tblopp', 'tblopp.nomorrekening', '=', 'gambarmetersms.nomorrekening')
            ->FilterMonth($request->month)
            ->FilterYear($request->year)
            ->FilterOperator($operator)
            ->FilterSbg($request->nomorrekening)
            ->where('tblopp.status', '1')
            ->get();
        try {
            if (!empty($mapping)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $mapping,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getPermintaan(Request $request)
    {
        $tickets = Ticket::whereBetween(DB::raw('DATE(created_at)'), [$request->from, $request->to])->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();
        try {
            if (!empty($tickets)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $tickets,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getComplaint(Request $request)
    {
        $tickets = Ticket::whereBetween(DB::raw('DATE(created_at)'), [$request->from, $request->to])->FilterDepartment($request->dapertement_id)->FilterStatus($request->status)->with(['action', 'customer', 'category', 'dapertement'])->get();
        try {
            if (!empty($tickets)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $tickets,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getSrnew(Request $request)
    {
        $year = date('Y');
        $customer = CtmPelanggan::selectRaw('CASE
        WHEN MONTH(tgltersambung) = 1 THEN "Januari"
        WHEN MONTH(tgltersambung) = 2 THEN "Februari"
        WHEN MONTH(tgltersambung) = 3 THEN "Maret"
        WHEN MONTH(tgltersambung) = 4 THEN "April"
        WHEN MONTH(tgltersambung) = 5 THEN "Mei"
        WHEN MONTH(tgltersambung) = 6 THEN "Juni"
        WHEN MONTH(tgltersambung) = 7 THEN "Juli"
        WHEN MONTH(tgltersambung) = 8 THEN "Agustus"
        WHEN MONTH(tgltersambung) = 9 THEN "September"
        WHEN MONTH(tgltersambung) = 10 THEN "Oktober"
        WHEN MONTH(tgltersambung) = 11 THEN "November"
        ELSE "Desember"
    END AS bulan,COUNT(nomorrekening) as total, MONTH(tgltersambung) as month, YEAR(tgltersambung) as tahun')
            ->groupBy('month')
            ->whereYear('tgltersambung', '=', $year)
            ->get();
        try {
            if (!empty($customer)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $customer,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getSr(Request $request)
    {
        $customer = CtmPelanggan::selectRaw('CASE
        WHEN tblwilayah.group_unit = 1 THEN "DAERAH KOTA"
        WHEN tblwilayah.group_unit = 2 THEN "UNIT KERAMBITAN"
        WHEN tblwilayah.group_unit = 3 THEN "UNIT SELEMADEG"
        WHEN tblwilayah.group_unit = 4 THEN "UNIT PENEBEL"
        ELSE "UNIT BATURITI"
    END AS namawilayah,SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as totalaktif, SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as totalpasif')
            ->join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
            ->groupBy('tblwilayah.group_unit')
            ->get();
        try {
            if (!empty($customer)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $customer,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }

    public function getAudited(Request $request)
    {
        $data = Audited::FilterYear($request->year)->get();
        try {
            if (!empty($data)) {
                return response()->json([
                    'message' => 'Sukses',
                    'data' => $data,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal',
                'data' => $ex,
            ]);
        }
    }
    public function actionStatusUpdate(Request $request)
    {
        try {
            // ambil data dari request simpan di dataForm

            $dataForm = json_decode($request->form);
            // data action
            $action = ActionApi::where('id', $dataForm->action_id)->with('ticket')->with('staff')->first();
            $uploadAction = false;
            $cekError = 'kosong';
            // image yang lama disimpan
            $actionImage = json_decode($action->image);
            $img_path = "/images/action";
            //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            $basepath  = base_path().'/public';
            $dataImageName = [];
            $dataImageNameTool = [];

            // cek status dan upload gambar dalam pengerjaan
            if ($action->status == 'pending' && $dataForm->status == 'active') {
                for ($i = 1; $i <= $request->countImagePengerjaan; $i++) {
                    if ($request->file('image' . $i)) {
                        $resourceImage = $request->file('image' . $i);
                        $nameImage = strtolower($action->id);
                        $file_extImage = $request->file('image' . $i)->extension();
                        $nameImage = str_replace(" ", "-", $nameImage);

                        $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->action_id . $i . "." . $file_extImage;

                        $resourceImage->move($basepath . $img_path, $img_name);

                        $dataImageName[] = $img_name;
                    } else {
                        $responseImage = 'Image tidak di dukung';
                        break;
                    }
                }
            } else if ($action->status == 'active' && $dataForm->status == 'active') {
                $oldImage = json_decode($action->image);
                $index = 0;

                for ($i = 1; $i <= $request->countImagePengerjaan; $i++) {
                    if ($request->file('image' . $i)) {
                        $resourceImage = $request->file('image' . $i);
                        $nameImage = strtolower($action->id);
                        $file_extImage = $request->file('image' . $i)->extension();
                        $nameImage = str_replace(" ", "-", $nameImage);

                        $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->action_id . $i . "." . $file_extImage;

                        $resourceImage->move($basepath . $img_path, $img_name);

                        $dataImageName[] = $img_name;
                    } else {
                        $dataImageName[] = $oldImage[$i - 1];
                        $responseImage = 'Image tidak di dukung';
                    }
                    // $index++;
                }
            }

            // foto sebelum pengerjaan
            if ($request->file('image_prework')) {
                $resource_image_prework = $request->file('image_prework');
                $id_name_image_prework = strtolower($action->id);
                $file_ext_image_prework = $request->file('image_prework')->extension();
                $id_name_image_prework = str_replace(' ', '-', $id_name_image_prework);

                $name_image_prework = $img_path . '/' . $id_name_image_prework . '-' . $dataForm->action_id . '-pre.' . $file_ext_image_prework;

                $resource_image_prework->move($basepath . $img_path, $name_image_prework);
                $data_image_prework = $name_image_prework;
            }

            // foto alat
            if ($action->status == 'pending' && $dataForm->status == 'active') {
                for ($i = 1; $i <= $request->countImageTool; $i++) {
                    if ($request->file('image_tools' . $i)) {
                        $resourceImage = $request->file('image_tools' . $i);
                        $nameImage = strtolower($action->id);
                        $file_extImage = $request->file('image_tools' . $i)->extension();
                        $nameImage = str_replace(" ", "-", $nameImage);

                        $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->action_id . $i . "." . $file_extImage;

                        $resourceImage->move($basepath . $img_path, $img_name);

                        $dataImageNameTool[] = $img_name;
                    } else {
                        $responseImage = 'Image tidak di dukung';
                        break;
                    }
                }
            } else if ($action->status == 'active' && $dataForm->status == 'active') {
                $oldImage = json_decode($action->image_tools);
                $index = 0;

                for ($i = 1; $i <= $request->countImageTool; $i++) {
                    if ($request->file('image_tools' . $i)) {
                        $resourceImage = $request->file('image_tools' . $i);
                        $nameImage = strtolower($action->id);
                        $file_extImage = $request->file('image_tools' . $i)->extension();
                        $nameImage = str_replace(" ", "-", $nameImage);

                        $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->action_id . $i . "-tools." . $file_extImage;

                        $resourceImage->move($basepath . $img_path, $img_name);

                        $dataImageNameTool[] = $img_name;
                    } else {
                        $dataImageNameTool[] = $oldImage[$i - 1];
                        $responseImage = 'Image tidak di dukung';
                    }
                    // $index++;
                }
            }

            // if ($request->file('image_tools')) {
            //     $resource_image_tools = $request->file('image_tools');
            //     $id_name_image_tools = strtolower($action->id);
            //     $file_ext_image_tools = $request->file('image_tools')->extension();
            //     $id_name_image_tools = str_replace(' ', '-', $id_name_image_tools);

            //     $name_image_tools = $img_path . '/' . $id_name_image_tools . '-' . $dataForm->action_id . '-tools.' . $file_ext_image_tools;

            //     $resource_image_tools->move($basepath . $img_path, $name_image_tools);
            //     $data_image_tools = $name_image_tools;
            // }

            for ($i = 1; $i <= $request->countImageDone; $i++) {

                if ($request->file('image_done' . $i)) {
                    $resourceImageDone = $request->file('image_done' . $i);
                    $nameImageDone = strtolower($action->id);
                    $file_extImageDone = $request->file('image_done' . $i)->extension();
                    $nameImageDone = str_replace(" ", "-", $nameImageDone);

                    $img_name_done = $img_path . "/" . $nameImageDone . "-" . $dataForm->action_id . $i . "-done." . $file_extImageDone;

                    $resourceImageDone->move($basepath . $img_path, $img_name_done);

                    $dataImageNameDone[] = $img_name_done;
                } else {
                    $responseImage = 'Image tidak di dukung';
                    break;
                }
            }
            $action = ActionApi::where('id', $dataForm->action_id)->with('ticket')->with('staff')->first();
            $cekAllStatus = false;
            $statusAction = $dataForm->status;

            $dateNow = date('Y-m-d H:i:s');

            // update database
            $dataNewAction = array(
                'status' => $statusAction,
                // 'image_prework' => $data_image_prework,
                // 'image_tools' => $dataImageNameTool,
                'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow,
                'memo' => $dataForm->memo,
            );
            if ($action->status != 'close' && $dataForm->status != 'close') {

                if ($dataImageNameTool && count($dataImageNameTool) > 0) {
                    $dataNewAction['image_tools'] = str_replace("\/", "/", json_encode($dataImageNameTool));
                }

                if ($request->file('image_prework')) {
                    $dataNewAction['image_prework'] = $data_image_prework;
                }

                if ($dataImageName && count($dataImageName) > 0) {
                    $dataNewAction['image'] = str_replace("\/", "/", json_encode($dataImageName));
                }

                $uploadAction = true;
            } else {
                $dataNewAction['image_done'] = str_replace("\/", "/", json_encode($dataImageNameDone));
                $uploadAction = true;
            }

            if ($uploadAction) {
                $action->update($dataNewAction);
                //update staff
                $ids = $action->staff()->allRelatedIds();
                foreach ($ids as $sid) {
                    $action->staff()->updateExistingPivot($sid, ['status' => $dataForm->status]);
                }
                //update ticket status
                $ticket = TicketApi::find($action->ticket_id);
                $ticket->status = $statusAction;
                $ticket->save();

                //def subdap
                $dateNow = date('Y-m-d H:i:s');
                $subdapertement_def = Subdapertement::where('def', '1')->first();
                $dapertement_def_id = $subdapertement_def->dapertement_id;
                $subdapertement_def_id = $subdapertement_def->id;

                //if close send notif to user
                //wa notif
                // $wa_data = [
                //     'phone' => $this->gantiformat($phone),
                //     'customer_id' => $customer_id,
                //     'message' => $message,
                //     'template_id' => 'test1',
                //     'status' => 'gagal',
                //     'ref_id' => $code . $customer_id,
                // ];
                //onesignal notif
                if ($statusAction == 'close') {
                    $customer = CustomerApi::find($ticket->customer_id);
                    $id_onesignal = $customer->_id_onesignal;
                    $message = 'Customer: Keluahan Sudah Diselesaikan  : ' . $ticket->code . $dataForm->memo;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    $phone_no = $customer->phone;
                    $wa_data = [
                        'phone' => $this->gantiFormat($phone_no),
                        'customer_id' => null,
                        'message' => $message,
                        'template_id' => '',
                        'status' => 'gagal',
                        'ref_id' => $wa_code,
                        'created_at' => date('Y-m-d h:i:sa'),
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to admin
                $admin_arr = User::where('dapertement_id', 0)->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Admin: Status Pengerjaan Diupdate  : ' . $ticket->code . $dataForm->memo;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to humas
                $admin_arr = User::where('subdapertement_id', $subdapertement_def_id)->where('staff_id', 0)->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Humas: Status Pengerjaan Diupdate  : ' . $ticket->code . $dataForm->memo;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to departement terkait
                $admin_arr = User::where('dapertement_id', $ticket->dapertement_id)
                    ->where('subdapertement_id', 0)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Bagian: Status Pengerjaan Diupdate : ' . $ticket->code . $dataForm->memo;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to sub departement terkait
                $admin_arr = User::where('subdapertement_id', $action->subdapertement_id)->where('staff_id', 0)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Sub Bagian: Status Pengerjaan Diupdate : ' . $ticket->code . $dataForm->memo;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to staff terkait
                $actionstaffs = ActionStaff::where('action_id', '=', $action->id)->get();
                foreach ($actionstaffs as $key => $actionstaff) {
                    $message = 'Staff: Status Pengerjaan Diupdate : ' . $ticket->code . $dataForm->memo;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    $staff_phone = StaffApi::where('id', $actionstaff->staff_id)->first();
                    $phone_no = $staff_phone->phone;

                    $wa_data = [
                        'phone' => $this->gantiFormat($phone_no),
                        'customer_id' => null,
                        'message' => $message,
                        'template_id' => '',
                        'status' => 'gagal',
                        'ref_id' => $wa_code,
                        'created_at' => date('Y-m-d h:i:sa'),
                        'updated_at' => date('Y-m-d h:i:sa'),
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
                }

                return response()->json([
                    'message' => 'Status di ubah ',
                    'data' => $action,
                    'datanew' => $dataNewAction,
                ]);
            } else {
                return response()->json([
                    'message' => '500',
                    'data' => $uploadAction,
                    'pesan' => $cekError,
                    'status' => $action->status,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal update status ',
                'data' => $ex,
            ]);
        }
    }

    function list(Request $request)
    {
        $department = '';
        $subdepartment = 0;
        $staff = 0;
        if (isset($request->userid) && $request->userid != '') {
            $admin = User::with('roles')->find($request->userid);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("ticket_all_access", $permission)) {
                $department = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
                $staff = $admin->staff_id;
            }
        }

        try {
            if ($subdepartment > 0 && $staff > 0) {
                $actions = ActionApi::selectRaw('DISTINCT actions.*')
                    ->join('action_staff', function ($join) use ($staff) {
                        $join->on('action_staff.action_id', '=', 'actions.id')
                            ->where('action_staff.staff_id', '=', $staff);
                    })
                    ->with('staff')
                    ->with('dapertement')
                    ->with('ticket')
                    ->where('ticket_id', $request->ticket_id)
                    ->orderBy('start', 'desc')
                    ->get();
            } else {
                $actions = ActionApi::with('staff')
                    ->with('dapertement')
                    ->with('ticket')
                    ->where('ticket_id', $request->ticket_id)
                    ->orderBy('start', 'desc')
                    ->get();
            }
            return response()->json([
                'message' => 'Data Ticket',
                'data' => $actions,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'data' => $ex,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dateNow = date('Y-m-d H:i:s');

        $data = $request->all();

        $rules = array(
            'description' => 'required',
            'ticket_id' => 'required',
            'dapertement_id' => 'required',
        );

        $validator = \Validator::make($data, $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all(),
            ]);
        }

        $data['status'] = 'pending';
        $data['start'] = $dateNow;
        //set SPK
        $arr['dapertement_id'] = $request->dapertement_id;
        $arr['month'] = date("m");
        $arr['year'] = date("Y");
        $last_spk = $this->get_last_code('spk', $arr);
        $spk = acc_code_generate($last_spk, 21, 17, 'Y');
        $data['spk'] = $spk;

        $action = ActionApi::create($data);
        $ticket = TicketApi::find($request->ticket_id);

        //def subdap
        $dateNow = date('Y-m-d H:i:s');
        $subdapertement_def = Subdapertement::where('def', '1')->first();
        $dapertement_def_id = $subdapertement_def->dapertement_id;
        $subdapertement_def_id = $subdapertement_def->id;

        //send notif to admin
        $admin_arr = User::where('dapertement_id', 0)->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Admin: Tindakan Baru Dibuat : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($admin->staff_id > 0) {
                $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                $phone_no = $staff_phone->phone;
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
                'updated_at' => date('Y-m-d h:i:sa'),
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

        //send notif to humas
        $admin_arr = User::where('subdapertement_id', $subdapertement_def_id)
            ->where('staff_id', 0)
            ->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Humas: Tindakan Baru Dibuat : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($admin->staff_id > 0) {
                $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                $phone_no = $staff_phone->phone;
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
                'updated_at' => date('Y-m-d h:i:sa'),
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

        //send notif to departement terkait
        $admin_arr = User::where('dapertement_id', $action->dapertement_id)
            ->where('subdapertement_id', 0)
            ->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Bagian: Tindakan Baru Dibuat : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($admin->staff_id > 0) {
                $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                $phone_no = $staff_phone->phone;
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
                'updated_at' => date('Y-m-d h:i:sa'),
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

        //send notif to sub departement terkait
        $admin_arr = User::where('subdapertement_id', $action->subdapertement_id)->where('staff_id', 0)
            ->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Sub Bagian: Tindakan Baru Dibuat : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($admin->staff_id > 0) {
                $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                $phone_no = $staff_phone->phone;
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
                'updated_at' => date('Y-m-d h:i:sa'),
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

        //send notif to staff
        $actionstaffs = ActionStaff::where('action_id', '=', $action->id)->get();
        foreach ($actionstaffs as $key => $actionstaff) {
            $message = 'Staff: Tindakan Baru Dibuat : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            $staff_phone = StaffApi::where('id', $actionstaff->staff_id)->first();
            $phone_no = $staff_phone->phone;
            $wa_data = [
                'phone' => $this->gantiFormat($phone_no),
                'customer_id' => null,
                'message' => $message,
                'template_id' => '',
                'status' => 'gagal',
                'ref_id' => $wa_code,
                'created_at' => date('Y-m-d h:i:sa'),
                'updated_at' => date('Y-m-d h:i:sa'),
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
        }

        return response()->json([
            'message' => 'Data Dapertement Add Success',
            'data' => $action,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ActionApi $action)
    {
        $rules = array(
            'description' => 'required',
            'dapertement_id' => 'required',
        );

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();
            return response()->json([
                'message' => $errors,
                'data' => $request->all(),
            ]);
        }

        $action->update($request->all());
        $ticket = TicketApi::find($action->ticket_id);

        //def subdap
        $dateNow = date('Y-m-d H:i:s');
        $subdapertement_def = Subdapertement::where('def', '1')->first();
        $dapertement_def_id = $subdapertement_def->dapertement_id;
        $subdapertement_def_id = $subdapertement_def->id;

        //send notif to admin
        $admin_arr = User::where('dapertement_id', 0)->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Admin: Tindakan Baru Diupdate : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($admin->staff_id > 0) {
                $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                $phone_no = $staff_phone->phone;
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
                'updated_at' => date('Y-m-d h:i:sa'),
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

        //send notif to humas
        $admin_arr = User::where('subdapertement_id', $subdapertement_def_id)
            ->where('staff_id', 0)
            ->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Humas: Tindakan Baru Diupdate : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($admin->staff_id > 0) {
                $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                $phone_no = $staff_phone->phone;
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
                'updated_at' => date('Y-m-d h:i:sa'),
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

        //send notif to departement terkait
        $admin_arr = User::where('dapertement_id', $action->dapertement_id)
            ->where('subdapertement_id', 0)
            ->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Bagian: Tindakan Baru Diupdate : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($admin->staff_id > 0) {
                $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                $phone_no = $staff_phone->phone;
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
                'updated_at' => date('Y-m-d h:i:sa'),
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

        //send notif to sub departement terkait
        $admin_arr = User::where('subdapertement_id', $action->subdapertement_id)->where('staff_id', 0)
            ->get();
        foreach ($admin_arr as $key => $admin) {
            $id_onesignal = $admin->_id_onesignal;
            $message = 'Sub Bagian: Tindakan Baru Diupdate : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($admin->staff_id > 0) {
                $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                $phone_no = $staff_phone->phone;
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
                'updated_at' => date('Y-m-d h:i:sa'),
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

        //send notif to staff
        $actionstaffs = ActionStaff::where('action_id', '=', $action->id)->get();
        foreach ($actionstaffs as $key => $actionstaff) {
            $message = 'Staff: Tindakan Baru Diupdate : ' . $ticket->code . $request->description;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            $staff_phone = StaffApi::where('id', $actionstaff->staff_id)->first();
            $phone_no = $staff_phone->phone;
            $wa_data = [
                'phone' => $this->gantiFormat($phone_no),
                'customer_id' => null,
                'message' => $message,
                'template_id' => '',
                'status' => 'gagal',
                'ref_id' => $wa_code,
                'created_at' => date('Y-m-d h:i:sa'),
                'updated_at' => date('Y-m-d h:i:sa'),
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
        }

        return response()->json([
            'message' => 'Data Dapertement Edit Success',
            'data' => $action,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ActionApi $action)
    {
        try {
            $actionstaff = ActionStaff::where('action_id', '=', $action->id)->first();
            if ($actionstaff === null) {

                //unlink
                //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin", \base_path());
                $basepath  = base_path().'/public';
                $img = $action->image;
                $img = str_replace('"', '', $img);
                $img = str_replace('[', '', $img);
                $img = str_replace(']', '', $img);
                $img_arr = explode(",", $img);
                foreach ($img_arr as $img_name) {
                    $file_path = $basepath . $img_name;
                    if (trim($img_name) != '' && file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                $action->delete();

                return response()->json([
                    'message' => 'Data Berhasil Di Hapus',
                    'data' => $actionstaff,
                ]);
                // user doesn't exist
            } else {
                return response()->json([
                    'message' => 'Data Masih Terkait dengan data yang lain',
                    'data' => $actionstaff,
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Data Masih Terkait dengan data yang lain',
                'data' => $e,
            ]);
        }
    }

    public function actionStaffs($action_id)
    {

        try {
            $action = ActionApi::where('id', $action_id)->with('staff')->with('dapertement')->first();
            return response()->json([
                'message' => 'sucssess',
                'data' => $action,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'sucssess',
                'data' => $ex,
            ]);
        }

        // $staffs = $action->staff;

    }

    public function actionStaffLists($action_id)
    {
        try {
            $action = ActionApi::findOrFail($action_id);

            $action_staffs = ActionApi::where('id', $action_id)->with('staff')->first();

            // $staffs = StaffApi::selectRaw('staffs.id,staffs.code,staffs.name,staffs.phone, work_units.name as work_unit_name')
            //     ->join('dapertements', 'dapertements.id', '=', 'staffs.dapertement_id')
            //     ->leftJoin('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
            //     ->where('subdapertement_id', $action->subdapertement_id)
            //     ->orderBy('work_units.serial_number', 'ASC')
            //     ->get();

            // // $staffs = StaffApi::where('dapertement_id', $action->dapertement_id)->with('action')->get();

            // $action_staff_lists = DB::table('staffs')
            //     ->join('action_staff', function ($join) {
            //         $join->on('action_staff.staff_id', '=', 'staffs.id')
            //             ->where('action_staff.status', '!=', 'close');
            //     })
            //     ->get();

            $action_staff_lists = DB::table('staffs')
                ->join('action_staff', function ($join) {
                    $join->on('action_staff.staff_id', '=', 'staffs.id')
                        ->where('action_staff.status', '!=', 'close');
                })
                ->join('actions', 'actions.id', '=', 'action_staff.action_id')
                ->where('actions.id', $action_id)
                ->get();

            $staffs = StaffApi::selectRaw('
        staffs.id,staffs.code,
        staffs.name,
        staffs.phone,
        work_units.name as work_unit_name,
        SUM(CASE WHEN status != "close" THEN 1 ELSE 0 END) AS jumlahtindakan
        ')
                ->leftJoin('action_staff', 'staffs.id', '=', 'action_staff.staff_id')
                ->leftJoin('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
                ->where('action_staff.status', '!=', null)
                ->where('subdapertement_id', $action->subdapertement_id)
                ->orWhere('action_staff.status', '=', null)
                ->where('subdapertement_id', $action->subdapertement_id)
                ->groupBy('staffs.id')
                ->orderBy('work_units.serial_number', 'ASC')
                ->get();

            $data = [
                'action' => $action,
                'action_staffs' => $action_staffs,
                'staffs' => $staffs,
                'action_staff_lists' => $action_staff_lists,
            ];

            return response()->json([
                'message' => 'success',
                'data' => $data,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal ambil data',
                'data' => $ex,
            ]);
        }
    }

    public function actionStaffStore(Request $request)
    {

        try {
            $rules = array(
                'action_id' => 'required',
                'staff_id' => 'required',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->messages();
                $errors = $messages->all();
                return response()->json([
                    'message' => $errors,
                    'data' => $request->all(),
                ]);
            }

            $action = ActionApi::with('ticket')->find($request->action_id);
            $staff = StaffApi::find($request->staff_id);

            if ($action) {
                $cek = $action->staff()->attach($request->staff_id, ['status' => 'pending']);

                if ($cek) {
                    $action = Action::where('id', $request->action_id)->with('staff')->first();

                    // dd($action->staff[0]->pivot->status);
                    $cekAllStatus = false;
                    $statusAction = 'close';
                    for ($status = 0; $status < count($action->staff); $status++) {
                        // dd($action->staff[$status]->pivot->status);
                        if ($action->staff[$status]->pivot->status == 'pending') {
                            $statusAction = 'pending';
                            break;
                        } else if ($action->staff[$status]->pivot->status == 'active') {

                            $statusAction = 'active';
                        }
                    }

                    $dateNow = date('Y-m-d H:i:s');

                    $action->update([
                        'status' => $statusAction,
                        'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow,
                    ]);
                }

                //def subdap
                $dateNow = date('Y-m-d H:i:s');
                $subdapertement_def = Subdapertement::where('def', '1')->first();
                $dapertement_def_id = $subdapertement_def->dapertement_id;
                $subdapertement_def_id = $subdapertement_def->id;

                //send notif to admin
                $admin_arr = User::where('dapertement_id', 0)->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Admin: Petugas Baru Ditugaskan : ' . $action->ticket->code . $staff->name;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to humas
                $admin_arr = User::where('subdapertement_id', $subdapertement_def_id)
                    ->where('staff_id', 0)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Humas: Petugas Baru Ditugaskan : ' . $action->ticket->code . $staff->name;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to departement terkait
                $admin_arr = User::where('dapertement_id', $action->dapertement_id)
                    ->where('subdapertement_id', 0)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Bagian: Petugas Baru Ditugaskan : ' . $action->ticket->code . $staff->name;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to sub departement terkait
                $admin_arr = User::where('subdapertement_id', $action->subdapertement_id)->where('staff_id', 0)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Sub Bagian: Petugas Baru Ditugaskan : ' . $action->ticket->code . $staff->name;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to staff terkait
                $message = 'Staff: Petugas Baru Ditugaskan : ' . $action->ticket->code . $staff->name;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                $phone_no = $staff->phone;
                $wa_data = [
                    'phone' => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message' => $message,
                    'template_id' => '',
                    'status' => 'gagal',
                    'ref_id' => $wa_code,
                    'created_at' => date('Y-m-d h:i:sa'),
                    'updated_at' => date('Y-m-d h:i:sa'),
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

                return response()->json([
                    'message' => 'staff Berhasil di tambahkan ',
                    'data' => $action,
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal tambah staff ',
                'data' => $ex,
            ]);
        }
    }

    public function actionStaffUpdate(Request $request)
    {
        try {

            $rules = array(
                'action_id' => 'required',
                'staff_id' => 'required',
                'status' => 'required',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->messages();
                $errors = $messages->all();
                return response()->json([
                    'message' => $errors,
                    'data' => $request->all(),
                ]);
            }

            $action = ActionApi::where('id', $request->action_id)->with('ticket')->with('staff')->first();
            $staff = StaffApi::find($request->staff_id);
            $idStaff = $request->staff_id;
            if ($action) {
                $cek = $action->staff()->updateExistingPivot($request->staff_id, ['status' => $request->status]);
                //    $cek =  $action->staff()->sync([$idStaff => [ 'status' => $request->status] ], false);
            }

            if ($cek) {
                $action = ActionApi::where('id', $request->action_id)->with('ticket')->with('staff')->first();

                //     // dd($action->staff[0]->pivot->status);
                $cekAllStatus = false;
                $statusAction = 'close';
                for ($status = 0; $status < count($action->staff); $status++) {
                    // dd($action->staff[$status]->pivot->status);
                    if ($action->staff[$status]->pivot->status == 'pending') {
                        $statusAction = 'pending';
                        break;
                    } else if ($action->staff[$status]->pivot->status == 'active') {

                        $statusAction = 'active';
                    }
                }

                $dateNow = date('Y-m-d H:i:s');

                $action->update([
                    'status' => $statusAction,
                    'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow,
                ]);

                // update ticket
                $statusTicket = 'close';
                if ($action) {
                    $actionStatusAll = ActionApi::where('ticket_id', $action->ticket_id)->with('ticket')->get();

                    for ($i = 0; $i < count($actionStatusAll); $i++) {
                        if ($actionStatusAll[$i]->status == 'pending') {
                            $statusTicket = 'pending';
                            break;
                        } else if ($actionStatusAll[$i]->status == 'active') {
                            $statusTicket = 'active';
                        }
                    }

                    $ticket = TicketApi::findOrFail($action->ticket_id);

                    $ticket->update([
                        'status' => $statusTicket,
                    ]);
                    // $actionStatusAll->update([
                    //     'status' => $statusTicket,
                    // ]);

                    // dd($statusTicket);
                }

                //def subdap
                $dateNow = date('Y-m-d H:i:s');
                $subdapertement_def = Subdapertement::where('def', '1')->first();
                $dapertement_def_id = $subdapertement_def->dapertement_id;
                $subdapertement_def_id = $subdapertement_def->id;

                //send notif to admin
                $admin_arr = User::where('dapertement_id', 0)->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Admin: Petugas Baru Diupdate : ' . $action->ticket->code . $staff->name;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to humas
                $admin_arr = User::where('subdapertement_id', $subdapertement_def_id)
                    ->where('staff_id', 0)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Humas: Petugas Baru Diupdate : ' . $action->ticket->code . $staff->name;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to departement terkait
                $admin_arr = User::where('dapertement_id', $action->dapertement_id)
                    ->where('subdapertement_id', 0)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Bagian: Petugas Baru Diupdate : ' . $action->ticket->code . $staff->name;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to sub departement terkait
                $admin_arr = User::where('subdapertement_id', $action->subdapertement_id)->where('staff_id', 0)
                    ->get();
                foreach ($admin_arr as $key => $admin) {
                    $id_onesignal = $admin->_id_onesignal;
                    $message = 'Sub Bagian: Petugas Baru Diupdate : ' . $action->ticket->code . $staff->name;
                    //wa notif
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff_phone->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa'),
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

                //send notif to staff terkait
                $message = 'Staff: Petugas Baru Diupdate : ' . $action->ticket->code . $staff->name;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                $phone_no = $staff->phone;
                $wa_data = [
                    'phone' => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message' => $message,
                    'template_id' => '',
                    'status' => 'gagal',
                    'ref_id' => $wa_code,
                    'created_at' => date('Y-m-d h:i:sa'),
                    'updated_at' => date('Y-m-d h:i:sa'),
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

                return response()->json([
                    'message' => 'Status di ubah ',
                    'data' => $action,
                ]);
            } else {
                return $request->all();
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal tambah staff ',
                'data' => $ex,
            ]);
        }
    }

    public function actionStaffDestroy($action_id, $staff_id)
    {
        // abort_unless(\Gate::allows('action_staff_delete'), 403);

        try {
            $action = ActionApi::findOrFail($action_id);

            if ($action) {
                $cek = $action->staff()->detach($staff_id);

                if ($cek) {
                    $action = ActionApi::where('id', $action_id)->with('staff')->first();

                    // dd($action->staff[0]->pivot->status);
                    $cekAllStatus = false;
                    if (count($action->staff) > 0) {
                        $statusAction = 'close';
                    } else {
                        $statusAction = $action->status;
                    }
                    for ($status = 0; $status < count($action->staff); $status++) {
                        // dd($action->staff[$status]->pivot->status);
                        if ($action->staff[$status]->pivot->status == 'pending') {
                            $statusAction = 'pending';
                            break;
                        } else if ($action->staff[$status]->pivot->status == 'active') {

                            $statusAction = 'active';
                        }
                    }

                    $dateNow = date('Y-m-d H:i:s');

                    $action->update([
                        'status' => $statusAction,
                        'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $dateNow,
                    ]);

                    return response()->json([
                        'message' => 'Berhasil di hapus ',
                        'data' => $action,
                    ]);
                }
            }
        } catch (QueryException $th) {
            return response()->json([
                'message' => 'gagal tambah staff ',
                'data' => $ex,
            ]);
        }
    }

    public function locklist(Request $request)
    {
        //dapertement
        $department = 0;
        $subdepartment = 0;
        $if_department = false;
        $if_staff = false;
        if (isset($request->userid) && $request->userid != '') {
            $admin = User::with('roles')->find($request->userid);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if ($admin->subdapertement_id == 0) {
                $if_department = true;
            }
            if ($admin->staff_id > 0) {
                $if_staff = true;
            }
            if (!in_array("lock_all_access", $permission)) {
                $department = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
            } else {
                $department = '';
                $subdepartment = '';
            }
        }
        //status
        $status = '';
        if ($request->status != '') {
            $status = $request->status;
        }
        //keyword
        $searchfilter = '';
        if ($request->searchfilter != '') {
            $searchfilter = $request->searchfilter;
        }
        try {
            if ($if_department) {
                $lock = Lock::selectRaw("locks.*")
                    ->FilterStatus($status)
                    ->FilterKeyword($searchfilter)
                    ->join('subdapertements', 'subdapertements.id', '=', 'locks.subdapertement_id')
                    ->join('dapertements', 'dapertements.id', '=', 'subdapertements.dapertement_id')
                    ->FilterDepartment($department)
                    ->with('subdapertement')
                    ->with('lockaction')
                    ->with('customer')
                    ->orderBy('created_at', 'DESC')
                    ->paginate(10, ['*'], 'page', $request->page);
            } else {
                if ($if_staff) {
                    $lock = Lock::selectRaw("locks.*")
                        ->join('simpletabctm.tblpelanggan as tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'locks.customer_id')
                        ->join('simpletabctm.tblopp as tblopp', 'tblopp.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                        ->join('area_staff', 'area_staff.area_id', '=', 'tblpelanggan.idareal')
                        ->FilterStatus($status)
                        ->FilterKeyword($searchfilter)
                        ->where('area_staff.staff_id', $admin->staff_id)
                        ->with('subdapertement')
                        ->with('lockaction')
                        ->with('customer')
                        ->orderBy('tblpelanggan.idareal', 'asc')
                        ->orderBy('tblopp.operator', 'asc')
                        ->orderBy('tblpelanggan.idurut', 'asc')
                        ->paginate(10, ['*'], 'page', $request->page);
                } else {
                    $lock = Lock::selectRaw("locks.*")
                        ->FilterStatus($status)
                        ->FilterKeyword($searchfilter)
                        ->FilterSubDepartment($subdepartment)
                        ->with('subdapertement')
                        ->with('lockaction')
                        ->with('customer')
                        ->orderBy('created_at', 'DESC')
                        ->paginate(10, ['*'], 'page', $request->page);
                }
            }
            return response()->json([
                'message' => 'success',
                'data' => $lock,
                'search' => $request->search,
                'page' => $request->page,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function lockDestroy($lockaction_id)
    {

        try {
            $lock = Lock::find($lockaction_id);
            $lockaction = LockAction::where('lock_id', '=', $lock->id)->first();
            if ($lockaction === null) {
                $lock->delete();
                return response()->json([
                    'message' => 'Data Berhasil Di Hapus',
                    'data' => $lock,
                ]);
            } else {
                return response()->json([
                    'message' => 'Data Masih Terkait dengan data yang lain',
                    'data' => $lockaction,
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Data Masih Terkait dengan data yang lain',
                'data' => $e,
            ]);
        }
    }

    public function lockStaffs($lockaction_id)
    {

        try {

            $lock = Lock::where('id', $lockaction_id)->with('staff')->first();

            return response()->json([
                'message' => 'sucssess',
                'data' => $lock,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'sucssess',
                'data' => $ex,
            ]);
        }
    }

    public function lockStaffList($lockaction_id)
    {
        try {
            $action = Lock::findOrFail($lockaction_id);

            $action_staffs = Lock::where('id', $lockaction_id)->with('staff')->first();

            $staffs = StaffApi::where('subdapertement_id', $action->subdapertement_id)->get();

            // $staffs = StaffApi::where('dapertement_id', $action->dapertement_id)->with('action')->get();

            $action_staff_lists = DB::table('staffs')
                ->join('lock_staff', function ($join) {
                    $join->on('lock_staff.staff_id', '=', 'staffs.id');
                })
                ->get();

            $data = [
                'action' => $action,
                'action_staffs' => $action_staffs,
                'staffs' => $staffs,
                'action_staff_lists' => $action_staff_lists,
            ];

            return response()->json([
                'message' => 'success',
                'data' => $data,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal ambil data',
                'data' => $ex,
            ]);
        }
    }
    public function lockStaffStore(Request $request)
    {

        try {
            $rules = array(
                'lockaction_id' => 'required',
                'staff_id' => 'required',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->messages();
                $errors = $messages->all();
                return response()->json([
                    'message' => $errors,
                    'data' => $request->all(),
                ]);
            }

            $action = Lock::findOrFail($request->lockaction_id);
            $staff = StaffApi::find($request->staff_id);

            if ($action) {
                $cek = $action->staff()->attach($request->staff_id);
            }

            //send notif to admin
            $admin_arr = User::where('dapertement_id', 0)->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Admin: Petugas Segel Meter Baru Ditugaskan : ' . $action->code . $staff->name;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff_phone->phone;
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
                    'updated_at' => date('Y-m-d h:i:sa'),
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

            //send notif to departement terkait
            $subdapertement_obj = Subdapertement::where('id', $action->subdapertement_id)->first();
            $admin_arr = User::where('dapertement_id', $subdapertement_obj->dapertement_id)
                ->where('subdapertement_id', 0)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Bagian: Petugas Segel Meter Baru Ditugaskan : ' . $action->code . $staff->name;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff_phone->phone;
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
                    'updated_at' => date('Y-m-d h:i:sa'),
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

            //send notif to sub departement terkait
            $admin_arr = User::where('subdapertement_id', $action->subdapertement_id)->where('staff_id', 0)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Sub Bagian: Petugas Segel Meter Baru Ditugaskan : ' . $action->code . $staff->name;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff_phone->phone;
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
                    'updated_at' => date('Y-m-d h:i:sa'),
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

            //send notif to staff terkait
            $message = 'Staff: Petugas Segel Meter Baru Ditugaskan : ' . $action->code . $staff->name;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            $phone_no = $staff->phone;
            $wa_data = [
                'phone' => $this->gantiFormat($phone_no),
                'customer_id' => null,
                'message' => $message,
                'template_id' => '',
                'status' => 'gagal',
                'ref_id' => $wa_code,
                'created_at' => date('Y-m-d h:i:sa'),
                'updated_at' => date('Y-m-d h:i:sa'),
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

            return response()->json([
                'message' => 'staff Berhasil di tambahkan ',
                'data' => $action,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal tambah staff ',
                'data' => $ex,
            ]);
        }
    }

    public function actionlocklist(Request $request)
    {
        try {
            $actions = LockAction::with('subdapertement')
                ->with('lock')
                ->where('lock_id', $request->lockaction_id)
                ->get();
            return response()->json([
                'message' => 'Success',
                'data' => $actions,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'data' => $ex,
            ]);
        }
    }
    public function lockactionscreate(Request $request)
    {

        $img_path = "/pdf";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        $dataForm = json_decode($request->form);
        $responseImage = '';
        $dataQtyImage = json_decode($request->qtyImage);
        for ($i = 1; $i <= $dataQtyImage; $i++) {
            if ($request->file('image' . $i)) {
                $resourceImage = $request->file('image' . $i);
                $nameImage = time() + $i;
                $file_extImage = $request->file('image' . $i)->extension();
                $nameImage = str_replace(" ", "-", $nameImage);

                $img_name = $img_path . "/" . $nameImage . "." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);

                $dataImageName[] = $nameImage . "." . $file_extImage;
            } else {
                $responseImage = 'Image tidak di dukung';
                break;
            }
        }

        if ($responseImage != '') {
            return response()->json([
                'message' => $responseImage,
            ]);
        }
        //set data
        $data = array(
            'lock_id' => $dataForm->lock_id,
            'type' => $dataForm->type,
            'memo' => $dataForm->memo,
            'image' => str_replace("\/", "/", json_encode($dataImageName)),
        );

        try {
            $ticket = LockAction::create($data);
            //update status lock
            $lock = Lock::find($dataForm->lock_id);
            $lock->status = $dataForm->type;
            $lock->save();
            //send notif to admin
            $admin_arr = User::where('dapertement_id', 0)->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Admin: Tindakan Penyegelan/Pencabutan Baru Dibuat : ' . $dataForm->memo;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff_phone->phone;
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
                    'updated_at' => date('Y-m-d h:i:sa'),
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

            //send notif to departement terkait
            $lock_obj = Lock::where('id', $dataForm->lock_id)->first();
            $subdapertement_obj = Subdapertement::where('id', $lock_obj->subdapertement_id)->first();
            $admin_arr = User::where('dapertement_id', $subdapertement_obj->dapertement_id)
                ->where('subdapertement_id', 0)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Bagian: Tindakan Penyegelan/Pencabutan Baru Dibuat : ' . $dataForm->memo;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff_phone->phone;
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
                    'updated_at' => date('Y-m-d h:i:sa'),
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

            //send notif to sub departement terkait
            $admin_arr = User::where('subdapertement_id', $lock_obj->subdapertement_id)->where('staff_id', 0)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                $message = 'Sub Bagian: Tindakan Penyegelan/Pencabutan Baru Dibuat : ' . $dataForm->memo;
                //wa notif
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff_phone = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff_phone->phone;
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
                    'updated_at' => date('Y-m-d h:i:sa'),
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

            //send notif to staff terkait
            $message = 'Staff: Tindakan Penyegelan/Pencabutan Baru Dibuat : ' . $dataForm->memo;
            //wa notif
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            $staff_phone = StaffApi::where('id', $ticket->staff_id)->first();
            $phone_no = $staff_phone->phone;
            $wa_data = [
                'phone' => $this->gantiFormat($phone_no),
                'customer_id' => null,
                'message' => $message,
                'template_id' => '',
                'status' => 'gagal',
                'ref_id' => $wa_code,
                'created_at' => date('Y-m-d h:i:sa'),
                'updated_at' => date('Y-m-d h:i:sa'),
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

            return response()->json([
                'message' => 'Success',
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => $ex,
            ]);
        }
    }

    public function lockStaffDestroy($lockaction_id, $staff_id)
    {
        try {
            $action = Lock::findOrFail($lockaction_id);

            if ($action) {
                $cek = $action->staff()->detach($staff_id);

                if ($cek) {
                    $action = Lock::where('id', $lockaction_id)->with('staff')->first();

                    $cekAllStatus = false;

                    $dateNow = date('Y-m-d H:i:s');

                    $action->update();
                }
            }
            return response()->json([
                'message' => 'Success',
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => $ex,
            ]);
        }
    }

    public function lockactionsdestroy($lockaction_id)
    {
        try {
            $lock = LockAction::find($lockaction_id);
            $lock->delete();
            return response()->json([
                'message' => 'Data Berhasil Di Hapus',
                'data' => $lock,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Data Masih Terkait dengan data yang lain',
                'data' => $e,
            ]);
        }
    }

    public function lockshow($lock_id)
    {
        try {

            $lock_obj = Lock::where('id', $lock_id)->first();
            $customer = Customer::where('nomorrekening', $lock_obj->customer_id)
                ->first();
            $customer->year = date('Y');
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
                    ->where('tblpembayaran.nomorrekening', $lock_obj->customer_id)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<=', date('Y-n-01'))
                    ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                    ->get();
                // $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                //     ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                //     ->where('tblpembayaran.nomorrekening', $lock_obj->customer_id)
                //     ->where('tblpembayaran.tahunrekening', date('Y'))
                //     ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                //     ->where('tblpembayaran.bulanrekening', '<', $month_next)
                //     ->get();
            } else {
                $ctm_lock_old = 1;
                $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                    ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                    ->where('tblpembayaran.nomorrekening', $lock_obj->customer_id)
                    ->whereDate(DB::raw('concat(tblpembayaran.tahunrekening,"-",tblpembayaran.bulanrekening,"-01")'), '<', date('Y-n-01'))
                    ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                    ->get();
                // $ctm = CtmPembayaran::selectRaw("tblpembayaran.*,tblpelanggan.*")
                //     ->join('tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                //     ->where('tblpembayaran.nomorrekening', $lock_obj->customer_id)
                //     ->where('tblpembayaran.tahunrekening', date('Y'))
                //     ->where('tblpembayaran.bulanrekening', '<', $month_next)
                //     ->orderBy('tblpembayaran.bulanrekening', 'ASC')
                //     ->get();
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

            return response()->json([
                'data' => $customer, $dataPembayaran, $recap,
                'message' => 'Success',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Data Masih Terkait dengan data yang lain',
                'data' => $e,
            ]);
        }
    }

    public function typeshow($lockaction_id)
    {

        try {
            $lock = Lock::with('lockaction')->find($lockaction_id);
            return response()->json([
                'message' => 'Success',
                'data' => $lock,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'data' => $ex,
            ]);
        }
    }

    public function segellist(Request $request)
    {
        $group_unit = 0;
        $teruskan = 1;
        if (isset($request->userid) && $request->userid != '') {
            $admin = User::with('roles')->find($request->userid);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("lock_all_access", $permission)) {
                $department_id = $admin->dapertement_id;
                $department = Dapertement::find($department_id);
                $group_unit = $department->group_unit;
                if (($group_unit == 1 && $department_id != 1) || $admin->subdapertement_id > 0) {
                    $teruskan = 0;
                }
            }
        }

        $date_now = date('Y-m-d');
        $date_comp = date('Y-m') . '-20';
        $month_next = date('n', strtotime('+1 month'));
        $last_4_month = date("Y-n-d", strtotime('-4 month', strtotime(date('Y-m-01'))));

        try {
            if ($date_now >= $date_comp) {

                if (isset($request->status)) {
                    if ($request->status == 1) {
                        if ($group_unit > 0) {
                            $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak,'{$teruskan}' as teruskan")
                                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                                ->where('tblwilayah.group_unit', $group_unit)
                                ->where('tblpembayaran.tahunrekening', date('Y'))
                                ->where('tblpembayaran.bulanrekening', '<', $month_next)
                                ->havingRaw('((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2 >?', [$request->status])
                                ->groupBy('tblpembayaran.nomorrekening')
                                ->paginate(10, ['*'], 'page', $request->page);
                        } else {
                            $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                                ->where('tblpembayaran.tahunrekening', date('Y'))
                                ->where('tblpembayaran.bulanrekening', '<', $month_next)
                                ->havingRaw('((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2 >?', [$request->status])
                                ->groupBy('tblpembayaran.nomorrekening')
                                ->paginate(10, ['*'], 'page', $request->page);
                        }
                    } else if ($request->status == 0) {
                        if ($group_unit > 0) {
                            $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                                ->where('tblwilayah.group_unit', $group_unit)
                                ->where('tblpembayaran.tahunrekening', date('Y'))
                                ->where('tblpembayaran.bulanrekening', '<', $month_next)
                                ->havingRaw('((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2 <=?', [1])
                                ->groupBy('tblpembayaran.nomorrekening')
                                ->paginate(10, ['*'], 'page', $request->page);
                        } else {
                            $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                                ->where('tblpembayaran.tahunrekening', date('Y'))
                                ->where('tblpembayaran.bulanrekening', '<', $month_next)
                                ->havingRaw('((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2 <=?', [1])
                                ->groupBy('tblpembayaran.nomorrekening')
                                ->paginate(10, ['*'], 'page', $request->page);
                        }
                    }
                } else {
                    if ($group_unit > 0) {
                        $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as statusnunggak,'{$teruskan}' as teruskan")
                            ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                            ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                            ->where('tblwilayah.group_unit', $group_unit)
                            ->where('tblpembayaran.tahunrekening', date('Y'))
                            ->where('tblpembayaran.bulanrekening', '<', $month_next)
                            ->groupBy('tblpembayaran.nomorrekening')
                            ->paginate(10, ['*'], 'page', $request->page);
                    } else {
                        $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                            ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                            ->where('tblpembayaran.tahunrekening', date('Y'))
                            ->where('tblpembayaran.bulanrekening', '<', $month_next)
                            ->groupBy('tblpembayaran.nomorrekening')
                            ->paginate(10, ['*'], 'page', $request->page);
                    }
                }
            } else {

                if (isset($request->status)) {
                    if ($request->status == 1) {
                        if ($group_unit > 0) {
                            $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                                ->where('tblwilayah.group_unit', $group_unit)
                                ->where('tblpembayaran.tahunrekening', date('Y'))
                                ->where('tblpembayaran.bulanrekening', '<', $month_next)
                                ->havingRaw('((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2 >?', [$request->status])
                                ->groupBy('tblpembayaran.nomorrekening')
                                ->paginate(10, ['*'], 'page', $request->page);
                        } else {
                            $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                                ->where('tblpembayaran.tahunrekening', date('Y'))
                                ->where('tblpembayaran.bulanrekening', '<', $month_next)
                                ->havingRaw('((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2 >?', [$request->status])
                                ->groupBy('tblpembayaran.nomorrekening')
                                ->paginate(10, ['*'], 'page', $request->page);
                        }
                    } else if ($request->status == 0) {
                        if ($group_unit > 0) {
                            $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                                ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                                ->where('tblwilayah.group_unit', $group_unit)
                                ->where('tblpembayaran.tahunrekening', date('Y'))
                                ->where('tblpembayaran.bulanrekening', '<', $month_next)
                                ->havingRaw('((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2 <=?', [1])
                                ->groupBy('tblpembayaran.nomorrekening')
                                ->paginate(10, ['*'], 'page', $request->page);
                        } else {
                            $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                                ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                                ->where('tblpembayaran.tahunrekening', date('Y'))
                                ->where('tblpembayaran.bulanrekening', '<', $month_next)
                                ->havingRaw('((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2 <=?', [1])
                                ->groupBy('tblpembayaran.nomorrekening')
                                ->paginate(10, ['*'], 'page', $request->page);
                        }
                    }
                } else {
                    if ($group_unit > 0) {
                        $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                            ->join('tblwilayah', 'tblpelanggan.idareal', '=', 'tblwilayah.id')
                            ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                            ->where('tblwilayah.group_unit', $group_unit)
                            ->where('tblpembayaran.tahunrekening', date('Y'))
                            ->where('tblpembayaran.bulanrekening', '<', $month_next)
                            ->groupBy('tblpembayaran.nomorrekening')
                            ->paginate(10, ['*'], 'page', $request->page);
                    } else {
                        $qry = Customer::selectRaw("tblpelanggan.*, (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) as jumlahtunggakan,  (case when( (((count(tblpembayaran.statuslunas) * 2) - sum(tblpembayaran.statuslunas)) DIV 2) > 1 ) THEN 1 ELSE 0 END) as  statusnunggak,'{$teruskan}' as teruskan")
                            ->join('tblpembayaran', 'tblpelanggan.nomorrekening', '=', 'tblpembayaran.nomorrekening')
                            ->where('tblpembayaran.tahunrekening', date('Y'))
                            ->where('tblpembayaran.bulanrekening', '<', $month_next)
                            ->groupBy('tblpembayaran.nomorrekening')
                            ->paginate(10, ['*'], 'page', $request->page);
                    }
                }
            }
            return response()->json([
                'message' => 'success',
                'data' => $qry,
                'search' => $request->search,
                'page' => $request->page,
                'userid' => $request->userid,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function lockStore(Request $request)
    {
        //code gnr
        $subdapertement_id = 10;
        $arr['subdapertement_id'] = $subdapertement_id;
        $arr['month'] = date("m");
        $arr['year'] = date("Y");
        $last_scb = $this->get_last_code('scb-lock', $arr);
        $scb = acc_code_generate($last_scb, 16, 12, 'Y');

        $data = array(
            'code' => $scb,
            'customer_id' => $request->customer_id,
            'subdapertement_id' => $request->subdapertement_id,
            'description' => $request->description,
        );

        try {
            $lock = Lock::create($data);

            return response()->json([
                'message' => 'success',
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }
    public function scb(Request $request)
    {
        $subdapertement_id = 10;
        $arr['subdapertement_id'] = $subdapertement_id;
        $arr['month'] = date("m");
        $arr['year'] = date("Y");
        $last_scb = $this->get_last_code('scb-lock', $arr);
        $scb = acc_code_generate($last_scb, 16, 12, 'Y');
        try {
            return response()->json([
                'message' => 'success',
                'data' => $scb,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function SubDapertementlist(Request $request)
    {
        $department_id = 2;
        if (isset($request->userid) && $request->userid != '') {
            $admin = User::with('roles')->find($request->userid);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("lock_all_access", $permission)) {
                $department = Dapertement::find($admin->dapertement_id);
                $group_unit = $department->group_unit;
                if ($group_unit > 1) {
                    $department_id = $admin->dapertement_id;
                }
            }
        }

        $subdapertements = Subdapertement::where('dapertement_id', $department_id)->get();
        $dapertements = Dapertement::where('id', $department_id)->get();
        try {
            return response()->json([
                'message' => 'success',
                'data' => $dapertements, $subdapertements,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function alarmLocks()
    {
        //pending
        $pending = Lock::selectRaw('locks.id,locks.created_at,DATEDIFF(locks.created_at,current_date) AS day_diff,staffs.name')
            ->join('lock_staff', 'lock_staff.lock_id', '=', 'locks.id')
            ->join('staffs', 'lock_staff.staff_id', '=', 'staffs.id')
            ->where('locks.status', 'pending')
            ->whereRaw('DATEDIFF(locks.created_at,current_date) < 0')
            ->groupBy('lock_staff.staff_id')
            ->get();
        //lock_resist
        $pending = Lock::selectRaw('locks.id,locks.updated_at,DATEDIFF(locks.updated_at,current_date) AS day_diff,staffs.name')
            ->join('lock_staff', 'lock_staff.lock_id', '=', 'locks.id')
            ->join('staffs', 'lock_staff.staff_id', '=', 'staffs.id')
            ->where('locks.status', 'lock_resist')
            ->whereRaw('DATEDIFF(locks.updated_at,current_date) < -8')
            ->groupBy('lock_staff.staff_id')
            ->get();
        //lock
        $pending = Lock::selectRaw('locks.id,locks.updated_at,DATEDIFF(locks.updated_at,current_date) AS day_diff,staffs.name')
            ->join('lock_staff', 'lock_staff.lock_id', '=', 'locks.id')
            ->join('staffs', 'lock_staff.staff_id', '=', 'staffs.id')
            ->where('locks.status', 'lock')
            ->whereRaw('DATEDIFF(locks.updated_at,current_date) < -8')
            ->groupBy('lock_staff.staff_id')
            ->get();
        //unplug_resist
        $pending = Lock::selectRaw('locks.id,locks.updated_at,DATEDIFF(locks.updated_at,current_date) AS day_diff,staffs.name')
            ->join('lock_staff', 'lock_staff.lock_id', '=', 'locks.id')
            ->join('staffs', 'lock_staff.staff_id', '=', 'staffs.id')
            ->where('locks.status', 'unplug_resist')
            ->whereRaw('DATEDIFF(locks.updated_at,current_date) < 0')
            ->groupBy('lock_staff.staff_id')
            ->get();
        //unplug
        $pending = Lock::selectRaw('locks.id,locks.updated_at,DATEDIFF(locks.updated_at,current_date) AS day_diff,staffs.name')
            ->join('lock_staff', 'lock_staff.lock_id', '=', 'locks.id')
            ->join('staffs', 'lock_staff.staff_id', '=', 'staffs.id')
            ->where('locks.status', 'unplug')
            ->whereRaw('DATEDIFF(locks.updated_at,current_date) < 0')
            ->groupBy('lock_staff.staff_id')
            ->get();
        try {
            return response()->json([
                'message' => 'success',
                'pending' => $pending,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
            ]);
        }
    }

    public function detail(Request $request)
    {
        try {

            $actions = ActionApi::with('staff')
                ->with('dapertement')
                ->with('ticket')
                ->where('id', $request->id)
                ->orderBy('start', 'desc')
                ->first();

            return response()->json([
                'message' => 'Data Ticket',
                'data' => $actions,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'data' => $ex,
            ]);
        }
    }
}
