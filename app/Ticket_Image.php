<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket_Image extends Model
{
    protected $table ='ticket_image';
    protected $fillable = [
        'ticket_id',
        'image'
    ];
}
