<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wa_template_file extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'wa_template_id',
        'file',
    ];
}
