<?php

namespace App\Http\Controllers\Admin;

use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Staff;
use App\StaffSpecial;
use Illuminate\Http\Request;

class StaffSpecialController extends Controller
{
    public function index()
    {
        // $data = [
        //     'message' => "1"
        // ];
        // WaTemplate::create($data);
        abort_unless(\Gate::allows('absence_all_access'), 403);

        $dapertements = Dapertement::get();
        $staffSpecials = StaffSpecial::selectRaw('staffs.*, staff_specials.*')
            ->join('staffs', 'staff_specials.staff_id', '=', 'staffs.id')
            ->get();

        return view('admin.staff_specials.index', compact('staffSpecials', 'dapertements'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);

        $staffs = Staff::orderBy('name')->get();

        return view('admin.staff_specials.create', compact('staffs'));
    }

    public function store(Request $request)
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);

        $validated = $request->validate([
            'staff_id' => 'required|unique:staff_specials|max:255'
            // 'body' => 'required',
        ]);

        $staffSpecial = StaffSpecial::create($request->all());

        return redirect()->route('admin.staffSpecials.index');
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);

        $staffSpecial = StaffSpecial::where('id', $id)->first();
        // dd($staffSpecial);

        return view('admin.staff_specials.edit', compact('staffSpecial'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);

        $staffSpecial = StaffSpecial::where('id', $id);
        $staffSpecial->update($request->except(['_token', '_method']));

        return redirect()->route('admin.staffSpecials.index');
    }

    public function show($id)
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);
        $staffSpecial = StaffSpecial::where('id', $id)->first();

        return view('admin.staffs.show', compact('staff'));
    }

    public function destroy($id)
    {
        abort_unless(\Gate::allows('absence_all_access'), 403);
        $staffSpecial = StaffSpecial::where('id', $id);

        $staffSpecial->delete();

        return back();
    }

    public function massDestroy(MassDestroystaffRequest $request)
    {
        // dd(request('ids'));
        $data = [
            'message' => request('ids')
        ];
        foreach (request('ids') as $key) {

            WaTemplate::create(['message' => $key]);
        }

        return response()->json(['success' => 'Got Simple Ajax Request.']);
    }
}
