<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformedConsent extends Model
{
    protected $table = 'informed_consents';

    protected $fillable = [
        'student_id',
        'is_agreed',
        'agreed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_agreed' => 'boolean',
            'agreed_at' => 'datetime',
        ];
    }
}