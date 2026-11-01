<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    //
    protected $fillable = ['student_id','counsellor_id','date','time','mode','status'];
    protected $casts = ['date' => 'date:Y-m-d'];

    public function counsellor() { return $this->belongsTo(User::class, 'counsellor_id'); }
}
