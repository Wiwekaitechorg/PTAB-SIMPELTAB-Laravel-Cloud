<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dapertement extends Model
{
    protected $table = 'dapertements';
    protected $fillable = [
        'code',
        'name',
        'description',
        'director_id'
    ];

    public function scopeFilterCode($query, $id)
    {
        if ($id != '') {
            $query->where('code', $id);
        }
        return $query;
    }
}
