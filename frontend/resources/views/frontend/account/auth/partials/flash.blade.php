@if (session('status'))
  <div class="mb-5 rounded-xl border border-tb-primary/30 bg-tb-primary-soft px-4 py-3 text-sm text-tb-primary-text" role="status">{{ session('status') }}</div>
@endif

@if (session('success_key'))
  <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200" role="status">{{ __(session('success_key'), session('success_replace', [])) }}</div>
@elseif (session('success'))
  <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200" role="status">{{ session('success') }}</div>
@endif

@if ($errors->has('error'))
  <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300" role="alert">{{ $errors->first('error') }}</div>
@endif
