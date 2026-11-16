<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounsellingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_code',
        'counsellor_name',
        'counsellor_id',
        'concern',
        'notes',
        'session_note',
        'note_status',
        'support_action',
        'follow_up_due',
        'student_notified_at',
        'consent_accepted_at',
        'scheduled_date',
        'scheduled_time',
        'session_mode',
        'status',
        'priority',
        'submitted_label',
    ];

    protected function casts(): array
    {
        return [
            'consent_accepted_at' => 'datetime',
            'student_notified_at' => 'datetime',
            'follow_up_due' => 'boolean',
        ];
    }

    /**
     * Get the student who submitted this counselling request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assigned counsellor user, if linked.
     */
    public function counsellor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }

    /**
     * Get the institutional student reference code (e.g. ST-187).
     */
    public function getDisplayCodeAttribute(): string
    {
        if (! empty($this->student_code)) {
            return $this->student_code;
        }

        return 'ST-' . (180 + ($this->user_id ?? $this->id));
    }
}
