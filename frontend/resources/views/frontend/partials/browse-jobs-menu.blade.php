<style>
    .tb-browse-jobs { position: relative; }
    .tb-browse-jobs__trigger {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 0;
        border: 0;
        background: transparent;
        color: var(--hb-text);
        cursor: pointer;
        font: inherit;
        font-size: .875rem;
        font-weight: 500;
        transition: color .16s ease;
    }
    .tb-browse-jobs__trigger:hover,
    .tb-browse-jobs__trigger[aria-expanded="true"] { color: var(--hb-blue); }
    .tb-browse-jobs__chevron {
        width: 7px;
        height: 7px;
        border-right: 1.5px solid currentColor;
        border-bottom: 1.5px solid currentColor;
        transform: rotate(45deg) translateY(-2px);
        transition: transform .18s ease;
    }
    .tb-browse-jobs__trigger[aria-expanded="true"] .tb-browse-jobs__chevron {
        transform: rotate(225deg) translate(-1px, -1px);
    }
    .tb-browse-jobs__panel {
        position: fixed;
        z-index: 60;
        top: 84px;
        left: 50%;
        width: min(1120px, calc(100vw - 40px));
        max-height: calc(100vh - 104px);
        overflow: auto;
        padding: 25px 28px 27px;
        border: 1px solid var(--hb-border);
        border-radius: 10px;
        background: var(--hb-surface);
        box-shadow: 0 18px 48px rgb(15 27 51 / .2);
        color: var(--hb-text);
        transform: translateX(-50%);
        animation: tb-browse-enter .16s ease-out;
    }
    .tb-browse-jobs__panel.hidden { display: none; }
    .tb-browse-jobs__section + .tb-browse-jobs__section { margin-top: 23px; }
    .tb-browse-jobs__heading {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin: 0 0 13px;
        color: var(--hb-navy);
        font-size: 15px;
        font-weight: 700;
        letter-spacing: -.1px;
    }
    .tb-browse-jobs__all {
        color: var(--hb-blue);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }
    .tb-browse-jobs__all:hover { text-decoration: underline; }
    .tb-browse-jobs__levels {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .tb-browse-jobs__pill {
        display: inline-flex;
        min-height: 34px;
        align-items: center;
        padding: 7px 12px;
        border: 1px solid var(--hb-border);
        border-radius: 5px;
        background: var(--hb-surface);
        color: var(--hb-text);
        font-size: 12px;
        font-weight: 550;
        line-height: 1.35;
        text-decoration: none;
        transition: border-color .16s ease, background .16s ease, color .16s ease, transform .16s ease;
    }
    .tb-browse-jobs__pill:hover {
        transform: translateY(-1px);
        border-color: var(--hb-blue);
        background: var(--hb-info-soft);
        color: var(--hb-blue);
    }
    .tb-browse-jobs__quick {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 14px;
        border: 1px solid var(--hb-border);
        border-radius: 6px;
        background: var(--color-primary-soft);
        color: var(--hb-navy);
        text-decoration: none;
        transition: border-color .16s ease, background .16s ease;
    }
    .tb-browse-jobs__quick:hover { border-color: var(--color-primary); background: var(--color-primary-soft); }
    .tb-browse-jobs__quick-label { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 650; }
    .tb-browse-jobs__new {
        padding: 3px 7px;
        border-radius: 3px;
        background: var(--color-primary-soft);
        color: var(--color-primary-text);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
    }
    .tb-browse-jobs__quick-arrow { color: var(--hb-blue); font-size: 17px; line-height: 1; }
    .tb-browse-jobs__categories {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px 22px;
    }
    .tb-browse-jobs__category-column {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        min-width: 0;
    }
    .tb-browse-jobs__category {
        display: block;
        width: 100%;
        padding: 6px 7px;
        border-radius: 4px;
        color: var(--hb-text);
        font-size: 12px;
        line-height: 1.4;
        text-decoration: none;
        transition: background .14s ease, color .14s ease, padding .14s ease;
    }
    .tb-browse-jobs__category:hover {
        padding-inline-start: 10px;
        background: var(--hb-info-soft);
        color: var(--hb-blue);
    }
    @keyframes tb-browse-enter {
        from { opacity: 0; translate: 0 -5px; }
        to { opacity: 1; translate: 0 0; }
    }
    @media (max-width: 900px) {
        .tb-browse-jobs__panel { padding: 21px; }
        .tb-browse-jobs__categories { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 16px; }
    }
    @media (max-width: 600px) {
        .tb-browse-jobs__panel {
            top: 68px;
            width: calc(100vw - 20px);
            max-height: calc(100vh - 80px);
            padding: 18px 15px;
            border-radius: 8px;
        }
        .tb-browse-jobs__heading { font-size: 14px; }
        .tb-browse-jobs__categories { grid-template-columns: 1fr; gap: 0; }
        .tb-browse-jobs__category { padding-block: 7px; }
        .tb-browse-jobs__pill { min-height: 32px; padding: 6px 9px; font-size: 11px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .tb-browse-jobs__panel, .tb-browse-jobs__trigger, .tb-browse-jobs__pill, .tb-browse-jobs__category {
            animation: none;
            transition: none;
        }
    }
</style>

<div class="tb-browse-jobs">
    <button
        type="button"
        class="tb-browse-jobs__trigger"
        aria-expanded="false"
        aria-controls="browse-jobs-menu"
        aria-haspopup="true"
        data-dropdown-trigger
    >
        <span>{{ __('Browse Jobs') }}</span>
        <span class="tb-browse-jobs__chevron" aria-hidden="true"></span>
    </button>

    <div
        id="browse-jobs-menu"
        class="tb-browse-jobs__panel hidden"
        role="region"
        aria-label="{{ __('Browse jobs by career level and category') }}"
        data-dropdown-panel
        data-filter-options-url="{{ route('frontend.lookup.job-filters') }}"
        data-jobs-url="{{ route('frontend.jobs.index') }}"
    >
        <section class="tb-browse-jobs__section" aria-labelledby="browse-jobs-career-level">
            <h2 class="tb-browse-jobs__heading" id="browse-jobs-career-level">
                <span>{{ __('Jobs by Career Level') }}</span>
                <a class="tb-browse-jobs__all" href="{{ route('frontend.jobs.index') }}">{{ __('View all jobs') }}</a>
            </h2>
            <div class="tb-browse-jobs__levels" data-career-level-options>
                <p class="text-sm text-hb-muted">{{ __('Loading…') }}</p>
            </div>
        </section>

        <section class="tb-browse-jobs__section" aria-labelledby="browse-jobs-categories">
            <h2 class="tb-browse-jobs__heading" id="browse-jobs-categories">{{ __('Jobs by Category') }}</h2>
            <div class="tb-browse-jobs__categories" data-job-category-options>
                <p class="text-sm text-hb-muted">{{ __('Loading…') }}</p>
            </div>
        </section>
    </div>
</div>

<script>
(() => {
    const panel = document.querySelector('[data-filter-options-url]');
    const trigger = document.querySelector('[aria-controls="browse-jobs-menu"]');
    if (!panel || !trigger) return;

    const careers = panel.querySelector('[data-career-level-options]');
    const categories = panel.querySelector('[data-job-category-options]');
    let loaded = false;

    function makeLink(label, value, parameter, className) {
        const link = document.createElement('a');
        const url = new URL(panel.dataset.jobsUrl, window.location.origin);
        url.searchParams.append(parameter, value);
        link.href = url.toString();
        link.className = className;
        link.textContent = label;
        return link;
    }

    function render(options) {
        const careerOptions = Array.isArray(options.career_levels) ? options.career_levels : [];
        const categoryOptions = Array.isArray(options.categories) ? options.categories : [];
        careers.replaceChildren(...(careerOptions.length ? careerOptions.map((option) =>
            makeLink(option.label, option.value, 'career_level[]', 'tb-browse-jobs__pill')
        ) : [document.createTextNode(@json(__('No career levels available.')))]));

        if (!categoryOptions.length) {
            categories.textContent = @json(__('No job categories available.'));
            return;
        }

        const columns = Array.from({ length: Math.min(4, categoryOptions.length) }, () => {
            const column = document.createElement('div');
            column.className = 'tb-browse-jobs__category-column';
            return column;
        });
        categoryOptions.forEach((option, index) => {
            columns[index % columns.length].append(
                makeLink(option.name, option.id, 'category_id', 'tb-browse-jobs__category')
            );
        });
        categories.replaceChildren(...columns);
    }

    trigger.addEventListener('click', async () => {
        if (loaded) return;
        loaded = true;
        try {
            const response = await fetch(panel.dataset.filterOptionsUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('Filter options unavailable');
            const payload = await response.json();
            render(payload.filters || {});
        } catch {
            const message = @json(__('Job filters could not be loaded.'));
            careers.textContent = message;
            categories.textContent = message;
        }
    });
})();
</script>
