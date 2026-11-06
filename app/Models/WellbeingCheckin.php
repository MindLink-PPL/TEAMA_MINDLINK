<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WellbeingCheckin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mood',
        'answers',
        'total_score',
        'guidance',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
        ];
    }

    /**
     * Get the student who submitted this wellbeing check-in.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

