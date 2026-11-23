<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/** under_review / accepted / rejected - shared by every table that needs an approval. */
class ApprovalStatus extends LookupModel
{
    protected $table = 'approval_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;

    public function companies(): HasMany { return $this->hasMany(Company::class, 'approval_status_id'); }
    public function companyEmployees(): HasMany { return $this->hasMany(CompanyEmployee::class, 'approval_status_id'); }
    public function candidateProfiles(): HasMany { return $this->hasMany(CandidateProfile::class, 'approval_status_id'); }
}
