<?php

/*
|--------------------------------------------------------------------------
| Laravel Horizon Configuration
|--------------------------------------------------------------------------
|
| Note: To activate Horizon, run:
|   php artisan horizon:install
|   php artisan horizon:publish
|
*/

use Laravel\Horizon\MasterSupervisor;
use Laravel\Horizon\Repositories\RedisJobRepository;
use Laravel\Horizon\Repositories\RedisMasterSupervisorRepository;
use Laravel\Horizon\Repositories\RedisMetricsRepository;
use Laravel\Horizon\Repositories\RedisTagRepository;
use Laravel\Horizon\Repositories\RedisWorkloadRepository;

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    */

    'domain' => env('HORIZON_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    */

    'path' => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    */

    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    */

    'waits' => [
        'redis:default'       => 60,
        'redis:events'        => 30,
        'redis:notifications' => 60,
        'redis:sms'           => 30,
        'redis:reports'       => 300,
        'redis:imports'       => 300,
        'redis:analytics'     => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    */

    'trim' => [
        'recent'        => 60,
        'pending'       => 60,
        'completed'     => 60,
        'recent_failed' => 10080,
        'failed'        => 10080,
        'monitored'     => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Silenced Jobs
    |--------------------------------------------------------------------------
    */

    'silenced' => [],

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    */

    'metrics' => [
        'trim_snapshots' => [
            'job'   => 24,
            'queue' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    */

    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    */

    'memory_limit' => 64,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | supervisor-1: Handles high-priority and real-time queues.
    | supervisor-2: Handles slow, resource-intensive queues (1 worker each).
    |
    */

    'environments' => [

        'production' => [

            'supervisor-1' => [
                'connection'   => 'redis',
                'queue'        => ['default', 'events', 'notifications', 'sms'],
                'balance'      => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'tries'        => 3,
                'timeout'      => 60,
            ],

            'supervisor-2' => [
                'connection' => 'redis',
                'queue'      => ['reports', 'imports', 'analytics'],
                'balance'    => 'simple',
                'processes'  => 1,
                'tries'      => 1,
                'timeout'    => 300,
            ],

        ],

        'local' => [

            'supervisor-1' => [
                'connection'   => 'redis',
                'queue'        => ['default', 'events', 'notifications', 'sms'],
                'balance'      => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'tries'        => 3,
                'timeout'      => 60,
            ],

            'supervisor-2' => [
                'connection' => 'redis',
                'queue'      => ['reports', 'imports', 'analytics'],
                'balance'    => 'simple',
                'processes'  => 1,
                'tries'      => 1,
                'timeout'    => 300,
            ],

        ],

    ],

];
