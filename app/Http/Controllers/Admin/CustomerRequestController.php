<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CustomerRequest;
use App\Traits\TraitModel;
use App\Customer;

class CustomerRequestController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('customerrequests_access'), 403);
        $customerrequests = CustomerRequest::with('customer')
            ->get();

        return view('admin.customerrequests.index', compact('customerrequests'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('category');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('customerrequests_create'), 403);
        return view('admin.customerrequests.create', compact('code'));
    }

    public function store(Request $request)
    {
        abort_unless(\Gate::allows('customerrequests_create'), 403);
        $category = CustomerRequest::create($request->all());

        return redirect()->route('admin.customerrequests.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('customerrequests_edit'), 403);
        $customerrequest = CustomerRequest::with('customer')->findOrFail($id);
        return view('admin.customerrequests.edit', compact('customerrequest'));
    }

    public function update(Request $request, CustomerRequest $customerrequest)
    {
        abort_unless(\Gate::allows('customerrequests_edit'), 403);
        //update phone
        $customer = Customer::find($request->nomorrekening);
        $customer->telp = $request->phone;
        $customer->save();
        //update status
        $customerrequest->status = 'approve';
        $customerrequest->save();
        return redirect()->route('admin.customerrequests.index');
    }

    public function destroy(CustomerRequest $category)
    {
        abort_unless(\Gate::allows('customerrequests_delete'), 403);

        $category->delete();

        return back();
    }

    public function massDestory(Request $request)
    {
        CustomerRequest::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
