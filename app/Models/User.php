<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role that this user belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the user has a specific role or one of given roles.
     */
    public function hasRole(string|array $roles): bool
    {
        if (! $this->role) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($this->role->slug, $roles);
        }

        return $this->role->slug === $roles;
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if the user is a counsellor / staff member.
     */
    public function isCounsellor(): bool
    {
        return $this->hasRole(['counsellor', 'manager']);
    }

    /**
     * Check if the user is a student.
     */
    public function isStudent(): bool
    {
        return $this->hasRole(['student', 'user']);
    }

    /**
     * Get the display portal name for the header pill in Figma design.
     */
    public function getPortalNameAttribute(): string
    {
        if ($this->isAdmin()) {
            return 'Admin portal';
        }

        if ($this->isCounsellor()) {
            return 'Counsellor portal';
        }

        return 'Student portal';
    }

    /**
     * Check if the user has a specific permission via their role.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if (! $this->role) {
            return false;
        }

        // Administrators have all permissions
        if ($this->isAdmin()) {
            return true;
        }

        return $this->role->permissions->contains('slug', $permissionSlug);
    }

    /**
     * Get all permissions associated with the user's role.
     */
    public function getPermissionsAttribute(): Collection
    {
        return $this->role ? $this->role->permissions : new Collection();
    }
}
