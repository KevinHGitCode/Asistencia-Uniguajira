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
        'ended_at',
        'location',
        'link',
        'user_id',
        'dependency_id',
        'area_id',
    ];

    protected $casts = [
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asistencias()
    {
        return $this->hasMany(Attendance::class, 'event_id')
            ->orderBy('created_at', 'asc');
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

    /**
     * El evento está abierto para registro si no fue terminado manualmente
     * y la hora actual no ha superado su end_time (si tiene).
     */
    public function isOpenForAttendance(): bool
    {
        if ($this->ended_at !== null) {
            return false;
        }

        if ($this->end_time !== null) {
            $endDateTime = \Carbon\Carbon::parse($this->date . ' ' . $this->end_time);
            if (now()->gt($endDateTime)) {
                return false;
            }
        }

        return true;
    }

    public function isManuallyEnded(): bool
    {
        return $this->ended_at !== null;
    }

    /**
     * El evento aún no ha comenzado (start_time en el futuro).
     */
    public function hasNotStarted(): bool
    {
        if ($this->start_time === null) {
            return false;
        }

        $startDateTime = \Carbon\Carbon::parse($this->date . ' ' . $this->start_time);

        return now()->lt($startDateTime);
    }
}
