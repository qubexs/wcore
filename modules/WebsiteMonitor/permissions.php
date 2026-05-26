<?php

return [
    'roles' => [
        'SuperAdmin' => [
            'websitemonitor' => ['view', 'create', 'edit', 'delete', 'check', 'manage_settings'],
        ],
        'Admin' => [
            'websitemonitor' => ['view', 'create', 'edit', 'delete', 'check', 'manage_settings'],
        ],
        'HR' => [
            'websitemonitor' => ['view', 'check'],
        ],
        'Registrar' => [
            'websitemonitor' => ['view'],
        ],
        'Employee' => [
            'websitemonitor' => ['view'],
        ],
    ],
];
