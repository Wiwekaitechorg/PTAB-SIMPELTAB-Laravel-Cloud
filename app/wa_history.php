<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wa_history extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'status',
        'id',
        'template_id',
        'id_wa',
        'customer_id',
        'ref_id',
        'message',
        'channel_id',
    ];

    public function scopeFilterDate($query, $from, $to)
    {
        if (!empty(request()->input('from')) && !empty(request()->input('to'))) {
            $from = request()->input('from');
            $to =  request()->input('to');
            // $from = '2021-09-01';
            // $to = '2021-09-20';
            //return $query->whereBetween('created_at', [$from, $to]);
            return $query->whereRaw("DATE(created_at) >= '".$rfrom."' AND DATE(created_at) <= '".$rto."'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {
            return;
        }
    }

    public function scopeFilterStatus($query, $status)
    {
        if ($status != '') {
            $query->where('status', $status);
            return $query;
        } else {
        }
    }

    public function scopeFilterChannel($query, $channel)
    {
        if ($channel != '') {
            $query->where('channel_id', $channel);
            return $query;
        } else {
        }
    }

    public function scopeFilterCustom($query, $custom)
    {
        if (request()->input('custom') == 'customer') {
            $query->where('customer_id', '!=', '');
            return $query;
        } else if (request()->input('custom') == 'nonCustomer') {
            $query->where('customer_id', '=', '');
            return $query;
        } else {
        }
    }
}
