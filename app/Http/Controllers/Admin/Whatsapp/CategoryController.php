<?php

namespace App\Http\Controllers\Admin\Whatsapp;

use App\CategoryWa;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyWaCategoryRequest;
use App\Traits\TraitModel;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use TraitModel;

    public function index()
    {
        $categoryWas = CategoryWa::get();
        return view('admin.whatsapp.category.index', compact('categoryWas'));
    }
    public function create()
    {
        $last_code = $this->get_last_code('categoryWa');

        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.whatsapp.category.create', compact('code'));
    }
    public function store(Request $request)
    {
        $data = array_merge($request->all());
        CategoryWa::create($data);
        return redirect()->route('admin.categoryWA.index');
    }
    public function show($id)
    {
        abort_unless(\Gate::allows('permission_show'), 403);
        $categoryWa = CategoryWa::where('id', $id)->first();
        return view('admin..whatsapp.category.show', compact('categoryWa'));
    }
    public function edit($id)
    {
        // dd($id);
        $categoryWa = CategoryWa::where('id', $id)->first();
        return view('admin.whatsapp.category.edit', compact('categoryWa'));
    }
    public function update($id, Request $request)
    {
        $categoryWa = CategoryWa::where('id', $id)->first();
        $categoryWa->update($request->all());
        return redirect()->route('admin.categoryWA.index');
    }
    public function destroy($id)
    {
        $categoryWa = CategoryWa::where('id', $id)->first();
        $categoryWa->delete();
        return back();
    }
    public function massDestroy(MassDestroyWaCategoryRequest $request)
    {
        CategoryWa::whereIn('id', request('ids'))->delete();
        return response(null, 204);
    }
}
