<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'counsellor_id',
        'counselling_request_id',
        'student_code',
        'referral_type',
        'urgency',
        'reason',
        'support_plan',
        'status',
    ];

    /**
     * Get the student being referred for support.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the counsellor who created the referral.
     */
    public function counsellor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }

    /**
     * Get the associated counselling request, if any.
     */
    public function counsellingRequest(): BelongsTo
    {
        return $this->belongsTo(CounsellingRequest::class);
    }
}
