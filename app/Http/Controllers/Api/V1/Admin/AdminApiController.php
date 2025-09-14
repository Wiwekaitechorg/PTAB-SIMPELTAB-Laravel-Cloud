<?php

namespace App\Http\Controllers\Api\v1\admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminApiController extends Controller
{
    public function loginApi(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->with('roles')->first();
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

                return response()->json([
                    'success' => true,
                    'message' => 'success login',
                    'token' => $token,
                ]);
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

    public function loginJs(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->with('roles')->first();
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

                return response()->json([
                    'success' => true,
                    'message' => 'success login',
                    'token' => $token,
                    'data' => $admin,
                    'password' => $request->password,
                    'permission' => $permission,
                ]);
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

    public function login(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->with('roles')->first();
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
                    $admin->update(['_id_onesignal' => $request->_id_onesignal]);
                    return response()->json([
                        'success' => true,
                        'message' => 'success login',
                        'token' => $token,
                        'data' => $admin,
                        'password' => $request->password,
                        'permission' => $permission,
                    ]);
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

    public function profile()
    {
        $contact = Auth::user();
        $contact = $contact->makeHidden(['email_verified_at', 'password', 'remember_token']);

        $response['status'] = true;
        $response['message'] = 'User login profil';
        $response['data'] = $contact;

        return response()->json($response, 200);
    }
}
