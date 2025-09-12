<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'title',
        'date',
        'end',
        'type',
        'category',
        'start',
        'status',
        'created_at',
        'updated_at',
    ];
}
