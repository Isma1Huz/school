<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadModuleProviders();
    }

    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadModuleMigrations();
        $this->loadModuleViews();
        $this->loadModuleTranslations();
    }

    /**
     * Register each module's own ServiceProvider (if it exists).
     */
    protected function loadModuleProviders(): void
    {
        foreach ($this->getModuleNames() as $moduleName) {
            $providerClass = "App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * Load web and API routes for each module.
     */
    protected function loadModuleRoutes(): void
    {
        foreach ($this->getModulePaths() as $modulePath) {
            $webRoutes = $modulePath . '/routes/web.php';
            $apiRoutes = $modulePath . '/routes/api.php';

            if (File::exists($webRoutes)) {
                Route::middleware('web')->group($webRoutes);
            }

            if (File::exists($apiRoutes)) {
                Route::prefix('api')->middleware('api')->group($apiRoutes);
            }
        }
    }

    /**
     * Load migrations from every module's migrations folder.
     */
    protected function loadModuleMigrations(): void
    {
        foreach ($this->getModulePaths() as $modulePath) {
            $migrations = $modulePath . '/migrations';
            if (File::isDirectory($migrations)) {
                $this->loadMigrationsFrom($migrations);
            }
        }
    }

    /**
     * Load Blade views from each module's views folder.
     */
    protected function loadModuleViews(): void
    {
        foreach ($this->getModuleNames() as $moduleName) {
            $views = app_path("Modules/{$moduleName}/views");
            if (File::isDirectory($views)) {
                $this->loadViewsFrom($views, strtolower($moduleName));
            }
        }
    }

    /**
     * Load translations from each module's lang folder.
     */
    protected function loadModuleTranslations(): void
    {
        foreach ($this->getModuleNames() as $moduleName) {
            $lang = app_path("Modules/{$moduleName}/lang");
            if (File::isDirectory($lang)) {
                $this->loadTranslationsFrom($lang, strtolower($moduleName));
            }
        }
    }

    /**
     * Return an array of all module directory paths.
     *
     * @return string[]
     */
    protected function getModulePaths(): array
    {
        $modulesPath = app_path('Modules');

        if (!File::isDirectory($modulesPath)) {
            return [];
        }

        return File::directories($modulesPath);
    }

    /**
     * Return an array of all module names (directory base names).
     *
     * @return string[]
     */
    protected function getModuleNames(): array
    {
        return array_map('basename', $this->getModulePaths());
    }
}
