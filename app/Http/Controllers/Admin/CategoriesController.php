<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\StoreCategoriesRequest;
use App\Category;
use App\Traits\TraitModel;
use App\CategoryType;
use App\CategoryGroup;

class CategoriesController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('categories_access'), 403);
        $categories = Category::all();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('category');

        $code = acc_code_generate($last_code, 8, 3);
        $category_types = CategoryType::all();
        $category_groups = CategoryGroup::all();

        abort_unless(\Gate::allows('categories_create'), 403);
        return view('admin.categories.create', compact('code','category_types','category_groups'));
    }

    public function store(StoreCategoriesRequest $request)
    {
        abort_unless(\Gate::allows('categories_create'), 403);
        $category = Category::create($request->all());

        return redirect()->route('admin.categories.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('categories_edit'), 403);
        $category = Category::findOrFail($id);
        $category_types = CategoryType::all();
        $category_groups = CategoryGroup::all();
        return view('admin.categories.edit', compact('category','category_types','category_groups'));
    }

    public function update(UpdateCategoryRequest $request,Category $category)
    {
        abort_unless(\Gate::allows('categories_edit'), 403);
        $category->update($request->all());
        return redirect()->route('admin.categories.index');
    }

    public function destroy(Category $category)
    {
        abort_unless(\Gate::allows('categories_delete'), 403);

        $category->delete();

        return back();
    }

    public function massDestory(MassDestroyCategoriesRequest $request)
    {
        Category::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
