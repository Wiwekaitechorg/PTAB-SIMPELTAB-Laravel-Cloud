<?php

namespace App\Http\Controllers\Api\V1\Visit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;

class UserApiController extends Controller
{
    public function login(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->with('staffs')->with('roles')->with('dapertement')->first();
            if (empty($admin)) {
                return response()->json([
                    'success' => false,
                    'message' => ' Email Yang Di masukkan Salah',
                ]);
            }
            $role = $admin->roles[0];
            $credentials = $request->validate([
                'email' => ['required'],
                'password' => ['required'],
            ]);

            if (Hash::check($request->password, $admin->password)) {
                //  $this->smsApi($admin->phone, $request->OTP);

                $role->load('permissions');
                $permission = $role->permissions->pluck('title');
                Auth::login($admin);
                $token = Auth::user()->createToken('authToken')->accessToken;

                if (!empty($request->_id_onesignal)) {
                    // if ($admin->staffs->pbk != null && $admin->staffs->pbk != "") {
                    $admin->update(['_id_onesignal' => $request->_id_onesignal]);
                    return response()->json([
                        'success' => true,
                        'message' => 'success login',
                        'token' => $token,
                        'data' => $admin,
                        'password' => $request->password,
                        'permission' => $permission,
                    ]);
                    // } else {
                    //     return response()->json([
                    //         'success' => false,
                    //         'message' => $admin->id,
                    //     ]);
                    // }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'ID Onesignal masih kosong, coba diulangi kembali.',
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => ' Password Yang Di masukkan Salah',
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
