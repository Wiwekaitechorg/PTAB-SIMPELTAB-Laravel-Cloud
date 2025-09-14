<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftParent extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'code',
        'title',
        'dapertement_id',
        'job_id',
        'subdapertement_id',
        'work_unit_id',
        'created_at',
        'updated_at',
    ];
}
