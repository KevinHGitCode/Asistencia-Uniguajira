<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    public static function log(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        array  $metadata = [],
        ?int   $userId = null,
        ?int   $participantId = null,
    ): void {
        try {
            ActivityLog::create([
                'user_id'        => $userId ?? auth()->id(),
                'participant_id' => $participantId,
                'action'         => $action,
                'module'         => $module,
                'description'    => mb_substr($description, 0, 255),
                'subject_type'   => $subject ? class_basename($subject) : null,
                'subject_id'     => $subject?->getKey(),
                'ip_address'     => request()->ip(),
                'user_agent'     => mb_substr((string) request()->userAgent(), 0, 255),
                'metadata'       => ! empty($metadata) ? $metadata : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ActivityLogService::log failed: ' . $e->getMessage());
        }
    }
}
