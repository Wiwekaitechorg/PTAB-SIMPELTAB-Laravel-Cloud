<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\CustomerApi;
use App\CustomerMaps;
use App\CustomerRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiCustomerRegisterPublicRequest;
use App\Traits\TraitModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomersApiController extends Controller
{
    use TraitModel;

    public function requestCustomer(Request $request)
    {
        try {
            // ambil data dari request simpan di dataForm

            $dataForm = json_decode($request->form);
            $data['code'] = $dataForm->norek;
            $data['phone'] = $dataForm->telp;
            $data['address'] = $dataForm->alamat;
            $requestcustomer = CustomerRequest::create($data);

            $img_path = "/images/request";
            //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
            $basepath  = base_path().'/public';

            // cek status dan upload gambar
            if ($request->file('image')) {
                $resourceImage = $request->file('image');
                $nameImage = strtolower($requestcustomer->id);
                $file_extImage = $request->file('image')->extension();
                $nameImage = str_replace(" ", "-", $nameImage);

                $img_name = $img_path . "/" . $nameImage . "." . $file_extImage;

                $resourceImage->move($basepath . $img_path, $img_name);
            }

            if ($resourceImage) {
                $requestcustomer->img = $img_name;
                $requestcustomer->save();

                return response()->json([
                    'message' => 'Permintaan Dikirim',
                    'data' => $requestcustomer,
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
        $customer = CustomerApi::WhereMaps('phone', $request->phone)->first();

        if (empty($customer)) {
            $message = 'Reset gagal, Telfon tidak dikenali.';
            return response()->json([
                'success' => false,
                'message' => $message,
            ]);
        } else {
            $password = passw_gnr(7);
            $password_ency = bcrypt($password);
            $customer->password = $password_ency;
            $customer->save();
            $customer->pass = $password;
            //SMS Gateway
            $this->smsReset($request->phone, $password);
            //response
            $message = 'Reset berhasil, Password baru telah terkirim via SMS.';
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $customer,
            ]);
        }
    }

    public function register(Request $request)
    {
        $customer = CustomerApi::WhereMaps('code', $request->code)->first();

        if (empty($customer)) {
            $message = 'Register gagal, No. Rekening tidak dikenali.';
            return response()->json([
                'success' => false,
                'message' => $message,
            ]);
        } else {
            $password_ency = bcrypt($request->password);
            $customer->password = $password_ency;
            $customer->phone = $request->phone;
            $customer->_id_onesignal = $request->_id_onesignal;
            $customer->_synced = 0;
            $customer->save();
            Auth::login($customer);
            //$customer->update(['_id_onesignal' => $request->_id_onesignal]);
            $token = Auth::user()->createToken('authToken')->accessToken;

            return response()->json([
                'success' => true,
                'message' => 'Success Register',
                'token' => $token,
                'data' => $customer,
                'sugnal' => $request->_id_onesignal,
            ]);
        }
    }

    public function login(Request $request)
    {
        try {
            $customer = CustomerApi::WhereMaps('code', request('code'))->first();

            $credentials = $request->validate([
                'code' => ['required'],
                'password' => ['required'],
            ]);

            if (Hash::check($request->password, $customer->password)) {
                //  $this->smsApi($customer->code, $request->OTP);
                Auth::login($customer);
                $customer->update(['_id_onesignal' => $request->_id_onesignal]);
                $token = Auth::user()->createToken('authToken')->accessToken;

                // $data = [
                //     'success' =>  true,
                //     'message' => 'success login',
                //     'token' => $token,
                //     'data' => $customer,
                // ];
                return response()->json([
                    'success' => true,
                    'message' => 'success login',
                    'token' => $token,
                    'data' => $customer,
                    'sugnal' => $request->_id_onesignal,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'failed' => 'Email Atau Password Yang Di masukkan Salah',
                ]);
                // $data =[
                //     'message' => 'Email Atau Password Yang Di masukkan Salah',
                // ];
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => $e->message,
            ]);
            // $data = [
            //     'message' => $e->message
            // ];
        }

        // return response()->json($data);
    }

    public function register_public(StoreApiCustomerRegisterPublicRequest $request)
    {

        $last_code = $this->get_last_code('public');

        $code = acc_code_generate($last_code, 8, 3);
        // $code = $last_code + 1;

        $customer = new CustomerApi;
        $customer->name = $request->name;
        $customer->code = $code;
        if (!isset($request->email)) {
            $customer->email = null;
        } else {
            $request->validate([
                'email' => 'required|email',
            ]);
            $customer->email = $request->email;
        }
        $customer->email_verified_at = null;
        $customer->remember_token = null;
        $customer->password = bcrypt($request->passwordNew);
        $customer->phone = $request->phone;
        $customer->type = 'public';
        $customer->gender = $request->gender;
        $customer->address = $request->address;
        $customer->_synced = 99;
        $customer->_id_onesignal = $request->_id_onesignal;

        try {
            $customer->save();

            $customer = CustomerApi::WhereMaps('phone', request('phone'))->first();
            Auth::login($customer);
            $token = Auth::user()->createToken('authToken')->accessToken;

            return response()->json([
                'message' => 'Registrasi Berhasil',
                'token' => $token,
                'data' => $customer,
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                // 'message' => 'Registrasi Berhasil',
                // 'token' => $token,
                'data' => $request->email,
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
            $customerMaps = CustomerMaps::where('qrcode', $code)->first();
            if (empty($customerMaps)) {
                return response()->json([
                    'status' => '0',
                    'message' => 'Data Kosong',
                ]);
            } else {
                $customer = CustomerApi::WhereMaps('code', $customerMaps->nomorrekening)->first();

                if (isset($customer)) {
                    $pass_set = 0;
                    if ($customer->password != '') {
                        $pass_set = 1;
                    }
                    return response()->json([
                        'status' => '1',
                        'message' => 'Anda terdaftar sebagai pelanggan',
                        'data' => $customer,
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
