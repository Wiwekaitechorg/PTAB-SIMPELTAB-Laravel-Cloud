<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class LockAction extends Model
{
    protected $table = 'lock_action';
    protected $fillable = [
        'lock_id',
        'code',
        'type',
        'image',
        'customer_id',
        'staff_id',
        'memo',
        'lat',
        'lng',
        'image_work',
        'image_prework',
        'image_tools',
        'image_done',
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'nomorrekening');
    }
    public function subdapertement()
    {
        return $this->belongsTo(Subdapertement::class, 'subdapertement_id', 'id');
    }
    public function lock()
    {
        return $this->belongsTo(Lock::class, 'lock_id', 'id');
    }
    public function scopeFilterStatus($query, $status)
    {
        if ($status != '') {
            $query->where('lock_action.type', $status);
            return $query;
        } else {
        }
    }
    public function scopeFilterDate($query, $from, $to)
    {
        if (!empty(request()->input('from')) && !empty(request()->input('to'))) {
            $from = request()->input('from');
            $to =  request()->input('to');
            // $from = '2021-09-01';
            // $to = '2021-09-20';
            //return $query->whereBetween('lock_action.created_at', [$from, $to]);
            //return $query->whereBetween(DB::raw('DATE(lock_action.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(lock_action.created_at) >= '".$from."' AND DATE(lock_action.created_at) <= '".$to."'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {
            return;
        }
    }

    public function scopeFilterArea($query, $area)
    {
        if ($area != '') {
            $query->where('tblpelanggan.idareal', $area);
            return $query;
        } else {
        }
    }

    public function scopeFilterStaff($query, $staff)
    {
        if ($staff != '') {
            $query->where('users.id', $staff);
            return $query;
        } else {
        }
    }
}
