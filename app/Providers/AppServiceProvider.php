<?php

namespace App\Providers;

use App\Services\Modules\ModuleManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('admin.*', function ($view): void {
            if (! Schema::hasTable('module_states')) {
                $view->with('activeModules', []);

                return;
            }

            $moduleManager = app(ModuleManager::class);
            $modules = collect($moduleManager->all());

            $view->with('activeModules', $modules->where('is_active', true)->pluck('slug')->values()->all());
        });
    }
}
