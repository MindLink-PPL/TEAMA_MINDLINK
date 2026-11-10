<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounsellingRequest extends Model
{
    use HasFactory;

    protected $table = 'counselling_requests';
    protected $primaryKey = 'request_id';

    protected $fillable = [
        'student_id',
        'counsellor_name',
        'concern',
        'goals',
        'appointment_date',
        'appointment_time',
        'appointment_mode',
        'status'
    ];
}