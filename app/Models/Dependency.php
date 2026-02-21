<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependency extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Muchos a muchos: una dependencia puede tener varios usuarios,
    // y un usuario puede estar en varias dependencias.
    public function users()
    {
        return $this->belongsToMany(User::class, 'dependency_user')
                    ->withTimestamps();
    }


    // Una dependencia tiene muchos eventos
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

}
