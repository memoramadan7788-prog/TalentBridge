<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $table = 'admins';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function accountStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminRole::class,
            'admin_role_assignments',
            'admin_id',
            'admin_role_id'
        )->withPivot('assigned_by_admin_id', 'assigned_at');
    }

    public function getAllPermissions(): Collection
    {
        $roles = $this->relationLoaded('roles')
            ? $this->roles
            : $this->roles()->with('permissions')->get();

        return $roles->flatMap(fn (AdminRole $role) => $role->permissions->pluck('key'))
            ->unique()
            ->values();
    }

    public function hasPermission(string $key): bool
    {
        return $this->getAllPermissions()->contains($key);
    }

    public function hasPermissionForCompany(string $key, ?int $companyId = null): bool
    {
        return $this->hasPermission($key);
    }

    public function isAdmin(): bool
    {
        return true;
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles()
            ->where('admin_roles.name', 'super_admin')
            ->exists();
    }

    public function isActive(): bool
    {
        return $this->status_id === Status::ACCOUNT_ACTIVE;
    }

    /** Preserve the legacy account status key for existing API consumers. */
    public function getAccountStatusIdAttribute(): ?int
    {
        return isset($this->attributes['account_status_id']) ? (int) $this->attributes['account_status_id'] : (isset($this->attributes['status_id']) ? (int) $this->attributes['status_id'] : null);
    }

    public function setAccountStatusIdAttribute(int|string|null $value): void
    {
        $this->attributes['status_id'] = $value === null ? null : (int) $value;
    }

    public function resolveCompanyId(?int $explicitCompanyId = null): ?int
    {
        $companyId = $explicitCompanyId
            ?? request()?->query('company_id')
            ?? request()?->header('X-Company-Id')
            ?? request()?->input('company_id');

        return $companyId ? (int) $companyId : null;
    }
}
