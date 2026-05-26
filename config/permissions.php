<?php
// config/permissions.php
return [
    'settings' => [
        'manage settings',
    ],
    'modules' => [
        'manage modules',
    ],
    'roles' => [
        'manage roles',
    ],
    'users' => [
        'manage users',
    ],
    'database' => [
        'db.manage',
        'database.backup',
        'database.restore',
    ],
    'website' => [
        'website.backup',
        'website.update',
        'website.backup.zip',
    ],
];
