<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Info
    |--------------------------------------------------------------------------
    */
    'name'    => 'Intranet File Hosting Module',
    'slug'    => 'filehosting',
    'version' => '1.1.0',

    /*
    |--------------------------------------------------------------------------
    | Register the Provider
    |--------------------------------------------------------------------------
    | In a typical Laravel module, providers are listed in the module service
    | provider or in config/app.php. This entry can be used if you need to
    | dynamically merge providers, but it's often omitted.
    */
    // 'providers' => ServiceProvider::defaultProviders()->merge([
    //     \Modules\FileHosting\Providers\FileHostingServiceProvider::class,
    // ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    | Disk to use for file and thumbnail storage.
    | Set to 's3', 'public', etc. as needed.
    */
    'disk'             => env('FILEHOSTING_DISK', 'local'),
    'thumbnail_disk'   => env('FILEHOSTING_THUMB_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Upload Limits
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'max_folder_depth'        => 10,
        'max_files_per_folder'    => 1000,
        'max_folder_name_length'  => 255,
        'max_upload_size_mb'      => 100,
        'forbidden_names'         => ['.', '..', 'CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'LPT1'],
        'reserved_paths'          => ['system', 'trash', 'temp'],
        'allowed_mime_types'      => [], // Empty = allow all. e.g. ['image/jpeg','application/pdf']
    ],

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Settings
    |--------------------------------------------------------------------------
    */
    'thumbnails' => [
        'enabled' => true,
        'driver'  => env('FILEHOSTING_THUMB_DRIVER', 'auto'), // auto | gd | imagick
        'quality' => 85,
        'sizes'   => [
            'small'  => ['width' => 80,  'height' => 80],
            'medium' => ['width' => 300, 'height' => 300],
            'large'  => ['width' => 800, 'height' => 800],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Versioning
    |--------------------------------------------------------------------------
    */
    'versioning' => [
        'enabled'      => true,
        'max_versions' => 10, // 0 = unlimited
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled'     => true,
        'retain_days' => 90, // 0 = keep forever
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Permissions
    |--------------------------------------------------------------------------
    | Mirrors permissions.php but used by the ServiceProvider Gate.
    */
    'roles' => [
        'SuperAdmin' => [
            'folder' => ['create', 'rename', 'move', 'delete', 'view_all', 'manage_permissions'],
            'file'   => ['upload', 'edit', 'delete', 'replace', 'view', 'download', 'move', 'share', 'version_manage'],
            'system' => ['view_logs', 'manage_settings', 'backup_restore'],
        ],
        'Admin' => [
            'folder' => ['create', 'rename', 'move', 'delete', 'view_all', 'manage_permissions'],
            'file'   => ['upload', 'edit', 'delete', 'replace', 'view', 'download', 'move', 'share', 'version_manage'],
            'system' => ['view_logs'],
        ],
        'HR' => [
            'folder' => ['create', 'rename', 'move', 'delete_own', 'view_department'],
            'file'   => ['upload', 'edit_own', 'delete_own', 'view', 'download', 'move_own', 'share'],
            'system' => [],
        ],
        'Registrar' => [
            'folder' => ['view_assigned', 'create_in_assigned'],
            'file'   => ['view', 'download', 'upload_in_assigned'],
            'system' => [],
        ],
        'Employee' => [
            'folder' => ['view_assigned'],
            'file'   => ['view', 'download'],
            'system' => [],
        ],
    ],

];