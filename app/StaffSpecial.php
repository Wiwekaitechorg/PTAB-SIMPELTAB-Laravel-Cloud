<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSpecial extends Model
{
    use HasFactory;
    protected $fillable = [
        'staff_id',
        'fingerprint',
        'camera',
        'gps',
        'expired_date'
    ];

    public function scopeValid($query)
    {
        return $query->whereDate('expired_date', '>=', date('Y-m-d'));
    }

    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }
}
