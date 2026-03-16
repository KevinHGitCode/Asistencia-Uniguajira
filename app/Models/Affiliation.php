<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'affiliation_participant')
            ->withTimestamps();
    }
}
