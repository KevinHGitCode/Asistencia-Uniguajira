<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Format extends Model
{
    protected $fillable = ['name', 'slug', 'file', 'mapping', 'mapping_outdated'];

    protected $casts = [
        'mapping' => 'array',
        'mapping_outdated' => 'boolean',
    ];

    public function dependencies()
    {
        return $this->belongsToMany(Dependency::class, 'dependency_format');
    }

    /**
     * ¿El mapeo necesita atención? True si el PDF cambió tras el último mapeo
     * (mapping_outdated) o si hay un PDF cargado pero aún sin coordenadas.
     */
    public function needsMapping(): bool
    {
        return $this->mapping_outdated || ($this->file && empty($this->mapping));
    }
}
