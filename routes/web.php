<?php

use Illuminate\Support\Facades\Route;
use SmartWF\LaravelWebTerminal\Http\Controllers\WebTerminalController;

/*
|--------------------------------------------------------------------------
| Web Terminal Routes
|--------------------------------------------------------------------------
|
| These routes handle the Laravel Web Terminal functionality.
| All routes are protected by the web-terminal-auth middleware.
|
*/

$prefix = config('web-terminal.route.prefix', 'web-terminal');
$middleware = config('web-terminal.route.middleware', ['web', 'web-terminal-auth']);
$name = config('web-terminal.route.name', 'web-terminal.');

Route::prefix($prefix)
    ->middleware($middleware)
    ->name($name)
    ->group(function () {
        
        // Main terminal interface
        Route::get('/', [WebTerminalController::class, 'index'])
            ->name('index');
        
        // Execute command via AJAX
        Route::post('/execute', [WebTerminalController::class, 'execute'])
            ->name('execute');
        
        // Get system information
        Route::get('/info', [WebTerminalController::class, 'info'])
            ->name('info');
        
        // Command history management
        Route::get('/history', [WebTerminalController::class, 'history'])
            ->name('history');
        
        Route::delete('/history', [WebTerminalController::class, 'clearHistory'])
            ->name('history.clear');
    });