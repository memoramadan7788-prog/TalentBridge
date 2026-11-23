@extends('frontend.layouts.app')

@section('title', __('My Interview Schedule'))

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-hb-navy">Interview Schedule & Guide</h1>
            <p class="text-sm text-hb-muted mt-1">Manage your upcoming interview sessions, check schedules, and prepare effectively.</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <button onclick="switchInterviewTab('upcoming')" id="btn-upcoming" class="px-4 py-2 text-sm font-semibold rounded-xl bg-tb-primary text-white shadow-sm transition">Upcoming Interviews</button>
            <button onclick="switchInterviewTab('past')" id="btn-past" class="px-4 py-2 text-sm font-semibold rounded-xl bg-hb-tint text-hb-muted hover:bg-hb-border transition">Past Interviews</button>
        </div>
    </div>

    <!-- Week Calendar Bar View -->
    <div class="bg-hb-surface p-4 rounded-2xl border border-hb-border mb-8">
        <h3 class="text-sm font-bold text-hb-navy mb-3">This Week's Overview</h3>
        <div class="grid grid-cols-7 gap-2 text-center">
            <div class="p-2 rounded-xl bg-hb-tint border border-hb-border"><span class="block text-xs text-hb-muted">Mon</span><span class="text-sm font-bold text-hb-text">28</span></div>
            <div class="p-2 rounded-xl bg-hb-tint border border-hb-border"><span class="block text-xs text-hb-muted">Tue</span><span class="text-sm font-bold text-hb-text">29</span></div>
            <div class="p-2 rounded-xl bg-tb-primary text-white rounded-xl shadow-sm"><span class="block text-xs text-white">Wed</span><span class="text-sm font-bold">30</span></div>
            <div class="p-2 rounded-xl bg-hb-tint border border-hb-border"><span class="block text-xs text-hb-muted">Thu</span><span class="text-sm font-bold text-hb-text">01</span></div>
            <div class="p-2 rounded-xl bg-hb-tint border border-hb-border"><span class="block text-xs text-hb-muted">Fri</span><span class="text-sm font-bold text-hb-text">02</span></div>
            <div class="p-2 rounded-xl bg-hb-tint border border-hb-border"><span class="block text-xs text-hb-muted">Sat</span><span class="text-sm font-bold text-hb-text">03</span></div>
            <div class="p-2 rounded-xl bg-hb-tint border border-hb-border"><span class="block text-xs text-hb-muted">Sun</span><span class="text-sm font-bold text-hb-text">04</span></div>
        </div>
    </div>

    <!-- TAB 1: Upcoming Interviews -->
    <div id="tab-upcoming" class="space-y-6">
        <div class="bg-hb-surface p-6 rounded-2xl border border-hb-border flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold px-2.5 py-1 bg-tb-primary-soft text-tb-primary-text rounded-full">Technical Round</span>
                    <span class="text-xs text-hb-muted">Wed, Oct 30 • 02:00 PM - 03:00 PM</span>
                </div>
                <h3 class="text-lg font-bold text-hb-navy">Senior Full Stack Developer - TechCorp</h3>
                <p class="text-sm text-hb-muted">Interviewer: Eng. Ahmed (Lead Architect)</p>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                <a href="#" class="px-4 py-2 text-sm font-semibold text-hb-text bg-hb-tint hover:bg-hb-border rounded-xl transition text-center">Reschedule</a>
                <a href="#" class="px-5 py-2 text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-600 rounded-xl shadow-md shadow-emerald-500/20 transition text-center">Join Meeting</a>
            </div>
        </div>

        <!-- Preparation Checklist & Guide Box -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <div class="bg-hb-tint p-6 rounded-2xl border border-hb-border">
                <h3 class="text-base font-bold text-hb-navy mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-tb-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Preparation Checklist
                </h3>
                <ul class="space-y-2 text-sm text-hb-muted">
                    <li class="flex items-center gap-2"><input type="checkbox" class="rounded text-tb-primary focus:ring-tb-primary"> Test microphone and camera setup</li>
                    <li class="flex items-center gap-2"><input type="checkbox" class="rounded text-tb-primary focus:ring-tb-primary"> Review resume and project details</li>
                    <li class="flex items-center gap-2"><input type="checkbox" class="rounded text-tb-primary focus:ring-tb-primary"> Prepare questions for the interviewer</li>
                </ul>
            </div>

            <div class="bg-tb-primary-soft/50 p-6 rounded-2xl border border-tb-primary/30">
                <h3 class="text-base font-bold text-hb-navy mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-tb-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Interview Guide & Tips
                </h3>
                <p class="text-sm text-hb-muted leading-relaxed">
                    Be concise, structured, and use the STAR method (Situation, Task, Action, Result) when answering behavioral questions. Ensure a quiet environment with stable internet connectivity.
                </p>
            </div>
        </div>
    </div>

    <!-- TAB 2: Past Interviews (Hidden by default) -->
    <div id="tab-past" class="space-y-6 hidden">
        <div class="bg-hb-surface p-6 rounded-2xl border border-hb-border flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-full">Completed - Passed</span>
                    <span class="text-xs text-hb-muted">Mon, Oct 15 • 10:00 AM</span>
                </div>
                <h3 class="text-lg font-bold text-hb-navy">HR Screening - CloudSystems</h3>
                <p class="text-sm text-hb-muted">Feedback: Excellent communication and culture fit.</p>
            </div>
            <div>
                <span class="text-sm font-semibold text-hb-muted">Result: Proceeded to Tech Round</span>
            </div>
        </div>
    </div>
</div>

<script>
    function switchInterviewTab(tab) {
        const upcomingTab = document.getElementById('tab-upcoming');
        const pastTab = document.getElementById('tab-past');
        const btnUpcoming = document.getElementById('btn-upcoming');
        const btnPast = document.getElementById('btn-past');

        if (tab === 'upcoming') {
            upcomingTab.classList.remove('hidden');
            pastTab.classList.add('hidden');
            btnUpcoming.className = "px-4 py-2 text-sm font-semibold rounded-xl bg-tb-primary text-white shadow-sm transition";
            btnPast.className = "px-4 py-2 text-sm font-semibold rounded-xl bg-hb-tint text-hb-muted hover:bg-hb-border transition";
        } else {
            pastTab.classList.remove('hidden');
            upcomingTab.classList.add('hidden');
            btnPast.className = "px-4 py-2 text-sm font-semibold rounded-xl bg-tb-primary text-white shadow-sm transition";
            btnUpcoming.className = "px-4 py-2 text-sm font-semibold rounded-xl bg-hb-tint text-hb-muted hover:bg-hb-border transition";
        }
    }
</script>
@endsection