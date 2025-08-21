<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{

    protected $fillable = [
        'affiliation_type',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class, 'affiliation_id');
    }
}
