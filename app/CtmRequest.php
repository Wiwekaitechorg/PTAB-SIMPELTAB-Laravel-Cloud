<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CtmRequest extends Model
{
    // use SoftDeletes;

    protected $table = 'ctm_requests';
    protected $fillable = [
        'norek',
        'wmmeteran',
        'namastatus',
        'opp',
        'lat',
        'lng',
        'accuracy',
        'operator',
        'nomorpengirim',
        'statusonoff',
        'description',
        'img',
        'img1',
        'status',
        'datecatatf1',
        'datecatatf2',
        'datecatatf3',
        'year',
        'month',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'norek', 'nomorrekening');
    }

    public function scopeFilterDate($query, $from, $to)
    {
        if (!empty(request()->input('from')) && !empty(request()->input('to'))) {
            $from = request()->input('from');
            $to =  request()->input('to');
            // $from = '2021-09-01';
            // $to = '2021-09-20';
            //return $query->whereBetween('datecatatf1', [$from, $to]);
            return $query->whereRaw("DATE(datecatatf1) >= '".$from."' AND DATE(datecatatf1) <= '".$to."'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {
            return $query->whereMonth('datecatatf1', '=', date('m'))
                ->whereYear('datecatatf1', '=', date('Y'));
        }
    }
}
