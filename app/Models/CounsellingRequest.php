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
        'counsellor_name',
        'concern',
        'notes',
        'consent_accepted_at',
        'scheduled_date',
        'scheduled_time',
        'session_mode',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'consent_accepted_at' => 'datetime',
        ];
    }

    /**
     * Get the student who submitted this counselling request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

