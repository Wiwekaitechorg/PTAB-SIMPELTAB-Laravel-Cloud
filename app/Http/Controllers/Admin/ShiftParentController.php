<?php

namespace App\Http\Controllers\Admin;

use App\Absence;
use App\Absence_categories;
use App\Dapertement;
use App\Day;
use App\Http\Controllers\Controller;
use App\Job;
use App\ShiftGroups;
use Illuminate\Http\Request;
use App\Traits\TraitModel;
use App\ShiftParent;
use App\Subdapertement;
use App\User;
use App\WorkUnit;
use Illuminate\Support\Facades\Auth;

class ShiftParentController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        $subdapertement = Auth::user()->subdapertement_id != '0' ? Auth::user()->subdapertement_id : '';
        $checker = [];
        $users = User::with(['roles'])
            ->where('id', Auth::user()->id)
            ->first();
        foreach ($users->roles as $data) {
            foreach ($data->permissions as $data2) {
                $checker[] = $data2->title;
            }
        }
        if (!in_array('absence_all_access', $checker)) {
            $shift_parents = ShiftParent::selectRaw('shift_parents.*, dapertements.name as dapertement_name, jobs.name as job_name, work_units.name as work_unit_name')
                ->leftJoin('jobs', 'jobs.id', '=', 'shift_parents.job_id')
                ->leftJoin('dapertements', 'dapertements.id', '=', 'shift_parents.dapertement_id')
                ->leftJoin('work_units', 'work_units.id', '=', 'shift_parents.work_unit_id')
                ->where('dapertements.id', Auth::user()->dapertement_id)
                ->orderBy('updated_at');

            if ($subdapertement != '') {

                $shift_parents = $shift_parents->where('shift_parents.subdapertement_id', Auth::user()->subdapertement_id);
            }
            $shift_parents =  $shift_parents->get();
        } else {
            $shift_parents = ShiftParent::selectRaw('shift_parents.*, dapertements.name as dapertement_name, jobs.name as job_name, work_units.name as work_unit_name')
                ->leftJoin('jobs', 'jobs.id', '=', 'shift_parents.job_id')
                ->leftJoin('dapertements', 'dapertements.id', '=', 'shift_parents.dapertement_id')
                ->leftJoin('work_units', 'work_units.id', '=', 'shift_parents.work_unit_id')
                ->orderBy('updated_at')
                ->get();
        }
        return view('admin.shift_parent.index', compact('shift_parents'));
    }
    public function create()
    {
        // $last_code = $this->get_last_code('shift_parent');

        $dapertements = Dapertement::all();
        $work_units = WorkUnit::all();
        $jobs = Job::all();

        return view('admin.shift_parent.create', compact('dapertements', 'work_units', 'jobs'));
    }
    public function store(Request $request)
    {
        $data = array_merge($request->all());

        ShiftParent::create($data);

        return redirect()->route('admin.shift_parent.index');
    }
    public function show($id)
    {
        abort_unless(\Gate::allows('permission_show'), 403);
        $shift_parent = ShiftParent::where('shift_parent.id', $id)->first();
        return view('admin.shift_parent.show', compact('shift_parent'));
    }
    public function edit($id)
    {
        // dd($id);
        $dapertements = Dapertement::all();
        $work_units = WorkUnit::all();
        $jobs = Job::all();
        $shift_parent = ShiftParent::where('id', $id)->first();
        $subdapertements = Subdapertement::where('dapertement_id',  $shift_parent->dapertement_id)->get();
        // $dapertements = Dapertement::all();
        // $subdapertements = Subdapertement::where('dapertement_id', $shift_parent->dapertement_id)->get();

        return view('admin.shift_parent.edit', compact('shift_parent', 'dapertements', 'work_units', 'jobs', 'subdapertements'));
    }
    public function update($id, Request $request)
    {
        $shift_parent = ShiftParent::where('id', $id)->first();
        $shift_parent->update($request->all());
        // $shift_group = ShiftGroups::where('shift_parent_id')->get();
        // foreach ($shift_group as $value) {
        # code...
        ShiftGroups::where('shift_parent_id', $id)->update([
            'subdapertement_id' => $request->subdapertement_id,
            'work_unit_id' => $request->work_unit_id,
            'job_id' => $request->job_id,
        ]);
        // }
        return redirect()->route('admin.shift_parent.index', ['id' => $shift_parent->work_type_id]);
    }
    public function destroy($id)
    {
        $shift_parent = ShiftParent::where('id', $id)->first();
        $shift_parent = ShiftParent::where('work_type_id', $shift_parent->work_type_id)
            ->where('day_id', $shift_parent->day_id);
        $shift_parent->delete();
        return back();
    }
    public function massDestroy(Request $request)
    {
        ShiftParent::whereIn('id', request('ids'))->delete();
        return response(null, 204);
    }
}
