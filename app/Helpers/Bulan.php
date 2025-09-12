<?php
    function Bulan($date){
     
        $bulan = $date;
     
        switch($bulan){
            case '01':
                $b = "Januari";
            break;
     
            case '02':			
                $b = "Februari";
            break;
     
            case '03':
                $b = "Maret";
            break;
     
            case '04':
                $b = "April";
            break;
     
            case '05':
                $b = "Mei";
            break;
     
            case '06':
                $b = "Juni";
            break;
     
            case '07':
                $b = "Juli";
            break;

            case '08':
                $b = "Agustus";
            break;

            case '09':
                $b = "September";
            break;

            case '10':
                $b = "Oktober";
            break;
            
            case '11':
                $b = "November";
            break;

            case '12':
                $b = "Desember";
            break;

            default:
                $b = "Tidak di ketahui";		
            break;
        }
     
        return $b;
     
    }
?>