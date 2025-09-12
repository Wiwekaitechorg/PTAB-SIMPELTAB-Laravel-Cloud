<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryWa extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];
}
