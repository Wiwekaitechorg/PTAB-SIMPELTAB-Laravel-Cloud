<?php

namespace App\Http\Controllers\Api\V1\Absence1;

use App\Absence;
use App\AbsenceLog;
use App\AbsenceRequest;
use App\AbsenceRequestLogs;
use App\Day;
use App\Holiday;
use App\Http\Controllers\Controller;
use App\MessageLog;
use App\Requests;
use App\Requests_file;
use App\ShiftPlannerStaffs;
use App\Staff;
use App\StaffApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OneSignal;
use App\Traits\WablasTrait;
use App\User;
use App\wa_history;
use App\WorkTypeDays;

class RequestApiController extends Controller
{
    use WablasTrait;
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
        // $dataForm = $request;
        // pengecekan utama start
        if ($dataForm->category == "extra") {
            $start1 = date("Y-m-d H:i:s", strtotime($dataForm->start . $dataForm->time));
            $day = date("w", strtotime($dataForm->start));
            $staff = Staff::selectRaw('staffs.*,work_types.type as work_type, work_types.id as work_type_id ')->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
                ->where('staffs.id', $dataForm->staff_id)->first();
            // dd($staff);
            if ($staff->work_type == "shift") {
                $jumShift = ShiftPlannerStaffs::whereDate('shift_planner_staffs.start', '=', $dataForm->start)
                    ->where('staff_id', $staff->id)
                    ->get();
                foreach ($jumShift as $data) {
                    $absence = ShiftPlannerStaffs::join('shift_groups', 'shift_groups.id', '=', 'shift_planner_staffs.shift_group_id')
                        ->join('shift_group_timesheets', 'shift_groups.id', '=', 'shift_group_timesheets.shift_group_id')
                        ->where('shift_group_timesheets.absence_category_id', '1')
                        ->where('shift_groups.id', $data->shift_group_id)
                        ->where('staff_id', $staff->id)
                        ->whereDate('shift_planner_staffs.start', '=', $dataForm->start)
                        ->orWhere('shift_group_timesheets.absence_category_id', '2')
                        ->where('shift_groups.id', $data->shift_group_id)
                        ->where('staff_id', $staff->id)
                        ->whereDate('shift_planner_staffs.start', '=', $dataForm->start)
                        ->orderBy('shift_group_timesheets.absence_category_id', 'ASC')
                        ->get();

                    if ($absence[0]->time > $absence[1]->time) {
                        $masuk = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[0]->time));
                        $pulang = date("Y-m-d H:i:s", strtotime('+ ' . '1' . ' days', strtotime($dataForm->start . $absence[1]->time)));
                        //  date("Y-m-d H:i:s", strtotime());
                    } else {
                        $masuk = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[0]->time));
                        $pulang = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[1]->time));
                    }


                    // dd($masuk, $start1, $pulang, $absence);
                    if ($start1 > $masuk && $start1 < $pulang) {
                        return response()->json(
                            [

                                'message' => 'anda tidak bisa melakukan lembur di jam kerja'
                            ]
                        );
                    }
                }
            } else {
                $absence = WorkTypeDays::selectRaw('time')
                    ->where('work_type_id', $staff->work_type_id)
                    ->where('day_id', $day != "0" ? $day : "7")
                    ->where('work_type_days.absence_category_id', '2')
                    ->orWhere('work_type_days.absence_category_id', '1')
                    ->where('work_type_id', $staff->work_type_id)
                    ->where('day_id', $day != "0" ? $day : "7")
                    ->orderBy('work_type_days.absence_category_id', 'ASC')
                    ->get();
                $masuk = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[0]->time));
                $pulang = date("Y-m-d H:i:s", strtotime($dataForm->start . $absence[1]->time));
                // dd($masuk, $start1, $pulang);
                if ($start1 > $masuk && $start1 < $pulang) {
                    return response()->json(
                        [
                            'message' => 'anda tidak bisa melakukan lembur di jam kerja'
                        ]
                    );
                }
            }

            // dd($absence);
        } else {
        }
        // pengecekan utama end

        $start = "";
        $end = "";
        $error = "";
        $cek = null;
        if ($dataForm->start == "") {
            $start = date('Y-m-d H:i:s');
            $startS = date('Y-m-d H:i:s');
        } else if ($dataForm->start != "" && $dataForm->time == "") {
            $start = date("Y-m-d H:i:s", strtotime($dataForm->start));
            $startS = date("Y-m-d H:i:s", strtotime($dataForm->start));
        } else {
            $start = date("Y-m-d H:i:s", strtotime($dataForm->start . $dataForm->time));
            $startS = date("Y-m-d H:i:s", strtotime($dataForm->start . $dataForm->time));
        }

        if ($dataForm->end == "") {
            $end = date("Y-m-d H:i:s", strtotime($dataForm->start . '23:59:59'));
            $endS = date("Y-m-d H:i:s", strtotime($dataForm->start . '23:59:59'));
        } else {
            $end = date("Y-m-d H:i:s", strtotime($dataForm->end . '23:59:59'));
            $endS = date("Y-m-d H:i:s", strtotime($dataForm->end . '23:59:59'));
        }
        if ($start < date('Y-m-d') || $start > $end) {
            $cek = "pass";
            $error = "Tanggal kurang dari hari ini";
        } else if ($dataForm->category == "duty" || $dataForm->category == "visit" || $dataForm->category == "leave" || $dataForm->category == "permission") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) use ($start, $end) {
                    $query->where('category', 'visit')
                        ->orWhere('category', 'visit')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission')
                        ->orWhere('category', 'extra')
                        ->orWhere('category', 'geolocation_off')
                        ->orWhere('category', 'excuse');

                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            // dd($cek);


            if ($cek) {
                $error = "Anda Masih Memiliki Cuti/Dinas/Izin yang masih aktif di tanggal ini";
            }
            //  else {
            //     $error = "kosong";
            // }
            // return response()->json([
            //     'message' => $error,
            //     'data' => $error,
            // ]);
        } else if ($dataForm->category == "excuse") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) {
                    $query->where('category', 'excuse')
                        ->orWhere('category', 'duty')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission');
                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            if ($cek) {
                $error = "Anda Masih Memiliki Permisi di tanggal ini";
            }
        } else if ($dataForm->category == "extra") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) {
                    $query->where('category', 'extra')
                        ->orWhere('category', 'duty')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission');
                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            if ($cek) {
                $error = "Anda Masih Memiliki Lembur di tanggal ini";
            }
        } else if ($dataForm->category == "geolocation_off") {
            $start =  date("Y-m-d", strtotime($start));
            $end =  date("Y-m-d", strtotime($end));
            $cek = AbsenceRequest::where('staff_id', $dataForm->staff_id)
                ->where(function ($query) {
                    $query->where('category', 'geolocation_off')
                        ->orWhere('category', 'duty')
                        ->orWhere('category', 'leave')
                        ->orWhere('category', 'permission')
                        ->orWhere('category', 'excuse');
                    // ->orWhere('status', 'close');
                })
                ->where(function ($query)  use ($start, $end) {
                    //$query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$start, $end])
                    $query->whereRaw("DATE(absence_requests.start) >= '".$start."' AND DATE(absence_requests.start) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        })
                        //->orWhereBetween(DB::raw('DATE(absence_requests.end)'), [$start, $end])
                        ->orWhereRaw("DATE(absence_requests.end) >= '".$start."' AND DATE(absence_requests.end) <= '".$end."'")
                        ->where(function ($query)  use ($start, $end) {
                            $query->where('status', '=', 'active')
                                ->orWhere('status', '=', 'pending')
                                ->orWhere('status', '=', 'approve');
                            // ->orWhere('status', 'close');
                        });
                    // ->orWhere('status', 'close');
                })

                ->first();
            if ($cek) {
                $error = "Anda Masih Memiliki Permohonan Absen Luar di tanggal ini";
            }
        } else {
        }

        if ($dataForm->type == "sick") {
            $endS = "";
        } else if ($dataForm->type == "sick_approve") {
            $endS = "";
        } else {
            $endS =  $endS;
        }

        if ($error == "") {
            $requests = new AbsenceRequest();
            $requests->staff_id = $dataForm->staff_id;
            $requests->description = $dataForm->description;
            $requests->start = $startS;
            $requests->end = $endS;
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


            // untuk Notif start
            $admin = Staff::selectRaw('users.*')->where('staffs.id',  $dataForm->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
            $id_onesignal = $admin->_id_onesignal;
            // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
            //wa notif                
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user

            if ($dataForm->category == "visit") {
                $categoryName = "Dinas Dalam";
            } else if ($dataForm->category == "duty") {
                $categoryName = "Dinas Luar";
            } else if ($dataForm->category == "permission") {
                $categoryName = "Izin";
            } else if ($dataForm->category == "excuse") {
                $categoryName = "Permisi";
            } else if ($dataForm->category == "geolocation_off") {
                $categoryName = "Absen Diluar";
            } else if ($dataForm->category == "extra") {
                $categoryName = "Lembur";
            } else if ($dataForm->category == "leave") {
                $categoryName = "Cuti";
            } else {
                $categoryName = "";
            }

            $phone_no = $admin->phone;
            $message = "Pengajuan " . $categoryName . " oleh " . $admin->name;
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
            // DB::table('wa_histories')->insert($wa_data);
            // $wa_sent = WablasTrait::sendText($wa_data_group);
            // $array_merg = [];
            // if (!empty(json_decode($wa_sent)->data->messages)) {
            //     $array_merg = array_merge(json_decode($wa_sent)->data->messages, $array_merg);
            // }
            // foreach ($array_merg as $key => $value) {
            //     if (!empty($value->ref_id)) {
            //         wa_history::where('ref_id', $value->ref_id)->update(['id_wa' => $value->id, 'status' => ($value->status === false) ? "gagal" : $value->status]);
            //     }
            // }

            // //onesignal notif                                
            // if (!empty($id_onesignal)) {
            //     OneSignal::sendNotificationToUser(
            //         $message,
            //         $id_onesignal,
            //         $url = null,
            //         $data = null,
            //         $buttons = null,
            //         $schedule = null
            //     );
            // }
            // // untuk notif end

            //send notif to admin
            $admin_arr = User::where('dapertement_id', 5)
                ->where('subdapertement_id', 0)
                ->where('staff_id', 0)->get();
            foreach ($admin_arr as $key => $admin) {
                $id_onesignal = $admin->_id_onesignal;
                // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
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

            return response()->json([
                'message' => 'Pengajuan Terkirim',
                'data' => $requests,
            ]);
        } else {
            return response()->json([
                'message' => $error,
                'data' => '',
            ]);
        }
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
            ->orderBy('created_at', 'DESC')
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


    public function requestApprove(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        // return response()->json([
        //     'message' => 'Pengajuan Terkirim',
        //     'data' =>  $user,
        // ]);
        if ($user->dapertement_id == '5') {
            $requests = AbsenceRequest::select('absence_requests.*', 'staffs.name as staff_name')->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
                // ->FilterDapertement($user->dapertement_id)
                ->FilterDate($request->from, $request->to)
                ->where('category', $request->category)
                ->orderBy('created_at', 'DESC')
                ->paginate(3, ['*'], 'page', $request->page);
            return response()->json([
                'message' => 'Pengajuan Terkirim',
                'data' => $requests,
            ]);
        } else {
            $requests = AbsenceRequest::select('absence_requests.*', 'staffs.name as staff_name')->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
                ->FilterDapertement($user->dapertement_id)
                ->FilterDate($request->from, $request->to)
                ->where('category', $request->category)
                ->orderBy('created_at', 'DESC')
                ->paginate(3, ['*'], 'page', $request->page);
            return response()->json([
                'message' => 'Pengajuan Terkirim',
                'data' => $requests,
            ]);
        }
    }

    public function approve(Request $request)
    {


        $requests = AbsenceRequest::selectRaw('absence_requests.*,work_types.id as work_type_id, work_types.type as type, staffs.id as staff_id')
            ->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
            ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
            ->where('absence_requests.id', $request->id)->first();

        //     ->update(
        //         [
        //             'status' => 'approve'
        //         ]
        //     );


        // jika dinas luar/izin/lembur start
        if ($requests->category == "permission" || $requests->category == "duty" || $requests->category == "leave") {

            $d = AbsenceRequest::where('id', $request->id)
                ->update(['status' => 'approve']);
            $d = AbsenceRequest::where('id', $request->id)->first();

            // buat absence log start

            $absenceRequest =  AbsenceRequest::select(
                'absence_requests.*',
                DB::raw('DATE(start) as start'),
                DB::raw('DATE(end) as end'),
            )
                ->where('id', $request->id)->first();
            // dd($absenceRequest);
            if ($requests->category == "permission") {
                $message = "Izin anda tanggal " . $d->start . " disetujui";
            } else if ($requests->category == "duty") {
                $message = "Dinas anda tanggal " . $d->start . " sampai dengan " . $d->end . " disetujui";
            } else if ($requests->category == "leave") {
                $message = "Cuti anda tanggal " . $d->start . " sampai dengan " . $d->end . " disetujui";
            } else {
                $message = "";
            }
            // if (date('Y-m-d') >= $absenceRequest->start) {
            $begin = strtotime($absenceRequest->start);
            $end   = strtotime($absenceRequest->end);
            // return response()->json([
            //     'message' => 'Pengajuan Terkirim',
            //     'begin' => $begin,
            //     'end' => $end
            // ]);
            // dd();



            if ($requests->type != "shift") {

                // libur nasional
                // $holidays = Holiday::select(DB::raw('DATE(holidays.start) AS start'), DB::raw('DATE(holidays.end) AS end'))
                //     //  ->whereBetween(DB::raw('DATE(holidays.start)'), [$from, $to])
                //     //  ->orWhereBetween(DB::raw('DATE(holidays.end)'), [$from, $to])
                //     ->get();
                // // dd($holidays);
                // foreach ($holidays as $holiday) {
                //     $awal_libur = date_create_from_format('Y-m-d', $holiday->start);
                //     $awal_libur = date_format($awal_libur, 'Y-m-d');
                //     $awal_libur = strtotime($awal_libur);

                //     $akhir_libur = date_create_from_format('Y-m-d', $holiday->end);
                //     $akhir_libur = date_format($akhir_libur, 'Y-m-d');
                //     $akhir_libur = strtotime($akhir_libur);

                //     $work_type_days = Day::select('days.*')->leftJoin(
                //         'work_type_days',
                //         function ($join) use ($requests) {
                //             $join->on('days.id', '=', 'work_type_days.day_id')
                //                 ->where('work_type_id', $requests->work_type_id);
                //         }
                //     )
                //         ->where('work_type_days.day_id', '=', null)->get();
                // }


                $work_type_days = Day::select('days.*')->leftJoin(
                    'work_type_days',
                    function ($join) use ($requests) {
                        $join->on('days.id', '=', 'work_type_days.day_id')
                            ->where('work_type_id', $requests->work_type_id);
                    }
                )
                    ->where('work_type_days.day_id', '=', null)->get();
                // dd($work_type_days);
                // dd($work_type_days);
                $jadwallibur = [];
                foreach ($work_type_days as $work_type_day) {
                    $jadwallibur = array_merge($jadwallibur, [$work_type_day->id != "7" ? '' . $work_type_day->id : '0']);
                }
                $ab_id = [];
                for ($i = $begin; $i <= $end; $i = $i + 86400) {
                    $holiday = Holiday::whereDate(DB::raw('DATE(start)'), '<=', date('Y-m-d', $i))->whereDate(DB::raw('DATE(end)'), '>=', date('Y-m-d', $i))->first();

                    if (!$holiday) {
                        if ((!in_array(date('w', $i), $jadwallibur))) {
                            // dd('test');
                            // $ab_id[] = [
                            //     'day_id' => date("w", strtotime(date('Y-m-d', $i))),
                            //     'staff_id' => $absenceRequest->staff_id,
                            //     'created_at' => date('Y-m-d H:i:s', $i),
                            //     'updated_at' => date('Y-m-d H:i:s')
                            // ];
                            $ab_id = Absence::create([
                                'day_id' => date("w", strtotime(date('Y-m-d', $i))),
                                'staff_id' => $absenceRequest->staff_id,
                                'created_at' => date('Y-m-d H:i:s', $i),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                            AbsenceLog::create([
                                'absence_category_id' => $absenceRequest->category == "leave" ? 8 : 13,
                                'lat' => '',
                                'lng' => '',
                                'absence_request_id' => $absenceRequest->id,
                                'register' => date('Y-m-d', $i),
                                'absence_id' => $ab_id->id,
                                'duration' => '',
                                'status' => ''
                            ]);
                        }
                        // dd($holiday);

                    }
                    // if ($holiday) {
                    //     $hd[] = ['test1' => $holiday->start, 'test1' => $holiday->end];
                    // }
                    // $id[] = [date('Y-m-d', $i)];
                }
                // return response()->json([
                //     'message' => 'Pengajuan Terkirim',
                //     'begin' => $ab_id,
                //     'holiday' => $hd,
                //     'i' => $id
                // ]);
            } else {
                $shift_planners = ShiftPlannerStaffs::select('shift_planner_staffs.*', DB::raw('DATE(shift_planner_staffs.start) as start'))->where('staff_id',  $requests->staff_id)
                    // ->whereBetween(DB::raw('DATE(shift_planner_staffs.start)'), [$from, $to])
                    ->get();

                $jadwalmasuk = [];
                foreach ($shift_planners as $shift_planner) {
                    $jadwalmasuk = array_merge($jadwalmasuk, [$shift_planner->start]);
                }

                for ($i = $begin; $i <= $end; $i = $i + 86400) {
                    $holiday = Holiday::whereDate('start', '<=', date('Y-m-d', $i))->whereDate('end', '>=', date('Y-m-d', $i))->first();
                    // if (!$holiday) {
                    if ((in_array(date("Y-m-d", strtotime(date('Y-m-d', $i))), $jadwalmasuk))) {
                        // dd('test');
                        $ab_id = Absence::create([
                            'day_id' => date("w", strtotime(date('Y-m-d', $i))),
                            'staff_id' => $absenceRequest->staff_id,
                            'created_at' => date('Y-m-d H:i:s', $i),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        AbsenceLog::create([
                            'absence_category_id' => $absenceRequest->category == "leave" ? 8 : 13,
                            'lat' => '',
                            'lng' => '',
                            'absence_request_id' => $absenceRequest->id,
                            'register' => date('Y-m-d', $i),
                            'absence_id' => $ab_id->id,
                            'duration' => '',
                            'status' => ''
                        ]);
                        // }
                    }
                    // dd($holiday);
                    // }
                }
            }
            // return $jadwallibur;

            // dd('hhh');
            // buat absence log end
            $message = "Izin anda tanggal " . $d->start . " sampai dengan " . $d->end . " diterima";
            MessageLog::create([
                'staff_id' => $d->staff_id,
                'memo' => $message,
                'type' => 'message',
                'status' => 'pending',
            ]);

            // untuk Notif start
            $admin = Staff::selectRaw('users.*')->where('staffs.id', $d->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
            $id_onesignal = $admin->_id_onesignal;
            // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
            //wa notif                
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($d->staff_id > 0) {
                $staff = Staff::where('id', $d->staff_id)->first();
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
            // $wa_sent = WablasTrait::sendText($wa_data_group);
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
            // if (!empty($id_onesignal)) {
            //     OneSignal::sendNotificationToUser(
            //         $message,
            //         $id_onesignal,
            //         $url = null,
            //         $data = null,
            //         $buttons = null,
            //         $schedule = null
            //     );
            // }
            // untuk notif end
        } else {
            $d = AbsenceRequest::where('id', $request->id)
                ->update(['status' => 'approve']);

            $d = AbsenceRequest::where('id', $request->id)->first();
            // dd($d);
            $message = "Permisi anda tanggal " . $d->start . " disetujui";
            if ($d->category == "visit") {
                $message = "Dinas anda tanggal " . $d->start . " disetujui";
            } else if ($d->category == "excuse") {
                $message = "Permisi anda tanggal " . $d->start . " disetujui";
            } else if ($d->category == "extra") {
                $message = "Lembur anda tanggal " . $d->start . " disetujui";
            } else {
                $message = "geolocation off disetujui";
            }
            MessageLog::create([
                'staff_id' => $d->staff_id,
                'memo' => $message,
                'type' => 'message',
                'status' => 'pending',
            ]);
            // dd($message);

            // untuk Notif start
            $admin = Staff::selectRaw('users.*')->where('staffs.id', $d->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
            $id_onesignal = $admin->_id_onesignal;
            // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
            //wa notif                
            $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
            $wa_data_group = [];
            //get phone user
            if ($d->staff_id > 0) {
                $staff = Staff::where('id', $d->staff_id)->first();
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
            // $wa_sent = WablasTrait::sendText($wa_data_group);
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
            // if (!empty($id_onesignal)) {
            //     OneSignal::sendNotificationToUser(
            //         $message,
            //         $id_onesignal,
            //         $url = null,
            //         $data = null,
            //         $buttons = null,
            //         $schedule = null
            //     );
            // }
            // untuk notif end
        }

        // jika dinas luar/izin/lembur end




        return response()->json([
            'message' => 'Bukti Dihapus',
            // 'id' => $id,
            'data' => $requests,
        ]);
    }

    public function reject(Request $request)
    {
        $d = AbsenceRequest::where('id', $request->id)
            ->update(['status' => 'reject']);
        $d = AbsenceRequest::where('id', $request->id)->first();
        if ($request->id) {
            $absenceLog = AbsenceLog::where('absence_request_id', $request->id)->get();
            foreach ($absenceLog as $da) {
                $deleteAbsence = Absence::where('id', $da->absence_id)->first();
                if ($deleteAbsence) {
                    Absence::where('id', $da->absence_id)->delete();
                }
            }


            AbsenceLog::where('absence_request_id', $request->id)->delete();
        }
        if ($d->category == "permission") {
            $message = "Izin anda tanggal " . $d->start . " ditolak";
        } else if ($d->category == "duty") {
            $message = "Dinas anda tanggal " . $d->start . " sampai dengan " . $d->end . " ditolak";
        } else if ($d->category == "leave") {
            $message = "Cuti anda tanggal " . $d->start . " sampai dengan " . $d->end . " ditolak";
        } else {
            $message = "";
        }
        MessageLog::create([
            'staff_id' => $d->staff_id,
            'memo' => $message,
            'type' => 'message',
            'status' => 'pending',
        ]);

        // untuk Notif start
        $admin = Staff::selectRaw('users.*')->where('staffs.id', $d->staff_id)->join('users', 'users.staff_id', '=', 'staffs.id')->first();
        $id_onesignal = $admin->_id_onesignal;
        // $message = 'Admin: Keluhan Baru Diterima : ' . $dataForm->description;
        //wa notif                
        $wa_code = date('y') . date('m') . date('d') . date('H') . date('i') . date('s');
        $wa_data_group = [];
        //get phone user
        if ($d->staff_id > 0) {
            $staff = Staff::where('id', $d->staff_id)->first();
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
        // $wa_sent = WablasTrait::sendText($wa_data_group);
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
        // if (!empty($id_onesignal)) {
        //     OneSignal::sendNotificationToUser(
        //         $message,
        //         $id_onesignal,
        //         $url = null,
        //         $data = null,
        //         $buttons = null,
        //         $schedule = null
        //     );
        // }
        // untuk notif end

        return response()->json([
            'message' => 'Bukti Dihapus',
            // 'id' => $id,
            'data' => $d,
        ]);
    }

    public function show(Request $request)
    {

        $requests = AbsenceRequest::selectRaw('absence_requests.*,work_types.id as work_type_id, work_types.type as type, staffs.id as staff_id')
            ->join('staffs', 'absence_requests.staff_id', '=', 'staffs.id')
            ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
            ->where('absence_requests.id', $request->id)->first();

        $request_file = AbsenceRequestLogs::where('absence_request_id', $request->id)->get();

        return response()->json([
            'message' => 'Bukti Dihapus',
            'data' => $requests,
            'data2' => $request_file,
        ]);
    }
}
