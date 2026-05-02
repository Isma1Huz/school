<?php

namespace App\Console\Commands;

use App\Services\ModuleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleRegisterCommand extends Command
{
    protected $signature = 'module:register {name : The module name (PascalCase)}';
    protected $description = 'Register an existing module in the database from its module.json manifest';

    public function handle(ModuleService $moduleService): int
    {
        $name = ucfirst($this->argument('name'));
        $manifestPath = app_path("Modules/{$name}/module.json");

        if (!File::exists($manifestPath)) {
            $this->error("module.json not found at: {$manifestPath}");
            return self::FAILURE;
        }

        $module = $moduleService->registerFromManifest($name);

        $this->info("Module [{$module->key}] registered successfully (ID: {$module->id}).");

        return self::SUCCESS;
    }
}
