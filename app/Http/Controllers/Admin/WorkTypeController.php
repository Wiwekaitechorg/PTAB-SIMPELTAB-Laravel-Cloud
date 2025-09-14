<?php

namespace App\Http\Controllers\Admin;

use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Job;
use App\ShiftGroups;
use App\Subdapertement;
// use App\Http\Requests\MassDestroyWaCategoryRequest;
use App\Traits\TraitModel;
use App\WorkTypeDays;
use App\WorkTypes;
use App\WorkUnit;
use Illuminate\Http\Request;

class WorkTypeController extends Controller
{
    use TraitModel;

    public function index()
    {
        $work_types = WorkTypes::where('type', 'reguler')->get();
        return view('admin.work_type.index', compact('work_types'));
    }
    public function create()
    {
        $last_code = $this->get_last_code('work_type');

        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.work_type.create', compact('code'));
    }
    public function store(Request $request)
    {
        $data = array_merge($request->all());
        WorkTypes::create($data);
        return redirect()->route('admin.work_type.index');
    }
    public function show($id)
    {
        abort_unless(\Gate::allows('permission_show'), 403);
        $work_type = WorkTypes::where('work_types.id', $id)->first();
        return view('admin..work_type.show', compact('work_type'));
    }
    public function edit($id)
    {
        // dd($id);
        $work_type = WorkTypes::where('id', $id)->first();
        $dapertements = Dapertement::all();
        $subdapertements = Subdapertement::where('dapertement_id', $work_type->dapertement_id)->get();

        return view('admin.work_type.edit', compact('work_type', 'dapertements', 'subdapertements'));
    }
    public function update($id, Request $request)
    {
        $work_type = WorkTypes::where('id', $id)->first();
        $work_type->update($request->all());
        return redirect()->route('admin.work_type.index');
    }
    public function destroy($id)
    {

        $work_type = WorkTypes::where('id', $id)->first();
        $shift_group = ShiftGroups::where('work_type_id', $work_type->id)->first();
        $work_type_day = WorkTypeDays::where('work_type_id', $work_type->id)->first();
        if ($shift_group) {
            dd('Hapus Data Shift Group Terlebih dahulu');
        } else if ($work_type_day) {
            dd('Hapus data jadwal terlebih dahulu');
        }
        $work_type->delete();
        return back();
    }
    public function massDestroy(Request $request)
    {
        WorkTypes::whereIn('id', request('ids'))->delete();
        return response(null, 204);
    }
}
