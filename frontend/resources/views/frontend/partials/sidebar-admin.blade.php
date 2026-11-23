@include('frontend.partials.sidebar-nav', [
    'eyebrow' => __('navigation.admin.eyebrow'),
    'workspaceLabel' => __('navigation.admin.workspace'),
    'items' => [
        [
            'label' => __('navigation.admin.overview'),
            'section' => __('navigation.sections.main'),
            'icon' => 'grid',
            'route' => 'frontend.dashboard',
            'active' => request()->routeIs('frontend.dashboard'),
        ],
        [
            'label' => __('navigation.admin.organizations'),
            'section' => __('navigation.sections.manage'),
            'icon' => 'building',
            'route' => 'frontend.admin.companies.index',
            'active' => request()->routeIs('frontend.admin.companies.*'),
        ],
        [
            'label' => __('Job moderation preview'),
            'section' => __('navigation.sections.manage'),
            'icon' => 'shield',
            'route' => 'frontend.admin.jobs.index',
            'active' => request()->routeIs('frontend.admin.jobs.*'),
        ],
        [
            'label' => __('CV job title approvals'),
            'section' => __('navigation.sections.manage'),
            'icon' => 'shield',
            'route' => 'frontend.admin.cv-title-suggestions.index',
            'active' => request()->routeIs('frontend.admin.cv-title-suggestions.*'),
        ],
        [
            'label' => __('navigation.admin.users'),
            'section' => __('navigation.sections.manage'),
            'icon' => 'users',
            'route' => 'frontend.admin.users.index',
            'active' => request()->routeIs('frontend.admin.users.*'),
        ],
        [
            'label' => __('navigation.admin.roles'),
            'section' => __('navigation.sections.manage'),
            'icon' => 'shield',
            'route' => 'frontend.admin.roles.index',
            'active' => request()->routeIs('frontend.admin.roles.*'),
        ],
    ],
])
