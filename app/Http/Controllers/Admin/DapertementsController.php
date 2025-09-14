<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDapertementRequest;
use App\Http\Requests\UpdateDapertementRequest;
use App\Dapertement;
use App\Director;
use App\Traits\TraitModel;
use Illuminate\Database\QueryException;

class DapertementsController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('dapertement_access'), 403);

        $dapertements = Dapertement::all();

        return view('admin.dapertements.index', compact('dapertements'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('dapertement');

        $code = acc_code_generate($last_code, 8, 3);
        $directors = Director::get();

        abort_unless(\Gate::allows('dapertement_create'), 403);

        return view('admin.dapertements.create', compact('code', 'directors'));
    }

    public function store(StoreDapertementRequest $request)
    {
        abort_unless(\Gate::allows('dapertement_create'), 403);
        $dapertement = Dapertement::create($request->all());

        return redirect()->route('admin.dapertements.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {

        abort_unless(\Gate::allows('dapertement_edit'), 403);

        $dapertement = Dapertement::findOrFail($id);

        $directors = Director::get();

        return view('admin.dapertements.edit', compact('dapertement', 'directors'));
    }

    public function update(UpdateDapertementRequest $request, Dapertement $dapertement)
    {
        abort_unless(\Gate::allows('dapertement_edit'), 403);

        $dapertement->update($request->all());

        return redirect()->route('admin.dapertements.index');
    }

    public function destroy(Dapertement $dapertement)
    {
        abort_unless(\Gate::allows('dapertement_delete'), 403);

        try {
            $dapertement->delete();
            return back();
        } catch (QueryException $e) {
            return back()->withErrors(['Mohon hapus dahulu data yang terkait']);
        }
    }

    public function massDestroy()
    {
        # code...
    }
}
