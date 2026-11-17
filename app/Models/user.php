<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

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
        'role',
        'role_id',
        'certificate_path',
        'verification_status',
        'rejection_reason',
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
     * Automatically keep the 'role' string column and 'role_id' foreign key in sync.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (! Schema::hasTable('roles')) {
                return;
            }

            // If role_id was explicitly set/changed, sync the string 'role' column
            if ($user->isDirty('role_id') && ! empty($user->role_id)) {
                $roleModel = Role::find($user->role_id);
                if ($roleModel) {
                    $user->attributes['role'] = $roleModel->slug === 'user' ? 'student' : $roleModel->slug;
                }
            }
            // If 'role' string was set (e.g. 'student', 'counsellor', 'admin') and role_id is empty, sync role_id
            elseif (! empty($user->attributes['role']) && empty($user->role_id)) {
                $rawRole = $user->attributes['role'];
                $slugCandidates = $rawRole === 'student' ? ['user', 'student'] : [$rawRole];

                $roleModel = Role::whereIn('slug', $slugCandidates)->first();
                if (! $roleModel) {
                    $defaultSlug = $rawRole === 'student' ? 'user' : $rawRole;
                    $roleModel = Role::firstOrCreate(
                        ['slug' => $defaultSlug],
                        ['name' => ucfirst($rawRole)]
                    );
                }

                $user->role_id = $roleModel->id;
            }
            // Default new users without role/role_id to Student
            elseif (empty($user->role_id) && empty($user->attributes['role'])) {
                $user->attributes['role'] = 'student';
                $roleModel = Role::whereIn('slug', ['user', 'student'])->first();
                if ($roleModel) {
                    $user->role_id = $roleModel->id;
                }
            }
        });
    }

    /**
     * Ensure $user->role returns the Role relationship model when accessed as an object property,
     * while keeping the 'role' database column intact for SQL queries.
     */
    public function getAttribute($key)
    {
        if ($key === 'role') {
            if ($this->relationLoaded('role')) {
                return $this->getRelationValue('role');
            }

            if (! empty($this->attributes['role_id'])) {
                return $this->getRelationValue('role');
            }

            if (! empty($this->attributes['role']) && Schema::hasTable('roles')) {
                $raw = $this->attributes['role'];
                $slugs = $raw === 'student' ? ['user', 'student'] : [$raw];

                return Role::whereIn('slug', $slugs)->first();
            }

            return null;
        }

        return parent::getAttribute($key);
    }

    /**
     * Get the role name string ('admin', 'counsellor', 'student') regardless of how the user was created.
     */
    public function getRoleStringAttribute(): string
    {
        if ($this->isAdmin()) {
            return 'admin';
        }

        if ($this->isCounsellor()) {
            return 'counsellor';
        }

        return 'student';
    }

    /**
     * Articles saved by the user.
     */
    public function savedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'saved_articles', 'user_id', 'article_id')
            ->withPivot('saved_at');
    }

    /**
     * Get the role that this user belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the wellbeing check-ins recorded by this student.
     */
    public function wellbeingCheckins(): HasMany
    {
        return $this->hasMany(WellbeingCheckin::class);
    }

    /**
     * Get the counselling requests / appointments for this student.
     */
    public function counsellingRequests(): HasMany
    {
        return $this->hasMany(CounsellingRequest::class);
    }

    /**
     * Get the support referrals recorded for this student.
     */
    public function studentReferrals(): HasMany
    {
        return $this->hasMany(StudentReferral::class, 'user_id');
    }

    /**
     * Check if the user has a specific role or one of given roles.
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $roleModel = $this->role;
        $userSlug = $roleModel ? $roleModel->slug : ($this->attributes['role'] ?? null);

        if (! $userSlug) {
            return false;
        }

        foreach ($roles as $role) {
            if ($userSlug === $role) {
                return true;
            }

            if (in_array($role, ['student', 'user'], true) && in_array($userSlug, ['student', 'user'], true)) {
                return true;
            }

            if (in_array($role, ['counsellor', 'manager'], true) && in_array($userSlug, ['counsellor', 'manager'], true)) {
                return true;
            }
        }

        return false;
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