<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceRequestLogs extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
        'absence_request_id',
        'type',
        'created_at',
        'updated_at',
    ];
}
