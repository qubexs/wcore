<?php

// permission.php

return [
    'roles' => [
        'superadmin' => [
            'upload', 'edit', 'delete', 'replace', 'view', 'download'
        ],
        'admin' => [
            'upload', 'edit', 'delete', 'replace', 'view', 'download'
        ],
        'hr' => [
            'upload', 'edit', 'delete', 'view', 'download'
        ],
        'registrar' => [
            'view', 'download'
        ],
        'employee' => [
            'view', 'download'
        ],
    ],

    'users' => [
        'superadmin@htpn' => 'superadmin',
        'admin@htpn'      => 'admin',
        'hr@htpn'         => 'hr',
        'registrar@htpn'  => 'registrar',
        'employee@htpn'   => 'employee',
    ],
];
