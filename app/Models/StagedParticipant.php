<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StagedParticipant extends Model
{
    protected $fillable = [
        'import_batch_id',
        'status',
        'document',
        'first_name',
        'last_name',
        'email',
        'existing_participant_id',
        'roles',
        'error',
        'raw',
    ];

    protected $casts = [
        'roles' => 'array',
        'raw'   => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}
