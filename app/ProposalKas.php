<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalKas extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        // 'dapertement_id',
    ];

    public function dapertements()
    {
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
