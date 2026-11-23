<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    /** Account statuses keep the ids 1..4 they always had. */
    public const ACCOUNT_ACTIVE      = 1;
    public const ACCOUNT_DEACTIVATED = 2;
    public const ACCOUNT_BLOCKED     = 3;
    public const ACCOUNT_DELETED     = 4;

    protected $table = 'statuses';
    protected $guarded = [];

    /** @var array<string,int>|null "type:code" => id */
    private static ?array $byKey = null;

    /** @var array<int,string>|null id => code */
    private static ?array $byId = null;

    protected static function booted(): void
    {
        $flush = function () {
            self::$byKey = null;
            self::$byId = null;
        };

        static::saved($flush);
        static::deleted($flush);
    }

    private static function loadCache(): void
    {
        if (self::$byKey !== null) {
            return;
        }

        self::$byKey = [];
        self::$byId = [];

        foreach (static::query()->get(['id', 'type', 'code']) as $row) {
            self::$byKey[$row->type . ':' . $row->code] = (int) $row->id;
            self::$byId[(int) $row->id] = (string) $row->code;
        }
    }

    public static function idFor(?string $code, string $type): ?int
    {
        if ($code === null || $code === '') {
            return null;
        }

        self::loadCache();

        $id = self::$byKey[$type . ':' . $code] ?? null;

        if ($id === null) {
            throw new \InvalidArgumentException(
                sprintf('Unknown status "%s" for type "%s".', $code, $type)
            );
        }

        return $id;
    }

    /** @param array<int,string> $codes */
    public static function idsFor(array $codes, string $type): array
    {
        self::loadCache();

        $ids = [];

        foreach ($codes as $code) {
            if (isset(self::$byKey[$type . ':' . $code])) {
                $ids[] = self::$byKey[$type . ':' . $code];
            }
        }

        return $ids;
    }

    public static function keyFor(?int $id): ?string
    {
        if ($id === null) {
            return null;
        }

        self::loadCache();

        return self::$byId[$id] ?? null;
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where($this->qualifyColumn('type'), $type);
    }

    // Relations

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'status_id');
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'status_id');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'status_id');
    }

    public function companyEmployees(): HasMany
    {
        return $this->hasMany(CompanyEmployee::class, 'status_id');
    }

    public function companyInvitations(): HasMany
    {
        return $this->hasMany(CompanyInvitation::class, 'status_id');
    }

    public function candidateProfiles(): HasMany
    {
        return $this->hasMany(CandidateProfile::class, 'status_id');
    }

    public function candidateEducations(): HasMany
    {
        return $this->hasMany(CandidateEducation::class, 'status_id');
    }

    public function candidates(): HasMany
    {
        return $this->candidateProfiles();
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'status_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'status_id');
    }

    public function applicationHistoryFrom(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'from_status_id');
    }

    public function applicationHistoryTo(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'to_status_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'status_id');
    }

    public function offersByApproval(): HasMany
    {
        return $this->hasMany(Offer::class, 'approval_status_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'status_id');
    }

    public function interviewerAvailabilities(): HasMany
    {
        return $this->hasMany(InterviewerAvailability::class, 'status_id');
    }

    public function interviewBookings(): HasMany
    {
        return $this->hasMany(InterviewBooking::class, 'status_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'status_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'status_id');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'status_id');
    }

    public function gradingResults(): HasMany
    {
        return $this->hasMany(GradingResult::class, 'status_id');
    }

    public function cvMatchResults(): HasMany
    {
        return $this->hasMany(CvMatchResult::class, 'status_id');
    }

    public function calendarIntegrations(): HasMany
    {
        return $this->hasMany(CalendarIntegration::class, 'status_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'status_id');
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class, 'status_id');
    }

    public function agencyEngagements(): HasMany
    {
        return $this->hasMany(AgencyEngagement::class, 'status_id');
    }

    public function agencyEngagementSubmissions(): HasMany
    {
        return $this->hasMany(AgencyEngagementSubmission::class, 'status_id');
    }

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class, 'status_id');
    }

    public function candidateCvs(): HasMany
    {
        return $this->hasMany(CandidateCv::class, 'status_id');
    }

    public function offerLetterTemplates(): HasMany
    {
        return $this->hasMany(OfferLetterTemplate::class, 'status_id');
    }
}
