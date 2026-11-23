<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\UploadCvRequest;
use App\Http\Resources\CandidateCvResource;
use App\Http\Resources\JobMatchResource;
use App\Http\Responses\ApiResponse;
use App\Jobs\RunCvScan;
use App\Models\CandidateCv;
use App\Models\CandidateProfile;
use App\Models\CvScan;
use App\Models\JobMatch;
use App\Models\Status;
use App\Services\Ats\JobMatchingDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CandidateCvController extends Controller
{
    /**
     * GET /api/candidate/cvs
     */
    public function index(Request $request): JsonResponse
    {
        $profile = CandidateProfile::where('user_id', $request->user()->id)->first();
        if (! $profile) {
            return ApiResponse::success(CandidateCvResource::collection(collect()));
        }
        $cvs = CandidateCv::with(['scan.points.pointType'])->where('candidate_id', $profile->id)->orderByDesc('created_at')->orderByDesc('id')->get();

        return ApiResponse::success(CandidateCvResource::collection($cvs));
    }

    /**
     * POST /api/candidate/cvs
     */
    public function store(UploadCvRequest $request): JsonResponse
    {
        $profile = CandidateProfile::where('user_id', $request->user()->id)->first();
        if (! $profile) {
            return ApiResponse::fromLegacy(['message' => 'Candidate profile not found.'], 404);
        }
        $file = $request->file('cv');
        $storedPath = $file->store("cvs/{$profile->id}", 'local');
        $hasDefault = CandidateCv::where('candidate_id', $profile->id)->where('is_default', true)->exists();
        $cv = CandidateCv::create(['candidate_id' => $profile->id, 'storage_path' => $storedPath, 'original_filename' => $file->getClientOriginalName(), 'mime_type' => $file->getClientMimeType() ?: 'application/pdf', 'size_bytes' => $file->getSize(), 'is_default' => ! $hasDefault, 'status_id' => Status::ACCOUNT_ACTIVE, 'uploaded_at' => now()]);
        $scan = CvScan::create([
            'cv_id' => $cv->id,
            'status' => CvScan::PENDING,
            'request_id' => (string) Str::uuid(),
            'UID' => $request->user()->id,
            'LUID' => $request->user()->id,
        ]);
        RunCvScan::dispatch($scan->id)->afterCommit();

        return ApiResponse::fromLegacy(['message' => 'CV uploaded successfully. AI scan started.', 'cv' => new CandidateCvResource($cv->load('scan.points.pointType'))], 201);
    }

    /**
     * GET /api/candidate/cvs/{id}/download
     */
    public function download(Request $request, int $id)
    {
        $profile = CandidateProfile::where('user_id', $request->user()->id)->first();
        if (! $profile) {
            return ApiResponse::fromLegacy(['message' => 'Candidate profile not found.'], 404);
        }

        $cv = CandidateCv::where('candidate_id', $profile->id)->findOrFail($id);
        $disk = Storage::disk('local');

        if (! $cv->storage_path || ! $disk->exists($cv->storage_path)) {
            return ApiResponse::fromLegacy(['message' => 'CV file not found.'], 404);
        }

        return $disk->download(
            $cv->storage_path,
            $cv->original_filename ?: basename($cv->storage_path),
            ['Content-Type' => $cv->mime_type ?: 'application/octet-stream']
        );
    }

    /**
     * PATCH /api/candidate/cvs/{id}/set-default
     */
    public function setDefault(Request $request, int $id): JsonResponse
    {
        $profile = CandidateProfile::where('user_id', $request->user()->id)->first();
        if (! $profile) {
            return ApiResponse::fromLegacy(['message' => 'Candidate profile not found.'], 404);
        }
        $cv = CandidateCv::where('candidate_id', $profile->id)->findOrFail($id);
        CandidateCv::where('candidate_id', $profile->id)->update(['is_default' => false]);
        $cv->update(['is_default' => true]);

        return ApiResponse::fromLegacy(['message' => 'Default CV updated successfully.', 'cv' => new CandidateCvResource($cv)], 200);
    }

    /** POST /api/candidate/cvs/{id}/rescan — rerun the profile scan for an owned CV. */
    public function rescan(Request $request, int $id): JsonResponse
    {
        $profile = CandidateProfile::where('user_id', $request->user()->id)->first();
        if (! $profile) {
            return ApiResponse::fromLegacy(['message' => 'Candidate profile not found.'], 404);
        }
        $cv = CandidateCv::where('candidate_id', $profile->id)->findOrFail($id);
        $scan = $cv->scan;
        if (! $scan) {
            $scan = CvScan::create([
                'cv_id' => $cv->id,
                'status' => CvScan::PENDING,
                'request_id' => (string) Str::uuid(),
                'UID' => $request->user()->id,
                'LUID' => $request->user()->id,
            ]);
        } else {
            $scan->update([
                'status' => CvScan::PENDING,
                'error_message' => null,
                'finished_at' => null,
                'request_id' => (string) Str::uuid(),
                'LUID' => $request->user()->id,
            ]);
        }
        RunCvScan::dispatch($scan->id)->afterCommit();

        return ApiResponse::fromLegacy(['message' => 'CV analysis queued.', 'data' => ['cv_id' => $cv->id, 'scan_status' => 'pending']], 202);
    }

    /**
     * POST /api/candidate/cvs/{id}/job-matches
     * Queue matching against the available jobs stored in jobs.
     */
    public function matchJobs(Request $request, int $id, JobMatchingDispatcher $dispatcher): JsonResponse
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        $cv = CandidateCv::where('candidate_id', $profile->id)->findOrFail($id);
        $scan = $cv->scan;
        if (! $scan || $scan->status !== CvScan::OK) {
            if (! $scan) {
                $scan = CvScan::create([
                    'cv_id' => $cv->id,
                    'status' => CvScan::PENDING,
                    'request_id' => (string) Str::uuid(),
                    'UID' => $request->user()->id,
                    'LUID' => $request->user()->id,
                ]);
            } elseif ($scan->status !== CvScan::PENDING) {
                $scan->update([
                    'status' => CvScan::PENDING,
                    'error_message' => null,
                    'finished_at' => null,
                    'request_id' => (string) Str::uuid(),
                    'LUID' => $request->user()->id,
                ]);
            }
            if ($scan->wasRecentlyCreated || $scan->wasChanged()) {
                RunCvScan::dispatch($scan->id)->afterCommit();
            }

            return ApiResponse::fromLegacy(['message' => 'The CV scan is not complete yet. AI scanning has been queued; retry job matching after it finishes.', 'data' => ['cv_id' => $cv->id, 'scan_status' => $scan->status]], 202);
        }
        $queued = $dispatcher->dispatchFor($cv, $request->user()->id);

        return ApiResponse::fromLegacy(['message' => 'Job matching queued.', 'data' => ['cv_id' => $cv->id, 'jobs_queued' => $queued]], 202);
    }

    /**
     * GET /api/candidate/cvs/{id}/job-matches
     */
    public function jobMatches(Request $request, int $id): JsonResponse
    {
        $profile = CandidateProfile::where('user_id', $request->user()->id)->first();
        if (! $profile) {
            return ApiResponse::collection([]);
        }

        $cv = CandidateCv::where('candidate_id', $profile->id)->findOrFail($id);
        $matches = JobMatch::where('cv_id', $cv->id)->with(['job.jobTitle.category.parent'])->orderByDesc('score')->orderByDesc('id')->get();

        return ApiResponse::success(JobMatchResource::collection($matches));
    }

    /**
     * DELETE /api/candidate/cvs/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $profile = CandidateProfile::where('user_id', $request->user()->id)->first();
        if (! $profile) {
            return ApiResponse::fromLegacy(['message' => 'Candidate profile not found.'], 404);
        }
        $cv = CandidateCv::where('candidate_id', $profile->id)->findOrFail($id);
        $cv->delete();

        return ApiResponse::fromLegacy(['message' => 'CV deleted successfully.'], 200);
    }
}
