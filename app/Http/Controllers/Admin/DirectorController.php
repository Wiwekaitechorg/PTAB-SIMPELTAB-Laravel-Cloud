<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use App\Director;
use Illuminate\Http\Request;

class DirectorController extends Controller
{
    use TraitModel;

    public function index()
    {
        // abort_unless(\Gate::allows('director_access'), 403);
        $directors = Director::selectRaw('directors.name,directors.id, dapertements.name as dapertement_name')->leftJoin('dapertements', 'dapertements.director_id', '=', 'directors.id')->get();
        return view('admin.director.index', compact('directors'));
    }
    public function create()
    {
        // abort_unless(\Gate::allows('Director_create'), 403);
        // $last_code = $this->get_last_code('Director');

        // $code = acc_code_generate($last_code, 8, 3);

        return view('admin.director.create');
    }
    public function store(Request $request)
    {
        // abort_unless(\Gate::allows('Director_create'), 403);
        $data = array_merge($request->all());
        Director::create($data);
        return redirect()->route('admin.director.index');
    }
    public function show($id)
    {
        // abort_unless(\Gate::allows('director_show'), 403);
        $director = Director::where('id', $id)->first();
        return view('admin.Director.show', compact('director'));
    }
    public function edit($id)
    {
        // dd($id);
        // abort_unless(\Gate::allows('director_edit'), 403);
        $director = Director::where('id', $id)->first();
        return view('admin.director.edit', compact('director'));
    }
    public function update($id, Request $request)
    {
        // abort_unless(\Gate::allows('Director_edit'), 403);
        $director = Director::where('id', $id)->first();
        $director->update($request->all());
        return redirect()->route('admin.director.index');
    }
    public function destroy($id)
    {
        // abort_unless(\Gate::allows('Director_delete'), 403);
        $director = Director::where('id', $id)->first();
        $director->delete();
        return back();
    }
    // public function massDestroy(MassDestroyWaCategoryRequest $request)
    // {
    //     Director ::whereIn('id', request('ids'))->delete();
    //     return response(null, 204);
    // }


}
