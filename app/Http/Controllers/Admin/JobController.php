<?php

namespace App\Http\Controllers\Admin;

use App\Dapertement;
use App\Http\Controllers\Controller;
use App\Job;
use App\Subdapertement;
// use App\Http\Requests\MassDestroyWaCategoryRequest;
use App\Traits\TraitModel;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use TraitModel;

    public function index()
    {
        $jobs = Job::selectRaw('jobs.id,jobs.name, dapertements.name as dapertement_name, subdapertements.name as subdapertement_name')
            ->join('dapertements', 'jobs.dapertement_id', '=', 'dapertements.id')
            ->join('subdapertements', 'jobs.subdapertement_id', '=', 'subdapertements.id')
            ->get();
        return view('admin.job.index', compact('jobs'));
    }
    public function create()
    {
        $last_code = $this->get_last_code('job');

        $code = acc_code_generate($last_code, 8, 3);

        $departementlist = Dapertement::all();

        return view('admin.job.create', compact('code', 'departementlist'));
    }
    public function store(Request $request)
    {
        $data = array_merge($request->all());
        Job::create($data);
        return redirect()->route('admin.job.index');
    }
    public function show($id)
    {
        abort_unless(\Gate::allows('permission_show'), 403);
        $job = Job::selectRaw('jobs.id,jobs.name, dapertements.name as dapertement_name, subdapertements.name as subdapertement_name')
            ->join('dapertements', 'jobs.dapertement_id', '=', 'dapertements.id')
            ->join('subdapertements', 'jobs.subdapertement_id', '=', 'subdapertements.id')
            ->where('jobs.id', $id)->first();
        return view('admin..job.show', compact('job'));
    }
    public function edit($id)
    {
        // dd($id);
        $job = Job::where('id', $id)->first();
        $dapertements = Dapertement::all();
        $subdapertements = Subdapertement::where('dapertement_id', $job->dapertement_id)->get();

        return view('admin.job.edit', compact('job', 'dapertements', 'subdapertements'));
    }
    public function update($id, Request $request)
    {
        $job = Job::where('id', $id)->first();
        $job->update($request->all());
        return redirect()->route('admin.job.index');
    }
    public function destroy($id)
    {
        $job = Job::where('id', $id)->first();
        $job->delete();
        return back();
    }
    public function massDestroy(Request $request)
    {
        Job::whereIn('id', request('ids'))->delete();
        return response(null, 204);
    }
}
