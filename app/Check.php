<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    protected $table = 'checks';
    protected $fillable = [
        'description',
        'status',
        'dapertement_id',
        'ticket_id',
        'start',
        'end',
        'memo',
        'image',
        'image_prework',
        'image_tools',
        'image_done',
        'subdapertement_id',
        'todo',
        'spk',
    ];

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'check_staff', 'check_id', 'staff_id')
            ->withPivot([
                'status'
            ]);
    }

    public function dapertement()
    {
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id');
    }

    public function subdapertement()
    {
        return $this->belongsTo(Subdapertement::class, 'subdapertement_id', 'id');
    }

    public function ticket()
    {
        return $this->belongsTo('App\Ticket')->select('id', 'title', 'code');
    }
}
