<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateSkillsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->skill_id,
            'candidate_id' => $this->candidate_id,
            'skill_id'     => $this->skill_id,
            'level'        => $this->level ?? null,
            'skill'        => $this->whenLoaded('skill', function () {
                return [
                    'id'       => $this->skill->id,
                    'name'     => $this->skill->name,
                    'type'     => $this->skill->type,
                    'accepted' => (bool) $this->skill->accepted,
                ];
            }),
            'name'         => $this->relationLoaded('skill') && $this->skill ? $this->skill->name : null,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
