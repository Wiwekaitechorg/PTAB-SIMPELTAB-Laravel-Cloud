<?php

namespace App\Http\Controllers\Admin;

use App\Action;
use App\ActionStaff;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActionRequest;
use App\Staff;
use App\Ticket;
use App\Traits\TraitModel;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ActionApi;
use App\StaffApi;

class ActionsController extends Controller
{
    use TraitModel;

    public function actionStaffStoreTest(Request $request)
    {

        $action = ActionApi::with('ticket')->find($request->action_id);
        $staff = StaffApi::find($request->staff_id);

        if ($action) {
            $cek = $action->staff()->attach($request->staff_id, ['status' => 'pending']);

            if (!$cek) {
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

                echo $statusAction;
            } else {
                print_r($cek);
            }
        }
    }

    public function index()
    {
        abort_unless(\Gate::allows('action_access'), 403);

        return view('admin.actions.index');
    }

    public function create($ticket_id)
    {
        abort_unless(\Gate::allows('action_create'), 403);

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

        return view('admin.actions.create', compact('dapertements', 'ticket_id', 'staffs'));
    }

    public function store(StoreActionRequest $request)
    {
        abort_unless(\Gate::allows('action_create'), 403);

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

        $action = Action::create($data);

        return redirect()->route('admin.actions.list', $request->ticket_id);
    }

    public function show($id)
    {
        abort_unless(\Gate::allows('action_show'), 403);
    }

    public function edit(Action $action)
    {
        abort_unless(\Gate::allows('action_edit'), 403);

        $dapertements = Dapertement::all();

        $tickets = Ticket::all();

        $staffs = Staff::all();

        return view('admin.actions.edit', compact('dapertements', 'tickets', 'staffs', 'action'));
    }

    public function update(Request $request, Action $action)
    {
        abort_unless(\Gate::allows('action_edit'), 403);

        $action->update($request->all());

        return redirect()->route('admin.actions.list', $action->ticket_id);
    }

    public function destroy(Request $request, Action $action)
    {
        abort_unless(\Gate::allows('action_delete'), 403);

        $data = [];
        foreach ($action->staff as $key => $staff) {
            $data[$key] = $staff->id;
        }

        $cek = $action->staff()->detach($data);

        $action->delete();

        return redirect()->route('admin.actions.list', $action->ticket_id);
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
        abort_unless(\Gate::allows('action_access'), 403);

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
            $actions = Action::selectRaw('DISTINCT actions.*')
                ->join('action_staff', function ($join) use ($staff) {
                    $join->on('action_staff.action_id', '=', 'actions.id')
                        ->where('action_staff.staff_id', '=', $staff);
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
            $actions = Action::with('staff')
                ->with('dapertement')
                ->with('subdapertement')
                ->with('ticket')
                // ->orderBy('dapertements.group', 'desc')
                ->where('ticket_id', $ticket_id)
                ->orderBy('start', 'desc')
                ->get();
        }

        return view('admin.actions.list', compact('actions', 'ticket_id'));
        // dd($actions);
    }

    // list pegawai
    public function actionStaff($action_id)
    {
        abort_unless(\Gate::allows('action_staff_access'), 403);

        $action = Action::findOrFail($action_id);

        // $staffs = $action->staff;

        return view('admin.actions.actionStaff', compact('action'));
    }

    // nambah staff untuk tindakan
    public function actionStaffCreate($action_id)
    {

        abort_unless(\Gate::allows('action_staff_create'), 403);

        $action = Action::findOrFail($action_id);

        $action_staffs = Action::where('id', $action_id)->with('staff')->first();

        // $staffs = Staff::selectRaw('staffs.id,staffs.code,staffs.name,staffs.phone, work_units.name as work_unit_name')
        //     ->join('dapertements', 'dapertements.id', '=', 'staffs.dapertement_id')
        //     ->leftJoin('work_units', 'staffs.work_unit_id', '=', 'work_units.id')
        //     ->where('subdapertement_id', $action->subdapertement_id)
        //     ->orderBy('work_units.serial_number', 'ASC')
        //     ->get();

        // dd($staffs);

        // $staffs = Staff::where('dapertement_id', $action->dapertement_id)->with('action')->get();

        $action_staffs_list = DB::table('staffs')
            ->join('action_staff', function ($join) {
                $join->on('action_staff.staff_id', '=', 'staffs.id')
                    ->where('action_staff.status', '!=', 'close');
            })
            ->join('actions', 'actions.id', '=', 'action_staff.action_id')
            ->where('actions.id', $action_id)
            ->get();

        $staffs = Staff::selectRaw('
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
        // dd($staffs);

        return view('admin.actions.actionStaffCreate', compact('action_id', 'staffs', 'action', 'action_staffs', 'action_staffs_list'));

        // dd($action_staffs_list);
    }

    // store pegawai untuk tindakan

    public function actionStaffStore(Request $request)
    {
        abort_unless(\Gate::allows('action_staff_create'), 403);

        $action = Action::findOrFail($request->action_id);

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
        }

        return redirect()->route('admin.actions.actionStaff', $request->action_id);
    }

    // update pegawai tindakan

    public function actionStaffEdit($action_id)
    {
        abort_unless(\Gate::allows('action_staff_edit'), 403);

        $action = Action::with('ticket')->findOrFail($action_id);
        // dd($action);
        return view('admin.actions.actionStaffEdit', compact('action'));
    }

    public function actionStaffUpdate(Request $request)
    {
        abort_unless(\Gate::allows('action_staff_edit'), 403);
        // ini_set('max_file_uploads', 50);
        $img_path = "/images/action";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';

        // dd($request->image_done);
        // upload image
        if ($request->file('image')) {
            foreach ($request->file('image') as $key => $image) {
                $resourceImage = $image;
                $nameImage = strtolower($request->action_id);
                $file_extImage = $image->extension();
                $nameImage = str_replace(" ", "-", $nameImage);
                $img_name = $img_path . "/" . $nameImage . "-" . $request->action_id . $key . "-work." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);
                $dataImageName[] = $img_name;
            }
        }

        // foto sebelum pengerjaan
        if ($request->file('image_prework')) {
            $resource_image_prework = $request->file('image_prework');
            $id_name_image_prework = strtolower($request->action_id);
            $file_ext_image_prework = $request->file('image_prework')->extension();
            $id_name_image_prework = str_replace(' ', '-', $id_name_image_prework);

            $name_image_prework = $img_path . '/' . $id_name_image_prework . '-' . $request->action_id . '-pre.' . $file_ext_image_prework;

            $resource_image_prework->move($basepath . $img_path, $name_image_prework);
            $data_image_prework = $name_image_prework;
        }

        // foto alat
        if ($request->file('image_tools')) {
            foreach ($request->file('image_tools') as $key => $image) {

                $resourceImage = $image;
                $nameImage = strtolower($request->action_id);
                $file_extImage = $resourceImage->extension();
                $nameImage = str_replace(" ", "-", $nameImage);

                $img_name = $img_path . "/" . $nameImage . "-" . $request->action_id . $key . "-tool." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);

                $dataImageNameTool[] = $img_name;
            }
        }

        if ($request->file('image_done')) {
            foreach ($request->file('image_done') as $key => $image) {
                $resourceImageDone = $image;
                $nameImageDone = strtolower($request->action_id);
                $file_extImageDone = $image->extension();
                $nameImageDone = str_replace(" ", "-", $nameImageDone);

                $img_name_done = $img_path . "/" . $nameImageDone . "-" . $request->action_id . $key . "-done." . $file_extImageDone;

                $resourceImageDone->move($basepath . $img_path, $img_name_done);

                $dataImageNameDone[] = $img_name_done;
            }
        }

        // upload image end

        $action = Action::where('id', $request->action_id)->with('ticket')->with('staff')->first();
        $cekAllStatus = false;
        $statusAction = $request->status;

        $dateNow = date('Y-m-d H:i:s');

        //check if current status is close
        $date_end = $dateNow;
        if ($action->status == 'close') {
            $date_end = $action->end;
        }

        if ($request->file('image')) {
            $dataNewAction = array(
                'status' => $statusAction,
                'image' => str_replace("\/", "/", json_encode($dataImageName)),
                'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $date_end,
                'memo' => $request->memo,
                'todo' => $request->todo,
            );
        } else {
            $dataNewAction = array(
                'status' => $statusAction,
                'end' => $statusAction == 'pending' || $statusAction == 'active' ? '' : $date_end,
                'memo' => $request->memo,
                'todo' => $request->todo,
            );
        }
        if ($request->file('image_tools')) {
            $dataNewAction = array_merge(
                $dataNewAction,
                ['image_tools' => str_replace("\/", "/", json_encode($dataImageNameTool))]
            );
        }
        if ($request->file('image_prework')) {
            $dataNewAction = array_merge(
                $dataNewAction,
                ['image_prework' => $data_image_prework]
            );
        }
        if ($request->file('image_done')) {
            $dataNewAction = array_merge(
                $dataNewAction,
                ['image_done' => str_replace("\/", "/", json_encode($dataImageNameDone))]
            );
        }

        $action->update($dataNewAction);
        //update staff
        $ids = $action->staff()->allRelatedIds();
        foreach ($ids as $sid) {
            $action->staff()->updateExistingPivot($sid, ['status' => $request->status]);
        }
        //update ticket status
        $ticket = Ticket::find($action->ticket_id);
        $ticket->status = $statusAction;
        $ticket->save();

        return redirect()->route('admin.actions.list', $ticket->id);
    }

    // editt status tindakan pegawai
    public function actionStaffDestroy($action_id, $staff_id)
    {
        abort_unless(\Gate::allows('action_staff_delete'), 403);

        $action = Action::findOrFail($action_id);

        if ($action) {
            $cek = $action->staff()->detach($staff_id);

            if ($cek) {
                $action = Action::where('id', $action_id)->with('staff')->first();

                // dd($action->staff[0]->pivot->status);
                $cekAllStatus = false;
                if (count($action->staff) > 0) {
                    $statusAction = 'close';
                } else {
                    $statusAction = $action->status;
                }
                // $statusAction = 'close';
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
        }

        return redirect()->route('admin.actions.actionStaff', $action_id);
    }


    // tambahan foto selesai start


    public function additionalDone($action_id)
    {
        abort_unless(\Gate::allows('action_staff_edit'), 403);

        $action = Action::with('ticket')->findOrFail($action_id);
        $ticket = Ticket::find($action->ticket_id);
        // dd($action);
        return view('admin.actions.additionalDone', compact('action', 'ticket'));
    }


    public function storeAdditionalDone(Request $request)
    {
        abort_unless(\Gate::allows('action_staff_edit'), 403);
        // ini_set('max_file_uploads', 50);
        $img_path = "/images/action";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';

        // dd($request->image_done);
        // upload image

        $action = Action::where('id', $request->action_id)->with('ticket')->with('staff')->first();

        $actionImgNow = json_decode($action->image_done);
        // dd(count(($actionImgNow)));

        if ($request->file('image_done')) {
            foreach ($request->file('image_done') as $key => $image) {
                $resourceImageDone = $image;
                $nameImageDone = strtolower($request->action_id);
                $file_extImageDone = $image->extension();
                $nameImageDone = str_replace(" ", "-", $nameImageDone);

                $img_name_done = $img_path . "/" . $nameImageDone . "-" . $request->action_id . (count($actionImgNow) + $key + 1) . "-done." . $file_extImageDone;

                $resourceImageDone->move($basepath . $img_path, $img_name_done);

                $dataImageNameDone[] = $img_name_done;
            }
            $newImg = array_merge($dataImageNameDone, $actionImgNow);
        }



        // upload image end


        if ($request->file('image_done')) {
            $action->update(['image_done' => str_replace("\/", "/", json_encode($newImg))]);
        }


        // return redirect()->route('admin.actions.list', $request->ticket_id);
        return redirect()->back();
    }


    // tambahan foto selesai end



    //start surya buat
    public function printservice()
    {
        return view('admin.actions.printservice');
    }

    public function printspk()
    {
        return view('admin.actions.printspk');
    }

    public function printReport()
    {
        return view('admin.actions.printreport');
    }

    public function ubahData()
    {
        $t = Action::get();
        foreach ($t as $key => $value) {
            $db = Action::where('id', $value->id)->first();
            $db->image_tools = '["' . $value->image_tools . '"]';
            $db->update();
        }
    }

    //end surya buat
}
