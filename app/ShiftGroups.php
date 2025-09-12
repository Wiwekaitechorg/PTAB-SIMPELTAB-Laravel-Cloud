<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftGroups extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        // 'code',
        'title',
        'dapertement_id',
        'job_id',
        'subdapertement_id',
        'shift_parent_id',
        'created_at',
        'updated_at',
        'work_type_id',
        'work_unit_id',
        'queue',
        'type_s'
    ];


    public function scopeFilterSubDapertement($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('subdapertement_id', $ststussm);
        }
        return $query;
    }

    public function scopeFilterDapertement($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('dapertement_id', $ststussm);
        }
        return $query;
    }

    public function scopeFilterWorkUnit($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('work_unit_id', $ststussm);
        }
        return $query;
    }

    public function scopeFilterJob($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('job_id', $ststussm);
        }
        return $query;
    }
}
