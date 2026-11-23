<?php

namespace App\Frontend\Lookup;

use App\Models\JobAPI;
use App\Models\LookupAPI;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LookupController extends Controller
{
    public function __construct(
        private LookupAPI $api,
        private JobAPI $jobs,
    ) {}

    public function getCountries(Request $request)
    {
        $response = $this->api->getCountries();

        if ($response['statusCode'] === 200) {
            // Countries retrieval successful, you can perform additional actions here if needed
            return response()->json(['countries' => $response['data']], 200);
        } else {
            // Countries retrieval failed, return the error message
            return response()->json(['error' => $response['message']], $response['statusCode']);
        }
    }

    public function getCategories(Request $request)
    {
        $response = $this->api->getCategories();

        if ($response['statusCode'] === 200) {
            // Categories retrieval successful, you can perform additional actions here if needed
            return response()->json(['categories' => $response['data']], 200);
        } else {
            // Categories retrieval failed, return the error message
            return response()->json(['error' => $response['message']], $response['statusCode']);
        }
    }

    public function getJobFilterOptions(Request $request)
    {
        $filters = $request->only([
            'q', 'title', 'company_name', 'workplace', 'work_mode', 'country_id', 'country',
            'city_id', 'city', 'area_id', 'area', 'location', 'career_level', 'experience_years',
            'min_experience', 'max_experience', 'experience_range', 'category_id', 'category',
            'discipline', 'job_type', 'employment_type', 'company_id', 'skills', 'status_id',
            'visibility_id', 'salary_min', 'salary_max', 'published_from', 'published_to',
        ]);
        $response = $this->jobs->getFilterOptions($filters);

        if (($response['statusCode'] ?? 500) === 200 && is_array($response['data'] ?? null)) {
            return response()->json(['filters' => $response['data']], 200);
        }

        return response()->json(['error' => $response['message'] ?? __('Could not load job filters.')], $response['statusCode'] ?? 500);
    }

    public function getSkills(Request $request)
    {
        $keyword = (string) $request->query('q', '');

        $response = $this->api->getSkills($keyword);

        if ($response['statusCode'] === 200) {
            // Skills retrieval successful, you can perform additional actions here if needed
            return response()->json(['skills' => $response['data']], 200);
        } else {
            // Skills retrieval failed, return the error message
            return response()->json(['error' => $response['message']], $response['statusCode']);
        }
    }
}
