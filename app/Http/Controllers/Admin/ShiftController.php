<?php

namespace App\Http\Controllers\Admin;

use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Shift;
use App\ShiftStaff;
use App\Staff;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('shift_access'), 403);
        $staffs = User::get();
        $dapertements = Dapertement::get();
        $shifts = Shift::selectRaw('shifts.*, dapertements.name as dapertement_name')
            ->join('dapertements', 'dapertements.id', '=', 'shifts.dapertement_id')->get();
        $qry = ShiftStaff::selectRaw('shifts.*, users.name as staff_name, shifts.title as shift_title, dapertements.name as dapertement_name, shift_staff.date as shift_date, shift_staff.id as id')
            ->join('users', 'shift_staff.staff_id', '=', 'users.id')
            ->join('shifts', 'shift_staff.shift_id', '=', 'shifts.id')
            ->join('dapertements', 'dapertements.id', '=', 'shifts.dapertement_id')
            ->FilterStaff($request->staff_id)
            ->FilterDapertement($request->dapertement_id)
            ->FilterShift($request->shift_id);
        // dd($qry->get());
        if ($request->ajax()) {
            //set query
            $table = Datatables::of($qry);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = '';
                $editGate = 'shift_edit';
                $deleteGate = '';
                $crudRoutePart = 'shift';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });
            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : "";
            });
            $table->editColumn('staff_name', function ($row) {
                return $row->staff_name ? $row->staff_name : "";
            });
            $table->editColumn('shift_title', function ($row) {
                return $row->shift_title ? $row->shift_title : "";
            });
            $table->editColumn('dapertement_name', function ($row) {
                return $row->dapertement_name ? $row->dapertement_name : "";
            });
            $table->editColumn('shift_date', function ($row) {
                return $row->shift_date ? $row->shift_date : "";
            });
            $table->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at : "";
            });
            $table->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        return view('admin.shift.index', compact('shifts', 'dapertements', 'staffs'));
    }
    function countDays($year, $month, $ignore)
    {
        $count = 0;
        $counter = mktime(0, 0, 0, $month, 1, $year);
        while (date("n", $counter) == $month) {
            if (in_array(date("w", $counter), $ignore) == false) {
                $count++;
            }
            $counter = strtotime("+1 day", $counter);
        }
        return  $count;
    }

    public function create()
    {
        abort_unless(\Gate::allows('shift_create'), 403);
        $dapertement_id = 1;
        $shifts = Shift::selectRaw('shifts.*, dapertements.name as dapertement_name')
            ->join('dapertements', 'dapertements.id', '=', 'shifts.dapertement_id')
            ->where('dapertement_id', $dapertement_id)->get();
        $users = User::orderBy('name')->get();

        return view('admin.shift.create', compact('shifts', 'users'));
    }
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('shift_create'), 403);
        $d = ShiftStaff::create($request->all());
        return redirect()->route('admin.shift.index');
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('shift_edit'), 403);
        $shift_staff = ShiftStaff::where('id', $id)->first();
        $dapertement_id = 1;
        $shifts = Shift::selectRaw('shifts.*, dapertements.name as dapertement_name')
            ->join('dapertements', 'dapertements.id', '=', 'shifts.dapertement_id')
            ->where('dapertement_id', $dapertement_id)->get();
        $users = User::orderBy('name')->get();

        return view('admin.shift.edit', compact('shifts', 'users', 'shift_staff'));
    }
    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('shift_edit'), 403);
        $d = ShiftStaff::where('id', $id)
            ->update($request->except(['_token', '_method']));
        return redirect()->route('admin.shift.index');
    }
    public function destroy($id)
    {
        abort_unless(\Gate::allows('shift_delete'), 403);
        ShiftStaff::where('id', $id)->delete();
        return redirect()->back();
    }
}
