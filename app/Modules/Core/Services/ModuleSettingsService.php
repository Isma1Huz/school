<?php

declare(strict_types=1);

namespace App\Modules\Core\Services;

use App\Models\Tenant;
use App\Modules\Core\Exceptions\InvalidSettingException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ModuleSettingsService
{
    /** @var array<string, array> In-memory schema cache */
    private static array $schemaCache = [];

    /**
     * Get a single setting for a tenant's module.
     * Falls back to the schema default if not set.
     */
    public function get(string $tenantId, string $moduleKey, string $settingKey): mixed
    {
        $all = $this->getAll($tenantId, $moduleKey);

        return $all[$settingKey] ?? null;
    }

    /**
     * Set a single setting value, validating it against the schema first.
     *
     * @throws InvalidSettingException
     */
    public function set(string $tenantId, string $moduleKey, string $settingKey, mixed $value): void
    {
        $schema = $this->loadSchema($moduleKey);
        $fieldSchema = $schema['settings_schema'][$settingKey] ?? null;

        if ($fieldSchema === null) {
            throw new InvalidSettingException(
                "Unknown setting key '{$settingKey}' for module '{$moduleKey}'."
            );
        }

        $this->validateField($settingKey, $value, $fieldSchema);

        $module = DB::table('modules')->where('key', $moduleKey)->first();

        if ($module === null) {
            throw new InvalidSettingException("Module '{$moduleKey}' not found.");
        }

        // Read current settings, merge the new key in PHP, and write back
        // using a parameterized query — avoids raw SQL injection risk.
        $pivot = DB::table('module_tenant')
            ->where('tenant_id', $tenantId)
            ->where('module_id', $module->id)
            ->value('settings');

        $current = $pivot ? (json_decode($pivot, true) ?? []) : [];
        $current[$settingKey] = $value;
        $merged = json_encode($current, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        DB::table('module_tenant')
            ->where('tenant_id', $tenantId)
            ->where('module_id', $module->id)
            ->update(['settings' => $merged]);

        Cache::forget("module_settings:{$tenantId}:{$moduleKey}");
    }

    /**
     * Get all settings for a tenant's module, merged with schema defaults.
     */
    public function getAll(string $tenantId, string $moduleKey): array
    {
        return Cache::remember(
            "module_settings:{$tenantId}:{$moduleKey}",
            300,
            function () use ($tenantId, $moduleKey): array {
                $schema   = $this->loadSchema($moduleKey);
                $defaults = $this->extractDefaults($schema['settings_schema'] ?? []);

                $module = DB::table('modules')->where('key', $moduleKey)->first();

                if ($module === null) {
                    return $defaults;
                }

                $pivot = DB::table('module_tenant')
                    ->where('tenant_id', $tenantId)
                    ->where('module_id', $module->id)
                    ->value('settings');

                $overrides = $pivot ? (json_decode($pivot, true) ?? []) : [];

                return array_merge($defaults, $overrides);
            }
        );
    }

    /**
     * Validate a full settings array against the module schema.
     *
     * @return array{valid: bool, errors: array<string, string>}
     */
    public function validate(string $moduleKey, array $settings): array
    {
        $schema = $this->loadSchema($moduleKey);
        $fieldSchemas = $schema['settings_schema'] ?? [];
        $errors = [];

        foreach ($fieldSchemas as $key => $fieldSchema) {
            if (($fieldSchema['required'] ?? false) && !array_key_exists($key, $settings)) {
                $errors[$key] = "The '{$key}' setting is required.";
                continue;
            }

            if (array_key_exists($key, $settings)) {
                try {
                    $this->validateField($key, $settings[$key], $fieldSchema);
                } catch (InvalidSettingException $e) {
                    $errors[$key] = $e->getMessage();
                }
            }
        }

        // Check for unknown keys
        foreach (array_keys($settings) as $key) {
            if (!array_key_exists($key, $fieldSchemas)) {
                $errors[$key] = "Unknown setting key '{$key}'.";
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Load and parse the module's module.json schema.
     * Result is cached in a static property (memory cache).
     */
    public function loadSchema(string $moduleKey): array
    {
        if (isset(self::$schemaCache[$moduleKey])) {
            return self::$schemaCache[$moduleKey];
        }

        // Guard against path traversal: only allow lowercase alphanumeric + underscores
        if (!preg_match('/^[a-z0-9_]+$/', $moduleKey)) {
            throw new \InvalidArgumentException("Invalid module key '{$moduleKey}'.");
        }

        $moduleName = ucfirst($moduleKey);
        $path = app_path("Modules/{$moduleName}/module.json");

        // Ensure the resolved path stays within the modules directory
        $modulesDir = app_path('Modules');
        $realPath   = realpath(dirname($path));
        if ($realPath === false || !str_starts_with($realPath, $modulesDir)) {
            throw new \RuntimeException("module.json path is outside the modules directory.");
        }

        if (!file_exists($path)) {
            throw new \RuntimeException("module.json not found for module '{$moduleKey}'.");
        }

        $contents = file_get_contents($path);
        $parsed   = json_decode($contents, true);

        if ($parsed === null) {
            throw new \RuntimeException("Invalid JSON in module.json for module '{$moduleKey}'.");
        }

        self::$schemaCache[$moduleKey] = $parsed;

        return $parsed;
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    private function extractDefaults(array $fieldSchemas): array
    {
        $defaults = [];

        foreach ($fieldSchemas as $key => $schema) {
            $defaults[$key] = $schema['default'] ?? null;
        }

        return $defaults;
    }

    /**
     * Validate a single field value against its schema definition.
     *
     * @throws InvalidSettingException
     */
    private function validateField(string $key, mixed $value, array $schema): void
    {
        $type = $schema['type'] ?? 'string';

        switch ($type) {
            case 'string':
                if (!is_string($value) && $value !== null) {
                    throw new InvalidSettingException("Setting '{$key}' must be a string.");
                }
                if (is_string($value) && isset($schema['max']) && mb_strlen($value) > (int) $schema['max']) {
                    throw new InvalidSettingException(
                        "Setting '{$key}' must not exceed {$schema['max']} characters."
                    );
                }
                break;

            case 'boolean':
                if (!is_bool($value) && $value !== null) {
                    throw new InvalidSettingException("Setting '{$key}' must be a boolean.");
                }
                break;

            case 'integer':
            case 'number':
                if (!is_numeric($value) && $value !== null) {
                    throw new InvalidSettingException("Setting '{$key}' must be numeric.");
                }
                break;

            case 'enum':
                $options = $schema['options'] ?? [];
                if ($value !== null && !in_array($value, $options, true)) {
                    $allowed = implode(', ', $options);
                    throw new InvalidSettingException(
                        "Setting '{$key}' must be one of: {$allowed}."
                    );
                }
                break;

            default:
                // Unknown type — pass through
                break;
        }

        if (($schema['required'] ?? false) && ($value === null || $value === '')) {
            throw new InvalidSettingException("Setting '{$key}' is required and cannot be empty.");
        }
    }
}
