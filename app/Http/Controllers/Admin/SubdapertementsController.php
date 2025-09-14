<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSubdapertementRequest;
use App\Http\Requests\UpdateSubdapertementRequest;
use App\Subdapertement;
use App\Dapertement;
use App\Traits\TraitModel;
use Illuminate\Database\QueryException;

class SubdapertementsController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('subdapertement_access'), 403);

        $subdapertements = Subdapertement::with('dapertement')->get();

        return view('admin.subdapertements.index', compact('subdapertements'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('subdapertement');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('subdapertement_create'), 403);
        $dapertements = Dapertement::all();

        return view('admin.subdapertements.create', compact('code','dapertements'));
    }

    public function store(StoreSubdapertementRequest $request)
    {
        abort_unless(\Gate::allows('subdapertement_create'), 403);
        $subdapertement = Subdapertement::create($request->all());

        return redirect()->route('admin.subdapertements.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {

        abort_unless(\Gate::allows('subdapertement_edit'), 403);

        $subdapertement = Subdapertement::findOrFail($id);
        $dapertements = Dapertement::all();

        return view('admin.subdapertements.edit', compact('subdapertement','dapertements'));
    }

    public function update(UpdateSubdapertementRequest $request, Subdapertement $subdapertement)
    {
        abort_unless(\Gate::allows('subdapertement_edit'), 403);
    
        $subdapertement->update($request->all());

        return redirect()->route('admin.subdapertements.index');
    }

    public function destroy(Subdapertement $subdapertement)
    {
        abort_unless(\Gate::allows('subdapertement_delete'), 403);

        try{
            $subdapertement->delete();
            return back();
        }
        catch(QueryException $e) {
            return back()->withErrors(['Mohon hapus dahulu data yang terkait']);
        }
    }

    public function massDestroy()
    {
        # code...
    }
}
