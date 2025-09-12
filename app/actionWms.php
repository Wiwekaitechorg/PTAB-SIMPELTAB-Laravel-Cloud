<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class actionWms extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'code',
        'status',
        'proposal_wm_id',
        'memo',
        'noWM1',
        'brandWM1',
        'standWM1',
        'noWM2',
        'brandWM2',
        'standWM2',
        'subdapertement_id',
        'lat',
        'category',
        'lng',
        'old_image',
        'new_image',
        'image_done'
    ];

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'action_wm_staff', 'action_wm_id', 'staff_id');
    }

    // public function dapertement() { 
    //     return $this->belongsTo('App\Dapertement')->select('id', 'name'); 
    // }

    public function proposalwms()
    {
        return $this->belongsTo(proposalWms::class)->select('id');
    }
    public function scopeFilterAreas($query, $idareal)
    {
        if ($idareal != '') {
            $query->where('tblpelanggan.idareal', $idareal);
        }
        return $query;
    }
}
