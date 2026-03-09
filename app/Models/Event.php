<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'location',
        'link',
        'user_id',
        'dependency_id',
        'area_id', // agregar solo si existe en la migración
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asistencias()
    {
        return $this->hasMany(Attendance::class, 'event_id');
    }

    public function participantes()
    {
        return $this->belongsToMany(Participant::class, 'attendances', 'event_id', 'participant_id')
            ->withTimestamps();
    }

    public function dependency()
    {
        return $this->belongsTo(Dependency::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function getIsEditableAttribute(): bool
    {
        return \Carbon\Carbon::parse($this->date)->greaterThanOrEqualTo(now()->startOfDay());
    }

    public function getIsDeletableAttribute(): bool
    {
        return \Carbon\Carbon::parse($this->date)->greaterThanOrEqualTo(now()->startOfDay());
    }
}
