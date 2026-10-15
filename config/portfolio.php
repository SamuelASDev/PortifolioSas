<?php

return [
    'sharing' => ['disk' => 'public', 'max_size_kb' => 10240, 'max_dimension' => 6000],
    'carousel' => ['max_projects' => 10],
    'attachments' => [
        'disk' => 'public', 'max_size_kb' => 10240, 'max_per_project' => 20,
        'max_portfolio_bytes' => 104857600, 'max_portfolio_files' => 100, 'pending_hours' => 24,
    ],
    'photos' => [
        'disk' => 'public',
        'max_size_kb' => 10240,
        'max_dimension' => 3000,
    ],

    'covers' => [
        'disk' => 'public',
        'max_size_kb' => 10240,
        'max_dimension' => 6000,
    ],

    'reserved_slugs' => [
        'admin', 'login', 'logout', 'register', 'dashboard', 'profile', 'up',
        'forgot-password', 'reset-password', 'confirm-password', 'verify-email',
        'email', 'password', 'api', 'storage', 'build', 'assets',
    ],
];
