<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model
{   
    // use SoftDeletes;

    protected $table = 'categories';
    protected $fillable = [
        'code',
        'name',
        'category_group_id',
        'category_type_id',
    ];
    // protected $dates = ['deleted_at'];

    public function categorygroup() { 
        return $this->belongsTo(CategoryGroup::class, 'category_group_id', 'id'); 
    }

    public function categorytype() { 
        return $this->belongsTo(CategoryType::class, 'category_type_id', 'id'); 
    }
}
