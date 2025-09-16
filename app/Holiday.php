<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'title',
        'description',
        'day_id',
        'start',
        'end',
    ];

    public function scopeToday($query)
    {
        return $query->whereDate('start', '<=', today())
                     ->whereDate('end', '>=', today());
    }
}
