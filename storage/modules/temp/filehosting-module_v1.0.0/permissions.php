<?php
// permissions.php

return [
    // ============================================================
    // GLOBAL ROLE PERMISSIONS (Flattened format)
    // ============================================================
    'roles' => [
        'SuperAdmin' => [
            'folder.create',
            'folder.rename',
            'folder.move',
            'folder.delete',
            'folder.view_all',
            'folder.manage_permissions',
            'file.upload',
            'file.edit',
            'file.delete',
            'file.replace',
            'file.view',
            'file.download',
            'file.move',
            'file.share',
            'file.version_manage',
            'system.view_logs',
            'system.manage_settings',
            'system.backup_restore'
        ],
        'Admin' => [
            'folder.create',
            'folder.rename',
            'folder.move',
            'folder.delete',
            'folder.view_all',
            'folder.manage_permissions',
            'file.upload',
            'file.edit',
            'file.delete',
            'file.replace',
            'file.view',
            'file.download',
            'file.move',
            'file.share',
            'file.version_manage',
            'system.view_logs'
        ],
        'HR' => [
            'folder.create',
            'folder.rename',
            'folder.move',
            'folder.delete_own',
            'folder.view_department',
            'file.upload',
            'file.edit_own',
            'file.delete_own',
            'file.view',
            'file.download',
            'file.move_own',
            'file.share'
        ],
        'Registrar' => [
            'folder.view_assigned',
            'folder.create_in_assigned',
            'file.view',
            'file.download',
            'file.upload_in_assigned'
        ],
        'Employee' => [
            'folder.view_assigned',
            'file.view',
            'file.download'
        ],
    ],

    // ============================================================
    // PERMISSION DEFINITIONS (Documentation - unchanged)
    // ============================================================
    'definitions' => [
        'folder' => [
            'create' => 'Create new folders',
            'rename' => 'Rename any folder',
            'move' => 'Move folders to different locations',
            'delete' => 'Delete any folder',
            'delete_own' => 'Delete only own folders',
            'view_all' => 'View all folders regardless of visibility',
            'view_department' => 'View department folders',
            'view_assigned' => 'View folders explicitly shared with user',
            'manage_permissions' => 'Manage folder sharing permissions',
            'create_in_assigned' => 'Create subfolders in assigned folders',
        ],
        'file' => [
            'upload' => 'Upload files to any folder',
            'edit' => 'Edit any file metadata',
            'edit_own' => 'Edit only own files',
            'delete' => 'Delete any file',
            'delete_own' => 'Delete only own files',
            'replace' => 'Replace file content (create new version)',
            'view' => 'View file details and preview',
            'download' => 'Download files',
            'move' => 'Move files between folders',
            'share' => 'Share files with others',
            'version_manage' => 'Restore/delete previous versions',
            'upload_in_assigned' => 'Upload to folders shared with user',
        ],
        'system' => [
            'view_logs' => 'View system logs and statistics',
            'manage_settings' => 'Configure module settings',
            'backup_restore' => 'Backup and restore data',
        ]
    ],

    // ============================================================
    // DEFAULT FOLDER PERMISSIONS (Unchanged)
    // ============================================================
    'folder_defaults' => [
        'private' => [
            'owner' => ['create', 'rename', 'move', 'delete', 'upload', 'manage_permissions'],
            'others' => []
        ],
        'public' => [
            'owner' => ['create', 'rename', 'move', 'delete', 'upload', 'manage_permissions'],
            'others' => ['view', 'download']
        ],
        'restricted' => [
            'owner' => ['create', 'rename', 'move', 'delete', 'upload', 'manage_permissions'],
            'others' => []
        ]
    ],

    // ============================================================
    // MAX LIMITS (Unchanged)
    // ============================================================
    'limits' => [
        'max_folder_depth' => 10,
        'max_files_per_folder' => 1000,
        'max_folder_name_length' => 255,
        'forbidden_names' => ['.', '..', 'CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'LPT1'],
        'reserved_paths' => ['system', 'trash', 'temp']
    ]
];