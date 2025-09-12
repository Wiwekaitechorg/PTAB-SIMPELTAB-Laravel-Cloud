<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryApi extends Model
{
    protected $table = 'categories';
    protected $fillable = [
        'code',
        'name',
        'category_group_id',
        'category_type_id',
    ];
}
