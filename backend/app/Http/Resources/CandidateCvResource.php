<?php

namespace App\Http\Resources;

use App\Models\CandidateCv;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CandidateCv
 */
class CandidateCvResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'is_default' => (bool) $this->is_default,
            'status_id' => $this->status_id,
            'status' => $this->recordStatus?->name,
            'ats_score' => $this->ats_score !== null ? (float) $this->ats_score : null,
            'ats_strengths' => $this->ats_strengths ?? [],
            'ats_weaknesses' => $this->ats_weaknesses ?? [],
            'ai_scan' => $this->whenLoaded('scan', fn () => $this->scan ? [
                'status' => $this->scan->status,
                'score' => $this->scan->score !== null ? (float) $this->scan->score : null,
                'summary' => $this->scan->summary,
                'extracted_data' => $this->scan->extracted_data,
                'strengths' => $this->scan->strengths->pluck('content')->all(),
                'weaknesses' => $this->scan->weaknesses->pluck('content')->filter(fn ($item) => ! preg_match('/^(none|none mentioned|not mentioned|not specified|not provided|n\/?a|no weaknesses|no specific weaknesses|لا يوجد|لا توجد|غير مذكور|غير متوفر)[.!،。 ]*$/iu', trim((string) $item)))->values()->all(),
                'suggested_jobs' => collect(data_get($this->scan->extracted_data, 'suggested_jobs', []))->map(function ($job) {
                    $title = is_array($job) ? ($job['title'] ?? '') : (string) $job;
                    $pending = $this->scan->titleSuggestions->firstWhere('normalized_title', mb_strtolower($title));
                    return ['title' => $title, 'confidence' => is_array($job) ? ($job['confidence'] ?? null) : null, 'approval_status' => $pending?->status ?? 'available'];
                })->values()->all(),
                'error' => $this->scan->error_message,
                'finished_at' => $this->scan->finished_at?->toIso8601String(),
            ] : null),
            'uploaded_at' => $this->uploaded_at?->toIso8601String(),
        ];
    }
}
