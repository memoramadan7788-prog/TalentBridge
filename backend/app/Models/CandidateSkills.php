<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateSkills extends Model
{
    protected $table = 'candidate_skills';

    public $incrementing = false;

    protected $fillable = [
        'candidate_id',
        'skill_id',
        'level',
        'UID',
        'LUID',
    ];

    // Optional: Define relationship back to skill
    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }
}
