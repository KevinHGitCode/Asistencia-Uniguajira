<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
