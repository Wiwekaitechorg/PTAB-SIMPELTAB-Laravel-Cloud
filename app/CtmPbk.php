<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmPbk extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'pbk';

    protected $primaryKey = ['Number'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Status',
    ];
}
