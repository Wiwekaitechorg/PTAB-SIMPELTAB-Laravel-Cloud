<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ShiftGroupTimesheetFilter;

class ShiftGroupTimesheets extends Model
{
    use HasFactory;
    use ShiftGroupTimesheetFilter;

    protected $fillable = [
        'id',
        'shift_group_id',
        'absence_category_id',
        'time',
        'start',
        'end',
        'duration',
        'created_at',
        'updated_at',
        'duration_exp',
    ];

    // relations
    public function absenceCategory()
    {
        return $this->belongsTo(Absence_categories::class, 'absence_category_id');
    }

    public function shiftGroup()
    {
        return $this->belongsTo(ShiftGroups::class, 'shift_group_id');
    }
}
