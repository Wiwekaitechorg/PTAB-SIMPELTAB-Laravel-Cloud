<?php

namespace App\Http\Controllers\Api\V1\WaterMeter;

use App\actionWms;
use App\actionWmStaff;
use App\CtmStatussmPelanggan;
use App\CtmWilayah;
use App\Customer;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\proposalWms;
use App\Staff;
use App\StaffApi;
use App\Subdapertement;
use App\Traits\TraitModel;
use App\User;
use App\wa_history;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\WablasTrait;

class actionWmApiController extends Controller
{
    use TraitModel;
    use WablasTrait;
    // index untuk data water meter
    public function index($id, Request $request)
    {
        $proposal_wms = [];
        $user = User::with('roles')->where('id', $id)->first();

        foreach ($user->roles as $data) {
            $roles[] = $data->id;
        }
        if (in_array('7', $roles)) {
            $roles = "1";
            if (date('d') < 21) {
                $proposal_wms = actionWmStaff::selectRaw('proposal_wms.status_wm,
            proposal_wms.code,
            proposal_wms.memo,
            proposal_wms.queue,
            proposal_wms.id as proposal_wm_id,
        proposal_wms.priority,
        proposal_wms.status,
        action_wms.category,
        action_wms.id as id,
        tblpelanggan.namapelanggan,
        tblpelanggan.nomorrekening,
        tblpelanggan.alamat,
        tblpelanggan.idareal,
        staffs.name as staff_name
        ')
                    ->join('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                    ->join('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                    ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                    ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                    ->join('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
                    ->groupBy('proposal_wms.id')
                    ->where('action_wm_staff.staff_id', $user->staff_id)
                    // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('-1 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterAreas($request->areas)
                    ->FilterKeyword($request->search)
                    ->FilterDate(request()->input('from'), request()->input('to'));
            } else {
                $proposal_wms = actionWmStaff::selectRaw('proposal_wms.status_wm,
            proposal_wms.code,
            proposal_wms.memo,
            proposal_wms.queue,
            proposal_wms.id as proposal_wm_id,
        proposal_wms.priority,
        proposal_wms.status,
        action_wms.category,
        action_wms.id as id,
        tblpelanggan.namapelanggan,
        tblpelanggan.nomorrekening,
        tblpelanggan.alamat,
        tblpelanggan.idareal,
        staffs.name as staff_name
        ')
                    ->join('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                    ->join('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                    ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                    ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                    ->join('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
                    ->groupBy('proposal_wms.id')
                    ->where('action_wm_staff.staff_id', $user->staff_id)
                    // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterAreas($request->areas)
                    ->FilterKeyword($request->search)
                    ->FilterDate(request()->input('from'), request()->input('to'));
            }
        }


        // dd(in_array('18', $roles));
        // dd();


        else if (in_array('16', $roles)) {
            $roles = "2";
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', $user->dapertement_id)->first()->group_unit;
            if (date('d') < 21) {
                $proposal_wms = actionWmStaff::selectRaw('proposal_wms.status_wm,
            proposal_wms.code,
        proposal_wms.priority,
        proposal_wms.queue,
        proposal_wms.status,
        proposal_wms.memo,
           proposal_wms.id as proposal_wm_id,
        action_wms.id as id,
        action_wms.category,
        tblpelanggan.namapelanggan,
        tblpelanggan.nomorrekening,
        tblpelanggan.alamat,
        tblpelanggan.idareal,
        staffs.id as staff_id,
        staffs.name as staff_name
        ')
                    ->rightJoin('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                    ->rightjoin('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                    ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                    ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                    ->leftJoin('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
                    ->groupBy('proposal_wms.id')
                    ->where('tblwilayah.group_unit', $group_unit)
                    ->where('proposal_wms.status', '!=', 'pending')
                    ->where('proposal_wms.status', '!=', 'reject')
                    // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('-1 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterAreas($request->areas)
                    ->FilterKeyword($request->search)
                    ->FilterDate(request()->input('from'), request()->input('to'));
            } else {
                $proposal_wms = actionWmStaff::selectRaw('proposal_wms.status_wm,
                    proposal_wms.code,
                proposal_wms.priority,
                proposal_wms.queue,
                proposal_wms.status,
                proposal_wms.memo,
                   proposal_wms.id as proposal_wm_id,
                action_wms.id as id,
                action_wms.category,
                tblpelanggan.namapelanggan,
                tblpelanggan.nomorrekening,
                tblpelanggan.alamat,
                tblpelanggan.idareal,
                staffs.id as staff_id,
                staffs.name as staff_name
                ')
                    ->rightJoin('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                    ->rightjoin('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                    ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                    ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                    ->leftJoin('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
                    ->groupBy('proposal_wms.id')
                    ->where('tblwilayah.group_unit', $group_unit)
                    ->where('proposal_wms.status', '!=', 'pending')
                    ->where('proposal_wms.status', '!=', 'reject')
                    // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterAreas($request->areas)
                    ->FilterKeyword($request->search)
                    ->FilterDate(request()->input('from'), request()->input('to'));
            }
        } else {


            $roles = "3";
            $group_unit = Dapertement::select('dapertements.group_unit')
                ->where('dapertements.id', $user->dapertement_id)->first()->group_unit;
            if (date('d') < 21) {
                $proposal_wms = actionWmStaff::selectRaw('proposal_wms.status_wm,
            proposal_wms.code,
        proposal_wms.priority,
        proposal_wms.status,
        proposal_wms.queue,
        proposal_wms.memo,
           proposal_wms.id as proposal_wm_id,
        action_wms.id as id,
        action_wms.category,
        tblpelanggan.namapelanggan,
        tblpelanggan.nomorrekening,
        tblpelanggan.alamat,
        tblpelanggan.idareal,
        staffs.id as staff_id,
        staffs.name as staff_name
        ')
                    ->rightJoin('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                    ->rightjoin('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                    ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                    ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                    ->leftJoin('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
                    ->groupBy('proposal_wms.id')
                    ->where('tblwilayah.group_unit', $group_unit)
                    // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('-1 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('0 month', strtotime(date('Y-m-d'))))])
                    ->FilterPriority($request->priority)
                    ->FilterStatus($request->status)
                    ->FilterStatusWM($request->statussm)
                    ->FilterAreas($request->areas)
                    ->FilterKeyword($request->search)
                    ->FilterDate(request()->input('from'), request()->input('to'));
            } else { {
                    $proposal_wms = actionWmStaff::selectRaw('proposal_wms.status_wm,
        proposal_wms.code,
    proposal_wms.priority,
    proposal_wms.status,
    proposal_wms.queue,
    proposal_wms.memo,
       proposal_wms.id as proposal_wm_id,
    action_wms.id as id,
    action_wms.category,
    tblpelanggan.namapelanggan,
    tblpelanggan.nomorrekening,
    tblpelanggan.alamat,
    tblpelanggan.idareal,
    staffs.id as staff_id,
    staffs.name as staff_name
    ')
                        ->rightJoin('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                        ->rightjoin('proposal_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                        ->join('simpletabctm.tblpelanggan', 'tblpelanggan.nomorrekening', '=', 'proposal_wms.customer_id')
                        ->join('simpletabctm.tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                        ->leftJoin('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
                        ->groupBy('proposal_wms.id')
                        ->where('tblwilayah.group_unit', $group_unit)
                        // ->whereBetween('proposal_wms.created_at', [date('Y-m-21', strtotime('0 month', strtotime(date('Y-m-d')))), date('Y-m-20', strtotime('+1 month', strtotime(date('Y-m-d'))))])
                        ->FilterPriority($request->priority)
                        ->FilterStatus($request->status)
                        ->FilterStatusWM($request->statussm)
                        ->FilterAreas($request->areas)
                        ->FilterKeyword($request->search)
                        ->FilterDate(request()->input('from'), request()->input('to'));
                }
            }
        }

        if ($request->order != "" && $request->orderType != "") {
            $proposal_wms =  $proposal_wms->orderBy($request->order, $request->orderType)
                ->paginate(10, ['*'], 'page', $request->page);
        } else {
            $proposal_wms =  $proposal_wms->orderBy('proposal_wms.updated_at', 'desc')->paginate(10, ['*'], 'page', $request->page);
        }
        return response()->json([
            'message' => 'success',
            'data' => $proposal_wms,
            'data1' => $roles,
        ]);
    }


    // *untuk edit action wm(belum dipakai);
    public function actionwmEdit(Request $request)
    {
        $dataForm = json_decode($request->form);
        $actionWm = actionWms::where('proposal_wm_id', $dataForm->id)->first();

        return response()->json([
            'message' => 'success',
            'data' => $actionWm,

        ]);
    }

    // *untuk filterArea
    public function area($id)
    {
        $data = [];
        $n = 2;
        $data[] = ['id' => 1, 'code' => 'semua', 'value' => '', 'checked' => true];
        $user = User::with('roles')->where('id', $id)->first();
        $group_unit = Dapertement::select('dapertements.group_unit')
            ->where('dapertements.id', $user->dapertement_id)->first()->group_unit;
        if ($user->roles[count($user->roles) - 1]->id === 7) {
            $areas = CtmWilayah::select('id as code', 'NamaWilayah')->get();
        } else {
            $areas = CtmWilayah::select('id as code', 'NamaWilayah')->where('group_unit', $group_unit)->get();
        }


        foreach ($areas as $d) {
            $data[] = ['id' => $n, 'code' => $d->code, 'value' => $d->code, 'checked' => false];
            $n++;
        }
        return response()->json([
            'message' => 'success',
            'data' => $data,
        ]);
    }

    // *untuk Bukti Sebelum Pengerjaan
    public function actionWmNewImageUpdate(Request $request)
    {
        $dataForm = json_decode($request->form);

        if ($dataForm->noWM1 != "" && $dataForm->brandWM1 != "" && $dataForm->standWM1 != "" && $dataForm->noWM2 != "" && $dataForm->brandWM2 != "" && $dataForm->standWM2 != "" && $request->qtyImageTool != "" && $request->qtyImagePreWork != "") {
            $img_path = "/images/WaterMeter";
            //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            $basepath  = base_path().'/public';
            // $dataForm = json_decode($request->form);
            $responseImage = '';
            $code = date('y') . date('d');
            $dataQtyImagePreWork = json_decode($request->qtyImagePreWork);
            for ($i = 1; $i <= $dataQtyImagePreWork; $i++) {
                if ($request->file('imagePreWork' . $i)) {
                    $resourceImage = $request->file('imagePreWork' . $i);
                    $nameImage = $request->file('imagePreWork' . $i)->getClientOriginalName();
                    $file_extImage = $request->file('imagePreWork' . $i)->extension();
                    $nameImage = str_replace(" ", "-", $nameImage);

                    $img_name = $img_path . "/" . $nameImage . "-" . $i . "." . $file_extImage;
                    $folder_upload = 'images/WaterMeter';
                    $resourceImage->move($folder_upload, $img_name);

                    $dataImageNamePreWork[] = $img_name;
                } else {
                    $responseImage = 'Image tidak di dukung';
                    break;
                }
            }

            $dataQtyImageTool = json_decode($request->qtyImageTool);
            for ($i = 1; $i <= $dataQtyImageTool; $i++) {
                if ($request->file('imageTool' . $i)) {
                    $resourceImage = $request->file('imageTool' . $i);
                    $nameImage = $request->file('imageTool' . $i)->getClientOriginalName();
                    $file_extImage = $request->file('imageTool' . $i)->extension();
                    $nameImage = str_replace(" ", "-", $nameImage);

                    $img_name = $img_path . "/" . $nameImage . "-" . $i . "." . $file_extImage;
                    $folder_upload = 'images/WaterMeter';
                    $resourceImage->move($folder_upload, $img_name);

                    $dataImageNameTool[] = $img_name;
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

            try {

                // $ticket = LockAction::create($data);
                // if ($ticket) {

                $actionWm = actionWms::where('id', $dataForm->id)->first();
                $actionWm->old_image = str_replace("\/", "/", json_encode($dataImageNamePreWork));
                $actionWm->new_image = str_replace("\/", "/", json_encode($dataImageNameTool));
                $actionWm->noWM1 = $dataForm->noWM1;
                $actionWm->brandWM1 = $dataForm->brandWM1;
                $actionWm->standWM1 = $dataForm->standWM1;
                $actionWm->noWM2 = $dataForm->noWM2;
                $actionWm->brandWM2 = $dataForm->brandWM2;
                $actionWm->standWM2 = $dataForm->standWM2;
                $actionWm->lat = $dataForm->lat;
                $actionWm->lng = $dataForm->lng;
                $actionWm->status = 'active';
                $actionWm->update();

                $proposal_wms = proposalWms::where('id', $actionWm->proposal_wm_id)->first();
                $proposalWm = $proposal_wms;
                $customer_id =  $proposal_wms->customer_id;
                $proposal_wms->where('id', $proposal_wms->proposal_wm_id);
                $proposal_wms->status = 'work';
                $proposal_wms->update();

                $group_unit = Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                    ->where('nomorrekening', $customer_id)->first()->group_unit;
                // Wa Blast

                $admin_arr = User::selectRaw('users.*, dapertements.*')
                    ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
                    ->join('role_user', 'users.id', '=', 'role_user.user_id')
                    ->where('group_unit', $group_unit)->where('role_user.role_id', 17)
                    ->orWhere('role_user.role_id', 14)->where('group_unit', $group_unit)
                    ->orWhere('group_unit', $group_unit)->where('role_user.role_id', 18)
                    ->orWhere('role_user.role_id', 15)->where('group_unit', $group_unit)
                    ->orWhere('group_unit', $group_unit)->where('role_user.role_id', 16)

                    // ->orwhere('dapertement_id', '2')->where('subdapertement_id', '0')->where('staff_id', '0')->where('group_unit', $group_unit)
                    ->get();
                // dd($admin_arr);
                foreach ($admin_arr as $key => $admin) {
                    $message = 'Test: ' . $admin->id . ' sedang dikerjakan';
                    //wa notif                
                    $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                    $wa_data_group = [];
                    //get phone user
                    if ($admin->staff_id > 0) {
                        $staff = StaffApi::where('id', $admin->staff_id)->first();
                        $phone_no = $staff->phone;
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
                        'updated_at' => date('Y-m-d h:i:sa')
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



                // send notif to end

                return response()->json([
                    'message' => 'Data Water Meter Terkirim',
                    'data' => $proposal_wms
                ]);
            } catch (QueryException $ex) {
                return response()->json([
                    'message' => 'gagal',
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Mohon lengkapi data terlebih dahulu',
            ]);
        }
    }

    // *untuk Bukti Foto Selesai
    public function actionWmdoneImageUpdate(Request $request)
    {
        $img_path = "/images/WaterMeter";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        $dataForm = json_decode($request->form);
        $responseImage = '';
        $code = date('y') . date('d');
        $dataQtyImageDone = json_decode($request->qtyImageDone);
        for ($i = 1; $i <= $dataQtyImageDone; $i++) {
            if ($request->file('imageDone' . $i)) {
                $resourceImage = $request->file('imageDone' . $i);
                $nameImage = $request->file('imageDone' . $i)->getClientOriginalName();
                $file_extImage = $request->file('imageDone' . $i)->extension();
                $nameImage = str_replace(" ", "-", $nameImage);

                $img_name = $img_path . "/" . $nameImage . "-" . $i . "." . $file_extImage;
                $folder_upload = 'images/WaterMeter';
                $resourceImage->move($folder_upload, $img_name);

                $dataImageNameDone[] = $img_name;
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

        try {

            // $ticket = LockAction::create($data);
            // if ($ticket) {

            $actionWm = actionWms::where('id', $dataForm->id)->first();
            $actionWm->image_done = str_replace("\/", "/", json_encode($dataImageNameDone));
            $actionWm->memo = $dataForm->memo;
            $actionWm->lat = $dataForm->lat;
            $actionWm->lng = $dataForm->lng;
            $actionWm->status = 'close';
            $actionWm->update();


            // membuat nomor surat start
            $proposal = proposalWms::join('action_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
                ->join('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
                ->join('dapertements', 'subdapertements.dapertement_id', '=', 'dapertements.id')
                ->where('proposal_wms.id', $actionWm->proposal_wm_id)->first();
            $gU = $proposal->group_unit;
            if ($gU == "1") {
                $s = "BAP";
                // $n = 14;
            } else if ($gU == "2") {
                $s = "BAPUK";
                // $n = 15;
            } else if ($gU == "4") {
                $s = "BAPUP";
                // $n = 15;
            } else if ($gU == "5") {
                $s = "BAPUB";
                // $n = 15;
            } else if ($gU == "3") {
                $s = "BAPUS";
                // $n = 15;
            } else {
                $s = "";
                // $n = 15;
            }


            $last_code = $this->get_last_codeC('proposal_wm', $gU);
            // membuat nomor surat end

            $proposal_wms = proposalWms::where('id', $actionWm->proposal_wm_id)->first();
            $proposalWm = $proposal_wms;
            $proposal_wms->close_queue = $last_code;
            $proposal_wms->code = '/' . $s . '/' . date('n') . '/' . date('Y');
            $customer_id =  $proposal_wms->customer_id;
            $proposal_wms->where('id', $proposal_wms->proposal_wm_id);
            // $proposal_wms->status = $dataForm->status;
            $proposal_wms->status = $dataForm->status;
            $proposal_wms->update();



            $group_unit = Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                ->where('nomorrekening', $customer_id)->first()->group_unit;
            // Wa Blast

            $admin_arr = User::selectRaw('users.*, dapertements.*')
                ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
                ->join('role_user', 'users.id', '=', 'role_user.user_id')
                ->where('group_unit', $group_unit)->where('role_user.role_id', 17)
                ->orWhere('role_user.role_id', 14)->where('group_unit', $group_unit)
                ->orWhere('group_unit', $group_unit)->where('role_user.role_id', 18)
                ->orWhere('role_user.role_id', 15)->where('group_unit', $group_unit)
                ->orWhere('group_unit', $group_unit)->where('role_user.role_id', 16)

                // ->orwhere('dapertement_id', '2')->where('subdapertement_id', '0')->where('staff_id', '0')->where('group_unit', $group_unit)
                ->get();
            // dd($admin_arr);
            foreach ($admin_arr as $key => $admin) {
                $message = 'Test: ' . $admin->id . ' Selesai';
                //wa notif                
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff->phone;
                } else {
                    $phone_no = $admin->phone;
                }

                // if ($admin->staff_id > 0) {
                //     $staff = StaffApi::where('id', $admin->staff_id)->first();
                //     $phone_no = "6281236815960";
                // } else {
                //     $phone_no = "6281236815960";
                // }

                $wa_data = [
                    'phone' => $this->gantiFormat($phone_no),
                    'customer_id' => null,
                    'message' => $message,
                    'template_id' => '',
                    'status' => 'gagal',
                    'ref_id' => $wa_code,
                    'created_at' => date('Y-m-d h:i:sa'),
                    'updated_at' => date('Y-m-d h:i:sa')
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


            // send notif to end

            return response()->json([
                'message' => "Pergantian Water Meter Selesai",
                'data' => $proposal_wms
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // *untuk Bukti show
    public function show($id)
    {
        try {
            $actionWm = actionWms::selectRaw('action_wms.id, action_wms.code,
            proposal_wms.status_wm,
            action_wms.proposal_wm_id,
            action_wms.subdapertement_id,
            proposal_wms.status,
            proposal_wms.priority,
            tblpelanggan.namapelanggan,
            tblpelanggan.nomorrekening,
            tblpelanggan.alamat,
            tblpelanggan.idgol,
            tblpelanggan.telp,
            map_koordinatpelanggan.lat,
            map_koordinatpelanggan.lng,
            tblpelanggan.idareal,
            subdapertements.name
            ')
                ->with('staff')->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
                ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->leftJoin('simpletabctm.map_koordinatpelanggan', 'map_koordinatpelanggan.nomorrekening', '=', 'tblpelanggan.nomorrekening')
                ->join('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
                ->where('action_wms.id', $id)->first();
            return response()->json([
                'message' => 'success',
                'data' => $actionWm
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // *untuk Bukti show
    public function showHistory($id)
    {
        try {
            $actionWm = actionWms::selectRaw('action_wms.id, action_wms.code,
            action_wms.proposal_wm_id,
            action_wms.memo,
            action_wms.old_image,
            action_wms.new_image,
            action_wms.image_done,
            action_wms.noWM1,
            action_wms.brandWM1,
            action_wms.standWM1,
            action_wms.noWM2,
            action_wms.brandWM2,
            action_wms.standWM2,
            action_wms.subdapertement_id,
            proposal_wms.customer_id,
            proposal_wms.status,
            proposal_wms.priority,
            proposal_wms.created_at,
            proposal_wms.updated_at,
            tblpelanggan.namapelanggan,
            tblpelanggan.nomorrekening,
            tblpelanggan.alamat,
            tblpelanggan.telp,
            tblpelanggan.idareal,
            subdapertements.name
            ')
                ->with('staff')->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
                ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->join('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
                ->where('action_wms.id', $id)->first();
            return response()->json([
                'message' => 'Data Water Meter Terkirim',
                'data' => $actionWm
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // *untuk Tindakan
    public function indexActionWm($id)
    {
        try {
            # code...
            $actionWm = actionWms::where('id', $id)->get();
            return response()->json([
                'message' => 'Data Water Meter Terkirim',
                'data' => $actionWm
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // *untuk data Staff
    public function indexStaff($id)
    {
        try {
            $staffs = actionWmStaff::selectRaw('staffs.name, staffs.id as staff_id, staffs.phone,action_wm_staff.id, work_units.name as work_unit_name')
                ->join('action_wms', 'action_wms.id', '=', 'action_wm_staff.action_wm_id')
                ->join('staffs', 'staffs.id', '=', 'action_wm_staff.staff_id')
                ->leftJoin('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
                ->where('action_wms.id', $id)
                ->get();
            return response()->json([
                'message' => 'success',
                'data' => $staffs
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }

        // dd($staffs);

    }

    // Unit
    // *untuk tambah staff
    public function addStaff($id)
    {
        try {
            $action = actionWms::where('id', $id)->first();

            // $staffs = Staff::where('subdapertement_id', $action->subdapertement_id)->get();

            // $action_staffs_list = DB::table('staffs')
            //     ->selectRaw('staffs.name, staffs.id, staffs.phone,action_wm_id')
            //     ->leftJoin('action_wm_staff', function ($join) {
            //         $join->on('action_wm_staff.staff_id', '=', 'staffs.id')
            //             ->join('action_wms', 'action_wm_staff.action_wm_id', '=', 'action_wms.id')
            //             ->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
            //             ->where('proposal_wms.status', '!=', 'close');
            //     })->where('staffs.subdapertement_id', '!=', $action->subdapertement_id)

            //     ->get();
            $action_staffs_list = DB::table('staffs')
                ->selectRaw('staffs.name, staffs.id, staffs.phone,action_wm_id, work_units.name as work_unit_name')
                ->leftJoin('action_wm_staff', function ($join) use ($id) {
                    $join->on('action_wm_staff.staff_id', '=', 'staffs.id')
                        ->join('action_wms', 'action_wm_staff.action_wm_id', '=', 'action_wms.id')
                        ->join('proposal_wms', 'proposal_wms.id', '=', 'action_wms.proposal_wm_id')
                        ->where('proposal_wms.status', '!=', 'close')
                        ->Where('proposal_wms.status', '!=', 'reject')
                        ->where('action_wms.id', '=', $id);
                })
                ->leftJoin('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
                ->where('staffs.subdapertement_id', $action->subdapertement_id)

                ->get();

            return response()->json([
                'message' => 'Data Water Meter Terkirim',
                'data' => $action_staffs_list,

            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    public function addStaffStore(Request $request)
    {
        try {
            $data = [
                'action_wm_id' => $request->id,
                'staff_id' => $request->staff_id
            ];
            $actionWmStaff = actionWmStaff::create($data);

            $admin_arr = Staff::where('id', $request->staff_id)
                ->get();
            foreach ($admin_arr as $key => $admin) {
                $message = 'Test: ' . $request->staff_id . ' Pergantian Water Meter Buka Aplikasi Segel Meter';
                //wa notif                
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff->phone;
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
                    'updated_at' => date('Y-m-d h:i:sa')
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
                'message' => 'Data Water Meter Terkirim',
                'data' => $actionWmStaff,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }


    public function approve($id)
    {
        try {
            $proposalWm = proposalWms::selectRaw('customer_id,NamaStatus, tblpelanggan.namapelanggan as name, tblpelanggan.nomorrekening')
                ->join('simpletabctm.tblpelanggan', 'proposal_wms.customer_id', '=', 'tblpelanggan.nomorrekening')
                ->leftJoin('simpletabctm.tblstatuswm', 'tblstatuswm.id', '=', 'proposal_wms.status_wm')
                ->find($id);
            // dd($proposalWm);
            // if (Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 8) {
            //     $subdapertement =  User::with('dapertement')->where('dapertement_id', '5')->where('subdapertement_id', '13')->get();
            // } else if (Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 5 || Auth::user()->roles[count(Auth::user()->roles) - 1]->id === 6) {

            // dd($proposalWm);
            $customer = Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                ->where('nomorrekening', $proposalWm->customer_id);

            // dd($customer->first());
            //send notif to admin start
            // $group_unit = CtmWilayah::where('tblwilayah.id', )->first()->group_unit;
            if ($customer->first()->group_unit == 1) {
                $subdapertement = "10";
            } else {
                $subdapertement = Subdapertement::select('subdapertements.id')->leftJoin('dapertements', 'dapertements.id', '=', 'subdapertements.dapertement_id')->where('subdapertements.name', 'TEKNIK')->where('group_unit', $customer->first()->group_unit)->first();
                $subdapertement = $subdapertement->id;
            }
            return response()->json([
                'message' => 'success',
                'data' => $customer->first(),
                'subdapertement' => $subdapertement
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    public function approveProses(Request $request)
    {
        try {
            $proposalwms = proposalWms::find($request->id);
            $proposalWm = $proposalwms;

            $data = [
                'proposal_wm_id' => $request->id,
                'subdapertement_id' => $request->subdapertement_id,
                'memo' => $request->memo,
                'category' => $request->category
            ];
            actionWms::create($data);

            $proposalwms = $proposalwms->update([
                'priority' => $request->priority,
                'status' => $request->status,
                'updated_at' => date('Y-m-d h:m:s'),
                'created_at' => date('Y-m-d h:m:s')
            ]);

            $customer = Customer::join('tblwilayah', 'tblwilayah.id', '=', 'tblpelanggan.idareal')
                ->where('nomorrekening', $proposalWm->customer_id);

            $group_unit = CtmWilayah::where('tblwilayah.id', $customer->first()->idareal)->first()->group_unit;

            $admin_arr = User::selectRaw('users.*, dapertements.*')
                ->leftJoin('dapertements', 'users.dapertement_id', '=', 'dapertements.id')
                ->leftjoin('role_user', 'users.id', '=', 'role_user.user_id')
                ->where('group_unit', $group_unit)->where('role_user.role_id', 17)
                ->orWhere('role_user.role_id', 14)->where('group_unit', $group_unit)
                ->orWhere('group_unit', $group_unit)->where('role_user.role_id', 16)

                // ->orwhere('dapertement_id', '2')->where('subdapertement_id', '0')->where('staff_id', '0')->where('group_unit', $group_unit)
                ->get();
            // dd($admin_arr);
            foreach ($admin_arr as $key => $admin) {
                $message = 'Test: ' . $admin->id . ' Disetujui';
                //wa notif                
                $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
                $wa_data_group = [];
                //get phone user
                if ($admin->staff_id > 0) {
                    $staff = StaffApi::where('id', $admin->staff_id)->first();
                    $phone_no = $staff->phone;
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
                    'updated_at' => date('Y-m-d h:i:sa')
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
                'message' => 'success',
                'data' => $proposalwms,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    public function destroy(Request $request)
    {
        // dd($request->all());
        try {
            $test = actionWmStaff::where('id', $request->id)->delete();
            return response()->json([
                'message' => 'success',
                'data' => $test,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // public function store(Request $request)
    // {

    //     $last_code = $this->get_last_code('lock_action');

    //     $code = acc_code_generate($last_code, 8, 3);
    //     $img_path = "/images/segelMeter";
    //     //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            //$basepath  = base_path().'/public';
    //     $dataForm = json_decode($request->form);
    //     $responseImage = '';

    //     $dataQtyImage = json_decode($request->qtyImage);
    //     for ($i = 1; $i <= $dataQtyImage; $i++) {
    //         if ($request->file('image' . $i)) {
    //             $resourceImage = $request->file('image' . $i);
    //             $nameImage = strtolower($code);
    //             $file_extImage = $request->file('image' . $i)->extension();
    //             $nameImage = str_replace(" ", "-", $nameImage);

    //             $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->customer_id . $i . "." . $file_extImage;
    //             $folder_upload = 'images/segelMeter';
    //             $resourceImage->move($folder_upload, $img_name);

    //             $dataImageName[] = $img_name;
    //         } else {
    //             $responseImage = 'Image tidak di dukung';
    //             break;
    //         }
    //     }

    //     if ($responseImage != '') {
    //         return response()->json([
    //             'message' => $responseImage,
    //         ]);
    //     }
    //     // image
    //     // $resourceImage = $request->file('image');
    //     // $nameImage = strtolower($code);
    //     // $file_extImage = $request->file('image')->extension();
    //     // $nameImage = str_replace(" ", "-", $nameImage);

    //     // $img_name = $img_path . "/" . $nameImage . "-" . $dataForm->customer_id . "." . $file_extImage;

    //     // $resourceImage->move($basepath . $img_path, $img_name);

    //     // video
    //     $video_name = '';
    //     if ($request->file('video')) {

    //         $video_path = "/videos/segelMeter";
    //         $resource = $request->file('video');
    //         // $filename = $resource->getClientOriginalName();
    //         // $file_extVideo = $request->file('video')->extension();
    //         $video_name = $video_path . "/" . strtolower($code) . '-' . $dataForm->customer_id . '.mp4';

    //         $resource->move($basepath . $video_path, $video_name);
    //     }

    //     // if (!isset($dataForm->title)) {
    //     //     $dataForm->title = 'Tiket Keluhan';
    //     // }

    //     // if (!isset($dataForm->category_id)) {
    //     //     $category = CategoryApi::orderBy('id', 'ASC')->first();
    //     //     $dataForm->category_id = $category->id;
    //     // }

    //     //set SPK

    //     // $dateNow = date('Y-m-d H:i:s');
    //     // $subdapertement_def = Subdapertement::where('def', '1')->first();
    //     // $dapertement_def_id = $subdapertement_def->dapertement_id;
    //     // $subdapertement_def_id = $subdapertement_def->id;
    //     // $arr['dapertement_id'] = $dapertement_def_id;
    //     // $arr['month'] = date("m");
    //     // $arr['year'] = date("Y");
    //     // $last_spk = $this->get_last_code('spk-ticket', $arr);
    //     // $spk = acc_code_generate($last_spk, 21, 17, 'Y');

    //     try {

    //         // $ticket = LockAction::create($data);
    //         // if ($ticket) {
    //         $upload_image = new LockAction;
    //         $upload_image->image = str_replace("\/", "/", json_encode($dataImageName));
    //         $upload_image->code = $code;
    //         $upload_image->customer_id = $dataForm->customer_id;
    //         $upload_image->staff_id = $dataForm->staff_id;
    //         $upload_image->type = $dataForm->type;
    //         $upload_image->memo = $dataForm->memo;
    //         $upload_image->lat = $dataForm->lat;
    //         $upload_image->lng = $dataForm->lng;

    //         $upload_image->save();
    //         // }

    //         //send notif to admin

    //         // $admin_arr = User::where('dapertement_id', 0)->get();
    //         // foreach ($admin_arr as $key => $admin) {
    //         //     $id_onesignal = $admin->_id_onesignal;
    //         //     $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->memo;
    //         //     if (!empty($id_onesignal)) {
    //         //         OneSignal::sendNotificationToUser(
    //         //             $message,
    //         //             $id_onesignal,
    //         //             $url = null,
    //         //             $data = null,
    //         //             $buttons = null,
    //         //             $schedule = null
    //         //         );
    //         //     }
    //         // }

    //         //send notif to humas

    //         // $admin_arr = User::where('subdapertement_id', $subdapertement_def_id)
    //         //     ->where('staff_id', 0)
    //         //     ->get();
    //         // foreach ($admin_arr as $key => $admin) {
    //         //     $id_onesignal = $admin->_id_onesignal;
    //         //     $message = 'Humas: Keluhan Baru Diterima : ' . $dataForm->memo;
    //         //     if (!empty($id_onesignal)) {
    //         //         OneSignal::sendNotificationToUser(
    //         //             $message,
    //         //             $id_onesignal,
    //         //             $url = null,
    //         //             $data = null,
    //         //             $buttons = null,
    //         //             $schedule = null
    //         //         );
    //         //     }
    //         // }

    //         return response()->json([
    //             'message' => 'Segel Meter Terkirim',
    //             'data' => $upload_image,
    //         ]);
    //     } catch (QueryException $ex) {
    //         return response()->json([
    //             'message' => 'gagal',
    //         ]);
    //     }
    // }
}
