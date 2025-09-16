<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkUnit extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'code',
        'name',
        'serial_number',
        'lat',
        'lng',
        'radius',
    ];

    public function scopeWithStaffAndType($query, $staffId)
    {
        return $query->join('staffs', 'staffs.work_unit_id', '=', 'work_units.id')
            ->join('work_types', 'staffs.work_type_id', '=', 'work_types.id')
            ->where('staffs.id', $staffId);
    }
}
