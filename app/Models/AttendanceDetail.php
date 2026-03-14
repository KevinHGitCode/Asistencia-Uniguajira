<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'sexo',
        'telefono',
        'address_id',
        'grupo_priorizado',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
