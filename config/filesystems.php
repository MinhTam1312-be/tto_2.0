<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'), // Đường dẫn tới thư mục lưu trữ cục bộ
        ],

    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('https://be-datn-production-19f3.up.railway.app/api/') . '/storage',
        'visibility' => 'public',
    ],

        'cloudinary' => [
            'driver' => 'cloudinary',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'), // Khóa truy cập AWS
            'secret' => env('AWS_SECRET_ACCESS_KEY'), // Mật khẩu truy cập AWS
            'region' => env('AWS_DEFAULT_REGION'), // Khu vực AWS
            'bucket' => env('AWS_BUCKET'), // Tên bucket trên S3
            'url' => env('AWS_URL'), // URL tùy chỉnh (nếu có)
            'endpoint' => env('AWS_ENDPOINT'), // Điểm cuối của S3 (nếu cần)
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false), // Sử dụng đường dẫn kiểu
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
