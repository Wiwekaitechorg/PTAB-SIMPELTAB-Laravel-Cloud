<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckApi extends Model
{
    protected $table = 'checks';
    protected $fillable =[
        'description',
        'status',
        'dapertement_id',
        'ticket_id',
        'start',
        'end',
        'memo',
        'image',
        'subdapertement_id',
        'todo',
        'spk',
        'image_prework',
        'image_tools',
        'image_done'
    ];

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'check_staff', 'check_id', 'staff_id')
            ->withPivot([
                'status'
            ]);
    }

    public function dapertement() { 
        return $this->belongsTo('App\Dapertement')->select('id', 'name'); 
    }

    public function ticket() { 
        return $this->belongsTo('App\Ticket')->select('id', 'title'); 
    }
}
