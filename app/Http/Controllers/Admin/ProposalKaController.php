<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\ProposalKas;
use Illuminate\Http\Request;
use App\User;

class ProposalKaController extends Controller
{
    public function index()
    {
        abort_unless(\Gate::allows('proposal_ka_access'), 403);

        $proposalKas = ProposalKas::with('dapertements')->with('users')->get();

        return view('admin.proposalKa.index', compact('proposalKas'));
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('proposal_ka_edit'), 403);
        $proposalKa = proposalKas::where('id', $id)->first();
        $users = User::where('dapertement_id', $proposalKa->dapertement_id)->where('staff_id', '!=', null)->get();

        return view('admin.proposalKa.edit', compact('proposalKa', 'users'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(\Gate::allows('proposal_ka_edit'), 403);
        $proposalKa = proposalKas::where('id', $id)->update(['user_id' => $request->user_id]);


        return redirect()->route('admin.proposalKa.index');
    }
}
