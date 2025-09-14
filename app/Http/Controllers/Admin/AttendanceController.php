<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = [
            ['id' => '1', 'name' => 'Wayan', 'date' => '12-12-2022', 'activity' => 'Sakit', 'day' => 'senin', 'part' => '1', 'location' => '', 'status' => 'pending', 'late' => '0'],
            ['id' => '2', 'name' => 'Kadek', 'date' => '12-12-2022', 'activity' => 'reguler', 'day' => 'senin', 'part' => '1', 'location' => '', 'status' => 'approve', 'late' => '0'],
            ['id' => '3', 'name' => 'Nyoman', 'date' => '12-12-2022', 'activity' => 'reguler', 'day' => 'senin', 'part' => '1', 'location' => '', 'status' => 'approve', 'late' => '0'],
            ['id' => '4', 'name' => 'Ketut', 'date' => '12-12-2022', 'activity' => 'reguler', 'day' => 'senin', 'part' => '1', 'location' => '', 'status' => 'approve', 'late' => '1']
        ];
        return view('admin.attendance.index', compact('attendances'));
    }

    public function create()
    {
        $reason = [
            ['id' => '4', 'name' => 'sakit', 'group' => '3'],
            ['id' => '5', 'name' => 'izin', 'group' => '3']
        ];
        return view('admin.attendance.create', compact('reason'));
    }
    function countDays($year, $month, $ignore)
    {
        $count = 0;
        $counter = mktime(0, 0, 0, $month, 1, $year);
        while (date("n", $counter) == $month) {
            if (in_array(date("w", $counter), $ignore) == false) {
                $count++;
            }
            $counter = strtotime("+1 day", $counter);
        }
        return  $count;
    }
    public function test()
    {
        echo $this->countDays(2022, 12, array(0, 6)); // 23
    }

    public function attendanceMenu()
    {

        $masuk1 = date('H:i:s', strtotime("11:30:00"));
        $masuk2 = date('H:i:s', strtotime("13:00:00"));
        $pulang1 = date('H:i:s', strtotime("15:30:00"));
        $pulang2 = date('H:i:s', strtotime("16:00:00"));
        if ($masuk1 < date('H:i:s') && $masuk2 > date('H:i:s')) {
            echo "absen masuk";
        } else if ($pulang1 < date('H:i:s') && $pulang2 > date('H:i:s')) {
            echo "absen pulang";
        } else {
            echo "g";
        }
        $time = strtotime('11:30:00');

        // untuk menjumlahkan waktu
        $startTime = date("H:i:s", strtotime('-30 minutes', $time));
        $endTime = date("H:i:s", strtotime('+30 minutes', $time));
        dd($endTime);
    }

    function getRadius($coordinateArray, $center, $radius)
    {
        // $resultArray = array();
        // $lat1 = $center[0];
        // $long1 = $center[1];
        // foreach ($coordinateArray as $coordinate) {
        //     $lat2 = $coordinate[0];
        //     $long2 = $coordinate[1];
        //     $distance = 3959 * acos(cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($long2) - deg2rad($long1)) + sin(deg2rad($lat1)) * sin(deg2rad($lat2)));
        //     dd($distance, $radius);
        //     if ($distance < $radius) $resultArray[] = $coordinate;
        // }
        // return $resultArray;

        return 6371 * acos(cos(deg2rad(45.815005)) * cos(deg2rad($center[0])) * cos(deg2rad($center[1]) - deg2rad(15.978501)) + sin(deg2rad(45.815005)) * sin(deg2rad(-8.6556162)));
    }


    function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }



    public function cekradius()
    {
        // dd($this->getRadius([[-8.5372862, 115.132938]], [-8.459556, 115.046600], 10000));
        // dd($this->getRadius([[-8.6556162, 115.2316827]], [-8.5392225, 115.1339101], 1000));
        // dd($this->getRadius([[-6.1421489, 106.8109178, 15]], [-8.5392225, 115.1339101], 1000));

        // if ($this->distance(-8.5357391, 115.131616, -8.5357967, 115.1323389, "K") < 10) {
        //     echo "True";
        // } else {
        //     echo "False";
        // }

        $lng = -8.5826809;
        $lat = 115.0992146;

        if ($this->distance(-8.5838641, 115.1216412, $lng, $lat, "K") < 1) {
            echo "True";
        } else {
            echo "False";
        }
    }
}
