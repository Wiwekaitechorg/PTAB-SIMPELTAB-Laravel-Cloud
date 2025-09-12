<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'staff_id',
        'memo',
        'type',
        'status',
        'lat',
        'lng',
        'created_at',
    ];

    public function getCreatedAtAttribute()
    {
        $timeStamp = date("Y-m-d H:i:s", strtotime($this->attributes['created_at']));
        return $timeStamp;
    }
}
