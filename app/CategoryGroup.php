<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class CategoryGroup extends Model
{   
    // use SoftDeletes;

    protected $table = 'category_groups';
    protected $fillable = [
        'code',
        'name'
    ];
    // protected $dates = ['deleted_at'];
}
