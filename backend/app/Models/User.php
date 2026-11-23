<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use \App\Models\Concerns\HasLookupAttributes;

    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Auditable;


    protected $table = 'users';
    protected $guarded = [];

    protected $attributes = [
        'status_id' => Status::ACCOUNT_ACTIVE,
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

        public function accountStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function isActive(): bool
    {
        return $this->status_id === Status::ACCOUNT_ACTIVE;
    }

    public function isDeactivated(): bool
    {
        return $this->status_id === Status::ACCOUNT_DEACTIVATED;
    }

    public function isBlocked(): bool
    {
        return $this->status_id === Status::ACCOUNT_BLOCKED;
    }

    public function isDeleted(): bool
    {
        return $this->status_id === Status::ACCOUNT_DELETED;
    }

    /** Keep the legacy API/account payload key while the database stores status_id. */
    public function getAccountStatusIdAttribute(): ?int
    {
        return isset($this->attributes['account_status_id']) ? (int) $this->attributes['account_status_id'] : (isset($this->attributes['status_id']) ? (int) $this->attributes['status_id'] : null);
    }

    public function setAccountStatusIdAttribute(int|string|null $value): void
    {
        $this->attributes['status_id'] = $value === null ? null : (int) $value;
    }

public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function candidateProfile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class, 'user_id');
    }

    public function emailVerificationCodes(): HasMany
    {
        return $this->hasMany(EmailVerificationCode::class);
    }

    public function companyEmployee(): HasOne
    {
        return $this->hasOne(CompanyEmployee::class, 'user_id');
    }

    public function ownedCompany(): HasOne
    {
        return $this->hasOne(Company::class, 'owner_user_id');
    }

    /**
     * Backward-compatible relation name for older callers.
     * The database now enforces one company account per owner.
     */
    public function ownedCompanies(): HasOne
    {
        return $this->ownedCompany();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withPivot('company_id', 'assigned_by', 'assigned_at');
    }

    public function receivedNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_id');
    }

    public function chatbotSessions(): HasMany
    {
        return $this->hasMany(ChatbotSession::class, 'user_id');
    }

    public function calendarIntegrations(): HasMany
    {
        return $this->hasMany(CalendarIntegration::class, 'user_id');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(InterviewerAvailability::class, 'interviewer_id');
    }

    // ─── Phase 0: Permission & Company Resolution Helpers ─────────────

    /**
     * Resolve the company ID for the authenticated user.
     * Priority: owned companies → company_employee → user_roles pivot.
     */
    public function resolveCompanyId(?int $explicitCompanyId = null): ?int
    {
        if ($this->account_type === 'admin') {
            $companyId = $explicitCompanyId
                ?? request()?->query('company_id')
                ?? request()?->header('X-Company-Id')
                ?? request()?->input('company_id');

            return $companyId ? (int) $companyId : null;
        }

        $owned = $this->ownedCompany()->first();
        if ($owned) {
            return $owned->id;
        }

        $employee = $this->companyEmployee;
        if ($employee && in_array($employee->approval_status, ['accepted', 'under_review'])) {
            return $employee->company_id;
        }

        $roleWithCompany = $this->roles()->wherePivotNotNull('company_id')->first();
        if ($roleWithCompany) {
            return $roleWithCompany->pivot->company_id;
        }

        return null;
    }

    /**
     * Resolve and return the Company model for the authenticated user.
     */
    public function resolveCompany(): ?Company
    {
        $companyId = $this->resolveCompanyId();
        return $companyId ? Company::find($companyId) : null;
    }

    /**
     * Get all aggregated permission keys from the user's roles.
     */
    public function getAllPermissions(): Collection
    {
        $roles = $this->relationLoaded('roles') ? $this->roles : $this->roles()->with('permissions')->get();
        return $roles->flatMap(function ($role) {
            return $role->permissions->pluck('key');
        })->unique()->values();
    }

    /**
     * Check if the user has a specific permission via their roles.
     */
    public function hasPermission(string $key): bool
    {
        return $this->getAllPermissions()->contains($key);
    }

    /**
     * Check if the user has permission within the context of a specific company.
     */
    public function hasPermissionForCompany(string $key, ?int $companyId): bool
    {
        if ($this->account_type === 'admin') {
            return true;
        }

        if (!$companyId) {
            return $this->hasPermission($key);
        }

        // Company owner has all company-scoped permissions for their company
        if ($this->ownedCompany()->where('id', $companyId)->exists()) {
            return true;
        }

        return $this->roles()
            ->where(function ($q) use ($companyId) {
                $q->where('user_roles.company_id', $companyId)
                  ->orWhereNull('user_roles.company_id');
            })
            ->whereHas('permissions', fn ($q) => $q->where('key', $key))
            ->exists();
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(array $keys): bool
    {
        return $this->getAllPermissions()->intersect($keys)->isNotEmpty();
    }

    /**
     * Check if the user is an admin (account_type = admin).
     */
    public function isAdmin(): bool
    {
        return $this->account_type === 'admin';
    }

    protected function lookups(): array
    {
        return [
            'account_type' => ['account_type_id', AccountType::class, 'candidate'],
            'locale' => ['language_id', Language::class, 'ar'],
        ];
    }

    public function accountType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    public function language(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
