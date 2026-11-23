<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\StoreSkillsRequest;
use App\Http\Resources\CandidateSkillsResource;
use App\Models\CandidateSkills;
use App\Models\CandidateProfile;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CandidateSkillsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        
        $skills = CandidateSkills::where('candidate_id', $profile->id)
            ->with('skill')
            ->get();

        return CandidateSkillsResource::collection($skills);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSkillsRequest $request): JsonResponse
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        $data = $request->validated();
        
        $skillId = $data['skill_id'] ?? null;

        // If a skill name is provided instead of (or in addition to) an ID
        if (!$skillId && !empty($data['skill_name'])) {
            $skillName = trim($data['skill_name']);

            // Look up the skill in the skills table case-insensitively
            $skill = Skill::where('name', 'LIKE', $skillName)->first();

            if ($skill) {
                $skillId = $skill->id;
            } else {
                $newSkill = Skill::create([
                    'name'     => $skillName,
                    'type'     => $data['type'] ?? 'tech',
                    'accepted' => true,
                ]);
                $skillId = $newSkill->id;
            }
        }

        if (!$skillId) {
            return response()->json([
                'message' => 'Please provide a valid skill selection or name.',
            ], 422);
        }

        $candidateSkill = CandidateSkills::updateOrCreate(
            [
                'candidate_id' => $profile->id,
                'skill_id'     => $skillId,
            ],
            [
                'level' => $data['level'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Skills added successfully.',
            'skills'  => new CandidateSkillsResource($candidateSkill->load('skill')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): CandidateSkillsResource
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        
        $skill = CandidateSkills::where('candidate_id', $profile->id)
            ->where('skill_id', $id)
            ->with('skill')
            ->firstOrFail();

        return new CandidateSkillsResource($skill);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        
        $candidateSkill = CandidateSkills::where('candidate_id', $profile->id)
            ->where('skill_id', $id)
            ->firstOrFail();

        $data = $request->all();
        $skillId = $data['skill_id'] ?? $id;

        if (!empty($data['skill_name'])) {
            $skillName = trim($data['skill_name']);
            $skill = Skill::where('name', 'LIKE', $skillName)->first();

            if ($skill) {
                $skillId = $skill->id;
            } else {
                $newSkill = Skill::create([
                    'name'     => $skillName,
                    'type'     => $data['type'] ?? 'tech',
                    'accepted' => true,
                ]);
                $skillId = $newSkill->id;
            }
        }

        $candidateSkill->where('candidate_id', $profile->id)
            ->where('skill_id', $id)
            ->update([
                'skill_id' => $skillId,
                'level'    => $data['level'] ?? $candidateSkill->level,
            ]);

        $updated = CandidateSkills::where('candidate_id', $profile->id)
            ->where('skill_id', $skillId)
            ->with('skill')
            ->first();

        return response()->json([
            'message' => 'Skills updated successfully.',
            'skills'  => new CandidateSkillsResource($updated),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        
        CandidateSkills::where('candidate_id', $profile->id)
            ->where('skill_id', $id)
            ->delete();

        return response()->json([
            'message' => 'Skills deleted successfully.',
        ], 200);
    }
}
