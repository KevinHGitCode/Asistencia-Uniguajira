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
        
        $this->attendees = Attendance::with([
            'participant.activeRoles.type',
            'participant.activeRoles.program',
            'participant.activeRoles.dependency',
            'participant.activeRoles.affiliation',
            'detail.participantRole.type',
            'detail.participantRole.program',
            'detail.participantRole.dependency',
            'detail.participantRole.affiliation',
        ])
            ->where('event_id', $this->eventId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $this->totalAttendees = count($this->attendees);
    }

    public function render()
    {
        return view('livewire.event.attendees-modal');
    }
}