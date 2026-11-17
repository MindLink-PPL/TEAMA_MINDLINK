<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counsellor extends Model
{
    use HasFactory;

    protected $table = 'counsellors';
    protected $primaryKey = 'counsellor_id';
    protected $fillable = ['name', 'specialization'];
}