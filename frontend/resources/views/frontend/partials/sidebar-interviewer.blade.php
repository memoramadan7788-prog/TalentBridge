@include('frontend.partials.sidebar-nav', [
    'eyebrow' => __('navigation.interviewer.eyebrow'),
    'workspaceLabel' => __('navigation.interviewer.workspace'),
    'items' => [
        [
            'label' => __('navigation.interviewer.schedule'),
            'section' => __('navigation.sections.main'),
            'icon' => 'calendar',
            'route' => 'frontend.interviewer.interviews.index',
            'active' => request()->routeIs('frontend.interviewer.interviews.*'),
        ],
        [
            'label' => __('navigation.interviewer.availability'),
            'section' => __('navigation.sections.main'),
            'icon' => 'clock',
            'route' => 'frontend.interviewer.availability.add',
            'active' => request()->routeIs('frontend.interviewer.availability.*'),
        ],
        [
            'label' => __('navigation.company.overview'),
            'section' => __('navigation.sections.manage'),
            'icon' => 'grid',
            'route' => 'frontend.company.dashboard',
            'active' => request()->routeIs('frontend.company.dashboard'),
        ],
    ],
])
