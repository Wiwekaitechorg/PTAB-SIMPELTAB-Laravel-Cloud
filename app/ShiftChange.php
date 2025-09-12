<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShiftChange extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'shift_id',
        'shift_change_id',
        'description',
        'created_at',
        'updated_at',
        'status',
        'staff_id',
        'staff_change_id'
    ];
    public function scopeFilterDate($query, $from, $to)
    {
        if (!empty(request()->input('from')) && !empty(request()->input('to'))) {
            $from = request()->input('from');
            $to =  request()->input('to');
            // $from = '2021-09-01';
            // $to = '2021-09-20';
            //return $query->whereBetween('lock_action.created_at', [$from, $to]);
            //return $query->whereBetween(DB::raw('DATE(shift_changes.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(shift_changes.created_at) >= '".$from."' AND DATE(shift_changes.created_at) <= '".$to."'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {
            if (date('d') > 20) {
                $from = date("Y-m-d", strtotime(date('Y-m') . "-21"));
                $to = date("Y-m-d", strtotime('+1 month', strtotime(date('Y-m') . "-20")));
            } else {
                $from = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . "-21")));
                $to = date("Y-m-d", strtotime('0 month', strtotime(date('Y-m') . "-20")));
            }

            //return $query->whereBetween(DB::raw('DATE(shift_changes.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(shift_changes.created_at) >= '".$from."' AND DATE(shift_changes.created_at) <= '".$to."'");
        }
    }

    public function scopeFilterDapertement($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('sh2.dapertement_id', $ststussm);
        }
        return $query;
    }
}
