<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformedConsent extends Model
{
    public const CURRENT_VERSION = '2026-v1';

    protected $table = 'informed_consents';

    protected $fillable = [
        'user_id',
        'policy_version',
        'agreed_at',
    ];

    protected $casts = [
        'agreed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function currentFor(User $user): ?self
    {
        return static::where('user_id', $user->id)
            ->where('policy_version', self::CURRENT_VERSION)
            ->first();
    }

    public function getStudentIdAttribute(): string
    {
        return $this->attributes['student_id']
            ?? ($this->user ? ($this->user->name . ' (ST-' . (180 + $this->user_id) . ')') : ('ST-' . (180 + ($this->user_id ?? $this->id))));
    }

    public function getIsAgreedAttribute(): bool
    {
        return ! empty($this->agreed_at);
    }
}
