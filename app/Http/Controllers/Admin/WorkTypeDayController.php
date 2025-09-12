<?php

namespace App\Http\Controllers\Admin;

use App\Absence;
use App\Absence_categories;
use App\Day;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\TraitModel;
use App\WorkTypeDays;
use App\WorkTypes;

class WorkTypeDayController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        $work_type_days = Day::selectRaw('
        days.*,
        in.id,
        in.time as time_in,
        out.time as time_out,
        breakin.time as time_breakin,
        breakout.time as time_breakout
        ')
            // ->join('work_type_days', 'work_type_days.day_id', '=', 'days.id')
            // ->join('absence_categories', 'work_type_days.absence_category_id', '=', 'absence_categories.id')
            ->join(
                'work_type_days as in',
                function ($join) use ($request) {
                    $join->on('days.id', '=', 'in.day_id')
                        ->join('absence_categories as c_in', 'in.absence_category_id', '=', 'c_in.id')
                        ->where('c_in.type', 'presence')
                        ->where('in.work_type_id', $request->id)
                        ->where('c_in.queue', '1');
                }
            )
            ->leftJoin(
                'work_type_days as out',
                function ($join) use ($request) {
                    $join->on('days.id', '=', 'out.day_id')
                        ->join('absence_categories as c_out', 'out.absence_category_id', '=', 'c_out.id')
                        ->where('c_out.type', 'presence')
                        ->where('out.work_type_id', $request->id)
                        ->where('c_out.queue', '2');
                }
            )
            ->join(
                'work_type_days as breakin',
                function ($join) use ($request) {
                    $join->on('days.id', '=', 'breakin.day_id')
                        ->join('absence_categories as c_breakin', 'breakin.absence_category_id', '=', 'c_breakin.id')
                        ->where('c_breakin.type', 'break')
                        ->where('breakin.work_type_id', $request->id)
                        ->where('c_breakin.queue', '1');
                }
            )
            ->join(
                'work_type_days as breakout',
                function ($join) use ($request) {
                    $join->on('days.id', '=', 'breakout.day_id')
                        ->join('absence_categories as c_breakout', 'breakout.absence_category_id', '=', 'c_breakout.id')
                        ->where('c_breakout.type', 'break')
                        ->where('breakout.work_type_id', $request->id)
                        ->where('c_breakout.queue', '2');
                }
            )

            ->orderBy('days.id')
            ->groupBy('days.id')
            ->get();
        // dd($work_type_days);
        $work_type_id = $request->id;
        $work_type_title = WorkTypes::where('id', $request->id)->first()->title;
        // dd($work_type_title);
        return view('admin.work_type_day.index', compact('work_type_days', 'work_type_id', 'work_type_title'));
    }
    public function create(Request $request)
    {
        // $last_code = $this->get_last_code('work_type_day');

        // $code = acc_code_generate($last_code, 8, 3);

        $days = Day::select('days.*')->leftJoin(
            'work_type_days',
            function ($join) use ($request) {
                $join->on('days.id', '=', 'work_type_days.day_id')
                    ->where('work_type_id', $request->work_type_id);
            }
        )


            // ->where('work_type_days.day_id', '=', null)

            ->where('work_type_days.day_id', '=', null)->get();
        // dd($days);

        $work_type_id = $request->work_type_id;

        return view('admin.work_type_day.create', compact('days', 'work_type_id'));
    }
    public function store(Request $request)
    {
        // $data = array_merge($request->all());
        $presence = Absence_categories::where('type', 'presence')->get();
        // dd($presence);
        foreach ($presence as $p) {
            $data = [
                'day_id' => $request->day_id,
                'absence_category_id' => $p->id,
                'time' => $p->queue == 1 ? '07:30:00' : '15:30:00',
                'duration' => $p->queue == 1 ? 8 : 0,
                'duration_exp' => 1,
                'work_type_id' => $request->work_type_id,
            ];
            $work_type_day =  WorkTypeDays::create($data);
        }

        $break = Absence_categories::where('type', 'break')->get();
        foreach ($break as $b) {
            $data = [
                'day_id' => $request->day_id,
                'absence_category_id' => $b->id,
                'time' => '00:00:00',
                'duration' => 8,
                'duration_exp' => 0,
                'work_type_id' => $request->work_type_id,
            ];
            $work_type_day =  WorkTypeDays::create($data);
        }

        return redirect()->route('admin.work_type_day.index', ['id' => $work_type_day->work_type_id]);
    }
    public function show($id)
    {
        abort_unless(\Gate::allows('permission_show'), 403);
        $work_type_day = WorkTypeDays::where('work_type_days.id', $id)->first();
        return view('admin..work_type_day.show', compact('work_type_day'));
    }
    public function edit($id)
    {
        // dd($id);
        $work_type_day = WorkTypeDays::where('id', $id)->first();
        // $dapertements = Dapertement::all();
        // $subdapertements = Subdapertement::where('dapertement_id', $work_type_day->dapertement_id)->get();

        return view('admin.work_type_day.edit', compact('work_type_day'));
    }
    public function update($id, Request $request)
    {
        $work_type_day = WorkTypeDays::where('id', $id)->first();




        $work_type_days = WorkTypeDays::selectRaw('work_type_days.id as id, absence_categories.type, absence_categories.queue ')
            ->where('work_type_id', $work_type_day->work_type_id)
            ->join('absence_categories', 'absence_categories.id', '=', 'work_type_days.absence_category_id')
            ->where('day_id', $work_type_day->day_id)->get();
        // dd($work_type_days);

        foreach ($work_type_days as $d) {
            if ($d->type == 'presence' && $d->queue == '1') {
                $update = WorkTypeDays::where('id', $d->id)->first();
                $update->update([
                    'duration' => $request->duration,
                    'duration_exp' =>  $request->duration_exp,
                    'time' => $request->time
                ]);
                // dd($update);
            } else if ($d->type == 'presence' && $d->queue == '2') {
                $update = WorkTypeDays::where('id', $d->id)->first();

                $time = date("H:i:s", strtotime('+' . ($request->duration) . ' hours', strtotime(date('Y-m-d ' . $request->time))));
                $update->update([
                    'duration' => 0,
                    'duration_exp' => $request->duration_exp,
                    'time' => $time
                ]);
                // dd($update);
            } else {
                $update = WorkTypeDays::where('id', $d->id)->first();
                $update->update([
                    'duration' => $request->duration,
                    'duration_exp' => $request->duration_exp,
                ]);
            }
        }
        $work_type_day = WorkTypeDays::where('id', $id)->first();
        return redirect()->route('admin.work_type_day.index', ['id' => $work_type_day->work_type_id]);
    }
    public function destroy($id)
    {
        $work_type_day = WorkTypeDays::where('id', $id)->first();
        $work_type_days = WorkTypeDays::where('work_type_id', $work_type_day->work_type_id)
            ->where('day_id', $work_type_day->day_id);
        $work_type_days->delete();
        return back();
    }
    public function massDestroy(Request $request)
    {
        WorkTypeDays::whereIn('id', request('ids'))->delete();
        return response(null, 204);
    }
}
