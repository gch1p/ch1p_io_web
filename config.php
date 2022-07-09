<?php

return [
    'domain' => 'example.com',
    'cookie_host' => '.example.com',
    'admin_email' => 'admin@example.com',

    'db' => [
        'type' => 'mysql',
        'host' => '127.0.0.1',
        'user' => '',
        'password' => '',
        'database' => '',
    ],

    'log_file' => '/var/log/example.com-backend.log',

    'password_salt' => '12345',
    'csrf_token' => '12345',
    'uploads_dir' => '/home/user/files.example.com',
    'uploads_host' => 'files.example.com',

    'dirs_mode' => 0775,
    'files_mode' => 0664,
    'group' => 33, // id -g www-data
    'is_dev' => false,
];
