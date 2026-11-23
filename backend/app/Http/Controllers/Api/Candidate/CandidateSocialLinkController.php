<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Resources\CandidateSocialLinkResource;
use App\Models\CandidateSocialLink;
use App\Models\SocialMediaPlatform;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CandidateSocialLinkController extends Controller
{
    /**
     * GET /api/candidate/social-links
     */
    public function index(Request $request): JsonResponse
    {
        $links = $request->user()
            ->candidateSocialLinks()
            ->with('platform')
            ->get();

        return response()->json([
            'data' => CandidateSocialLinkResource::collection($links),
        ], 200);
    }

    /**
     * POST /api/candidate/social-links
     * Add or update a social link for the authenticated candidate.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform_id' => 'required|exists:social_media_platforms,id',
            'url'         => 'required|url|max:2048',
        ]);

        $platform = SocialMediaPlatform::findOrFail($validated['platform_id']);

        if (! $platform->matchesDomain($validated['url'])) {
            throw ValidationException::withMessages([
                'url' => ["The URL must belong to the {$platform->name} domain ({$platform->base_domain})."],
            ]);
        }

        $link = CandidateSocialLink::updateOrCreate(
            [
                'candidate_id' => $request->user()->id,
                'platform_id'  => $platform->id,
            ],
            [
                'url' => $validated['url'],
            ]
        );

        $link->load('platform');

        return (new CandidateSocialLinkResource($link))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * DELETE /api/candidate/social-links/{id}
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $link = $request->user()
            ->candidateSocialLinks()
            ->where(function ($query) use ($id) {
                $query->where('id', $id)
                    ->orWhere('platform_id', $id);
            })
            ->first();

        if (! $link) {
            return response()->json([
                'message' => 'Social link not found.',
            ], 404);
        }

        $link->delete();

        return response()->json([
            'message' => 'Social link deleted successfully.',
        ], 200);
    }
}
