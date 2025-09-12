<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ComparisonAbsences extends Model
{
    use HasFactory;
    protected $fillable = [
        'nik',
        'date',
        'from_sistem',
        'description'

    ];
}
