<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    //
    protected $fillable = ['counsellor_id','date','time'];
    protected $casts = ['date' => 'date:Y-m-d'];
}
