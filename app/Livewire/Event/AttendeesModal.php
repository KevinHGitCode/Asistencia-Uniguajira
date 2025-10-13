<?php

namespace App\Livewire\Event;

use Livewire\Component;
use App\Models\Event;
use App\Models\Attendance;

class AttendeesModal extends Component
{
    public $eventId;
    public $attendees = [];
    public $totalAttendees = 0;

    public function mount($eventId)
    {
        $this->eventId = $eventId;
        $this->loadAttendees();
    }

    public function loadAttendees()
    {
        $event = Event::findOrFail($this->eventId);
        
        $this->attendees = Attendance::with(['participant.program'])
            ->where('event_id', $this->eventId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $this->totalAttendees = count($this->attendees);
    }

    public function render()
    {
        return view('livewire.event.attendees-modal');
    }
}