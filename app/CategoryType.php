<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class CategoryType extends Model
{   
    // use SoftDeletes;

    protected $table = 'category_types';
    protected $fillable = [
        'code',
        'name'
    ];
    // protected $dates = ['deleted_at'];
}
