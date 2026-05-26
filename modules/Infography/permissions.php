<?php

// \permissions.php

return [
    'roles' => [
        'SuperAdmin' => [
            'upload', 'edit', 'delete', 'replace', 'view', 'download'
        ],
        'Admin' => [
            'upload', 'edit', 'delete', 'replace', 'view', 'download'
        ],
        'HR' => [
            'upload', 'edit', 'delete', 'view', 'download'
        ],
        'Registrar' => [
            'view', 'download'
        ],
        'Employee' => [
            'view', 'download'
        ],
    ],


];
