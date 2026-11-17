<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Availability extends Model
{
    protected $fillable = [
        'counsellor_id',
        'date',
        'time',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function counsellor()
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }
}