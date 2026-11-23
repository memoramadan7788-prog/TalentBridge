<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visibility extends Model
{
    protected $table = 'visibilities';
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'int';

    public function jobs(): HasMany { return $this->hasMany(Job::class, 'visibility_id'); }
    public function companies(): HasMany { return $this->hasMany(Company::class, 'visibility_id'); }
    public function candidateProfiles(): HasMany { return $this->hasMany(CandidateProfile::class, 'visibility_id'); }
    public function candidateEducations(): HasMany { return $this->hasMany(CandidateEducation::class, 'visibility_id'); }
    public function applications(): HasMany { return $this->hasMany(Application::class, 'visibility_id'); }
    public function offers(): HasMany { return $this->hasMany(Offer::class, 'visibility_id'); }
    public function interviews(): HasMany { return $this->hasMany(Interview::class, 'visibility_id'); }
    public function exams(): HasMany { return $this->hasMany(Exam::class, 'visibility_id'); }
    public function contactMessages(): HasMany { return $this->hasMany(ContactMessage::class, 'visibility_id'); }
    public function agencyEngagements(): HasMany { return $this->hasMany(AgencyEngagement::class, 'visibility_id'); }
}
