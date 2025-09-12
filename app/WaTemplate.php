<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaTemplate extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'code',
        'message',
        'name',
        'category_wa_id',
        'create',
    ];
}
