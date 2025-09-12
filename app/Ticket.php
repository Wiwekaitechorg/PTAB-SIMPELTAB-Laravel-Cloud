<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';
    protected $dates = ['delegated_at'];
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
    ];
    
    public function check()
    {
        return $this->hasMany(Check::class, 'ticket_id', 'id')->with('staff')->with('subdapertement')->select('*');
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

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'nomorrekening');
    }

    public function category()
    {
        return $this->belongsTo('App\Category')->with('categorygroup')->with('categorytype')->select('*');
    }

    public function action()
    {
        return $this->hasMany(Action::class, 'ticket_id', 'id')->with('staff')->with('subdapertement')->select('*');
    }
    public function ticket_image()
    {
        return $this->hasMany('App\Ticket_Image', 'ticket_id', 'id');
    }

    public function scopeFilterStatus($query, $status)
    {
        if ($status != '') {
            $query->where('tickets.status', $status);
        }
        return $query;
    }

    public function scopeFilterDepartment($query, $department)
    {
        if ($department != '') {
            $query->where('tickets.dapertement_id', $department);
        }
        return $query;
    }

    public function scopeFilterJoinStatus($query, $status)
    {
        if ($status != '') {
            $query->where('tickets.status', $status);
        }
        return $query;
    }

    public function scopeFilterJoinDepartment($query, $department)
    {
        if ($department != '') {
            $query->where('actions.dapertement_id', $department);
        }
        return $query;
    }

    public function scopeFilterJoinSubDepartment($query, $subdepartment)
    {
        if ($subdepartment != '') {
            $query->where('actions.subdapertement_id', $subdepartment);
        }
        return $query;
    }

    public function scopeFilterSubDepartment($query, $subdepartment)
    {
        if ($subdepartment != '') {
            $query->join('actions', function ($join) use ($subdepartment) {
                $join->on('actions.ticket_id', '=', 'tickets.id')
                    ->where('actions.subdapertement_id', '=', $subdepartment);
            });
        }
        return $query;
    }

    public function scopeFilterStaff($query, $staff)
    {
        if ($staff != '') {
            $query->join('action_staff', function ($join) use ($staff) {
                $join->on('action_staff.action_id', '=', 'actions.id')
                    ->where('action_staff.staff_id', '=', $staff);
            });
        }
        return $query;
    }

    public function scopeFilterDate($query, $from, $to)
    {
        if (!empty(request()->input('from')) && !empty(request()->input('to'))) {
            $from = request()->input('from');
            $to =  request()->input('to');
            //$from = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            //$to = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            // $from = '2021-09-01';
            // $to = '2021-09-20';
            //return $query->whereBetween('tickets.created_at', [$from, $to]);
            return $query->whereRaw("DATE(tickets.created_at) >= '".$from."' AND DATE(tickets.created_at) <= '".$to."'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {
            return;
        }
    }
}
