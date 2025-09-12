<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActionApi extends Model
{
    protected $table = 'actions';
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
        return $this->belongsToMany(Staff::class, 'action_staff', 'action_id', 'staff_id')
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
