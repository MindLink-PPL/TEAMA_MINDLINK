<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounsellingRequest extends Model
{
    use HasFactory;

    protected $table = 'counselling_requests';
    protected $primaryKey = 'id';

    protected $appends = [
        'request_id',
    ];

    protected $fillable = [
        'request_id',
        'user_id',
        'student_id',
        'student_code',
        'counsellor_name',
        'counsellor_id',
        'concern',
        'goals',
        'notes',
        'session_note',
        'note_status',
        'support_action',
        'follow_up_due',
        'student_notified_at',
        'consent_accepted_at',
        'appointment_date',
        'appointment_time',
        'appointment_mode',
        'scheduled_date',
        'scheduled_time',
        'session_mode',
        'status',
        'priority',
        'submitted_label',
        'counsellor_notes',
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
     * Synchronize paired attributes across teammates' implementations.
     */
    protected static function booted(): void
    {
        static::saving(function (CounsellingRequest $req) {
            if (empty($req->user_id) && ! empty($req->student_id)) {
                $req->user_id = $req->student_id;
            } elseif (empty($req->student_id) && ! empty($req->user_id)) {
                $req->student_id = $req->user_id;
            }

            if (empty($req->appointment_date) && ! empty($req->scheduled_date)) {
                $req->appointment_date = $req->scheduled_date;
            } elseif (empty($req->scheduled_date) && ! empty($req->appointment_date)) {
                $req->scheduled_date = $req->appointment_date;
            }

            if (empty($req->appointment_time) && ! empty($req->scheduled_time)) {
                $req->appointment_time = $req->scheduled_time;
            } elseif (empty($req->scheduled_time) && ! empty($req->appointment_time)) {
                $req->scheduled_time = $req->appointment_time;
            }

            if (empty($req->appointment_mode) && ! empty($req->session_mode)) {
                $req->appointment_mode = $req->session_mode;
            } elseif (empty($req->session_mode) && ! empty($req->appointment_mode)) {
                $req->session_mode = $req->appointment_mode;
            }
        });

        static::created(function (CounsellingRequest $req) {
            if (empty($req->attributes['request_id'])) {
                $req->forceFill(['request_id' => $req->id])->saveQuietly();
            }
        });
    }

    /**
     * Ensure $request->request_id always returns the primary key ID.
     */
    public function getRequestIdAttribute(): ?int
    {
        return $this->attributes['request_id'] ?? $this->attributes['id'] ?? null;
    }

    /**
     * Get the student who submitted this counselling request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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