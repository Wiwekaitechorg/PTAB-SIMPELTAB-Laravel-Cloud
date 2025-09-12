<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubdapertementApi extends Model
{
    protected $table = 'subdapertements';
    protected $fillable = [
        'dapertement_id',
        'code',
        'name',
        'description'
    ];

    public function dapertement() { 
        return $this->belongsTo(DapertementApi::class, 'dapertement_id', 'id'); 
    }
}
