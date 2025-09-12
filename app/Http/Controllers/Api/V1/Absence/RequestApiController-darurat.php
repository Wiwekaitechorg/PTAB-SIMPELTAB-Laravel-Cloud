<?php

namespace App\Http\Controllers\Api\V1\Absence;

use App\Absence;
use App\AbsenceRequest;
use App\AbsenceRequestLogs;
use App\Http\Controllers\Controller;
use App\Requests;
use App\Requests_file;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestApiController extends Controller
{

    // tidak dipakai lagi
    public function index(Request $request)
    {
        $workPermit = Absence::where('user_id', $request->id)->where('register', $request->date)->get();
        $absenOut = Absence::where('user_id', $request->id)->where('absen_category_id', $request->absen_category_id)->get();
        $wP = '0';
        $aO = '0';
        if (count($workPermit) > 0) {
            $wP = '0';
        } else {
            $wP = '1';
        }
        if (count($absenOut) > 0) {
            $aO = '1';
        } else {
            $wP = '0';
        }
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'absenOut' => $aO,
            'workPermit' => $wP,
        ]);
    }

    public function store(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');
        // $code = acc_code_generate($last_code, 8, 3);
        $dataForm = json_decode($request->form);

        $start = "";
        $end = "";

        if ($dataForm->start == "") {
            $start = date('Y-m-d H:i:s');
        } else if ($dataForm->start != "" && $dataForm->time == "") {
            $start = date("Y-m-d H:i:s", strtotime($dataForm->start));
        } else {
            $start = date("Y-m-d H:i:s", strtotime($dataForm->start . $dataForm->time));
        }

        if ($dataForm->end == "") {
            $end = date("Y-m-d H:i:s", strtotime($dataForm->start . '23:59:59'));
        } else {
            $end = date("Y-m-d H:i:s", strtotime($dataForm->end . '23:59:59'));
        }
        if ($dataForm->category == "duty" || $dataForm->category == "leave" || $dataForm->category == "permission") {
            $cek = AbsenceRequest::where('category', 'duty')
                //->whereBetween(DB::raw('DATE(absence_logs.start)'), [$start,  $end])->first();
                ->whereRaw("DATE(absence_logs.start) >= '".$start."' AND DATE(absence_logs.start) <= '".$end."'")->first();
        } else {
            $cek = "pass";
        }


        // if (!$cek) {
        $requests = new AbsenceRequest();
        $requests->staff_id = $dataForm->staff_id;
        $requests->description = $dataForm->description;
        $requests->start = $start;
        $requests->end = $end;
        $requests->type = $dataForm->type;
        $requests->time = $dataForm->time;
        $requests->status = $dataForm->status;
        $requests->category = $dataForm->category;

        $requests->save();
        $requests_id = $requests->id;


        if ($request->file('imageP')) {
            $image = $request->file('imageP');
            $resourceImage = $image;
            $nameImage = 'imageP' . date('Y-m-d h:i:s') . '.' . $image->extension();
            $file_extImage = $image->extension();
            $folder_upload = 'images/RequestFile';
            $resourceImage->move($folder_upload, $nameImage);

            // dd($request->file('old_image')->move($folder_upload, $img_name));

            // if ($actionWm->old_image != '') {
            //     foreach (json_decode($actionWm->old_image) as $n) {
            //         if (file_exists($n)) {

            //             unlink($basepath . $n);
            //         }
            //     }
            // }
            $data = [
                'image' => $nameImage,
                'absence_request_id' => $requests_id,
                'type' => 'approve'
            ];
            $data = AbsenceRequestLogs::create($data);
        }

        if ($request->file('imagePng')) {
            $image = $request->file('imagePng');
            $resourceImage = $image;
            $nameImage = 'imagePng' . date('Y-m-d h:i:s') . '.' . $image->extension();
            $file_extImage = $image->extension();
            $folder_upload = 'images/RequestFile';
            $resourceImage->move($folder_upload, $nameImage);


            $data = [
                'image' =>  $nameImage,
                'absence_request_id' => $requests_id,
                'type' => 'request'
            ];
            $data = AbsenceRequestLogs::create($data);
        }
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $requests,
        ]);
        // } else {
        //     return response()->json([
        //         'message' => 'Sudah Ada di tanggal ini',
        //         'data' => $requests,
        //     ]);
        // }
    }

    public function update(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');
        // $code = acc_code_generate($last_code, 8, 3);
        $dataForm = json_decode($request->form);

        if ($request->file('imageP')) {
            $image = $request->file('imageP');
            $resourceImage = $image;
            $nameImage = 'imageP' . date('Y-m-d h:i:s') . '.' . $image->extension();
            $file_extImage = $image->extension();
            $folder_upload = 'images/RequestFile';
            $resourceImage->move($folder_upload, $nameImage);

            // dd($request->file('old_image')->move($folder_upload, $img_name));

            // if ($actionWm->old_image != '') {
            //     foreach (json_decode($actionWm->old_image) as $n) {
            //         if (file_exists($n)) {

            //             unlink($basepath . $n);
            //         }
            //     }
            // }
            $data = [
                'image' => $nameImage,
                'absence_request_id' => $dataForm->id,
                'type' => 'approve'
            ];
            $data = AbsenceRequestLogs::create($data);
        }

        if ($request->file('imagePng')) {
            $image = $request->file('imagePng');
            $resourceImage = $image;
            $nameImage = 'imagePng' . date('Y-m-d h:i:s') . '.' . $image->extension();
            $file_extImage = $image->extension();
            $folder_upload = 'images/RequestFile';
            $resourceImage->move($folder_upload, $nameImage);


            $data = [
                'image' =>  $nameImage,
                'absence_request_id' => $dataForm->id,
                'type' => 'request'
            ];
            $data = AbsenceRequestLogs::create($data);
        }

        return response()->json([
            'message' => 'Pengajuan Terkirim',
        ]);
    }

    public function history(Request $request)
    {
        $requests = AbsenceRequest::where('staff_id', $request->staff_id)
            ->FilterDate($request->from, $request->to)
            ->paginate(3, ['*'], 'page', $request->page);
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $requests,
        ]);
    }

    public function imageDelete($id)
    {
        $requests = AbsenceRequestLogs::where('id', $id)->delete();
        return response()->json([
            'message' => 'Bukti Dihapus',
            'id' => $id,
            'data' => $requests,
        ]);
    }

    public function getPermissionCat(Request $request)
    {
        $cat = [
            ['id' => 'sick', 'name' => 'sakit', 'checked' => false],
            ['id' => 'other', 'name' => 'Lain-Lain', 'checked' => false],
        ];
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $cat,
        ]);
    }

    public function listFile(Request $request)
    {
        $file = AbsenceRequestLogs::selectRaw('image, id')->where('absence_request_id', $request->id)->get();
        return response()->json([
            'message' => 'Pengajuan Terkirim',
            'data' => $file,
            '$s' => $request->id
        ]);
    }

    // mungkin tidak dipakai
    public function absenceList(Request $request)
    {
        $duty = Requests::where('category', 'duty')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->get();
        $extra = Requests::where('category', 'extra')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->get();
        $permit = Requests::where('category', 'permit')->whereDate('date', '=', date('Y-m-d'))->where('user_id', $request->user_id)->where('status', 'approve')->get();

        return response()->json([
            'message' => 'Succes',
            'duty' => $duty,
            'extra' => $extra,
            'permit' => $permit,
        ]);
    }
}
