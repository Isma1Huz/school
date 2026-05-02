<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleMakeCommand extends Command
{
    protected $signature = 'module:make
        {name : The module name (PascalCase)}
        {--type= : Comma-separated allowed tenant types (default: school)}
        {--dependencies= : Comma-separated module keys this module depends on}';

    protected $description = 'Scaffold a new module folder structure under app/Modules/';

    public function handle(): int
    {
        $name = ucfirst($this->argument('name'));
        $path = app_path("Modules/{$name}");

        if (File::exists($path)) {
            $this->error("Module [{$name}] already exists at: {$path}");
            return self::FAILURE;
        }

        $this->createDirectories($path);
        $this->createFiles($name, $path);

        $this->info("Module [{$name}] scaffolded successfully.");
        $this->line("  Path: {$path}");
        $this->newLine();
        $this->line("  Next steps:");
        $this->line("    1. Edit app/Modules/{$name}/module.json");
        $this->line("    2. Run: php artisan module:register {$name}");

        return self::SUCCESS;
    }

    protected function createDirectories(string $path): void
    {
        $directories = [
            'Controllers',
            'Controllers/Admin',
            'Controllers/Api',
            'Services/Contracts',
            'Models/Traits',
            'Repositories/Contracts',
            'Events',
            'Listeners',
            'Jobs',
            'Exports',
            'Imports',
            'routes',
            'migrations',
            'seeders',
            'Providers',
            'config',
        ];

        foreach ($directories as $dir) {
            File::makeDirectory("{$path}/{$dir}", 0755, true);
        }
    }

    protected function createFiles(string $name, string $path): void
    {
        $key = strtolower($name);

        // Resolve options
        $types = $this->option('type')
            ? array_filter(array_map('trim', explode(',', $this->option('type'))))
            : ['school'];

        $dependencies = $this->option('dependencies')
            ? array_filter(array_map('trim', explode(',', $this->option('dependencies'))))
            : [];

        // module.json
        $manifest = [
            'name'                => $name,
            'key'                 => $key,
            'icon'                => 'PackageIcon',
            'description'         => "{$name} module",
            'version'             => '1.0.0',
            'allowed_tenant_types'=> array_values($types),
            'dependencies'        => array_values($dependencies),
            'permissions'         => [
                "{$key}:view",
                "{$key}:create",
                "{$key}:edit",
                "{$key}:delete",
            ],
            'settings_schema'     => (object) [],
            'sort_order'          => 100,
            'is_core'             => false,
        ];

        File::put("{$path}/module.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Providers/{Name}ServiceProvider.php
        File::put("{$path}/Providers/{$name}ServiceProvider.php", $this->getServiceProviderTemplate($name));

        // Controllers/{Name}Controller.php
        File::put("{$path}/Controllers/{$name}Controller.php", $this->getControllerTemplate($name));

        // Models/{Name}.php
        File::put("{$path}/Models/{$name}.php", $this->getModelTemplate($name));

        // Services/{Name}Service.php
        File::put("{$path}/Services/{$name}Service.php", $this->getServiceTemplate($name));

        // routes/web.php and api.php
        File::put("{$path}/routes/web.php", "<?php\n\n// {$name} web routes\n");
        File::put("{$path}/routes/api.php", "<?php\n\n// {$name} API routes\n");

        // Migration stub
        $migrationFile = date('Y_m_d_His') . "_create_{$key}_tables.php";
        File::put("{$path}/migrations/{$migrationFile}", $this->getMigrationTemplate($name));
    }

    protected function getServiceProviderTemplate(string $name): string
    {
        return <<<PHP
<?php

namespace App\Modules\\{$name}\Providers;

use Illuminate\Support\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
PHP;
    }

    protected function getControllerTemplate(string $name): string
    {
        return <<<PHP
<?php

namespace App\Modules\\{$name}\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class {$name}Controller extends Controller
{
    public function index(Request \$request)
    {
        //
    }
}
PHP;
    }

    protected function getModelTemplate(string $name): string
    {
        return <<<PHP
<?php

namespace App\Modules\\{$name}\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    use HasTenant;

    protected \$fillable = [
        'tenant_id',
    ];
}
PHP;
    }

    protected function getServiceTemplate(string $name): string
    {
        return <<<PHP
<?php

namespace App\Modules\\{$name}\Services;

class {$name}Service
{
    //
}
PHP;
    }

    protected function getMigrationTemplate(string $name): string
    {
        $table = strtolower($name) . '_items';

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
    }
}
