<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CandidateCourse
 */
class CandidateCoursesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'candidate_id' => $this->candidate_id,
            'name'         => $this->name,
            'link'         => $this->link,
            'image_path'   => $this->image_path,
            'UID'          => $this->UID,
            'LUID'         => $this->LUID,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
            'deleted_at'   => $this->whenNotNull($this->deleted_at?->toIso8601String()),
        ];
    }
}

