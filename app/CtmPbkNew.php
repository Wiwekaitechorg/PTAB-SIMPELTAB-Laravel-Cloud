<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class CtmPbkNew extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $connection = 'mysql2';

    protected $table = 'pbk';

    protected $primaryKey = 'Number';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Status',
    ];
}
