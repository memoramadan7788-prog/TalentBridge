<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobHiringTeamMember extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    protected $table = 'job_hiring_team';
    protected $guarded = [];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function lookups(): array
    {
        return [
            'role' => ['hiring_team_role_id', HiringTeamRole::class, 'member'],
        ];
    }

    public function hiringTeamRole(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(HiringTeamRole::class, 'hiring_team_role_id');
    }
}
