<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\Participant;

class Role extends Model
{

    protected $fillable = [
        'role_type',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class, 'role_id');
    }
}
