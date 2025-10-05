<?php

namespace SynceraTech\LaravelWebTerminal;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use SynceraTech\LaravelWebTerminal\Http\Middleware\WebTerminalAuth;
use SynceraTech\LaravelWebTerminal\Console\Commands\GenerateKeyCommand;

class LaravelWebTerminalServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/web-terminal.php', 'web-terminal');

        // Register services
        $this->app->singleton('web-terminal', function ($app) {
            return new WebTerminalManager($app);
        });
    }

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/web-terminal.php' => config_path('web-terminal.php'),
            ], 'web-terminal-config');

            // Register commands
            $this->commands([
                GenerateKeyCommand::class,
            ]);
        }

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'web-terminal');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/web-terminal'),
        ], 'web-terminal-views');

        // Register middleware
        $this->app['router']->aliasMiddleware('web-terminal-auth', WebTerminalAuth::class);

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Configure logging channel
        $this->configureLogging();
    }

    /**
     * Configure logging channel for web terminal
     */
    protected function configureLogging(): void
    {
        $config = config('logging.channels', []);
        
        if (!isset($config['web-terminal'])) {
            config([
                'logging.channels.web-terminal' => [
                    'driver' => 'single',
                    'path' => storage_path('logs/web-terminal.log'),
                    'level' => config('web-terminal.logging.level', 'info'),
                    'permission' => 0644,
                ]
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['web-terminal'];
    }
}