<?php

namespace App\Http\Controllers\Admin;

use App\Check;
use App\CheckStaff;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckRequest;
use App\Staff;
use App\Ticket;
use App\Traits\TraitModel;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CheckApi;
use App\StaffApi;

class ChecksController extends Controller
{
    use TraitModel;

    public function checkStaffStoreTest(Request $request)
    {

        $check = CheckApi::with('ticket')->find($request->check_id);
        $staff = StaffApi::find($request->staff_id);

        if ($check) {
            $cek = $check->staff()->attach($request->staff_id, ['status' => 'pending']);

            if (!$cek) {
                $check = Check::where('id', $request->check_id)->with('staff')->first();

                // dd($check->staff[0]->pivot->status);
                $cekAllStatus = false;
                $statusCheck = 'close';
                for ($status = 0; $status < count($check->staff); $status++) {
                    // dd($check->staff[$status]->pivot->status);
                    if ($check->staff[$status]->pivot->status == 'pending') {
                        $statusCheck = 'pending';
                        break;
                    } else if ($check->staff[$status]->pivot->status == 'active') {

                        $statusCheck = 'active';
                    }
                }

                $dateNow = date('Y-m-d H:i:s');

                echo $statusCheck;
            } else {
                print_r($cek);
            }
        }
    }

    public function index()
    {
        abort_unless(\Gate::allows('check_access'), 403);

        return view('admin.checks.index');
    }

    public function create($ticket_id)
    {
        abort_unless(\Gate::allows('check_create'), 403);

        // $user_id = Auth::check() ? Auth::user()->id : null;
        // $department = '';
        // if (isset($user_id) && $user_id != '') {
        //     $admin = User::with('roles')->find($user_id);
        //     $role = $admin->roles[0];
        //     $role->load('permissions');
        //     $permission = json_decode($role->permissions->pluck('title'));
        //     if (!in_array("ticket_all_access", $permission)) {
        //         $department = $admin->dapertement_id;
        //     }
        // }

        // if ($department != '') {
        //     $dapertements = Dapertement::where('id', $department)->get();
        // } else {
        //     $dapertements = Dapertement::all();
        // }

        $ticket = Ticket::findOrFail($ticket_id);
        $dapertements = Dapertement::where('id', $ticket->dapertement_id)->get();

        $staffs = Staff::all();

        return view('admin.checks.create', compact('dapertements', 'ticket_id', 'staffs'));
    }

    public function store(StoreCheckRequest $request)
    {
        abort_unless(\Gate::allows('check_create'), 403);

        $dateNow = date('Y-m-d H:i:s');

        $data = array(
            'description' => $request->description,
            'status' => 'pending',
            'dapertement_id' => $request->dapertement_id,
            'ticket_id' => $request->ticket_id,
            'start' => $dateNow,
            'subdapertement_id' => $request->subdapertement_id,
            'todo' => $request->todo,
        );

        $check = Check::create($data);

        return redirect()->route('admin.checks.list', $request->ticket_id);
    }

    public function show($id)
    {
        abort_unless(\Gate::allows('check_show'), 403);
    }

    public function edit(Check $check)
    {
        abort_unless(\Gate::allows('check_edit'), 403);

        $dapertements = Dapertement::all();

        $tickets = Ticket::all();

        $staffs = Staff::all();

        return view('admin.checks.edit', compact('dapertements', 'tickets', 'staffs', 'check'));
    }

    public function update(Request $request, Check $check)
    {
        abort_unless(\Gate::allows('check_edit'), 403);

        $check->update($request->all());

        return redirect()->route('admin.checks.list', $check->ticket_id);
    }

    public function destroy(Request $request, Check $check)
    {
        abort_unless(\Gate::allows('check_delete'), 403);

        $data = [];
        foreach ($check->staff as $key => $staff) {
            $data[$key] = $staff->id;
        }

        $cek = $check->staff()->detach($data);

        $check->delete();

        return redirect()->route('admin.checks.list', $check->ticket_id);
    }

    // get staff
    public function staff(Request $request)
    {
        abort_unless(\Gate::allows('staff_access'), 403);
        $staffs = Staff::where('dapertement_id', $request->dapertement_id)->get();

        return json_encode($staffs);
    }

    // list tindakan
    function list($ticket_id)
    {
        abort_unless(\Gate::allows('check_access'), 403);

        $user_id = Auth::check() ? Auth::user()->id : null;
        $department = '';
        $subdepartment = 0;
        $staff = 0;
        if (isset($user_id) && $user_id != '') {
            $admin = User::with('roles')->find($user_id);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("ticket_all_access", $permission)) {
                $department = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
                $staff = $admin->staff_id;
            }
        }

        if ($subdepartment > 0 && $staff > 0) {
            $checks = Check::selectRaw('DISTINCT checks.*')
                ->join('check_staff', function ($join) use ($staff) {
                    $join->on('check_staff.check_id', '=', 'checks.id')
                        ->where('check_staff.staff_id', '=', $staff);
                })
                ->with('staff')
                ->with('dapertement')
                ->with('subdapertement')
                ->with('ticket')
                ->where('ticket_id', $ticket_id)
                // ->orderBy('dapertements.group', 'desc')
                ->orderBy('start', 'desc')
                ->get();
        } else {
            $checks = Check::with('staff')
                ->with('dapertement')
                ->with('subdapertement')
                ->with('ticket')
                // ->orderBy('dapertements.group', 'desc')
                ->where('ticket_id', $ticket_id)
                ->orderBy('start', 'desc')
                ->get();
        }

        return view('admin.checks.list', compact('checks', 'ticket_id'));
        // dd($checks);
    }

    // list pegawai
    public function checkStaff($check_id)
    {
        abort_unless(\Gate::allows('check_staff_access'), 403);

        $check = Check::findOrFail($check_id);

        // $staffs = $check->staff;

        return view('admin.checks.checkStaff', compact('check'));
    }

    // nambah staff untuk tindakan
    public function checkStaffCreate($check_id)
    {

        abort_unless(\Gate::allows('check_staff_create'), 403);

        $check = Check::findOrFail($check_id);

        $check_staffs = Check::where('id', $check_id)->with('staff')->first();

        // $staffs = Staff::selectRaw('staffs.id,staffs.code,staffs.name,staffs.phone, work_units.name as work_unit_name')
        //     ->join('dapertements', 'dapertements.id', '=', 'staffs.dapertement_id')
        //     ->leftJoin('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
        //     ->where('subdapertement_id', $check->subdapertement_id)
        //     ->orderBy('work_units.serial_number', 'ASC')
        //     ->get();

        // dd($staffs);

        // $staffs = Staff::where('dapertement_id', $check->dapertement_id)->with('check')->get();

        $check_staffs_list = DB::table('staffs')
            ->join('check_staff', function ($join) {
                $join->on('check_staff.staff_id', '=', 'staffs.id')
                    ->where('check_staff.status', '!=', 'close');
            })
            ->join('checks', 'checks.id', '=', 'check_staff.check_id')
            ->where('checks.id', $check_id)
            ->get();

        $staffs = Staff::selectRaw('
        staffs.id,staffs.code,
        staffs.name,
        staffs.phone,
        work_units.name as work_unit_name,
        SUM(CASE WHEN status != "close" THEN 1 ELSE 0 END) AS jumlahtindakan
        ')
            ->leftJoin('check_staff', 'staffs.id', '=', 'check_staff.staff_id')
            ->leftJoin('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
            ->where('check_staff.status', '!=', null)
            ->where('subdapertement_id', $check->subdapertement_id)
            ->orWhere('check_staff.status', '=', null)
            ->where('subdapertement_id', $check->subdapertement_id)
            ->groupBy('staffs.id')
            ->orderBy('work_units.serial_number', 'ASC')
            ->get();
        // dd($staffs);

        return view('admin.checks.checkStaffCreate', compact('check_id', 'staffs', 'check', 'check_staffs', 'check_staffs_list'));

        // dd($check_staffs_list);
    }

    // store pegawai untuk tindakan

    public function checkStaffStore(Request $request)
    {
        abort_unless(\Gate::allows('check_staff_create'), 403);

        $check = Check::findOrFail($request->check_id);

        if ($check) {
            $cek = $check->staff()->attach($request->staff_id, ['status' => 'pending']);

            if ($cek) {
                $check = Check::where('id', $request->check_id)->with('staff')->first();

                // dd($check->staff[0]->pivot->status);
                $cekAllStatus = false;
                $statusCheck = 'close';
                for ($status = 0; $status < count($check->staff); $status++) {
                    // dd($check->staff[$status]->pivot->status);
                    if ($check->staff[$status]->pivot->status == 'pending') {
                        $statusCheck = 'pending';
                        break;
                    } else if ($check->staff[$status]->pivot->status == 'active') {

                        $statusCheck = 'active';
                    }
                }

                $dateNow = date('Y-m-d H:i:s');

                $check->update([
                    'status' => $statusCheck,
                    'end' => $statusCheck == 'pending' || $statusCheck == 'active' ? '' : $dateNow,
                ]);
            }
        }

        return redirect()->route('admin.checks.checkStaff', $request->check_id);
    }

    // update pegawai tindakan

    public function checkStaffEdit($check_id)
    {
        abort_unless(\Gate::allows('check_staff_edit'), 403);

        $check = Check::with('ticket')->findOrFail($check_id);
        // dd($check);
        return view('admin.checks.checkStaffEdit', compact('check'));
    }

    public function checkStaffUpdate(Request $request)
    {
        abort_unless(\Gate::allows('check_staff_edit'), 403);
        // ini_set('max_file_uploads', 50);
        $img_path = "/images/check";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';

        // dd($request->image_done);
        // upload image
        if ($request->file('image')) {
            foreach ($request->file('image') as $key => $image) {
                $resourceImage = $image;
                $nameImage = strtolower($request->check_id);
                $file_extImage = $image->extension();
                $nameImage = str_replace(" ", "-", $nameImage);
                $img_name = $img_path . "/" . $nameImage . "-" . $request->check_id . $key . "-work." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);
                $dataImageName[] = $img_name;
            }
        }

        // foto sebelum pengerjaan
        if ($request->file('image_prework')) {
            $resource_image_prework = $request->file('image_prework');
            $id_name_image_prework = strtolower($request->check_id);
            $file_ext_image_prework = $request->file('image_prework')->extension();
            $id_name_image_prework = str_replace(' ', '-', $id_name_image_prework);

            $name_image_prework = $img_path . '/' . $id_name_image_prework . '-' . $request->check_id . '-pre.' . $file_ext_image_prework;

            $resource_image_prework->move($basepath . $img_path, $name_image_prework);
            $data_image_prework = $name_image_prework;
        }

        // foto alat
        if ($request->file('image_tools')) {
            foreach ($request->file('image_tools') as $key => $image) {

                $resourceImage = $image;
                $nameImage = strtolower($request->check_id);
                $file_extImage = $resourceImage->extension();
                $nameImage = str_replace(" ", "-", $nameImage);

                $img_name = $img_path . "/" . $nameImage . "-" . $request->check_id . $key . "-tool." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);

                $dataImageNameTool[] = $img_name;
            }
        }

        if ($request->file('image_done')) {
            foreach ($request->file('image_done') as $key => $image) {
                $resourceImageDone = $image;
                $nameImageDone = strtolower($request->check_id);
                $file_extImageDone = $image->extension();
                $nameImageDone = str_replace(" ", "-", $nameImageDone);

                $img_name_done = $img_path . "/" . $nameImageDone . "-" . $request->check_id . $key . "-done." . $file_extImageDone;

                $resourceImageDone->move($basepath . $img_path, $img_name_done);

                $dataImageNameDone[] = $img_name_done;
            }
        }

        // upload image end

        $check = Check::where('id', $request->check_id)->with('ticket')->with('staff')->first();
        $cekAllStatus = false;
        $statusCheck = $request->status;

        $dateNow = date('Y-m-d H:i:s');

        //check if current status is close
        $date_end = $dateNow;
        if ($check->status == 'close') {
            $date_end = $check->end;
        }

        if ($request->file('image')) {
            $dataNewCheck = array(
                'status' => $statusCheck,
                'image' => str_replace("\/", "/", json_encode($dataImageName)),
                'end' => $statusCheck == 'pending' || $statusCheck == 'active' ? '' : $date_end,
                'memo' => $request->memo,
                'todo' => $request->todo,
            );
        } else {
            $dataNewCheck = array(
                'status' => $statusCheck,
                'end' => $statusCheck == 'pending' || $statusCheck == 'active' ? '' : $date_end,
                'memo' => $request->memo,
                'todo' => $request->todo,
            );
        }
        if ($request->file('image_tools')) {
            $dataNewCheck = array_merge(
                $dataNewCheck,
                ['image_tools' => str_replace("\/", "/", json_encode($dataImageNameTool))]
            );
        }
        if ($request->file('image_prework')) {
            $dataNewCheck = array_merge(
                $dataNewCheck,
                ['image_prework' => $data_image_prework]
            );
        }
        if ($request->file('image_done')) {
            $dataNewCheck = array_merge(
                $dataNewCheck,
                ['image_done' => str_replace("\/", "/", json_encode($dataImageNameDone))]
            );
        }

        $check->update($dataNewCheck);
        //update staff
        $ids = $check->staff()->allRelatedIds();
        foreach ($ids as $sid) {
            $check->staff()->updateExistingPivot($sid, ['status' => $request->status]);
        }
        //update ticket status
        $ticket = Ticket::find($check->ticket_id);
        $ticket->status = $statusCheck;
        $ticket->save();

        return redirect()->route('admin.checks.list', $ticket->id);
    }

    // editt status tindakan pegawai
    public function checkStaffDestroy($check_id, $staff_id)
    {
        abort_unless(\Gate::allows('check_staff_delete'), 403);

        $check = Check::findOrFail($check_id);

        if ($check) {
            $cek = $check->staff()->detach($staff_id);

            if ($cek) {
                $check = Check::where('id', $check_id)->with('staff')->first();

                // dd($check->staff[0]->pivot->status);
                $cekAllStatus = false;
                if (count($check->staff) > 0) {
                    $statusCheck = 'close';
                } else {
                    $statusCheck = $check->status;
                }
                // $statusCheck = 'close';
                for ($status = 0; $status < count($check->staff); $status++) {
                    // dd($check->staff[$status]->pivot->status);
                    if ($check->staff[$status]->pivot->status == 'pending') {
                        $statusCheck = 'pending';
                        break;
                    } else if ($check->staff[$status]->pivot->status == 'active') {

                        $statusCheck = 'active';
                    }
                }

                $dateNow = date('Y-m-d H:i:s');

                $check->update([
                    'status' => $statusCheck,
                    'end' => $statusCheck == 'pending' || $statusCheck == 'active' ? '' : $dateNow,
                ]);
            }
        }

        return redirect()->route('admin.checks.checkStaff', $check_id);
    }


    // tambahan foto selesai start


    public function additionalDone($check_id)
    {
        abort_unless(\Gate::allows('check_staff_edit'), 403);

        $check = Check::with('ticket')->findOrFail($check_id);
        $ticket = Ticket::find($check->ticket_id);
        // dd($check);
        return view('admin.checks.additionalDone', compact('check', 'ticket'));
    }


    public function storeAdditionalDone(Request $request)
    {
        abort_unless(\Gate::allows('check_staff_edit'), 403);
        // ini_set('max_file_uploads', 50);
        $img_path = "/images/check";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';

        // dd($request->image_done);
        // upload image

        $check = Check::where('id', $request->check_id)->with('ticket')->with('staff')->first();

        $checkImgNow = json_decode($check->image_done);
        // dd(count(($checkImgNow)));

        if ($request->file('image_done')) {
            foreach ($request->file('image_done') as $key => $image) {
                $resourceImageDone = $image;
                $nameImageDone = strtolower($request->check_id);
                $file_extImageDone = $image->extension();
                $nameImageDone = str_replace(" ", "-", $nameImageDone);

                $img_name_done = $img_path . "/" . $nameImageDone . "-" . $request->check_id . (count($checkImgNow) + $key + 1) . "-done." . $file_extImageDone;

                $resourceImageDone->move($basepath . $img_path, $img_name_done);

                $dataImageNameDone[] = $img_name_done;
            }
            $newImg = array_merge($dataImageNameDone, $checkImgNow);
        }



        // upload image end


        if ($request->file('image_done')) {
            $check->update(['image_done' => str_replace("\/", "/", json_encode($newImg))]);
        }


        // return redirect()->route('admin.checks.list', $request->ticket_id);
        return redirect()->back();
    }


    // tambahan foto selesai end



    //start surya buat
    public function printservice()
    {
        return view('admin.checks.printservice');
    }

    public function printspk()
    {
        return view('admin.checks.printspk');
    }

    public function printReport()
    {
        return view('admin.checks.printreport');
    }

    public function ubahData()
    {
        $t = Check::get();
        foreach ($t as $key => $value) {
            $db = Check::where('id', $value->id)->first();
            $db->image_tools = '["' . $value->image_tools . '"]';
            $db->update();
        }
    }

    //end surya buat
}
