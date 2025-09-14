<?php

namespace App\Http\Controllers\Admin;

use App\Absence;
use App\AbsenceLog;
use App\CtmPbk;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Staff;
use App\CtmWilayah;
use App\Job;
use App\ShiftPlannerStaff;
use App\ShiftPlannerStaffs;
use App\Subdapertement;
use App\Traits\TraitModel;
use App\User;
use App\WorkTypes;
use App\WorkUnit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class StaffsController extends Controller
{
    use TraitModel;

    public function getStaff(Request $request)
    {
        $staffs = Staff::where('subdapertement_id', $request->subdapertement_id)
            ->pluck('name', 'id');

        return response()->json($staffs);
    }

    public function getSubdapertement(Request $request)
    {
        $subdapertements = Subdapertement::where('dapertement_id', $request->dapertement_id)
            ->pluck('name', 'id');

        return response()->json($subdapertements);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $checker = [];
            $users = User::with(['roles'])
                ->where('id', Auth::user()->id)
                ->first();
            foreach ($users->roles as $data) {
                foreach ($data->permissions as $data2) {
                    $checker[] = $data2->title;
                }
            }

            //user role
            $user_id = Auth::check() ? Auth::user()->id : null;
            $department = '';
            $subdepartment = 0;
            $staff = 0;
            if (isset($user_id) && $user_id != '') {
                $admin = User::with('roles')->find($user_id);
                $role = $admin->roles[0];
                $role->load('permissions');
                $permission = json_decode($role->permissions->pluck('title'));
                if (!in_array("ticket_all_access", $permission) && !in_array('absence_all_access', $checker)) {
                    $department = $admin->dapertement_id;
                    $subdepartment = $admin->subdapertement_id;
                    $staff = $admin->staff_id;
                }
            }
            //set query

            if (in_array('absence_all_access', $checker)) {
                $qry = Staff::with('dapertement')->with('subdapertement')->FilterDapertement($department)->get();
            } else if (request()->input('dapertement_id') != "") {
                $dapertement_id = request()->input('dapertement_id');

                $qry = Staff::where('dapertement_id', $dapertement_id)->with('dapertement')->with('subdapertement')->FilterDapertement($department);
            } else {
                $qry = Staff::with('dapertement')->with('subdapertement')->FilterDapertement($department)->get();
            }

            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = 'staff_edit';
                $deleteGate = 'staff_delete';
                $crudRoutePart = 'staffs';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('dapertement', function ($row) {
                return $row->dapertement ? $row->dapertement->name : "";
            });

            $table->editColumn('subdapertement', function ($row) {
                return $row->subdapertement ? $row->subdapertement->name : "";
            });

            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });
            $table->editColumn('NIK', function ($row) {
                return $row->NIK ? $row->NIK : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }

        $dapertements = Dapertement::all();

        return view('admin.staffs.index', compact('dapertements'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('staff');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('staff_create'), 403);



        $checker = [];
        $users = User::with(['roles'])
            ->where('id', Auth::user()->id)
            ->first();
        foreach ($users->roles as $data) {
            foreach ($data->permissions as $data2) {
                $checker[] = $data2->title;
            }
        }
        //user role
        $area = CtmWilayah::select('id as code', 'NamaWilayah')->get();

        $work_units = WorkUnit::get();

        $user_id = Auth::check() ? Auth::user()->id : null;
        $pbks = CtmPbk::get();
        $department = '';
        $subdepartment = 0;
        $staff = 0;
        if (isset($user_id) && $user_id != '') {
            $admin = User::with('roles')->find($user_id);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("ticket_all_access", $permission) && !in_array('absence_all_access', $checker)) {
                $department = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
                $staff = $admin->staff_id;
            }
        }
        // dd()
        if ($department === 5 && $subdepartment === 0) {
            $dapertements = Dapertement::all();
        } else if ($department > 0) {
            $dapertements = Dapertement::where('id', $department)->get();
        } else {
            $dapertements = Dapertement::all();
        }


        // if (in_array('absence_all_access', $checker)) {
        //     $qry = Staff::with('dapertement')->with('subdapertement')->FilterDapertement($department)->get();
        // }

        $work_types = WorkTypes::get();
        $jobs = Job::get();

        return view('admin.staffs.create', compact('dapertements', 'jobs', 'code', 'area', 'work_types', 'work_units', 'pbks'));
    }

    public function store(StoreStaffRequest $request)
    {
        $validated = $request->validate([
            'phone' => 'required|unique:staffs|max:255',
            'NIK' => 'required|unique:staffs|max:255',
            // 'body' => 'required',
        ]);
        $staff = Staff::create($request->all());
        $areas = $request->input('area', []);
        for ($area = 0; $area < count($areas); $area++) {
            $staff->area()->attach($areas[$area]);
        }
        return redirect()->route('admin.staffs.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('staff_edit'), 403);
        $area = CtmWilayah::select('id as code', 'NamaWilayah')->get();
        // $staff = Staff::findOrFail($id);
        $staff = Staff::where('id', $id)->with('area')->first();
        $work_units = WorkUnit::get();
        $pbks = CtmPbk::get();

        $checker = [];
        $users = User::with(['roles'])
            ->where('id', Auth::user()->id)
            ->first();
        foreach ($users->roles as $data) {
            foreach ($data->permissions as $data2) {
                $checker[] = $data2->title;
            }
        }
        //user role
        $user_id = Auth::check() ? Auth::user()->id : null;
        $department = '';
        $subdepartment = 0;
        if (isset($user_id) && $user_id != '') {
            $admin = User::with('roles')->find($user_id);
            $role = $admin->roles[0];
            $role->load('permissions');
            $permission = json_decode($role->permissions->pluck('title'));
            if (!in_array("ticket_all_access", $permission) && !in_array('absence_all_access', $checker)) {
                $department = $admin->dapertement_id;
                $subdepartment = $admin->subdapertement_id;
            }
        }
        if ($department === 5 && $subdepartment === 0) {
            $dapertements = Dapertement::all();
        } else if ($department > 0) {
            $dapertements = Dapertement::where('id', $department)->get();
        } else {
            $dapertements = Dapertement::all();
        }

        $work_types = WorkTypes::get();
        $jobs = Job::get();

        $subdapertements = Subdapertement::where('dapertement_id', $staff->dapertement_id)->get();
        return view('admin.staffs.edit', compact('staff', 'jobs', 'work_types', 'dapertements', 'subdapertements', 'area', 'work_units', 'pbks'));
    }

    public function update(UpdateStaffRequest $request, Staff $staff)
    {
        abort_unless(\Gate::allows('staff_edit'), 403);

        $validated = $request->validate([
            'phone' => 'required|unique:staffs,phone,' . $staff->id . ',id',
            'NIK' => 'required|unique:staffs,NIK,' . $staff->id . ',id',
            // 'body' => 'required',
        ]);
        if ($staff->work_type_id != $request->work_type_id) {
            // dd("halo");
            // $shift = ShiftPlannerStaffs::where('staff_id', $staff->id)
            //     // ->whereDate('start', '>=', date('Y-m-d'))
            //     ->get();

            // dd($absence);
            if ($staff->work_type_id == "2") {
                ShiftPlannerStaffs::where('staff_id', $staff->id)
                    ->whereDate('start', '>=', date('Y-m-d'))->delete();
            }

            $absence = Absence::join('absence_logs', 'absence_logs.absence_id', '=', 'absences.id')
                ->whereDate('absences.created_at', date('Y-m-d'))
                ->where('register', '=', null)
                ->where('absence_category_id', 1)
                ->where('staff_id', $staff->id)->first();
            if ($absence) {

                AbsenceLog::where('absence_id', $absence->absence_id)->delete();
                Absence::where('id', $absence->absence_id)->delete();
            }
        } else {
            // dd("jjj");
        }
        // dd($shift);


        $staff->update($request->all());
        $areas = $request->input('area', []);
        $staff->area()->detach();
        for ($area = 0; $area < count($areas); $area++) {
            $staff->area()->attach($areas[$area]);
        }
        return redirect()->route('admin.staffs.index');
    }

    public function destroy(Staff $staff)
    {
        abort_unless(\Gate::allows('staff_delete'), 403);

        try {
            $staff->area()->detach();
            $staff->delete();
        } catch (QueryException $e) {
            return back()->withErrors(['Pegawai masih terdaftar dalam data Tiket']);
        }

        return back();
    }

    public function massDestroy()
    {
        # code...
    }
}
