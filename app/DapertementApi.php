<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DapertementApi extends Model
{
    protected $table = 'dapertements';
    protected $fillable = [
        'code',
        'name',
        'description'
    ];
}
