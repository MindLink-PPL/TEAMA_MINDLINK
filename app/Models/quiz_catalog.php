<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz_Catalog extends Model
{
    use HasFactory;

    protected $table = 'quiz_catalog';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Quiz_Questions::class, 'quiz_id')->orderBy('order', 'asc');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Quiz_Student_Result::class, 'quiz_id');
    }
}