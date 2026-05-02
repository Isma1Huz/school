<?php

namespace App\Console\Commands;

use App\Models\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleRemoveCommand extends Command
{
    protected $signature = 'module:remove
        {name : The module key or PascalCase name to remove}
        {--force : Also delete the module folder from the filesystem (irreversible)}';

    protected $description = 'Remove (soft-delete) a module from the database, optionally deleting its folder';

    public function handle(): int
    {
        $name = $this->argument('name');
        $key  = strtolower($name);

        $module = Module::where('key', $key)->first();

        if (!$module) {
            $this->error("Module [{$key}] not found in the database.");
            return self::FAILURE;
        }

        if ($module->is_core) {
            $this->error("Module [{$key}] is a core module and cannot be removed.");
            return self::FAILURE;
        }

        $confirmed = $this->option('force')
            ? $this->confirm("This will delete the module folder for [{$key}]. Are you sure?", false)
            : true;

        if (!$confirmed) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        // Soft-delete the module record
        $module->delete();
        $this->info("Module [{$key}] has been soft-deleted from the database.");

        if ($this->option('force')) {
            $modulePath = app_path("Modules/" . ucfirst($name));

            if (File::isDirectory($modulePath)) {
                File::deleteDirectory($modulePath);
                $this->warn("Module folder deleted: {$modulePath}");
            } else {
                $this->warn("Module folder not found at: {$modulePath}");
            }
        }

        return self::SUCCESS;
    }
}
