@extends('frontend.layouts.app')

@section('title', __('Company Onboarding Setup'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <!-- Header & Steps Indicator -->
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-hb-navy">Setup Your Company Workspace</h1>
        <p class="text-sm text-hb-muted mt-1">Complete the steps below to configure your organization workspace and hiring pipeline.</p>
        
        <!-- Progress Steps Bar -->
        <div class="flex items-center justify-center gap-3 mt-6">
            <div id="step-indicator-1" class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-tb-primary text-white transition">
                <span class="w-5 h-5 flex items-center justify-center rounded-full bg-hb-surface text-tb-primary text-[10px]">1</span>
                Identity
            </div>
            <div class="w-8 h-0.5 bg-hb-border"></div>
            <div id="step-indicator-2" class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-hb-tint text-hb-muted transition">
                <span class="w-5 h-5 flex items-center justify-center rounded-full bg-hb-border text-hb-muted text-[10px]">2</span>
                Workspace Details
            </div>
            <div class="w-8 h-0.5 bg-hb-border"></div>
            <div id="step-indicator-3" class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-hb-tint text-hb-muted transition">
                <span class="w-5 h-5 flex items-center justify-center rounded-full bg-hb-border text-hb-muted text-[10px]">3</span>
                Hiring Model & Preview
            </div>
        </div>
    </div>

    <!-- Wizard Form Container -->
    <div class="bg-hb-surface p-8 rounded-2xl border border-hb-border shadow-sm">
        <form id="onboardingForm" method="POST" action="#">
            @csrf

            <!-- STEP 1: Company Identity -->
            <div id="wizard-step-1" class="space-y-6">
                <h3 class="text-lg font-bold text-hb-navy border-b pb-3">Company Identity</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-hb-text mb-2">Company Legal Name</label>
                        <input type="text" name="company_name" placeholder="Enter your company name" class="w-full px-4 py-2.5 rounded-xl border border-hb-border focus:ring-2 focus:ring-tb-primary focus:outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-hb-text mb-2">Industry</label>
                        <select name="industry" class="w-full px-4 py-2.5 rounded-xl border border-hb-border focus:ring-2 focus:ring-tb-primary focus:outline-none text-sm bg-hb-surface">
                            <option value="">Select Industry</option>
                            <option value="tech">Information Technology & Software</option>
                            <option value="finance">Fintech & Banking</option>
                            <option value="ecommerce">E-Commerce & Retail</option>
                            <option value="healthcare">Healthcare & Pharma</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-hb-text mb-2">Company Logo Upload</label>
                    <div class="border-2 border-dashed border-hb-border rounded-xl p-6 text-center hover:border-tb-primary transition cursor-pointer">
                        <svg class="w-8 h-8 text-hb-muted mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-xs text-hb-muted">Drag & drop your company logo here, or <span class="text-tb-primary font-semibold">browse files</span></p>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" onclick="nextStep(2)" class="px-6 py-2.5 bg-tb-primary hover:bg-tb-primary-solid text-white font-semibold text-sm rounded-xl shadow-md transition">Next: Workspace Details</button>
                </div>
            </div>

            <!-- STEP 2: Workspace Details -->
            <div id="wizard-step-2" class="space-y-6 hidden">
                <h3 class="text-lg font-bold text-hb-navy border-b pb-3">Workspace Details & Subdomain</h3>
                
                <div>
                    <label class="block text-sm font-semibold text-hb-text mb-2">Workspace URL</label>
                    <div class="flex rounded-xl shadow-sm">
                        <input type="text" id="workspace_url" name="workspace_url" placeholder="your-company" class="flex-1 px-4 py-2.5 rounded-l-xl border border-r-0 border-hb-border focus:ring-2 focus:ring-tb-primary focus:outline-none text-sm">
                        <span class="inline-flex items-center px-4 rounded-r-xl border border-hb-border bg-hb-tint text-hb-muted text-sm font-medium">.platform.com</span>
                    </div>
                    <p class="text-xs text-hb-muted mt-1">This will be your dedicated recruitment portal address.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-hb-text mb-2">Company Size</label>
                        <select name="company_size" class="w-full px-4 py-2.5 rounded-xl border border-hb-border focus:ring-2 focus:ring-tb-primary focus:outline-none text-sm bg-hb-surface">
                            <option value="1-10">1 - 10 employees</option>
                            <option value="11-50">11 - 50 employees</option>
                            <option value="51-200">51 - 200 employees</option>
                            <option value="200+">200+ employees</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-hb-text mb-2">Timezone</label>
                        <select name="timezone" class="w-full px-4 py-2.5 rounded-xl border border-hb-border focus:ring-2 focus:ring-tb-primary focus:outline-none text-sm bg-hb-surface">
                            <option value="Africa/Cairo">(GMT+2:00) Cairo, Egypt</option>
                            <option value="Asia/Dubai">(GMT+4:00) Dubai, UAE</option>
                            <option value="Europe/London">(GMT+0:00) London, UK</option>
                            <option value="America/New_York">(GMT-5:00) New York, USA</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <button type="button" onclick="prevStep(1)" class="px-6 py-2.5 bg-hb-tint hover:bg-hb-border text-hb-text font-semibold text-sm rounded-xl transition">Back</button>
                    <button type="button" onclick="nextStep(3)" class="px-6 py-2.5 bg-tb-primary hover:bg-tb-primary-solid text-white font-semibold text-sm rounded-xl shadow-md transition">Next: Hiring Model</button>
                </div>
            </div>

            <!-- STEP 3: Hiring Model & Preview -->
            <div id="wizard-step-3" class="space-y-6 hidden">
                <h3 class="text-lg font-bold text-hb-navy border-b pb-3">Hiring Model & Workspace Preview</h3>
                
                <div>
                    <label class="block text-sm font-semibold text-hb-text mb-3">Primary Hiring Focus</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="border border-hb-border p-4 rounded-xl cursor-pointer hover:border-tb-primary transition flex flex-col justify-between">
                            <div>
                                <input type="radio" name="hiring_model" value="tech" class="text-tb-primary focus:ring-tb-primary mb-2" checked>
                                <h4 class="font-bold text-hb-navy text-sm">Tech & Engineering</h4>
                                <p class="text-xs text-hb-muted mt-1">Software development, DevOps, IT infrastructure.</p>
                            </div>
                        </label>
                        <label class="border border-hb-border p-4 rounded-xl cursor-pointer hover:border-tb-primary transition flex flex-col justify-between">
                            <div>
                                <input type="radio" name="hiring_model" value="corporate" class="text-tb-primary focus:ring-tb-primary mb-2">
                                <h4 class="font-bold text-hb-navy text-sm">Corporate & Sales</h4>
                                <p class="text-xs text-hb-muted mt-1">Business development, marketing, HR, finance.</p>
                            </div>
                        </label>
                        <label class="border border-hb-border p-4 rounded-xl cursor-pointer hover:border-tb-primary transition flex flex-col justify-between">
                            <div>
                                <input type="radio" name="hiring_model" value="general" class="text-tb-primary focus:ring-tb-primary mb-2">
                                <h4 class="font-bold text-hb-navy text-sm">General & Mixed</h4>
                                <p class="text-xs text-hb-muted mt-1">Multi-department hiring pipeline.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <button type="button" onclick="prevStep(2)" class="px-6 py-2.5 bg-hb-tint hover:bg-hb-border text-hb-text font-semibold text-sm rounded-xl transition">Back</button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm rounded-xl shadow-md transition">Complete Setup</button>
                </div>
            </div>
        </form>
