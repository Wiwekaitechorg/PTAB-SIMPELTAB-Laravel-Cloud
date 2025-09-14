<?php

namespace App\Http\Controllers\Admin;

use App\Absence_categories;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Job;
use App\Shift;
use App\Subdapertement;
// use App\Http\Requests\MassDestroyWaCategoryRequest;
use App\Traits\TraitModel;
use App\ShiftGroups;
use App\ShiftGroupTimesheets;
use App\ShiftParent;
use App\ShiftPlannerStaffs;
use App\WorkTypes;
use App\WorkUnit;
use Illuminate\Http\Request;


class ShiftGroupController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        $shift_parent_id = $request->id;
        $shift_groups = ShiftGroups::where('shift_parent_id', $request->id)->get();
        // dd($shift_groups);
        return view('admin.shift_group.index', compact('shift_groups', 'shift_parent_id'));
    }
    public function create(Request $request)
    {
        $last_code = $this->get_last_code('shift_group');

        $code = acc_code_generate($last_code, 8, 3);

        $shift_group = ShiftGroups::where('shift_parent_id', $request->shift_parent_id)->orderBy('queue', 'DESC')->first();
        if ($shift_group) {
            $queue = $shift_group->queue + 1;
            // dd($queue);
        } else {
            $queue = 1;
            // dd($queue);
        }

        $departementlist = Dapertement::all();
        $jobs = Job::get();
        $work_units = WorkUnit::get();
        $shift_parent_id = $request->shift_parent_id;


        return view('admin.shift_group.create', compact('queue', 'code', 'departementlist', 'jobs', 'work_units', 'shift_parent_id'));
    }
    public function store(Request $request)
    {
        $shift_parent = ShiftParent::where('id', $request->shift_parent_id)->first();
        // dd($shift_parent);
        $data = array_merge($request->all(), ['dapertement_id' => $shift_parent->dapertement_id, 'job_id' => $shift_parent->job_id, 'shift_parent_id' => $shift_parent->id]);
        // dd($data);
        $shift_group = ShiftGroups::create($data);
        $presence = Absence_categories::where('type', 'presence')->get();
        $break = Absence_categories::where('type', 'break')->get();
        foreach ($presence as $item) {
            ShiftGroupTimesheets::create([
                'shift_group_id' => $shift_group->id,
                'absence_category_id' => $item->id,
                'time' => '00:00:00',
                'start' => '00:00:00',
                'end' => '00:00:00',
                'duration' => '0',
            ]);
        }

        foreach ($break as $item) {
            ShiftGroupTimesheets::create([
                'shift_group_id' => $shift_group->id,
                'absence_category_id' => $item->id,
                'time' => '00:00:00',
                'start' => '00:00:00',
                'end' => '00:00:00',
                'duration' => '0',
            ]);
        }
        // dd($data->shift_parent_id);

        return redirect()->route('admin.shift_group.index', ["id" => $data['shift_parent_id']]);
    }
    public function show($id)
    {
        abort_unless(\Gate::allows('permission_show'), 403);
        $shift_group = ShiftGroups::where('shift_groups.id', $id)->first();
        return view('admin..shift_group.show', compact('shift_group'));
    }
    public function edit($id)
    {
        // dd($id);
        $shift_group = ShiftGroups::where('id', $id)->first();
        $dapertements = Dapertement::all();
        $subdapertements = Subdapertement::where('dapertement_id', $shift_group->dapertement_id)->get();

        return view('admin.shift_group.edit', compact('shift_group', 'dapertements', 'subdapertements'));
    }
    public function update($id, Request $request)
    {
        $shift_group = ShiftGroups::where('id', $id)->first();
        $shift_group->update($request->all());
        return redirect()->route('admin.shift_group.index', ["id" => $shift_group->shift_parent_id]);
    }
    public function destroy($id)
    {
        $shift_group_timesheets = ShiftGroupTimesheets::where('shift_group_id', $id)->delete();
        $shift_planner_staff = ShiftPlannerStaffs::where('shift_group_id', $id)->delete();
        $shift_group = ShiftGroups::where('id', $id)->first();
        $shift_group->delete();
        return back();
    }
    public function massDestroy(Request $request)
    {
        ShiftGroups::whereIn('id', request('ids'))->delete();
        return response(null, 204);
    }

    // schedule
    public function schedule($id)
    {
        $schedules = ShiftGroupTimesheets::selectRaw('shift_group_timesheets.*, absence_categories.title, absence_categories.type, absence_categories.queue')
            ->join('absence_categories', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
            ->where('shift_group_id', $id)->get();
        return view('admin.shift_group.schedule', compact('schedules'));
        // dd($shift_group_timesheets);
    }

    // schedule
    public function scheduleEdit($id)
    {
        $schedule = ShiftGroupTimesheets::selectRaw('shift_group_timesheets.*, absence_categories.title, absence_categories.type')
            ->join('absence_categories', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
            ->where('shift_group_timesheets.id', $id)->first();
        // dd($schedule);
        return view('admin.shift_group.scheduleEdit', compact('schedule'));
        // dd($shift_group_timesheets);
    }
    // schedule
    public function scheduleUpdate($id, Request $request)
    {
        $schedule = ShiftGroupTimesheets::where('id', $id)->first();
        // $schedule->update($request->all());
        // dd($schedule);

        $shift_group_timesheets = ShiftGroupTimesheets::selectRaw('shift_group_timesheets.id as id, absence_categories.type, absence_categories.queue ')
            ->where('shift_group_id', $schedule->shift_group_id)
            ->join('absence_categories', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
            ->get();
        // dd($shift_group_timesheets);

        foreach ($shift_group_timesheets as $d) {
            if ($d->type == 'presence' && $d->queue == '1') {
                $update = ShiftGroupTimesheets::where('id', $d->id)->first();
                $update->update([
                    'duration' => $request->duration,
                    'duration_exp' =>  $request->duration_exp,
                    'time' => $request->time
                ]);
                // dd($update);
            } else if ($d->type == 'presence' && $d->queue == '2') {
                $update = ShiftGroupTimesheets::where('id', $d->id)->first();

                $time = date("H:i:s", strtotime('+' . ($request->duration) . ' hours', strtotime(date('Y-m-d ' . $request->time))));
                $update->update([
                    'duration' => 0,
                    'duration_exp' => $request->duration_exp,
                    'time' => $time
                ]);
                // dd($update);
            } else {
                $update = ShiftGroupTimesheets::where('id', $d->id)->first();
                $update->update([
                    'duration' => $request->duration,
                    'duration_exp' => $request->duration_exp,
                ]);
            }
        }

        $schedule = ShiftGroupTimesheets::where('id', $id)->first();


        return redirect()->route('admin.shift_group.schedule', [$schedule->shift_group_id]);
        // dd($shift_group_timesheets);
    }
}
