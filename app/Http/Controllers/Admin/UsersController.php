<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Role;
use App\User;
use App\Dapertement;
use App\Subdapertement;
use App\Staff;

class UsersController extends Controller
{
    public function index()
    {
        abort_unless(\Gate::allows('user_access'), 403);

        $users = User::with('dapertement')->with('subdapertement')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('user_create'), 403);

        $roles = Role::where('id', '!=', 24)->pluck('title', 'id');
        $dapertements = Dapertement::all();

        return view('admin.users.create', compact('roles', 'dapertements'));
    }

    public function store(StoreUserRequest $request)
    {
        abort_unless(\Gate::allows('user_create'), 403);
        $validated = $request->validate([
            'email' => 'required|unique:users|max:255'
            // 'body' => 'required',
        ]);


        $user = User::create($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        abort_unless(\Gate::allows('user_edit'), 403);

        // $roles = Role::all()->pluck('title', 'id');
        $roles = Role::all()->where('id', '!=', 24)->pluck('title', 'id');

        $user->load('roles');
        $dapertements = Dapertement::all();
        $subdapertements = Subdapertement::where('dapertement_id', $user->dapertement_id)->get();
        $staffs = Staff::where('subdapertement_id', $user->subdapertement_id)->get();

        return view('admin.users.edit', compact('roles', 'user', 'dapertements', 'subdapertements', 'staffs'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {

        abort_unless(\Gate::allows('user_edit'), 403);
        $validated = $request->validate([
            'email' => 'required|unique:users,email,' . $user->id . ',id',
            // 'body' => 'required',
        ]);

        $user->update($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function show(User $user)
    {
        abort_unless(\Gate::allows('user_show'), 403);

        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        abort_unless(\Gate::allows('user_delete'), 403);

        $user->delete();

        return back();
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        User::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
