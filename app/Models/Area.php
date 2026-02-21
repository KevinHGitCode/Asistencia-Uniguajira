<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'dependency_id'];

    public function dependency()
    {
        return $this->belongsTo(Dependency::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
