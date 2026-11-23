<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\StoreCoursesRequest;
use App\Http\Resources\CandidateCoursesResource;
use App\Models\CandidateCourse;
use App\Models\CandidateProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CandidateCoursesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        
        $courses = CandidateCourse::where('candidate_id', $profile->id)
            ->latest()
            ->get();

        return CandidateCoursesResource::collection($courses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoursesRequest $request): JsonResponse
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        $data = $request->validated();
        $data['candidate_id'] = $profile->id;

        $course = CandidateCourse::create($data);

        return response()->json([
            'message' => 'Course added successfully.',
            'courses' => new CandidateCoursesResource($course),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id): CandidateCoursesResource
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        
        $course = CandidateCourse::where('candidate_id', $profile->id)->findOrFail($id);

        return new CandidateCoursesResource($course);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCoursesRequest $request, int $id): JsonResponse
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        
        $course = CandidateCourse::where('candidate_id', $profile->id)->findOrFail($id);

        $data = $request->validated();
        
        $course->update($data);

        return response()->json([
            'message' => 'Course updated successfully.',
            'courses' => new CandidateCoursesResource($course),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $profile = CandidateProfile::firstOrCreate(['user_id' => $request->user()->id]);
        
        $course = CandidateCourse::where('candidate_id', $profile->id)->findOrFail($id);

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully.',
        ], 200);
    }
}
