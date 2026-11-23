<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\UpdateProfileRequest;
use App\Http\Resources\CandidateProfileResource;
use App\Models\AccountStatus;
use App\Models\CandidateProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateProfileController extends Controller
{
    /**
     * GET /api/candidate/profile
     */
    public function show(Request $request): JsonResponse
    {
        $profile = CandidateProfile::firstOrCreate(
            ['user_id' => $request->user()->id]
        );

        return (new CandidateProfileResource(
            $profile->load(['user', 'skills', 'candidateExperiences', 'candidateEducations', 'candidateCourses', 'candidateCvs', 'candidateSocialLinks.platform'])
        ))->response()->setStatusCode(200);
    }

    /**
     * PUT /api/candidate/profile
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = CandidateProfile::firstOrCreate(
            ['user_id' => $request->user()->id]
        );

        $profile->update($request->validated());

        return (new CandidateProfileResource(
            $profile->load(['user', 'skills', 'candidateExperiences', 'candidateEducations', 'candidateCourses', 'candidateCvs', 'candidateSocialLinks.platform'])
        ))->response()->setStatusCode(200);
    }

    /**
     * POST /api/candidate/avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        $file = $request->file('avatar');
        $path = $file->store("avatars/{$profile->id}", 'public');

        $profile->update(['photo_path' => $path]);

        return response()->json([
            'message'    => 'Avatar uploaded successfully.',
            'photo_path' => $path,
            'photo_url'  => asset('storage/' . $path),
        ], 200);
    }

    /**
     * DELETE /api/candidate/account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->update(['account_status_id' => AccountStatus::DELETED]);

        return response()->json([
            'message' => 'Candidate account deleted successfully.',
        ], 200);
    }

    /**
     * GET /api/candidate/notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        $query = $request->user()->receivedNotifications()->latest();

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $perPage = (int) $request->input('per_page', 15);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $notifications->items(),
            'meta'   => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
                'unread_count' => $request->user()->receivedNotifications()->whereNull('read_at')->count(),
            ],
        ], 200);
    }

    /**
     * PATCH /api/candidate/notifications/{id}/read
     */
    public function markNotificationAsRead(Request $request, $id): JsonResponse
    {
        $notification = $request->user()->receivedNotifications()->findOrFail($id);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'message'      => 'Notification marked as read.',
            'notification' => $notification,
        ], 200);
    }

    /**
     * PATCH /api/candidate/notifications/read-all
     */
    public function markAllNotificationsAsRead(Request $request): JsonResponse
    {
        $updatedCount = $request->user()->receivedNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message'       => 'All notifications marked as read.',
            'updated_count' => $updatedCount,
        ], 200);
    }
}
