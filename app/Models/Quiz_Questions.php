<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz_Questions extends Model
{
    use HasFactory;

    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question_text',
        'order',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz_Catalog::class, 'quiz_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(Quiz_Options::class, 'question_id');
    }
}