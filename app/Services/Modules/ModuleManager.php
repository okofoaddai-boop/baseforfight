<?php

namespace App\Services\Modules;

use App\Models\ModuleState;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;

class ModuleManager
{
    public function all(): array
    {
        $definitions = (array) config('modules.definitions', []);
        $this->syncModuleStates($definitions);

        $states = ModuleState::query()->get()->keyBy('slug');
        $result = [];

        foreach ($definitions as $slug => $definition) {
            $state = $states->get($slug);
            $result[] = [
                'slug' => $slug,
                'name' => (string) Arr::get($definition, 'name', $slug),
                'description' => (string) Arr::get($definition, 'description', ''),
                'class' => (string) Arr::get($definition, 'class', 'integration'),
                'is_active' => (bool) ($state?->is_active ?? false),
                'activated_at' => $state?->activated_at,
            ];
        }

        return $result;
    }

    public function isActive(string $slug): bool
    {
        return (bool) ModuleState::query()->where('slug', $slug)->value('is_active');
    }

    public function activate(string $slug): void
    {
        $definitions = (array) config('modules.definitions', []);
        if (! array_key_exists($slug, $definitions)) {
            abort(404);
        }

        $this->runActivationScript($slug);
        $this->runModuleMigrations($slug);

        ModuleState::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'display_name' => (string) Arr::get($definitions[$slug], 'name', $slug),
                'is_active' => true,
                'activated_at' => now(),
                'deactivated_at' => null,
            ]
        );
    }

    public function deactivate(string $slug): void
    {
        ModuleState::query()->where('slug', $slug)->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }

    private function syncModuleStates(array $definitions): void
    {
        foreach ($definitions as $slug => $definition) {
            ModuleState::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'display_name' => (string) Arr::get($definition, 'name', $slug),
                    'is_active' => false,
                ]
            );
        }
    }

    private function runActivationScript(string $slug): void
    {
        $scriptPath = base_path("modules/{$slug}/scripts/activate.php");
        if (is_file($scriptPath)) {
            require $scriptPath;
        }
    }

    private function runModuleMigrations(string $slug): void
    {
        $migrationPath = "modules/{$slug}/database/migrations";
        if (! is_dir(base_path($migrationPath))) {
            return;
        }

        Artisan::call('migrate', [
            '--path' => $migrationPath,
            '--force' => true,
        ]);
    }
}
