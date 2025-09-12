<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use App\WorkUnit;
use Illuminate\Http\Request;

class WorkUnitController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('workUnit_access'), 403);
        $work_units = WorkUnit::get();
        return view('admin.workUnit.index', compact('work_units'));
    }
    public function create()
    {
        abort_unless(\Gate::allows('workUnit_create'), 403);
        $last_code = $this->get_last_code('workUnit');

        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.workUnit.create', compact('code'));
    }
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('workUnit_create'), 403);
        $data = array_merge($request->all());
        WorkUnit::create($data);
        return redirect()->route('admin.workUnit.index');
    }
    public function show($id)
    {
        abort_unless(\Gate::allows('workUnit_show'), 403);
        // abort_unless(\Gate::allows('permission_show'), 403);
        $work_unit = WorkUnit::where('id', $id)->first();
        return view('admin.workUnit.show', compact('work_unit'));
    }
    public function edit($id)
    {
        // dd($id);
        abort_unless(\Gate::allows('workUnit_edit'), 403);
        $work_unit = WorkUnit::where('id', $id)->first();
        return view('admin.workUnit.edit', compact('work_unit'));
    }
    public function update($id, Request $request)
    {
        abort_unless(\Gate::allows('workUnit_edit'), 403);
        $work_unit = WorkUnit::where('id', $id)->first();
        $work_unit->update($request->all());
        return redirect()->route('admin.workUnit.index');
    }
    public function destroy($id)
    {
        abort_unless(\Gate::allows('workUnit_delete'), 403);
        $work_unit = WorkUnit::where('id', $id)->first();
        $work_unit->delete();
        return back();
    }
    // public function massDestroy(MassDestroyWaCategoryRequest $request)
    // {
    //     WorkUnit ::whereIn('id', request('ids'))->delete();
    //     return response(null, 204);
    // }


}
