<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\Dapertement;
use App\StaffApi;
use App\StaffMaps;
use App\StaffRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiStaffRegisterPublicRequest;
use App\Traits\TraitModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;


class StaffApiController extends Controller
{
    use TraitModel;

    public function requestStaff(Request $request)
    {
        try {
            // ambil data dari request simpan di dataForm

            $dataForm = json_decode($request->form);
            $data['code'] = $dataForm->norek;
            $data['phone'] = $dataForm->telp;
            $data['address'] = $dataForm->alamat;
            $requestStaff = StaffRequest::create($data);

            $img_path = "/images/request";
            //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            $basepath  = base_path().'/public';

            // cek status dan upload gambar
            if ($request->file('image')) {
                $resourceImage = $request->file('image');
                $nameImage = strtolower($requestStaff->id);
                $file_extImage = $request->file('image')->extension();
                $nameImage = str_replace(" ", "-", $nameImage);

                $img_name = $img_path . "/" . $nameImage . "." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);
            }

            if ($resourceImage) {
                $requestStaff->img = $img_name;
                $requestStaff->save();

                return response()->json([
                    'message' => 'Permintaan Dikirim',
                    'data' => $requestStaff,
                ]);
            } else {
                return response()->json([
                    'message' => 'Foto Gagal Di Simpan',
                ]);
            }
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Permintaan Gagal Dikirim',
                'data' => $ex,
            ]);
        }
    }

    public function smsReset($phone, $otp)
    {
        date_default_timezone_set("Asia/Singapore");
        $date = date("Y-m-d H:i:s");
        $number = "+62" . ltrim($phone, '0');
        $message = '#plg OTP : ' . $otp;
        $md5_str = "1f4a449a85" . $date . $number . $message;
        $md5 = md5($md5_str);
        $data = array(
            'outbox' => '',
            'date' => $date,
            'number' => $number,
            'message' => $message,
            'md5' => $md5,
        );

        $url = 'https://www.ptab-vps-storage.com/gs-gateway-sms-v3/api.php';

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        curl_exec($ch);

        //close connection
        curl_close($ch);
    }

    public function reset(Request $request)
    {
        $Staff = StaffApi::WhereMaps('phone', $request->phone)->first();

        if (empty($Staff)) {
            $message = 'Reset gagal, Telfon tidak dikenali.';
            return response()->json([
                'success' => false,
                'message' => $message,
            ]);
        } else {
            $password = passw_gnr(7);
            $password_ency = bcrypt($password);
            $Staff->password = $password_ency;
            $Staff->save();
            $Staff->pass = $password;
            //SMS Gateway
            $this->smsReset($request->phone, $password);
            //response
            $message = 'Reset berhasil, Password baru telah terkirim via SMS.';
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $Staff,
            ]);
        }
    }


    public function login(Request $request)
    {
        try {
            $admin = User::where('email', request('email'))->with('roles')->with('dapertement')->first();
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
                    if ($admin->subdapertement_id === 10 || $admin->dapertement->group_unit > 1 || $admin->id === 2) {
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

    public function smsApi(Request $request)
    {
        date_default_timezone_set("Asia/Singapore");
        $date = date("Y-m-d H:i:s");
        $number = "+62" . ltrim($request->phone, '0');
        $message = '#plg OTP : ' . $request->OTP;
        $md5_str = "1f4a449a85" . $date . $number . $message;
        $md5 = md5($md5_str);
        $data = array(
            'outbox' => '',
            'date' => $date,
            'number' => $number,
            'message' => $message,
            'md5' => $md5,
        );

        $url = 'https://www.ptab-vps-storage.com/gs-gateway-sms-v3/api.php';

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);
    }

    public function scanBarcode(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $code = $request->code;

        try {
            $StaffMaps = StaffMaps::where('qrcode', $code)->first();
            if (empty($StaffMaps)) {
                return response()->json([
                    'status' => '0',
                    'message' => 'Data Kosong',
                ]);
            } else {
                $Staff = StaffApi::WhereMaps('code', $StaffMaps->nomorrekening)->first();

                if (isset($Staff)) {
                    $pass_set = 0;
                    if ($Staff->password != '') {
                        $pass_set = 1;
                    }
                    return response()->json([
                        'status' => '1',
                        'message' => 'Anda terdaftar sebagai pelanggan',
                        'data' => $Staff,
                        'pass_set' => $pass_set,
                    ]);
                } else {
                    return response()->json([
                        'status' => '0',
                        'message' => 'Data Kosong',
                    ]);
                }
            }
        } catch (QueryException $ex) {
            return response()->json([
                'status' => '0',
                'message' => $ex,
            ]);
        }
    }

    public function logout(Request $res)
    {
        if (Auth::user()) {
            $user = Auth::user()->token();
            $user->revoke();

            return response()->json([
                'success' => true,
                'message' => 'Logout successfully',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unable to Logout',
            ]);
        }
    }

    public function test()
    {

        $arr['dapertement_id'] = 2;
        // $arr['month'] = date("m");
        // $arr['year'] = date("Y");
        $date = date_create("2021-06-23 06:15:36");
        $arr['month'] = date_format($date, "m");
        $arr['year'] = date_format($date, "Y");
        $last_code = $this->get_last_code('spk-ticket', $arr);
        $code = acc_code_generate($last_code, 21, 17, 'Y');
        return $code;
    }
}
