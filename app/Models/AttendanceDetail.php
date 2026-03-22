<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDetail extends Model
{
    protected $fillable = [
        'attendance_id',
        'participant_role_id',
        'gender',
        'phone',
        'city',
        'neighborhood',
        'address',
        'priority_group',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function participantRole()
    {
        return $this->belongsTo(ParticipantRole::class);
    }
}