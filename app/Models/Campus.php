<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function dependencies()
    {
        return $this->hasMany(Dependency::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}
