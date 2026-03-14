<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Format extends Model
{
    protected $fillable = ['name', 'slug'];

    public function dependencies()
    {
        return $this->belongsToMany(Dependency::class, 'dependency_format');
    }
}