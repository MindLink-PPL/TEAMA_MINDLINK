<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz_Student_Result extends Model
{
    use HasFactory;

    protected $table = 'quiz_student_results';

    protected $fillable = [
        'user_id',
        'quiz_id',
        'total_score',
        'max_score',
        'distress_level',
        'ai_summary',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz_Catalog::class, 'quiz_id');
    }
}