<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class CtmPelanggan extends Model
{   
    // use SoftDeletes;
    protected $connection = 'mysql2';

    protected $table = 'tblpelanggan';

    public function scopeFilterStaff($query, $staff)
    {
        if ($staff != '') {
            $query->where('tblopp.operator', $staff)->where('tblopp.status', 1);
        }else{
            $query->where('tblopp.status', 1);
        }  
        return $query;      
    }

    public function scopeFilterDate($query, $from, $to)
    {
        if (!empty(request()->input('from')) && !empty(request()->input('to')) && request()->input('status')=='sudah') {
            $from = request()->input('from');
            $to =  request()->input('to'); 
            return $query->whereRaw("DATE(tblpelanggan.last_update) >= '".$from."' AND DATE(tblpelanggan.last_update) <= '".$to."'");

        } else {
            return;
        }
    }
    
}
