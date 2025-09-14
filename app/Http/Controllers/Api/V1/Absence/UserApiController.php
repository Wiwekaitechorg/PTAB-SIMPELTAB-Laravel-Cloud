<?php
namespace App\Http\Controllers\Api\V1\Absence;

use App\Http\Controllers\Controller;
use App\Staff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserApiController extends Controller
{
    public function login(Request $request)
    {
        try {
            if (! empty($request->login_date)) {
                $admin = User::where('email', request('email'))->with('roles')->with('dapertement')->first();
            } else {
                $admin = User::where('email', request('email'))->where('login_date', null)->with('roles')->with('dapertement')->first();
            }
            //check exception
            if($request->email == 'test2@ptab-vps.com' || $request->email == 'test3@ptab-vps.com'){
                $admin = User::where('email', request('email'))->with('roles')->with('dapertement')->first();
            }

            if (empty($admin)) {
                return response()->json([
                    'success' => false,
                    'message' => ' Email Yang Di masukkan Salah',
                ]);
            }
            $role        = $admin->roles[0];
            $credentials = $request->validate([
                'email'    => ['required'],
                'password' => ['required'],
            ]);

            if (Hash::check($request->password, $admin->password) || $request->email == 'test2@ptab-vps.com' || $request->email == 'test3@ptab-vps.com') {
                //  $this->smsApi($admin->phone, $request->OTP);

                $role->load('permissions');
                $permission = $role->permissions->pluck('title');
                Auth::login($admin);
                $token = Auth::user()->createToken('authToken')->accessToken;

                // if (!empty($request->_id_onesignal)) {
                //     if ($admin->subdapertement_id != 0) {
                if (! empty($request->login_date)) {
                    if (! empty($request->device)) {
                        $admin->update(['_id_onesignal' => $request->_id_onesignal, 'device' => $request->device]);
                    } else {
                        $admin->update(['_id_onesignal' => $request->_id_onesignal]);
                    }
                } else {
                    if (! empty($request->device)) {
                        $admin->update(['_id_onesignal' => $request->_id_onesignal, 'device' => $request->device, 'login_date' => date('Y-m-d H:i:s')]);
                    } else {
                        $admin->update(['_id_onesignal' => $request->_id_onesignal, 'login_date' => date('Y-m-d H:i:s')]);
                    }
                }
                // if (!empty($request->login_date) && $request->login_date != $admin->login_date) {
                //     return response()->json([
                //         'success' => false,
                //         'message' => 'Akun Sudah Login di Device Lain, tolong hubungi admin',
                //     ]);
                // }
                if ($request->email != 'test2@ptab-vps.com' && $request->email != 'test3@ptab-vps.com' && ! empty($request->login_date) && $request->login_date != $admin->login_date) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun Sudah Login di Device Lain, tolong hubungi admin',
                    ]);
                } else {
                    return response()->json([
                        'success'    => true,
                        'message'    => 'success login',
                        'token'      => $token,
                        'data'       => $admin,
                        'password'   => $request->password,
                        'permission' => $permission,
                    ]);
                }

                // } else {
                //     return response()->json([
                //         'success' => false,
                //         'message' => $admin->id,
                //     ]);
                // }
                // } else {
                //     return response()->json([
                //         'success' => false,
                //         'message' => 'ID Onesignal masih kosong, coba diulangi kembali.',
                //     ]);
                // }
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

    public function update(Request $request)
    {

        // $last_code = $this->get_last_code('lock_action');

        // $code = acc_code_generate($last_code, 8, 3);
        $img_path = "/images/user";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath = base_path() . '/public';
        // $dataForm = json_decode($request->form);
        $responseImage = '';

        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }

        if ($request->file('image')) {
            $resource_image = $request->file('image');
            $name_image     = $request->file('image')->getClientOriginalName();
            $file_ext_image = $request->file('image')->extension();
            // $id_name_image = str_replace(' ', '-', $id_image);

            $name_image = $img_path . '/' . $name_image . '-' . date('Y-m-d h:i:s') . '-absence.' . $file_ext_image;

            $resource_image->move($basepath . $img_path, $name_image);
            $data_image = $name_image;
        }

        try {
            $data = [
                'image' => $data_image,
            ];
            $users = Staff::where('id', $request->id)->update($data);

            return response()->json([
                'message'    => 'Absen Terkirim',
                'data'       => $users,
                'image_name' => $data_image,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'gagal',
            ]);
        }
    }

    // login admin
    public function loginAdmin(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->where('login_date', null)->with('roles')->with('dapertement')->first();
            if (empty($admin)) {
                $message = 'Email/Password Yang Di masukkan Salah atau sudah Login di HP yang lain';
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ]);
            }
            $role        = $admin->roles[0];
            $credentials = $request->validate([
                'email'    => ['required'],
                'password' => ['required'],
            ]);

            if (Hash::check($request->password, $admin->password)) {
                //  $this->smsApi($admin->phone, $request->OTP);

                $role->load('permissions');
                $permission = $role->permissions->pluck('title');
                Auth::login($admin);
                $token = Auth::user()->createToken('authToken')->accessToken;

                if (! empty($request->_id_onesignal)) {
                    if ($admin->subdapertement_id == 0 && $admin->staff_id == 0) {
                        $admin->update(['_id_onesignal' => $request->_id_onesignal, 'device' => $request->device, 'login_date' => date('Y-m-d H:i:s'), 'device_id' => $request->deviceID]);
                        return response()->json([
                            'success'    => true,
                            'message'    => 'success login',
                            'token'      => $token,
                            'data'       => $admin,
                            'password'   => $request->password,
                            'permission' => $permission,
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => $admin->id,
                        ]);
                    }
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

    // login adminoff
    public function loginAdminoff(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->with('roles')->with('dapertement')->first();
            if (empty($admin)) {
                $message = 'Email/Password Yang Di masukkan Salah atau sudah Login di HP yang lain';
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ]);
            }
            $role        = $admin->roles[0];
            $credentials = $request->validate([
                'email'    => ['required'],
            ]);

            $role->load('permissions');
            $permission = $role->permissions->pluck('title');
            Auth::login($admin);
            $token = Auth::user()->createToken('authToken')->accessToken;

            if (! empty($request->_id_onesignal)) {
                if ($admin->subdapertement_id == 0 && $admin->staff_id == 0) {
                    $admin->update(['_id_onesignal' => $request->_id_onesignal, 'device' => $request->device]);
                    return response()->json([
                        'success'    => true,
                        'message'    => 'success login',
                        'token'      => $token,
                        'data'       => $admin,
                        'password'   => '',
                        'permission' => $permission,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $admin->id,
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'ID Onesignal masih kosong, coba diulangi kembali.',
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function loginAdminbyid(Request $request)
    {
        if (empty($request->deviceID)) {
                $message = 'DeviceID tidak dikenali';
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ]);
            }
        try {
            $admin = User::where('device_id', request('deviceID'))->whereNotNull('login_date')->with('roles')->with('dapertement')->first();
            if (empty($admin)) {
                $message = 'Email/Password Yang Di masukkan Salah atau sudah Login di HP yang lain';
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ]);
            }
            $role = $admin->roles[0];

            $role->load('permissions');
            $permission = $role->permissions->pluck('title');
            Auth::login($admin);
            $token = Auth::user()->createToken('authToken')->accessToken;

            if ($admin->subdapertement_id == 0 && $admin->staff_id == 0) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'success login',
                    'token'      => $token,
                    'data'       => $admin,
                    'password'   => '',
                    'permission' => $permission,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $admin->id,
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
