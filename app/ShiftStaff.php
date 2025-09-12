<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftStaff extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'staff_id',
        'shift_id',
        'date',
        'change_staff_id',
        'description',
        'created_at',
        'updated_at',
    ];

    public function scopeFilterDapertement($query, $dapertment)
    {
        if ($dapertment != '') {
            $query->where('shifts.dapertement_id', $dapertment);
        }
        return $query;
    }

    public function scopeFilterShift($query, $shift)
    {
        if ($shift != '') {
            $query->where('shift_id', $shift);
        }
        return $query;
    }

    public function scopeFilterStaff($query, $staff)
    {
        if ($staff != '') {
            $query->where('staff_id', $staff);
        }
        return $query;
    }
}
