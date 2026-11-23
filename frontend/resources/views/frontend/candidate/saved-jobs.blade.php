@extends('frontend.layouts.app')

@section('title', __('Saved Jobs & Alerts'))

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-hb-navy">Saved Jobs & Search Alerts</h1>
            <p class="text-sm text-hb-muted mt-1">Manage your bookmarked opportunities and automated search preferences.</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <button onclick="switchTab('jobs')" id="btn-jobs" class="px-4 py-2 text-sm font-semibold rounded-xl bg-tb-primary text-white shadow-sm transition">Saved Jobs (3)</button>
            <button onclick="switchTab('alerts')" id="btn-alerts" class="px-4 py-2 text-sm font-semibold rounded-xl bg-hb-tint text-hb-muted hover:bg-hb-border transition">Search Alerts (2)</button>
        </div>
    </div>

    <!-- TAB 1: Saved Jobs Section -->
    <div id="tab-jobs" class="space-y-6">
        <!-- Filters Bar -->
        <div class="bg-hb-surface p-4 rounded-2xl border border-hb-border flex flex-col sm:flex-row gap-4 justify-between items-center">
            <input type="text" id="job-search-filter" placeholder="Search saved jobs..." class="w-full sm:w-80 rounded-xl border-hb-border border px-3.5 py-2 text-sm focus:ring-tb-primary focus:border-tb-primary">
            <select id="job-status-filter" class="w-full sm:w-auto rounded-xl border-hb-border border px-3.5 py-2 text-sm focus:ring-tb-primary focus:border-tb-primary">
                <option value="all">All Statuses</option>
                <option value="active">Active Openings</option>
                <option value="closed">Closed / Expired</option>
            </select>
        </div>

        <!-- Saved Jobs List -->
        <div class="grid grid-cols-1 gap-4" id="saved-jobs-list">
            <!-- Job Card 1 -->
            <div class="bg-hb-surface p-6 rounded-2xl border border-hb-border flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition hover:shadow-md">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-full">Active Opening</span>
                        <span class="text-xs text-hb-muted">Saved 2 days ago</span>
                    </div>
                    <h3 class="text-lg font-bold text-hb-navy">Senior Full Stack Developer</h3>
                    <p class="text-sm text-hb-muted">TechCorp Solutions • Cairo, Egypt (Hybrid)</p>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    <button onclick="removeSavedJob(1)" class="px-4 py-2 text-sm font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition">Remove</button>
                    <a href="#" class="px-5 py-2 text-sm font-bold text-white bg-tb-primary hover:bg-tb-primary-solid rounded-xl shadow-md shadow-tb-primary/20 transition text-center">Apply Now</a>
                </div>
            </div>

            <!-- Job Card 2 -->
            <div class="bg-hb-surface p-6 rounded-2xl border border-hb-border flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition hover:shadow-md">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full">Update: Requirements Changed</span>
                        <span class="text-xs text-hb-muted">Saved 1 week ago</span>
                    </div>
                    <h3 class="text-lg font-bold text-hb-navy">DevOps & Cloud Engineer</h3>
                    <p class="text-sm text-hb-muted">CloudSystems Inc • New Capital, Egypt (Remote)</p>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    <button onclick="removeSavedJob(2)" class="px-4 py-2 text-sm font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition">Remove</button>
                    <a href="#" class="px-5 py-2 text-sm font-bold text-white bg-tb-primary hover:bg-tb-primary-solid rounded-xl shadow-md shadow-tb-primary/20 transition text-center">View Update</a>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Search Alerts Section (Hidden by default) -->
    <div id="tab-alerts" class="space-y-6 hidden">
        <div class="bg-hb-surface p-6 rounded-2xl border border-hb-border flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold px-2.5 py-1 bg-tb-primary-soft text-tb-primary-text rounded-full">Alert Active</span>
                    <span class="text-xs text-hb-muted">Frequency: Daily</span>
                </div>
                <h3 class="text-lg font-bold text-hb-navy">Backend Developer in Cairo</h3>
                <p class="text-sm text-hb-muted">Keywords: Node.js, Laravel, PostgreSQL • Matching 12 new jobs</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="toggleAlertStatus(this)" class="px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl transition">Pause</button>
                <button onclick="editAlert(1)" class="px-4 py-2 text-sm font-semibold text-hb-text bg-hb-tint hover:bg-hb-border rounded-xl transition">Edit</button>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        const jobsTab = document.getElementById('tab-jobs');
        const alertsTab = document.getElementById('tab-alerts');
        const btnJobs = document.getElementById('btn-jobs');
        const btnAlerts = document.getElementById('btn-alerts');

        if (tab === 'jobs') {
            jobsTab.classList.remove('hidden');
            alertsTab.classList.add('hidden');
            btnJobs.className = "px-4 py-2 text-sm font-semibold rounded-xl bg-tb-primary text-white shadow-sm transition";
            btnAlerts.className = "px-4 py-2 text-sm font-semibold rounded-xl bg-hb-tint text-hb-muted hover:bg-hb-border transition";
        } else {
            alertsTab.classList.remove('hidden');
            jobsTab.classList.add('hidden');
            btnAlerts.className = "px-4 py-2 text-sm font-semibold rounded-xl bg-tb-primary text-white shadow-sm transition";
            btnJobs.className = "px-4 py-2 text-sm font-semibold rounded-xl bg-hb-tint text-hb-muted hover:bg-hb-border transition";
        }
    }

    function toggleAlertStatus(button) {
        if (button.textContent.trim() === 'Pause') {
            button.textContent = 'Resume';
            button.className = "px-4 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition";
        } else {
            button.textContent = 'Pause';
            button.className = "px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl transition";
        }
    }

    function removeSavedJob(id) {
        if(confirm('Are you sure you want to remove this job from saved list?')) {
            // API integration call simulation
            fetch(`/api/candidate/saved-jobs/${id}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } })
                .then(() => alert('Job removed successfully'))
                .catch(() => alert('Action completed'));
        }
    }
</script>
@endsection