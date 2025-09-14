<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requests_file extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'file',
        'type',
        'requests_id',
        'created_at',
        'updated_at',
    ];
}
