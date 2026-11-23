<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\CvJobTitleSuggestion;
use App\Models\JobTitle;
use App\Models\MatchedJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CvJobTitleSuggestionController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = CvJobTitleSuggestion::query()->with('scan.cv.candidate.user')->where('status', 'pending')->orderBy('created_at')->paginate(30);
        $rows->getCollection()->transform(function (CvJobTitleSuggestion $row) {
            return ['id' => $row->id, 'title' => $row->title, 'confidence' => $row->confidence, 'created_at' => $row->created_at, 'cv_id' => $row->scan?->cv_id, 'filename' => $row->scan?->cv?->original_filename, 'candidate' => $row->scan?->cv?->candidate?->user?->name];
        });
        return ApiResponse::success($rows);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $input = $request->validate(['decision' => ['required', 'in:approve,reject']]);
        $suggestion = DB::transaction(function () use ($id, $input, $request) {
            $suggestion = CvJobTitleSuggestion::query()->lockForUpdate()->findOrFail($id);
            if ($suggestion->status !== 'pending') {
                abort(409, 'This suggestion has already been reviewed.');
            }
            $title = null;
            if ($input['decision'] === 'approve') {
                $title = JobTitle::query()->whereRaw('LOWER(name) = ?', [$suggestion->normalized_title])->first();
                if (! $title) {
                    $title = JobTitle::create(['name' => $suggestion->title]);
                }
                MatchedJob::updateOrCreate(
                    ['cv_id' => $suggestion->scan->cv_id, 'job_title_id' => $title->id],
                    ['confidence' => $suggestion->confidence, 'sort_order' => $suggestion->sort_order]
                );
            }
            $suggestion->update([
                'status' => $input['decision'] === 'approve' ? 'approved' : 'rejected',
                'job_title_id' => $title?->id,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
            return $suggestion;
        });

        return ApiResponse::success(['id' => $suggestion->id, 'status' => $suggestion->fresh()->status]);
    }
}
