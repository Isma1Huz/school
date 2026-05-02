<?php

use App\Providers\AppServiceProvider;
use App\Providers\ModuleServiceProvider;
use App\Providers\TenantServiceProvider;

return [
    AppServiceProvider::class,
    TenantServiceProvider::class,
    ModuleServiceProvider::class,
];
