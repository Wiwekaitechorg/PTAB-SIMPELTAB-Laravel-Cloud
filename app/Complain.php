<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{
    protected $table    = 'complains';
    protected $dates    = ['delegated_at'];
    protected $fillable = [
        'code',
        'title',
        'image',
        'video',
        'lat',
        'lng',
        'status',
        'category_id',
        'dapertement_id',
        'customer_id',
        'description',
        'area',
        'spk',
        'dapertement_receive_id',
        'delegated_at',
        'print_status',
        'print_spk_status',
        'print_report_status',
        'creator',
        'address',
        'user_id',
        'complain_status_id',
        'customer_name',
        'lat_loc',
        'lng_loc',
        'pbk_id',
    ];

    public function area()
    {
        return $this->belongsTo(CtmWilayah::class, 'area', 'id');
    }

    // public function scopeFilterByGroupUnit($query, $groupUnit)
    // {
    //     return $query->join('simpletabctm.tblwilayah as a', 'complains.area', '=', 'a.id')
    //         ->where('a.group_unit', $groupUnit)
    //         ->select('complains.*');
    // }

    public function scopeFilterByGroupUnit($query, $groupUnit = null)
    {
        return $query->when($groupUnit, function ($q) use ($groupUnit) {
            $q->join('simpletabctm.tblwilayah as a', 'complains.area', '=', 'a.id')
                ->where('a.group_unit', $groupUnit)
                ->select('complains.*');
        });
    }

    public function pbk()
    {
        return $this->belongsTo(CtmPbkNew::class, 'pbk_id', 'Name')->select('*');
    }

    public function scopeFilterPbk($query, $user_id)
    {
        if ($user_id != '') {
            $query->where('complains.pbk_id', $user_id);
        }
        return $query;
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id')->with('action')->select('*');
    }

    public function scopeFilterArea($query, $status)
    {
        if ($status != '') {
            $query->where('complains.area', $status);
        }
        return $query;
    }

    public function scopeFilterUser($query, $user_id)
    {
        if ($user_id != '') {
            $query->where('complains.user_id', $user_id);
        }
        return $query;
    }

    public function areas()
    {
        return $this->belongsTo(CtmWilayah::class, 'area', 'id');
    }

    public function complainstatus()
    {
        return $this->belongsTo(ComplainStatus::class, 'complain_status_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->with('staffs')->select('*');
    }

    public function dapertementReceive()
    {
        return $this->belongsTo(Dapertement::class, 'dapertement_receive_id', 'id');
    }

    public function dapertement()
    {
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\Category')->with('categorygroup')->with('categorytype')->select('*');
    }

    public function check()
    {
        return $this->hasMany(ComplainCheck::class, 'complain_id', 'id')->with('staff')->with('subdapertement')->select('*');
    }

    public function scopeFilterComplainStatus($query)
    {
        if (! empty(request()->input('complain_status'))) {
            $query->where('complains.complain_status_id', request()->input('complain_status'));
        }
        return $query;
    }

    public function scopeFilterStatus($query)
    {
        if (! empty(request()->input('status'))) {
            $query->where('complains.status', request()->input('status'));
        }
        return $query;
    }

    public function scopeFilterDepartment($query, $department)
    {
        if ($department != '') {
            $query->where('complains.dapertement_id', $department);
        }
        return $query;
    }

    public function scopeFilterJoinStatus($query, $status)
    {
        if ($status != '') {
            $query->where('complains.status', $status);
        }
        return $query;
    }

    public function scopeFilterSubDepartment($query, $subdepartment)
    {
        if ($subdepartment != '') {
            $query->join('complain_action', function ($join) use ($subdepartment) {
                $join->on('complain_action.complain_id', '=', 'complains.id')
                    ->where('complain_action.subdapertement_id', '=', $subdepartment);
            });
        }
        return $query;
    }

    public function scopeFilterDate($query, $from, $to)
    {
        if (! empty(request()->input('from')) && ! empty(request()->input('to'))) {
            $from = request()->input('from');
            $to   = request()->input('to');
            //$from = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            //$to = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            // $from = '2021-09-01';
            // $to = '2021-09-20';
            //return $query->whereBetween('complains.created_at', [$from, $to]);
            return $query->whereRaw("DATE(complains.created_at) >= '" . $from . "' AND DATE(complains.created_at) <= '" . $to . "'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {
            return;
        }
    }
}
