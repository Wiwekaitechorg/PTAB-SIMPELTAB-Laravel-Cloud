<?php
namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'staff_id',
        'title',
        'start',
        'end',
        'type',
        'time',
        'status',
        'category',
        'description',
        'attendance',
        'created_at',
        'updated_at',
        'attendance',

    ];

    public function scopeCurrentExtraNight($query, $staffId)
    {
        // no pending status at night
        return $query->where('start', '<=', now())
            ->where('end', '>=', now())
            ->where('category', 'extra')
            ->where('staff_id', $staffId)
            ->where(function ($q) {
                $q->where('status', 'approve')
                  ->orWhere('status', 'active');
            })
            ->orderByRaw("FIELD(status,'active','approve')");
    }

    public function scopeActiveNow($q)
    {
        return $q->where('start', '<=', now())
                 ->where('end', '>=', now())
                 ->where(function ($query) {
                     $query->where('status', 'approve')
                           ->orWhere('status', 'active');
                 })
                 ->orderBy(DB::raw('FIELD(status,"active","approve")'));
    }

    public function scopeForStaff($q, $staffId)
    {
        return $q->where('staff_id', $staffId);
    }

    public function scopeByType(Builder $query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getCreatedAtAttribute()
    {
        $timeStamp = date("Y-m-d H:i:s", strtotime($this->attributes['created_at']));
        return $timeStamp;
    }

    public function scopeFilterCategory($query, $category)
    {
        if ($category != '') {
            $query->where('absence_requests.category', $category);
        }
        return $query;
    }

    public function scopeFilterStatus($query, $status)
    {
        if ($status != '') {
            $query->where('absence_requests.status', $status);
        }
        return $query;
    }

    public function scopeFilterDate($query, $from, $to)
    {
        if (! empty(request()->input('from')) && ! empty(request()->input('to'))) {
            $from = request()->input('from');
            $to   = request()->input('to');
            // $from = '2021-09-01';
            // $to = '2021-09-20';
            //return $query->whereBetween('lock_action.created_at', [$from, $to]);
            //return $query->whereBetween(DB::raw('DATE(absence_requests.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(absence_requests.created_at) >= '" . $from . "' AND DATE(absence_requests.created_at) <= '" . $to . "'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {
            if (date('d') > 20) {
                $from = date("Y-m-d", strtotime(date('Y-m') . "-21"));
                $to   = date("Y-m-d", strtotime('+1 month', strtotime(date('Y-m') . "-20")));
            } else {
                $from = date("Y-m-d", strtotime('-1 month', strtotime(date('Y-m') . "-21")));
                $to   = date("Y-m-d", strtotime('0 month', strtotime(date('Y-m') . "-20")));
            }

            //return $query->whereBetween(DB::raw('DATE(absence_requests.created_at)'), [$from, $to]);
            return $query->whereRaw("DATE(absence_requests.created_at) >= '" . $from . "' AND DATE(absence_requests.created_at) <= '" . $to . "'");
        }
    }

    public function scopeFilterDateStart($query, $from, $to)
    {
        if (! empty(request()->input('from')) && ! empty(request()->input('to'))) {
            $from = request()->input('from');
            $to   = request()->input('to');
            // $from = '2021-09-01';
            // $to = '2021-09-20';
            //return $query->whereBetween('lock_action.created_at', [$from, $to]);
            //return $query->whereBetween(DB::raw('DATE(absence_requests.start)'), [$from, $to]);
            return $query->whereRaw("DATE(absence_requests.start) >= '" . $from . "' AND DATE(absence_requests.start) <= '" . $to . "'");
            // return $query->where('froms_id', $from);
            // dd(request()->input('from'));

        } else {

            return $query;
        }
    }

    public function scopeFilterDapertement($query, $dapertement)
    {
        if ($dapertement != '') {
            $query->where('staffs.dapertement_id', $dapertement);
        }
        return $query;
    }

    public function scopeActiveInPeriod($query, $now)
    {
        return $query->where('start', '<=', $now)
            ->where('end', '>=', $now);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeApprovedOrActive($query)
    {
        return $query->whereIn('status', ['approve', 'active']);
    }

    public function scopeGeofenceOff($query)
    {
        return $query->whereIn('category', ['geolocation_off', 'forget', 'AdditionalTime'])
            ->where('status', 'approve');
    }

    public function scopeOrderActiveApproveFirst($query)
    {
        return $query->orderByRaw("FIELD(status, 'active', 'approve')");
    }
}
