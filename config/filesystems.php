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

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],

        'ftp' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            'root' => env('FTP_ROOT') // for example: /public_html/images
 
            // Optional FTP Settings...
            // 'port' => env('FTP_PORT', 21),
            // 'passive' => true,
            // 'ssl' => true,
            // 'timeout' => 30,
        ],

        'sftp' => [
            'driver' => 'sftp',
            'host' => env('SFTP_HOST'),
            'username' => env('SFTP_USERNAME'),
            'password' => env('SFTP_PASSWORD'),
            'root' => env('SFTP_ROOT'),
            'permPublic' => 0755,
            'directoryPerm' => 0755,
          
            // Settings for SSH key based authentication with encryption password...
            //'privateKey' => env('SFTP_PRIVATE_KEY'),
            //'passphrase' => env('SFTP_PASSPHRASE'),
          
            // Optional SFTP Settings...
            // 'hostFingerprint' => env('SFTP_HOST_FINGERPRINT'),
            // 'maxTries' => 4,
            // 'passphrase' => env('SFTP_PASSPHRASE'),
            // 'port' => env('SFTP_PORT', 22),
            // 'timeout' => 30,
            // 'useAgent' => true,
        ],

        'google' => [
            'driver' => 'google',
            'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folderId' => env('GOOGLE_DRIVE_FOLDER_ID'),
            'folder' => env('GOOGLE_DRIVE_FOLDER'),
        ],

        'gcs' => [
    		'driver' => 'gcs',
    		'key_file_path' => env('GOOGLE_CLOUD_KEY_FILE', base_path('laravel-gcs.json')),
    		'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'ptab-cloud-storage'),
    		'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'ptab_gcstorage_bucket'),
    		'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''),
    		'storage_api_uri' => env('GOOGLE_CLOUD_STORAGE_API_URI', null),
    		'apiEndpoint' => env('GOOGLE_CLOUD_STORAGE_API_ENDPOINT', null),
    		'visibility' => 'private',
    		'visibility_handler' => null,
    		'metadata' => ['cacheControl'=> 'public,max-age=86400'],
    		'throw' => true,
],

    ],

];
